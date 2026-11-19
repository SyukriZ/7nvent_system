<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — 7NVENT Hotel Inventory System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/hospitality.css">
</head>
<body class="hospitality-public">
<main class="container py-4 py-lg-5">
    <a href="<?= APP_URL ?>" class="brand-wordmark login-brand d-inline-block fs-4 mb-4"><span>7</span>NVENT</a>
    <div class="login-panel row g-0">
        <div class="col-lg-5 public-visual d-none d-lg-flex">
            <dl class="login-stats">
                <div><dt>Units in stock</dt><dd><?= number_format($liveStats['total_items']) ?></dd></div>
                <div><dt>Critical alerts</dt><dd><?= number_format($liveStats['critical_alerts']) ?></dd></div>
                <div><dt>Pending order value</dt><dd>RM <?= number_format($liveStats['pending_value'], 2) ?></dd></div>
            </dl>
        </div>
        <div class="col-lg-7 login-form">
            <p class="public-kicker">Hotel operations workspace</p>
            <h1 class="login-title mb-3">Sign in to 7NVENT</h1>
            <p class="public-copy mb-4">Use your assigned account to access hotel inventory.</p>
            <?php if ($flash = flash()): ?>
            <div role="alert" class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-danger' ?>">
                <div>
                    <?= htmlspecialchars($flash['message']) ?>
                    <?php if (isset($_SESSION['attempts_remaining']) && $_SESSION['attempts_remaining'] > 0): ?>
                        <br><strong><?= $_SESSION['attempts_remaining'] ?> attempt(s) remaining before lockout.</strong>
                        <?php unset($_SESSION['attempts_remaining']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['lockout_remaining'])): ?>
                        <br><strong>Account locked. Try again in <?= $_SESSION['lockout_remaining'] ?> minute(s).</strong>
                        <?php unset($_SESSION['lockout_remaining']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/login">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="usernameField">Username</label>
                    <input class="form-control" id="usernameField" type="text" name="username" autocomplete="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="passwordField">Password</label>
                    <div class="input-group">
                        <input class="form-control" id="passwordField" type="password" name="password" autocomplete="current-password" required>
                        <button type="button" class="btn btn-outline-secondary" id="passwordToggle" aria-controls="passwordField" aria-label="Show password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-4 form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-hospitality w-100 py-2">Sign in</button>
            </form>
            <details class="mt-4">
                <summary class="fw-semibold">Demo accounts</summary>
                <div class="d-grid gap-2 mt-2">
                    <?php
                    $roles = [
                        ['name'=>'Inventory Manager',   'user'=>'elizabeth.lee','pass'=>'Admin@7nvent'],
                        ['name'=>'Housekeeping Manager','user'=>'alvin.yuan',   'pass'=>'House@7nvent'],
                        ['name'=>'Procurement Officer', 'user'=>'sarah.qinn',   'pass'=>'PO@7nvent123'],
                        ['name'=>'IT Administrator',    'user'=>'abdul.hakim',  'pass'=>'ITadmin@7nvent'],
                        ['name'=>'Hotel GM',            'user'=>'farah.nabilah','pass'=>'GM@7nvent2026'],
                        ['name'=>'Supervisor',          'user'=>'melissa.yee',  'pass'=>'Super@7nvent'],
                    ];
                    foreach ($roles as $role): ?>
                        <button type="button" class="btn btn-outline-secondary text-start demo-account"
                                data-username="<?= htmlspecialchars($role['user']) ?>"
                                data-password="<?= htmlspecialchars($role['pass']) ?>">
                            <?= htmlspecialchars($role['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    </div>
</main>
<script>
const password = document.getElementById('passwordField');
const passwordToggle = document.getElementById('passwordToggle');
passwordToggle.addEventListener('click', () => {
    const show = password.type === 'password';
    password.type = show ? 'text' : 'password';
    passwordToggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    passwordToggle.firstElementChild.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});

const username = document.getElementById('usernameField');
document.querySelectorAll('.demo-account').forEach(button => {
    button.addEventListener('click', () => {
        username.value = button.dataset.username;
        password.value = button.dataset.password;
        button.closest('details').open = false;
        username.focus();
    });
});
</script>
</body>
</html>
