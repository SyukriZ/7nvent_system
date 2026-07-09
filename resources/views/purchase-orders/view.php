<?php
$pageTitle = 'Detail PO: ' . $order['po_number'];
ob_start();

$statusSteps = ['Pending', 'In Transit', 'Delivered'];
$currentStep = array_search($order['status'], $statusSteps);
if ($currentStep === false) $currentStep = -1; // Cancelled
?>

<style>
/* ======= DELIVERY TRACKER ======= */
.tracker-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    gap: 0;
}
.tracker-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
}
.tracker-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    border: 3px solid var(--border-color);
    background: var(--bg-subtle);
    position: relative; z-index: 2;
    transition: all 0.5s;
}
.tracker-icon i {
    font-size: 28px;
}
.tracker-icon.done {
    background: #dcfce7;
    border-color: #22c55e;
    box-shadow: 0 0 0 6px rgba(34,197,94,0.12);
}
.tracker-icon.done i {
    color: #22c55e;
}
.tracker-icon.active {
    background: #dbeafe;
    border-color: #3b82f6;
    box-shadow: 0 0 0 6px rgba(59,130,246,0.15);
    animation: activePulse 2s ease-in-out infinite;
}
.tracker-icon.active i {
    color: #3b82f6;
}
.tracker-icon.cancelled {
    background: #fee2e2;
    border-color: #ef4444;
}
.tracker-icon.cancelled i {
    color: #ef4444;
}
@keyframes activePulse {
    0%,100% { box-shadow: 0 0 0 6px rgba(59,130,246,0.15); }
    50%      { box-shadow: 0 0 0 14px rgba(59,130,246,0.05); }
}

