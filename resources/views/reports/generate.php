<?php
$typeNames = [
    'stock-summary' => 'Stock Summary Report',
    'consumption'   => 'Consumption Analytics',
    'po-history'    => 'Purchase Order History',
    'valuation'     => 'Inventory Valuation',
    'supplier'      => 'Supplier Performance',
    'waste-expiry'  => 'Waste & Expiry Report',
];
$type = clean($_GET['type'] ?? 'stock-summary');
$pageTitle = $typeNames[$type] ?? 'Report';
ob_start();
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small"><?= count($items) ?> record found &bull; <?= date('d M Y, H:i') ?></div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary"><i class="ph ph-printer me-1"></i>Print / PDF</button>
        <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
</div>

<div class="stat-card">
    <div class="d-flex align-items-center gap-2 mb-3">
        <div class="brand-logo" style="font-size:18px;color:#1a1a2e"><span style="color:#0096FF">7</span>NVENT</div>
        <div>
            <div class="fw-bold"><?= $pageTitle ?></div>
            <div class="text-muted small">Generated: <?= date('d M Y, H:i') ?> by <?= clean($user['full_name']) ?></div>
        </div>
    </div>

    <?php if ($type === 'stock-summary' && !empty($items)): ?>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr><th>Item</th><th>Category</th><th>Location</th><th>Qty</th><th>Par Level</th><th>Status</th><th>Value (RM)</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td><?= clean($i['item_name']) ?></td>
                <td><?= clean($i['category']) ?></td>
                <td><?= clean($i['location_name']) ?></td>
                <td><?= $i['quantity'] ?></td>
                <td><?= $i['par_level'] ?></td>
                <td><?= $i['status'] ?></td>
                <td><?= number_format($i['quantity']*$i['unit_price'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ($type === 'po-history' && !empty($items)): ?>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr><th>PO Number</th><th>Supplier</th><th>Items</th><th>Value</th><th>Raised By</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $po): ?>
            <tr>
                <td><?= clean($po['po_number']) ?></td>
                <td><?= clean($po['supplier_name']) ?></td>
                <td><?= $po['total_items'] ?></td>
                <td><?= formatRM((float)$po['total_value']) ?></td>
                <td><?= clean($po['full_name']) ?></td>
                <td><?= date('d M Y', strtotime($po['po_date'])) ?></td>
                <td><?= $po['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ($type === 'waste-expiry' && !empty($items)): ?>
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr><th>Item</th><th>Category</th><th>Qty</th><th>Expiry Date</th><th>Days Left</th><th>Value at Risk</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i): $daysLeft = (strtotime($i['expiry_date']) - time()) / 86400; ?>
            <tr class="<?= $daysLeft < 7 ? 'table-danger' : ($daysLeft < 30 ? 'table-warning' : '') ?>">
                <td><?= clean($i['item_name']) ?></td>
                <td><?= clean($i['category']) ?></td>
                <td><?= $i['quantity'] ?></td>
                <td><?= date('d M Y', strtotime($i['expiry_date'])) ?></td>
                <td><?= (int)$daysLeft ?> hari</td>
                <td><?= formatRM($i['quantity'] * $i['unit_price']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="text-center text-muted py-5">
        <i class="ph ph-chart-bar" style="font-size:40px"></i>
        <div class="mt-2">Report <?= $pageTitle ?> - <?= count($items) ?> record found.</div>
        <div class="small mt-1">Export to PDF or Excel is not available in this system version.</div>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>