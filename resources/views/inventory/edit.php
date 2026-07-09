<?php
$pageTitle = 'Edit Item';
ob_start();

$statusColor = [
    'In-Stock'     => ['bg'=>'#dcfce7','color'=>'#16a34a','icon'=>'ph-check-circle'],
    'Low Stock'    => ['bg'=>'#fef9c3','color'=>'#b45309','icon'=>'ph-warning'],
    'Out of Stock' => ['bg'=>'#fee2e2','color'=>'#dc2626','icon'=>'ph-x-circle'],
];
$sc = $statusColor[$item['status']] ?? ['bg'=>'#f8fafc','color'=>'#64748b','icon'=>'ph-package'];
?>

<style>
.edit-card {
    opacity:0; transform:translateY(20px);
    animation: fadeUp 0.5s ease forwards;
}
@keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
.edit-section { opacity:0; transform:translateY(12px); animation: fadeUp 0.4s ease forwards; }

.field-label {
    font-size:11px; font-weight:700; color:var(--text-faint);
    text-transform:uppercase; letter-spacing:0.8px;
    margin-bottom:6px; display:block;
}
.edit-input {
    font-size:15px !important; padding:12px 14px !important;
    border:2px solid var(--glass-border) !important; border-radius:10px !important;
    transition:all 0.2s !important; background:var(--glass-bg) !important;
    color:var(--text-primary) !important;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.edit-input:focus {
    border-color:#0096FF !important;
    box-shadow:0 0 0 4px rgba(0,150,255,0.15) !important;
    background:var(--glass-bg-strong) !important;
    color:var(--text-primary) !important;
}
.edit-input::placeholder { color:var(--text-faint); opacity:1; }
input[type="date"].edit-input::-webkit-calendar-picker-indicator {
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230096FF' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z'/%3E%3C/svg%3E") no-repeat center;
    cursor:pointer; opacity:0.8; padding:4px; border-radius:4px;
}
input[type="date"].edit-input::-webkit-calendar-picker-indicator:hover { background-color:rgba(0,150,255,0.12); }
.section-div {
    display:flex; align-items:center; gap:12px;
    margin:24px 0 16px; color:var(--text-faint);
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;
}
.section-div::before,.section-div::after { content:''; flex:1; height:1px; background:var(--border-color); }

.stat-num {
    font-size:32px; font-weight:900; line-height:1;
}
.kpi-mini {
    background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:12px; padding:16px; text-align:center;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
}
.btn-update {
    background:linear-gradient(135deg,#0096FF,#6366f1);
    background-size:200% 200%;
    animation:btnGrad 3s ease infinite;
    border:none; color:#fff; font-size:16px; font-weight:700;
    padding:13px 36px; border-radius:12px;
    box-shadow:0 4px 16px rgba(0,150,255,0.35);
    transition:all 0.3s;
}
.btn-update:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,150,255,0.5); color:#fff; }
@keyframes btnGrad { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
.btn-cancel-glass {
    background:var(--glass-bg) !important; border:1px solid var(--glass-border) !important;
    color:var(--text-secondary) !important;
    backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%);
    transition:all 0.2s;
}
.btn-cancel-glass:hover { background:rgba(239,68,68,0.12) !important; border-color:rgba(239,68,68,0.4) !important; color:#ef4444 !important; }
.health-box-lg { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:8px; padding:12px; backdrop-filter:blur(10px) saturate(160%); -webkit-backdrop-filter:blur(10px) saturate(160%); }
.info-row { border-bottom:1px solid var(--border-color); }
</style>

<div class="row g-4">

    <!-- ======= LEFT — FORM ======= -->
    <div class="col-md-8">
        <div class="stat-card edit-card">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#6366f1,#0096FF);
                            border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px">
                    <i class="ph-fill ph-pencil-simple" style="color:#fff"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Edit Item: <?= clean($item['item_name']) ?></h5>
                    <div style="font-size:12px;color:var(--text-faint)">Update the details below and click Save Changes</div>
                </div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/inventory/update">
                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">

                <!-- Basic Info -->
                <div class="row g-3 mb-2 edit-section" style="animation-delay:0.1s">
                    <div class="col-md-8">
                        <label class="field-label"><i class="ph-fill ph-package me-1"></i>Item Name</label>
                        <input type="text" name="item_name" class="form-control edit-input"
                               value="<?= clean($item['item_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-tag me-1"></i>Category</label>
                        <select name="category" class="form-select edit-input" required>
                            <?php foreach(['Toiletries','F&B','Linens','Cleaning','Minibar'] as $c): ?>
                                <option value="<?= $c ?>" <?= $item['category']===$c?'selected':'' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-2 edit-section" style="animation-delay:0.12s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-barcode me-1"></i>Item Code / SKU</label>
                        <input type="text" name="item_code" class="form-control edit-input" maxlength="20"
                               placeholder="e.g. 7NV-0001 — leave blank to skip"
                               value="<?= clean($item['item_code'] ?? '') ?>">
                    </div>
                </div>

                <div class="section-div"><i class="ph-fill ph-map-pin me-1"></i>Location & Supplier</div>
                <div class="row g-3 mb-2 edit-section" style="animation-delay:0.15s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-map-pin me-1"></i>Location</label>
                        <select name="location_id" class="form-select edit-input" required>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?= $loc['location_id'] ?>" <?= $item['location_id']==$loc['location_id']?'selected':'' ?>>
                                    <?= clean($loc['location_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-factory me-1"></i>Supplier</label>
                        <select name="supplier_id" class="form-select edit-input">
                            <option value="">None</option>
                            <?php foreach($suppliers as $s): ?>
                                <option value="<?= $s['supplier_id'] ?>" <?= $item['supplier_id']==$s['supplier_id']?'selected':'' ?>>
                                    <?= clean($s['supplier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="section-divider" style="display:flex;align-items:center;gap:12px;margin:24px 0 16px;color:var(--text-faint);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
                    <span><i class="ph-fill ph-stack me-1"></i>Stock Details</span>
                    <div style="flex:1;height:1px;background:var(--border-color)"></div>
                </div>
                <div class="row g-3 mb-2 edit-section" style="animation-delay:0.2s">
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-stack me-1"></i>Current Quantity</label>
                        <input type="number" name="quantity" id="qtyInput"
                               class="form-control edit-input" min="0"
                               value="<?= $item['quantity'] ?>" required
                               oninput="updateLiveStatus()">
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-chart-bar me-1"></i>Par Level</label>
                        <div class="position-relative">
                            <input type="number" name="par_level" id="parInput"
                                   class="form-control edit-input" min="0"
                                   value="0" data-real="<?= $item['par_level'] ?>" required
                                   oninput="updateLiveStatus()">
                        </div>
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Minimum stock threshold</div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label"><i class="ph-fill ph-coins me-1"></i>Unit Price (RM)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border:2px solid var(--glass-border);border-right:none;border-radius:10px 0 0 10px;font-weight:700;color:#0096FF;background:rgba(0,150,255,0.10);backdrop-filter:blur(10px)">RM</span>
                            <input type="number" name="unit_price"
                                   id="priceInput"
                                   class="form-control edit-input"
                                   min="0" step="0.01"
                                   value="0.00" data-real="<?= $item['unit_price'] ?>"
                                   style="border-left:none!important;border-radius:0 10px 10px 0!important">
                        </div>
                    </div>
                </div>

                <div class="section-divider" style="display:flex;align-items:center;gap:12px;margin:24px 0 16px;color:var(--text-faint);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
                    <span><i class="ph-fill ph-calendar me-1"></i>Expiry & Status</span>
                    <div style="flex:1;height:1px;background:var(--border-color)"></div>
                </div>
                <div class="row g-3 mb-2 edit-section" style="animation-delay:0.25s">
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-calendar me-1"></i>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control edit-input"
                               value="<?= $item['expiry_date'] ?? '' ?>">
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Leave blank for non-perishable items</div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label"><i class="ph-fill ph-arrows-clockwise me-1"></i>Current Status</label>
                        <div id="statusDisplay" class="form-control edit-input"
                             style="background:<?= $sc['bg'] ?>!important;color:<?= $sc['color'] ?>;font-weight:700;border-color:<?= $sc['color'] ?>44!important">
                            <i class="ph-fill <?= $sc['icon'] ?> me-1"></i><?= $item['status'] ?>
                        </div>
                        <div style="font-size:10px;color:var(--text-faint);margin-top:3px">Auto-recalculated on save</div>
                    </div>
                </div>

                <hr class="my-4" style="border-color:var(--border-color)">
                <div class="d-flex gap-3 align-items-center edit-section" style="animation-delay:0.35s">
                    <button type="submit" class="btn-update">
                        <i class="ph-fill ph-floppy-disk me-2"></i>Save Changes
                    </button>
                    <a href="<?= APP_URL ?>/inventory" class="btn btn-cancel-glass px-4"
                       style="padding:12px 24px;font-size:15px;border-radius:12px">
                        <i class="ph-bold ph-x me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ======= RIGHT — LIVE STATS ======= -->
    <div class="col-md-4">

        <!-- Current Stock KPIs -->
        <div class="stat-card edit-card mb-4" style="animation-delay:0.15s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-chart-bar me-1"></i> Current Values
            </div>

            <div class="kpi-mini mb-3">
                <div style="font-size:11px;color:var(--text-faint);font-weight:700;margin-bottom:4px">CURRENT QTY</div>
                <div class="stat-num text-primary" id="liveQty">0</div>
                <div style="font-size:11px;color:var(--text-faint)">units in stock</div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="kpi-mini">
                        <div style="font-size:9px;color:var(--text-faint);font-weight:700;margin-bottom:2px">PAR LEVEL</div>
                        <div class="stat-num" id="livePar" style="font-size:22px;color:#6366f1">0</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-mini">
                        <div style="font-size:9px;color:var(--text-faint);font-weight:700;margin-bottom:2px">UNIT PRICE</div>
                        <div class="stat-num" id="livePrice" style="font-size:18px;color:#22c55e">RM 0.00</div>
                    </div>
                </div>
            </div>

            <!-- Stock health bar -->
            <div class="health-box-lg">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:11px;color:var(--text-secondary);font-weight:600"><i class="ph-fill ph-heartbeat me-1"></i>Stock Health</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-primary)" id="healthPct">—</span>
                </div>
                <div style="height:8px;background:var(--border-color);border-radius:4px;overflow:hidden">
                    <div id="healthBar" style="height:100%;width:0%;background:#22c55e;border-radius:4px;transition:width 0.5s,background 0.3s"></div>
                </div>
            </div>
        </div>

        <!-- Item Info -->
        <div class="stat-card edit-card" style="animation-delay:0.3s">
            <div class="fw-bold mb-3" style="font-size:13px;color:var(--text-faint);text-transform:uppercase;letter-spacing:1px">
                <i class="ph-fill ph-info me-1"></i> Item Info
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 info-row">
                <span style="font-size:13px;color:var(--text-secondary)"><i class="ph ph-hash me-1"></i>Item ID</span>
                <span style="font-size:13px;font-weight:700;color:var(--text-primary)">#<?= $item['item_id'] ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 info-row">
                <span style="font-size:13px;color:var(--text-secondary)"><i class="ph ph-barcode me-1"></i>Item Code</span>
                <code style="font-size:12px;background:var(--bg-subtle);color:var(--text-primary);padding:2px 8px;border-radius:4px"><?= clean($item['item_code'] ?? '—') ?></code>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 info-row">
                <span style="font-size:13px;color:var(--text-secondary)"><i class="ph ph-tag me-1"></i>Category</span>
                <span style="font-size:13px;font-weight:600;color:var(--text-primary)"><?= clean($item['category']) ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <span style="font-size:13px;color:var(--text-secondary)"><i class="ph ph-clock me-1"></i>Last Updated</span>
                <span style="font-size:12px;color:var(--text-faint)"><?= $item['last_updated'] ? date('d M Y', strtotime($item['last_updated'])) : '—' ?></span>
            </div>
        </div>
    </div>
</div>

<script>
// ======= LOADING ANIMATION for Par Level & Unit Price =======
window.addEventListener('load', function() {
    // Par Level counter
    const parEl   = document.getElementById('parInput');
    const parReal = parseFloat(parEl.dataset.real) || 0;
    const parLive = document.getElementById('livePar');
    animateNum(parLive, parReal, 1200, false);
    setTimeout(() => { parEl.value = parReal; updateLiveStatus(); }, 1300);

    // Unit Price counter
    const priceEl   = document.getElementById('priceInput');
    const priceReal = parseFloat(priceEl.dataset.real) || 0;
    const priceLive = document.getElementById('livePrice');
    animateRM(priceLive, priceReal, 1400);
    setTimeout(() => { priceEl.value = priceReal.toFixed(2); }, 1500);

    // Quantity live
    const qty = parseInt(document.getElementById('qtyInput').value) || 0;
    animateNum(document.getElementById('liveQty'), qty, 1000, false);
    setTimeout(updateLiveStatus, 1400);
});

function animateNum(el, target, dur, isRM) {
    const start = performance.now();
    function tick(now) {
        const p    = Math.min((now - start) / dur, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        el.textContent = isRM
            ? 'RM ' + (ease * target).toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2})
            : Math.floor(ease * target).toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = isRM ? 'RM ' + target.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2}) : target.toLocaleString();
    }
    requestAnimationFrame(tick);
}

function animateRM(el, target, dur) { animateNum(el, target, dur, true); }

function updateLiveStatus() {
    const qty = parseInt(document.getElementById('qtyInput').value) || 0;
    const par = parseInt(document.getElementById('parInput').value) || 0;
    const price = parseFloat(document.getElementById('priceInput')?.value) || 0;

    document.getElementById('liveQty').textContent   = qty.toLocaleString();
    document.getElementById('livePar').textContent   = par.toLocaleString();
    document.getElementById('livePrice').textContent = 'RM ' + price.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});

    // Health bar
    const pct = par > 0 ? Math.min(100, Math.round(qty / par * 100)) : 100;
    const bar = document.getElementById('healthBar');
    const pctEl = document.getElementById('healthPct');
    bar.style.width = pct + '%';
    bar.style.background = qty === 0 ? '#ef4444' : (qty <= par ? '#f59e0b' : '#22c55e');
    pctEl.textContent = pct + '%';
    pctEl.style.color = qty === 0 ? '#ef4444' : (qty <= par ? '#f59e0b' : '#22c55e');

    // Live status display
    const sd = document.getElementById('statusDisplay');
    if (qty === 0) {
        sd.innerHTML='<i class="ph-fill ph-x-circle me-1"></i>Out of Stock'; sd.style.background='#fee2e2'; sd.style.color='#dc2626'; sd.style.borderColor='#fca5a5';
    } else if (qty <= par) {
        sd.innerHTML='<i class="ph-fill ph-warning me-1"></i>Low Stock'; sd.style.background='#fef9c3'; sd.style.color='#b45309'; sd.style.borderColor='#fde68a';
    } else {
        sd.innerHTML='<i class="ph-fill ph-check-circle me-1"></i>In-Stock'; sd.style.background='#dcfce7'; sd.style.color='#16a34a'; sd.style.borderColor='#bbf7d0';
    }
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>