<?php
// =============================================================
// 7NVENT - Daily Alert Seeder
// -------------------------------------------------------------
// Ensures the Alerts page / dashboard always reflects live
// inventory state, and that there is always a small, realistic
// spread (2-5) of active Critical/Warning alerts to review on any
// given day, instead of an empty state.
//
// Runs at most once per calendar day (guarded via the `settings`
// table's `last_alert_seed_date` key), triggered from
// DashboardController::index() on the first dashboard load of the
// day. Two phases:
//
//   1. REAL scan — identical logic to AlertController::runScan():
//      resolve alerts for items that are healthy again, create
//      alerts for items that are genuinely Low/Out of Stock or
//      expiring soon. This is 100% live data, nothing synthetic.
//
//   2. DEMO TOP-UP — only runs if phase 1 still leaves fewer than 2
//      active Critical/Warning alerts (e.g. a freshly-seeded DB
//      where every item happens to be healthy that day). It nudges
//      a handful of randomly chosen items' quantity down below
//      their par level, so the system has something realistic to
//      show. Every such adjustment is logged to audit_logs tagged
//      "[Demo Seed]" so it is never confused with a real stock
//      movement — but the resulting alert itself is a completely
//      normal, live, DB-backed alert tied to a real inventory row,
//      not a fake floating record.
// =============================================================

require_once __DIR__ . '/Auth.php';

class AlertSeeder {

    public static function run(): void {
        try {
            $today = date('Y-m-d');
            $last  = db()->fetchOne(
                "SELECT setting_value FROM settings WHERE setting_key = 'last_alert_seed_date'"
            );
            if ($last && $last['setting_value'] === $today) {
                return; // already ran today
            }

            self::selfHealSchema();
            self::realScan();
            self::topUpIfNeeded();

            db()->execute(
                "INSERT INTO settings (setting_key, setting_value) VALUES ('last_alert_seed_date',?)
                 ON DUPLICATE KEY UPDATE setting_value=?",
                [$today, $today]
            );
        } catch (\Throwable $e) {
            // Never let alert housekeeping break page loads — fail silently.
            error_log('7NVENT AlertSeeder: ' . $e->getMessage());
        }
    }

    // ── Fixes a pre-existing schema gap ──────────────────────────────
    // Several places in the codebase (AlertController::runScan(),
    // QRController::qrUpdate()) set alerts.status = 'Resolved', but that
    // value was never added to the alerts.status ENUM('Active','Approved',
    // 'Dismissed'). Widening the ENUM is additive and safe; this is a
    // no-op if already applied, and non-fatal if the DB user lacks ALTER
    // privilege (the try/catch in run() swallows it).
    private static function selfHealSchema(): void {
        try {
            db()->execute(
                "ALTER TABLE alerts MODIFY status ENUM('Active','Approved','Dismissed','Resolved') DEFAULT 'Active'"
            );
        } catch (\Throwable $e) {
            // Already applied, or insufficient privilege — either way, non-fatal.
        }
    }

    // ── Phase 1: same rules as AlertController::runScan(), no redirect ──
    private static function realScan(): void {
        db()->execute(
            "UPDATE alerts a
             JOIN inventory_items i ON a.item_id = i.item_id
             SET a.status='Resolved', a.resolved_at=NOW()
             WHERE a.status='Active'
               AND a.alert_type IN ('Critical','Warning')
               AND i.status = 'In-Stock'"
        );

        $lowItems = db()->fetchAll(
            "SELECT i.item_id, i.item_name, i.category,
                    i.quantity, i.par_level, i.location_id, i.status
             FROM inventory_items i
             WHERE i.status != 'In-Stock'"
        );

        foreach ($lowItems as $item) {
            $existing = db()->fetchOne(
                "SELECT alert_id FROM alerts WHERE item_id=? AND status='Active' LIMIT 1",
                [$item['item_id']]
            );
            if ($existing) continue;

            $alertType = $item['status'] === 'Out of Stock' ? 'Critical' : 'Warning';
            $label     = "{$item['category']} - {$item['item_name']}";
            $title     = $item['status'] === 'Out of Stock'
                ? "$label — Out of Stock"
                : "LOW STOCK: $label";
            $desc = "Current stock: {$item['quantity']} unit(s). Par level: {$item['par_level']} unit(s).";

            db()->execute(
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated)
                 VALUES (?,?,?,?,?,1)",
                [$alertType, $title, $desc, $item['item_id'], $item['location_id']]
            );
        }

        $expiring = db()->fetchAll(
            "SELECT item_id, item_name, expiry_date, location_id,
                    DATEDIFF(expiry_date, CURDATE()) AS days_left
             FROM inventory_items
             WHERE expiry_date IS NOT NULL
               AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
        );

        foreach ($expiring as $e) {
            $existing = db()->fetchOne(
                "SELECT alert_id FROM alerts WHERE item_id=? AND alert_type='Info' AND status='Active' LIMIT 1",
                [$e['item_id']]
            );
            if ($existing) continue;

            $days  = (int)$e['days_left'];
            $title = "Expiry Warning: {$e['item_name']} expires in $days day(s)";
            $desc  = "Expiry date: {$e['expiry_date']}. Please review FIFO compliance and action accordingly.";

            db()->execute(
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated)
                 VALUES ('Info',?,?,?,?,1)",
                [$title, $desc, $e['item_id'], $e['location_id']]
            );
        }
    }

    // ── Phase 2: only tops up if the real scan left too few alerts ──────
    private static function topUpIfNeeded(): void {
        $count = db()->fetchOne(
            "SELECT COUNT(*) AS cnt FROM alerts WHERE status='Active' AND alert_type IN ('Critical','Warning')"
        )['cnt'] ?? 0;
        $count = (int)$count;

        if ($count >= 2) return; // already in the healthy 2-5 range

        $target = random_int(2, 5);
        $need   = $target - $count;
        if ($need <= 0) return;

        // Pick random currently-healthy items to nudge down, so the demo
        // alerts spread across different categories/locations realistically.
        $candidates = db()->fetchAll(
            "SELECT item_id, item_name, category, quantity, par_level, location_id
             FROM inventory_items
             WHERE status = 'In-Stock' AND par_level > 0
             ORDER BY RAND() LIMIT " . (int)$need
        );

        foreach ($candidates as $item) {
            // Land the quantity somewhere between 0 and (par_level - 1) so it
            // genuinely reads as Low Stock or Out of Stock — never negative.
            $newQty    = random_int(0, max(0, $item['par_level'] - 1));
            $newStatus = $newQty === 0 ? 'Out of Stock' : 'Low Stock';

            db()->execute(
                "UPDATE inventory_items SET quantity=?, status=? WHERE item_id=?",
                [$newQty, $newStatus, $item['item_id']]
            );

            Auth::log(
                'DEMO_SEED_ALERT',
                'inventory_items',
                $item['item_id'],
                "[Demo Seed] Quantity nudged {$item['quantity']} → {$newQty} unit(s) to keep the Alerts page " .
                "populated for demo/testing purposes. Par level: {$item['par_level']}."
            );

            $alertType = $newStatus === 'Out of Stock' ? 'Critical' : 'Warning';
            $label     = "{$item['category']} - {$item['item_name']}";
            $title     = $newStatus === 'Out of Stock' ? "$label — Out of Stock" : "LOW STOCK: $label";
            $desc      = "Current stock: {$newQty} unit(s). Par level: {$item['par_level']} unit(s). [Demo Seed]";

            db()->execute(
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated)
                 VALUES (?,?,?,?,?,1)",
                [$alertType, $title, $desc, $item['item_id'], $item['location_id']]
            );
        }
    }
}
