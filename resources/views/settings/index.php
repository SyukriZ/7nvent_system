<?php
$pageTitle = 'Settings';
ob_start();

$activeTab = $_GET['tab'] ?? 'general';
$tabs = [
    'general'      => ['icon' => '<i class="ph ph-gear"></i>',  'label' => 'General'],
    'notifications'=> ['icon' => '<i class="ph ph-bell"></i>',  'label' => 'Notifications'],
    'inventory'    => ['icon' => '<i class="ph ph-package"></i>',  'label' => 'Inventory Rules'],
    'integrations' => ['icon' => '<i class="ph ph-link"></i>',  'label' => 'Integrations'],
    'security'     => ['icon' => '<i class="ph ph-lock-key"></i>',  'label' => 'Security'],
    'backup'       => ['icon' => '<i class="ph ph-floppy-disk"></i>',  'label' => 'Backup'],
];
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />

<div class="row g-3">

  <!-- Sidebar Tabs -->
  <div class="col-md-3">
    <div class="stat-card p-2">
      <?php foreach ($tabs as $key => $tab): ?>
        <a href="?tab=<?= $key ?>"
           class="d-flex align-items-center gap-2 px-3 py-2 rounded mb-1 text-decoration-none <?= $activeTab === $key ? 'text-white fw-bold' : '' ?>"
           style="<?= $activeTab === $key ? 'background:#0096FF;' : 'color:var(--text-primary);' ?>font-size:13px;">
          <?= $tab['icon'] ?> <?= $tab['label'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Content -->
  <div class="col-md-9">
    <div class="stat-card">

      <?php if ($settings): ?>

      <!-- ======= GENERAL ======= -->
      <?php if ($activeTab === 'general'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-gear"></i> General Settings</h6>
        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=general">

          <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div>
              <div class="fw-bold small">System Name</div>
              <div class="text-muted" style="font-size:12px">Displayed across the system and reports</div>
            </div>
            <span class="badge bg-dark fs-6 px-3 py-2"><?= $settings['system_name'] ?? '7NVENT' ?></span>
          </div>

          <?php
          $toggles = [
            ['key'=>'automated_reorder_alerts','label'=>'Automated Reorder Alerts','desc'=>'Auto-generate POs when stock hits par level'],
            ['key'=>'expiry_notifications',    'label'=>'Expiry Notifications',    'desc'=>'Alert when items are within 7 days of expiry'],
            ['key'=>'fifo_enforcement',        'label'=>'FIFO Enforcement',        'desc'=>'Enforce First-In-First-Out for perishables'],
            ['key'=>'data_backup_frequency',   'label'=>'Data Backup Frequency',   'desc'=>'Automated database backup interval'],
            ['key'=>'pdpa_compliance_mode',    'label'=>'PDPA Compliance Mode',    'desc'=>'Enable data privacy protections (PDPA 2010)'],
          ];
          foreach ($toggles as $t):
            $val = $settings[$t['key']] ?? '0';
          ?>
          <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
            <div>
              <div class="fw-bold small"><?= $t['label'] ?></div>
              <div class="text-muted" style="font-size:12px"><?= $t['desc'] ?></div>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="<?= $t['key'] ?>"
                     role="switch" style="width:3em;height:1.5em" <?= $val==='1'?'checked':'' ?>>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-warning fw-bold px-4"><i class="ph ph-floppy-disk me-1"></i>Save Settings</button>
          </div>
        </form>

      <!-- ======= NOTIFICATIONS ======= -->
      <?php elseif ($activeTab === 'notifications'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-bell"></i> Notification Settings</h6>
        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=notifications">

          <div class="mb-3">
            <label class="form-label fw-bold small">Notification Email Recipient</label>
            <input type="email" name="notif_email_recipient" class="form-control"
                   value="<?= htmlspecialchars($settings['notif_email_recipient'] ?? '') ?>"
                   placeholder="manager@hotel.com">
            <div class="form-text">System notifications will be sent to this email address.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Notification Frequency</label>
            <select name="notif_frequency" class="form-select">
              <?php foreach(['immediate'=>'Immediate (Real-time)','daily'=>'Daily Summary','weekly'=>'Weekly Summary'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($settings['notif_frequency']??'immediate')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold small">Low Stock Threshold (%)</label>
            <div class="d-flex align-items-center gap-3">
              <input type="range" name="notif_threshold_pct" class="form-range flex-grow-1"
                     min="10" max="75" step="5"
                     value="<?= $settings['notif_threshold_pct'] ?? 25 ?>"
                     oninput="document.getElementById('threshVal').textContent=this.value+'%'">
              <span class="badge bg-primary" id="threshVal"><?= $settings['notif_threshold_pct'] ?? 25 ?>%</span>
            </div>
            <div class="form-text">Notification is sent when stock falls below this percentage of par level.</div>
          </div>

          <div class="border-top pt-3">
            <div class="fw-bold small mb-3">Active Notification Types</div>
            <?php
            $notifToggles = [
              ['key'=>'notif_low_stock', 'label'=>'Low Stock Alert',       'desc'=>'Send alert when stock is running low'],
              ['key'=>'notif_expiry',    'label'=>'Expiry Warning',        'desc'=>'Send alert when items are approaching expiry'],
              ['key'=>'notif_po_update', 'label'=>'Purchase Order Update', 'desc'=>'Send notification when PO status changes'],
            ];
            foreach ($notifToggles as $t):
              $val = $settings[$t['key']] ?? '1';
            ?>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <div>
                <div class="small fw-bold"><?= $t['label'] ?></div>
                <div class="text-muted" style="font-size:12px"><?= $t['desc'] ?></div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="<?= $t['key'] ?>"
                       role="switch" style="width:3em;height:1.5em" <?= $val==='1'?'checked':'' ?>>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-warning fw-bold px-4"><i class="ph ph-floppy-disk me-1"></i>Save Notifications</button>
          </div>
        </form>

      <!-- ======= INVENTORY RULES ======= -->
      <?php elseif ($activeTab === 'inventory'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-package"></i> Inventory Rules</h6>
        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=inventory">

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label fw-bold small">Expiry Warning (Days)</label>
              <select name="inv_expiry_warning_days" class="form-select">
                <?php foreach(['7'=>'7 days','14'=>'14 days','30'=>'30 days'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['inv_expiry_warning_days']??'7')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">System sends alert this many days before item expires.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Warning Threshold (%)</label>
              <select name="inv_warning_threshold" class="form-select">
                <?php foreach(['25'=>'25%','50'=>'50%','75'=>'75%'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['inv_warning_threshold']??'50')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Triggers yellow "Low Stock" badge.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Critical Threshold (%)</label>
              <select name="inv_critical_threshold" class="form-select">
                <?php foreach(['10'=>'10%','25'=>'25%','30'=>'30%'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['inv_critical_threshold']??'25')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Triggers red "Critical" badge.</div>
            </div>
          </div>

          <div class="border-top pt-3">
            <?php
            $invToggles = [
              ['key'=>'inv_adjustment_approval','label'=>'Stock Adjustment Approval',  'desc'=>'Require Inventory Manager approval for all quantity adjustments'],
              ['key'=>'inv_auto_po',            'label'=>'Auto-Generate PO on Critical','desc'=>'System automatically creates a PO when stock reaches Critical level'],
              ['key'=>'fifo_enforcement',       'label'=>'FIFO Enforcement',            'desc'=>'Enforce First-In-First-Out for all perishable goods'],
            ];
            foreach ($invToggles as $t):
              $val = $settings[$t['key']] ?? '1';
            ?>
            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
              <div>
                <div class="fw-bold small"><?= $t['label'] ?></div>
                <div class="text-muted" style="font-size:12px"><?= $t['desc'] ?></div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="<?= $t['key'] ?>"
                       role="switch" style="width:3em;height:1.5em" <?= $val==='1'?'checked':'' ?>>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-warning fw-bold px-4"><i class="ph ph-floppy-disk me-1"></i>Save Inventory Rules</button>
          </div>
        </form>

      <!-- ======= INTEGRATIONS ======= -->
      <?php elseif ($activeTab === 'integrations'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-link"></i> Integrations</h6>
        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=integrations">

          <div class="mb-4">
            <div class="fw-bold small mb-3 text-uppercase text-muted" style="letter-spacing:1px"><i class="ph ph-envelope-simple"></i> SMTP Email Server</div>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label small fw-bold">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control"
                       value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com') ?>"
                       placeholder="smtp.gmail.com">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold">Port</label>
                <input type="number" name="smtp_port" class="form-control"
                       value="<?= $settings['smtp_port'] ?? '587' ?>" placeholder="587">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Username / Email</label>
                <input type="email" name="smtp_username" class="form-control"
                       value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"
                       placeholder="hotel@gmail.com">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="smtp_password" class="form-control"
                       value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>"
                       placeholder="App password">
              </div>
            </div>
          </div>

          <div class="border-top pt-4 mb-4">
            <div class="fw-bold small mb-3 text-uppercase text-muted" style="letter-spacing:1px"><i class="ph ph-barcode"></i> Barcode / QR Scanner</div>
            <label class="form-label small fw-bold">Scanner Type</label>
            <div class="d-flex gap-3">
              <?php foreach(['camera'=>['icon'=>'<i class="ph ph-camera"></i>','label'=>'Phone Camera'],'usb'=>['icon'=>'<i class="ph ph-plug"></i>','label'=>'USB Scanner'],'bluetooth'=>['icon'=>'<i class="ph ph-bluetooth"></i>','label'=>'Bluetooth']] as $v=>$s): ?>
              <label class="d-flex align-items-center gap-2 p-3 border rounded flex-grow-1" style="cursor:pointer">
                <input type="radio" name="scanner_type" value="<?= $v ?>" <?= ($settings['scanner_type']??'camera')===$v?'checked':'' ?>>
                <span><?= $s['icon'] ?> <?= $s['label'] ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="border-top pt-4">
            <div class="fw-bold small mb-3 text-uppercase text-muted" style="letter-spacing:1px"><i class="ph ph-cloud-arrow-up"></i> Cloud Backup</div>
            <label class="form-label small fw-bold">Cloud Backup Destination URL</label>
            <input type="url" name="cloud_backup_url" class="form-control"
                   value="<?= htmlspecialchars($settings['cloud_backup_url'] ?? '') ?>"
                   placeholder="https://backup.example.com/api/upload">
            <div class="form-text">Leave empty to use local backup only.</div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-warning fw-bold px-4"><i class="ph ph-floppy-disk me-1"></i>Save Integrations</button>
          </div>
        </form>

      <!-- ======= SECURITY ======= -->
      <?php elseif ($activeTab === 'security'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-lock-key"></i> Security Settings</h6>

        <div class="alert alert-info py-2 small mb-4">
          <i class="ph ph-shield-check me-1"></i>
          <strong>7NVENT Security Policy:</strong> All passwords are stored as bcrypt hashes.
          Sensitive data is encrypted using AES-256.
        </div>

        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=security">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Session Timeout</label>
              <select name="security_session_timeout" class="form-select">
                <?php foreach(['15'=>'15 minutes','30'=>'30 minutes','60'=>'60 minutes','120'=>'120 minutes'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['security_session_timeout']??'30')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Auto-logout after this period of inactivity.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Minimum Password Length</label>
              <select name="security_min_password" class="form-select">
                <?php foreach(['6'=>'6 characters','8'=>'8 characters','10'=>'10 characters','12'=>'12 characters'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['security_min_password']??'8')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Maximum Login Attempts</label>
              <select name="security_max_attempts" class="form-select">
                <?php foreach(['3'=>'3 attempts','5'=>'5 attempts','10'=>'10 attempts'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['security_max_attempts']??'3')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Account will be locked after this limit is reached.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Account Lockout Duration</label>
              <select name="security_lockout_duration" class="form-select">
                <?php foreach(['5'=>'5 minutes','15'=>'15 minutes','30'=>'30 minutes','60'=>'60 minutes'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['security_lockout_duration']??'15')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="border-top pt-3 mb-3">
            <div class="fw-bold small mb-2">Current System Users</div>
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead>
                  <tr><th>Full Name</th><th>Username</th><th>Role</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php
                  $users = db()->fetchAll("SELECT u.full_name, u.username, u.status, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.role_id");
                  foreach ($users as $u):
                  ?>
                  <tr>
                    <td><?= clean($u['full_name']) ?></td>
                    <td><code><?= clean($u['username']) ?></code></td>
                    <td><small><?= clean($u['role_name']) ?></small></td>
                    <td><span class="badge <?= $u['status']==='Active'?'bg-success':'bg-secondary' ?>"><?= $u['status'] ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-warning fw-bold px-4"><i class="ph ph-floppy-disk me-1"></i>Save Security Settings</button>
          </div>
        </form>

      <!-- ======= BACKUP ======= -->
      <?php elseif ($activeTab === 'backup'): ?>
        <h6 class="fw-bold mb-4"><i class="ph ph-floppy-disk"></i> Backup Settings</h6>

        <?php
        $lastBackup = $settings['backup_last_run'] ?? '';
        $backupOk   = !empty($lastBackup);
        ?>
        <div class="alert <?= $backupOk ? 'alert-success' : 'alert-warning' ?> py-2 small mb-4">
          <i class="ph ph-<?= $backupOk ? 'check-circle' : 'warning-circle' ?> me-1"></i>
          <?= $backupOk ? 'Last backup: <strong>'.$lastBackup.'</strong>' : 'No backup record found. Please run a manual backup.' ?>
        </div>

        <form method="POST" action="<?= APP_URL ?>/settings/update?tab=backup">
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-bold small">Backup Frequency</label>
              <select name="backup_frequency" class="form-select">
                <?php foreach(['daily'=>'Every Day','weekly'=>'Every Week','monthly'=>'Every Month'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['backup_frequency']??'daily')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Backup Retention Period</label>
              <select name="backup_retention_days" class="form-select">
                <?php foreach(['7'=>'7 days','30'=>'30 days','60'=>'60 days','90'=>'90 days'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($settings['backup_retention_days']??'30')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Storage Location</label>
              <select name="backup_storage" class="form-select">
                <option value="local" <?= ($settings['backup_storage']??'local')==='local'?'selected':'' ?>>Local Server</option>
                <option value="cloud" <?= ($settings['backup_storage']??'')==='cloud'?'selected':'' ?>>Cloud Storage</option>
              </select>
            </div>
          </div>

          <!-- Backup Stats -->
          <div class="row g-3 mb-4">
            <?php
            $tableCount = db()->fetchOne("SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA='7nvent'")['cnt'] ?? 10;
            $userCount  = db()->fetchOne("SELECT COUNT(*) as cnt FROM users")['cnt'] ?? 6;
            $itemCount  = db()->fetchOne("SELECT COUNT(*) as cnt FROM inventory_items")['cnt'] ?? 24;
            $logCount   = db()->fetchOne("SELECT COUNT(*) as cnt FROM audit_logs")['cnt'] ?? 0;
            $stats = [
                ['val'=>$tableCount, 'lbl'=>'Tables'],
                ['val'=>$userCount,  'lbl'=>'Users'],
                ['val'=>$itemCount,  'lbl'=>'Inventory Items'],
                ['val'=>$logCount,   'lbl'=>'Audit Logs'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="col-6 col-md-3">
              <div class="text-center p-3 rounded" style="background:var(--bg-subtle)">
                <div style="font-size:22px;font-weight:800"><?= $s['val'] ?></div>
                <div class="text-muted small"><?= $s['lbl'] ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-primary"
                    onclick="runManualBackup()" id="manualBackupBtn">
              <i class="ph ph-arrow-clockwise me-1"></i>Run Manual Backup Now
            </button>
            <button type="submit" class="btn btn-warning fw-bold px-4">
              <i class="ph ph-floppy-disk me-1"></i>Save Backup Settings
            </button>
          </div>
        </form>

        <script>
        function runManualBackup() {
            const btn = document.getElementById('manualBackupBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Running backup...';
            fetch('<?= APP_URL ?>/settings/manual-backup', { method: 'POST' })
                .then(r => r.json())
                .then(d => {
                    btn.innerHTML = '<i class="ph ph-check-circle me-1"></i>Backup successful! ' + d.time;
                    btn.className = 'btn btn-success';
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-arrow-clockwise me-1"></i>Run Manual Backup Now';
                    alert('Backup failed. Please try again.');
                });
        }
        </script>

      <?php endif; ?>
      <?php else: ?>
        <div class="text-center text-muted py-5">No settings data found.</div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>