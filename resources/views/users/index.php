<?php $pageTitle = 'Users & Roles'; ob_start();

$roleConfig = [
    'Inventory Manager'    => ['color'=>'#0096FF','bg'=>'#dbeafe','icon'=>'ph ph-user-gear','dept_color'=>'#1d4ed8'],
    'Housekeeping Manager' => ['color'=>'#f59e0b','bg'=>'#fef9c3','icon'=>'ph ph-house',       'dept_color'=>'#b45309'],
    'Procurement Officer'  => ['color'=>'#8b5cf6','bg'=>'#ede9fe','icon'=>'ph ph-shopping-cart',        'dept_color'=>'#6d28d9'],
    'IT Administrator'     => ['color'=>'#ef4444','bg'=>'#fee2e2','icon'=>'ph ph-cpu',         'dept_color'=>'#b91c1c'],
    'Hotel GM'             => ['color'=>'#22c55e','bg'=>'#dcfce7','icon'=>'ph ph-buildings',    'dept_color'=>'#15803d'],
    'Supervisor'           => ['color'=>'#f97316','bg'=>'#ffedd5','icon'=>'ph ph-eye',         'dept_color'=>'#c2410c'],
];
$totalActive = count(array_filter($users, fn($u) => $u['status'] === 'Active'));
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
/* Page entrance */
.ur-card { opacity:0; transform:translateY(16px); animation:urUp 0.45s cubic-bezier(0.23,1,0.32,1) forwards; will-change:transform; }
@keyframes urUp { to { opacity:1; transform:translateY(0); } }

/* KPI strip */
.ur-kpi {
    position:relative; overflow:hidden; isolation:isolate;
    border-radius:14px; padding:18px 20px;
    display:flex; align-items:center; gap:16px;
    background:var(--glass-bg);
    backdrop-filter:blur(16px) saturate(180%);
    -webkit-backdrop-filter:blur(16px) saturate(180%);
    border:1px solid var(--glass-border);
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 20px var(--glass-shadow);
    transition:transform 0.2s ease, box-shadow 0.2s ease, background-color 0.25s ease;
}
.ur-kpi:hover { transform:translateY(-3px); }
.ur-kpi::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.5; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.ur-kpi::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.ur-kpi:hover::after { opacity:.6; }
@media (prefers-reduced-motion: reduce) { .ur-kpi::before { animation: none; } }
.ur-kpi-icon { width:50px; height:50px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; animation:iconBob 3s ease-in-out infinite; flex-shrink:0; }
@keyframes iconBob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
.ur-kpi-num { font-size:32px; font-weight:900; line-height:1; }
.ur-kpi-lbl { font-size:11px; color:var(--text-faint); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }

