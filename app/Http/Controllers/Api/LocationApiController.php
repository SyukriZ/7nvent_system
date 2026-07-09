<?php
// =============================================================
// 7NVENT - Location API Controller (mobile)
// =============================================================
// Mirrors LocationController exactly — same query, same capacity update
// rule, same role check — just JWT-guarded JSON instead of session-guarded
// HTML/redirects.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class LocationApiController extends ApiController {

    /** GET /api/locations */
    public function index(): void {
        $this->requireAuth();
        $db = db();

        $locations = $db->fetchAll(
            "SELECT *, ROUND(current_items/NULLIF(capacity,0)*100,0) as capacity_pct
             FROM locations ORDER BY location_name"
        );
        $totalItems = $db->fetchOne("SELECT SUM(current_items) as total FROM locations")['total'] ?? 0;
        $lowStockCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM locations WHERE status='Low Stock'")['cnt'] ?? 0;

        $this->json([
            'success'         => true,
            'locations'       => $locations,
            'total_items'     => (int) $totalItems,
            'low_stock_count' => (int) $lowStockCount,
        ]);
    }

    /** POST /api/locations/update — capacity only, same as web. */
    public function update(): void {
        $payload = $this->requireRole('Inventory Manager');
        $body = $this->body();

        $locationId = (int)($body['location_id'] ?? 0);
        $capacity   = (int)($body['capacity'] ?? 0);

        if ($capacity < 0) {
            $this->jsonError('Capacity cannot be negative.', 422);
        }
        if (!db()->fetchOne("SELECT location_id FROM locations WHERE location_id = ?", [$locationId])) {
            $this->jsonError('Location not found.', 404);
        }

        db()->execute("UPDATE locations SET capacity=? WHERE location_id=?", [$capacity, $locationId]);
        Auth::log('UPDATE_LOCATION', 'locations', $locationId, 'Updated location capacity (mobile app)', (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => 'Location updated successfully!']);
    }
}
