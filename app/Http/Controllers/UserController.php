<?php
require_once __DIR__ . '/../../Auth.php';

class UserController {
    public function index(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) redirect('/dashboard', 'Access denied.', 'error');
        $users = db()->fetchAll(
            "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.role_id, u.full_name"
        );
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/users/index.php';
    }

    public function create(): void {
        Auth::required();
        if (!Auth::hasRole('IT Administrator', 'Inventory Manager')) redirect('/users', 'Access denied.', 'error');
        $roles = db()->fetchAll("SELECT * FROM roles ORDER BY role_id");
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/users/create.php';
    }

    public function store(): void {
        Auth::required();
        if (!Auth::hasRole('IT Administrator', 'Inventory Manager')) redirect('/users', 'Access denied.', 'error');

        $username     = clean($_POST['username'] ?? '');
        $rawPassword  = $_POST['password'] ?? '';
        $fullName     = clean($_POST['full_name'] ?? '');
        $email        = clean($_POST['email'] ?? '');
        $roleId       = (int)($_POST['role_id'] ?? 0);
        $dept         = clean($_POST['department'] ?? '');

        // ── Server-side validation ──────────────────────────────────────
        // The create-user form's `required` attributes are client-only and
        // trivially bypassed with a raw POST — every rule is re-checked here.
        $errors = [];
        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3-50 characters (letters, numbers, dot, underscore, dash only).';
        } elseif (db()->fetchOne("SELECT user_id FROM users WHERE username = ?", [$username])) {
            $errors[] = "Username '$username' is already taken.";
        }
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address is invalid.';
        }
        // No silent "password123" fallback — a guessable default password
        // for every new account is a real security hole, not a convenience.
        if (strlen($rawPassword) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        $roleRow = $roleId > 0 ? db()->fetchOne("SELECT role_name FROM roles WHERE role_id = ?", [$roleId]) : null;
        if (!$roleRow) {
            $errors[] = 'A valid role must be selected.';
        }
        if ($errors) {
            redirect('/users/create', implode(' ', $errors), 'error');
        }

        $password = password_hash($rawPassword, PASSWORD_BCRYPT);
        $role     = $roleRow;

        // Get access level from role
        $accessMap = [
            'Inventory Manager'  => 'Full Admin',
            'Housekeeping Manager'=> 'Update',
            'Procurement Officer'=> 'PO Manager',
            'IT Administrator'   => 'System Admin',
            'Hotel GM'           => 'Approval Only',
            'Supervisor'         => 'Spectator',
        ];
        $accessLevel = $accessMap[$role['role_name'] ?? ''] ?? 'Spectator';

        db()->execute(
            "INSERT INTO users (username, password, full_name, email, role_id, department, access_level) VALUES (?,?,?,?,?,?,?)",
            [$username, $password, $fullName, $email, $roleId, $dept, $accessLevel]
        );
        $id = (int)db()->lastInsertId();
        Auth::log('ADD_USER', 'users', $id, "Created user: $username");
        redirect('/users', "Pengguna '$fullName' added successfully!", 'success');
    }

    public function edit(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) redirect('/users', 'Access denied.', 'error');
        $userId = (int)($_GET['id'] ?? 0);
        $editUser = db()->fetchOne("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?", [$userId]);
        if (!$editUser) redirect('/users', 'User not found.', 'error');
        $roles = db()->fetchAll("SELECT * FROM roles ORDER BY role_id");
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/users/edit.php';
    }

    public function update(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) redirect('/users', 'Access denied.', 'error');
        $userId = (int)($_POST['user_id'] ?? 0);
        $status = clean($_POST['status'] ?? 'Active');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $dept   = clean($_POST['department'] ?? '');

        $errors = [];
        if (!$userId || !db()->fetchOne("SELECT user_id FROM users WHERE user_id = ?", [$userId])) {
            $errors[] = 'User not found.';
        }
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $errors[] = 'Invalid status.';
        }
        if (!db()->fetchOne("SELECT role_id FROM roles WHERE role_id = ?", [$roleId])) {
            $errors[] = 'Invalid role selected.';
        }
        if ($errors) {
            redirect('/users', implode(' ', $errors), 'error');
        }

        db()->execute("UPDATE users SET role_id=?, department=?, status=? WHERE user_id=?", [$roleId, $dept, $status, $userId]);
        Auth::log('UPDATE_USER', 'users', $userId, "Updated user ID: $userId");
        redirect('/users', 'User information updated successfully!', 'success');
    }
}