<?php
require_once __DIR__ . '/../../../app/Support/FifoPriority.php';
$pageTitle = 'Inventory';
ob_start();

function categoryBadge(string $cat): string {
    $map = [
        'Toiletries' => 'cat-toiletries',
        'F&B'        => 'cat-fb',
        'Linens'     => 'cat-linens',
        'Cleaning'   => 'cat-cleaning',
        'Minibar'    => 'cat-minibar',
    ];
    $cls = $map[$cat] ?? 'bg-secondary text-white';
    return "<span class='badge $cls' style='font-size:13px;padding:5px 10px'>$cat</span>";
}

function statusBadge(string $status): string {
    $map = [
        'In-Stock'     => 'badge-in-stock',
        'Low Stock'    => 'badge-low-stock',
        'Out of Stock' => 'badge-out-stock',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return "<span class='badge $cls' style='font-size:13px;padding:6px 12px'>$status</span>";
}
?>

<style>
.inv-table th { font-size:13px !important; padding:14px 12px !important; letter-spacing:0.5px; }
.inv-table td { font-size:15px !important; padding:14px 12px !important; vertical-align:middle; }
.inv-table .item-name  { font-size:16px !important; font-weight:600; color:var(--text-primary); }
.inv-table .qty-cell   { font-size:20px !important; font-weight:800; }
.inv-table .muted-cell { font-size:14px !important; color:var(--text-muted); }
.inv-filter input, .inv-filter select { font-size:14px !important; padding:8px 12px !important; height:auto !important; }
.inv-action-btn { font-size:13px !important; padding:7px 14px !important; font-weight:600; }

/* ======= Glassmorphism: filter/search toolbar controls ======= */
.inv-filter .input-group .form-control,
.inv-filter .form-select {
    background: var(--glass-bg) !important;
    border: 1px solid var(--glass-border) !important;
    color: var(--text-primary) !important;
    backdrop-filter: blur(12px) saturate(160%);
    -webkit-backdrop-filter: blur(12px) saturate(160%);
    transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
.inv-filter .input-group .form-control::placeholder { color: var(--text-faint); opacity:1; }
.inv-filter .form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
}
.inv-filter .input-group .btn-outline-secondary {
    background: var(--glass-bg) !important;
    border: 1px solid var(--glass-border) !important;
    color: var(--text-primary) !important;
    backdrop-filter: blur(12px) saturate(160%);
    -webkit-backdrop-filter: blur(12px) saturate(160%);
}
.inv-filter .input-group .btn-outline-secondary:hover { background: rgba(0,150,255,0.16) !important; color:#0096FF !important; border-color: rgba(0,150,255,0.4) !important; }
.inv-filter .form-select:focus,
.inv-filter .input-group .form-control:focus {
    background: var(--glass-bg-strong) !important;
    border-color: rgba(0,150,255,0.55) !important;
    box-shadow: 0 0 0 3px rgba(0,150,255,0.15) !important;
    color: var(--text-primary) !important;
}
.inv-filter .btn-outline-danger {
    background: rgba(239,68,68,0.10) !important;
    border: 1px solid rgba(239,68,68,0.35) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* FIFO Queue button — glass instead of solid light pill */
.btn-fifo-glass {
    background: rgba(0,150,255,0.12) !important;
    color: #0096FF !important;
    border: 1.5px solid rgba(0,150,255,0.38) !important;
    backdrop-filter: blur(12px) saturate(160%);
    -webkit-backdrop-filter: blur(12px) saturate(160%);
    transition: all 0.2s ease;
}
.btn-fifo-glass:hover {
    background: rgba(0,150,255,0.22) !important;
    color: #fff !important;
    border-color: rgba(0,150,255,0.6) !important;
    box-shadow: 0 4px 16px rgba(0,150,255,0.25);
}

/* Table rows: subtle glass hover instead of flat fill */
.inv-table tbody tr { transition: background-color 0.18s ease; }
.inv-table tbody tr:hover { background: var(--glass-bg) !important; }

/* Quantity progress bar track — recessed glass look */
.qty-bar-track { background: var(--glass-border); border-radius:4px; overflow:hidden; position:relative; }

/* ======= FIFO modal — glassmorphism ======= */
#fifoModal .modal-content {
    background: var(--glass-bg-strong) !important;
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border: 1px solid var(--glass-border) !important;
}
.fifo-dash {
    position: relative; isolation: isolate; overflow: hidden;
    background: var(--glass-bg) !important;
    backdrop-filter: blur(16px) saturate(160%);
    -webkit-backdrop-filter: blur(16px) saturate(160%);
    border-bottom: 1px solid var(--glass-border) !important;
}
.fifo-footer {
    background: var(--glass-bg) !important;
    backdrop-filter: blur(16px) saturate(160%);
    -webkit-backdrop-filter: blur(16px) saturate(160%);
    border-top: 1px solid var(--glass-border) !important;
}
.fifo-row {
    position: relative; isolation: isolate; overflow: hidden;
    background: var(--glass-bg) !important;
    backdrop-filter: blur(14px) saturate(160%);
    -webkit-backdrop-filter: blur(14px) saturate(160%);
    border: 1px solid var(--glass-border) !important;
    transition: transform 0.18s ease, background-color 0.18s ease;
}
.fifo-row:hover { transform: translateX(3px); background: var(--glass-bg-strong) !important; }
.fifo-row::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%);
    opacity:.35; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite;
    pointer-events:none; mix-blend-mode:overlay; z-index:-1;
}
@media (prefers-reduced-motion: reduce) { .fifo-row::before { animation: none; } }
</style>

