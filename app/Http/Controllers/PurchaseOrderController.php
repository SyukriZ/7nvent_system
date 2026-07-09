<?php
// =============================================================
// 7NVENT - Purchase Order Controller
// =============================================================

require_once __DIR__ . '/../../Auth.php';

class PurchaseOrderController {

    public function index(): void {
        Auth::required();

        $status     = clean($_GET['status'] ?? '');
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

        $orders = db()->fetchAll($sql, $params);
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/purchase-orders/index.php';
    }

    public function create(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Procurement Officer')) {
            redirect('/purchase-orders', 'You do not have permission.', 'error');
        }
        $suppliers = db()->fetchAll("SELECT * FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
        $items = db()->fetchAll("SELECT i.*, l.location_name FROM inventory_items i JOIN locations l ON i.location_id = l.location_id ORDER BY i.item_name");
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/purchase-orders/create.php';
    }

    public function store(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Procurement Officer')) {
            redirect('/purchase-orders', 'Access denied.', 'error');
        }

        $supplierId       = (int)($_POST['supplier_id'] ?? 0);
        $poDate           = clean($_POST['po_date'] ?? date('Y-m-d'));
        $expectedDelivery = clean($_POST['expected_delivery'] ?? '');
        $notes            = clean($_POST['notes'] ?? '');

        // Line items posted as parallel arrays: item_id[] and qty[]
        $itemIds = $_POST['item_id'] ?? [];
        $qtys    = $_POST['qty'] ?? [];

        // Build validated line items + compute totals SERVER-SIDE (never trust client)
        $lines      = [];
        $totalItems = 0;
        $totalValue = 0.0;
        foreach ($itemIds as $i => $rawId) {
            $iid = (int)$rawId;
            $q   = (int)($qtys[$i] ?? 0);
            if ($iid <= 0 || $q <= 0) continue;
            $row = db()->fetchOne("SELECT unit_price FROM inventory_items WHERE item_id = ?", [$iid]);
            if (!$row) continue;
            $price   = (float)$row['unit_price'];
            $lines[] = ['item_id' => $iid, 'qty' => $q, 'price' => $price];
            $totalItems += $q;
            $totalValue += $q * $price;
        }

        if ($supplierId <= 0) {
            redirect('/purchase-orders/create', 'Please select a supplier.', 'error');
        }
        if (empty($lines)) {
            redirect('/purchase-orders/create', 'Please add at least one item with a quantity.', 'error');
        }

        // Generate PO number
        $lastPO   = db()->fetchOne("SELECT po_number FROM purchase_orders ORDER BY po_id DESC LIMIT 1");
        $nextNum  = $lastPO ? ((int)substr($lastPO['po_number'], -4) + 1) : 1;
        $poNumber = 'PO-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $userId = Auth::user()['user_id'];
        db()->execute(
            "INSERT INTO purchase_orders (po_number, supplier_id, total_items, total_value, raised_by, po_date, expected_delivery, notes, status, approval_status)
             VALUES (?,?,?,?,?,?,?,?,'Pending','Manual')",
            [$poNumber, $supplierId, $totalItems, $totalValue, $userId, $poDate, $expectedDelivery ?: null, $notes]
        );
        $poId = (int)db()->lastInsertId();

        // Insert each line item (subtotal is a generated column — do NOT insert it)
        foreach ($lines as $ln) {
            db()->execute(
                "INSERT INTO purchase_order_items (po_id, item_id, quantity_ordered, unit_price)
                 VALUES (?,?,?,?)",
                [$poId, $ln['item_id'], $ln['qty'], $ln['price']]
            );
        }

        Auth::log('CREATE_PO', 'purchase_orders', $poId,
            "Created PO: $poNumber ($totalItems items, RM " . number_format($totalValue, 2) . ")");
        redirect('/purchase-orders', "Purchase Order $poNumber created successfully!", 'success');
    }

    public function view(): void {
        Auth::required();
        $poId = (int)($_GET['id'] ?? 0);
        $order = db()->fetchOne(
            "SELECT po.*, s.supplier_name, s.contact_person, s.phone, s.email as supplier_email, u.full_name as raised_by_name
             FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.supplier_id
             JOIN users u ON po.raised_by = u.user_id
             WHERE po.po_id = ?",
            [$poId]
        );
        if (!$order) redirect('/purchase-orders', 'Purchase Order not found.', 'error');

        // Get approval/status change date from audit logs
        $approvalLog = db()->fetchOne(
            "SELECT al.timestamp, u.full_name FROM audit_logs al
             JOIN users u ON al.user_id = u.user_id
             WHERE al.target_table = 'purchase_orders' AND al.target_id = ?
             ORDER BY al.timestamp DESC LIMIT 1",
            [$poId]
        );
        $order['approved_at']   = $approvalLog['timestamp'] ?? $order['po_date'];
        $order['approved_by']   = $approvalLog['full_name'] ?? $order['raised_by_name'];

        // Line items for this PO (empty for older header-level POs)
        $lineItems = db()->fetchAll(
            "SELECT poi.*, i.item_name, i.category, i.item_code
             FROM purchase_order_items poi
             JOIN inventory_items i ON poi.item_id = i.item_id
             WHERE poi.po_id = ?
             ORDER BY poi.poi_id",
            [$poId]
        );

        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/purchase-orders/view.php';
    }

    public function update(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Procurement Officer')) {
            redirect('/purchase-orders', 'You do not have permission to update purchase orders.', 'error');
        }
        $poId   = (int)($_POST['po_id'] ?? 0);
        $status = clean($_POST['status'] ?? '');

        $allowed = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];
        if (!in_array($status, $allowed)) {
            redirect('/purchase-orders', 'Invalid status.', 'error');
        }

        db()->execute("UPDATE purchase_orders SET status = ? WHERE po_id = ?", [$status, $poId]);
        Auth::log('UPDATE_PO', 'purchase_orders', $poId, "PO status updated to: $status");

        // If delivered, update inventory
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

        redirect('/purchase-orders', "PO status updated to '$status'.", 'success');
    }
}