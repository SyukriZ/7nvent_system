<?php
require_once __DIR__ . '/../../Auth.php';

class SupplierController {
    public function index(): void {
        Auth::required();
        $suppliers = db()->fetchAll("SELECT s.*, COUNT(po.po_id) as total_orders FROM suppliers s LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id GROUP BY s.supplier_id ORDER BY s.rating DESC");
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/suppliers/index.php';
    }

    public function create(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Procurement Officer')) redirect('/suppliers', 'Access denied.', 'error');
        $user = Auth::user();
        require_once __DIR__ . '/../../../resources/views/suppliers/create.php';
    }

    public function store(): void {
        Auth::required();
        if (!Auth::hasRole('Inventory Manager', 'Procurement Officer')) redirect('/suppliers', 'Access denied.', 'error');

        $data = [
            'supplier_name'   => clean($_POST['supplier_name']   ?? ''),
            'category'        => clean($_POST['category']        ?? ''),
            'contact_person'  => clean($_POST['contact_person']  ?? ''),
            'phone'           => clean($_POST['phone']           ?? ''),
            'email'           => clean($_POST['email']           ?? ''),
            'rating'          => (float)($_POST['rating']          ?? 0),
            'lead_time_days'  => (float)($_POST['lead_time_days']  ?? 0),
        ];

        // Server-side validation — the create form's `required`/`min`/`max`
        // attributes are client-only and trivially bypassed with a raw POST.
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
            redirect('/suppliers/create', implode(' ', $errors), 'error');
        }

        db()->execute(
            "INSERT INTO suppliers (supplier_name, category, contact_person, phone, email, rating, lead_time_days) VALUES (?,?,?,?,?,?,?)",
            [
                $data['supplier_name'], $data['category'], $data['contact_person'],
                $data['phone'], $data['email'], $data['rating'], $data['lead_time_days'],
            ]
        );
        $id = (int)db()->lastInsertId();
        Auth::log('ADD_SUPPLIER', 'suppliers', $id, "Added supplier: " . $data['supplier_name']);
        redirect('/suppliers', 'Supplier added successfully!', 'success');
    }
}