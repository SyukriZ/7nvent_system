<?php $pageTitle = 'Reports'; ob_start();
$totalVal   = array_sum(array_column($categoryBreakdown, 'value'));
$totalItems = array_sum(array_column($categoryBreakdown, 'quantity'));
?>

<style>
/* GPU-accelerated base */
* { -webkit-font-smoothing:antialiased; }
.rep-card {
    opacity:0; transform:translateY(16px) translateZ(0);
    animation:repUp 0.45s cubic-bezier(0.23,1,0.32,1) forwards;
    will-change:transform,opacity;
}
@keyframes repUp { to { opacity:1; transform:translateY(0) translateZ(0); } }

/* Report type cards */
.rcard {
    background:var(--glass-bg); border-radius:14px; padding:22px 20px;
    backdrop-filter:blur(18px) saturate(180%);
    -webkit-backdrop-filter:blur(18px) saturate(180%);
    border:1px solid var(--glass-border);
    box-shadow:0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
    transition:transform 0.22s cubic-bezier(0.23,1,0.32,1), box-shadow 0.22s ease, background-color 0.25s ease;
    position:relative; overflow:hidden; isolation:isolate; height:100%; display:flex; flex-direction:column;
    will-change:transform;
}
/* ::after already carries the per-report colored top strip, so the sheen
   sweep uses ::before instead (no pointer-glint here to avoid a 3rd
   pseudo-element fighting for the same box). */
.rcard::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.4; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
@media (prefers-reduced-motion: reduce) { .rcard::before { animation: none; } }
.rcard::after { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--rc); z-index:1; }
.rcard:hover { transform:translateY(-4px) translateZ(0); }
.rcard-icon {
    width:48px; height:48px; border-radius:13px;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; margin-bottom:12px;
    animation:iconFloat 3.5s ease-in-out infinite;
    will-change:transform;
}
.rcard-icon i { font-size: 24px; }
@keyframes iconFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
.rcard-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:5px; }
.rcard-desc  { font-size:12px; color:var(--text-faint); line-height:1.6; margin-bottom:14px; flex:1; }
.btn-gen {
    border:2px solid; border-radius:10px; padding:8px 16px;
    font-size:13px; font-weight:600;
    transition:all 0.18s cubic-bezier(0.23,1,0.32,1);
    text-decoration:none; display:inline-flex; align-items:center; gap:6px; width:fit-content;
}
.btn-gen:hover { transform:translateX(3px); }

/* KPI strip */
.kpi-strip { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
.kpi-item {
    background:linear-gradient(135deg,var(--ka),var(--kb));
    border-radius:11px; padding:12px 10px;
    color:#fff; text-align:center; position:relative; overflow:hidden;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    min-height:76px;
}
.kpi-item::after {
    content:var(--ki); position:absolute; right:-5px; bottom:-8px;
    font-size:38px; opacity:0.14; line-height:1; pointer-events:none;
}
.kpi-item .kv { font-size:clamp(13px,1.8vw,20px); font-weight:900; line-height:1.2; word-break:break-word; }
.kpi-item .kl { font-size:9px; font-weight:700; opacity:0.8; letter-spacing:0.5px; margin-top:3px; }

/* Jarvis chart container */
.jarvis-wrap {
    position:relative;
    background:radial-gradient(ellipse at 40% 40%, #0d1f3c 0%, #060d1a 100%);
    border-radius:13px; padding:14px;
    border:1px solid rgba(56,189,248,0.18);
    box-shadow:0 0 24px rgba(0,100,255,0.06),inset 0 0 30px rgba(0,0,0,0.25);
    will-change:auto;
}
.jarvis-wrap::before {
    content:''; position:absolute; inset:0; border-radius:13px; pointer-events:none;
    background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,150,255,0.018) 3px,rgba(0,150,255,0.018) 4px);
}
.j-scan {
    position:absolute; left:0; right:0; height:1px;
    background:linear-gradient(90deg,transparent 5%,rgba(56,189,248,0.5) 50%,transparent 95%);
    animation:jScan 4s ease-in-out infinite; pointer-events:none; z-index:2;
}
@keyframes jScan { 0%{top:0%;opacity:0} 8%{opacity:1} 92%{opacity:1} 100%{top:100%;opacity:0} }
.j-corner { position:absolute; width:10px; height:10px; border-color:rgba(56,189,248,0.55); border-style:solid; z-index:3; }
.jc-tl { top:5px;left:5px; border-width:2px 0 0 2px; }
.jc-tr { top:5px;right:5px; border-width:2px 2px 0 0; }
.jc-bl { bottom:5px;left:5px; border-width:0 0 2px 2px; }
.jc-br { bottom:5px;right:5px; border-width:0 2px 2px 0; }
.j-label {
    position:absolute; top:7px; left:50%; transform:translateX(-50%);
    font-size:9px; font-weight:700; color:rgba(56,189,248,0.65);
    letter-spacing:2px; text-transform:uppercase; z-index:3;
    animation:jBlink 2.5s ease-in-out infinite;
}
@keyframes jBlink { 0%,100%{opacity:0.65} 50%{opacity:1} }
.j-center {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-52%);
    text-align:center; pointer-events:none; z-index:4;
}
.j-center .jn { font-size:22px; font-weight:900; color:#38bdf8; text-shadow:0 0 12px rgba(56,189,248,0.7); }
.j-center .jl { font-size:9px; color:rgba(56,189,248,0.55); font-weight:700; letter-spacing:1px; margin-top:1px; }

/* Metric bars */
.metric-card { background:var(--bg-subtle); border-radius:11px; padding:14px 16px; border:1px solid var(--border-color); margin-bottom:14px; transition:background-color 0.25s ease, border-color 0.25s ease; }
.metric-bar-wrap { background:var(--border-color); border-radius:6px; height:10px; overflow:hidden; margin-top:8px; }
.metric-bar {
    height:100%; border-radius:6px; width:0%;
    transition:width 1.6s cubic-bezier(0.25,0.46,0.45,0.94);
    will-change:width; position:relative;
}
.metric-bar::after {
    content:''; position:absolute; right:0; top:0; bottom:0; width:18px;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,0.35));
    animation:mShimmer 2s ease-in-out infinite;
}
@keyframes mShimmer { 0%,100%{opacity:0} 60%{opacity:1} }
.metric-val { font-size:26px; font-weight:900; line-height:1; }

