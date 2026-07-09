<?php
// =============================================================
// 7NVENT - Report API Controller (mobile)
// =============================================================
// Same overview metrics + the same 6 report data-sets as ReportController,
// returned as JSON (title/columns/rows) instead of a PDF view or CSV
// download — the app renders/shares these itself. Fetch logic copied
// verbatim from ReportController::fetchData()/getTitle()/getColumns().

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class ReportApiController extends ApiController {

    /** GET /api/reports — overview metrics, same as web index(). */
    public function index(): void {
        $this->requireAuth();
        $categoryBreakdown = db()->fetchAll(
            "SELECT category, SUM(quantity) AS quantity, SUM(quantity * unit_price) AS value
             FROM inventory_items GROUP BY category ORDER BY value DESC"
        );
        $this->json([
            'success' => true,
            'category_breakdown' => $categoryBreakdown,
            'metrics' => [
                'manual_time_reduced' => 80,
                'inventory_accuracy'  => 95,
                'waste_reduction'     => 18,
            ],
        ]);
    }

    /** GET /api/reports/generate?type=stock-summary — structured data for the app to render/export/share. */
    public function generate(): void {
        $payload = $this->requireAuth();
        $type = trim((string)($_GET['type'] ?? 'stock-summary'));

        Auth::log('GENERATE_REPORT', 'reports', 0, "Generated $type (mobile app)", (int)$payload['user_id']);

        $this->json([
            'success' => true,
            'type'    => $type,
            'title'   => $this->getTitle($type),
            'columns' => $this->getColumns($type),
            'rows'    => $this->fetchData($type),
        ]);
    }

    private function fetchData(string $type): array {
        switch ($type) {
            case 'stock-summary':
                return db()->fetchAll(
                    "SELECT i.item_name, i.category, l.location_name, i.quantity,
                            i.par_level, i.status, i.unit_price,
                            (i.quantity * i.unit_price) AS total_value,
                            COALESCE(s.supplier_name, '—') AS supplier_name,
                            COALESCE(i.expiry_date, 'N/A') AS expiry_date
                     FROM inventory_items i
                     JOIN locations l ON i.location_id = l.location_id
                     LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
                     ORDER BY i.category, i.item_name"
                );
            case 'consumption':
                return db()->fetchAll(
                    "SELECT DATE(sm.movement_date) AS date, i.item_name, i.category,
                            COALESCE(l.location_name, '—') AS location_name,
                            SUM(sm.quantity) AS units_consumed,
                            ROUND(SUM(sm.quantity * i.unit_price), 2) AS consumption_value
                     FROM stock_movements sm
                     JOIN inventory_items i ON sm.item_id = i.item_id
                     LEFT JOIN locations l ON sm.location_id = l.location_id
                     WHERE sm.movement_type = 'OUT'
                     GROUP BY DATE(sm.movement_date), sm.item_id
                     ORDER BY sm.movement_date DESC, units_consumed DESC"
                );
            case 'po-history':
                return db()->fetchAll(
                    "SELECT po.po_id, s.supplier_name, po.total_items, po.total_value,
                            u.full_name AS raised_by, po.po_date, po.expected_delivery,
                            po.status, po.approval_status
                     FROM purchase_orders po
                     JOIN suppliers s ON po.supplier_id = s.supplier_id
                     JOIN users u ON po.raised_by = u.user_id
                     ORDER BY po.po_date DESC"
                );
            case 'valuation':
                return db()->fetchAll(
                    "SELECT category, COUNT(*) AS item_count, SUM(quantity) AS total_qty,
                            SUM(quantity * unit_price) AS total_value, AVG(unit_price) AS avg_price,
                            SUM(CASE WHEN status='Low Stock'    THEN 1 ELSE 0 END) AS low_stock_count,
                            SUM(CASE WHEN status='Out of Stock' THEN 1 ELSE 0 END) AS out_stock_count
                     FROM inventory_items GROUP BY category ORDER BY total_value DESC"
                );
            case 'supplier':
                return db()->fetchAll(
                    "SELECT s.supplier_name, s.category, s.contact_person, s.phone, s.email,
                            s.rating, s.lead_time_days, s.ytd_orders_value,
                            COUNT(po.po_id) AS total_orders,
                            SUM(CASE WHEN po.status='Delivered' THEN 1 ELSE 0 END) AS delivered,
                            SUM(CASE WHEN po.status='Cancelled' THEN 1 ELSE 0 END) AS cancelled
                     FROM suppliers s LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
                     GROUP BY s.supplier_id ORDER BY s.rating DESC"
                );
            case 'waste-expiry':
                return db()->fetchAll(
                    "SELECT i.item_name, i.category, l.location_name, i.quantity, i.expiry_date,
                            DATEDIFF(i.expiry_date, CURDATE()) AS days_remaining,
                            (i.quantity * i.unit_price) AS at_risk_value, i.status
                     FROM inventory_items i JOIN locations l ON i.location_id = l.location_id
                     WHERE i.expiry_date IS NOT NULL ORDER BY i.expiry_date ASC"
                );
            default:
                return [];
        }
    }

    private function getTitle(string $type): string {
        switch ($type) {
            case 'stock-summary': return 'Stock Summary Report';
            case 'consumption':   return 'Consumption Analytics Report';
            case 'po-history':    return 'Purchase Order History Report';
            case 'valuation':     return 'Inventory Valuation Report';
            case 'supplier':      return 'Supplier Performance Report';
            case 'waste-expiry':  return 'Waste & Expiry Report';
            default:              return 'Report';
        }
    }

    private function getColumns(string $type): array {
        switch ($type) {
            case 'stock-summary': return ['Item Name','Category','Location','Qty','Par Level','Status','Unit Price (RM)','Total Value (RM)','Supplier','Expiry'];
            case 'consumption':   return ['Date','Item','Category','Location','Units Consumed','Consumption Value (RM)'];
            case 'po-history':    return ['PO #','Supplier','Items','Total Value (RM)','Raised By','PO Date','Est. Delivery','Status','Approval'];
            case 'valuation':     return ['Category','Item Count','Total Qty','Total Value (RM)','Avg Price (RM)','Low Stock','Out of Stock'];
            case 'supplier':      return ['Supplier','Category','Contact','Phone','Email','Rating','Lead Days','YTD Value (RM)','Orders','Delivered','Cancelled'];
            case 'waste-expiry':  return ['Item Name','Category','Location','Qty','Expiry Date','Days Remaining','At-Risk Value (RM)','Status'];
            default:              return [];
        }
    }
}
