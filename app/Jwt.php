<?php
// =============================================================
// 7NVENT - Minimal JWT (HS256) helper for the mobile API
// =============================================================
// Dependency-free by design: no composer package assumed installed.
// Implements just enough of RFC 7519 for our own login/verify loop —
// not a general-purpose JWT library (no alg negotiation, no other
// signing algorithms, no JWK support). That's a deliberate scope cut,
// not an oversight: /api/* only ever issues and reads its own tokens.

require_once __DIR__ . '/../config/config.php';

class Jwt {

    private static function b64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string {
        $pad = strlen($data) % 4;
        if ($pad > 0) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Issue a signed token for the given payload. `sub` (subject / user_id)
     * and `exp` (expiry, unix timestamp) are set automatically.
     */
    public static function issue(array $payload): string {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_TTL_SECONDS;

        $segments = [
            self::b64UrlEncode(json_encode($header)),
            self::b64UrlEncode(json_encode($payload)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, JWT_SECRET, true);
        $segments[] = self::b64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Verify a token's signature and expiry. Returns the decoded payload
     * array on success, or null on any failure (bad format, bad signature,
     * expired). Never throws — callers just check for null.
     */
    public static function verify(?string $token): ?array {
        if (!$token) return null;

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$headerB64, $payloadB64, $sigB64] = $parts;

        $expectedSig = hash_hmac('sha256', "$headerB64.$payloadB64", JWT_SECRET, true);
        $actualSig   = self::b64UrlDecode($sigB64);

        // constant-time comparison — avoid timing side-channels on the
        // signature check
        if (!hash_equals($expectedSig, $actualSig)) return null;

        $payload = json_decode(self::b64UrlDecode($payloadB64), true);
        if (!is_array($payload)) return null;

        if (!isset($payload['exp']) || time() >= (int) $payload['exp']) return null;

        return $payload;
    }

    /**
     * Pull "Bearer <token>" out of the Authorization header, handling the
     * fact that PHP under Apache sometimes hides it from $_SERVER and only
     * exposes it via apache_request_headers() or getallheaders().
     */
    public static function bearerFromRequest(): ?string {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$header && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    $header = $value;
                    break;
                }
            }
        }

        if (!$header || stripos($header, 'Bearer ') !== 0) return null;
        return trim(substr($header, 7));
    }
}