<!-- Filters -->
<div class="data-table mb-4 p-3 inv-filter">
    <form method="GET" action="<?= APP_URL ?>/inventory" class="d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group" style="max-width:300px">
            <input type="text" name="search" class="form-control"
                   placeholder="Search items by name or location..."
                   value="<?= clean($_GET['search'] ?? '') ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="ph-bold ph-magnifying-glass"></i></button>
        </div>
        <select name="category" class="form-select" style="max-width:175px" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach(['Toiletries','F&B','Linens','Cleaning','Minibar'] as $c): ?>
                <option value="<?= $c ?>" <?= ($_GET['category']??'')===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
        <select name="location" class="form-select" style="max-width:200px" onchange="this.form.submit()">
            <option value="">All Locations</option>
            <?php foreach($locations as $loc): ?>
                <option value="<?= $loc['location_id'] ?>" <?= ($_GET['location']??'')==$loc['location_id']?'selected':'' ?>><?= clean($loc['location_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-select" style="max-width:160px" onchange="this.form.submit()">
            <option value="">All Status</option>
            <?php foreach(['In-Stock','Low Stock','Out of Stock'] as $s): ?>
                <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($_GET['search']) || !empty($_GET['category']) || !empty($_GET['location']) || !empty($_GET['status'])): ?>
            <a href="<?= APP_URL ?>/inventory" class="btn btn-outline-danger"><i class="ph-bold ph-x me-1"></i>Clear</a>
        <?php endif; ?>

        <!-- Buttons: FIFO Queue + Add Item -->
        <div class="d-flex gap-2 ms-auto">
            <button type="button" class="btn inv-action-btn btn-fifo-glass"
                    data-bs-toggle="modal" data-bs-target="#fifoModal">
                <i class="ph-fill ph-arrows-clockwise me-1"></i>FIFO Queue
            </button>
            <a href="<?= APP_URL ?>/inventory/create" class="btn btn-primary inv-action-btn">
                <i class="ph-bold ph-plus me-1"></i>Add Item
            </a>
        </div>
    </form>
</div>

<!-- Inventory Table -->
<div class="data-table">
    <table class="table table-hover mb-0 inv-table">
        <thead>
            <tr>
                <th class="ps-4"><i class="ph ph-cube me-1"></i>ITEM NAME</th>
                <th class="text-center"><i class="ph ph-tag me-1"></i>CATEGORY</th>
                <th class="text-center"><i class="ph ph-map-pin me-1"></i>LOCATION</th>
                <th class="text-center"><i class="ph ph-stack me-1"></i>QUANTITY</th>
                <th class="text-center"><i class="ph ph-chart-bar me-1"></i>PAR LEVEL</th>
                <th class="text-center"><i class="ph ph-check-circle me-1"></i>STATUS</th>
                <th class="text-center"><i class="ph ph-calendar me-1"></i>EXPIRY</th>
                <th class="text-center"><i class="ph ph-gear me-1"></i>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td class="ps-4">
                    <span class="item-name"><?= clean($item['item_name']) ?></span>
                </td>
                <td class="text-center"><?= categoryBadge($item['category']) ?></td>
                <td class="text-center">
                    <span class="muted-cell"><?= clean($item['location_name']) ?></span>
                </td>
                <td class="text-center">
                    <?php
                    $qty   = (int)$item['quantity'];
                    $par   = (int)$item['par_level'];
                    $pct   = $par > 0 ? min(100, round($qty / $par * 100)) : 100;
                    $color = $qty === 0 ? '#ef4444' : ($qty <= $par ? '#f59e0b' : '#22c55e');
                    ?>
                    <div style="min-width:120px;margin:0 auto">
                        <div style="line-height:1">
                            <span class="qty-num" data-target="<?= $qty ?>"
                                  style="font-size:18px;font-weight:800;color:<?= $color ?>">0</span>
                            <span style="font-size:11px;font-weight:500;color:var(--text-faint)"> units</span>
                        </div>
                        <div class="qty-bar-track" style="margin-top:5px;height:6px">
                            <div class="qty-bar" data-pct="<?= $pct ?>"
                                 style="width:0%;height:100%;background:<?= $color ?>;border-radius:4px;
                                        transition:width 1.2s cubic-bezier(0.25,0.46,0.45,0.94)"></div>
                        </div>
                        <div style="font-size:10px;color:var(--text-faint);margin-top:2px">
                            <span class="qty-pct" data-pct="<?= $pct ?>">0</span>% of par
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="muted-cell"><?= number_format($item['par_level']) ?></span>
                </td>
                <td class="text-center"><?= statusBadge($item['status']) ?></td>
                <td class="text-center">
                    <span class="muted-cell">
                        <?= $item['expiry_date'] ? date('M Y', strtotime($item['expiry_date'])) : 'N/A' ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if (Auth::hasRole('Inventory Manager', 'Housekeeping Manager')): ?>
                    <a href="<?= APP_URL ?>/inventory/edit?id=<?= $item['item_id'] ?>"
                       class="btn btn-primary inv-action-btn">
                       <i class="ph-bold ph-pencil-simple me-1"></i>Edit
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::hasRole('Inventory Manager')): ?>
                    <form method="POST" action="<?= APP_URL ?>/inventory/delete"
                          class="d-inline" onsubmit="return confirm('Delete this item?')">
                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                        <button type="submit" class="btn btn-outline-danger inv-action-btn">
                            <i class="ph-bold ph-trash me-1"></i>Del
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if (!Auth::hasRole('Inventory Manager', 'Housekeeping Manager')): ?>
                        <span class="text-muted" style="font-size:13px"><i class="ph ph-eye me-1"></i>View only</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-5" style="font-size:16px">
                    <i class="ph-fill ph-package me-1"></i>No items found.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mt-2 ps-1" style="font-size:14px;color:var(--text-muted)">
    <i class="ph ph-list-dashes me-1"></i><?= count($items) ?> item(s) found
