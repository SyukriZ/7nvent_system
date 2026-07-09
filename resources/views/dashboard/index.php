<?php
$pageTitle = 'Dashboard';
ob_start();
?>

<style>
/* ======= DASHBOARD ANIMATIONS ======= */

/* Card slide-in */
.dash-card {
    opacity: 0;
    transform: translateY(20px);
    animation: cardSlideIn 0.5s ease forwards;
}
@keyframes cardSlideIn {
    to { opacity:1; transform:translateY(0); }
}

/* Skeleton shimmer */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.2s infinite;
    border-radius: 6px;
}
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Progress bar animation */
.prog-bar-anim {
    width: 0%;
    transition: width 1.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* KPI pulse after count */
@keyframes kpiPulse {
    0%  { transform:scale(1); }
    50% { transform:scale(1.05); }
    100%{ transform:scale(1); }
}
.kpi-pulse { animation: kpiPulse 0.4s ease; }

/* Refresh spin */
@keyframes spin { 100%{ transform:rotate(360deg); } }
.spin { animation: spin 0.6s linear; }

/* Activity item slide in */
.activity-item {
    opacity: 0;
    transform: translateX(-10px);
    animation: slideRight 0.4s ease forwards;
}
@keyframes slideRight { to { opacity:1; transform:translateX(0); } }

/* Status indicator pulse */
@keyframes statusPulse {
    0%,100% { box-shadow: 0 0 0 0 currentColor; }
    50%     { box-shadow: 0 0 0 4px transparent; }
}

/* Refresh button */
.refresh-btn {
    background: none; border: 1px solid var(--border-color);
    border-radius: 8px; padding: 4px 10px;
    font-size: 12px; color: var(--text-faint); cursor: pointer;
    transition: all 0.2s; display:flex; align-items:center; gap:5px;
}
.refresh-btn:hover { background: var(--bg-subtle); color:#0096FF; border-color:#0096FF; }
</style>

<!-- ======= KPI Stats Row ======= -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.05s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-package me-1"></i>Total Items in Stock</div>
                    <div class="stat-value" id="kpi-total" data-target="<?= $data['totalStock'] ?>">0</div>
                </div>
                <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-trend-up me-1"></i>+3.2%</span>
            </div>
            <!-- Mini sparkline -->
            <div style="height:3px;background: var(--border-color);border-radius:2px;margin-top:12px;overflow:hidden">
                <div id="spark1" style="height:100%;width:0%;background:#22c55e;border-radius:2px;transition:width 1.8s ease"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.15s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-receipt me-1"></i>Pending Order Value</div>
                    <div class="stat-value" id="kpi-pov" data-target="<?= (float)$data['pendingPOValue'] ?>">RM 0.00</div>
                </div>
                <span style="background:#fef9c3;color:#b45309;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-clock me-1"></i>Pending</span>
            </div>
            <div style="height:3px;background: var(--border-color);border-radius:2px;margin-top:12px;overflow:hidden">
                <div id="spark2" style="height:100%;width:0%;background:#f59e0b;border-radius:2px;transition:width 2s ease"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.25s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-warning-octagon me-1"></i>Critical Alerts</div>
                    <div class="stat-value text-danger" id="kpi-alerts" data-target="<?= $data['criticalAlerts'] ?>">0</div>
                </div>
                <span style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-warning me-1"></i>Critical</span>
            </div>
            <div style="height:3px;background: var(--border-color);border-radius:2px;margin-top:12px;overflow:hidden">
                <div id="spark3" style="height:100%;width:0%;background:#ef4444;border-radius:2px;transition:width 1.5s ease"></div>
            </div>
        </div>
    </div>
</div>

<!-- ======= Charts + Stock Levels ======= -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card h-100 dash-card" style="animation-delay:0.35s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-chart-bar me-1"></i>Weekly Consumption (Units)</div>
                <div class="d-flex align-items-center gap-2">
                    <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-calendar me-1"></i>Today</span>
                    <button class="refresh-btn" onclick="refreshChart(this)" title="Refresh chart">
                        <i class="ph ph-arrows-clockwise" id="refreshIcon"></i>
                    </button>
                </div>
            </div>
            <canvas id="weeklyChart" height="120"></canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="stat-card h-100 dash-card" style="animation-delay:0.45s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-stack me-1"></i>Stock Levels</div>
                <span style="font-size:11px;color:var(--text-faint)">by category</span>
            </div>
            <?php
            $catColors = [
                'Toiletries' => '#3b82f6',
                'Linens'     => '#ef4444',
                'F&B'        => '#8b5cf6',
                'Minibar'    => '#f59e0b',
                'Cleaning'   => '#22c55e',
            ];
            foreach ($data['categoryStock'] as $i => $cat):
                $pct   = min((int)$cat['pct'], 100);
                $color = $catColors[$cat['category']] ?? 'var(--text-muted)';
                $warn  = $pct < 30 ? 'text-danger fw-bold' : ($pct < 60 ? 'text-warning fw-bold' : '');
            ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span><i class="ph ph-tag me-1"></i><?= clean($cat['category']) ?></span>
                    <span class="<?= $warn ?>" id="pct-<?= $i ?>">0%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:4px;background: var(--border-subtle)">
                    <div class="progress-bar prog-bar-anim"
                         id="bar-<?= $i ?>"
                         data-target="<?= $pct ?>"
                         data-color="<?= $color ?>"
                         style="background:<?= $color ?>;border-radius:4px;width:0%">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ======= Recent Activity + Active Alerts ======= -->
<div class="row g-3">
    <!-- Recent Activity -->
    <div class="col-md-6 d-flex">
        <div class="stat-card dash-card w-100" style="animation-delay:0.55s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-activity me-1"></i>Recent Activity</div>
                <a href="<?= APP_URL ?>/reports" class="small text-primary"><i class="ph ph-arrow-right me-1"></i>View All</a>
            </div>
            <?php
            $roleColors = [
                'Inventory Manager'   => ['bg'=>'#0096FF','icon'=>'ph-user-gear'],
                'Housekeeping Manager'=> ['bg'=>'#f59e0b','icon'=>'ph-house'],
                'Procurement Officer' => ['bg'=>'#8b5cf6','icon'=>'ph-shopping-cart'],
                'IT Administrator'    => ['bg'=>'#ef4444','icon'=>'ph-cpu'],
                'Hotel GM'            => ['bg'=>'#22c55e','icon'=>'ph-buildings'],
                'Supervisor'          => ['bg'=>'#f97316','icon'=>'ph-eye'],
            ];
            $actionIcons = [
                'LOGIN'          => 'ph-sign-in',
                'LOGOUT'         => 'ph-sign-out',
                'ADD_ITEM'       => 'ph-plus-circle',
                'UPDATE_ITEM'    => 'ph-pencil',
                'DELETE_ITEM'    => 'ph-trash',
                'CREATE_PO'      => 'ph-receipt',
                'UPDATE_PO'      => 'ph-repeat',
                'GENERATE_REPORT'=> 'ph-chart-bar',
                'UPDATE_SETTINGS'=> 'ph-gear',
                'ADD_USER'       => 'ph-user-plus',
            ];
            foreach ($data['recentActivity'] as $i => $log):
                $role    = $log['role_name'] ?? 'Inventory Manager';
                $roleInfo= $roleColors[$role] ?? ['bg'=>'#0096FF','icon'=>'ph-user'];
                $action  = $log['action'] ?? 'LOGIN';
                $actIcon = $actionIcons[$action] ?? 'ph-activity';
            ?>
            <div class="d-flex align-items-start gap-2 mb-3 activity-item" style="animation-delay:<?= 0.6 + $i * 0.1 ?>s">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;background:<?= $roleInfo['bg'] ?>22;border:2px solid <?= $roleInfo['bg'] ?>55"
                     title="<?= clean($role) ?>">
                    <i class="ph-fill <?= $actIcon ?>" style="color:<?= $roleInfo['bg'] ?>;font-size:14px"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600"><?= clean($log['description'] ?? $log['action']) ?></div>
                    <div style="font-size:10px;display:flex;gap:6px;margin-top:2px">
                        <span style="color:<?= $roleInfo['bg'] ?>;font-weight:600"><?= clean($log['full_name'] ?? '') ?></span>
                        <span class="text-muted"><?= date('d M Y, H:i', strtotime($log['timestamp'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($data['recentActivity'])): ?>
                <div class="text-muted small text-center py-3"><i class="ph-fill ph-info me-1"></i>Tiada aktiviti terkini.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="col-md-6 d-flex">
        <div class="stat-card dash-card w-100" style="animation-delay:0.65s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-bell me-1"></i>Active Alerts</div>
                <a href="<?= APP_URL ?>/alerts" class="small text-primary"><i class="ph ph-arrow-right me-1"></i>View All</a>
            </div>
            <?php
            $alertIcons = [
                'Critical' => ['icon'=>'ph-warning-octagon', 'color'=>'#ef4444', 'bg'=>'#fef2f2'],
                'Warning'  => ['icon'=>'ph-warning',          'color'=>'#f59e0b', 'bg'=>'#fffbeb'],
                'Info'     => ['icon'=>'ph-info',             'color'=>'#3b82f6', 'bg'=>'#eff6ff'],
            ];
            foreach ($data['activeAlerts'] as $i => $alert):
                $ai = $alertIcons[$alert['alert_type']] ?? $alertIcons['Info'];
            ?>
            <div class="d-flex align-items-start gap-2 mb-3 activity-item" style="animation-delay:<?= 0.7 + $i * 0.1 ?>s">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;background:<?= $ai['bg'] ?>;border:2px solid <?= $ai['color'] ?>33">
                    <i class="ph-fill <?= $ai['icon'] ?>" style="color:<?= $ai['color'] ?>;font-size:15px"></i>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600"><?= clean($alert['title']) ?></div>
                    <div class="text-muted" style="font-size:11px"><?= date('d M, H:i', strtotime($alert['triggered_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($data['activeAlerts'])): ?>
                <div class="text-muted small text-center py-3"><i class="ph-fill ph-check-circle me-1 text-success"></i>Semua sistem normal.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$weeklyLabels = json_encode(array_column($data['weeklyConsumption'], 'day'));
$weeklyValues = json_encode(array_column($data['weeklyConsumption'], 'units'));
// Mon=0, Tue=1, Wed=2, Thu=3, Fri=4, Sat=5, Sun=6
$todayIndex   = (int)date('N') - 1;
$catCount     = count($data['categoryStock']);

$extraJs = "
// ======= COUNTER ANIMATION (GSAP) =======
function animateCounter(el, target, prefix, suffix, duration) {
    const obj = { val: 0 };
    gsap.to(obj, {
        val: target,
        duration: duration / 1000,
        ease: 'power3.out',
        onUpdate: function() {
            const current = Math.floor(obj.val);
            if (prefix === 'RM ') {
                el.textContent = 'RM ' + current.toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2});
            } else {
                el.textContent = prefix + current.toLocaleString() + suffix;
            }
        },
        onComplete: function() {
            gsap.fromTo(el, { scale: 1.08 }, { scale: 1, duration: 0.25, ease: 'back.out(2)' });
        }
    });
}

// ======= PROGRESS BAR ANIMATION (GSAP) =======
function animateBars() {
    for (let i = 0; i < $catCount; i++) {
        const bar = document.getElementById('bar-' + i);
        const pctEl = document.getElementById('pct-' + i);
        if (!bar) continue;
        const target = parseInt(bar.dataset.target);
        const counterObj = { val: 0 };

        gsap.to(bar, {
            width: target + '%',
            duration: 1.1,
            ease: 'power2.out',
            delay: 0.4 + i * 0.18,
        });
        gsap.to(counterObj, {
            val: target,
            duration: 1.1,
            delay: 0.4 + i * 0.18,
            ease: 'power2.out',
            onUpdate: function() { pctEl.textContent = Math.round(counterObj.val) + '%'; }
        });
    }
}

// ======= SPARKLINES (GSAP) =======
function animateSparklines() {
    gsap.to('#spark1', { width: '72%', duration: 0.9, delay: 0.4, ease: 'power2.out' });
    gsap.to('#spark2', { width: '58%', duration: 0.9, delay: 0.5, ease: 'power2.out' });
    gsap.to('#spark3', { width: '35%', duration: 0.9, delay: 0.6, ease: 'power2.out' });
}

// ======= CHART =======
const todayIndex = $todayIndex;
const barColors  = $weeklyLabels.map((d, i) => i === todayIndex ? '#0096FF' : '#f59e0b');

// Theme-aware chart colors (canvas can't resolve CSS var() directly)
function dashChartTheme() {
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    return {
        gridColor: dark ? 'rgba(148,163,184,0.15)' : '#f0f0f0',
        tickColor: dark ? '#8e94ab' : '#888',
    };
}

let chartInstance;
function buildChart(values) {
    const ctxEl = document.getElementById('weeklyChart').getContext('2d');
    if (chartInstance) chartInstance.destroy();
    chartInstance = new Chart(ctxEl, {
        type: 'bar',
        data: {
            labels: $weeklyLabels,
            datasets: [{
                label: 'Units',
                data: values,
                backgroundColor: barColors,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1200,
                easing: 'easeOutBounce',
                delay: (ctx) => ctx.dataIndex * 80,
            },
            plugins: {
                legend: { display:false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.parsed.y + ' units' + (ctx.dataIndex === todayIndex ? ' (Today)' : '')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: dashChartTheme().gridColor },
                    animation: { duration: 1000 }
                },
                x: {
                    grid: { display:false },
                    ticks: {
                        color: (ctx) => ctx.index === todayIndex ? '#0096FF' : dashChartTheme().tickColor,
                        font:  (ctx) => ({ weight: ctx.index === todayIndex ? 'bold' : 'normal' })
                    }
                }
            }
        }
    });
}
buildChart($weeklyValues);

// Refresh chart button
function refreshChart(btn) {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('spin');
    setTimeout(() => {
        // Re-render with the SAME real data (replays the entry animation)
        buildChart($weeklyValues);
        icon.classList.remove('spin');
    }, 600);
}

// ======= RUN ALL ON LOAD =======
window.addEventListener('load', () => {
    // KPI counters — stagger
    setTimeout(() => animateCounter(document.getElementById('kpi-total'),  parseInt(document.getElementById('kpi-total').dataset.target),  '', '', 1400), 200);
    setTimeout(() => animateCounter(document.getElementById('kpi-pov'),    parseFloat(document.getElementById('kpi-pov').dataset.target),   'RM ', '', 1600), 350);
    setTimeout(() => animateCounter(document.getElementById('kpi-alerts'), parseInt(document.getElementById('kpi-alerts').dataset.target), '', '', 800),  500);

    // Progress bars
    animateBars();

    // Sparklines
    animateSparklines();
});
";

require_once __DIR__ . '/../layouts/app.php';
?>