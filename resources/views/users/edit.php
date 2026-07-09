<?php $pageTitle = 'Edit User'; ob_start();

$roleConfig = [
    'Inventory Manager'    => ['color'=>'#0096FF','bg'=>'#dbeafe','emoji'=>'<i class="ph ph-gear"></i>','dept'=>'Operations'],
    'Housekeeping Manager' => ['color'=>'#f59e0b','bg'=>'#fef9c3','emoji'=>'<i class="ph ph-house"></i>','dept'=>'Housekeeping'],
    'Procurement Officer'  => ['color'=>'#8b5cf6','bg'=>'#ede9fe','emoji'=>'<i class="ph ph-shopping-cart"></i>','dept'=>'Procurement'],
    'IT Administrator'     => ['color'=>'#ef4444','bg'=>'#fee2e2','emoji'=>'<i class="ph ph-desktop"></i>','dept'=>'IT'],
    'Hotel GM'             => ['color'=>'#22c55e','bg'=>'#dcfce7','emoji'=>'<i class="ph ph-buildings"></i>','dept'=>'Executive'],
    'Supervisor'           => ['color'=>'#f97316','bg'=>'#ffedd5','emoji'=>'<i class="ph ph-eye"></i>','dept'=>'Operations'],
];
$currentRole = $editUser['role_name'] ?? 'Inventory Manager';
$rc = $roleConfig[$currentRole] ?? ['color'=>'#0096FF','bg'=>'#dbeafe','emoji'=>'<i class="ph ph-user"></i>','dept'=>''];
$initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $editUser['full_name']), 0, 2))));
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
/* Entrance */
.eu-card { opacity:0; transform:translateY(20px); animation:euUp 0.55s cubic-bezier(0.23,1,0.32,1) forwards; }
@keyframes euUp { to { opacity:1; transform:translateY(0); } }
.eu-step { opacity:0; transform:translateY(12px); animation:euUp 0.42s ease forwards; }

/* Floating header */
.eu-avatar {
    width:68px; height:68px; border-radius:50%;
    background:var(--ac); color:#fff;
    font-size:24px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    position:relative; flex-shrink:0;
    animation:avatarSpin 0.6s cubic-bezier(0.34,1.56,0.64,1) forwards;
    box-shadow:0 8px 24px rgba(0,0,0,0.2);
}
@keyframes avatarSpin { from{transform:scale(0) rotate(-180deg)} to{transform:scale(1) rotate(0)} }
.eu-avatar::after {
    content:''; position:absolute; inset:-4px; border-radius:50%;
    border:2px solid var(--ac); opacity:0.4;
    animation:avatarRing 2.5s ease-in-out infinite;
}
@keyframes avatarRing { 0%,100%{transform:scale(1);opacity:0.4} 50%{transform:scale(1.08);opacity:0.7} }

/* Badge emoji float */
.role-emoji {
    font-size:32px;
    animation:emojiBounce 2s ease-in-out infinite;
    display:inline-block;
}
@keyframes emojiBounce { 0%,100%{transform:translateY(0) rotate(-5deg)} 50%{transform:translateY(-8px) rotate(5deg)} }

/* Inputs */
.eu-input {
    font-size:15px !important; padding:12px 14px !important;
    border:2px solid #e2e8f0 !important; border-radius:10px !important;
    transition:all 0.2s !important; background:#fafbfc !important;
}
.eu-input:focus {
    border-color:#0096FF !important;
    box-shadow:0 0 0 4px rgba(0,150,255,0.08) !important;
    background:#fff !important;
}
.eu-readonly {
    font-size:15px; padding:12px 14px;
    background:#f1f5f9 !important; border-radius:10px;
    border:2px solid #e2e8f0; color:#64748b;
}

