<?php
// =============================================================
// 7NVENT - Alert API Controller (mobile)
// =============================================================
// Mirrors AlertController exactly — same query/counts, same
// approve/dismiss/scan actions, same auto-PO-on-approve logic.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class AlertApiController extends ApiController {

    /** GET /api/alerts?type=Critical|Warning|Info */
    public function index(): void {
        $this->requireAuth();
        $type = trim((string)($_GET['type'] ?? ''));

        $sql = "SELECT a.*, i.item_name, i.category, i.quantity, i.par_level, l.location_name
                FROM alerts a
                LEFT JOIN inventory_items i ON a.item_id = i.item_id
                LEFT JOIN locations l ON a.location_id = l.location_id
                WHERE a.status = 'Active'";
        $params = [];
        if ($type) { $sql .= " AND a.alert_type = ?"; $params[] = $type; }
        $sql .= " ORDER BY FIELD(a.alert_type,'Critical','Warning','Info'), a.triggered_at DESC";

        $alerts = db()->fetchAll($sql, $params);
        $counts = db()->fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(alert_type='Critical') AS critical,
                    SUM(alert_type='Warning')  AS warning,
                    SUM(alert_type='Info')     AS info
             FROM alerts WHERE status='Active'"
        );

        $this->json(['success' => true, 'alerts' => $alerts, 'counts' => $counts]);
    }

    /**
     * POST /api/alerts/resolve — { action: 'approve'|'dismiss'|'scan', alert_id? }
     * Same three branches as the web resolve() action.
     */
    public function resolve(): void {
        $payload = $this->requireAuth();
        $body = $this->body();
        $action = trim((string)($body['action'] ?? ''));

        if ($action === 'scan') {
            if (!in_array($payload['role_name'] ?? '', ['Inventory Manager', 'Procurement Officer'], true)) {
                $this->jsonError('Access denied.', 403);
            }
            $this->runScan((int)$payload['user_id']);
            return;
        }

        if (!in_array($payload['role_name'] ?? '', ['Inventory Manager', 'Procurement Officer'], true)) {
            $this->jsonError('You do not have permission to resolve alerts.', 403);
        }

        $alertId   = (int)($body['alert_id'] ?? 0);
        if (!$alertId) $this->jsonError('Alert id is required.', 422);
        $newStatus = $action === 'approve' ? 'Approved' : 'Dismissed';

        db()->execute(
            "UPDATE alerts SET status=?, resolved_by=?, resolved_at=NOW() WHERE alert_id=?",
            [$newStatus, $payload['user_id'], $alertId]
        );
        Auth::log('RESOLVE_ALERT', 'alerts', $alertId, "Alert $newStatus by user (mobile app)", (int)$payload['user_id']);

        if ($action === 'approve') {
            $poNumber = $this->createAutoPO($alertId, (int)$payload['user_id']);
            if ($poNumber) {
                $this->json(['success' => true, 'message' => "Alert approved — Purchase Order $poNumber was auto-generated.", 'po_number' => $poNumber]);
            }
            $this->json(['success' => true, 'message' => 'Alert approved, but no Purchase Order was created — this item has no supplier assigned. Assign one via Inventory, then create the PO manually.']);
        }

        $this->json(['success' => true, 'message' => "Alert successfully $newStatus."]);
    }

    private function createAutoPO(int $alertId, int $userId): ?string {
        $alert = db()->fetchOne("SELECT * FROM alerts WHERE alert_id = ?", [$alertId]);
        if (!$alert || !$alert['item_id']) return null;

        $item = db()->fetchOne(
            "SELECT item_id, item_name, supplier_id, quantity, par_level, unit_price
             FROM inventory_items WHERE item_id = ?",
            [$alert['item_id']]
        );
        if (!$item || !$item['supplier_id']) return null;

        $supplier = db()->fetchOne("SELECT lead_time_days FROM suppliers WHERE supplier_id = ?", [$item['supplier_id']]);

        $orderQty = max(1, (int)$item['par_level'] - (int)$item['quantity']);
        $price    = (float)$item['unit_price'];

        $lastPO   = db()->fetchOne("SELECT po_number FROM purchase_orders ORDER BY po_id DESC LIMIT 1");
        $nextNum  = $lastPO ? ((int)substr($lastPO['po_number'], -4) + 1) : 1;
        $poNumber = 'PO-' . date('Y') . '-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);

        $leadDays         = (int)round((float)($supplier['lead_time_days'] ?? 3));
        $expectedDelivery = date('Y-m-d', strtotime("+{$leadDays} days"));

        db()->execute(
            "INSERT INTO purchase_orders
             (po_number, supplier_id, total_items, total_value, raised_by, po_date, expected_delivery, notes, status, approval_status)
             VALUES (?,?,?,?,?,CURDATE(),?,?,'Pending','Auto')",
            [
                $poNumber, $item['supplier_id'], $orderQty, $orderQty * $price,
                $userId, $expectedDelivery, "Auto-generated from alert: {$alert['title']}",
            ]
        );
        $poId = (int) db()->lastInsertId();

        db()->execute(
            "INSERT INTO purchase_order_items (po_id, item_id, quantity_ordered, unit_price) VALUES (?,?,?,?)",
            [$poId, $item['item_id'], $orderQty, $price]
        );

        Auth::log('AUTO_CREATE_PO', 'purchase_orders', $poId,
            "Auto-generated $poNumber from alert #$alertId ($orderQty x {$item['item_name']}) (mobile app)", $userId);

        return $poNumber;
    }

    private function runScan(int $userId): void {
        $created = 0;

        db()->execute(
            "UPDATE alerts a
             JOIN inventory_items i ON a.item_id = i.item_id
             SET a.status='Resolved', a.resolved_at=NOW()
             WHERE a.status='Active' AND a.alert_type IN ('Critical','Warning') AND i.status = 'In-Stock'"
        );

        $lowItems = db()->fetchAll(
            "SELECT i.item_id, i.item_name, i.category, i.quantity, i.par_level, i.location_id, i.status
             FROM inventory_items i WHERE i.status != 'In-Stock'"
        );

        foreach ($lowItems as $item) {
            $existing = db()->fetchOne("SELECT alert_id FROM alerts WHERE item_id=? AND status='Active' LIMIT 1", [$item['item_id']]);
            if ($existing) continue;

            $alertType = $item['status'] === 'Out of Stock' ? 'Critical' : 'Warning';
            $label     = "{$item['category']} - {$item['item_name']}";
            $title     = $item['status'] === 'Out of Stock' ? "$label — Out of Stock" : "LOW STOCK: $label";
            $desc      = "Current stock: {$item['quantity']} unit(s). Par level: {$item['par_level']} unit(s).";

            db()->execute(
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated) VALUES (?,?,?,?,?,1)",
                [$alertType, $title, $desc, $item['item_id'], $item['location_id']]
            );
            $created++;
        }

        $expiring = db()->fetchAll(
            "SELECT item_id, item_name, expiry_date, location_id, DATEDIFF(expiry_date, CURDATE()) AS days_left
             FROM inventory_items
             WHERE expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
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
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated) VALUES ('Info',?,?,?,?,1)",
                [$title, $desc, $e['item_id'], $e['location_id']]
            );
            $created++;
        }

        Auth::log('SCAN_ALERTS', 'alerts', 0, "Manual scan completed (mobile app) — $created alert(s) generated", $userId);

        $msg = $created > 0 ? "Scan complete — $created new alert(s) generated." : 'Scan complete — all stock levels are within acceptable range.';
        $this->json(['success' => true, 'message' => $msg, 'created' => $created]);
    }
}
