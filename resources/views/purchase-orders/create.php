<?php
$pageTitle = 'Purchase Order Create';
ob_start();

// Item list for the JS line-item picker (id, name, price, category)
$itemsJs = json_encode(array_map(function ($i) {
    return [
        'id'    => (int)$i['item_id'],
        'name'  => $i['item_name'],
        'price' => (float)$i['unit_price'],
        'cat'   => $i['category'],
    ];
}, $items), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
?>

<style>
/* ======= PO CREATE ANIMATIONS ======= */
.po-create-card {
    opacity: 0;
    transform: translateY(24px);
    animation: fadeUp 0.6s cubic-bezier(0.23,1,0.32,1) forwards;
}
@keyframes fadeUp {
    to { opacity:1; transform:translateY(0); }
}
.po-section {
    opacity: 0;
    transform: translateY(16px);
    animation: fadeUp 0.5s ease forwards;
}

/* Form labels */
.field-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 6px;
    display: block;
}

/* Enhanced inputs */
.po-input {
    font-size: 15px !important;
    padding: 12px 14px !important;
    border: 2px solid var(--border-color) !important;
    border-radius: 10px !important;
    transition: all 0.2s !important;
    background: var(--bg-subtle) !important;
}
.po-input:focus {
    border-color: #0096FF !important;
    box-shadow: 0 0 0 4px rgba(0,150,255,0.08) !important;
    background: var(--bg-card) !important;
}
.po-input::placeholder { color: #c0c8d4; }

/* Date input - custom style */
input[type="date"].po-input {
    color: var(--text-primary);
    cursor: pointer;
}
input[type="date"].po-input::-webkit-calendar-picker-indicator {
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230096FF' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z'/%3E%3C/svg%3E") no-repeat center;
    cursor: pointer;
    opacity: 0.8;
    padding: 4px;
}

/* Value display */
.value-display {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 2px solid #bae6fd;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}
.value-display .val {
    font-size: 28px;
    font-weight: 900;
    color: #0369a1;
}

/* Submit button */
.btn-submit-po {
    background: linear-gradient(135deg, #0096FF, #6366f1);
    background-size: 200% 200%;
    animation: btnGrad 3s ease infinite;
    border: none;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    padding: 14px 40px;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,150,255,0.35);
    transition: all 0.3s;
}
.btn-submit-po:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,150,255,0.5);
    color: #fff;
}
@keyframes btnGrad {
    0%,100% { background-position: 0% 50%; }
    50%      { background-position: 100% 50%; }
}

/* Section divider */
.section-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 28px 0 20px;
    color: var(--text-faint);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.section-divider::before, .section-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border-color);
}

