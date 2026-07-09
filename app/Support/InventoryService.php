<?php
// =============================================================
// 7NVENT - Inventory Service
// =============================================================
// Extracted from InventoryController so the same validation rules and
// alert-engine logic are shared by the web controller AND the new
// InventoryApiController (mobile). Before this, both would have needed
// their own copy of validateItemData()/handleAlerts() — two places that
// could silently drift out of sync every time one got a rule change and
// the other didn't. This is the single source of truth now.

require_once __DIR__ . '/StockStatus.php';

class InventoryService {

    public const ALLOWED_CATEGORIES = ['Toiletries', 'F&B', 'Linens', 'Cleaning', 'Minibar'];

    /**
     * Server-side validation — form min= attributes are client-only and
     * trivially bypassed with a raw POST/JSON body, so every rule here is
     * re-checked before anything touches the database.
     */
    public static function validateItemData(array $data, ?int $excludeItemId = null): array {
        $errors = [];

        if (($data['item_name'] ?? '') === '') {
            $errors[] = 'Item name is required.';
        } elseif (mb_strlen($data['item_name']) > 150) {
            $errors[] = 'Item name is too long (max 150 characters).';
        }

        // item_code is optional (nullable in the DB) — it's the SKU/barcode used
        // by the QR Scanner, so it only needs a length check plus a uniqueness
        // check when one is actually supplied.
        if (($data['item_code'] ?? null) !== null) {
            if (mb_strlen($data['item_code']) > 20) {
                $errors[] = 'Item code is too long (max 20 characters).';
            } else {
                $dupSql    = "SELECT item_id FROM inventory_items WHERE item_code = ?";
                $dupParams = [$data['item_code']];
                if ($excludeItemId) {
                    $dupSql      .= " AND item_id != ?";
                    $dupParams[]  = $excludeItemId;
                }
                if (db()->fetchOne($dupSql, $dupParams)) {
                    $errors[] = "Item code '{$data['item_code']}' is already used by another item.";
                }
            }
        }

        if (!in_array($data['category'] ?? '', self::ALLOWED_CATEGORIES, true)) {
            $errors[] = 'Invalid category selected.';
        }

        if ((int)($data['location_id'] ?? 0) <= 0) {
            $errors[] = 'A valid location is required.';
        }

        if ((int)($data['quantity'] ?? 0) < 0) {
            $errors[] = 'Quantity cannot be negative.';
        }

        if ((int)($data['par_level'] ?? 0) < 0) {
            $errors[] = 'Par level cannot be negative.';
        }

        if ((float)($data['unit_price'] ?? 0) < 0) {
            $errors[] = 'Unit price cannot be negative.';
        }

        if (!empty($data['expiry_date'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $data['expiry_date']);
            if (!$d || $d->format('Y-m-d') !== $data['expiry_date']) {
                $errors[] = 'Expiry date is invalid.';
            }
        }

        return $errors;
    }

    /** Create or resolve an alert to match the item's current stock status. */
    public static function handleAlerts(
        int    $itemId,
        string $status,
        int    $qty,
        int    $par,
        int    $locationId,
        string $itemName,
        string $category = ''
    ): void {

        if ($status === StockStatus::IN_STOCK) {
            db()->execute(
                "UPDATE alerts
                 SET status='Resolved', resolved_at=NOW()
                 WHERE item_id=? AND status='Active' AND alert_type IN ('Critical','Warning')",
                [$itemId]
            );
            return;
        }

        $alertType = $status === StockStatus::OUT_OF_STOCK ? 'Critical' : 'Warning';
        $label     = $category ? "$category - $itemName" : $itemName;
        $title     = $status === StockStatus::OUT_OF_STOCK
            ? "$label — Out of Stock"
            : "LOW STOCK: $label";
        $desc = "Current stock: $qty unit(s). Par level: $par unit(s). Immediate restocking required.";

        $existing = db()->fetchOne(
            "SELECT alert_id, alert_type FROM alerts
             WHERE item_id=? AND status='Active'
             ORDER BY triggered_at DESC LIMIT 1",
            [$itemId]
        );

        if (!$existing) {
            db()->execute(
                "INSERT INTO alerts (alert_type, title, description, item_id, location_id, auto_generated)
                 VALUES (?,?,?,?,?,1)",
                [$alertType, $title, $desc, $itemId, $locationId]
            );
        } elseif ($existing['alert_type'] !== $alertType) {
            db()->execute(
                "UPDATE alerts SET alert_type=?, title=?, description=? WHERE alert_id=?",
                [$alertType, $title, $desc, $existing['alert_id']]
            );
        }
    }

    /** Build the same request-shape $data array both store()/update() construct, from any assoc source. */
    public static function extractItemData(array $src, ?int $createdBy = null): array {
        $data = [
            'item_name'   => clean((string)($src['item_name']   ?? '')),
            'item_code'   => clean((string)($src['item_code']   ?? '')) ?: null,
            // Same fix as InventoryController::store()/quickAddAjax(): don't
            // htmlspecialchars() a value that's about to be strictly
            // whitelist-checked against ALLOWED_CATEGORIES — 'F&B' becomes
            // 'F&amp;B' otherwise and can never match, so every F&B item
            // failed validation with "Invalid category selected" no matter
            // what was actually selected.
            'category'    => trim((string)($src['category']    ?? '')),
            'location_id' => (int)($src['location_id'] ?? 0),
            'supplier_id' => (int)($src['supplier_id'] ?? 0) ?: null,
            'quantity'    => (int)($src['quantity']    ?? 0),
            'par_level'   => (int)($src['par_level']   ?? 0),
            'unit_price'  => (float)($src['unit_price'] ?? 0),
            'expiry_date' => clean((string)($src['expiry_date'] ?? '')) ?: null,
        ];
        if ($createdBy !== null) {
            $data['created_by'] = $createdBy;
        }
        return $data;
    }
}
