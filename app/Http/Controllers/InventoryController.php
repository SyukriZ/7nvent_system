<?php
// =============================================================
// 7NVENT - Inventory Controller
// =============================================================

require_once __DIR__ . '/../../Auth.php';
require_once __DIR__ . '/../../Support/StockStatus.php';
require_once __DIR__ . '/../../Support/InventoryService.php';

class InventoryController {

    public function index(): void {
        Auth::required();
        $db = db();

        $search   = clean($_GET['search']   ?? '');
        // Not clean() — this gets bound as a prepared-statement parameter
        // (never rendered into HTML directly), so htmlspecialchars() only
        // does harm here: filtering by "F&B" would clean() into "F&amp;B"
        // and match zero rows against the real 'F&B' values in the DB,
        // making the category filter silently return an empty list.
        $category = trim($_GET['category'] ?? '');
        $location = clean($_GET['location'] ?? '');
        $status   = clean($_GET['status']   ?? '');

        $sql = "SELECT i.*, l.location_name, s.supplier_name
                FROM inventory_items i
                JOIN locations l ON i.location_id = l.location_id
                LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (i.item_name LIKE ? OR l.location_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($category) { $sql .= " AND i.category = ?";    $params[] = $category; }
        if ($location) { $sql .= " AND i.location_id = ?"; $params[] = $location; }
        if ($status)   { $sql .= " AND i.status = ?";      $params[] = $status;   }

        $sql .= " ORDER BY i.category, i.item_name";
        $items     = $db->fetchAll($sql, $params);
        $locations = $db->fetchAll("SELECT * FROM locations ORDER BY location_name");
        $user      = Auth::user();

        // ── FIFO Queue data ───────────────────────────────────────────
        $fifoItems = $db->fetchAll(
            "SELECT i.item_name, i.category, l.location_name, i.quantity,
                    i.expiry_date, DATEDIFF(i.expiry_date, CURDATE()) AS days_left
             FROM inventory_items i
             JOIN locations l ON i.location_id = l.location_id
             WHERE i.expiry_date IS NOT NULL
             ORDER BY i.expiry_date ASC"
        );
        $totalPerishable = count($fifoItems);
        $expiredCount    = 0;
        foreach ($fifoItems as $f) {
            if ((int)$f['days_left'] < 0) $expiredCount++;
        }
        $fifoCompliance = $totalPerishable > 0
            ? round((($totalPerishable - $expiredCount) / $totalPerishable) * 100)
            : 100;

        require_once __DIR__ . '/../../../resources/views/inventory/index.php';
    }

    public function create(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            redirect('/inventory', 'You do not have permission to add items.', 'error');
        }
        $locations  = db()->fetchAll("SELECT * FROM locations WHERE status != 'Low Stock' ORDER BY location_name");
        $suppliers  = db()->fetchAll("SELECT * FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
        $prefillName = clean($_GET['name'] ?? '');  // pre-fill from QR scanner lookup
        $prefillCode = clean($_GET['code'] ?? '');  // pre-fill item_code from an unrecognized QR/barcode scan
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/inventory/create.php';
    }

    public function store(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            redirect('/inventory', 'Access denied.', 'error');
        }

        $data = [
            'item_name'   => clean($_POST['item_name']   ?? ''),
            'item_code'   => clean($_POST['item_code']   ?? '') ?: null,
            // Bug fix: this used to be clean($_POST['category']) — clean()
            // runs htmlspecialchars(), which turns 'F&B' into 'F&amp;B'
            // before InventoryService::validateItemData() ever checks it
            // against ALLOWED_CATEGORIES (which contains the literal 'F&B').
            // Every "F&B" submission failed with "Invalid category selected"
            // regardless of what was actually picked in the dropdown, since
            // the mangled value could never match. Category is a closed
            // 5-value enum validated by strict in_array() right after this —
            // trim() is all it needs; htmlspecialchars() escaping belongs at
            // output time, not before a value comparison.
            'category'    => trim($_POST['category']    ?? ''),
            'location_id' => (int)($_POST['location_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0) ?: null,
            'quantity'    => (int)($_POST['quantity']    ?? 0),
            'par_level'   => (int)($_POST['par_level']   ?? 0),
            'unit_price'  => (float)($_POST['unit_price'] ?? 0),
            'expiry_date' => clean($_POST['expiry_date'] ?? '') ?: null,
            'created_by'  => Auth::user()['user_id'],
        ];

        $errors = InventoryService::validateItemData($data);
        if ($errors) {
            redirect('/inventory/create', implode(' ', $errors), 'error');
        }

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

        $itemId = (int)db()->lastInsertId();
        Auth::log('ADD_ITEM', 'inventory_items', $itemId, "Added item: {$data['item_name']}");

        InventoryService::handleAlerts(
            $itemId, $status,
            $data['quantity'], $data['par_level'],
            $data['location_id'], $data['item_name'], $data['category']
        );

        redirect('/inventory', "Item '{$data['item_name']}' added successfully!", 'success');
    }

    // Same create logic as store(), but returns JSON instead of redirecting —
    // used by the QR Scanner's inline "Add Product" quick-add form so the user
    // never has to leave the scanner page to register an item.
    public function quickAddAjax(): void {
        Auth::required();
        header('Content-Type: application/json');

        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to add items.']);
            return;
        }

        $data = [
            'item_name'   => clean($_POST['item_name']   ?? ''),
            'item_code'   => clean($_POST['item_code']   ?? '') ?: null,
            // Bug fix: this used to be clean($_POST['category']) — clean()
            // runs htmlspecialchars(), which turns 'F&B' into 'F&amp;B'
            // before InventoryService::validateItemData() ever checks it
            // against ALLOWED_CATEGORIES (which contains the literal 'F&B').
            // Every "F&B" submission failed with "Invalid category selected"
            // regardless of what was actually picked in the dropdown, since
            // the mangled value could never match. Category is a closed
            // 5-value enum validated by strict in_array() right after this —
            // trim() is all it needs; htmlspecialchars() escaping belongs at
            // output time, not before a value comparison.
            'category'    => trim($_POST['category']    ?? ''),
            'location_id' => (int)($_POST['location_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0) ?: null,
            'quantity'    => (int)($_POST['quantity']    ?? 0),
            'par_level'   => (int)($_POST['par_level']   ?? 0),
            'unit_price'  => (float)($_POST['unit_price'] ?? 0),
            'expiry_date' => clean($_POST['expiry_date'] ?? '') ?: null,
            'created_by'  => Auth::user()['user_id'],
        ];

        $errors = InventoryService::validateItemData($data);
        if ($errors) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            return;
        }

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

        $itemId = (int)db()->lastInsertId();
        Auth::log('ADD_ITEM', 'inventory_items', $itemId, "Added item via QR Scanner quick-add: {$data['item_name']}");

        InventoryService::handleAlerts(
            $itemId, $status,
            $data['quantity'], $data['par_level'],
            $data['location_id'], $data['item_name'], $data['category']
        );

        $loc = db()->fetchOne("SELECT location_name FROM locations WHERE location_id = ?", [$data['location_id']]);

        echo json_encode([
            'success' => true,
            'message' => "Item '{$data['item_name']}' added successfully!",
            'item' => [
                'item_id'     => $itemId,
                'item_name'   => $data['item_name'],
                // Same fallback the QR Scanner list itself uses, so a fresh
                // item with no manually-entered code still gets a scannable one.
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

    public function edit(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            redirect('/inventory', 'You do not have permission to edit items.', 'error');
        }
        $itemId = (int)($_GET['id'] ?? 0);
        if (!$itemId) redirect('/inventory', 'Item not found.', 'error');

        $item = db()->fetchOne(
            "SELECT i.*, l.location_name FROM inventory_items i
             JOIN locations l ON i.location_id = l.location_id
             WHERE i.item_id = ?",
            [$itemId]
        );
        if (!$item) redirect('/inventory', 'Item not found.', 'error');

        $locations = db()->fetchAll("SELECT * FROM locations ORDER BY location_name");
        $suppliers = db()->fetchAll("SELECT * FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/inventory/edit.php';
    }

    public function update(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            redirect('/inventory', 'You do not have permission to edit items.', 'error');
        }
        $itemId = (int)($_POST['item_id'] ?? 0);
        if (!$itemId) redirect('/inventory', 'Item not found.', 'error');

        $data = [
            'item_name'   => clean($_POST['item_name']   ?? ''),
            'item_code'   => clean($_POST['item_code']   ?? '') ?: null,
            // Bug fix: this used to be clean($_POST['category']) — clean()
            // runs htmlspecialchars(), which turns 'F&B' into 'F&amp;B'
            // before InventoryService::validateItemData() ever checks it
            // against ALLOWED_CATEGORIES (which contains the literal 'F&B').
            // Every "F&B" submission failed with "Invalid category selected"
            // regardless of what was actually picked in the dropdown, since
            // the mangled value could never match. Category is a closed
            // 5-value enum validated by strict in_array() right after this —
            // trim() is all it needs; htmlspecialchars() escaping belongs at
            // output time, not before a value comparison.
            'category'    => trim($_POST['category']    ?? ''),
            'location_id' => (int)($_POST['location_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0) ?: null,
            'quantity'    => (int)($_POST['quantity']    ?? 0),
            'par_level'   => (int)($_POST['par_level']   ?? 0),
            'unit_price'  => (float)($_POST['unit_price'] ?? 0),
            'expiry_date' => clean($_POST['expiry_date'] ?? '') ?: null,
        ];

        $errors = InventoryService::validateItemData($data, $itemId);
        if ($errors) {
            redirect('/inventory/edit?id=' . $itemId, implode(' ', $errors), 'error');
        }

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

        Auth::log('UPDATE_ITEM', 'inventory_items', $itemId, "Updated item ID $itemId → status: $status");
        redirect('/inventory', 'Item updated successfully!', 'success');
    }

    // validateItemData() and handleAlerts() now live in InventoryService
    // (app/Support/InventoryService.php) so the web controller and the new
    // /api/inventory mobile controller share one copy of this logic instead
    // of each carrying its own that could quietly drift apart over time.

    // ── AJAX: Upload / replace a product photo for an existing item ──
    // Used by the QR Scanner's "Generate QR" grid so a real photo can be
    // attached to each item without leaving that page. The photo is what the
    // public /product/view page (linked from the printed QR) actually shows —
    // before this, that page had nothing to display but a category icon.
    public function uploadImage(): void {
        Auth::required();
        header('Content-Type: application/json');

        if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to upload item photos.']);
            return;
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        if (!$itemId) {
            echo json_encode(['success' => false, 'message' => 'Missing item.']);
            return;
        }

        // Defensive check: this endpoint depends on the `image_path` column
        // added by database/add_item_image.sql. If that migration hasn't
        // been run yet, the SELECT below throws a raw SQL error — which
        // previously surfaced to the user as a generic, unhelpful "Network
        // error" (the PHP error output isn't valid JSON, so the frontend's
        // r.json() call fails and its .catch() shows that fallback message).
        // Catching it here and returning a real explanation instead.
        try {
            $columnCheck = db()->fetchOne("SHOW COLUMNS FROM inventory_items LIKE 'image_path'");
        } catch (\Throwable $e) {
            $columnCheck = null;
        }
        if (!$columnCheck) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database not ready: run database/add_item_image.sql in phpMyAdmin first (adds the image_path column), then try uploading again.',
            ]);
            return;
        }

        $item = db()->fetchOne("SELECT item_id, image_path FROM inventory_items WHERE item_id = ?", [$itemId]);
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            return;
        }

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image received.']);
            return;
        }

        $file = $_FILES['image'];

        // 5MB cap — plenty for a phone photo, small enough to keep the
        // uploads folder and the public page's load time sane.
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image too large (max 5MB).']);
            return;
        }

        // Verify it's actually an image (not just a renamed file — extension
        // alone is not trustworthy) and derive the extension from the real
        // detected type rather than trusting the client's filename.
        $imageInfo = @getimagesize($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$imageInfo || !isset($allowed[$imageInfo['mime']])) {
            echo json_encode(['success' => false, 'message' => 'File must be a JPG, PNG, or WEBP image.']);
            return;
        }
        $ext = $allowed[$imageInfo['mime']];

        $uploadDir = __DIR__ . '/../../../public/uploads/items';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Old photo (any extension) removed first so items don't accumulate
        // orphaned files across repeated re-uploads.
        if (!empty($item['image_path'])) {
            $oldFile = __DIR__ . '/../../../public/' . $item['image_path'];
            if (is_file($oldFile)) @unlink($oldFile);
        }

        $filename   = 'item_' . $itemId . '_' . time() . '.' . $ext;
        $relPath    = 'uploads/items/' . $filename;
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Could not save the uploaded file.']);
            return;
        }

        db()->execute("UPDATE inventory_items SET image_path = ? WHERE item_id = ?", [$relPath, $itemId]);
        Auth::log('UPDATE_ITEM_IMAGE', 'inventory_items', $itemId, "Uploaded/replaced product photo for item ID $itemId");

        echo json_encode([
            'success'    => true,
            'message'    => 'Photo uploaded.',
            'image_path' => $relPath,
            'image_url'  => APP_URL . '/' . $relPath,
        ]);
    }

    public function delete(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager')) {
            redirect('/inventory', 'Only Inventory Manager can delete items.', 'error');
        }
        $itemId = (int)($_POST['item_id'] ?? 0);

        db()->execute(
            "UPDATE alerts SET status='Resolved', resolved_at=NOW()
             WHERE item_id=? AND status='Active'",
            [$itemId]
        );
        db()->execute("DELETE FROM inventory_items WHERE item_id = ?", [$itemId]);
        Auth::log('DELETE_ITEM', 'inventory_items', $itemId, "Deleted inventory item ID: $itemId");
        redirect('/inventory', 'Item deleted successfully!', 'success');
    }

}