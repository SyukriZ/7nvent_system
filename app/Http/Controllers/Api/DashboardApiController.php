<?php
// =============================================================
// 7NVENT - Dashboard API Controller (mobile)
// =============================================================
// Mirrors DashboardController::index()'s exact stats queries — same KPIs,
// same category breakdown, same recent-activity/alerts feeds, same
// weekday-anchored weekly consumption calc — just returned as JSON instead
// of rendered into resources/views/dashboard/index.php.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';
require_once __DIR__ . '/../../../AlertSeeder.php';

class DashboardApiController extends ApiController {

    /** GET /api/dashboard — home screen stats for the Flutter app. */
    public function index(): void {
        $this->requireAuth();

        // Same self-healing alert top-up the web dashboard runs — keeps the
        // mobile app's alert feed just as "live" whichever client opens
        // first each day. Idempotent/no-op after the first load of the day.
        AlertSeeder::run();

        $db = db();

        $totalStock = $db->fetchOne("SELECT SUM(quantity) as total FROM inventory_items")['total'] ?? 0;
        $pendingPOValue = $db->fetchOne(
            "SELECT SUM(total_value) as total FROM purchase_orders WHERE status IN ('Pending','In Transit')"
        )['total'] ?? 0;
        $criticalAlerts = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM alerts WHERE alert_type = 'Critical' AND status = 'Active'"
        )['cnt'] ?? 0;

        $categoryStock = $db->fetchAll(
            "SELECT category,
                    SUM(quantity) as total_qty,
                    SUM(par_level) as total_par,
                    ROUND(SUM(quantity)/NULLIF(SUM(par_level),0)*100,0) as pct
             FROM inventory_items
             GROUP BY category"
        );

        $recentActivity = $db->fetchAll(
            "SELECT al.*, u.full_name, r.role_name
             FROM audit_logs al
             JOIN users u  ON al.user_id  = u.user_id
             JOIN roles r  ON u.role_id   = r.role_id
             ORDER BY al.timestamp DESC LIMIT 5"
        );

        $activeAlerts = $db->fetchAll(
            "SELECT a.*, i.item_name FROM alerts a
             LEFT JOIN inventory_items i ON a.item_id = i.item_id
             WHERE a.status = 'Active'
             ORDER BY FIELD(a.alert_type,'Critical','Warning','Info'), a.triggered_at DESC
             LIMIT 4"
        );

        // Weekly consumption — same weekday-anchored logic as the web
        // dashboard (see DashboardController for the reasoning): always
        // shows the most recent recorded OUT-movement total per weekday,
        // not tied to a specific calendar week.
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $rows = $db->fetchAll(
            "SELECT movement_date, SUM(quantity) AS units
             FROM stock_movements
             WHERE movement_type = 'OUT'
             GROUP BY movement_date
             ORDER BY movement_date DESC"
        );

        $byWeekday = [];
        foreach ($rows as $r) {
            $wd = (int)date('N', strtotime($r['movement_date'])) - 1; // 0=Mon..6=Sun
            if (!isset($byWeekday[$wd])) {
                $byWeekday[$wd] = (int)$r['units'];
            }
        }

        $weeklyConsumption = [];
        for ($i = 0; $i <= 6; $i++) {
            $weeklyConsumption[] = [
                'day'   => $dayNames[$i],
                'units' => $byWeekday[$i] ?? 0,
            ];
        }

        $this->json([
            'success' => true,
            'stats' => [
                'total_stock'       => (int) $totalStock,
                'pending_po_value'  => (float) $pendingPOValue,
                'critical_alerts'   => (int) $criticalAlerts,
            ],
            'category_stock'     => $categoryStock,
            'recent_activity'    => $recentActivity,
            'active_alerts'      => $activeAlerts,
            'weekly_consumption' => $weeklyConsumption,
        ]);
    }
}