/* User cards grid */
.user-card {
    background:var(--glass-bg);
    backdrop-filter:blur(18px) saturate(180%);
    -webkit-backdrop-filter:blur(18px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:14px; padding:20px;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
    transition:all 0.22s cubic-bezier(0.23,1,0.32,1), background-color 0.25s ease;
    border-left:4px solid var(--uc);
    position:relative; overflow:hidden; isolation:isolate;
    will-change:transform;
}
.user-card:hover { transform:translateY(-4px) translateX(2px); }
.user-card::before {
    content:''; position:absolute; top:0; right:0;
    width:80px; height:80px; border-radius:50%;
    background:var(--uc); opacity:0.04;
    transform:translate(20px,-20px);
    z-index:-1;
}
.user-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.user-card:hover::after { opacity:.6; }

/* Avatar */
.user-avatar {
    width:52px; height:52px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:800; color:#fff;
    flex-shrink:0; position:relative;
}
.user-avatar::after {
    content:''; position:absolute; inset:-2px;
    border-radius:50%; border:2px solid var(--uc);
    opacity:0.4; animation:avatarRing 3s ease-in-out infinite;
}
@keyframes avatarRing { 0%,100%{opacity:0.4;transform:scale(1)} 50%{opacity:0.7;transform:scale(1.05)} }

/* Status dot */
.status-dot {
    width:8px; height:8px; border-radius:50%;
    display:inline-block; margin-right:5px;
}
.status-dot.active { background:#22c55e; animation:dotPulse 2s ease-in-out infinite; }
@keyframes dotPulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
.status-dot.inactive { background:var(--text-faint); }

/* Role badge */
.role-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 10px; border-radius:20px;
    font-size:11px; font-weight:700;
}

/* Access chip */
.access-chip {
    background:var(--border-subtle); color:var(--text-secondary);
    border-radius:6px; padding:3px 10px;
    font-size:11px; font-weight:600;
}

/* Last login */
.login-now { color:#22c55e; font-weight:700; }
.login-recent { color:#0096FF; }
.login-old { color:var(--text-faint); }

/* Edit button */
.btn-edit {
    background:var(--uc); color:#fff; border:none;
    border-radius:10px; padding:8px 18px;
    font-size:13px; font-weight:600;
    transition:all 0.18s cubic-bezier(0.23,1,0.32,1);
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
}
.btn-edit:hover { color:#fff; filter:brightness(1.15); transform:translateX(2px); }

/* Section label */
.sec-label {
    font-size:11px; font-weight:700; color:var(--text-faint);
    text-transform:uppercase; letter-spacing:0.8px;
    margin-bottom:3px; display:block;
}
</style>

<!-- HEADER + ADD BUTTON -->
<div class="d-flex justify-content-between align-items-center mb-4 ur-card">
    <div>
        <h5 class="mb-0 fw-bold">Users & Roles Management</h5>
        <div style="font-size:13px;color:var(--text-faint)"><?= count($users) ?> registered users · <?= $totalActive ?> active</div>
    </div>
    <?php if (Auth::hasRole('Inventory Manager','IT Administrator')): ?>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-primary px-4"
       style="border-radius:12px;font-size:14px;font-weight:600;padding:10px 22px">
        <i class="ph ph-user-plus me-2"></i>Add User
    </a>
    <?php endif; ?>
</div>

<!-- KPI STRIP -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="ur-kpi ur-card" style="animation-delay:0.06s">
            <div class="ur-kpi-icon" style="background:#dbeafe"><i class="ph ph-users" style="color:#1d4ed8;font-size:24px"></i></div>
            <div>
                <div class="ur-kpi-num text-primary" id="kpiUsers" data-target="<?= count($users) ?>">0</div>
                <div class="ur-kpi-lbl">Total Users</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ur-kpi ur-card" style="animation-delay:0.12s">
            <div class="ur-kpi-icon" style="background:#dcfce7"><i class="ph ph-check-circle" style="color:#15803d;font-size:24px"></i></div>
            <div>
                <div class="ur-kpi-num" style="color:#22c55e" id="kpiActive" data-target="<?= $totalActive ?>">0</div>
                <div class="ur-kpi-lbl">Active Users</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ur-kpi ur-card" style="animation-delay:0.18s">
            <div class="ur-kpi-icon" style="background:#ede9fe"><i class="ph ph-shield-check" style="color:#6d28d9;font-size:24px"></i></div>
            <div>
                <div class="ur-kpi-num" style="color:#8b5cf6" id="kpiRoles" data-target="<?= count($roleConfig) ?>">0</div>
                <div class="ur-kpi-lbl">Total Roles</div>
            </div>
        </div>
    </div>
</div>

<!-- USER CARDS GRID -->
<div class="row g-3">
    <?php foreach ($users as $i => $u):
        $rc  = $roleConfig[$u['role_name']] ?? ['color'=>'var(--text-muted)','bg'=>'var(--border-subtle)','icon'=>'ph ph-user','dept_color'=>'var(--text-muted)'];
        $ini = strtoupper(substr($u['full_name'], 0, 2));
        $lastLogin = $u['last_login']
            ? (date('Y-m-d', strtotime($u['last_login'])) === date('Y-m-d')
                ? '<span class="login-now"><i class="ph ph-circle" style="color:#22c55e;font-size:10px;vertical-align:middle;margin-right:4px"></i>Today</span>'
                : '<span class="login-recent">'.date('d M', strtotime($u['last_login'])).'</span>')
            : '<span class="login-old">Never</span>';
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="user-card ur-card" style="--uc:<?= $rc['color'] ?>;animation-delay:<?= 0.24 + $i*0.07 ?>s">

            <!-- Avatar + Name -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="user-avatar" style="--uc:<?= $rc['color'] ?>;background:<?= $rc['color'] ?>">
                    <?= $ini ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:16px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= clean($u['full_name']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-faint)"><?= clean($u['email'] ?? '') ?></div>
                </div>
                <!-- Status -->
                <div>
                    <span class="status-dot <?= $u['status']==='Active' ? 'active' : 'inactive' ?>"></span>
                    <span style="font-size:11px;font-weight:700;color:<?= $u['status']==='Active' ? '#22c55e' : 'var(--text-faint)' ?>">
                        <?= $u['status'] ?>
                    </span>
                </div>
            </div>

            <!-- Role badge -->
            <div class="mb-3">
                <span class="role-badge" style="background:<?= $rc['bg'] ?>;color:<?= $rc['color'] ?>">
                    <i class="<?= $rc['icon'] ?>"></i>
                    <?= clean($u['role_name']) ?>
                </span>
            </div>

            <!-- Info grid -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <span class="sec-label">Department</span>
                    <div style="font-size:13px;font-weight:600;color:<?= $rc['dept_color'] ?>"><?= clean($u['department'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <span class="sec-label">Access Level</span>
                    <span class="access-chip"><?= clean($u['access_level']) ?></span>
                </div>
                <div class="col-6">
                    <span class="sec-label">Last Login</span>
                    <div style="font-size:13px"><?= $lastLogin ?></div>
                </div>
                <div class="col-6">
                    <span class="sec-label">User ID</span>
                    <div style="font-size:13px;color:var(--text-faint)">#<?= str_pad($u['user_id'], 4, '0', STR_PAD_LEFT) ?></div>
                </div>
            </div>

            <!-- Edit button -->
            <div class="d-flex justify-content-end">
                <a href="<?= APP_URL ?>/users/edit?id=<?= $u['user_id'] ?>"
                   class="btn-edit" style="--uc:<?= $rc['color'] ?>">
                    <i class="ph ph-pencil-simple"></i> Edit User
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
window.addEventListener('load', function() {
    [['kpiUsers',900],['kpiActive',700],['kpiRoles',500]].forEach(([id,dur]) => {
        const el = document.getElementById(id);
        if (!el) return;
        const target = parseInt(el.dataset.target) || 0;
        const start  = performance.now();
        (function tick(now) {
            const p = Math.min((now-start)/dur, 1), ease = 1-Math.pow(1-p,3);
            el.textContent = Math.floor(ease * target);
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target;
        })(start);
    });
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>