/* Timeline dot glow */
@keyframes dotGlow {
    0%,100% { box-shadow: 0 0 0 3px rgba(59,130,246,0.3), 0 0 12px rgba(59,130,246,0.4); }
    50%      { box-shadow: 0 0 0 6px rgba(59,130,246,0.15), 0 0 24px rgba(59,130,246,0.6); }
}
.timeline-dot-active {
    animation: dotGlow 2s ease-in-out infinite !important;
}
.tracker-label {
    font-size: 13px;
    font-weight: 700;
    margin-top: 10px;
    color: var(--text-faint);
}
.tracker-label.done   { color: #22c55e; }
.tracker-label.active { color: #3b82f6; }
.tracker-label.cancelled { color: #ef4444; }
.tracker-sub {
    font-size: 11px;
    color: var(--text-faint);
    margin-top: 2px;
}

/* Connector line */
.tracker-line {
    flex: 1;
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    margin: 0 -2px;
    position: relative;
    top: -22px;
    z-index: 1;
    overflow: hidden;
}
.tracker-line-fill {
    height: 100%;
    background: #22c55e;
    border-radius: 2px;
    width: 0%;
    transition: width 1.2s ease 0.5s;
}

/* Info grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.info-item label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-faint);
    font-weight: 700;
    display: block;
    margin-bottom: 4px;
}
.info-item .val {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

/* KPI cards */
.po-kpi {
    text-align: center;
    background: var(--bg-subtle);
    border-radius: 12px;
    padding: 16px 12px;
    border: 1px solid var(--border-color);
}
.po-kpi .num { font-size: 28px; font-weight: 900; }
.po-kpi .lbl { font-size: 11px; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

/* Phosphor icon sizing helpers */
.ph-16 { font-size: 16px; }
.ph-20 { font-size: 20px; }
.ph-24 { font-size: 24px; }
.ph-32 { font-size: 32px; }
</style>

<!-- ======= PAGE HEADER ======= -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-800"><?= clean($order['po_number']) ?></h4>
        <div class="d-flex gap-2 align-items-center">
            <?php
            $badgeMap = ['Delivered'=>'success','In Transit'=>'primary','Pending'=>'warning','Cancelled'=>'danger'];
            $bc = $badgeMap[$order['status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $bc ?> fs-6 px-3 py-2"><?= clean($order['status']) ?></span>
            <?php if ($order['approval_status'] === 'Auto'): ?>
                <span class="badge bg-info"><i class="ph-fill ph-robot me-1"></i>Auto-Generated</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if (in_array($order['status'], ['Pending','In Transit'])): ?>
        <form method="POST" action="<?= APP_URL ?>/purchase-orders/update" class="d-flex gap-2">
            <input type="hidden" name="po_id" value="<?= $order['po_id'] ?>">
            <?php if ($order['status'] === 'Pending'): ?>
                <input type="hidden" name="status" value="In Transit">
                <button class="btn btn-primary px-4 fw-bold"><i class="ph ph-truck me-2"></i>Mark In Transit</button>
            <?php else: ?>
                <input type="hidden" name="status" value="Delivered">
                <button class="btn btn-success px-4 fw-bold"><i class="ph-fill ph-check-circle me-2"></i>Mark Delivered</button>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/purchase-orders" class="btn btn-outline-secondary px-4">
            <i class="ph ph-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- ======= LEFT COLUMN ======= -->
    <div class="col-md-7">

        <!-- Delivery Tracker Card -->
        <div class="stat-card mb-4">
            <div class="fw-bold mb-1" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-package me-1"></i> Delivery Status Tracker
            </div>

            <div class="tracker-wrap">

                <!-- Step 1: Pending -->
                <div class="tracker-step">
                    <div class="tracker-icon <?= $currentStep >= 0 ? 'done' : '' ?>">
                        <i class="ph-fill ph-factory"></i>
                    </div>
                    <div class="tracker-label <?= $currentStep >= 0 ? 'done' : '' ?>">Order Placed</div>
                    <div class="tracker-sub"><?= date('d M Y', strtotime($order['po_date'])) ?></div>
                </div>

                <!-- Line 1 -->
                <div class="tracker-line">
                    <div class="tracker-line-fill" id="line1" style="width:<?= $currentStep >= 1 ? '0' : '0' ?>%"></div>
                </div>

                <!-- Step 2: In Transit -->
                <div class="tracker-step">
                    <div class="tracker-icon <?= $currentStep === 1 ? 'active' : ($currentStep > 1 ? 'done' : ($order['status']==='Cancelled'?'cancelled':'')) ?>">
                        <i class="ph-fill ph-truck"></i>
                    </div>
                    <div class="tracker-label <?= $currentStep === 1 ? 'active' : ($currentStep > 1 ? 'done' : '') ?>">In Transit</div>
                    <div class="tracker-sub">On the way</div>
                </div>

                <!-- Line 2 -->
                <div class="tracker-line">
                    <div class="tracker-line-fill" id="line2"></div>
                </div>

                <!-- Step 3: Delivered -->
                <div class="tracker-step">
                    <div class="tracker-icon <?= $currentStep >= 2 ? 'done' : '' ?>">
                        <i class="ph-fill ph-warehouse"></i>
                    </div>
                    <div class="tracker-label <?= $currentStep >= 2 ? 'done' : '' ?>">Delivered</div>
                    <div class="tracker-sub">
                        <?= $order['expected_delivery'] ? date('d M Y', strtotime($order['expected_delivery'])) : 'TBC' ?>
                    </div>
                </div>

            </div>

            <?php if ($order['status'] === 'Cancelled'): ?>
            <div class="alert alert-danger py-2 small text-center mt-2">
                <i class="ph-fill ph-x-circle me-1"></i> This purchase order has been <strong>cancelled</strong>.
            </div>
            <?php endif; ?>
        </div>

        <!-- PO Info -->
        <div class="stat-card">
            <div class="fw-bold mb-4" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-clipboard-text me-1"></i> Order Information
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label><i class="ph ph-buildings me-1"></i>Supplier</label>
                    <div class="val"><?= clean($order['supplier_name']) ?></div>
                </div>
                <div class="info-item">
                    <label><i class="ph ph-user me-1"></i>Contact Person</label>
                    <div class="val"><?= clean($order['contact_person'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <label><i class="ph ph-currency-dollar me-1"></i>Total Value</label>
                    <div class="val text-success" style="font-size:20px" id="poValue" data-target="<?= $order['total_value'] ?>">RM 0.00</div>
                </div>
                <div class="info-item">
                    <label><i class="ph ph-user-circle me-1"></i>Raised By</label>
                    <div class="val"><?= clean($order['raised_by_name']) ?></div>
                </div>
                <div class="info-item">
                    <label><i class="ph ph-calendar me-1"></i>PO Date</label>
                    <div class="val"><?= formatDate($order['po_date']) ?></div>
                </div>
                <div class="info-item">
                    <label><i class="ph ph-calendar-check me-1"></i>Expected Arrival</label>
                    <div class="val"><?= $order['expected_delivery'] ? formatDate($order['expected_delivery']) : '—' ?></div>
                </div>
            </div>
            <?php if ($order['notes']): ?>
            <hr class="my-3">
            <div style="font-size:13px;color:var(--text-muted)">
                <i class="ph-fill ph-sticky-note me-1"></i><?= clean($order['notes']) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ITEMS ORDERED -->
        <div class="stat-card mt-4">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-package me-1"></i> Items Ordered
            </div>
            <?php if (!empty($lineItems)): ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr style="font-size:11px;color:var(--text-faint);text-transform:uppercase;letter-spacing:.5px">
                            <th><i class="ph ph-cube me-1"></i>Item</th>
                            <th class="text-center"><i class="ph ph-stack me-1"></i>Qty</th>
                            <th class="text-end"><i class="ph ph-tag me-1"></i>Unit Price</th>
                            <th class="text-end"><i class="ph ph-coins me-1"></i>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lineItems as $li): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:14px"><?= clean($li['item_name']) ?></div>
                                <div style="font-size:11px;color:var(--text-faint)"><?= clean($li['category']) ?><?= $li['item_code'] ? ' &middot; '.clean($li['item_code']) : '' ?></div>
                            </td>
                            <td class="text-center fw-bold"><?= number_format($li['quantity_ordered']) ?></td>
                            <td class="text-end"><?= formatRM($li['unit_price']) ?></td>
                            <td class="text-end fw-bold text-success"><?= formatRM($li['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--border-color)">
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="text-end fw-bold text-success" style="font-size:16px"><?= formatRM($order['total_value']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-4" style="font-size:13px">
                <i class="ph-fill ph-info me-1"></i>
                Itemized breakdown not available for this PO (header-level record).
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ======= RIGHT COLUMN ======= -->
    <div class="col-md-5">

        <!-- KPI Cards -->
        <div class="stat-card mb-4">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-chart-bar me-1"></i> Order Summary
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="po-kpi">
                        <div class="num text-primary" id="poItems" data-target="<?= $order['total_items'] ?>">0</div>
                        <div class="lbl">Total Items</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="po-kpi">
                        <div class="num" style="color:<?= $bc==='success'?'#22c55e':($bc==='primary'?'#3b82f6':($bc==='warning'?'#f59e0b':'#ef4444')) ?>">
                            <?= clean($order['status']) ?>
                        </div>
                        <div class="lbl">Current Status</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="po-kpi">
                        <div class="num text-muted" style="font-size:18px"><?= date('d M', strtotime($order['po_date'])) ?></div>
                        <div class="lbl">Order Date</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="po-kpi">
                        <div class="num text-muted" style="font-size:18px">
                            <?= $order['expected_delivery'] ? date('d M', strtotime($order['expected_delivery'])) : '—' ?>
                        </div>
                        <div class="lbl">Expected</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Info -->
        <div class="stat-card mb-4">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-shield-check me-1"></i> Approval Details
            </div>
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--bg-subtle);border:1px solid var(--border-color)">
                <div style="font-size:32px">
                    <?php if ($order['approval_status']==='Auto'): ?>
                        <i class="ph-fill ph-robot text-info"></i>
                    <?php else: ?>
                        <i class="ph-fill ph-user-check text-primary"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="fw-bold"><?= $order['approval_status']==='Auto' ? 'Auto-Generated' : 'Manual Approval' ?></div>
                    <div style="font-size:13px;color:var(--text-muted)">
                        Raised by <strong><?= clean($order['raised_by_name']) ?></strong>
                    </div>
                    <div style="font-size:12px;color:var(--text-faint);margin-top:3px">
                        <i class="ph ph-clock me-1"></i>
                        Approved: <strong><?= date('d M Y, H:i', strtotime($order['approved_at'])) ?></strong>
                    </div>
                    <div style="font-size:12px;color:var(--text-faint);margin-top:2px">
                        <i class="ph ph-user me-1"></i>
                        By: <strong><?= clean($order['approved_by']) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="stat-card">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-clock me-1"></i> Timeline
            </div>
            <div style="position:relative;padding-left:24px">
                <div style="position:absolute;left:8px;top:0;bottom:0;width:2px;background:var(--border-color);border-radius:2px"></div>

                <div class="d-flex gap-3 mb-3" style="position:relative">
                    <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;background:#22c55e;border:2px solid #fff;box-shadow:0 0 0 2px #22c55e"></div>
                    <div>
                        <div style="font-size:13px;font-weight:600"><i class="ph-fill ph-check-circle me-1 text-success"></i>Order Created</div>
                        <div style="font-size:11px;color:var(--text-faint)"><?= formatDate($order['po_date']) ?></div>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3" style="position:relative">
                    <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;
                                background:<?= $currentStep >= 1 ? '#3b82f6' : 'var(--border-color)' ?>;
                                border:2px solid #fff;
                                box-shadow:0 0 0 2px <?= $currentStep >= 1 ? '#3b82f6' : 'var(--border-color)' ?>"
                         <?= $currentStep === 1 ? 'class="timeline-dot-active"' : '' ?>></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:<?= $currentStep >= 1 ? '#3b82f6' : 'var(--text-faint)' ?>">
                            <i class="ph-fill ph-truck me-1"></i>In Transit
                        </div>
                        <div style="font-size:11px;color:var(--text-faint)"><?= $currentStep >= 1 ? 'Shipment dispatched' : 'Pending dispatch' ?></div>
                    </div>
                </div>

                <div class="d-flex gap-3" style="position:relative">
                    <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;
                                background:<?= $currentStep >= 2 ? '#22c55e' : 'var(--border-color)' ?>;
                                border:2px solid #fff;
                                box-shadow:0 0 0 2px <?= $currentStep >= 2 ? '#22c55e' : 'var(--border-color)' ?>"
                         <?= $currentStep === 2 ? 'class="timeline-dot-active" style="animation-color:#22c55e"' : '' ?>></div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:<?= $currentStep >= 2 ? '#22c55e' : 'var(--text-faint)' ?>">
                            <i class="ph-fill ph-warehouse me-1"></i>Delivered
                        </div>
                        <div style="font-size:11px;color:var(--text-faint)">
                            <?= $currentStep >= 2 ? 'Order received <i class="ph-fill ph-check-circle text-success"></i>' : ($order['expected_delivery'] ? 'Expected: '.formatDate($order['expected_delivery']) : 'TBC') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Animate tracker lines
window.addEventListener('load', function() {
    const step = <?= (int)$currentStep ?>;

    requestAnimationFrame(() => requestAnimationFrame(() => {
        if (step >= 1) document.getElementById('line1').style.width = '100%';
        if (step >= 2) setTimeout(() => document.getElementById('line2').style.width = '100%', 400);
    }));

    // Counter - total value
    const valEl = document.getElementById('poValue');
    if (valEl) {
        const target = parseFloat(valEl.dataset.target) || 0;
        const duration = 1400;
        const start = performance.now();
        function tickVal(now) {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            valEl.textContent = 'RM ' + (ease * target).toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
            if (p < 1) requestAnimationFrame(tickVal);
            else valEl.textContent = 'RM ' + target.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
        }
        requestAnimationFrame(tickVal);
    }

    // Counter - total items
    const itemEl = document.getElementById('poItems');
    if (itemEl) {
        const target = parseInt(itemEl.dataset.target) || 0;
        const duration = 1200;
        const start = performance.now();
        function tickItem(now) {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            itemEl.textContent = Math.floor(ease * target).toLocaleString();
            if (p < 1) requestAnimationFrame(tickItem);
            else itemEl.textContent = target.toLocaleString();
        }
        requestAnimationFrame(tickItem);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>