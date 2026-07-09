<?php
// Standalone printable report — no app layout
// Variables from controller: $type, $title, $columns, $data, $user

// ── Pre-compute summary stats ─────────────────────────────────────────
$summaryStats = [];
switch ($type) {
    case 'stock-summary':
        $tQty = 0; $tVal = 0.0; $nLow = 0; $nOut = 0;
        foreach ($data as $r) {
            $tQty += (int)($r['quantity'] ?? 0);
            $tVal += (float)($r['total_value'] ?? 0);
            if (($r['status'] ?? '') === 'Low Stock')    $nLow++;
            if (($r['status'] ?? '') === 'Out of Stock') $nOut++;
        }
        $summaryStats = [
            ['Total Records',      count($data),                        ''],
            ['Total Quantity',     number_format($tQty),               ''],
            ['Total Stock Value',  'RM ' . number_format($tVal, 2),    '#0096FF'],
            ['Low / Out of Stock', $nLow . ' / ' . $nOut,              '#f59e0b'],
        ];
        break;

    case 'po-history':
        $tVal2 = 0.0; $nDel = 0; $nPend = 0;
        foreach ($data as $r) {
            $tVal2 += (float)($r['total_value'] ?? 0);
            if (($r['status'] ?? '') === 'Delivered') $nDel++;
            if (in_array($r['status'] ?? '', ['Pending','In Transit'])) $nPend++;
        }
        $summaryStats = [
            ['Total Orders',       count($data),                        ''],
            ['Total PO Value',     'RM ' . number_format($tVal2, 2),   '#0096FF'],
            ['Delivered',          $nDel,                               '#22c55e'],
            ['Pending/In Transit', $nPend,                              '#f59e0b'],
        ];
        break;

    case 'valuation':
        $gVal = 0.0; $gItems = 0; $gQty = 0;
        foreach ($data as $r) {
            $gVal   += (float)($r['total_value'] ?? 0);
            $gItems += (int)($r['item_count'] ?? 0);
            $gQty   += (int)($r['total_qty'] ?? 0);
        }
        $summaryStats = [
            ['Categories',        count($data),                        ''],
            ['Total Items',       number_format($gItems),             ''],
            ['Total Quantity',    number_format($gQty),               ''],
            ['Grand Total Value', 'RM ' . number_format($gVal, 2),   '#8b5cf6'],
        ];
        break;

    case 'waste-expiry':
        $atRisk = 0.0; $nExpired = 0; $n30d = 0;
        foreach ($data as $r) {
            $atRisk += (float)($r['at_risk_value'] ?? 0);
            $days = (int)($r['days_remaining'] ?? 0);
            if ($days < 0)  $nExpired++;
            if ($days <= 30 && $days >= 0) $n30d++;
        }
        $summaryStats = [
            ['Total Records',      count($data),                           ''],
            ['At-Risk Value',      'RM ' . number_format($atRisk, 2),     '#ef4444'],
            ['Already Expired',    $nExpired,                              '#7c3aed'],
            ['Expiring ≤ 30 Days', $n30d,                                  '#f59e0b'],
        ];
        break;

    case 'supplier':
        $totalYTD = 0.0; $sumRating = 0.0;
        foreach ($data as $r) {
            $totalYTD  += (float)($r['ytd_orders_value'] ?? 0);
            $sumRating += (float)($r['rating'] ?? 0);
        }
        $avgRating = count($data) > 0 ? round($sumRating / count($data), 1) : 0;
        $summaryStats = [
            ['Total Suppliers',  count($data),                         ''],
            ['Average Rating',   $avgRating . ' <i class="ph ph-star" style="color:#f59e0b"></i>',                   '#f59e0b'],
            ['YTD Orders Value', 'RM ' . number_format($totalYTD, 2), '#0096FF'],
        ];
        break;

    default: // consumption
        $summaryStats = [
            ['Total Records', count($data),        ''],
            ['Period',        'Last 100 actions',  ''],
        ];
        break;
}