</div>

<!-- ═══════════════════════════════════════════════════════════
     FIFO ENFORCEMENT MODAL
     Perishable items sorted by expiry date (oldest first)
     ═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="fifoModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">

    <!-- Header -->
    <div class="modal-header" style="background:linear-gradient(135deg,#0096FF,#6366f1);border:none;padding:20px 24px">
        <div>
            <h5 class="modal-title text-white fw-bold mb-0"><i class="ph-fill ph-arrows-clockwise me-2"></i>FIFO Enforcement Queue</h5>
            <div style="font-size:12px;color:rgba(255,255,255,.75);margin-top:3px">
                Items sorted by expiry date — consume oldest stock first
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body p-0">

        <!-- Compliance Dashboard -->
        <div class="fifo-dash" style="padding:18px 24px">
            <div class="row g-3 mb-3">
                <div class="col-3 text-center">
                    <div id="fifoScore"
                         style="font-size:30px;font-weight:900;
                                color:<?= $fifoCompliance>=80?'#22c55e':($fifoCompliance>=60?'#f59e0b':'#ef4444') ?>">
                        0%
                    </div>
                    <div style="font-size:9px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
                        FIFO Compliance
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div style="font-size:24px;font-weight:900;color:var(--text-primary)"><?= $totalPerishable ?></div>
                    <div style="font-size:9px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
                        Perishable Items
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div style="font-size:24px;font-weight:900;color:#ef4444"><?= $expiredCount ?></div>
                    <div style="font-size:9px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
                        Expired
                    </div>
                </div>
                <div class="col-3 text-center">
                    <?php
                    $urgentCount = 0;
                    foreach ($fifoItems as $f) {
                        $d = (int)$f['days_left'];
                        if ($d >= 0 && $d <= 30) $urgentCount++;
                    }
                    ?>
                    <div style="font-size:24px;font-weight:900;color:#f59e0b"><?= $urgentCount ?></div>
                    <div style="font-size:9px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
                        Expiring ≤30 Days
                    </div>
                </div>
            </div>

            <!-- Compliance bar -->
            <div style="background:var(--border-color);border-radius:6px;height:10px;overflow:hidden;margin-bottom:8px">
                <div id="fifoBar"
                     style="width:0%;height:100%;border-radius:6px;transition:width 1.4s ease;
                            background:<?= $fifoCompliance>=80
                                ? 'linear-gradient(90deg,#22c55e,#16a34a)'
                                : ($fifoCompliance>=60
                                    ? 'linear-gradient(90deg,#f59e0b,#d97706)'
                                    : 'linear-gradient(90deg,#ef4444,#dc2626)') ?>">
                </div>
            </div>
            <div style="font-size:11px;color:var(--text-muted)">
                <?php if ($fifoCompliance >= 80): ?>
                    <i class="ph-fill ph-check-circle me-1 text-success"></i>Good FIFO compliance — stock rotation is within acceptable range.
                <?php elseif ($fifoCompliance >= 60): ?>
                    <i class="ph-fill ph-warning me-1 text-warning"></i>Moderate compliance — some perishable items need immediate attention.
                <?php else: ?>
                    <i class="ph-fill ph-warning-octagon me-1 text-danger"></i>Low compliance — expired items detected. Immediate stock review required.
                <?php endif; ?>
            </div>
        </div>

        <!-- Priority Queue List -->
        <div style="padding:16px 24px">
            <?php if (empty($fifoItems)): ?>
            <div style="text-align:center;padding:40px;color:var(--text-faint)">
                <div style="font-size:36px;margin-bottom:8px"><i class="ph-fill ph-package"></i></div>
                <div style="font-weight:700">No perishable items found.</div>
                <div style="font-size:12px;margin-top:4px">
                    Add expiry dates to inventory items to enable FIFO enforcement.
                </div>
            </div>
            <?php else: ?>

            <div style="font-size:10px;font-weight:700;color:var(--text-faint);text-transform:uppercase;
                        letter-spacing:.5px;margin-bottom:12px;display:flex">
                <span><i class="ph-fill ph-list-dashes me-1"></i>Priority Queue</span>
                <span style="margin-left:auto">← Use items at top of list first</span>
            </div>

            <?php foreach ($fifoItems as $fi => $f):
                $days = (int)$f['days_left'];

                // Classification logic now lives in app/Support/FifoPriority.php
                // so it can be unit-tested without rendering this view.
                $class     = FifoPriority::classify($days);
                $daysLabel = FifoPriority::daysLabel($days);

                switch ($class) {
                    case FifoPriority::EXPIRED:
                        $badge = 'EXPIRED';
                        $clr = '#ef4444'; $bg = '#fee2e2'; $tc = '#b91c1c';
                        $icon = 'ph-warning-octagon';
                        break;
                    case FifoPriority::EXPIRES_TODAY:
                        $badge = 'EXPIRES TODAY';
                        $clr = '#ef4444'; $bg = '#fee2e2'; $tc = '#b91c1c';
                        $icon = 'ph-warning-octagon';
                        break;
                    case FifoPriority::USE_NOW:
                        $badge = 'USE NOW';
                        $clr = '#ef4444'; $bg = '#fee2e2'; $tc = '#b91c1c';
                        $icon = 'ph-warning';
                        break;
                    case FifoPriority::USE_NEXT:
                        $badge = 'USE NEXT';
                        $clr = '#f59e0b'; $bg = '#fef9c3'; $tc = '#92400e';
                        $icon = 'ph-warning';
                        break;
                    default:
                        $badge = 'OK';
                        $clr = '#22c55e'; $bg = '#dcfce7'; $tc = '#166534';
                        $icon = 'ph-check-circle';
                        break;
                }
            ?>
            <div class="fifo-row" style="display:flex;align-items:center;gap:12px;padding:12px 14px;
                        border-radius:10px;
                        border-left:4px solid <?= $clr ?> !important;margin-bottom:8px">

                <!-- Priority number -->
                <div style="width:28px;height:28px;border-radius:50%;background:<?= $bg ?>;
                            display:flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:900;color:<?= $clr ?>;flex-shrink:0">
                    <?= $fi + 1 ?>
                </div>

                <!-- Item info -->
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary)">
                        <?= clean($f['item_name']) ?>
                        <span style="font-size:11px;font-weight:500;color:var(--text-faint);margin-left:6px">
                            <?= clean($f['category']) ?>
                        </span>
                    </div>
                    <div style="font-size:11px;color:var(--text-faint);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap">
                        <span><i class="ph-fill ph-map-pin me-1"></i><?= clean($f['location_name']) ?></span>
                        <span><i class="ph-fill ph-package me-1"></i><?= number_format($f['quantity']) ?> units</span>
                        <span><i class="ph-fill ph-calendar me-1"></i>Exp: <?= date('d M Y', strtotime($f['expiry_date'])) ?></span>
                    </div>
                </div>

                <!-- Priority badge -->
                <div style="text-align:right;flex-shrink:0">
                    <div style="background:<?= $bg ?>;color:<?= $tc ?>;padding:4px 12px;
                                border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap">
                        <i class="ph-fill <?= $icon ?> me-1"></i><?= $badge ?>
                    </div>
                    <div style="font-size:11px;color:<?= $clr ?>;margin-top:4px;font-weight:600">
                        <?= $daysLabel ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-footer fifo-footer" style="padding:12px 24px">
        <div style="font-size:11px;color:var(--text-faint);flex:1">
            <i class="ph-fill ph-lightning me-1"></i>FIFO enforcement ensures oldest stock is consumed first to minimise waste.
        </div>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="ph-bold ph-x me-1"></i>Close</button>
    </div>

