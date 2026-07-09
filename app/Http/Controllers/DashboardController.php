<?php
// =============================================================
// 7NVENT - Dashboard Controller
// =============================================================

require_once __DIR__ . '/../../Auth.php';
require_once __DIR__ . '/../../AlertSeeder.php';

class DashboardController {

    public function index(): void {
        Auth::required();

        // Keep alerts honest and live: resolves healthy items, raises
        // alerts for genuinely low/expiring stock, and — only if a slow
        // day leaves fewer than 2 active alerts — tops up to a realistic
        // 2-5 for demo purposes (see app/AlertSeeder.php for details).
        // No-ops after the first dashboard load of the calendar day.
        AlertSeeder::run();

        $db = db();

        // KPI Stats
        $totalStock = $db->fetchOne("SELECT SUM(quantity) as total FROM inventory_items")['total'] ?? 0;
        $pendingPOValue = $db->fetchOne(
            "SELECT SUM(total_value) as total FROM purchase_orders WHERE status IN ('Pending','In Transit')"
        )['total'] ?? 0;
        $criticalAlerts = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM alerts WHERE alert_type = 'Critical' AND status = 'Active'"
        )['cnt'] ?? 0;

        // Stock levels by category (percentage of items in stock)
        $categoryStock = $db->fetchAll(
            "SELECT category,
                    SUM(quantity) as total_qty,
                    SUM(par_level) as total_par,
                    ROUND(SUM(quantity)/NULLIF(SUM(par_level),0)*100,0) as pct
             FROM inventory_items
             GROUP BY category"
        );

        // Recent activity (last 5 audit logs) - include role for color coding
        $recentActivity = $db->fetchAll(
            "SELECT al.*, u.full_name, r.role_name
             FROM audit_logs al
             JOIN users u  ON al.user_id  = u.user_id
             JOIN roles r  ON u.role_id   = r.role_id
             ORDER BY al.timestamp DESC LIMIT 5"
        );

        // Active alerts (max 4 for sidebar)
        $activeAlerts = $db->fetchAll(
            "SELECT a.*, i.item_name FROM alerts a
             LEFT JOIN inventory_items i ON a.item_id = i.item_id
             WHERE a.status = 'Active'
             ORDER BY FIELD(a.alert_type,'Critical','Warning','Info'), a.triggered_at DESC
             LIMIT 4"
        );

        // Weekly consumption — REAL data, weekday-anchored
        // (not tied to a specific calendar week, so it never
        // goes stale — always shows the most recent recorded
        // consumption for each weekday: Mon, Tue, ... Sun)
        // =====================================================
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $todayDow = (int)date('N'); // 1=Mon ... 7=Sun

        $rows = $db->fetchAll(
            "SELECT movement_date, SUM(quantity) AS units
             FROM stock_movements
             WHERE movement_type = 'OUT'
             GROUP BY movement_date
             ORDER BY movement_date DESC"
        );

        // For each weekday, keep only the MOST RECENT date's total
        // (rows are already ordered DESC, so first match wins)
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

        $data = compact(
            'totalStock', 'pendingPOValue', 'criticalAlerts',
            'categoryStock', 'recentActivity', 'activeAlerts', 'weeklyConsumption'
        );

        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/dashboard/index.php';
    }
}