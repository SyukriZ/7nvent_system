<?php
// =============================================================
// 7NVENT - Auth Controller
// =============================================================

require_once __DIR__ . '/../../Auth.php';

class AuthController {

    public function showLogin(): void {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $db = db();
        $liveStats = [
            'total_items'     => (int)($db->fetchOne("SELECT SUM(quantity) as t FROM inventory_items")['t'] ?? 0),
            'critical_alerts' => (int)($db->fetchOne("SELECT COUNT(*) as c FROM alerts WHERE alert_type='Critical' AND status='Active'")['c'] ?? 0),
            'pending_value'   => (float)($db->fetchOne("SELECT SUM(total_value) as v FROM purchase_orders WHERE status IN ('Pending','In Transit')")['v'] ?? 0),
        ];

        require_once __DIR__ . '/../../../resources/views/auth/login.php';
    }

    public function login(): void {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            redirect('/login', 'Please enter your username and password.', 'error');
        }

        if (Auth::attempt($username, $password)) {
            redirect('/dashboard', 'Welcome to 7NVENT!', 'success');
        } else {
            // Auth::attempt() already sets $_SESSION['lockout_remaining'] if locked
            if (isset($_SESSION['lockout_remaining'])) {
                $mins = (int)$_SESSION['lockout_remaining'];
                redirect('/login', "Too many failed attempts. Account locked for $mins minute(s).", 'error');
            }

            // Show remaining attempts warning
            $remaining = $_SESSION['attempts_remaining'] ?? null;
            if ($remaining !== null && $remaining > 0) {
                redirect('/login', 'Invalid username or password. Please try again.', 'error');
            } elseif ($remaining === 0) {
                redirect('/login', 'Account has been locked due to too many failed attempts.', 'error');
            }

            redirect('/login', 'Invalid username or password. Please try again.', 'error');
        }
    }

    public function logout(): void {
        Auth::logout();
        redirect('/login', 'You have been logged out successfully.', 'success');
    }
}