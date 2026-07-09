<?php $pageTitle = 'Alerts'; ob_start(); ?>

<style>
/* Page entrance */
.al-wrap { opacity:0; transform:translateY(14px); animation:alUp 0.45s cubic-bezier(0.23,1,0.32,1) forwards; }
@keyframes alUp { to { opacity:1; transform:translateY(0); } }
.al-item { opacity:0; transform:translateX(-12px); animation:alSlide 0.4s ease forwards; }
@keyframes alSlide { to { opacity:1; transform:translateX(0); } }

/* Filter tabs */
.al-tab {
    padding:8px 18px; border-radius:20px; font-size:13px; font-weight:700;
    cursor:pointer; text-decoration:none; transition:all 0.2s ease;
    border:2px solid transparent; display:inline-flex; align-items:center; gap:6px;
}
.al-tab:hover { transform:translateY(-2px); }
.al-tab.t-all     { background:var(--accent-blue); color:#fff; }
.al-tab.t-all.off { background: var(--bg-subtle); color: var(--text-muted); border-color: var(--border-color); }
.al-tab.t-crit     { background:#fee2e2; color:#dc2626; border-color:#fecaca; }
.al-tab.t-crit.off { background: var(--bg-subtle); color: var(--text-faint); border-color: var(--border-color); }
.al-tab.t-warn     { background:#fef9c3; color:#b45309; border-color:#fde68a; }
.al-tab.t-warn.off { background: var(--bg-subtle); color: var(--text-faint); border-color: var(--border-color); }
.al-tab.t-info     { background:#dbeafe; color:#1d4ed8; border-color:#bfdbfe; }
.al-tab.t-info.off { background: var(--bg-subtle); color: var(--text-faint); border-color: var(--border-color); }

/* Alert card */
.al-card {
    position:relative; overflow:hidden; isolation:isolate;
    background: var(--glass-bg-strong);
    backdrop-filter:blur(16px) saturate(180%);
    -webkit-backdrop-filter:blur(16px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:14px; padding:18px 20px;
    margin-bottom:12px; border-left:5px solid transparent;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 6px 18px var(--glass-shadow);
    transition:all 0.22s cubic-bezier(0.23,1,0.32,1), background-color 0.25s ease;
    will-change:transform;
}
.al-card::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.4; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.al-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.al-card:hover::after { opacity:.5; }
@media (prefers-reduced-motion: reduce) { .al-card::before { animation: none; } }
.al-card:hover { transform:translateX(4px); }
.al-card.critical { border-left-color:#ef4444; }
.al-card.warning  { border-left-color:#f59e0b; }
.al-card.info     { border-left-color:#3b82f6; }

/* Icon badge */
.al-icon {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
    animation:iconPop 0.4s cubic-bezier(0.34,1.56,0.64,1) forwards;
}
@keyframes iconPop { from{transform:scale(0)} to{transform:scale(1)} }
.al-card.critical .al-icon { background:#fee2e2; animation:critPulse 2.5s ease-in-out infinite; }
.al-card.warning  .al-icon { background:#fef9c3; }
.al-card.info     .al-icon { background:#dbeafe; }
@keyframes critPulse {
    0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,0.2)}
    50%{box-shadow:0 0 0 8px rgba(239,68,68,0)}
}

/* Progress bar for stock level */
.stock-bar-wrap { background: var(--border-subtle); border-radius:6px; height:8px; overflow:hidden; margin-top:6px; }
.stock-bar { height:100%; border-radius:6px; width:0%; transition:width 1.2s ease; }

/* Action buttons */
.btn-approve {
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:#fff; border:none; border-radius:10px;
    padding:8px 16px; font-size:13px; font-weight:700;
    cursor:pointer; transition:all 0.18s ease; min-width:90px;
    box-shadow:0 3px 10px rgba(34,197,94,0.3);
}
.btn-approve:hover { transform:translateY(-2px); filter:brightness(1.1); box-shadow:0 5px 16px rgba(34,197,94,0.4); }

.btn-dismiss {
    background: var(--bg-subtle); color: var(--text-muted); border: 2px solid var(--border-color);
    border-radius:10px; padding:8px 16px; font-size:13px; font-weight:600;
    cursor:pointer; transition:all 0.18s ease; min-width:90px;
}
.btn-dismiss:hover { background: var(--border-color); color: var(--text-primary); }

.btn-viewstock {
    background:#eff6ff; color:#1d4ed8; border:2px solid #bfdbfe;
    border-radius:10px; padding:8px 16px; font-size:13px; font-weight:600;
    text-decoration:none; display:block; text-align:center;
    transition:all 0.18s ease; min-width:90px;
}
.btn-viewstock:hover { background:#dbeafe; color:#1e40af; transform:translateY(-1px); }

/* KPI strip */
.al-kpi {
    position:relative; overflow:hidden; isolation:isolate;
    background: var(--glass-bg);
    backdrop-filter:blur(16px) saturate(180%);
    -webkit-backdrop-filter:blur(16px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:12px; padding:14px 18px;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 20px var(--glass-shadow);
    text-align:center; transition:background-color 0.25s ease;
}
.al-kpi::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.4; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.al-kpi::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.al-kpi:hover::after { opacity:.5; }
@media (prefers-reduced-motion: reduce) { .al-kpi::before { animation: none; } }
.al-kpi .n { font-size:28px; font-weight:900; line-height:1; }
.al-kpi .l { font-size:10px; color: var(--text-faint); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-top:3px; }
</style>

<!-- KPI Strip -->
<div class="row g-3 mb-4 al-wrap">
    <div class="col-3">
        <div class="al-kpi">
            <div class="n" style="color: var(--text-primary)" id="kpiAll" data-target="<?= $counts['total'] ?? 0 ?>">0</div>
            <div class="l">Total Active</div>
        </div>
    </div>
    <div class="col-3">
        <div class="al-kpi">
            <div class="n text-danger" id="kpiCrit" data-target="<?= $counts['critical'] ?? 0 ?>">0</div>
            <div class="l"><i class="ph-fill ph-warning-octagon" style="color:#ef4444;font-size:10px;margin-right:4px"></i>Critical</div>
        </div>
    </div>
    <div class="col-3">
        <div class="al-kpi">
            <div class="n text-warning" id="kpiWarn" data-target="<?= $counts['warning'] ?? 0 ?>">0</div>
            <div class="l"><i class="ph-fill ph-warning" style="color:#f59e0b;font-size:10px;margin-right:4px"></i>Warning</div>
        </div>
    </div>
    <div class="col-3">
        <div class="al-kpi">
            <div class="n text-info" id="kpiInfo" data-target="<?= $counts['info'] ?? 0 ?>">0</div>
            <div class="l"><i class="ph-fill ph-info" style="color:#3b82f6;font-size:10px;margin-right:4px"></i>Info</div>
        </div>
    </div>
</div>

<!-- Filter Tabs + Scan Button -->
<?php
$cur  = $_GET['type'] ?? '';
$tot  = $counts['total']    ?? 0;
$crit = $counts['critical'] ?? 0;
$warn = $counts['warning']  ?? 0;
$info = $counts['info']     ?? 0;
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-4 al-wrap" style="animation-delay:0.08s">
    <a href="<?= APP_URL ?>/alerts" class="al-tab t-all <?= $cur==='' ? '' : 'off' ?>">
        All <span><?= $tot ?></span>
    </a>
    <a href="<?= APP_URL ?>/alerts?type=Critical" class="al-tab t-crit <?= $cur==='Critical' ? '' : 'off' ?>">
        <i class="ph-fill ph-warning-octagon" style="color:#dc2626;margin-right:4px"></i>Critical <span><?= $crit ?></span>
    </a>
    <a href="<?= APP_URL ?>/alerts?type=Warning" class="al-tab t-warn <?= $cur==='Warning' ? '' : 'off' ?>">
        <i class="ph-fill ph-warning" style="color:#b45309;margin-right:4px"></i>Warning <span><?= $warn ?></span>
    </a>
    <a href="<?= APP_URL ?>/alerts?type=Info" class="al-tab t-info <?= $cur==='Info' ? '' : 'off' ?>">
        <i class="ph-fill ph-info" style="color:#1d4ed8;margin-right:4px"></i>Info <span><?= $info ?></span>
    </a>

    <?php if (Auth::hasRole('Inventory Manager', 'Procurement Officer')): ?>
    <form method="POST" action="<?= APP_URL ?>/alerts/resolve" class="ms-auto">
        <input type="hidden" name="action" value="scan">
        <button type="submit" class="al-tab t-all"
                style="border:2px solid #0096FF;background:#eff6ff;color:#1d4ed8;cursor:pointer"
                onmouseover="this.style.background='#0096FF';this.style.color='#fff'"
                onmouseout="this.style.background='#eff6ff';this.style.color='#1d4ed8'">
            <i class="ph-fill ph-arrows-clockwise" style="margin-right:4px"></i>Scan Inventory Now
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- Empty State -->
<?php if (empty($alerts)): ?>
<div class="stat-card text-center py-5 al-wrap" style="animation-delay:0.12s">
    <div style="font-size:56px;margin-bottom:12px;animation:iconPop 0.5s cubic-bezier(0.34,1.56,0.64,1)"><i class="ph-fill ph-check-circle" style="color:#22c55e;font-size:56px"></i></div>
    <div class="fw-bold" style="font-size:18px;color:#22c55e">All Systems Normal</div>
    <div style="font-size:13px;color: var(--text-faint);margin-top:6px">No active alerts at this time.</div>
</div>
<?php endif; ?>

<!-- Alert Cards -->
<?php foreach ($alerts as $i => $alert):
    $type = $alert['alert_type'];
    $typeCls = strtolower($type);
    $icons = [
        'Critical'=>'<i class="ph-fill ph-warning-octagon" style="color:#ef4444"></i>',
        'Warning'=>'<i class="ph-fill ph-warning" style="color:#f59e0b"></i>',
        'Info'=>'<i class="ph-fill ph-info" style="color:#3b82f6"></i>'
    ];
    $icon  = $icons[$type] ?? '<i class="ph-fill ph-info" style="color:#3b82f6"></i>';

    $qty  = (int)($alert['quantity']  ?? 0);
    $par  = (int)($alert['par_level'] ?? 0);
    $pct  = $par > 0 ? min(100, round($qty/$par*100)) : 0;
    $barClr = $qty===0 ? '#ef4444' : ($qty<=$par ? '#f59e0b' : '#22c55e');
?>
<div class="al-card <?= $typeCls ?> al-item" style="animation-delay:<?= 0.12 + $i*0.07 ?>s">
    <div class="d-flex align-items-start justify-content-between gap-3">

        <!-- Left: icon + content -->
        <div class="d-flex gap-3 flex-grow-1">
            <div class="al-icon" style="animation-delay:<?= 0.2 + $i*0.07 ?>s"><?= $icon ?></div>

            <div style="flex:1;min-width:0">
                <div style="font-size:15px;font-weight:800;color: var(--text-primary);margin-bottom:3px">
                    <?= clean($alert['title']) ?>
                </div>
                <div style="font-size:13px;color: var(--text-muted);margin-bottom:8px;line-height:1.5">
                    <?= clean($alert['description']) ?>
                </div>

                <!-- Stock info with bar -->
                <?php if ($alert['item_name'] && $par > 0): ?>
                <div class="d-flex gap-3 mb-2">
                    <div>
                        <span style="font-size:10px;color: var(--text-faint);font-weight:700;text-transform:uppercase">Quantity</span>
                        <div style="font-size:16px;font-weight:800;color:<?= $barClr ?>"><?= number_format($qty) ?></div>
                    </div>
                    <div>
                        <span style="font-size:10px;color: var(--text-faint);font-weight:700;text-transform:uppercase">Par Level</span>
                        <div style="font-size:16px;font-weight:800;color: var(--text-secondary)"><?= number_format($par) ?></div>
                    </div>
                    <div style="flex:1;padding-top:18px">
                        <div class="stock-bar-wrap">
                            <div class="stock-bar" data-pct="<?= $pct ?>" style="background:<?= $barClr ?>"></div>
                        </div>
                        <div style="font-size:10px;color: var(--text-faint);margin-top:2px"><?= $pct ?>% of par level</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Meta info -->
                <div style="font-size:11px;color: var(--text-faint);display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                    <?php if ($alert['location_name']): ?>
                    <span><i class="ph-fill ph-map-pin" style="margin-right:4px"></i><?= clean($alert['location_name']) ?></span>
                    <?php endif; ?>
                    <span><i class="ph-fill ph-clock" style="margin-right:4px"></i><?= date('d M Y, H:i', strtotime($alert['triggered_at'])) ?></span>
                    <?php if ($alert['auto_generated']): ?>
                    <span style="color:#0096FF;font-weight:600"><i class="ph-fill ph-lightning" style="margin-right:4px"></i>Auto Generated</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: action buttons -->
        <div class="d-flex flex-column gap-2" style="flex-shrink:0">
            <?php if (Auth::hasRole('Inventory Manager','Procurement Officer')): ?>
            <form method="POST" action="<?= APP_URL ?>/alerts/resolve">
                <input type="hidden" name="alert_id" value="<?= $alert['alert_id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn-approve w-100">
                    <i class="ph-fill ph-check me-1"></i>Approve
                </button>
            </form>
            <form method="POST" action="<?= APP_URL ?>/alerts/resolve">
                <input type="hidden" name="alert_id" value="<?= $alert['alert_id'] ?>">
                <input type="hidden" name="action" value="dismiss">
                <button type="submit" class="btn-dismiss w-100">
                    <i class="ph-fill ph-x me-1"></i>Dismiss
                </button>
            </form>
            <?php endif; ?>
            <?php if ($alert['item_id']): ?>
            <a href="<?= APP_URL ?>/inventory?search=<?= urlencode($alert['item_name'] ?? '') ?>" class="btn-viewstock">
                <i class="ph-fill ph-eye me-1"></i>View Stock
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
window.addEventListener('load', function() {
    ['kpiAll','kpiCrit','kpiWarn','kpiInfo'].forEach(function(id, i) {
        const el = document.getElementById(id);
        if (!el) return;
        const target = parseInt(el.dataset.target) || 0;
        setTimeout(function() {
            const start = performance.now(), dur = 700;
            (function tick(now) {
                const p = Math.min((now-start)/dur,1), ease=1-Math.pow(1-p,3);
                el.textContent = Math.floor(ease*target);
                if (p<1) requestAnimationFrame(tick); else el.textContent=target;
            })(start);
        }, i * 100);
    });

    requestAnimationFrame(function() { requestAnimationFrame(function() {
        document.querySelectorAll('.stock-bar').forEach(function(bar, i) {
            setTimeout(function() { bar.style.width = bar.dataset.pct + '%'; }, 400 + i*80);
        });
    }); });
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>