/* Role cards */
.role-cards-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.role-card {
    border:2px solid #e2e8f0; border-radius:12px; padding:12px 8px;
    text-align:center; cursor:pointer; background:#fafbfc;
    transition:all 0.2s cubic-bezier(0.23,1,0.32,1);
}
.role-card:hover { border-color:#0096FF44; transform:translateY(-2px); }
.role-card.selected { border-color:var(--rc); background:var(--rb); transform:translateY(-2px); box-shadow:0 0 0 3px rgba(0,150,255,0.08); }
.role-card .rc-e { font-size:20px; display:block; margin-bottom:3px; transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.role-card.selected .rc-e { transform:scale(1.3) rotate(-5deg); }
.role-card .rc-n { font-size:10px; font-weight:700; color:#475569; }
.role-card.selected .rc-n { color:var(--rc); }

/* Status toggle */
.status-toggle { display:flex; gap:10px; }
.status-btn {
    flex:1; padding:12px; border-radius:12px; border:2px solid #e2e8f0;
    font-size:14px; font-weight:700; cursor:pointer; text-align:center;
    transition:all 0.2s ease; background:#fafbfc; color:#64748b;
}
.status-btn.active-sel { border-color:#22c55e; background:#dcfce7; color:#16a34a; box-shadow:0 0 0 3px rgba(34,197,94,0.1); }
.status-btn.inactive-sel { border-color:#ef4444; background:#fee2e2; color:#dc2626; box-shadow:0 0 0 3px rgba(239,68,68,0.1); }

/* Section divider */
.sec-div { display:flex; align-items:center; gap:10px; margin:22px 0 14px; color:#94a3b8; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
.sec-div::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.field-lbl { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.7px; margin-bottom:6px; display:block; }

/* Update button */
.btn-update-user {
    background:linear-gradient(135deg,#0096FF,#8b5cf6);
    background-size:200%; animation:btnShift 3s ease infinite;
    border:none; color:#fff; font-size:16px; font-weight:700;
    padding:13px 40px; border-radius:12px;
    box-shadow:0 4px 16px rgba(0,150,255,0.35); transition:all 0.22s ease; cursor:pointer;
}
.btn-update-user:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,150,255,0.5); color:#fff; }
@keyframes btnShift { 0%,100%{background-position:0%} 50%{background-position:100%} }

/* Activity card */
.activity-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
.activity-row:last-child { border-bottom:none; }
</style>

<div class="row g-4">

    <!-- LEFT: FORM -->
    <div class="col-md-8">
        <div class="stat-card eu-card">

            <!-- Header with animated avatar -->
            <div class="d-flex align-items-center gap-4 mb-4 p-3 rounded-3" style="background:linear-gradient(135deg,<?= $rc['bg'] ?>,#f8fafc);border:1px solid <?= $rc['color'] ?>22">
                <div class="eu-avatar" style="--ac:<?= $rc['color'] ?>"><?= $initials ?></div>
                <div style="flex:1">
                    <div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Editing User</div>
                    <h5 class="mb-1 fw-bold" style="color:#1e293b"><?= clean($editUser['full_name']) ?></h5>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:13px;color:<?= $rc['color'] ?>;font-weight:700"><?= $rc['emoji'] ?> <?= clean($currentRole) ?></span>
                        <span style="font-size:12px;color:#94a3b8">· #<?= str_pad($editUser['user_id'],4,'0',STR_PAD_LEFT) ?></span>
                        <span style="padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;
                              background:<?= $editUser['status']==='Active'?'#dcfce7':'#fee2e2' ?>;
                              color:<?= $editUser['status']==='Active'?'#16a34a':'#dc2626' ?>">
                            <?= $editUser['status'] ?>
                        </span>
                    </div>
                </div>
                <div class="role-emoji"><?= $rc['emoji'] ?></div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/users/update">
                <input type="hidden" name="user_id" value="<?= $editUser['user_id'] ?>">
                <input type="hidden" name="role_id" id="roleInput" value="<?= $editUser['role_id'] ?>">
                <input type="hidden" name="status"  id="statusInput" value="<?= $editUser['status'] ?>">

                <!-- Basic Info (read-only) -->
                <div class="sec-div eu-step" style="animation-delay:0.1s"><span><i class="ph ph-user" style="margin-right:6px"></i>User Information</span></div>
                <div class="row g-3 eu-step" style="animation-delay:0.12s">
                    <div class="col-md-6">
                        <label class="field-lbl">Full Name</label>
                        <div class="eu-readonly d-flex align-items-center gap-2">
                            <i class="ph ph-user" style="color:#94a3b8;font-size:18px"></i>
                            <?= clean($editUser['full_name']) ?>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:4px">Contact IT Admin to change name</div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl">Email Address</label>
                        <input type="email" name="email" class="form-control eu-input"
                               value="<?= clean($editUser['email']) ?>">
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="sec-div eu-step" style="animation-delay:0.18s"><span><i class="ph ph-shield" style="margin-right:6px"></i>Role Assignment</span></div>
                <div class="eu-step" style="animation-delay:0.2s">
                    <div class="role-cards-grid mb-2">
                        <?php foreach ($roles as $r):
                            $cfg = $roleConfig[$r['role_name']] ?? ['color'=>'#888','bg'=>'#f1f5f9','emoji'=>'<i class="ph ph-user"></i>','dept'=>''];
                            $isSel = $editUser['role_id'] == $r['role_id'];
                        ?>
                        <div class="role-card <?= $isSel?'selected':'' ?>"
                             style="--rc:<?= $cfg['color'] ?>;--rb:<?= $cfg['bg'] ?>"
                             onclick="selectRole(<?= $r['role_id'] ?>,'<?= addslashes($r['role_name']) ?>','<?= $cfg['color'] ?>','<?= $cfg['bg'] ?>','<?= htmlspecialchars($cfg['emoji']) ?>','<?= $cfg['dept'] ?>')">
                            <span class="rc-e"><?= $cfg['emoji'] ?></span>
                            <div class="rc-n"><?= clean($r['role_name']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Department -->
                <div class="row g-3 eu-step mt-1" style="animation-delay:0.26s">
                    <div class="col-md-6">
                        <label class="field-lbl">Department</label>
                        <input type="text" name="department" id="deptInput" class="form-control eu-input"
                               value="<?= clean($editUser['department'] ?? '') ?>"
                               placeholder="e.g. Operations, IT, Procurement">
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl">Account Status</label>
                        <div class="status-toggle">
                            <div class="status-btn <?= $editUser['status']==='Active'?'active-sel':'' ?>"
                                 id="btnActive" onclick="setStatus('Active')">
                                <i class="ph ph-check-circle" style="margin-right:4px"></i>Active
                            </div>
                            <div class="status-btn <?= $editUser['status']==='Inactive'?'inactive-sel':'' ?>"
                                 id="btnInactive" onclick="setStatus('Inactive')">
                                <i class="ph ph-x-circle" style="margin-right:4px"></i>Inactive
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-3 eu-step" style="animation-delay:0.34s">
                    <button type="submit" class="btn-update-user">
                        <i class="ph ph-floppy-disk me-2"></i>Update User
                    </button>
                    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary px-4"
                       style="padding:12px 24px;font-size:15px;border-radius:12px">
                        <i class="ph ph-x-circle me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- RIGHT: INFO PANEL -->
    <div class="col-md-4">

        <!-- Account Details -->
        <div class="stat-card eu-card mb-4" style="animation-delay:0.12s">
            <div class="fw-bold mb-3" style="font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">
                <i class="ph ph-clipboard-text" style="margin-right:6px"></i>Account Details
            </div>
            <div class="activity-row">
                <i class="ph ph-identification-card" style="font-size:20px;color:#94a3b8"></i>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:700">USER ID</div>
                    <div style="font-size:14px;font-weight:700">#<?= str_pad($editUser['user_id'],4,'0',STR_PAD_LEFT) ?></div>
                </div>
            </div>
            <div class="activity-row">
                <i class="ph ph-at" style="font-size:20px;color:#94a3b8"></i>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:700">USERNAME</div>
                    <div style="font-size:14px;font-weight:700;font-family:monospace"><?= clean($editUser['username'] ?? '—') ?></div>
                </div>
            </div>
            <div class="activity-row">
                <i class="ph ph-clock" style="font-size:20px;color:#94a3b8"></i>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:700">LAST LOGIN</div>
                    <div style="font-size:14px;font-weight:700;color:<?= $editUser['last_login']?'#22c55e':'#94a3b8' ?>">
                        <?= $editUser['last_login'] ? date('d M Y, H:i', strtotime($editUser['last_login'])) : 'Never logged in' ?>
                    </div>
                </div>
            </div>
            <div class="activity-row">
                <i class="ph ph-calendar" style="font-size:20px;color:#94a3b8"></i>
                <div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:700">ACCOUNT CREATED</div>
                    <div style="font-size:14px;font-weight:700"><?= $editUser['created_at'] ? date('d M Y', strtotime($editUser['created_at'])) : '—' ?></div>
                </div>
            </div>
        </div>

        <!-- Access Level Info -->
        <div class="stat-card eu-card" style="animation-delay:0.26s">
            <div class="fw-bold mb-3" style="font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px">
                <i class="ph ph-shield" style="margin-right:6px"></i>Access Level Info
            </div>
            <div id="accessPanel" style="text-align:center;padding:16px;background:<?= $rc['bg'] ?>;border-radius:12px;border:1px solid <?= $rc['color'] ?>33">
                <div style="font-size:32px;margin-bottom:8px;animation:emojiBounce 2.5s ease-in-out infinite"><?= $rc['emoji'] ?></div>
                <div style="font-size:14px;font-weight:700;color:<?= $rc['color'] ?>" id="accessRoleName"><?= clean($currentRole) ?></div>
                <div style="font-size:11px;color:#64748b;margin-top:4px" id="accessLevelName"><?= clean($editUser['access_level'] ?? '—') ?></div>
            </div>
            <div class="mt-3 p-2 rounded" style="background:#f8fafc;font-size:12px;color:#64748b">
                <i class="ph ph-lightbulb" style="margin-right:4px"></i>Changing role will update the user's system access immediately after saving.
            </div>
        </div>
    </div>
</div>

<script>
function selectRole(id, name, color, bg, emoji, dept) {
    document.getElementById('roleInput').value = id;
    if (!document.querySelector('[name="department"]').value || document.querySelector('[name="department"]').dataset.auto) {
        document.getElementById('deptInput').value = dept;
        document.getElementById('deptInput').dataset.auto = '1';
    }
    // Update access panel
    const panel = document.getElementById('accessPanel');
    panel.style.background = bg;
    panel.style.borderColor = color + '33';
    document.getElementById('accessRoleName').style.color = color;
    document.getElementById('accessRoleName').textContent = name;
    document.getElementById('accessLevelName').innerHTML = emoji + ' ' + name;

    // Highlight selected card
    document.querySelectorAll('.role-card').forEach(c => {
        c.classList.remove('selected');
        c.style.removeProperty('--rc');
        c.style.removeProperty('--rb');
    });
    event.currentTarget.classList.add('selected');
    event.currentTarget.style.setProperty('--rc', color);
    event.currentTarget.style.setProperty('--rb', bg);
}

function setStatus(val) {
    document.getElementById('statusInput').value = val;
    const a = document.getElementById('btnActive');
    const b = document.getElementById('btnInactive');
    if (val === 'Active') {
        a.className = 'status-btn active-sel';
        b.className = 'status-btn';
        // Bounce animation
        a.style.animation = 'none';
        setTimeout(() => a.style.animation = '', 10);
    } else {
        b.className = 'status-btn inactive-sel';
        a.className = 'status-btn';
    }
}
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>