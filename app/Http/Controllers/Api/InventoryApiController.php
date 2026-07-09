<?php
// =============================================================
// 7NVENT - Inventory API Controller (mobile)
// =============================================================
// Same rules as InventoryController (web), same shared InventoryService
// for validation/alerts, same role checks — just JWT-guarded JSON in/out
// instead of session-guarded HTML/redirects.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';
require_once __DIR__ . '/../../../Support/StockStatus.php';
require_once __DIR__ . '/../../../Support/InventoryService.php';

class InventoryApiController extends ApiController {

    /** GET /api/inventory — list with the same filters the web index() supports. */
    public function index(): void {
        $this->requireAuth();
        $db = db();

        $search   = trim((string)($_GET['search']   ?? ''));
        $category = trim((string)($_GET['category'] ?? ''));
        $location = trim((string)($_GET['location'] ?? ''));
        $status   = trim((string)($_GET['status']   ?? ''));

        $sql = "SELECT i.*, l.location_name, s.supplier_name
                FROM inventory_items i
                JOIN locations l ON i.location_id = l.location_id
                LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (i.item_name LIKE ? OR l.location_name LIKE ? OR i.item_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($category) { $sql .= " AND i.category = ?";    $params[] = $category; }
        if ($location) { $sql .= " AND i.location_id = ?"; $params[] = $location; }
        if ($status)   { $sql .= " AND i.status = ?";      $params[] = $status;   }

        $sql .= " ORDER BY i.category, i.item_name";

        $this->json(['success' => true, 'items' => $db->fetchAll($sql, $params)]);
    }

    /** GET /api/inventory/detail?id=123 */
    public function detail(): void {
        $this->requireAuth();
        $itemId = (int)($_GET['id'] ?? 0);
        if (!$itemId) $this->jsonError('Item id is required.', 422);

        $item = db()->fetchOne(
            "SELECT i.*, l.location_name, s.supplier_name
             FROM inventory_items i
             JOIN locations l ON i.location_id = l.location_id
             LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
             WHERE i.item_id = ?",
            [$itemId]
        );
        if (!$item) $this->jsonError('Item not found.', 404);

        $this->json(['success' => true, 'item' => $item]);
    }

    /**
     * GET /api/inventory/lookup?code=XXXX — the mobile equivalent of the
     * QR Scanner's "look up what was just scanned" step. Same fallback
     * matching the web scanner uses: try the real item_code column first,
     * then the computed 7NV-XXXX code for items that never got a manual
     * code assigned.
     */
    public function lookup(): void {
        $this->requireAuth();
        $code = trim((string)($_GET['code'] ?? ''));
        if ($code === '') $this->jsonError('Code is required.', 422);

        $item = db()->fetchOne(
            "SELECT i.*, l.location_name,
                    COALESCE(NULLIF(i.item_code, ''), CONCAT('7NV-', LPAD(i.item_id, 4, '0'))) AS resolved_code
             FROM inventory_items i
             JOIN locations l ON i.location_id = l.location_id
             WHERE i.item_code = ? OR CONCAT('7NV-', LPAD(i.item_id, 4, '0')) = ?
             LIMIT 1",
            [$code, $code]
        );

        if (!$item) {
            $this->json(['success' => true, 'found' => false, 'scanned_code' => $code]);
        }

        $this->json(['success' => true, 'found' => true, 'item' => $item]);
    }

    /** POST /api/inventory/store — create. */
    public function store(): void {
        $payload = $this->requireRole('Inventory Manager', 'Housekeeping Manager');
        $body = $this->body();

        $data = InventoryService::extractItemData($body, (int)$payload['user_id']);

        $errors = InventoryService::validateItemData($data);
        if ($errors) $this->jsonError(implode(' ', $errors), 422);

        $status = StockStatus::determine($data['quantity'], $data['par_level']);

        db()->execute(
            "INSERT INTO inventory_items
             (item_name, item_code, category, location_id, supplier_id, quantity, par_level, unit_price, expiry_date, created_by, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['item_name'], $data['item_code'], $data['category'], $data['location_id'],
                $data['supplier_id'], $data['quantity'], $data['par_level'],
                $data['unit_price'], $data['expiry_date'], $data['created_by'], $status,
            ]
        );

        $itemId = (int) db()->lastInsertId();
        Auth::log('ADD_ITEM', 'inventory_items', $itemId, "Added item (mobile app): {$data['item_name']}", (int)$payload['user_id']);

        InventoryService::handleAlerts(
            $itemId, $status,
            $data['quantity'], $data['par_level'],
            $data['location_id'], $data['item_name'], $data['category']
        );

        $this->json([
            'success' => true,
            'message' => "Item '{$data['item_name']}' added successfully!",
            'item_id' => $itemId,
            'status'  => $status,
        ]);
    }

    /** POST /api/inventory/quick-add — same as store(), shaped for the QR-scan-then-register flow. */
    public function quickAdd(): void {
        $payload = $this->requireRole('Inventory Manager', 'Housekeeping Manager');
        $body = $this->body();

        $data = InventoryService::extractItemData($body, (int)$payload['user_id']);

        $errors = InventoryService::validateItemData($data);
        if ($errors) $this->jsonError(implode(' ', $errors), 422);

        $status = StockStatus::determine($data['quantity'], $data['par_level']);

        db()->execute(
            "INSERT INTO inventory_items
             (item_name, item_code, category, location_id, supplier_id, quantity, par_level, unit_price, expiry_date, created_by, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['item_name'], $data['item_code'], $data['category'], $data['location_id'],
                $data['supplier_id'], $data['quantity'], $data['par_level'],
                $data['unit_price'], $data['expiry_date'], $data['created_by'], $status,
            ]
        );

        $itemId = (int) db()->lastInsertId();
        Auth::log('ADD_ITEM', 'inventory_items', $itemId, "Added item via mobile QR quick-add: {$data['item_name']}", (int)$payload['user_id']);

        InventoryService::handleAlerts(
            $itemId, $status,
            $data['quantity'], $data['par_level'],
            $data['location_id'], $data['item_name'], $data['category']
        );

        $loc = db()->fetchOne("SELECT location_name FROM locations WHERE location_id = ?", [$data['location_id']]);

        $this->json([
            'success' => true,
            'message' => "Item '{$data['item_name']}' added successfully!",
            'item' => [
                'item_id'     => $itemId,
                'item_name'   => $data['item_name'],
                'item_code'   => $data['item_code'] ?: ('7NV-' . str_pad((string)$itemId, 4, '0', STR_PAD_LEFT)),
                'category'    => $data['category'],
                'quantity'    => $data['quantity'],
                'par_level'   => $data['par_level'],
                'unit_price'  => $data['unit_price'],
                'expiry_date' => $data['expiry_date'],
                'status'      => $status,
                'loc'         => $loc['location_name'] ?? '',
            ],
        ]);
    }

    /** POST /api/inventory/update */
    public function update(): void {
        $payload = $this->requireRole('Inventory Manager', 'Housekeeping Manager');
        $body = $this->body();

        $itemId = (int)($body['item_id'] ?? 0);
        if (!$itemId) $this->jsonError('Item id is required.', 422);

        $data = InventoryService::extractItemData($body);

        $errors = InventoryService::validateItemData($data, $itemId);
        if ($errors) $this->jsonError(implode(' ', $errors), 422);

        $status = StockStatus::determine($data['quantity'], $data['par_level']);

        db()->execute(
            "UPDATE inventory_items
             SET item_name=?, item_code=?, category=?, location_id=?, supplier_id=?,
                 quantity=?, par_level=?, unit_price=?, status=?, expiry_date=?
             WHERE item_id=?",
            [
                $data['item_name'], $data['item_code'], $data['category'], $data['location_id'], $data['supplier_id'],
                $data['quantity'], $data['par_level'], $data['unit_price'], $status,
                $data['expiry_date'], $itemId
            ]
        );

        InventoryService::handleAlerts($itemId, $status, $data['quantity'], $data['par_level'], $data['location_id'], $data['item_name'], $data['category']);

        Auth::log('UPDATE_ITEM', 'inventory_items', $itemId, "Updated item ID $itemId (mobile app) → status: $status", (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => 'Item updated successfully!', 'status' => $status]);
    }

    /** POST /api/inventory/delete */
    public function delete(): void {
        $payload = $this->requireRole('Inventory Manager');
        $body = $this->body();
        $itemId = (int)($body['item_id'] ?? 0);
        if (!$itemId) $this->jsonError('Item id is required.', 422);

        db()->execute(
            "UPDATE alerts SET status='Resolved', resolved_at=NOW()
             WHERE item_id=? AND status='Active'",
            [$itemId]
        );
        db()->execute("DELETE FROM inventory_items WHERE item_id = ?", [$itemId]);
        Auth::log('DELETE_ITEM', 'inventory_items', $itemId, "Deleted inventory item ID: $itemId (mobile app)", (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => 'Item deleted successfully!']);
    }

    /** GET /api/inventory/meta — categories, locations, suppliers for building the add/edit form dropdowns. */
    public function meta(): void {
        $this->requireAuth();
        $this->json([
            'success'    => true,
            'categories' => InventoryService::ALLOWED_CATEGORIES,
            'locations'  => db()->fetchAll("SELECT * FROM locations ORDER BY location_name"),
            'suppliers'  => db()->fetchAll("SELECT * FROM suppliers WHERE status = 'Active' ORDER BY supplier_name"),
        ]);
    }
}
