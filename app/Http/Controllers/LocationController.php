<?php
require_once __DIR__ . '/../../Auth.php';

class LocationController {
    public function index(): void {
        Auth::required();
        $locations = db()->fetchAll("SELECT *, ROUND(current_items/NULLIF(capacity,0)*100,0) as capacity_pct FROM locations ORDER BY location_name");
        $totalItems = db()->fetchOne("SELECT SUM(current_items) as total FROM locations")['total'] ?? 0;
        $lowStockCount = db()->fetchOne("SELECT COUNT(*) as cnt FROM locations WHERE status='Low Stock'")['cnt'] ?? 0;
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/locations/index.php';
    }

    public function update(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager')) redirect('/locations', 'Access denied.', 'error');
        $locationId = (int)($_POST['location_id'] ?? 0);
        $capacity   = (int)($_POST['capacity'] ?? 0);
        if ($capacity < 0) {
            redirect('/locations', 'Capacity cannot be negative.', 'error');
        }
        if (!db()->fetchOne("SELECT location_id FROM locations WHERE location_id = ?", [$locationId])) {
            redirect('/locations', 'Location not found.', 'error');
        }
        db()->execute("UPDATE locations SET capacity=? WHERE location_id=?", [$capacity, $locationId]);
        Auth::log('UPDATE_LOCATION', 'locations', $locationId, "Updated location capacity");
        redirect('/locations', 'Location updated successfully!', 'success');
    }
}