// ── Status badge map ──────────────────────────────────────────────────
$statusBadge = [
    'In-Stock'    => 'badge-success',
    'Delivered'   => 'badge-success',
    'Operational' => 'badge-success',
    'Active'      => 'badge-success',
    'Approved'    => 'badge-success',
    'Low Stock'   => 'badge-warning',
    'In Transit'  => 'badge-warning',
    'Partial Low' => 'badge-warning',
    'Pending'     => 'badge-warning',
    'Auto'        => 'badge-info',
    'Manual'      => 'badge-info',
    'Out of Stock'=> 'badge-danger',
    'Cancelled'   => 'badge-danger',
    'Critical'    => 'badge-danger',
    'Inactive'    => 'badge-secondary',
    'Dismissed'   => 'badge-secondary',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>7NVENT — <?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#1e293b;background:#f8fafc;
     -webkit-print-color-adjust:exact;print-color-adjust:exact}

/* ── Action bar (hidden on print) ── */
.no-print{background:#fff;border-bottom:2px solid #e2e8f0;padding:12px 24px;
          display:flex;align-items:center;justify-content:space-between;
          position:sticky;top:0;z-index:100;gap:12px}
.np-title{font-size:14px;font-weight:700;color:#1e293b}
.np-btns{display:flex;gap:8px;flex-shrink:0}
.nbtn{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;
      border:none;display:inline-flex;align-items:center;gap:6px;
      text-decoration:none;font-family:Arial,sans-serif;transition:opacity .15s}
.nbtn:hover{opacity:.85}
.btn-back{background:#f1f5f9;color:#475569}
.btn-csv{background:#22c55e;color:#fff}
.btn-print{background:#0096FF;color:#fff}

/* ── Page container ── */
.container{max-width:1020px;margin:0 auto;padding:20px 24px}

/* ── Report header ── */
.rpt-header{display:flex;justify-content:space-between;align-items:flex-start;
            padding-bottom:14px;border-bottom:3px solid #0096FF;margin-bottom:14px}
.brand-name{font-size:26px;font-weight:900;color:#0096FF;letter-spacing:-1px;line-height:1}
.brand-name span{color:#1e293b}
.brand-sub{font-size:8px;font-weight:700;color:#94a3b8;letter-spacing:1.5px;
           text-transform:uppercase;margin-top:3px}
.rpt-meta{text-align:right}
.rpt-title{font-size:17px;font-weight:800;color:#1e293b;margin-bottom:5px}
.rpt-info{font-size:10px;color:#64748b;line-height:1.8}

/* ── Summary strip ── */
.summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
         gap:10px;margin-bottom:14px}
.sum-box{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px}
.sum-label{font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;
           letter-spacing:.5px;margin-bottom:5px}
.sum-val{font-size:20px;font-weight:900;color:#1e293b;line-height:1}

/* ── Table ── */
.tbl-wrap{border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;background:#fff}
table{width:100%;border-collapse:collapse}
thead tr{background:#0096FF}
th{padding:9px 10px;text-align:left;font-size:10px;font-weight:700;
   color:#fff;white-space:nowrap;letter-spacing:.2px}
td{padding:7px 10px;font-size:10px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
tr:nth-child(even) td{background:#f8fafc}
tr:last-child td{border-bottom:none}

/* ── Badges ── */
.badge{display:inline-block;padding:2px 8px;border-radius:20px;
       font-size:9px;font-weight:700;white-space:nowrap}
.badge-success {background:#dcfce7;color:#15803d}
.badge-warning {background:#fef3c7;color:#b45309}
.badge-danger  {background:#fee2e2;color:#b91c1c}
.badge-info    {background:#dbeafe;color:#1d4ed8}
.badge-secondary{background:#f1f5f9;color:#475569}

/* ── Empty state ── */
.empty{text-align:center;padding:50px;color:#94a3b8;background:#fff;border-radius:10px}

/* ── Footer ── */
.rpt-footer{margin-top:14px;padding-top:10px;border-top:1px solid #e2e8f0;
            display:flex;justify-content:space-between;color:#94a3b8;font-size:9px}

/* ── Print overrides ── */
@media print{
    .no-print{display:none!important}
    body{background:#fff}
    .container{max-width:100%;padding:0}
    .tbl-wrap{border:none;border-radius:0}
    th,td{font-size:9px!important;padding:5px 7px!important}
    .sum-val{font-size:15px!important}
    tr{page-break-inside:avoid}
}
@page{size:A4 landscape;margin:1.5cm}
</style>
</head>
<body>

<!-- Action bar -->
<div class="no-print">
    <div class="np-title"><i class="ph ph-file-text" style="margin-right:6px;font-size:16px"></i><?= htmlspecialchars($title) ?></div>
    <div class="np-btns">
        <a href="javascript:history.back()" class="nbtn btn-back"><i class="ph ph-arrow-left" style="margin-right:4px"></i>Back</a>
        <a href="<?= APP_URL ?>/reports/generate?type=<?= htmlspecialchars($type) ?>&format=csv"
           class="nbtn btn-csv"><i class="ph ph-download-simple" style="margin-right:4px"></i>Download CSV</a>
        <button onclick="window.print()" class="nbtn btn-print"><i class="ph ph-printer" style="margin-right:4px"></i>Print / Save as PDF</button>
    </div>
</div>

<div class="container">

    <!-- Header -->
    <div class="rpt-header">
        <div>
            <div class="brand-name">7<span>NVENT</span></div>
            <div class="brand-sub">Hotel Inventory Management System</div>
        </div>
        <div class="rpt-meta">
            <div class="rpt-title"><?= htmlspecialchars($title) ?></div>
            <div class="rpt-info">
                Generated: <?= date('d M Y, h:i A') ?><br>
                By: <?= htmlspecialchars($user['full_name'] ?? 'System') ?>
                <?php if (!empty($user['role_name'])): ?>(<?= htmlspecialchars($user['role_name']) ?>)<?php endif; ?><br>
                Total records: <?= number_format(count($data)) ?>
            </div>
        </div>
    </div>

    <!-- Summary strip -->
    <?php if (!empty($summaryStats)): ?>
    <div class="summary">
        <?php foreach ($summaryStats as $stat): ?>
        <div class="sum-box">
            <div class="sum-label"><?= htmlspecialchars($stat[0]) ?></div>
            <div class="sum-val" style="color:<?= ($stat[2] ?: '#1e293b') ?>">
                <?= htmlspecialchars((string)$stat[1]) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <?php if (empty($data)): ?>
    <div class="empty">
        <div style="font-size:36px;margin-bottom:10px"><i class="ph ph-tray" style="font-size:36px;color:#94a3b8"></i></div>
        <div style="font-weight:700;font-size:13px">No data found for this report</div>
    </div>
    <?php else: ?>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <?php foreach ($columns as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $rowNum => $row):
                    $rowVals = array_values($row);
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:9px"><?= $rowNum + 1 ?></td>
                    <?php foreach ($columns as $ci => $colName):
                        $val = isset($rowVals[$ci]) ? $rowVals[$ci] : null;
                        $sVal = ($val === null || $val === '') ? '' : (string)$val;
                        $lc   = strtolower($colName);
                    ?>
                    <td>
                    <?php
                    // Empty / null
                    if ($sVal === '' || $sVal === null) {
                        echo '<span style="color:#94a3b8">—</span>';
                    }
                    // Status badges
                    elseif (isset($statusBadge[$sVal])) {
                        echo '<span class="badge ' . $statusBadge[$sVal] . '">'
                           . htmlspecialchars($sVal) . '</span>';
                    }
                    // Days remaining (waste-expiry)
                    elseif ($type === 'waste-expiry' && $lc === 'days remaining') {
                        $days = (int)$sVal;
                        if ($days < 0) {
                            echo '<span class="badge badge-danger">Expired ' . abs($days) . 'd ago</span>';
                        } elseif ($days <= 7) {
                            echo '<span class="badge badge-danger">' . $days . ' days</span>';
                        } elseif ($days <= 30) {
                            echo '<span class="badge badge-warning">' . $days . ' days</span>';
                        } else {
                            echo '<span class="badge badge-success">' . $days . ' days</span>';
                        }
                    }
                    // Rating
                    elseif ($lc === 'rating') {
                        echo htmlspecialchars($sVal) . ' <i class="ph ph-star" style="color:#f59e0b"></i>';
                    }
                    // Monetary columns
                    elseif (strpos($lc, '(rm)') !== false || strpos($lc, 'value') !== false) {
                        echo 'RM ' . number_format((float)$sVal, 2);
                    }
                    // N/A placeholder
                    elseif ($sVal === 'N/A') {
                        echo '<span style="color:#94a3b8">N/A</span>';
                    }
                    // Default
                    else {
                        echo htmlspecialchars($sVal);
                    }
                    ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="rpt-footer">
        <div>7NVENT — Hotel Inventory Management System | KPM Beranang | CSC2854 | BCS2402-042</div>
        <div>Generated: <?= date('d/m/Y H:i:s') ?> | <?= htmlspecialchars($user['full_name'] ?? '—') ?></div>
    </div>

</div>
</body>
</html>