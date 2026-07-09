<?php
$pageTitle = 'Suppliers';
ob_start();

// Official website + logo domain map
$supplierMeta = [
    'Merck'       => ['domain'=>'merck.com',           'web'=>'https://www.merck.com/',                     'color'=>'#003087'],
    'P&G'         => ['domain'=>'pg.com',               'web'=>'https://www.pgcareers.com/global/en',        'color'=>'#003087'],
    'Nestle'      => ['domain'=>'nestle.com',           'web'=>'https://www.nestle.com.my/',                 'color'=>'#009fe3'],
    'Colgate'     => ['domain'=>'colgate.com',          'web'=>'https://www.colgate.com/en-my/optic-white?gclsrc=aw.ds&gad_source=1&gad_campaignid=23203618290&gbraid=0AAAAAC4ciwUViVKVhBehOMIMczHW9dDze&gclid=Cj0KCQjw_7PRBhDcARIsAMjV7jkraROHmaC11Nr2dEp23JHSp1P1-japTQQIcG-Wxmoc2DKPTX89pOgaAoKCEALw_wcB', 'color'=>'#e31837'],
    'Kimberley'   => ['domain'=>'kimberly-clark.com',   'web'=>'https://www.kimberly-clark.com/en-us/',      'color'=>'#1b76bc'],
    'F&N'         => ['domain'=>'fn.com.my',            'web'=>'https://www.fn.com.my/',                     'color'=>'#e30613'],
    'Unilever'    => ['domain'=>'unilever.com',         'web'=>'https://www.unilever.com.my/',               'color'=>'#1f36c7'],
    'Dutch Lady'  => ['domain'=>'dutchlady.com.my',     'web'=>'https://www.dutchlady.com.my/',              'color'=>'#003da5'],
    'Cellini'     => ['domain'=>'cellini.com.my',       'web'=>'https://www.cellini.com.my/?srsltid=AfmBOorWtDPSGpt5Wu9T731pf53eDaCNqeWRdjAhmGGEVVxV_33EXMKD', 'color'=>'#8b6914'],
    'Carlsberg'   => ['domain'=>'carlsberg.com',        'web'=>'https://carlsbergmalaysia.com.my/',          'color'=>'#007934'],
    'Petronas'    => ['domain'=>'petronas.com',         'web'=>'https://www.petronas.com/pcg/',              'color'=>'#00a19c'],
    'Amway'       => ['domain'=>'amway.com',            'web'=>'https://www.amway.my/',                      'color'=>'#00558c'],
];

function getSupMeta(string $name, array $map): array {
    foreach ($map as $key => $data) {
        if (stripos($name, $key) !== false) return $data;
    }
    return ['domain'=>'', 'web'=>'#', 'color'=>'#0096FF'];
}
?>

<!-- Phosphor Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />


