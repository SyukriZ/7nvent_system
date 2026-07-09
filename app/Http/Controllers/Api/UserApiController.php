<?php
// =============================================================
// 7NVENT - User API Controller (mobile)
// =============================================================
// Mirrors UserController exactly — same queries, same validation, same
// access-level mapping, same role gate (Inventory Manager / IT Administrator).

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class UserApiController extends ApiController {

    /** GET /api/users */
    public function index(): void {
        $this->requireRole('Inventory Manager', 'IT Administrator');
        $users = db()->fetchAll(
            "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.role_id, u.full_name"
        );
        // Never ship password hashes to a client, even an authorized one.
        foreach ($users as &$u) unset($u['password']);
        $this->json(['success' => true, 'users' => $users]);
    }

    /** GET /api/users/meta — roles list for the create/edit form. */
    public function meta(): void {
        $this->requireRole('Inventory Manager', 'IT Administrator');
        $this->json(['success' => true, 'roles' => db()->fetchAll("SELECT * FROM roles ORDER BY role_id")]);
    }

    /** GET /api/users/detail?id=123 */
    public function detail(): void {
        $this->requireRole('Inventory Manager', 'IT Administrator');
        $userId = (int)($_GET['id'] ?? 0);
        $editUser = db()->fetchOne(
            "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
            [$userId]
        );
        if (!$editUser) $this->jsonError('User not found.', 404);
        unset($editUser['password']);
        $this->json(['success' => true, 'user' => $editUser]);
    }

    /** POST /api/users/store */
    public function store(): void {
        $payload = $this->requireRole('IT Administrator', 'Inventory Manager');
        $body = $this->body();

        $username    = trim((string)($body['username'] ?? ''));
        $rawPassword = (string)($body['password'] ?? '');
        $fullName    = trim((string)($body['full_name'] ?? ''));
        $email       = trim((string)($body['email'] ?? ''));
        $roleId      = (int)($body['role_id'] ?? 0);
        $dept        = trim((string)($body['department'] ?? ''));

        $errors = [];
        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3-50 characters (letters, numbers, dot, underscore, dash only).';
        } elseif (db()->fetchOne("SELECT user_id FROM users WHERE username = ?", [$username])) {
            $errors[] = "Username '$username' is already taken.";
        }
        if ($fullName === '') $errors[] = 'Full name is required.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email address is invalid.';
        if (strlen($rawPassword) < 8) $errors[] = 'Password must be at least 8 characters.';
        $roleRow = $roleId > 0 ? db()->fetchOne("SELECT role_name FROM roles WHERE role_id = ?", [$roleId]) : null;
        if (!$roleRow) $errors[] = 'A valid role must be selected.';
        if ($errors) $this->jsonError(implode(' ', $errors), 422);

        $password = password_hash($rawPassword, PASSWORD_BCRYPT);
        $accessMap = [
            'Inventory Manager'    => 'Full Admin',
            'Housekeeping Manager' => 'Update',
            'Procurement Officer'  => 'PO Manager',
            'IT Administrator'     => 'System Admin',
            'Hotel GM'             => 'Approval Only',
            'Supervisor'           => 'Spectator',
        ];
        $accessLevel = $accessMap[$roleRow['role_name'] ?? ''] ?? 'Spectator';

        db()->execute(
            "INSERT INTO users (username, password, full_name, email, role_id, department, access_level) VALUES (?,?,?,?,?,?,?)",
            [$username, $password, $fullName, $email, $roleId, $dept, $accessLevel]
        );
        $id = (int) db()->lastInsertId();
        Auth::log('ADD_USER', 'users', $id, "Created user (mobile app): $username", (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => "User '$fullName' added successfully!", 'user_id' => $id]);
    }

    /** POST /api/users/update — role/department/status only, same as web. */
    public function update(): void {
        $payload = $this->requireRole('Inventory Manager', 'IT Administrator');
        $body = $this->body();

        $userId = (int)($body['user_id'] ?? 0);
        $status = trim((string)($body['status'] ?? 'Active'));
        $roleId = (int)($body['role_id'] ?? 0);
        $dept   = trim((string)($body['department'] ?? ''));

        $errors = [];
        if (!$userId || !db()->fetchOne("SELECT user_id FROM users WHERE user_id = ?", [$userId])) {
            $errors[] = 'User not found.';
        }
        if (!in_array($status, ['Active', 'Inactive'], true)) $errors[] = 'Invalid status.';
        if (!db()->fetchOne("SELECT role_id FROM roles WHERE role_id = ?", [$roleId])) $errors[] = 'Invalid role selected.';
        if ($errors) $this->jsonError(implode(' ', $errors), 422);

        db()->execute("UPDATE users SET role_id=?, department=?, status=? WHERE user_id=?", [$roleId, $dept, $status, $userId]);
        Auth::log('UPDATE_USER', 'users', $userId, "Updated user ID: $userId (mobile app)", (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => 'User information updated successfully!']);
    }
}
