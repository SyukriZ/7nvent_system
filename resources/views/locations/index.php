<?php $pageTitle = 'Locations'; ob_start();

$locIcons = [
    'Storage'     => ['icon'=>'<i class="ph ph-factory"></i>','color'=>'#6366f1'],
    'Floor Pantry'=> ['icon'=>'<i class="ph ph-buildings"></i>','color'=>'#0096FF'],
    'F&B'         => ['icon'=>'<i class="ph ph-fork-knife"></i>','color'=>'#22c55e'],
    'Linen'       => ['icon'=>'<i class="ph ph-bed"></i>','color'=>'#f59e0b'],
];
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
/* ======= LOCATION ANIMATIONS ======= */
.loc-kpi {
    opacity:0; transform:translateY(20px);
    animation: locUp 0.5s ease forwards;
}
@keyframes locUp { to { opacity:1; transform:translateY(0); } }

.loc-row {
    opacity:0; transform:translateX(-12px);
    animation: locSlide 0.45s ease forwards;
}
@keyframes locSlide { to { opacity:1; transform:translateX(0); } }

/* KPI card */
.kpi-loc {
    border-radius:16px; padding:24px 20px;
    background:var(--glass-bg);
    backdrop-filter:blur(18px) saturate(180%);
    -webkit-backdrop-filter:blur(18px) saturate(180%);
    border:1px solid var(--glass-border);
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
    position:relative; overflow:hidden; isolation:isolate; text-align:center;
    transition:background-color 0.25s ease;
}
.kpi-loc::before {
    content:''; position:absolute;
    top:-30px; right:-30px;
    width:100px; height:100px;
    border-radius:50%; opacity:0.06;
    z-index:-1;
}
.kpi-loc::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.kpi-loc:hover::after { opacity:.6; }
.kpi-loc-1::before { background:#6366f1; }
.kpi-loc-2::before { background:#0096FF; }
.kpi-loc-3::before { background:#f59e0b; }
.kpi-num { font-size:40px; font-weight:900; line-height:1; }
.kpi-lbl { font-size:11px; font-weight:700; color:var(--text-faint); text-transform:uppercase; letter-spacing:1px; margin-top:6px; }
.kpi-icon { font-size:28px; margin-bottom:8px; display:block; animation:iconBounce 2s ease-in-out infinite; }
@keyframes iconBounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }

/* Location cards */
.loc-card {
    position:relative; overflow:hidden; isolation:isolate;
    background:var(--glass-bg);
    backdrop-filter:blur(18px) saturate(180%);
    -webkit-backdrop-filter:blur(18px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:14px; padding:20px;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
    border-left:4px solid transparent;
    transition:all 0.25s, background-color 0.25s ease;
}
.loc-card:hover { transform:translateX(4px); }
.loc-card::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.5; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.loc-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.loc-card:hover::after { opacity:.6; }
@media (prefers-reduced-motion: reduce) { .loc-card::before { animation: none; } }
.loc-card.op  { border-left-color:#22c55e; }
.loc-card.par { border-left-color:#f59e0b; }
.loc-card.low { border-left-color:#ef4444; }

.loc-icon-circle {
    width:48px; height:48px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; flex-shrink:0;
}

/* Capacity bar */
.cap-bar-wrap { background:var(--border-subtle); border-radius:6px; height:8px; overflow:hidden; margin-top:6px; }
.cap-bar { height:100%; border-radius:6px; width:0%; transition:width 1.3s cubic-bezier(0.25,0.46,0.45,0.94); }

/* Status badge */
.status-op  { background:#dcfce7; color:#16a34a; }
.status-par { background:#fef9c3; color:#b45309; }
.status-low { background:#fee2e2; color:#dc2626; }
.status-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }

/* Updated dot */
.live-dot {
    width:7px; height:7px; border-radius:50%; background:#22c55e;
    display:inline-block; margin-right:5px;
    animation:livePulse 2s ease-in-out infinite;
}
@keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.8)} }
</style>

<!-- ======= KPI CARDS ======= -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-loc kpi-loc-1 loc-kpi" style="animation-delay:0.05s">
            <span class="kpi-icon"><i class="ph ph-package" style="font-size:28px;color:#6366f1"></i></span>
            <div class="kpi-num text-primary" id="kpiItems" data-target="<?= $totalItems ?>">0</div>
            <div class="kpi-lbl">Items Across All Locations</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-loc kpi-loc-2 loc-kpi" style="animation-delay:0.15s">
            <span class="kpi-icon"><i class="ph ph-buildings" style="font-size:28px;color:#0096FF"></i></span>
            <div class="kpi-num" style="color:#0096FF" id="kpiLoc" data-target="<?= count($locations) ?>">0</div>
            <div class="kpi-lbl">Total Storage Locations</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-loc kpi-loc-3 loc-kpi" style="animation-delay:0.25s">
            <span class="kpi-icon"><i class="ph ph-warning-circle" style="font-size:28px;color:#f59e0b"></i></span>
            <div class="kpi-num text-warning" id="kpiLow" data-target="<?= $lowStockCount ?>">0</div>
            <div class="kpi-lbl">Locations Low Stock</div>
        </div>
    </div>
</div>

<!-- ======= LOCATION CARDS GRID ======= -->
<div class="row g-3">
    <?php foreach ($locations as $i => $loc):
        $pct    = min((int)$loc['capacity_pct'], 100);
        $li     = $locIcons[$loc['location_type']] ?? ['icon'=>'<i class="ph ph-map-pin"></i>','color'=>'#888'];
        $barClr = $pct > 80 ? '#ef4444' : ($pct > 50 ? '#f59e0b' : '#22c55e');
        $stMap  = ['Operational'=>['cls'=>'op','badge'=>'status-op'],'Partial Low'=>['cls'=>'par','badge'=>'status-par'],'Low Stock'=>['cls'=>'low','badge'=>'status-low']];
        $st     = $stMap[$loc['status']] ?? ['cls'=>'op','badge'=>'status-op'];
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="loc-card <?= $st['cls'] ?> loc-row" style="animation-delay:<?= 0.3 + $i*0.08 ?>s">

            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="loc-icon-circle" style="background:<?= $li['color'] ?>18">
                    <span><?= $li['icon'] ?></span>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:15px;font-weight:700;color:var(--text-primary)"><?= clean($loc['location_name']) ?></div>
                    <div style="font-size:12px;color:var(--text-faint)"><?= clean($loc['location_type']) ?> · <?= clean($loc['floor_area'] ?? '') ?></div>
                </div>
                <span class="status-badge <?= $st['badge'] ?>"><?= $loc['status'] ?></span>
            </div>

            <!-- Stats row -->
            <div class="d-flex gap-3 mb-3">
                <div style="text-align:center;flex:1;background:var(--bg-subtle);border-radius:8px;padding:8px">
                    <div class="loc-items-num" data-target="<?= $loc['current_items'] ?>"
                         style="font-size:20px;font-weight:800;color:<?= $li['color'] ?>">0</div>
                    <div style="font-size:10px;color:var(--text-faint);font-weight:700">ITEMS</div>
                </div>
                <div style="text-align:center;flex:1;background:var(--bg-subtle);border-radius:8px;padding:8px">
                    <div style="font-size:20px;font-weight:800;color:var(--text-secondary)">
                        <span class="loc-pct-num" data-pct="<?= $pct ?>">0</span>%
                    </div>
                    <div style="font-size:10px;color:var(--text-faint);font-weight:700">CAPACITY</div>
                </div>
            </div>

            <!-- Capacity bar -->
            <div>
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:11px;color:var(--text-muted);font-weight:600">Storage Usage</span>
                    <span style="font-size:11px;color:<?= $barClr ?>;font-weight:700"><?= $pct ?>%</span>
                </div>
                <div class="cap-bar-wrap">
                    <div class="cap-bar" data-pct="<?= $pct ?>" style="background:<?= $barClr ?>"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:1px solid var(--border-subtle)">
                <div style="font-size:11px;color:var(--text-faint)">
                    <span class="live-dot"></span>
                    <?= date('d M, H:i', strtotime($loc['updated_at'] ?? 'now')) ?>
                </div>
                <div style="font-size:11px;color:var(--text-faint)">
                    <?= number_format($loc['current_items']) ?> / <?= number_format($loc['capacity'] ?? 0) ?> units
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
window.addEventListener('load', function() {
    // KPI counter animations
    [
        ['kpiItems', 1400],
        ['kpiLoc',   800],
        ['kpiLow',   600],
    ].forEach(([id, dur]) => {
        const el = document.getElementById(id);
        if (!el) return;
        const target = parseInt(el.dataset.target) || 0;
        const start  = performance.now();
        function tick(now) {
            const p    = Math.min((now - start) / dur, 1);
            const ease = 1 - Math.pow(1-p, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(tick);
    });

    // Items counter per card
    document.querySelectorAll('.loc-items-num').forEach((el, i) => {
        const target = parseInt(el.dataset.target) || 0;
        setTimeout(() => {
            const dur = 1200, start = performance.now();
            function tick(now) {
                const p = Math.min((now-start)/dur,1);
                const ease = 1-Math.pow(1-p,3);
                el.textContent = Math.floor(ease*target).toLocaleString();
                if (p<1) requestAnimationFrame(tick);
                else el.textContent = target.toLocaleString();
            }
            requestAnimationFrame(tick);
        }, 300 + i * 100);
    });

    // Pct counter per card
    document.querySelectorAll('.loc-pct-num').forEach((el, i) => {
        const target = parseInt(el.dataset.pct) || 0;
        setTimeout(() => {
            let count = 0;
            const step = Math.max(1, Math.ceil(target/30));
            const iv = setInterval(() => {
                count += step;
                if (count >= target) { count = target; clearInterval(iv); }
                el.textContent = count;
            }, 35);
        }, 350 + i * 100);
    });

    // Capacity bars — double rAF
    requestAnimationFrame(() => requestAnimationFrame(() => {
        document.querySelectorAll('.cap-bar').forEach((bar, i) => {
            setTimeout(() => { bar.style.width = bar.dataset.pct + '%'; }, 400 + i * 80);
        });
    }));
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/app.php'; ?>