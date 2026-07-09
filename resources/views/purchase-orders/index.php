<?php
$pageTitle = 'Purchase Orders';
ob_start();

// Max items for progress bar reference (for visual scaling)
$maxItems = !empty($orders) ? max(array_column($orders, 'total_items')) : 1;
$maxValue = !empty($orders) ? max(array_column($orders, 'total_value')) : 1;
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
.po-table th {
    font-size: 13px !important;
    padding: 14px 12px !important;
    letter-spacing: 0.5px;
}
.po-table td {
    font-size: 15px !important;
    padding: 14px 12px !important;
    vertical-align: middle;
}
.po-action-btn {
    font-size: 13px !important;
    padding: 7px 14px !important;
    font-weight: 600;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <?php foreach([''=>'All','Pending'=>'Pending','In Transit'=>'In Transit','Delivered'=>'Delivered','Cancelled'=>'Cancelled'] as $val=>$label): ?>
            <a href="<?= APP_URL ?>/purchase-orders<?= $val ? '?status='.$val : '' ?>"
               class="btn <?= ($_GET['status']??'')===$val ? 'btn-primary' : 'btn-outline-secondary' ?>"
               style="font-size:14px;padding:7px 16px">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if (Auth::hasRole('Inventory Manager','Procurement Officer')): ?>
    <a href="<?= APP_URL ?>/purchase-orders/create" class="btn btn-primary po-action-btn">
        <i class="ph ph-plus me-1"></i>New Purchase Order
    </a>
    <?php endif; ?>
</div>

<div class="data-table">
    <table class="table table-hover mb-0 po-table">
        <thead>
            <tr>
                <th class="ps-4">PO NUMBER</th>
                <th class="text-center">SUPPLIER</th>
                <th class="text-center">ITEMS</th>
                <th class="text-center">TOTAL VALUE</th>
                <th class="text-center">RAISED BY</th>
                <th class="text-center">DATE</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $idx => $po):
            $itemPct  = $maxItems > 0 ? min(100, round($po['total_items'] / $maxItems * 100)) : 0;
            $valuePct = $maxValue > 0 ? min(100, round($po['total_value'] / $maxValue * 100)) : 0;
            $sCls = [
                'Delivered' => ['cls'=>'badge-delivered', 'color'=>'#22c55e'],
                'In Transit'=> ['cls'=>'badge-transit',   'color'=>'#3b82f6'],
                'Pending'   => ['cls'=>'badge-pending',   'color'=>'#f59e0b'],
                'Cancelled' => ['cls'=>'badge-cancelled', 'color'=>'#ef4444'],
            ];
            $statusInfo = $sCls[$po['status']] ?? ['cls'=>'', 'color'=>'#888'];
        ?>
            <tr>
                <!-- PO Number — left aligned -->
                <td class="ps-4">
                    <a href="<?= APP_URL ?>/purchase-orders/view?id=<?= $po['po_id'] ?>"
                       class="fw-bold text-primary" style="font-size:15px;text-decoration:none">
                        <?= clean($po['po_number']) ?>
                    </a>
                </td>

                <!-- Supplier -->
                <td class="text-center" style="font-size:14px">
                    <?= clean($po['supplier_name']) ?>
                </td>

                <!-- Items — with progress bar + animation -->
                <td class="text-center">
                    <div style="min-width:120px;margin:0 auto">
                        <div style="line-height:1">
                            <span class="po-items-num" data-target="<?= $po['total_items'] ?>"
                                  style="font-size:18px;font-weight:800;color:#0096FF">0</span>
                            <span style="font-size:11px;color:var(--text-faint)"> items</span>
                        </div>
                        <div style="margin-top:5px;background:var(--border-color);border-radius:4px;height:6px;overflow:hidden">
                            <div class="po-items-bar" data-pct="<?= $itemPct ?>"
                                 style="width:0%;height:100%;background:#0096FF;border-radius:4px;
                                        transition:width 1.2s cubic-bezier(0.25,0.46,0.45,0.94)"></div>
                        </div>
                    </div>
                </td>

                <!-- Total Value — with counter animation -->
                <td class="text-center">
                    <div style="min-width:130px;margin:0 auto">
                        <div style="line-height:1">
                            <span class="po-value-num" data-target="<?= $po['total_value'] ?>"
                                  style="font-size:16px;font-weight:800;color:#22c55e">RM 0.00</span>
                        </div>
                        <div style="margin-top:5px;background:var(--border-color);border-radius:4px;height:6px;overflow:hidden">
                            <div class="po-value-bar" data-pct="<?= $valuePct ?>"
                                 style="width:0%;height:100%;background:#22c55e;border-radius:4px;
                                        transition:width 1.4s cubic-bezier(0.25,0.46,0.45,0.94)"></div>
                        </div>
                    </div>
                </td>

                <!-- Raised By -->
                <td class="text-center" style="font-size:14px;color:var(--text-secondary)">
                    <?= clean($po['raised_by_name']) ?>
                </td>

                <!-- Date -->
                <td class="text-center" style="font-size:14px;color:var(--text-muted)">
                    <?= date('d M', strtotime($po['po_date'])) ?>
                </td>

                <!-- Status -->
                <td class="text-center">
                    <span class="<?= $statusInfo['cls'] ?> fw-bold" style="font-size:15px">
                        <?= $po['status'] ?>
                    </span>
                </td>

                <!-- Actions -->
                <td class="text-center">
                    <div style="display:inline-flex;gap:8px;align-items:center">
                        <a href="<?= APP_URL ?>/purchase-orders/view?id=<?= $po['po_id'] ?>"
                           class="btn btn-primary po-action-btn" style="width:90px">
                           <i class="ph ph-eye me-1"></i>View
                        </a>
                        <?php if (in_array($po['status'],['Pending','In Transit']) && Auth::hasRole('Inventory Manager','Procurement Officer')): ?>
                        <form method="POST" action="<?= APP_URL ?>/purchase-orders/update">
                            <input type="hidden" name="po_id" value="<?= $po['po_id'] ?>">
                            <input type="hidden" name="status" value="<?= $po['status']==='Pending' ? 'In Transit' : 'Delivered' ?>">
                            <button type="submit" class="btn btn-outline-primary po-action-btn" style="width:90px">
                                <?= $po['status']==='Pending' ? 'Track' : 'Deliver' ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <span style="width:90px;display:inline-block;visibility:hidden" class="btn btn-outline-primary po-action-btn">Track</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-5" style="font-size:16px">
                    No purchase orders found.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
window.addEventListener('load', function() {

    // Animate items numbers
    document.querySelectorAll('.po-items-num').forEach(function(el) {
        const target = parseInt(el.dataset.target) || 0;
        const duration = 1200;
        const start = performance.now();
        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(tick);
    });

    // Animate total value numbers
    document.querySelectorAll('.po-value-num').forEach(function(el) {
        const target = parseFloat(el.dataset.target) || 0;
        const duration = 1400;
        const start = performance.now();
        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            const cur = ease * target;
            el.textContent = 'RM ' + cur.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = 'RM ' + target.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
        }
        requestAnimationFrame(tick);
    });

    // Animate progress bars — double rAF
    requestAnimationFrame(() => requestAnimationFrame(() => {
        document.querySelectorAll('.po-items-bar, .po-value-bar').forEach(function(bar) {
            bar.style.width = bar.dataset.pct + '%';
        });
    }));
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>