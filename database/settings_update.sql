-- =============================================================
-- 7NVENT - Tambah Settings Baru
-- Jalankan dalam phpMyAdmin → database 7nvent → tab SQL
-- =============================================================

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES

-- Notifications
('notif_email_recipient',   'manager@hotel7nvent.com', 'Emel penerima notifikasi'),
('notif_low_stock',         '1',                       'Hantar notifikasi stok rendah'),
('notif_expiry',            '1',                       'Hantar notifikasi item hampir luput'),
('notif_po_update',         '1',                       'Hantar notifikasi kemaskini PO'),
('notif_frequency',         'immediate',               'Kekerapan: immediate / daily / weekly'),
('notif_threshold_pct',     '25',                      'Peratusan par level untuk trigger notifikasi'),

-- Inventory Rules
('inv_expiry_warning_days', '7',                       'Hari amaran sebelum item luput'),
('inv_warning_threshold',   '50',                      'Peratusan par level untuk Warning'),
('inv_critical_threshold',  '25',                      'Peratusan par level untuk Critical'),
('inv_adjustment_approval', '1',                       'Perlukan kelulusan untuk adjustment stok'),
('inv_auto_po',             '1',                       'Auto-generate PO bila stok kritikal'),

-- Integrations
('smtp_host',               'smtp.gmail.com',          'SMTP email server host'),
('smtp_port',               '587',                     'SMTP port'),
('smtp_username',           '',                        'SMTP username / email'),
('smtp_password',           '',                        'SMTP password'),
('scanner_type',            'camera',                  'Jenis scanner: usb / bluetooth / camera'),
('cloud_backup_url',        '',                        'URL destinasi cloud backup'),

-- Security
('security_session_timeout','30',                      'Session timeout dalam minit'),
('security_max_attempts',   '3',                       'Maksimum percubaan login'),
('security_lockout_duration','15',                     'Tempoh kunci akaun dalam minit'),
('security_min_password',   '8',                       'Panjang minimum password'),

-- Backup
('backup_frequency',        'daily',                   'Kekerapan: daily / weekly / monthly'),
('backup_retention_days',   '30',                      'Bilangan hari simpan backup'),
('backup_last_run',         '',                        'Tarikh backup terakhir'),
('backup_storage',          'local',                   'Lokasi: local / cloud');
