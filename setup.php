<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>7NVENT — Setup Installer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #1a1a2e; color: #fff; font-family: Arial, sans-serif; }
        .setup-card { background: #16213e; border-radius: 16px; padding: 40px; max-width: 640px; margin: 60px auto; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .brand { font-size: 28px; font-weight: 900; }
        .brand span { color: #0096FF; }
        .step { background: #0f3460; border-radius: 10px; padding: 16px; margin-bottom: 12px; }
        .step.success { background: #064e3b; border-left: 4px solid #22c55e; }
        .step.error { background: #450a0a; border-left: 4px solid #ef4444; }
        .step.info { border-left: 4px solid #0096FF; }
        .btn-setup { background: #0096FF; border: none; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="brand mb-1"><span>7</span>NVENT</div>
    <div class="text-secondary small mb-4">Hotel Inventory Management System — Setup Installer v1.0</div>

<?php
// ============================================================
// 7NVENT Auto Installer
// Run this file ONCE to set up the database.
// Then DELETE this file for security.
// ============================================================

$config = [
    'host' => $_POST['host'] ?? 'localhost',
    'user' => $_POST['user'] ?? 'root',
    'pass' => $_POST['pass'] ?? '',
    'name' => $_POST['name'] ?? '7nvent',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    echo '<h5 class="mb-3">⚙️ Installing 7NVENT...</h5>';

    // Step 1: Connect to MySQL
    try {
        $pdo = new PDO("mysql:host={$config['host']};charset=utf8mb4", $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo '<div class="step success">✅ Step 1: MySQL connection successful</div>';
    } catch (Exception $e) {
        echo '<div class="step error">❌ Step 1: Cannot connect to MySQL — ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<a href="setup.php" class="btn btn-setup mt-3">Try Again</a></div></body></html>';
        exit;
    }

    // Step 2: Create database
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$config['name']}`");
        echo '<div class="step success">✅ Step 2: Database `' . htmlspecialchars($config['name']) . '` ready</div>';
    } catch (Exception $e) {
        echo '<div class="step error">❌ Step 2: ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }

    // Step 3: Run SQL schema
    $sqlFile = __DIR__ . '/database/7nvent_schema.sql';
    if (!file_exists($sqlFile)) {
        echo '<div class="step error">❌ Step 3: Schema file not found at database/7nvent_schema.sql</div>';
        exit;
    }
    $sql = file_get_contents($sqlFile);
    // Remove the USE statement since we already selected the DB
    $sql = preg_replace('/USE\s+`7nvent`;/i', '', $sql);

    try {
        // Split by semicolon for multi-statement execution
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $count = 0;
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
                $count++;
            }
        }
        echo '<div class="step success">✅ Step 3: Schema executed — ' . $count . ' statements processed</div>';
    } catch (Exception $e) {
        echo '<div class="step error">❌ Step 3: Schema error — ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }

    // Step 4: Update config.php
    $configContent = "<?php
define('DB_HOST', '{$config['host']}');
define('DB_NAME', '{$config['name']}');
define('DB_USER', '{$config['user']}');
define('DB_PASS', '{$config['pass']}');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', '7NVENT');
define('APP_URL', 'http://localhost/7nvent/public');
define('APP_VERSION', '1.0.0');

define('SESSION_TIMEOUT', 1800);
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 900);

date_default_timezone_set('Asia/Kuala_Lumpur');
error_reporting(0);
ini_set('display_errors', 0);
";
    file_put_contents(__DIR__ . '/config/config.php', $configContent);
    echo '<div class="step success">✅ Step 4: config.php updated with database credentials</div>';

    // Step 5: Verify tables
    $tables = $pdo->query("SHOW TABLES FROM `{$config['name']}`")->fetchAll(PDO::FETCH_COLUMN);
    echo '<div class="step success">✅ Step 5: ' . count($tables) . ' tables created — ' . implode(', ', $tables) . '</div>';

    // Step 6: Verify seed data
    $userCount = $pdo->query("SELECT COUNT(*) FROM `{$config['name']}`.`users`")->fetchColumn();
    $itemCount = $pdo->query("SELECT COUNT(*) FROM `{$config['name']}`.`inventory_items`")->fetchColumn();
    echo '<div class="step success">✅ Step 6: Seed data loaded — ' . $userCount . ' users, ' . $itemCount . ' inventory items</div>';

    echo '
    <div class="step info mt-3">
        <div class="fw-bold mb-2">🎉 Installation Complete!</div>
        <div class="small">
            <div>🔗 <b>System URL:</b> <a href="http://localhost/7nvent/public/" class="text-info">http://localhost/7nvent/public/</a></div>
            <div>👤 <b>Login:</b> elizabeth.lee / password</div>
            <div class="mt-2 text-warning">⚠️ Please DELETE this setup.php file after installation for security!</div>
        </div>
    </div>
    <a href="http://localhost/7nvent/public/login" class="btn btn-setup mt-3">Go to Login Page →</a>
    ';

} else {
    // Show installation form
?>
    <h5 class="mb-3">Database Configuration</h5>
    <p class="text-secondary small">Enter your MySQL database information to start the 7NVENT installation.</p>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small text-secondary">Database Host</label>
            <input type="text" name="host" class="form-control bg-dark text-white border-secondary" value="localhost">
        </div>
        <div class="mb-3">
            <label class="form-label small text-secondary">MySQL Username</label>
            <input type="text" name="user" class="form-control bg-dark text-white border-secondary" value="root">
        </div>
        <div class="mb-3">
            <label class="form-label small text-secondary">MySQL Password</label>
            <input type="password" name="pass" class="form-control bg-dark text-white border-secondary" placeholder="Kosong = tiada password">
        </div>
        <div class="mb-4">
            <label class="form-label small text-secondary">Database Name</label>
            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="7nvent">
            <div class="text-secondary mt-1" style="font-size:11px">Database will be created automatically if it does not exist.</div>
        </div>

        <div class="step info mb-4">
            <div class="small">
                <div class="fw-bold mb-1">📋 What will happen:</div>
                <div>1. Connect to MySQL server</div>
                <div>2. Create database <code>7nvent</code></div>
                <div>3. Import schema (10 tables)</div>
                <div>4. Load demo seed data (6 users, 15 inventory items, 7 alerts...)</div>
                <div>5. Update config.php automatically</div>
            </div>
        </div>

        <button type="submit" name="install" class="btn btn-setup w-100">
            🚀 Install 7NVENT
        </button>
    </form>

    <div class="text-center mt-4 text-secondary" style="font-size:11px">
        CSC2854 FYP | BCS2402-042 | KPM Beranang | Session 1 2026/2027
    </div>
<?php } ?>
</div>
</body>
</html>
