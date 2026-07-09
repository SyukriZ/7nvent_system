<?php
// =============================================================
// 7NVENT - Auth & Session Helper
// =============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {

    private static function getSetting(string $key, $default) {
        try {
            $row = db()->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            return $row ? $row['setting_value'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function attempt(string $username, string $password): bool {

        $maxAttempts    = (int) self::getSetting('security_max_attempts',    MAX_LOGIN_ATTEMPTS);
        $lockoutSeconds = (int) self::getSetting('security_lockout_duration', LOCKOUT_DURATION / 60) * 60;
        $sessionTimeout = (int) self::getSetting('security_session_timeout',  SESSION_TIMEOUT / 60) * 60;

        if (isset($_SESSION['login_attempts'][$username])) {
            $attempts = $_SESSION['login_attempts'][$username];
            if ($attempts['count'] >= $maxAttempts) {
                $elapsed = time() - $attempts['last_attempt'];
                if ($elapsed < $lockoutSeconds) {
                    $remaining = ceil(($lockoutSeconds - $elapsed) / 60);
                    $_SESSION['lockout_remaining'] = $remaining;
                    return false;
                }
                unset($_SESSION['login_attempts'][$username]);
            }
        }

        $user = db()->fetchOne(
            "SELECT u.*, r.role_name FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.username = ? AND u.status = 'Active'",
            [$username]
        );

        if (!$user) {
            if (!isset($_SESSION['login_attempts'][$username])) {
                $_SESSION['login_attempts'][$username] = ['count' => 0, 'last_attempt' => 0];
            }
            $_SESSION['login_attempts'][$username]['count']++;
            $_SESSION['login_attempts'][$username]['last_attempt'] = time();
            return false;
        }

        if (password_verify($password, $user['password'])) {
            unset($_SESSION['login_attempts'][$username]);
            unset($_SESSION['lockout_remaining']);

            $_SESSION['user_id']         = $user['user_id'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['full_name']       = $user['full_name'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['role_id']         = $user['role_id'];
            $_SESSION['role_name']       = $user['role_name'];
            $_SESSION['access_level']    = $user['access_level'];
            $_SESSION['department']      = $user['department'];
            $_SESSION['logged_in']       = true;
            $_SESSION['login_time']      = time();
            $_SESSION['session_timeout'] = $sessionTimeout;

            db()->execute("UPDATE users SET last_login = NOW() WHERE user_id = ?", [$user['user_id']]);
            self::log('LOGIN', 'users', $user['user_id'], 'User logged in successfully');
            return true;

        } else {
            if (!isset($_SESSION['login_attempts'][$username])) {
                $_SESSION['login_attempts'][$username] = ['count' => 0, 'last_attempt' => 0];
            }
            $_SESSION['login_attempts'][$username]['count']++;
            $_SESSION['login_attempts'][$username]['last_attempt'] = time();
            $used = $_SESSION['login_attempts'][$username]['count'];
            $_SESSION['attempts_remaining'] = max(0, $maxAttempts - $used);
            return false;
        }
    }

    public static function check(): bool {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) return false;
        $timeout = $_SESSION['session_timeout']
                    ?? ((int) self::getSetting('security_session_timeout', SESSION_TIMEOUT / 60) * 60);
        if (time() - $_SESSION['login_time'] > $timeout) {
            self::logout();
            return false;
        }
        $_SESSION['login_time'] = time();
        return true;
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        return [
            'user_id'      => $_SESSION['user_id'],
            'username'     => $_SESSION['username'],
            'full_name'    => $_SESSION['full_name'],
            'email'        => $_SESSION['email'],
            'role_id'      => $_SESSION['role_id'],
            'role_name'    => $_SESSION['role_name'],
            'access_level' => $_SESSION['access_level'],
            'department'   => $_SESSION['department'],
        ];
    }

    public static function logout(): void {
        if (isset($_SESSION['user_id'])) {
            self::log('LOGOUT', 'users', $_SESSION['user_id'], 'User logged out');
        }
        session_destroy();
        session_start();
    }

    public static function required(): void {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    // $userIdOverride lets callers that aren't running inside a web session —
    // the /api/* mobile controllers, which authenticate via JWT instead of
    // $_SESSION — pass the acting user's id explicitly. Without this, every
    // audit-log call from the mobile app would silently no-op (session
    // user_id is never set for a JWT-only request) and mobile-initiated
    // actions would leave no trail at all.
    public static function log(string $action, string $table = '', int $targetId = 0, string $desc = '', ?int $userIdOverride = null): void {
        $userId = $userIdOverride ?? ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) return;
        db()->execute(
            "INSERT INTO audit_logs (user_id, action, target_table, target_id, description, ip_address) VALUES (?,?,?,?,?,?)",
            [$userId, $action, $table, $targetId, $desc, $_SERVER['REMOTE_ADDR'] ?? '']
        );
    }

    public static function hasRole(string ...$roles): bool {
        return in_array($_SESSION['role_name'] ?? '', $roles);
    }

    public static function hasAccess(string $level): bool {
        return ($_SESSION['access_level'] ?? '') === $level
            || ($_SESSION['access_level'] ?? '') === 'Full Admin';
    }
}

function redirect(string $path, string $msg = '', string $type = 'success'): void {
    if ($msg) {
        $_SESSION['flash_message'] = $msg;
        $_SESSION['flash_type']    = $type;
    }
    header('Location: ' . APP_URL . $path);
    exit;
}

function flash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type'    => $_SESSION['flash_type'] ?? 'success'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $flash;
    }
    return null;
}

function clean(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatRM(float $amount): string {
    return 'RM ' . number_format($amount, 2);
}

function formatDate(string $date): string {
    return date('d M Y', strtotime($date));
}