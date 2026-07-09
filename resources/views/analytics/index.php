<?php
$pageTitle = 'Analytics';
ob_start();
?>

<style>
/* ======= ANALYTICS PAGE ANIMATIONS ======= */
.an-card {
    position: relative; overflow: hidden; isolation: isolate;
    background: var(--glass-bg);
    backdrop-filter: blur(18px) saturate(180%);
    -webkit-backdrop-filter: blur(18px) saturate(180%);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
    opacity: 0;
    transform: translateY(16px);
    animation: anUp 0.5s cubic-bezier(0.23,1,0.32,1) forwards;
    transition: background-color 0.25s ease, box-shadow 0.25s ease;
}
@keyframes anUp { to { opacity:1; transform:translateY(0); } }
.an-card::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.5; transform:translateX(-130%); animation:glassSheen 8s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
.an-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
.an-card:hover::after { opacity:.6; }
@media (prefers-reduced-motion: reduce) { .an-card::before { animation: none; } }

.an-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.an-card-sub {
    font-size: 11px;
    color: var(--text-faint);
    margin-bottom: 14px;
}

/* Note badge for estimated/derived data */
.an-note {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    color: var(--text-faint);
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 3px 10px;
    margin-bottom: 10px;
}
</style>

<!-- ============================================================
     ROW 1 — Inventory Valuation Trend (candlestick, full width)
     ============================================================ -->
<div class="an-card mb-3" style="animation-delay:0.05s">
    <div class="an-card-title"><i class="ph-fill ph-trend-up" style="margin-right:6px"></i> Inventory Valuation Trend</div>
    <div class="an-card-sub">Estimated daily opening/closing inventory value, reconstructed from recorded consumption (stock_movements)</div>
    <div class="an-note"><i class="ph-fill ph-info"></i> Estimated trend, not a literal historical ledger</div>
    <div id="chartValuation"></div>
</div>

<!-- ============================================================
     ROW 2 — Fast/Slow Movers + Supplier Performance
     ============================================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="an-card h-100" style="animation-delay:0.12s">
            <div class="an-card-title"><i class="ph-fill ph-rocket-launch" style="margin-right:6px"></i> Fast vs Slow-Moving Items</div>
            <div class="an-card-sub">Top 10 items ranked by total units consumed (stock_movements)</div>
            <div id="chartMovers"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="an-card h-100" style="animation-delay:0.18s">
            <div class="an-card-title"><i class="ph-fill ph-truck" style="margin-right:6px"></i> Supplier Delivery Performance</div>
            <div class="an-card-sub">Purchase order outcomes per supplier (purchase_orders)</div>
            <div id="chartSupplierPerf"></div>
        </div>
    </div>
</div>

<!-- ============================================================
     ROW 3 — Consumption Heatmap + Supplier Ratings
     ============================================================ -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="an-card h-100" style="animation-delay:0.24s">
            <div class="an-card-title"><i class="ph-fill ph-fire" style="margin-right:6px"></i> Consumption Heatmap</div>
            <div class="an-card-sub">Units consumed by day &amp; location (stock_movements)</div>
            <div id="chartHeatmap"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="an-card h-100" style="animation-delay:0.30s">
            <div class="an-card-title"><i class="ph-fill ph-star" style="margin-right:6px"></i> Supplier Ratings</div>
            <div class="an-card-sub">Active suppliers ranked by performance rating (suppliers)</div>
            <div id="chartSupplierCat"></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// ---- Data payloads (escaped for safe inline JS embedding) ----
$valuationJs    = json_encode($valuation, JSON_UNESCAPED_UNICODE);
$moversJs       = json_encode($movers, JSON_UNESCAPED_UNICODE);
$supplierPerfJs = json_encode($supplierPerf, JSON_UNESCAPED_UNICODE);
$heatmapJs      = json_encode($heatmap, JSON_UNESCAPED_UNICODE);
$supplierMapJs  = json_encode($supplierMap, JSON_UNESCAPED_UNICODE);

$extraJs = "
const anValuation    = $valuationJs;
const anMovers       = $moversJs;
const anSupplierPerf = $supplierPerfJs;
const anHeatmap       = $heatmapJs;
const anSupplierMap  = $supplierMapJs;

// Detect current theme for chart styling
function anCurrentTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
}
const anFg = anCurrentTheme() === 'dark' ? '#b8bdd0' : '#475569';

// ===================================================================
// 1. Inventory Valuation Trend — Candlestick
// ===================================================================
const valuationSeries = [{
    data: anValuation.map(function(row) {
        return { x: row[0], y: [row[1], row[2], row[3], row[4]] };
    })
}];

new ApexCharts(document.querySelector('#chartValuation'), {
    chart: { type:'candlestick', height:280, toolbar:{show:false}, foreColor: anFg,
             animations: { enabled:true, easing:'easeinout', speed:700 } },
    series: valuationSeries,
    xaxis: { type:'category' },
    yaxis: { tooltip: { enabled:true }, labels: { formatter: function(v){ return 'RM ' + Math.round(v).toLocaleString(); } } },
    plotOptions: { candlestick: { colors: { upward:'#22c55e', downward:'#ef4444' } } },
    grid: { borderColor: 'rgba(148,163,184,0.18)' },
}).render();

