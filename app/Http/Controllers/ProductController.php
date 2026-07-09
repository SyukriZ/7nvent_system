<?php
// =============================================================
// 7NVENT - Public Product View Controller
//
// Deliberately NOT behind Auth::required() — this is the page a printed
// QR label actually opens when scanned with a plain phone camera (no
// 7nvent login involved). Before this controller existed, the QR payload
// pointed at /qr-scanner?code=..., which redirects anonymous visitors to
// the login page — so "scan to see product info" never actually worked
// for anyone without a 7nvent account. This route shows a read-only
// product card only: name, category, photo, price, and a stock badge.
// It intentionally does NOT expose exact quantity/par level (that stays
// inside the authenticated QR Scanner for staff), and has no way to
// mutate stock — pure display.
// =============================================================
require_once __DIR__ . '/../../Support/StockStatus.php';

class ProductController {

    public function show(): void {
        $code = clean($_GET['code'] ?? '');

        $item = null;
        if ($code !== '') {
            // Same lookup rule QRController uses: prefer the real stored
            // item_code, fall back to the computed 7NV-XXXX for older items.
            $item = db()->fetchOne(
                "SELECT i.*,
                        COALESCE(NULLIF(i.item_code, ''), CONCAT('7NV-', LPAD(i.item_id, 4, '0'))) AS resolved_code,
                        l.location_name
                 FROM inventory_items i
                 JOIN locations l ON i.location_id = l.location_id
                 WHERE i.item_code = ? OR CONCAT('7NV-', LPAD(i.item_id, 4, '0')) = ?
                 LIMIT 1",
                [$code, $code]
            );
        }

        $catIcons = [
            'Toiletries' => 'ph-fill ph-drop',
            'F&B'        => 'ph-fill ph-bowl-food',
            'Linens'     => 'ph-fill ph-towel',
            'Cleaning'   => 'ph-fill ph-broom',
            'Minibar'    => 'ph-fill ph-wine',
        ];
        $catColors = [
            'Toiletries' => '#3b82f6', 'F&B' => '#22c55e', 'Linens' => '#f59e0b',
            'Cleaning'   => '#8b5cf6', 'Minibar' => '#ef4444',
        ];

        $statusText = null;
        $statusColor = null;
        if ($item) {
            $statusText  = StockStatus::determine((int)$item['quantity'], (int)$item['par_level']);
            $statusColor = $statusText === 'In-Stock' ? '#22c55e' : ($statusText === 'Low Stock' ? '#f59e0b' : '#ef4444');
        }

        $imageUrl = ($item && !empty($item['image_path'])) ? APP_URL . '/' . $item['image_path'] : null;
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $item ? clean($item['item_name']) : 'Product Not Found' ?> — 7NVENT</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
<style>
    * { box-sizing:border-box; }
    body {
        margin:0; min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
        background:linear-gradient(160deg,#0a0e1a,#141a2e 60%,#0a0e1a);
        display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .card {
        width:100%; max-width:420px; background:rgba(27,32,48,0.72);
        border:1px solid rgba(255,255,255,0.10); border-radius:20px;
        padding:28px 24px; color:#e8eaf0;
        box-shadow:0 20px 60px rgba(0,0,0,0.45);
    }
    .brand { display:flex; align-items:center; gap:8px; margin-bottom:20px; opacity:0.75; font-size:13px; font-weight:700; letter-spacing:1px; }
    .brand .dot { width:6px; height:6px; border-radius:50%; background:#0096FF; }
    .photo-wrap {
        width:100%; aspect-ratio:1/1; border-radius:16px; overflow:hidden; margin-bottom:18px;
        background:#f8fafc; display:flex; align-items:center; justify-content:center;
    }
    .photo-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
    .placeholder-icon { font-size:64px; color:#c9ccd6; }
    .name { font-size:22px; font-weight:800; margin-bottom:6px; line-height:1.25; }
    .code { font-size:12px; font-family:monospace; color:#8e94ab; margin-bottom:14px; }
    .badges { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
    .badge {
        font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px;
        display:inline-flex; align-items:center; gap:5px;
    }
    .info-row {
        display:flex; justify-content:space-between; padding:12px 0;
        border-top:1px solid rgba(255,255,255,0.08); font-size:14px;
    }
    .info-row .label { color:#8e94ab; }
    .info-row .value { font-weight:700; }
    .price { font-size:26px; font-weight:800; color:#22c55e; margin:14px 0 4px; }
    .notfound { text-align:center; padding:20px 0; }
    .notfound i { font-size:56px; color:#ef4444; margin-bottom:14px; display:block; }
    .footer-note { margin-top:20px; font-size:11px; color:#6b7190; text-align:center; }
</style>
</head>
<body>
<div class="card">
    <div class="brand"><span class="dot"></span>7NVENT · HOTEL INVENTORY</div>

    <?php if ($item): ?>
        <div class="photo-wrap">
            <?php if ($imageUrl): ?>
                <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>" alt="<?= clean($item['item_name']) ?>">
            <?php else: ?>
                <i class="<?= $catIcons[$item['category']] ?? 'ph-fill ph-package' ?> placeholder-icon"></i>
            <?php endif; ?>
        </div>

        <div class="name"><?= clean($item['item_name']) ?></div>
        <div class="code"><?= clean($item['resolved_code']) ?></div>

        <div class="badges">
            <span class="badge" style="background:<?= $catColors[$item['category']] ?? '#64748b' ?>22;color:<?= $catColors[$item['category']] ?? '#64748b' ?>">
                <i class="<?= $catIcons[$item['category']] ?? 'ph-fill ph-tag' ?>"></i><?= clean($item['category']) ?>
            </span>
            <span class="badge" style="background:<?= $statusColor ?>22;color:<?= $statusColor ?>">
                <i class="ph-fill ph-circle"></i><?= $statusText ?>
            </span>
        </div>

        <div class="price">RM <?= number_format((float)$item['unit_price'], 2) ?></div>

        <div class="info-row"><span class="label"><i class="ph ph-map-pin"></i>&nbsp; Location</span><span class="value"><?= clean($item['location_name']) ?></span></div>
        <?php if (!empty($item['expiry_date'])): ?>
        <div class="info-row"><span class="label"><i class="ph ph-calendar"></i>&nbsp; Expiry</span><span class="value"><?= clean($item['expiry_date']) ?></span></div>
        <?php endif; ?>

        <div class="footer-note">Scanned via 7NVENT QR &middot; read-only product info</div>
    <?php else: ?>
        <div class="notfound">
            <i class="ph-fill ph-x-circle"></i>
            <div style="font-size:17px;font-weight:700;margin-bottom:6px">Product not found</div>
            <div style="font-size:13px;color:#8e94ab">This code doesn't match any item in 7NVENT.</div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
    }
}
