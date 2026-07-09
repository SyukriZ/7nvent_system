<?php
// =============================================================
// 7NVENT - Base API Controller
// =============================================================
// Shared helpers for every /api/* controller: consistent JSON responses,
// JSON request-body parsing, and the bearer-token auth guard. Every
// Api*Controller should extend this instead of duplicating this glue.

require_once __DIR__ . '/../../../Jwt.php';
require_once __DIR__ . '/../../../Database.php';

abstract class ApiController {

    /** Send a JSON response and stop execution. */
    protected function json($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonError(string $message, int $status = 400, array $extra = []): void {
        $this->json(array_merge(['success' => false, 'message' => $message], $extra), $status);
    }

    /** Parse a JSON request body into an associative array (empty array if none/invalid). */
    protected function body(): array {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Require a valid bearer token. On success returns the decoded JWT
     * payload (includes user_id, username, role_name, access_level —
     * whatever AuthApiController::login() put in there). On failure sends
     * a 401 JSON response and stops execution — callers never need their
     * own auth-failure branch.
     */
    protected function requireAuth(): array {
        $token = Jwt::bearerFromRequest();
        $payload = Jwt::verify($token);

        if (!$payload) {
            $this->jsonError('Unauthorized — missing or expired token.', 401);
        }

        return $payload;
    }

    /** Same as requireAuth() but additionally checks role_name is one of the allowed list. */
    protected function requireRole(string ...$roles): array {
        $payload = $this->requireAuth();
        if (!in_array($payload['role_name'] ?? '', $roles, true)) {
            $this->jsonError('Forbidden — insufficient role for this action.', 403);
        }
        return $payload;
    }
}
