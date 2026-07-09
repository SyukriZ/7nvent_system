<?php
// =============================================================
// 7NVENT - Auth API Controller (mobile)
// =============================================================
// Mirrors AuthController's login logic exactly (reuses Auth::attempt() —
// same lockout counting, same password_verify, same audit log entry) so
// web and mobile login behave identically. The only difference is the
// output: instead of a session + redirect, this issues a JWT and returns
// JSON.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class AuthApiController extends ApiController {

    public function login(): void {
        $body = $this->body();
        $username = trim((string)($body['username'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->jsonError('Please enter your username and password.', 422);
        }

        if (Auth::attempt($username, $password)) {
            $user = Auth::user();

            $token = Jwt::issue([
                'user_id'      => $user['user_id'],
                'username'     => $user['username'],
                'full_name'    => $user['full_name'],
                'role_id'      => $user['role_id'],
                'role_name'    => $user['role_name'],
                'access_level' => $user['access_level'],
                'department'   => $user['department'],
            ]);

            $this->json([
                'success' => true,
                'token'   => $token,
                'expires_in' => JWT_TTL_SECONDS,
                'user'    => $user,
            ]);
        }

        // Auth::attempt() sets $_SESSION['lockout_remaining'] / ['attempts_remaining']
        // on failure — mirror the same messaging the web login shows.
        if (isset($_SESSION['lockout_remaining'])) {
            $mins = (int) $_SESSION['lockout_remaining'];
            $this->jsonError("Too many failed attempts. Account locked for $mins minute(s).", 423, [
                'lockout_remaining' => $mins,
            ]);
        }

        $remaining = $_SESSION['attempts_remaining'] ?? null;
        if ($remaining !== null) {
            $this->jsonError('Invalid username or password.', 401, [
                'attempts_remaining' => (int) $remaining,
            ]);
        }

        $this->jsonError('Invalid username or password.', 401);
    }

    /** GET /api/auth/me — validate the current token and return the profile. */
    public function me(): void {
        $payload = $this->requireAuth();

        // Re-fetch fresh from DB rather than trusting stale JWT claims for
        // anything that might have changed since the token was issued
        // (status, role, department) — the token only needs to prove WHO,
        // the DB is still the source of truth for CURRENT permissions.
        $user = db()->fetchOne(
            "SELECT u.user_id, u.username, u.full_name, u.email, u.role_id, u.department,
                    u.access_level, u.status, r.role_name
             FROM users u JOIN roles r ON u.role_id = r.role_id
             WHERE u.user_id = ?",
            [$payload['user_id']]
        );

        if (!$user || $user['status'] !== 'Active') {
            $this->jsonError('Account no longer active.', 401);
        }

        $this->json(['success' => true, 'user' => $user]);
    }

    /**
     * GET /api/public/stats — deliberately NOT behind requireAuth(). Mirrors
     * AuthController::showLogin()'s $liveStats exactly (same 3 queries) so
     * the mobile login screen can show the same "live" numbers the web
     * login page shows before a user has signed in / has a token yet.
     */
    public function publicStats(): void {
        $db = db();
        $this->json([
            'success' => true,
            'total_items'     => (int)($db->fetchOne("SELECT SUM(quantity) as t FROM inventory_items")['t'] ?? 0),
            'critical_alerts' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM alerts WHERE alert_type='Critical' AND status='Active'")['c'] ?? 0),
            'pending_value'   => (float)($db->fetchOne("SELECT SUM(total_value) as v FROM purchase_orders WHERE status IN ('Pending','In Transit')")['v'] ?? 0),
        ]);
    }

    /**
     * POST /api/auth/logout — JWTs are stateless (no server-side session to
     * destroy), so this is really just an audit-log entry; the client is
     * responsible for discarding its stored token. A revocation list would
     * be the next step if this needs to support "log out this device
     * remotely" — noted as a gap, not silently pretended away.
     */
    public function logout(): void {
        $payload = $this->requireAuth();
        db()->execute(
            "INSERT INTO audit_logs (user_id, action, target_table, target_id, description, ip_address) VALUES (?,?,?,?,?,?)",
            [$payload['user_id'], 'LOGOUT', 'users', $payload['user_id'], 'User logged out (mobile app)', $_SERVER['REMOTE_ADDR'] ?? '']
        );
        $this->json(['success' => true]);
    }
}
