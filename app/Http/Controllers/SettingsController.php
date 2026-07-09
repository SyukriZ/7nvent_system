<?php
require_once __DIR__ . '/../../Auth.php';

class SettingsController {

    public function index(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) {
            redirect('/dashboard', 'Access denied.', 'error');
        }
        $settings = [];
        $rows = db()->fetchAll("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/settings/index.php';
    }

    public function update(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) {
            redirect('/settings', 'Access denied.', 'error');
        }

        $tab = clean($_GET['tab'] ?? 'general');

        // Define all toggle fields per tab
        $togglesByTab = [
            'general' => [
                'automated_reorder_alerts',
                'expiry_notifications',
                'fifo_enforcement',
                'data_backup_frequency',
                'pdpa_compliance_mode',
            ],
            'notifications' => [
                'notif_low_stock',
                'notif_expiry',
                'notif_po_update',
            ],
            'inventory' => [
                'inv_adjustment_approval',
                'inv_auto_po',
                'fifo_enforcement',
            ],
        ];

        // Define text/select fields per tab
        $textsByTab = [
            'notifications' => [
                'notif_email_recipient',
                'notif_frequency',
                'notif_threshold_pct',
            ],
            'inventory' => [
                'inv_expiry_warning_days',
                'inv_warning_threshold',
                'inv_critical_threshold',
            ],
            'integrations' => [
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'scanner_type',
                'cloud_backup_url',
            ],
            'security' => [
                'security_session_timeout',
                'security_min_password',
                'security_max_attempts',
                'security_lockout_duration',
            ],
            'backup' => [
                'backup_frequency',
                'backup_retention_days',
                'backup_storage',
            ],
        ];

        // Save toggle fields
        if (isset($togglesByTab[$tab])) {
            foreach ($togglesByTab[$tab] as $key) {
                $value = isset($_POST[$key]) ? '1' : '0';
                db()->execute(
                    "INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?",
                    [$key, $value, $value]
                );
            }
        }

        // Save text/select fields
        if (isset($textsByTab[$tab])) {
            foreach ($textsByTab[$tab] as $key) {
                if (isset($_POST[$key])) {
                    $value = clean($_POST[$key]);
                    // Don't save empty password if not changed
                    if ($key === 'smtp_password' && empty($value)) continue;
                    db()->execute(
                        "INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?",
                        [$key, $value, $value]
                    );
                }
            }
        }

        Auth::log('UPDATE_SETTINGS', 'settings', 0, "Settings tab '$tab' updated");
        redirect('/settings?tab=' . $tab, 'Settings saved successfully!', 'success');
    }

    public function manualBackup(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'IT Administrator')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }
        header('Content-Type: application/json');

        $time = date('d M Y, H:i');

        // Update last backup time in settings
        db()->execute(
            "INSERT INTO settings (setting_key, setting_value) VALUES ('backup_last_run',?) ON DUPLICATE KEY UPDATE setting_value=?",
            [$time, $time]
        );

        Auth::log('MANUAL_BACKUP', 'settings', 0, "Manual backup dijalankan pada $time");

        echo json_encode(['success' => true, 'time' => $time]);
    }
}