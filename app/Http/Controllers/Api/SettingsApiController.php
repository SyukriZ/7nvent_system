<?php
// =============================================================
// 7NVENT - Settings API Controller (mobile)
// =============================================================
// Mirrors SettingsController exactly — same per-tab toggle/text field
// definitions, so a setting changed from the app is the exact same
// setting_key row the web Settings page reads/writes.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class SettingsApiController extends ApiController {

    private const TOGGLES_BY_TAB = [
        'general' => [
            'automated_reorder_alerts', 'expiry_notifications', 'fifo_enforcement',
            'data_backup_frequency', 'pdpa_compliance_mode',
        ],
        'notifications' => ['notif_low_stock', 'notif_expiry', 'notif_po_update'],
        'inventory'     => ['inv_adjustment_approval', 'inv_auto_po', 'fifo_enforcement'],
    ];

    private const TEXTS_BY_TAB = [
        'notifications' => ['notif_email_recipient', 'notif_frequency', 'notif_threshold_pct'],
        'inventory'     => ['inv_expiry_warning_days', 'inv_warning_threshold', 'inv_critical_threshold'],
        'integrations'  => ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'scanner_type', 'cloud_backup_url'],
        'security'      => ['security_session_timeout', 'security_min_password', 'security_max_attempts', 'security_lockout_duration'],
        'backup'        => ['backup_frequency', 'backup_retention_days', 'backup_storage'],
    ];

    /** GET /api/settings */
    public function index(): void {
        $this->requireRole('Inventory Manager', 'IT Administrator');
        $settings = [];
        foreach (db()->fetchAll("SELECT setting_key, setting_value FROM settings") as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        // Redact stored SMTP password the same way the web form does (never
        // echo the real secret back to a client) — presence is signalled by
        // a boolean instead.
        $hasSmtpPassword = isset($settings['smtp_password']) && $settings['smtp_password'] !== '';
        unset($settings['smtp_password']);

        $this->json(['success' => true, 'settings' => $settings, 'has_smtp_password' => $hasSmtpPassword]);
    }

    /** POST /api/settings/update?tab=general — { fields: {key: value, ...} } */
    public function update(): void {
        $payload = $this->requireRole('Inventory Manager', 'IT Administrator');
        $tab = trim((string)($_GET['tab'] ?? 'general'));
        $body = $this->body();
        $fields = is_array($body['fields'] ?? null) ? $body['fields'] : $body;

        if (isset(self::TOGGLES_BY_TAB[$tab])) {
            foreach (self::TOGGLES_BY_TAB[$tab] as $key) {
                $value = !empty($fields[$key]) ? '1' : '0';
                db()->execute(
                    "INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?",
                    [$key, $value, $value]
                );
            }
        }

        if (isset(self::TEXTS_BY_TAB[$tab])) {
            foreach (self::TEXTS_BY_TAB[$tab] as $key) {
                if (array_key_exists($key, $fields)) {
                    $value = trim((string)$fields[$key]);
                    if ($key === 'smtp_password' && $value === '') continue;
                    db()->execute(
                        "INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?",
                        [$key, $value, $value]
                    );
                }
            }
        }

        Auth::log('UPDATE_SETTINGS', 'settings', 0, "Settings tab '$tab' updated (mobile app)", (int)$payload['user_id']);
        $this->json(['success' => true, 'message' => 'Settings saved successfully!']);
    }

    /** POST /api/settings/manual-backup */
    public function manualBackup(): void {
        $payload = $this->requireRole('Inventory Manager', 'IT Administrator');
        $time = date('d M Y, H:i');
        db()->execute(
            "INSERT INTO settings (setting_key, setting_value) VALUES ('backup_last_run',?) ON DUPLICATE KEY UPDATE setting_value=?",
            [$time, $time]
        );
        Auth::log('MANUAL_BACKUP', 'settings', 0, 'Manual backup dijalankan pada ' . $time . ' (mobile app)', (int)$payload['user_id']);
        $this->json(['success' => true, 'time' => $time]);
    }
}