</div>
</div>
</div>

<script>
window.addEventListener('load', function() {
    // Animate quantity numbers
    document.querySelectorAll('.qty-num').forEach(function(el) {
        const target = parseInt(el.dataset.target) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        const duration = 1200;
        const startTime = performance.now();
        function update(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(update);
    });

    // Animate progress bars and pct text
    requestAnimationFrame(function() { requestAnimationFrame(function() {
        document.querySelectorAll('.qty-bar').forEach(function(bar) {
            bar.style.width = bar.dataset.pct + '%';
        });
        document.querySelectorAll('.qty-pct').forEach(function(el) {
            const target = parseInt(el.dataset.pct) || 0;
            let count = 0;
            const step = Math.max(1, Math.ceil(target / 40));
            const interval = setInterval(function() {
                count += step;
                if (count >= target) { count = target; clearInterval(interval); }
                el.textContent = count;
            }, 30);
        });
    }); });
});

// Animate FIFO score + bar when modal opens
document.getElementById('fifoModal').addEventListener('shown.bs.modal', function() {
    const target  = <?= (int)$fifoCompliance ?>;
    const scoreEl = document.getElementById('fifoScore');
    const bar     = document.getElementById('fifoBar');

    if (bar) setTimeout(function() { bar.style.width = target + '%'; }, 100);

    if (scoreEl) {
        let c = 0;
        const step = Math.max(1, Math.ceil(target / 40));
        const iv = setInterval(function() {
            c = Math.min(c + step, target);
            scoreEl.textContent = c + '%';
            if (c >= target) clearInterval(iv);
        }, 25);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>