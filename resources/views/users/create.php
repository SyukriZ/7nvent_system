<?php $pageTitle = 'Add New User'; ob_start();
$roleConfig = [
    'Inventory Manager'    => ['color'=>'#0096FF','bg'=>'#dbeafe','icon'=>'ph ph-user-gear','emoji'=>'<i class="ph ph-gear"></i>','dept'=>'Operations'],
    'Housekeeping Manager' => ['color'=>'#f59e0b','bg'=>'#fef9c3','icon'=>'ph ph-house',       'emoji'=>'<i class="ph ph-house"></i>','dept'=>'Housekeeping'],
    'Procurement Officer'  => ['color'=>'#8b5cf6','bg'=>'#ede9fe','icon'=>'ph ph-shopping-cart',        'emoji'=>'<i class="ph ph-shopping-cart"></i>','dept'=>'Procurement'],
    'IT Administrator'     => ['color'=>'#ef4444','bg'=>'#fee2e2','icon'=>'ph ph-cpu',         'emoji'=>'<i class="ph ph-desktop"></i>','dept'=>'IT'],
    'Hotel GM'             => ['color'=>'#22c55e','bg'=>'#dcfce7','icon'=>'ph ph-buildings',    'emoji'=>'<i class="ph ph-buildings"></i>','dept'=>'Executive'],
    'Supervisor'           => ['color'=>'#f97316','bg'=>'#ffedd5','icon'=>'ph ph-eye',         'emoji'=>'<i class="ph ph-eye"></i>','dept'=>'Operations'],
];
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
.uc-card { opacity:0; transform:translateY(20px); animation:ucUp 0.55s cubic-bezier(0.23,1,0.32,1) forwards; }
@keyframes ucUp { to { opacity:1; transform:translateY(0); } }
.uc-step { opacity:0; transform:translateY(12px); animation:ucUp 0.42s ease forwards; }

