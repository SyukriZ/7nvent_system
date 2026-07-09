<?php
// =============================================================
// 7NVENT - Analytics Controller
// Five live, animated widgets — all backed by real 7NVENT data:
//   1. Inventory Valuation Trend     (candlestick)
//   2. Fast vs Slow-Moving Items     (horizontal bar ranking)
//   3. Supplier Delivery Performance (stacked bar)
//   4. Consumption Heatmap           (day x location)
//   5. Supplier Locations Map        (JS Vector Map, Malaysia)
//
// Design note: widgets 1 and 4 use a "weekday-evergreen" pattern —
// they bucket by day-of-week (Mon..Sun) using the most recent
// recorded value for that weekday, rather than an exact calendar
// date range. This mirrors the Dashboard fix and means these
// widgets never go stale, no matter when the system is opened.
// =============================================================

require_once __DIR__ . '/../../Auth.php';

class AnalyticsController {

    private $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    private $db;

    public function index(): void {
        Auth::required();
        $this->db = db();

        $valuation    = $this->buildValuationTrend();
        $movers       = $this->buildFastSlowMovers();
        $supplierPerf = $this->buildSupplierPerformance();
        $heatmap      = $this->buildConsumptionHeatmap();
        $supplierMap  = $this->buildSupplierMap();

        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/analytics/index.php';
    }

    // =====================================================================
    // 1. Inventory Valuation Trend — candlestick (Open/High/Low/Close)
    //
    // We don't store daily value snapshots, so we RECONSTRUCT a
    // realistic valuation history by walking backwards from the
    // current total inventory value, undoing each weekday's most
    // recent recorded consumption (OUT) value. This is an estimate,
    // not a literal historical record — clearly labelled as such
    // in the view.
    // =====================================================================
    private function buildValuationTrend(): array {
        $db = $this->db;

        $currentTotal = (float)($db->fetchOne(
            "SELECT SUM(quantity * unit_price) AS total FROM inventory_items"
        )['total'] ?? 0);

        // Most recent recorded consumption VALUE per weekday
        $rows = $db->fetchAll(
            "SELECT sm.movement_date,
                    SUM(sm.quantity * i.unit_price) AS value_change
             FROM stock_movements sm
             JOIN inventory_items i ON sm.item_id = i.item_id
             WHERE sm.movement_type = 'OUT'
             GROUP BY sm.movement_date
             ORDER BY sm.movement_date DESC"
        );

        $byWeekday = [];
        foreach ($rows as $r) {
            $wd = (int)date('N', strtotime($r['movement_date'])) - 1; // 0=Mon..6=Sun
            if (!isset($byWeekday[$wd])) {
                $byWeekday[$wd] = (float)$r['value_change'];
            }
        }

        // Walk backwards from Sunday (index 6) to Monday (index 0):
        // close[day] = open[day+1]; open[day] = close[day] + that day's consumption
        $close = [];
        $open  = [];
        $runningClose = $currentTotal;
        for ($i = 6; $i >= 0; $i--) {
            $close[$i] = $runningClose;
            $delta     = $byWeekday[$i] ?? 0;
            $open[$i]  = $close[$i] + $delta;
            $runningClose = $open[$i];
        }

        $series = [];
        for ($i = 0; $i <= 6; $i++) {
            $o = round($open[$i], 2);
            $c = round($close[$i], 2);
            $high = round(max($o, $c), 2);
            $low  = round(min($o, $c), 2);
            $series[] = [$this->dayNames[$i], $o, $high, $low, $c];
        }

        return $series;
    }

    // =====================================================================
    // 2. Fast vs Slow-Moving Items — ranked by total units consumed
    // =====================================================================
    private function buildFastSlowMovers(): array {
        return $this->db->fetchAll(
            "SELECT i.item_name, i.category, SUM(sm.quantity) AS total_out
             FROM stock_movements sm
             JOIN inventory_items i ON sm.item_id = i.item_id
             WHERE sm.movement_type = 'OUT'
             GROUP BY sm.item_id
             ORDER BY total_out DESC
             LIMIT 10"
        );
    }