<style>
/* KPI row */
.sup-kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px; }
.sup-kpi {
    position:relative; overflow:hidden; isolation:isolate;
    background:var(--glass-bg);
    backdrop-filter:blur(16px) saturate(180%);
    -webkit-backdrop-filter:blur(16px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:12px; padding:16px 20px;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 20px var(--glass-shadow);
    display:flex; align-items:center; gap:14px;
    transition:transform 0.2s ease, background-color 0.25s ease, box-shadow 0.25s ease;
}
.sup-kpi:hover { transform:translateY(-3px); }
.sup-kpi::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.5; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.sup-kpi::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.sup-kpi:hover::after { opacity:.6; }
@media (prefers-reduced-motion: reduce) { .sup-kpi::before { animation: none; } }
.sup-kpi-icon { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:22px; animation:iconBob 3s ease-in-out infinite; }
@keyframes iconBob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

/* Supplier card */
.sup-card {
    position:relative; overflow:hidden; isolation:isolate;
    background:var(--glass-bg);
    backdrop-filter:blur(18px) saturate(180%);
    -webkit-backdrop-filter:blur(18px) saturate(180%);
    border:1px solid var(--glass-border);
    border-radius:16px; padding:20px;
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow); height:100%;
    transition:all 0.22s cubic-bezier(0.23,1,0.32,1), background-color 0.25s ease;
    border-top:3px solid var(--sc,#0096FF);
    opacity:0; transform:translateY(14px);
    animation:supCardIn 0.45s ease forwards;
    will-change:transform;
}
@keyframes supCardIn { to { opacity:1; transform:translateY(0); } }
.sup-card:hover { transform:translateY(-5px); }
.sup-card::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.5; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.sup-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.sup-card:hover::after { opacity:.6; }
@media (prefers-reduced-motion: reduce) { .sup-card::before { animation: none; } }

/* Logo container */
.sup-logo-wrap {
    width:54px; height:54px; border-radius:13px; flex-shrink:0;
    border:1.5px solid var(--border-color); background:#fff;
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08);
    transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
}
.sup-card:hover .sup-logo-wrap { transform:scale(1.08) rotate(-3deg); }
.sup-logo-wrap img { width:100%; height:100%; object-fit:contain; padding:6px; }
.sup-logo-fallback {
    width:100%; height:100%; display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:800; color:#fff;
    background:var(--sc,#0096FF);
}

/* Rating stars — kekalkan emoji ⭐ */
.star-fill { color:#f59e0b; }
.star-empty { color:var(--border-color); }

/* Stat chips */
.sup-stat { text-align:center; flex:1; }
.sup-stat .sv { font-size:18px; font-weight:800; }
.sup-stat .sl { font-size:10px; color:var(--text-faint); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-top:1px; }

/* Action buttons */
.btn-newpo {
    flex:2; background:linear-gradient(135deg,#0096FF,#6366f1);
    color:#fff; border:none; border-radius:10px; padding:9px 0;
    font-size:12px; font-weight:700; cursor:pointer;
    transition:all 0.2s ease; text-align:center; text-decoration:none;
    display:flex; align-items:center; justify-content:center; gap:5px;
    box-shadow:0 3px 10px rgba(0,150,255,0.3);
}
.btn-newpo:hover { color:#fff; filter:brightness(1.1); transform:translateY(-1px); box-shadow:0 5px 16px rgba(0,150,255,0.4); }

.btn-hist {
    flex:1; background:var(--bg-subtle); color:var(--text-secondary);
    border:1.5px solid var(--border-color); border-radius:10px; padding:9px 0;
    font-size:12px; font-weight:600; cursor:pointer; text-align:center;
    text-decoration:none; transition:all 0.18s ease;
    display:flex; align-items:center; justify-content:center; gap:4px;
}
.btn-hist:hover { background:var(--border-color); color:var(--text-primary); }

.btn-web {
    flex:1; background:#f0fdf4; color:#166534;
    border:1.5px solid #bbf7d0; border-radius:10px; padding:9px 0;
    font-size:12px; font-weight:600; cursor:pointer; text-align:center;
    text-decoration:none; transition:all 0.18s ease;
    display:flex; align-items:center; justify-content:center; gap:4px;
}
.btn-web:hover { background:#dcfce7; color:#14532d; }
</style>

<!-- KPI Strip -->
<div class="sup-kpi-row">
    <div class="sup-kpi">
        <div class="sup-kpi-icon" style="background:#dbeafe"><i class="ph ph-factory" style="font-size:22px;color:#0096FF"></i></div>
        <div>
            <div style="font-size:28px;font-weight:900;color:#0096FF" id="kpiSup" data-target="<?= count($suppliers) ?>">0</div>
            <div style="font-size:11px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Active Suppliers</div>
        </div>
    </div>
    <div class="sup-kpi">
        <div class="sup-kpi-icon" style="background:#dcfce7">⭐</div>
        <div>
            <?php $avgRating = count($suppliers) ? round(array_sum(array_column($suppliers,'rating'))/count($suppliers),1) : 0; ?>
            <div style="font-size:28px;font-weight:900;color:#f59e0b"><?= $avgRating ?></div>
            <div style="font-size:11px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Avg Rating</div>
        </div>
    </div>
    <div class="sup-kpi">
        <div class="sup-kpi-icon" style="background:#ede9fe"><i class="ph ph-coins" style="font-size:22px;color:#8b5cf6"></i></div>
        <div>
            <?php $totalYTD = array_sum(array_column($suppliers,'ytd_orders_value')); ?>
            <div style="font-size:22px;font-weight:900;color:#8b5cf6" id="kpiYTD" data-target="<?= round($totalYTD/1000) ?>">RM 0K</div>
            <div style="font-size:11px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:0.5px">Total YTD Orders</div>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div style="font-size:14px;color:var(--text-secondary);font-weight:600">
        <?= count($suppliers) ?> active supplier<?= count($suppliers)!==1?'s':'' ?>
    </div>
    <?php if (Auth::hasRole('Inventory Manager','Procurement Officer')): ?>
    <a href="<?= APP_URL ?>/suppliers/create" class="btn btn-primary px-4"
       style="border-radius:12px;font-size:14px;font-weight:600;padding:10px 22px">
        <i class="ph ph-plus me-2"></i>Add Supplier
    </a>
    <?php endif; ?>
</div>

<!-- Supplier Cards -->
<div class="row g-3">
    <?php foreach ($suppliers as $i => $sup):
        $meta   = getSupMeta($sup['supplier_name'], $supplierMeta);
        $domain = $meta['domain'];
        $webUrl = $meta['web'];
        $color  = $meta['color'];
        $initials = strtoupper(substr($sup['supplier_name'], 0, 2));
        // Star rating — kekalkan emoji ⭐
        $rating = (float)$sup['rating'];
        $stars  = '';
        for ($s=1;$s<=5;$s++) $stars .= $s<=$rating ? '<span class="star-fill">⭐</span>' : '<span class="star-empty">★</span>';
    ?>
    <div class="col-md-4 col-sm-6">
        <div class="sup-card" style="--sc:<?= $color ?>;animation-delay:<?= $i*0.06 ?>s">

            <!-- Logo + Name -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="sup-logo-wrap" style="--sc:<?= $color ?>">
                    <?php if ($domain): ?>
                    <img id="logo-<?= $i ?>"
                         src="https://logo.clearbit.com/<?= $domain ?>"
                         alt="<?= clean($sup['supplier_name']) ?>"
                         style="width:100%;height:100%;object-fit:contain;padding:6px"
                         onerror="
                            this.onerror=null;
                            this.src='https://www.google.com/s2/favicons?domain=<?= $domain ?>&sz=128';
                            this.onerror=function(){
                                this.style.display='none';
                                this.nextElementSibling.style.display='flex';
                            };
                         ">
                    <div class="sup-logo-fallback" style="display:none"><?= $initials ?></div>
                    <?php else: ?>
                    <div class="sup-logo-fallback"><?= $initials ?></div>
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= clean($sup['supplier_name']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-faint);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= clean($sup['category'] ?? '') ?>
                    </div>
                </div>
            </div>

            <!-- Stars — kekalkan emoji ⭐ -->
            <div style="font-size:16px;margin-bottom:14px"><?= $stars ?> <span style="font-size:12px;font-weight:700;color:#f59e0b"><?= number_format($rating,1) ?></span></div>

            <!-- Stats row -->
            <div class="d-flex gap-2 p-3 rounded-3 mb-3" style="background:var(--bg-subtle);border:1px solid var(--border-subtle)">
                <div class="sup-stat">
                    <div class="sv text-warning"><?= number_format($sup['rating'],1) ?> ⭐</div>
                    <div class="sl">Rating</div>
                </div>
                <div style="width:1px;background:var(--border-color)"></div>
                <div class="sup-stat">
                    <div class="sv" style="color:var(--text-secondary)"><?= $sup['lead_time_days'] ?>d</div>
                    <div class="sl">Lead Time</div>
                </div>
                <div style="width:1px;background:var(--border-color)"></div>
                <div class="sup-stat">
                    <div class="sv text-success">RM<?= number_format($sup['ytd_orders_value']/1000,0) ?>K</div>
                    <div class="sl">YTD Orders</div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex gap-2">
                <a href="<?= APP_URL ?>/purchase-orders/create" class="btn-newpo">
                    <i class="ph ph-file-plus"></i> New Purchase Order
                </a>
                <a href="<?= APP_URL ?>/purchase-orders?supplier=<?= $sup['supplier_id'] ?>" class="btn-hist">
                    <i class="ph ph-clock-counter-clockwise"></i> History
                </a>
                <a href="<?= $webUrl ?>" target="_blank" rel="noopener" class="btn-web" title="Visit official website">
                    <i class="ph ph-globe"></i> Website
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
window.addEventListener('load', function() {
    const supEl = document.getElementById('kpiSup');
    const ytdEl = document.getElementById('kpiYTD');
    const supTarget = parseInt(supEl?.dataset.target) || 0;
    const ytdTarget = parseInt(ytdEl?.dataset.target) || 0;

    function cUp(el, target, dur, suffix) {
        if (!el) return;
        const start = performance.now();
        (function tick(now) {
            const p = Math.min((now-start)/dur,1), ease=1-Math.pow(1-p,3);
            el.textContent = (suffix==='k' ? 'RM ' : '') + Math.floor(ease*target) + (suffix==='k'?'K':'');
            if (p<1) requestAnimationFrame(tick);
            else el.textContent = (suffix==='k'?'RM ':'')+target+(suffix==='k'?'K':'');
        })(start);
    }
    cUp(supEl, supTarget, 800, '');
    cUp(ytdEl, ytdTarget, 1200, 'k');
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>