/* Val breakdown — stylish redesign */
.vb-wrap {
    background:var(--bg-subtle); border:1px solid var(--border-color);
    border-radius:14px; padding:16px 18px 14px; margin-top:6px;
    transition:background-color 0.25s ease, border-color 0.25s ease;
}
.vb-head {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:6px;
}
.vb-head .vb-head-lbl {
    font-size:11px; font-weight:700; color:var(--text-faint);
    text-transform:uppercase; letter-spacing:0.5px;
}
.vb-head .vb-head-lbl i { margin-right:6px; color:#8b5cf6; }
.vb-head .vb-total { font-size:13px; font-weight:800; color:var(--text-primary); }

.vb-chart-wrap { position:relative; padding:6px 0; }
.vb-chart-wrap::before {
    content:''; position:absolute; inset:14% 8%; border-radius:50%;
    background:radial-gradient(circle, rgba(139,92,246,0.16) 0%, transparent 72%);
    pointer-events:none; z-index:0;
}
.vb-center {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-54%);
    text-align:center; pointer-events:none; z-index:2;
}
.vb-center .vn { font-size:15px; font-weight:900; color:var(--text-primary); line-height:1.1; }
.vb-center .vl { font-size:8px; font-weight:700; letter-spacing:1px; color:var(--text-faint); margin-top:3px; }

.vb-row {
    display:flex; align-items:center; gap:9px;
    padding:7px 9px; border-radius:9px;
    transition:background-color 0.18s ease, transform 0.18s ease;
    cursor:default;
}
.vb-row:hover { transform:translateX(3px); }
.vb-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; box-shadow:0 0 8px var(--vc); background:var(--vc); }
.vb-label { font-size:12.5px; font-weight:600; color:var(--text-secondary); flex:1; }
.vb-bar-wrap { background:var(--border-color); border-radius:20px; height:6px; width:66px; overflow:hidden; flex-shrink:0; }
.vb-bar { height:100%; border-radius:20px; width:0%; background:linear-gradient(90deg,var(--vc)99,var(--vc)); transition:width 1.4s cubic-bezier(0.25,0.46,0.45,0.94); will-change:width; }
.vb-pct { font-size:10.5px; font-weight:800; color:var(--vc); min-width:30px; text-align:right; flex-shrink:0; }
.vb-amt { font-size:12px; font-weight:700; color:var(--text-primary); min-width:92px; text-align:right; flex-shrink:0; }
</style>