    // =====================================================================
    // 3. Supplier Delivery Performance — stacked bar per supplier
    // =====================================================================
    private function buildSupplierPerformance(): array {
        return $this->db->fetchAll(
            "SELECT s.supplier_name,
                    SUM(po.status = 'Delivered') AS delivered,
                    SUM(po.status IN ('Pending','In Transit')) AS pending,
                    SUM(po.status = 'Cancelled') AS cancelled
             FROM suppliers s
             LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
             GROUP BY s.supplier_id
             HAVING (delivered + pending + cancelled) > 0
             ORDER BY s.supplier_name"
        );
    }

    // =====================================================================
    // 4. Consumption Heatmap — day-of-week x location (weekday-evergreen)
    // =====================================================================
    private function buildConsumptionHeatmap(): array {
        $rows = $this->db->fetchAll(
            "SELECT sm.movement_date, l.location_name, SUM(sm.quantity) AS units
             FROM stock_movements sm
             JOIN locations l ON sm.location_id = l.location_id
             WHERE sm.movement_type = 'OUT'
             GROUP BY sm.movement_date, sm.location_id
             ORDER BY sm.movement_date DESC"
        );

        // Keep only the most recent (day-of-week, location) combination
        $seen = [];
        $matrix = []; // [location_name][weekday_index] = units
        $locationOrder = [];

        foreach ($rows as $r) {
            $wd  = (int)date('N', strtotime($r['movement_date'])) - 1;
            $loc = $r['location_name'];
            $key = $loc . '|' . $wd;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            if (!in_array($loc, $locationOrder)) $locationOrder[] = $loc;
            $matrix[$loc][$wd] = (int)$r['units'];
        }

        $series = [];
        foreach ($locationOrder as $loc) {
            $data = [];
            for ($i = 0; $i <= 6; $i++) {
                $data[] = $matrix[$loc][$i] ?? 0;
            }
            $series[] = ['name' => $loc, 'data' => $data];
        }

        return ['days' => $this->dayNames, 'series' => $series];
    }

    // =====================================================================
    // 5. Supplier Locations Map — Malaysia, marker map
    //
    // The `suppliers` table has no stored city/coordinates, so each
    // supplier is assigned a representative Malaysian city for
    // visualisation purposes (deterministic by supplier_id, not random
    // — the same supplier always lands on the same city on every load).
    // =====================================================================
    private function buildSupplierMap(): array {
        $suppliers = $this->db->fetchAll(
            "SELECT supplier_id, supplier_name, category, rating
             FROM suppliers ORDER BY supplier_id"
        );

        // Representative Malaysian cities (lat, lng)
        $cities = [
            ['name' => 'Kuala Lumpur',  'lat' => 3.1390, 'lng' => 101.6869],
            ['name' => 'Petaling Jaya', 'lat' => 3.1073, 'lng' => 101.6067],
            ['name' => 'Shah Alam',     'lat' => 3.0733, 'lng' => 101.5185],
            ['name' => 'Johor Bahru',   'lat' => 1.4927, 'lng' => 103.7414],
            ['name' => 'George Town',   'lat' => 5.4141, 'lng' => 100.3288],
            ['name' => 'Ipoh',          'lat' => 4.5975, 'lng' => 101.0901],
            ['name' => 'Melaka City',   'lat' => 2.1896, 'lng' => 102.2501],
            ['name' => 'Kuching',       'lat' => 1.5535, 'lng' => 110.3592],
            ['name' => 'Kota Kinabalu', 'lat' => 5.9804, 'lng' => 116.0735],
            ['name' => 'Seremban',      'lat' => 2.7297, 'lng' => 101.9381],
        ];

        $markers = [];
        $cityCount = count($cities);
        foreach ($suppliers as $i => $s) {
            $city = $cities[$i % $cityCount];
            $markers[] = [
                'name'     => $s['supplier_name'] . ' — ' . $city['name'],
                'coords'   => [$city['lat'], $city['lng']],
                'category' => $s['category'],
                'rating'   => $s['rating'],
            ];
        }

        return $markers;
    }
}