<?php
$pageTitle = 'Add New Item';
ob_start();

$categoryIcons = [
    'Toiletries' => 'ph-fill ph-drop',
    'F&B'        => 'ph-fill ph-bowl-food',
    'Linens'     => 'ph-fill ph-towel',
    'Cleaning'   => 'ph-fill ph-broom',
    'Minibar'    => 'ph-fill ph-wine',
];
?>

<style>
/* ======= CREATE ITEM ANIMATIONS ======= */
.create-card {
    opacity:0; transform:translateY(24px);
    animation: riseUp 0.6s cubic-bezier(0.23,1,0.32,1) forwards;
}
@keyframes riseUp { to { opacity:1; transform:translateY(0); } }
.form-step {
    opacity:0; transform:translateY(14px);
    animation: riseUp 0.45s ease forwards;
}

/* Floating icon animation */
.float-icon {
    animation: floatIcon 3s ease-in-out infinite;
    display:inline-block;
}
@keyframes floatIcon {
    0%,100% { transform:translateY(0); }
    50%      { transform:translateY(-8px); }
}

/* Category cards — glass, theme-aware */
.cat-card {
    border:2px solid var(--glass-border); border-radius:12px;
    padding:12px 8px; text-align:center; cursor:pointer;
    transition:all 0.2s; background:var(--glass-bg);
    backdrop-filter:blur(10px) saturate(160%);
    -webkit-backdrop-filter:blur(10px) saturate(160%);
    flex:1;
}
.cat-card:hover { border-color:#0096FF; background:rgba(0,150,255,0.10); transform:translateY(-2px); }
.cat-card.selected { border-color:#0096FF; background:rgba(0,150,255,0.14); box-shadow:0 0 0 4px rgba(0,150,255,0.15); }
.cat-card .cat-icon { font-size:24px; margin-bottom:4px; color:var(--text-muted); transition:color 0.2s; }
.cat-card:hover .cat-icon,
.cat-card.selected .cat-icon { color:#0096FF; }
.cat-card .cat-name { font-size:11px; font-weight:700; color:var(--text-secondary); }

/* Field styles — glass, theme-aware */
.field-label {
    font-size:11px; font-weight:700; color:var(--text-faint);
    text-transform:uppercase; letter-spacing:0.8px;
    margin-bottom:6px; display:block;
}
.add-input {
    font-size:15px !important; padding:12px 14px !important;
    border:2px solid var(--glass-border) !important; border-radius:10px !important;
    transition:all 0.2s !important; background:var(--glass-bg) !important;
    color:var(--text-primary) !important;
    backdrop-filter:blur(10px) saturate(160%);
    -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.add-input:focus {
    border-color:#0096FF !important;
    box-shadow:0 0 0 4px rgba(0,150,255,0.15) !important;
    background:var(--glass-bg-strong) !important;
    color:var(--text-primary) !important;
}
.add-input::placeholder { color:var(--text-faint); opacity:1; }
input[type="date"].add-input::-webkit-calendar-picker-indicator {
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230096FF' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z'/%3E%3C/svg%3E") no-repeat center;
    cursor:pointer; opacity:0.8; padding:4px; border-radius:4px;
}
input[type="date"].add-input::-webkit-calendar-picker-indicator:hover { background-color:rgba(0,150,255,0.12); }

.sec-divider {
    display:flex; align-items:center; gap:12px;
    margin:24px 0 16px; color:var(--text-faint);
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;
}
.sec-divider::before,.sec-divider::after { content:''; flex:1; height:1px; background:var(--border-color); }

/* Submit button */
.btn-save-item {
    background:linear-gradient(135deg,#22c55e,#0096FF);
    background-size:200% 200%; animation:btnG 3s ease infinite;
    border:none; color:#fff; font-size:16px; font-weight:700;
    padding:13px 40px; border-radius:12px;
    box-shadow:0 4px 16px rgba(34,197,94,0.35); transition:all 0.3s;
}
.btn-save-item:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(34,197,94,0.5); color:#fff; }
@keyframes btnG { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
.btn-cancel-glass {
    background:var(--glass-bg) !important; border:1px solid var(--glass-border) !important;
    color:var(--text-secondary) !important;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
    transition:all 0.2s;
}
.btn-cancel-glass:hover { background:rgba(239,68,68,0.12) !important; border-color:rgba(239,68,68,0.4) !important; color:#ef4444 !important; }

/* Preview card — glass, theme-aware */
.preview-badge {
    display:inline-block; padding:4px 12px; border-radius:20px;
    font-size:12px; font-weight:700;
}
.preview-box {
    border:2px dashed var(--glass-border); border-radius:12px; padding:16px;
    text-align:center; margin-bottom:16px;
    background:var(--glass-bg);
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.kpi-box {
    background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:12px; padding:14px; text-align:center;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.health-box {
    margin-top:12px; background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:8px; padding:10px;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.tip-chip {
    display:flex; gap:8px; padding:8px; border-radius:10px;
    border:1px solid var(--glass-border);
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
</style>

<div class="row g-4">

    <!-- ======= LEFT — FORM ======= -->
    <div class="col-md-8">
        <div class="stat-card create-card">

            <!-- Header with floating icon -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#22c55e,#0096FF);
                            border-radius:14px;display:flex;align-items:center;justify-content:center;
                            box-shadow:0 6px 20px rgba(34,197,94,0.3)">
                    <span class="float-icon" style="font-size:24px"><i class="ph-fill ph-package" style="color:#fff"></i></span>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Add New Inventory Item</h5>
                    <div style="font-size:12px;color:var(--text-faint)">Fill in all required fields to add a new item</div>
                </div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/inventory/store" id="createForm">

                <!-- Step 1: Basic Info -->
                <div class="sec-divider"><i class="ph-fill ph-clipboard-text me-1"></i>Basic Information</div>
                <div class="row g-3 mb-2 form-step" style="animation-delay:0.1s">
                    <div class="col-md-8">
                        <label class="field-label"><i class="ph-fill ph-package me-1"></i>Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control add-input"
                               placeholder="e.g. Shampoo 50ml, Bath Towel, Red Bull..." required
                               oninput="updatePreview()"
                               value="<?= htmlspecialchars($prefillName ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-calendar me-1"></i>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control add-input"
                               min="<?= date('Y-m-d') ?>">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Leave blank if non-perishable</div>
                    </div>
                </div>
                <div class="row g-3 mb-2 form-step" style="animation-delay:0.12s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-barcode me-1"></i>Item Code / SKU</label>
                        <input type="text" name="item_code" class="form-control add-input" maxlength="20"
                               placeholder="e.g. 7NV-0001 — leave blank to skip"
                               value="<?= htmlspecialchars($prefillCode ?? '', ENT_QUOTES) ?>">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">
                            Optional. Auto-filled when adding an item from an unrecognized QR/barcode scan.
                        </div>
                    </div>
                </div>

                <!-- Step 2: Category Selection -->
                <div class="sec-divider"><i class="ph-fill ph-tag me-1"></i>Category</div>
                <div class="form-step" style="animation-delay:0.15s">
                    <div class="d-flex gap-2 mb-2">
                        <?php foreach($categoryIcons as $cat => $icon): ?>
                        <div class="cat-card" onclick="selectCategory('<?= $cat ?>', this)"
                             title="<?= $cat ?>">
                            <div class="cat-icon"><i class="<?= $icon ?>"></i></div>
                            <div class="cat-name"><?= $cat ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="category" id="categoryInput" required>
                    <div id="catError" style="font-size:12px;color:#ef4444;display:none;margin-top:4px">
                        <i class="ph-fill ph-warning me-1"></i>Please select a category
                    </div>
                </div>

                <!-- Step 3: Location & Supplier -->
                <div class="sec-divider"><i class="ph-fill ph-map-pin me-1"></i>Location & Supplier</div>
                <div class="row g-3 mb-2 form-step" style="animation-delay:0.2s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-map-pin me-1"></i>Location <span class="text-danger">*</span></label>
                        <select name="location_id" class="form-select add-input" required>
                            <option value="">Choose Location...</option>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?= $loc['location_id'] ?>"><?= clean($loc['location_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-factory me-1"></i>Supplier</label>
                        <select name="supplier_id" class="form-select add-input">
                            <option value="">None / Choose later</option>
                            <?php foreach($suppliers as $s): ?>
                                <option value="<?= $s['supplier_id'] ?>"><?= clean($s['supplier_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Step 4: Stock Numbers -->
                <div class="sec-divider"><i class="ph-fill ph-chart-bar me-1"></i>Stock Numbers</div>
                <div class="row g-3 mb-4 form-step" style="animation-delay:0.25s">
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-stack me-1"></i>Current Quantity</label>
                        <input type="number" name="quantity" class="form-control add-input"
                               min="0" value="0" required oninput="updatePreview()">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Units currently in stock</div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-chart-bar me-1"></i>Par Level <span class="text-danger">*</span></label>
                        <input type="number" name="par_level" class="form-control add-input"
                               min="1" value="100" required oninput="updatePreview()">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Auto-alert when stock falls below</div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-coins me-1"></i>Unit Price (RM)</label>
                        <div class="input-group">
                            <span class="input-group-text"
                                  style="border:2px solid var(--glass-border);border-right:none;border-radius:10px 0 0 10px;
                                         font-weight:700;color:#22c55e;background:rgba(34,197,94,0.10);backdrop-filter:blur(10px)">RM</span>
                            <input type="number" name="unit_price" class="form-control add-input"
                                   min="0" step="0.01" value="0.00"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important"
                                   oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-3 align-items-center form-step" style="animation-delay:0.35s">
                    <button type="submit" class="btn-save-item" onclick="return validateForm()">
                        <i class="ph-fill ph-plus-circle me-2"></i>Add Item to Inventory
                    </button>
                    <a href="<?= APP_URL ?>/inventory" class="btn btn-cancel-glass px-4"
                       style="padding:12px 24px;font-size:15px;border-radius:12px">
                        <i class="ph-bold ph-x me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ======= RIGHT — LIVE PREVIEW ======= -->
    <div class="col-md-4">

        <!-- Live Preview -->
        <div class="stat-card create-card mb-4" style="animation-delay:0.2s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-eye me-1"></i> Live Preview
            </div>

            <div class="preview-box">
                <div style="font-size:28px;margin-bottom:4px;color:var(--text-faint)" id="prevIcon"><i class="ph-fill ph-package"></i></div>
                <div style="font-size:16px;font-weight:700;color:var(--text-primary)" id="prevName">New Item</div>
                <div class="mt-2">
                    <span id="prevCatBadge" class="preview-badge" style="background:var(--bg-subtle);color:var(--text-faint);border:1px solid var(--border-color)">No Category</span>
                </div>
                <div class="mt-2">
                    <span id="prevStatusBadge" class="preview-badge" style="background:#dcfce7;color:#16a34a"><i class="ph-fill ph-check-circle me-1"></i>In-Stock</span>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6">
                    <div class="kpi-box">
                        <div style="font-size:22px;font-weight:900;color:#0096FF" id="prevQty">0</div>
                        <div style="font-size:10px;color:var(--text-faint);font-weight:700">QTY</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-box">
                        <div style="font-size:22px;font-weight:900;color:#6366f1" id="prevPar">100</div>
                        <div style="font-size:10px;color:var(--text-faint);font-weight:700">PAR LEVEL</div>
                    </div>
                </div>
            </div>

            <!-- Stock health -->
            <div class="health-box">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:11px;color:var(--text-secondary);font-weight:600"><i class="ph-fill ph-heartbeat me-1"></i>Stock Health</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-primary)" id="prevHealthPct">0%</span>
                </div>
                <div style="height:7px;background:var(--border-color);border-radius:4px;overflow:hidden">
                    <div id="prevHealthBar" style="height:100%;width:0%;background:#22c55e;border-radius:4px;transition:width 0.4s,background 0.3s"></div>
                </div>
            </div>
        </div>

        <!-- Quick Tips -->
        <div class="stat-card create-card" style="animation-delay:0.35s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-lightbulb me-1"></i> Tips
            </div>
            <div class="tip-chip mb-2" style="background:rgba(0,150,255,0.10)">
                <span><i class="ph-fill ph-bell" style="color:#0096FF"></i></span>
                <div style="font-size:12px;color:var(--text-secondary)"><strong style="color:var(--text-primary)">Par Level</strong> — system auto-alerts when stock drops below this</div>
            </div>
            <div class="tip-chip mb-2" style="background:rgba(34,197,94,0.10)">
                <span><i class="ph-fill ph-calendar" style="color:#22c55e"></i></span>
                <div style="font-size:12px;color:var(--text-secondary)"><strong style="color:var(--text-primary)">Expiry Date</strong> — only needed for perishable items (F&B, toiletries)</div>
            </div>
            <div class="tip-chip" style="background:rgba(168,85,247,0.10)">
                <span><i class="ph-fill ph-factory" style="color:#a855f7"></i></span>
                <div style="font-size:12px;color:var(--text-secondary)"><strong style="color:var(--text-primary)">Supplier</strong> — optional, can be assigned later via Suppliers page</div>
            </div>
        </div>
    </div>
</div>

<script>
const catIcons = { 'Toiletries':'ph-fill ph-drop','F&B':'ph-fill ph-bowl-food','Linens':'ph-fill ph-towel','Cleaning':'ph-fill ph-broom','Minibar':'ph-fill ph-wine' };
const catColors = {
    'Toiletries':['#dbeafe','#1d4ed8'],
    'F&B'       :['#dcfce7','#166534'],
    'Linens'    :['#fef9c3','#92400e'],
    'Cleaning'  :['#f3e8ff','#6b21a8'],
    'Minibar'   :['#ffe4e6','#9f1239'],
};

function selectCategory(cat, el) {
    document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('categoryInput').value = cat;
    document.getElementById('catError').style.display = 'none';
    // Update preview
    const icon = catIcons[cat] || 'ph-fill ph-package';
    const [bg, color] = catColors[cat] || ['#f1f5f9','#64748b'];
    document.getElementById('prevIcon').innerHTML = '<i class="' + icon + '"></i>';
    const badge = document.getElementById('prevCatBadge');
    badge.textContent = cat;
    badge.style.background = bg;
    badge.style.color = color;
}

function updatePreview() {
    const name  = document.querySelector('[name="item_name"]').value || 'New Item';
    const qty   = parseInt(document.querySelector('[name="quantity"]').value) || 0;
    const par   = parseInt(document.querySelector('[name="par_level"]').value) || 0;

    document.getElementById('prevName').textContent = name;
    document.getElementById('prevQty').textContent  = qty.toLocaleString();
    document.getElementById('prevPar').textContent  = par.toLocaleString();

    const pct = par > 0 ? Math.min(100, Math.round(qty/par*100)) : 0;
    const bar = document.getElementById('prevHealthBar');
    bar.style.width = pct + '%';
    bar.style.background = qty===0?'#ef4444':(qty<=par?'#f59e0b':'#22c55e');
    document.getElementById('prevHealthPct').textContent = pct + '%';

    const sb = document.getElementById('prevStatusBadge');
    if (qty===0)      { sb.innerHTML='<i class="ph-fill ph-x-circle me-1"></i>Out of Stock'; sb.style.background='#fee2e2'; sb.style.color='#dc2626'; }
    else if (qty<=par){ sb.innerHTML='<i class="ph-fill ph-warning me-1"></i>Low Stock';   sb.style.background='#fef9c3'; sb.style.color='#b45309'; }
    else              { sb.innerHTML='<i class="ph-fill ph-check-circle me-1"></i>In-Stock'; sb.style.background='#dcfce7'; sb.style.color='#16a34a'; }
}

function validateForm() {
    if (!document.getElementById('categoryInput').value) {
        document.getElementById('catError').style.display = 'block';
        document.querySelectorAll('.cat-card')[0].scrollIntoView({behavior:'smooth'});
        return false;
    }
    return true;
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>