/* Floating header */
.uc-header-icon {
    width:60px; height:60px; border-radius:18px;
    background:linear-gradient(135deg,#0096FF,#8b5cf6);
    display:flex; align-items:center; justify-content:center;
    font-size:26px; box-shadow:0 8px 24px rgba(0,150,255,0.3);
    animation:iconFloat 3s ease-in-out infinite; flex-shrink:0;
}
@keyframes iconFloat { 0%,100%{transform:translateY(0) rotate(-3deg)} 50%{transform:translateY(-8px) rotate(3deg)} }

/* Inputs */
.uc-input {
    font-size:15px !important; padding:12px 14px !important;
    border:2px solid #e2e8f0 !important; border-radius:10px !important;
    transition:all 0.2s !important; background:#fafbfc !important;
}
.uc-input:focus {
    border-color:#0096FF !important;
    box-shadow:0 0 0 4px rgba(0,150,255,0.08) !important;
    background:#fff !important;
}
.uc-input::placeholder { color:#c0c8d4; }

/* Role selector cards */
.role-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.role-card {
    border:2px solid #e2e8f0; border-radius:12px; padding:12px 8px;
    text-align:center; cursor:pointer; background:#fafbfc;
    transition:all 0.2s cubic-bezier(0.23,1,0.32,1);
    position:relative;
}
.role-card:hover { border-color:#0096FF55; transform:translateY(-2px); }
.role-card.selected { border-color:var(--rc); background:var(--rb); box-shadow:0 0 0 3px rgba(0,150,255,0.1); transform:translateY(-2px); }
.role-card .rc-emoji { font-size:22px; margin-bottom:4px; display:block; transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.role-card.selected .rc-emoji { transform:scale(1.3) rotate(-5deg); }
.role-card .rc-name { font-size:10px; font-weight:700; color:#475569; }
.role-card.selected .rc-name { color:var(--rc); }

/* Section divider */
.sec-div {
    display:flex; align-items:center; gap:10px; margin:22px 0 14px;
    color:#94a3b8; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;
}
.sec-div::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* Field label */
.field-lbl { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.7px; margin-bottom:6px; display:block; }

/* Password strength */
.pwd-bar-wrap { height:4px; background:#e2e8f0; border-radius:2px; margin-top:6px; }
.pwd-bar { height:100%; border-radius:2px; width:0%; transition:width 0.4s ease, background 0.3s; }
.pwd-hint { font-size:10px; color:#94a3b8; margin-top:3px; }

/* Submit button */
.btn-save-user {
    background:linear-gradient(135deg,#0096FF,#8b5cf6);
    background-size:200%; animation:btnShift 3s ease infinite;
    border:none; color:#fff; font-size:16px; font-weight:700;
    padding:13px 40px; border-radius:12px;
    box-shadow:0 4px 16px rgba(0,150,255,0.35); transition:all 0.22s ease; cursor:pointer;
}
.btn-save-user:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,150,255,0.5); color:#fff; }
@keyframes btnShift { 0%,100%{background-position:0%} 50%{background-position:100%} }

/* Avatar preview */
.preview-avatar {
    width:64px; height:64px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; font-weight:800; color:#fff; margin:0 auto 10px;
    transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    position:relative;
}
.preview-avatar::after {
    content:''; position:absolute; inset:-3px; border-radius:50%;
    border:2px solid var(--rc,#0096FF); opacity:0.5;
    animation:ringPulse 2s ease-in-out infinite;
}
@keyframes ringPulse { 0%,100%{opacity:0.5;transform:scale(1)} 50%{opacity:0.8;transform:scale(1.06)} }
</style>

<div class="row g-4">

    <!-- LEFT: FORM -->
    <div class="col-md-8">
        <div class="stat-card uc-card">

            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="uc-header-icon"><i class="ph ph-user-plus" style="color:#fff;font-size:28px"></i></div>
                <div>
                    <h5 class="mb-0 fw-bold">Add New User</h5>
                    <div style="font-size:12px;color:#94a3b8">Create a new staff account for 7NVENT</div>
                </div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/users/store" id="userForm">

                <!-- Personal Info -->
                <div class="sec-div uc-step" style="animation-delay:0.1s"><span><i class="ph ph-user" style="margin-right:6px"></i>Personal Information</span></div>
                <div class="row g-3 uc-step" style="animation-delay:0.12s">
                    <div class="col-md-6">
                        <label class="field-lbl">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control uc-input"
                               placeholder="e.g. Sarah Qinn" required
                               oninput="updatePreview(this.value)">
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="border:2px solid #e2e8f0;border-right:none;border-radius:10px 0 0 10px;background:#f0f9ff;color:#0096FF;font-weight:700"><i class="ph ph-envelope-simple" style="font-size:18px"></i></span>
                            <input type="email" name="email" class="form-control uc-input" required
                                   placeholder="staff@hotel.com.my"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important">
                        </div>
                    </div>
                </div>

                <!-- Login Credentials -->
                <div class="sec-div uc-step" style="animation-delay:0.18s"><span><i class="ph ph-lock-key" style="margin-right:6px"></i>Login Credentials</span></div>
                <div class="row g-3 uc-step" style="animation-delay:0.2s">
                    <div class="col-md-6">
                        <label class="field-lbl">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="border:2px solid #e2e8f0;border-right:none;border-radius:10px 0 0 10px;background:#f0f9ff;color:#0096FF;font-weight:700">@</span>
                            <input type="text" name="username" class="form-control uc-input" required
                                   placeholder="firstname.lastname"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important">
                        </div>
                        <div style="font-size:10px;color:#94a3b8;margin-top:4px">Use format: firstname.lastname</div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-lbl">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwdField" class="form-control uc-input"
                                   placeholder="Min 8 characters"
                                   style="border-right:none!important;border-radius:10px 0 0 10px!important"
                                   oninput="checkPwd(this.value)">
                            <button type="button" onclick="togglePwd()"
                                    style="border:2px solid #e2e8f0;border-left:none;border-radius:0 10px 10px 0;background:#fafbfc;padding:0 12px;cursor:pointer;color:#94a3b8;transition:color 0.2s"
                                    id="eyeBtn" onmouseover="this.style.color='#0096FF'" onmouseout="this.style.color='#94a3b8'">
                                <i class="ph ph-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="pwd-bar-wrap"><div class="pwd-bar" id="pwdBar"></div></div>
                        <div class="pwd-hint" id="pwdHint">Leave blank to use default: <code>password123</code></div>
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="sec-div uc-step" style="animation-delay:0.26s"><span><i class="ph ph-shield" style="margin-right:6px"></i>Role & Department</span></div>
                <div class="uc-step" style="animation-delay:0.28s">
                    <label class="field-lbl mb-2">Select Role <span class="text-danger">*</span></label>
                    <div class="role-cards mb-2">
                        <?php foreach ($roles as $r):
                            $cfg = $roleConfig[$r['role_name']] ?? ['color'=>'#888','bg'=>'#f1f5f9','emoji'=>'<i class="ph ph-user"></i>','dept'=>''];
                        ?>
                        <div class="role-card" style="--rc:<?= $cfg['color'] ?>;--rb:<?= $cfg['bg'] ?>"
                             onclick="selectRole(<?= $r['role_id'] ?>,'<?= addslashes($r['role_name']) ?>','<?= $cfg['color'] ?>','<?= $cfg['bg'] ?>','<?= htmlspecialchars($cfg['emoji']) ?>','<?= $cfg['dept'] ?>')">
                            <span class="rc-emoji"><?= $cfg['emoji'] ?></span>
                            <div class="rc-name"><?= clean($r['role_name']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="role_id" id="roleInput" required>
                    <div id="roleError" style="font-size:12px;color:#ef4444;display:none;margin-top:4px"><i class="ph ph-warning-circle" style="margin-right:4px"></i>Please select a role</div>
                </div>

                <div class="row g-3 uc-step mt-2" style="animation-delay:0.32s">
                    <div class="col-12">
                        <label class="field-lbl">Department</label>
                        <input type="text" name="department" id="deptInput" class="form-control uc-input"
                               placeholder="e.g. Housekeeping, Operations, IT...">
                        <div style="font-size:10px;color:#94a3b8;margin-top:4px">Auto-filled when you select a role above</div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex gap-3 uc-step" style="animation-delay:0.38s">
                    <button type="submit" class="btn-save-user" onclick="return validateForm()">
                        <i class="ph ph-user-plus me-2"></i>Save New User
                    </button>
                    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary px-4"
                       style="padding:12px 24px;font-size:15px;border-radius:12px">
                        <i class="ph ph-x-circle me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- RIGHT: PREVIEW + TIPS -->
    <div class="col-md-4">

        <!-- Live Preview -->
        <div class="stat-card uc-card mb-4" style="animation-delay:0.14s">
            <div class="fw-bold mb-3" style="font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px"><i class="ph ph-eye" style="margin-right:6px"></i>Live Preview</div>
            <div style="text-align:center;padding:16px;background:linear-gradient(135deg,#f0f9ff,#f5f3ff);border-radius:12px;border:1px solid #e2e8f0">
                <div class="preview-avatar" id="previewAvatar" style="--rc:#0096FF;background:#0096FF">??</div>
                <div style="font-size:16px;font-weight:700;color:#1e293b" id="previewName">New User</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px" id="previewRole">Role not selected</div>
                <div id="previewRoleBadge" style="display:inline-block;margin-top:8px;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;background:#f1f5f9;color:#64748b">
                    No Role
                </div>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="stat-card uc-card" style="animation-delay:0.28s">
            <div class="fw-bold mb-3" style="font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px"><i class="ph ph-lightbulb" style="margin-right:6px"></i>Security Guide</div>
            <div style="display:flex;gap:10px;padding:10px;border-radius:10px;margin-bottom:8px;background:#eff6ff">
                <i class="ph ph-lock-key" style="font-size:18px;color:#1d4ed8"></i>
                <div style="font-size:12px;color:#1d4ed8"><strong>Password</strong> — Minimum 8 characters. User should change on first login</div>
            </div>
            <div style="display:flex;gap:10px;padding:10px;border-radius:10px;margin-bottom:8px;background:#f0fdf4">
                <i class="ph ph-shield" style="font-size:18px;color:#166534"></i>
                <div style="font-size:12px;color:#166534"><strong>Role</strong> — Each role has specific access. Assign carefully</div>
            </div>
            <div style="display:flex;gap:10px;padding:10px;border-radius:10px;background:#fdf4ff">
                <i class="ph ph-at" style="font-size:18px;color:#6b21a8"></i>
                <div style="font-size:12px;color:#6b21a8"><strong>Username</strong> — Must be unique. Format: firstname.lastname</div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePreview(name) {
    const ini = name.trim().split(' ').map(w=>w[0]||'').slice(0,2).join('').toUpperCase() || '??';
    document.getElementById('previewAvatar').textContent = ini;
    document.getElementById('previewName').textContent   = name || 'New User';
}

let selColor = '#0096FF', selBg = '#dbeafe';
function selectRole(id, name, color, bg, emoji, dept) {
    selColor = color; selBg = bg;
    document.getElementById('roleInput').value = id;
    document.getElementById('roleError').style.display = 'none';
    document.getElementById('deptInput').value = dept;
    document.getElementById('previewRole').textContent = name;
    const badge = document.getElementById('previewRoleBadge');
    badge.innerHTML = emoji + ' ' + name;
    badge.style.background = bg; badge.style.color = color;
    const avatar = document.getElementById('previewAvatar');
    avatar.style.background = color; avatar.style.setProperty('--rc', color);

    // Highlight selected card
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function checkPwd(val) {
    const bar  = document.getElementById('pwdBar');
    const hint = document.getElementById('pwdHint');
    if (!val) { bar.style.width='0%'; hint.textContent="Leave blank to use default: password123"; return; }
    const strength = val.length >= 12 ? 100 : val.length >= 8 ? 65 : val.length >= 5 ? 35 : 15;
    const color    = strength >= 65 ? '#22c55e' : strength >= 35 ? '#f59e0b' : '#ef4444';
    const label    = strength >= 65 ? 'Strong password' : strength >= 35 ? 'Medium — add numbers or symbols' : 'Too short — minimum 8 characters';
    bar.style.width = strength + '%'; bar.style.background = color;
    hint.textContent = label; hint.style.color = color;
}

function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('eyeIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'ph ph-eye' : 'ph ph-eye-slash';
}

function validateForm() {
    if (!document.getElementById('roleInput').value) {
        document.getElementById('roleError').style.display = 'block';
        document.querySelector('.role-cards').scrollIntoView({behavior:'smooth'});
        return false;
    }
    return true;
}
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>