<!-- REPORT CARDS -->
<div class="row g-3 mb-4">
<?php
$rc = [
    ['Stock Summary Reports',  'Complete inventory snapshot across all locations with category breakdown and par level comparisons.',  'ph-fill ph-package','stock-summary','#0096FF'],
    ['Consumption Analytics',  'Weekly and monthly consumption trends per category with demand forecasting for the next 30 days.',    'ph-fill ph-chart-line-up','consumption',  '#22c55e'],
    ['Purchase Order History', 'All Purchase Orders approval status, supplier performance, delivery timelines and cost analysis.',    'ph-fill ph-receipt','po-history',   '#f59e0b'],
    ['Inventory Valuation',    'Current stock value by category, holding costs, and month-on-month cost optimization tracking.',      'ph-fill ph-coins','valuation',    '#8b5cf6'],
    ['Supplier Performance',   'Lead time accuracy, delivery fulfillment rates, price comparison, and supplier rating scorecards.',   'ph-fill ph-truck','supplier',     '#ef4444'],
    ['Waste & Expiry Report',  'Track expired items, disposal records, FIFO compliance, and sustainability metrics vs 20% waste reduction target.','ph-fill ph-recycle','waste-expiry','#14b8a6'],
];
foreach($rc as $i=>[$t,$d,$ic,$type,$clr]):
?>
<div class="col-md-4">
    <div class="rcard rep-card" style="--rc:<?= $clr ?>;animation-delay:<?= $i*0.06 ?>s">
        <div class="rcard-icon" style="background:<?= $clr ?>18;animation-delay:<?= $i*0.4 ?>s"><i class="<?= $ic ?>"></i></div>
        <div class="rcard-title"><?= $t ?></div>
        <div class="rcard-desc"><?= $d ?></div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/reports/generate?type=<?= $type ?>&format=pdf"
               class="btn-gen" target="_blank"
               style="color:<?= $clr ?>;border-color:<?= $clr ?>40;background:<?= $clr ?>08;font-size:12px;padding:7px 12px"
               onmouseover="this.style.background='<?= $clr ?>';this.style.color='#fff'"
               onmouseout="this.style.background='<?= $clr ?>08';this.style.color='<?= $clr ?>'">
                <i class="ph-fill ph-file-pdf"></i> PDF
            </a>
            <a href="<?= APP_URL ?>/reports/generate?type=<?= $type ?>&format=csv"
               class="btn-gen"
               style="color:#22c55e;border-color:#22c55e40;background:#22c55e08;font-size:12px;padding:7px 12px"
               onmouseover="this.style.background='#22c55e';this.style.color='#fff'"
               onmouseout="this.style.background='#22c55e08';this.style.color='#22c55e'">
                <i class="ph-fill ph-file-csv"></i> CSV
            </a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- CHARTS + METRICS -->
