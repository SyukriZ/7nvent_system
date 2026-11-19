<?php
$pageTitle = 'Dashboard';
ob_start();
?>

<style>
/* Refresh spin */
@keyframes spin { 100%{ transform:rotate(360deg); } }
.spin { animation: spin 0.6s linear; }

/* Refresh button */
.refresh-btn {
    background: none; border: 1px solid var(--border-color);
    border-radius: 8px; padding: 4px 10px;
    font-size: 12px; color: var(--text-faint); cursor: pointer;
    transition: all 0.2s; display:flex; align-items:center; gap:5px;
}
.refresh-btn:hover { background: var(--bg-subtle); color:#0096FF; border-color:#0096FF; }
</style>

<section class="operations-intro operations-intro--dashboard" aria-labelledby="operations-heading">
    <div>
        <h2 id="operations-heading">Hotel operations overview</h2>
        <p>Review stock, pending orders, and alerts for your next shift.</p>
    </div>
    <a class="btn fw-semibold" href="<?= APP_URL ?>/inventory">View inventory <i class="ph ph-arrow-right ms-1" aria-hidden="true"></i></a>
</section>

<!-- KPI statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.05s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-package me-1"></i>Total Items in Stock</div>
                    <div class="stat-value" id="kpi-total"><?= number_format($data['totalStock']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.15s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-receipt me-1"></i>Pending Order Value</div>
                    <div class="stat-value" id="kpi-pov"><?= 'RM ' . number_format($data['pendingPOValue'], 2) ?></div>
                </div>
                <span style="background:#fef9c3;color:#b45309;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-clock me-1"></i>Pending</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card dash-card" style="animation-delay:0.25s">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label"><i class="ph-fill ph-warning-octagon me-1"></i>Critical Alerts</div>
                    <div class="stat-value text-danger" id="kpi-alerts"><?= number_format($data['criticalAlerts']) ?></div>
                </div>
                <span style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-warning me-1"></i>Critical</span>
            </div>
        </div>
    </div>
</div>

<!-- ======= Charts + Stock Levels ======= -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card h-100 dash-card" style="animation-delay:0.35s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-chart-bar me-1"></i>Latest recorded usage by weekday</div>
                <div class="d-flex align-items-center gap-2">
                    <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px"><i class="ph-fill ph-calendar me-1"></i>Units</span>
                    <button class="refresh-btn" onclick="refreshChart(this)" title="Redraw chart" aria-label="Redraw consumption chart">
                        <i class="ph ph-arrows-clockwise" id="refreshIcon"></i>
                    </button>
                </div>
            </div>
            <canvas id="weeklyChart" height="120" role="img" aria-label="Stock consumption by weekday: <?= clean(implode(', ', array_map(static fn ($day) => $day['day'] . ' ' . $day['units'] . ' units', $data['weeklyConsumption']))) ?>"></canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="stat-card h-100 dash-card" style="animation-delay:0.45s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold"><i class="ph-fill ph-stack me-1"></i>Stock Levels</div>
                <span style="font-size:11px;color:var(--text-faint)">by category</span>
            </div>
            <?php
            foreach ($data['categoryStock'] as $cat):
                $pct   = min((int)$cat['pct'], 100);
                $color = $pct < 30 ? '#b94443' : ($pct < 60 ? '#b28843' : '#48826b');
                $warn  = $pct < 30 ? 'text-danger fw-bold' : ($pct < 60 ? 'text-warning fw-bold' : '');
            ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span><i class="ph ph-tag me-1"></i><?= clean($cat['category']) ?></span>
                    <span class="<?= $warn ?>"><?= $pct ?>%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:4px;background: var(--border-subtle)">
                    <div class="progress-bar"
                         style="background:<?= $color ?>;border-radius:4px;width:<?= $pct ?>%">
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
$extraJs = "
// ======= CHART =======
const todayIndex = $todayIndex;
const barColors  = $weeklyLabels.map((d, i) => i === todayIndex ? '#287355' : '#b69862');

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
    if (typeof Chart === 'undefined') return;
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
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 350 },
            plugins: {
                legend: { display:false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.parsed.y + ' units'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: dashChartTheme().gridColor },
                    ticks: { color: dashChartTheme().tickColor }
                },
                x: {
                    grid: { display:false },
                    ticks: {
                        color: (ctx) => ctx.index === todayIndex ? '#367c65' : dashChartTheme().tickColor,
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

";

require_once __DIR__ . '/../layouts/app.php';
?>