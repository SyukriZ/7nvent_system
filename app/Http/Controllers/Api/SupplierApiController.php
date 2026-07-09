<?php
// =============================================================
// 7NVENT - Supplier API Controller (mobile)
// =============================================================
// Mirrors SupplierController exactly — same query, same validation rules,
// same role check.

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../Auth.php';

class SupplierApiController extends ApiController {

    /** GET /api/suppliers */
    public function index(): void {
        $this->requireAuth();
        $suppliers = db()->fetchAll(
            "SELECT s.*, COUNT(po.po_id) as total_orders
             FROM suppliers s LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
             GROUP BY s.supplier_id ORDER BY s.rating DESC"
        );
        $this->json(['success' => true, 'suppliers' => $suppliers]);
    }

    /** POST /api/suppliers/store */
    public function store(): void {
        $payload = $this->requireRole('Inventory Manager', 'Procurement Officer');
        $body = $this->body();

        $data = [
            'supplier_name'  => trim((string)($body['supplier_name']  ?? '')),
            'category'       => trim((string)($body['category']       ?? '')),
            'contact_person' => trim((string)($body['contact_person']  ?? '')),
            'phone'          => trim((string)($body['phone']          ?? '')),
            'email'          => trim((string)($body['email']          ?? '')),
            'rating'         => (float)($body['rating']         ?? 0),
            'lead_time_days' => (float)($body['lead_time_days'] ?? 0),
        ];

        // Same server-side validation as the web create() action — a JSON
        // body bypasses client-only form attributes just as easily as a
        // raw POST would.
        $errors = [];
        if ($data['supplier_name'] === '') {
            $errors[] = 'Supplier name is required.';
        } elseif (mb_strlen($data['supplier_name']) > 150) {
            $errors[] = 'Supplier name is too long (max 150 characters).';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address is invalid.';
        }
        if ($data['rating'] < 0 || $data['rating'] > 5) {
            $errors[] = 'Rating must be between 0 and 5.';
        }
        if ($data['lead_time_days'] < 0 || $data['lead_time_days'] > 365) {
            $errors[] = 'Lead time must be between 0 and 365 days.';
        }
        if ($errors) {
            $this->jsonError(implode(' ', $errors), 422);
        }

        db()->execute(
            "INSERT INTO suppliers (supplier_name, category, contact_person, phone, email, rating, lead_time_days) VALUES (?,?,?,?,?,?,?)",
            [
                $data['supplier_name'], $data['category'], $data['contact_person'],
                $data['phone'], $data['email'], $data['rating'], $data['lead_time_days'],
            ]
        );
        $id = (int) db()->lastInsertId();
        Auth::log('ADD_SUPPLIER', 'suppliers', $id, 'Added supplier (mobile app): ' . $data['supplier_name'], (int)$payload['user_id']);

        $this->json(['success' => true, 'message' => 'Supplier added successfully!', 'supplier_id' => $id]);
    }
}