/* Line item table */
.line-table th {
    font-size: 11px; font-weight: 700; color: var(--text-faint);
    text-transform: uppercase; letter-spacing: 0.5px;
    padding: 8px 10px;
}
.line-table td { padding: 8px 10px; vertical-align: middle; }
.line-table .po-input { padding: 9px 12px !important; font-size: 14px !important; }
.btn-add-line {
    background: #eff6ff; color: #1d4ed8; border: 2px dashed #bfdbfe;
    border-radius: 10px; font-weight: 700; font-size: 13px; padding: 10px 16px;
    width: 100%; transition: all 0.2s;
}
.btn-add-line:hover { background: #dbeafe; border-color: #0096FF; color: #0096FF; }
</style>

<div class="row g-4">

    <!-- ======= LEFT — MAIN FORM ======= -->
    <div class="col-md-8">
        <div class="stat-card po-create-card">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#0096FF,#6366f1);
                            border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px">
                    <i class="ph-fill ph-clipboard-text" style="color:#fff"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">New Purchase Order</h5>
                    <div style="font-size:12px;color:var(--text-faint)">Select items and quantities — totals are calculated automatically</div>
                </div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/purchase-orders/store" id="poForm" onsubmit="return validatePO()">

                <!-- Supplier -->
                <div class="mb-4 po-section" style="animation-delay:0.1s">
                    <label class="field-label"><i class="ph-fill ph-factory me-1"></i>Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select po-input" required onchange="updateSupplierInfo(this)">
                        <option value="">Choose Supplier...</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= $s['supplier_id'] ?>"
                                    data-lead="<?= $s['lead_time_days'] ?>"
                                    data-rating="<?= $s['rating'] ?>"
                                    data-cat="<?= clean($s['category']) ?>">
                                <?= clean($s['supplier_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Supplier info badge -->
                    <div id="supplierInfo" style="display:none;margin-top:8px" class="d-flex gap-2">
                        <span class="badge" style="background:#dbeafe;color:#1d4ed8;font-size:12px;padding:5px 10px" id="supRating"><i class="ph-fill ph-star me-1"></i>—</span>
                        <span class="badge" style="background:#dcfce7;color:#166534;font-size:12px;padding:5px 10px" id="supLead"><i class="ph-fill ph-package me-1"></i>— days lead</span>
                        <span class="badge" style="background:#f3e8ff;color:#6b21a8;font-size:12px;padding:5px 10px" id="supCat">—</span>
                    </div>
                </div>

                <!-- Line items -->
                <div class="section-divider"><i class="ph-fill ph-list-dashes me-1"></i>Items to Order</div>
                <div class="po-section" style="animation-delay:0.2s">
                    <table class="table line-table mb-2">
                        <thead>
                            <tr>
                                <th style="min-width:230px"><i class="ph ph-cube me-1"></i>Item</th>
                                <th style="width:110px" class="text-center"><i class="ph ph-stack me-1"></i>Qty</th>
                                <th style="width:120px" class="text-end"><i class="ph ph-tag me-1"></i>Unit Price</th>
                                <th style="width:130px" class="text-end"><i class="ph ph-coins me-1"></i>Subtotal</th>
                                <th style="width:44px"></th>
                            </tr>
                        </thead>
                        <tbody id="lineBody"><!-- rows injected by JS --></tbody>
                    </table>
                    <button type="button" class="btn-add-line" onclick="addLine()">
                        <i class="ph-bold ph-plus me-1"></i>Add Item
                    </button>
                </div>

                <!-- Dates -->
                <div class="section-divider"><i class="ph-fill ph-calendar me-1"></i>Schedule</div>
                <div class="row g-3 mb-4 po-section" style="animation-delay:0.3s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-calendar-blank me-1"></i>PO Date</label>
                        <input type="date" name="po_date" class="form-control po-input"
                               value="<?= date('Y-m-d') ?>">
                        <div style="font-size:11px;color:var(--text-faint);margin-top:4px">Date this order is raised</div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-truck me-1"></i>Expected Delivery</label>
                        <input type="date" name="expected_delivery" class="form-control po-input"
                               min="<?= date('Y-m-d') ?>">
                        <div style="font-size:11px;color:var(--text-faint);margin-top:4px">Estimated arrival date</div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="section-divider"><i class="ph-fill ph-notebook me-1"></i>Additional Info</div>
                <div class="mb-4 po-section" style="animation-delay:0.4s">
                    <label class="field-label"><i class="ph-fill ph-sticky-note me-1"></i>Notes</label>
                    <textarea name="notes" class="form-control po-input"
                              rows="4" placeholder="Additional notes, special instructions, or references..."
                              style="resize:vertical"></textarea>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-3 align-items-center po-section" style="animation-delay:0.5s">
                    <button type="submit" class="btn-submit-po">
                        <i class="ph-fill ph-paper-plane-right me-2"></i>Send Purchase Order
                    </button>
                    <a href="<?= APP_URL ?>/purchase-orders" class="btn btn-outline-secondary px-4" style="padding:13px 24px;font-size:15px;border-radius:12px">
                        <i class="ph-bold ph-x me-1"></i>Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- ======= RIGHT — SUMMARY ======= -->
    <div class="col-md-4">

        <!-- Live Summary -->
        <div class="stat-card mb-4 po-create-card" style="animation-delay:0.15s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-chart-bar me-1"></i> Order Summary
            </div>

            <div class="value-display mb-3">
                <div style="font-size:11px;color:#0369a1;font-weight:700;margin-bottom:4px">TOTAL VALUE</div>
                <div class="val" id="summaryValue">RM 0.00</div>
            </div>

            <div class="d-flex justify-content-between p-3 rounded mb-2" style="background:var(--bg-subtle);border:1px solid var(--border-color)">
                <span style="font-size:13px;color:var(--text-muted)"><i class="ph ph-stack me-1"></i>Total Items</span>
                <span style="font-size:16px;font-weight:800;color:#0096FF" id="summaryItems">0</span>
            </div>

            <div class="d-flex justify-content-between p-3 rounded mb-2" style="background:var(--bg-subtle);border:1px solid var(--border-color)">
                <span style="font-size:13px;color:var(--text-muted)"><i class="ph ph-list-dashes me-1"></i>Line Items</span>
                <span style="font-size:16px;font-weight:800;color:#6366f1" id="summaryLines">0</span>
            </div>

            <div class="d-flex justify-content-between p-3 rounded mb-2" style="background:var(--bg-subtle);border:1px solid var(--border-color)">
                <span style="font-size:13px;color:var(--text-muted)"><i class="ph ph-clock me-1"></i>Status</span>
                <span style="font-size:13px;font-weight:700;color:#f59e0b">Pending</span>
            </div>

            <div class="d-flex justify-content-between p-3 rounded" style="background:var(--bg-subtle);border:1px solid var(--border-color)">
                <span style="font-size:13px;color:var(--text-muted)"><i class="ph ph-user me-1"></i>Created by</span>
                <span style="font-size:13px;font-weight:600;color:var(--text-primary)"><?= clean($user['full_name']) ?></span>
            </div>
        </div>

        <!-- Tips -->
        <div class="stat-card po-create-card" style="animation-delay:0.3s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-lightbulb me-1"></i> Quick Guide
            </div>
            <div class="d-flex gap-2 mb-2">
                <span style="font-size:16px"><i class="ph-fill ph-number-circle-one text-primary"></i></span>
                <div style="font-size:13px;color:var(--text-muted)">Select the supplier from the dropdown</div>
            </div>
            <div class="d-flex gap-2 mb-2">
                <span style="font-size:16px"><i class="ph-fill ph-number-circle-two text-primary"></i></span>
                <div style="font-size:13px;color:var(--text-muted)">Add items and set quantities — price fills in automatically</div>
            </div>
            <div class="d-flex gap-2 mb-2">
                <span style="font-size:16px"><i class="ph-fill ph-number-circle-three text-primary"></i></span>
                <div style="font-size:13px;color:var(--text-muted)">Set expected delivery date</div>
            </div>
            <div class="d-flex gap-2">
                <span style="font-size:16px"><i class="ph-fill ph-number-circle-four text-primary"></i></span>
                <div style="font-size:13px;color:var(--text-muted)">Click <strong>Send Purchase Order</strong> — stock updates on delivery</div>
            </div>
        </div>
    </div>
</div>

<script>
const ITEMS = <?= $itemsJs ?>;

function optionHtml() {
    let h = '<option value="">Select item...</option>';
    ITEMS.forEach(function(it) {
        h += '<option value="' + it.id + '" data-price="' + it.price + '">'
           + it.name + ' — ' + it.cat + ' (RM ' + it.price.toFixed(2) + ')</option>';
    });
    return h;
}

function addLine() {
    const tbody = document.getElementById('lineBody');
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML =
        '<td><select name="item_id[]" class="form-select po-input line-item" onchange="recalc()" required>' + optionHtml() + '</select></td>' +
        '<td><input type="number" name="qty[]" class="form-control po-input line-qty" min="1" value="1" oninput="recalc()" required></td>' +
        '<td class="text-end line-price" style="font-size:14px;color:var(--text-muted)">RM 0.00</td>' +
        '<td class="text-end fw-bold text-success line-sub" style="font-size:14px">RM 0.00</td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="ph-bold ph-x"></i></button></td>';
    tbody.appendChild(tr);
    recalc();
}

function removeLine(btn) {
    const rows = document.querySelectorAll('#lineBody .line-row');
    if (rows.length <= 1) return; // keep at least one row
    btn.closest('tr').remove();
    recalc();
}

function money(n) {
    return 'RM ' + n.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function recalc() {
    let totalItems = 0, totalValue = 0, lineCount = 0;
    document.querySelectorAll('#lineBody .line-row').forEach(function(row) {
        const sel = row.querySelector('.line-item');
        const qty = parseInt(row.querySelector('.line-qty').value) || 0;
        const opt = sel.options[sel.selectedIndex];
        const price = (opt && opt.value) ? parseFloat(opt.dataset.price) : 0;
        const sub = price * qty;
        row.querySelector('.line-price').textContent = money(price);
        row.querySelector('.line-sub').textContent   = money(sub);
        if (opt && opt.value && qty > 0) { totalItems += qty; totalValue += sub; lineCount++; }
    });
    document.getElementById('summaryItems').textContent = totalItems.toLocaleString();
    document.getElementById('summaryValue').textContent = money(totalValue);
    document.getElementById('summaryLines').textContent = lineCount;
}

function validatePO() {
    let valid = false;
    document.querySelectorAll('#lineBody .line-row').forEach(function(row) {
        const sel = row.querySelector('.line-item');
        const qty = parseInt(row.querySelector('.line-qty').value) || 0;
        if (sel.value && qty > 0) valid = true;
    });
    if (!valid) {
        alert('Please add at least one item with a quantity.');
        return false;
    }
    return true;
}

function updateSupplierInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('supplierInfo');
    if (!opt.value) { info.style.display = 'none'; return; }
    document.getElementById('supRating').innerHTML = '<i class="ph-fill ph-star me-1"></i>' + opt.dataset.rating + ' rating';
    document.getElementById('supLead').innerHTML   = '<i class="ph-fill ph-package me-1"></i>' + opt.dataset.lead + ' days lead time';
    document.getElementById('supCat').textContent    = opt.dataset.cat;
    info.style.display = 'flex';
}

// Start with one empty line
addLine();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>