// ===================================================================
// 2. Fast vs Slow-Moving Items — horizontal bar
// ===================================================================
const moverNames  = anMovers.map(function(r){ return r.item_name; });
const moverValues = anMovers.map(function(r){ return parseInt(r.total_out); });
const moverMax    = Math.max.apply(null, moverValues.concat([1]));

new ApexCharts(document.querySelector('#chartMovers'), {
    chart: { type:'bar', height:300, toolbar:{show:false}, foreColor: anFg,
             animations: { enabled:true, easing:'easeout', speed:800 } },
    series: [{ name:'Units Consumed', data: moverValues }],
    plotOptions: { bar: { horizontal:true, borderRadius:6, distributed:true } },
    colors: moverValues.map(function(v){
        const pct = v / moverMax;
        return pct > 0.6 ? '#22c55e' : (pct > 0.3 ? '#f59e0b' : '#94a3b8');
    }),
    dataLabels: { enabled:true, style:{ colors:['#fff'] } },
    xaxis: { categories: moverNames },
    legend: { show:false },
    grid: { borderColor: 'rgba(148,163,184,0.18)' },
}).render();

// ===================================================================
// 3. Supplier Delivery Performance — stacked bar
// ===================================================================
const supNames = anSupplierPerf.map(function(r){ return r.supplier_name; });

new ApexCharts(document.querySelector('#chartSupplierPerf'), {
    chart: { type:'bar', height:300, stacked:true, toolbar:{show:false}, foreColor: anFg,
             animations: { enabled:true, easing:'easeout', speed:800 } },
    series: [
        { name:'Delivered', data: anSupplierPerf.map(function(r){ return parseInt(r.delivered); }) },
        { name:'Pending/In Transit', data: anSupplierPerf.map(function(r){ return parseInt(r.pending); }) },
        { name:'Cancelled', data: anSupplierPerf.map(function(r){ return parseInt(r.cancelled); }) },
    ],
    colors: ['#22c55e','#f59e0b','#ef4444'],
    plotOptions: { bar: { borderRadius:4, columnWidth:'55%' } },
    xaxis: { categories: supNames, labels: { rotate:-30, style:{ fontSize:'10px' } } },
    legend: { position:'top', fontSize:'11px' },
    grid: { borderColor: 'rgba(148,163,184,0.18)' },
}).render();

// ===================================================================
// 4. Consumption Heatmap
// ===================================================================
new ApexCharts(document.querySelector('#chartHeatmap'), {
    chart: { type:'heatmap', height:300, toolbar:{show:false}, foreColor: anFg,
             animations: { enabled:true, easing:'easeout', speed:800 } },
    series: anHeatmap.series.map(function(s){
        return { name: s.name, data: anHeatmap.days.map(function(d,i){ return { x:d, y:s.data[i] }; }) };
    }),
    plotOptions: { heatmap: { colorScale: { ranges: [
        { from:0,  to:0,   color:'#e2e8f0', name:'No data' },
        { from:1,  to:30,  color:'#bbf7d0', name:'Low' },
        { from:31, to:70,  color:'#4ade80', name:'Medium' },
        { from:71, to:999, color:'#16a34a', name:'High' },
    ]}}},
    dataLabels: { enabled:false },
    grid: { borderColor: 'rgba(148,163,184,0.18)' },
}).render();

// ===================================================================
// 5. Supplier Ratings — horizontal bar, sorted (reuses existing data)
// ===================================================================
(function initSupplierRatings() {
    const el = document.querySelector('#chartSupplierCat');
    if (!el) return;

    // Sort high -> low, take top 10 to keep it readable
    const rows = anSupplierMap
        .map(function(s){
            return { name: s.name.split('—')[0].trim(), rating: parseFloat(s.rating) || 0 };
        })
        .sort(function(a, b){ return a.rating - b.rating; }) // asc: ApexCharts draws bottom-up
        .slice(-10);

    const names   = rows.map(function(r){ return r.name; });
    const ratings = rows.map(function(r){ return r.rating; });

    new ApexCharts(el, {
        chart: { type:'bar', height:340, toolbar:{show:false}, foreColor: anFg,
                 animations: { enabled:true, easing:'easeout', speed:800 } },
        series: [{ name:'Rating', data: ratings }],
        plotOptions: { bar: { horizontal:true, borderRadius:6, distributed:true, barHeight:'70%' } },
        colors: ratings.map(function(v){
            return v >= 4.5 ? '#22c55e' : (v >= 3.5 ? '#f59e0b' : '#ef4444');
        }),
        dataLabels: {
            enabled:true,
            formatter: function(v){ return '★ ' + v.toFixed(1); },
            style:{ colors:['#fff'], fontSize:'11px', fontWeight:600 }
        },
        xaxis: {
            categories: names,
            max: 5,
            tickAmount: 5,
            labels: { style:{ fontSize:'10px' } }
        },
        yaxis: { labels: { style:{ fontSize:'11px' } } },
        legend: { show:false },
        tooltip: { y: { formatter: function(v){ return v.toFixed(1) + ' / 5.0'; } } },
        grid: { borderColor: 'rgba(148,163,184,0.18)' },
    }).render();
})();
";

require_once __DIR__ . '/../layouts/app.php';
?>