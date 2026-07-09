<?php
// =============================================================
// 7NVENT - Purchase Order API Controller (mobile)
// =============================================================
// Mirrors PurchaseOrderController exactly — same filters, same server-side
// total computation (never trust client-sent totals), same PO numbering,
// same auto-inventory-update on Delivered.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class PurchaseOrderApiController extends ApiController {

    /** GET /api/purchase-orders — list with the same filters the web index() supports. */
    public function index(): void {
        $this->requireAuth();

        $status     = trim((string)($_GET['status']   ?? ''));
        $supplierId = (int)($_GET['supplier'] ?? 0);

        $sql = "SELECT po.*, s.supplier_name, u.full_name as raised_by_name
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id = s.supplier_id
                JOIN users u ON po.raised_by = u.user_id
                WHERE 1=1";
        $params = [];
        if ($status)     { $sql .= " AND po.status = ?";      $params[] = $status; }
        if ($supplierId) { $sql .= " AND po.supplier_id = ?"; $params[] = $supplierId; }
        $sql .= " ORDER BY po.po_date DESC";

        $this->json(['success' => true, 'orders' => db()->fetchAll($sql, $params)]);
    }

    /** GET /api/purchase-orders/meta — suppliers + items for the create form, same lists as web create(). */
    public function meta(): void {
        $this->requireAuth();
        $this->json([
            'success'   => true,
            'suppliers' => db()->fetchAll("SELECT * FROM suppliers WHERE status = 'Active' ORDER BY supplier_name"),
            'items'     => db()->fetchAll(
                "SELECT i.*, l.location_name FROM inventory_items i
                 JOIN locations l ON i.location_id = l.location_id ORDER BY i.item_name"
            ),
        ]);
    }

    /** GET /api/purchase-orders/view?id=123 */
    public function view(): void {
        $this->requireAuth();
        $poId = (int)($_GET['id'] ?? 0);
        if (!$poId) $this->jsonError('Purchase Order id is required.', 422);

        $order = db()->fetchOne(
            "SELECT po.*, s.supplier_name, s.contact_person, s.phone, s.email as supplier_email, u.full_name as raised_by_name
             FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.supplier_id
             JOIN users u ON po.raised_by = u.user_id
             WHERE po.po_id = ?",
            [$poId]
        );
        if (!$order) $this->jsonError('Purchase Order not found.', 404);

        $approvalLog = db()->fetchOne(
            "SELECT al.timestamp, u.full_name FROM audit_logs al
             JOIN users u ON al.user_id = u.user_id
             WHERE al.target_table = 'purchase_orders' AND al.target_id = ?
             ORDER BY al.timestamp DESC LIMIT 1",
            [$poId]
        );
        $order['approved_at'] = $approvalLog['timestamp'] ?? $order['po_date'];
        $order['approved_by'] = $approvalLog['full_name'] ?? $order['raised_by_name'];

        $lineItems = db()->fetchAll(
            "SELECT poi.*, i.item_name, i.category, i.item_code
             FROM purchase_order_items poi
             JOIN inventory_items i ON poi.item_id = i.item_id
             WHERE poi.po_id = ? ORDER BY poi.poi_id",
            [$poId]
        );

        $this->json(['success' => true, 'order' => $order, 'line_items' => $lineItems]);
    }

    /**
     * POST /api/purchase-orders/store — expects:
     *   { supplier_id, po_date, expected_delivery, notes, lines: [{item_id, qty}, ...] }
     * (the web form posts parallel item_id[]/qty[] arrays from an HTML
     * table; JSON gives us a proper array of objects instead — the
     * validation and total computation below are otherwise identical).
     */
    public function store(): void {
        $payload = $this->requireRole('Inventory Manager', 'Procurement Officer');
        $body = $this->body();

        $supplierId       = (int)($body['supplier_id'] ?? 0);
        $poDate           = trim((string)($body['po_date'] ?? date('Y-m-d')));
        $expectedDelivery = trim((string)($body['expected_delivery'] ?? ''));
        $notes            = trim((string)($body['notes'] ?? ''));
        $rawLines         = is_array($body['lines'] ?? null) ? $body['lines'] : [];

        $lines = [];
        $totalItems = 0;
        $totalValue = 0.0;
        foreach ($rawLines as $line) {
            $iid = (int)($line['item_id'] ?? 0);
            $q   = (int)($line['qty'] ?? 0);
            if ($iid <= 0 || $q <= 0) continue;
            $row = db()->fetchOne("SELECT unit_price FROM inventory_items WHERE item_id = ?", [$iid]);
            if (!$row) continue;
            $price = (float)$row['unit_price'];
            $lines[] = ['item_id' => $iid, 'qty' => $q, 'price' => $price];
            $totalItems += $q;
            $totalValue += $q * $price;
        }

        if ($supplierId <= 0) $this->jsonError('Please select a supplier.', 422);
        if (empty($lines))    $this->jsonError('Please add at least one item with a quantity.', 422);

        $lastPO   = db()->fetchOne("SELECT po_number FROM purchase_orders ORDER BY po_id DESC LIMIT 1");
        $nextNum  = $lastPO ? ((int)substr($lastPO['po_number'], -4) + 1) : 1;
        $poNumber = 'PO-' . date('Y') . '-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);

        $userId = (int)$payload['user_id'];
        db()->execute(
            "INSERT INTO purchase_orders (po_number, supplier_id, total_items, total_value, raised_by, po_date, expected_delivery, notes, status, approval_status)
             VALUES (?,?,?,?,?,?,?,?,'Pending','Manual')",
            [$poNumber, $supplierId, $totalItems, $totalValue, $userId, $poDate, $expectedDelivery ?: null, $notes]
        );
        $poId = (int) db()->lastInsertId();

        foreach ($lines as $ln) {
            db()->execute(
                "INSERT INTO purchase_order_items (po_id, item_id, quantity_ordered, unit_price) VALUES (?,?,?,?)",
                [$poId, $ln['item_id'], $ln['qty'], $ln['price']]
            );
        }

        Auth::log('CREATE_PO', 'purchase_orders', $poId,
            "Created PO (mobile app): $poNumber ($totalItems items, RM " . number_format($totalValue, 2) . ")", $userId);

        $this->json([
            'success'   => true,
            'message'   => "Purchase Order $poNumber created successfully!",
            'po_id'     => $poId,
            'po_number' => $poNumber,
        ]);
    }

    /** POST /api/purchase-orders/update — status change, same allowed list + inventory bump on Delivered. */
    public function update(): void {
        $payload = $this->requireRole('Inventory Manager', 'Procurement Officer');
        $body = $this->body();

        $poId   = (int)($body['po_id'] ?? 0);
        $status = trim((string)($body['status'] ?? ''));

        $allowed = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->jsonError('Invalid status.', 422);
        }
        if (!$poId || !db()->fetchOne("SELECT po_id FROM purchase_orders WHERE po_id = ?", [$poId])) {
            $this->jsonError('Purchase Order not found.', 404);
        }

        db()->execute("UPDATE purchase_orders SET status = ? WHERE po_id = ?", [$status, $poId]);
        Auth::log('UPDATE_PO', 'purchase_orders', $poId, "PO status updated to: $status (mobile app)", (int)$payload['user_id']);

        if ($status === 'Delivered') {
            $items = db()->fetchAll(
                "SELECT poi.item_id, poi.quantity_ordered FROM purchase_order_items poi WHERE poi.po_id = ?",
                [$poId]
            );
            foreach ($items as $item) {
                db()->execute(
                    "UPDATE inventory_items SET quantity = quantity + ?, status = CASE WHEN quantity + ? > par_level THEN 'In-Stock' ELSE status END WHERE item_id = ?",
                    [$item['quantity_ordered'], $item['quantity_ordered'], $item['item_id']]
                );
            }
        }

        $this->json(['success' => true, 'message' => "PO status updated to '$status'."]);
    }
}