<div class="row g-3">
    <!-- Left: Donut Chart -->
    <div class="col-md-5">
        <div class="stat-card h-100 rep-card" style="animation-delay:0.38s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold" style="font-size:14px"><i class="ph-fill ph-chart-pie-slice" style="margin-right:6px"></i>Stock by Category</div>
                <span style="font-size:11px;color:var(--text-faint)"><?= date('M Y') ?></span>
            </div>

            <!-- KPI Strip -->
            <div class="kpi-strip">
                <div class="kpi-item" style="--ka:#0096FF;--kb:#6366f1;--ki:'📦'">
                    <div class="kv" id="kpiTotal" data-target="<?= $totalItems ?>">0</div>
                    <div class="kl">TOTAL ITEMS</div>
                </div>
                <div class="kpi-item" style="--ka:#22c55e;--kb:#14b8a6;--ki:'🏷️'">
                    <div class="kv"><?= count($categoryBreakdown) ?></div>
                    <div class="kl">CATEGORIES</div>
                </div>
                <div class="kpi-item" style="--ka:#f59e0b;--kb:#ef4444;--ki:'💰'">
                    <div class="kv" id="kpiVal" data-target="<?= round($totalVal) ?>">RM 0</div>
                    <div class="kl">TOTAL VALUE</div>
                </div>
            </div>

            <!-- Jarvis Chart -->
            <div class="jarvis-wrap">
                <div class="j-scan"></div>
                <div class="j-corner jc-tl"></div><div class="j-corner jc-tr"></div>
                <div class="j-corner jc-bl"></div><div class="j-corner jc-br"></div>
                <div class="j-label">INVENTORY SCAN</div>
                <div style="position:relative;margin-top:12px">
                    <canvas id="catChart" height="195"></canvas>
                    <div class="j-center">
                        <div class="jn" id="jCenter">—</div>
                        <div class="jl">TOTAL ITEMS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Metrics + Breakdown -->
    <div class="col-md-7">
        <div class="stat-card h-100 rep-card" style="animation-delay:0.46s">
            <div class="fw-bold mb-4" style="font-size:14px"><i class="ph-fill ph-target" style="margin-right:6px"></i>System Performance Metrics — <?= date('M Y') ?></div>

            <?php
            $mCfg = [
                'manual_time_reduced'=>['Manual Counting Time Reduced','ph-fill ph-timer','#22c55e','Staff time saved vs manual process'],
                'inventory_accuracy' =>['Inventory Accuracy Rate',      'ph-fill ph-target','#0096FF','Data accuracy vs physical count'],               
                'waste_reduction'    =>['Waste Reduction vs Baseline',  'ph-fill ph-recycle','#ef4444','Waste saved compared to before 7NVENT'],
            ];
            foreach($metrics as $key=>$val):
                [$lbl,$ico,$clr,$dsc] = $mCfg[$key] ?? [$key,'ph-fill ph-chart-bar','var(--text-muted)',''];
            ?>
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:20px"><i class="<?= $ico ?>"></i></span>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--text-primary)"><?= $lbl ?></div>
                            <div style="font-size:11px;color:var(--text-faint)"><?= $dsc ?></div>
                        </div>
                    </div>
                    <div class="metric-val" data-target="<?= $val ?>" style="color:<?= $clr ?>">0%</div>
                </div>
                <div class="metric-bar-wrap">
                    <div class="metric-bar" data-pct="<?= $val ?>" style="background:linear-gradient(90deg,<?= $clr ?>99,<?= $clr ?>)"></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Value Breakdown -->
            <div class="vb-wrap">
                <div class="vb-head">
                    <div class="vb-head-lbl"><i class="ph-fill ph-coins"></i>Inventory Value Breakdown</div>
                    <div class="vb-total">RM <?= number_format($totalVal,2) ?></div>
                </div>
                <div class="row g-0 align-items-center">
                    <div class="col-md-5">
                        <div class="vb-chart-wrap">
                            <canvas id="valChart" height="150"></canvas>
                            <div class="vb-center">
                                <div class="vn" id="vbCenter">RM 0</div>
                                <div class="vl">TOTAL VALUE</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 ps-md-3">
                        <?php
                        $vc = ['#3b82f6','#22c55e','#f59e0b','#8b5cf6','#ef4444'];
                        foreach($categoryBreakdown as $ci=>$cat):
                            $pct = $totalVal>0 ? round($cat['value']/$totalVal*100) : 0;
                            $c   = $vc[$ci % 5];
                        ?>
                        <div class="vb-row" style="--vc:<?= $c ?>"
                             onmouseover="this.style.background='<?= $c ?>14'"
                             onmouseout="this.style.background='transparent'">
                            <div class="vb-dot"></div>
                            <span class="vb-label"><?= clean($cat['category']) ?></span>
                            <div class="vb-bar-wrap"><div class="vb-bar" data-pct="<?= $pct ?>"></div></div>
                            <span class="vb-pct"><?= $pct ?>%</span>
                            <span class="vb-amt">RM <?= number_format($cat['value'],2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content    = ob_get_clean();
$catLabels  = json_encode(array_column($categoryBreakdown,'category'));
$catQty     = json_encode(array_column($categoryBreakdown,'quantity'));
$catVals    = json_encode(array_column($categoryBreakdown,'value'));
$totalItemsJS = (int)$totalItems;
$totalValJS   = (int)round($totalVal);

$extraJs = "
function cUp(el,target,dur,isRM){
    if(!el||!target)return;
    const s=performance.now();
    (function tick(n){
        const p=Math.min((n-s)/dur,1), e=1-Math.pow(1-p,3), v=Math.floor(e*target);
        el.textContent=isRM?'RM '+v.toLocaleString():v.toLocaleString();
        if(p<1) requestAnimationFrame(tick);
        else el.textContent=isRM?'RM '+target.toLocaleString():target.toLocaleString();
    })(s);
}

window.addEventListener('load',function(){
    // KPI counters
    cUp(document.getElementById('kpiTotal'),$totalItemsJS,1100,false);
    cUp(document.getElementById('kpiVal'),$totalValJS,1300,true);

    // Glow plugin for Jarvis chart
    const glowPlugin={
        id:'glow',
        beforeDatasetsDraw(c){c.ctx.shadowBlur=16;c.ctx.shadowColor='rgba(56,189,248,0.45)';},
        afterDatasetsDraw(c){c.ctx.shadowBlur=0;c.ctx.shadowColor='transparent';}
    };

    // Main donut chart
    const ctx=document.getElementById('catChart')?.getContext('2d');
    if(ctx){
        Chart.register(glowPlugin);
        new Chart(ctx,{
            type:'doughnut',
            data:{
                labels:$catLabels,
                datasets:[{
                    data:$catQty,
                    backgroundColor:['rgba(56,189,248,0.88)','rgba(52,211,153,0.88)','rgba(251,191,36,0.88)','rgba(248,113,113,0.88)','rgba(167,139,250,0.88)'],
                    borderWidth:2, borderColor:'rgba(6,13,26,0.7)', hoverOffset:8,
                    hoverBackgroundColor:['#38bdf8','#34d399','#fbbf24','#f87171','#a78bfa'],
                }]
            },
            options:{
                responsive:true, cutout:'63%',
                animation:{animateRotate:true,animateScale:false,duration:1800,easing:'easeOutCubic',
                    onComplete:()=>{ cUp(document.getElementById('jCenter'),$totalItemsJS,700,false); }
                },
                plugins:{
                    legend:{position:'bottom',labels:{padding:10,font:{size:11},usePointStyle:true,pointStyle:'circle',color:'rgba(255,255,255,0.72)'}},
                    tooltip:{backgroundColor:'rgba(6,13,26,0.9)',borderColor:'rgba(56,189,248,0.35)',borderWidth:1,
                        titleColor:'#38bdf8',bodyColor:'rgba(255,255,255,0.8)',
                        callbacks:{label:c=>' '+c.label+': '+c.parsed.toLocaleString()+' units'}
                    },
                    glow:{}
                }
            }
        });
    }
    setTimeout(()=>{ const jc=document.getElementById('jCenter'); if(jc&&jc.textContent==='—') cUp(jc,$totalItemsJS,600,false); },2200);

    // Value breakdown — doughnut with rounded segments + center total
    const vCtx=document.getElementById('valChart')?.getContext('2d');
    if(vCtx){
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const vColors = ['#3b82f6','#22c55e','#f59e0b','#8b5cf6','#ef4444'];
        setTimeout(()=>{
            new Chart(vCtx,{
                type:'doughnut',
                data:{
                    labels:$catLabels,
                    datasets:[{data:$catVals,
                        backgroundColor:vColors,
                        borderWidth:3, borderColor: dark ? '#161b2c' : '#fff',
                        borderRadius:6, spacing:2, hoverOffset:8,
                        hoverBorderColor: dark ? '#161b2c' : '#fff'
                    }]
                },
                options:{
                    responsive:true, cutout:'70%',
                    animation:{animateRotate:true,duration:1400,easing:'easeOutCubic',
                        onComplete:()=>{ cUp(document.getElementById('vbCenter'),$totalValJS,700,true); }
                    },
                    plugins:{legend:{display:false},
                        tooltip:{backgroundColor:'rgba(15,23,42,0.92)',borderColor:'rgba(139,92,246,0.35)',borderWidth:1,
                            titleColor:'#c4b5fd',bodyColor:'rgba(255,255,255,0.85)',padding:10,
                            callbacks:{label:c=>' RM '+parseFloat(c.parsed).toLocaleString('en-MY',{minimumFractionDigits:2})}
                        }
                    }
                }
            });
        },300);
        setTimeout(()=>{ const vb=document.getElementById('vbCenter'); if(vb&&vb.textContent==='RM 0') cUp(vb,$totalValJS,600,true); },1900);
    }

    // Metrics bars + counters
    requestAnimationFrame(()=>requestAnimationFrame(()=>{
        document.querySelectorAll('.metric-bar').forEach((bar,i)=>{
            setTimeout(()=>{ bar.style.width=bar.dataset.pct+'%'; },250+i*220);
        });
        document.querySelectorAll('.metric-val').forEach((el,i)=>{
            const t=parseInt(el.dataset.target)||0;
            setTimeout(()=>{
                let c=0; const step=Math.max(1,Math.ceil(t/30));
                const iv=setInterval(()=>{c=Math.min(c+step,t);el.textContent=c+'%';if(c>=t)clearInterval(iv);},25);
            },250+i*220);
        });
        document.querySelectorAll('.vb-bar').forEach((b,i)=>{
            setTimeout(()=>{ b.style.width=b.dataset.pct+'%'; },500+i*100);
        });
    }));
});
";
require_once __DIR__.'/../layouts/app.php';
?>