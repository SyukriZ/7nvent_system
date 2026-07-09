<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> — 7NVENT</title>

    <!-- Apply saved theme immediately (before paint) to avoid a flash of the wrong theme -->
    <script>
        (function() {
            var t = localStorage.getItem('7nvent_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    <style>
        :root {
            --sidebar-width: 230px;
            --sidebar-bg: #1a1a2e;
            --accent-blue: #0096FF;
            --accent-yellow: #FFD700;
            --sidebar-text: #c5c5d3;
            --header-h: 56px;

            /* ---- Theme palette (Light = default) ---- */
            --bg-page: #f4f6fb;
            --bg-card: #ffffff;
            --bg-subtle: #f8fafc;
            --bg-header: #ffffff;
            --text-primary: #1a1a2e;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --text-faint: #94a3b8;
            --border-color: #e2e8f0;
            --border-subtle: #f1f5f9;
            --shadow-color: rgba(0,0,0,.06);

            /* ---- Liquid Glass tokens (Light) ---- */
            --glass-bg: rgba(255,255,255,0.55);
            --glass-bg-strong: rgba(255,255,255,0.70);
            --glass-border: rgba(255,255,255,0.55);
            --glass-highlight: rgba(255,255,255,0.85);
            --glass-shadow: rgba(31,41,55,0.10);
            --glass-sidebar-bg: rgba(26,26,46,0.78);
            --glass-blob-1: rgba(0,150,255,0.30);
            --glass-blob-2: rgba(255,215,0,0.22);
            --glass-blob-3: rgba(168,85,247,0.22);
            --glass-blob-opacity: 1;
        }

        /* ---- Theme palette (Dark) — toggled via Bootstrap 5.3's data-bs-theme ---- */
        [data-bs-theme="dark"] {
            --bg-page: #0f1320;
            --bg-card: #1b2030;
            --bg-subtle: #242a3d;
            --bg-header: #1b2030;
            --text-primary: #e8eaf0;
            --text-secondary: #b8bdd0;
            --text-muted: #8e94ab;
            --text-faint: #6b7190;
            --border-color: #313850;
            --border-subtle: #262c40;
            --shadow-color: rgba(0,0,0,.35);

            /* ---- Liquid Glass tokens (Dark) ---- */
            --glass-bg: rgba(27,32,48,0.55);
            --glass-bg-strong: rgba(27,32,48,0.72);
            --glass-border: rgba(255,255,255,0.10);
            --glass-highlight: rgba(255,255,255,0.18);
            --glass-shadow: rgba(0,0,0,0.45);
            --glass-sidebar-bg: rgba(15,19,32,0.72);
            --glass-blob-1: rgba(0,150,255,0.35);
            --glass-blob-2: rgba(255,215,0,0.16);
            --glass-blob-3: rgba(168,85,247,0.30);
            --glass-blob-opacity: 1.3;
        }

        html {
            scrollbar-gutter: stable;
            overflow-x: hidden;
        }

        /* The page itself must never scroll horizontally — only individual
           widgets (e.g. a wide table inside .data-table) should. Without this,
           any fixed-position panel (chat/a11y/language) that briefly renders
           off past the right edge during its open animation can widen the
           document just long enough to leave the whole page scrolled
           sideways, hiding left-side content until the user manually scrolls
           back. This guard makes that class of bug structurally impossible. */
        body {
            background: var(--bg-page);
            color: var(--text-primary);
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            overflow-x: hidden;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* ---- Liquid Glass ambient layer ----
             Sits behind everything (z-index:-1) so the sidebar/header/cards
             have colour to blur. Tables, forms and alerts are never placed
             above this on purpose — they keep solid backgrounds for legibility. */
        #glassAmbient {
            position: fixed; inset: 0; z-index: -1;
            overflow: hidden; pointer-events: none;
        }
        #glassAmbient .g-blob {
            position: absolute; border-radius: 50%;
            filter: blur(90px);
            opacity: var(--glass-blob-opacity);
            will-change: transform;
        }
        #glassAmbient .g-blob-1 {
            width: 46vw; height: 46vw; top: -14%; left: -8%;
            background: var(--glass-blob-1);
            animation: glassDrift1 22s ease-in-out infinite;
        }
        #glassAmbient .g-blob-2 {
            width: 38vw; height: 38vw; top: 55%; left: 60%;
            background: var(--glass-blob-2);
            animation: glassDrift2 26s ease-in-out infinite;
        }
        #glassAmbient .g-blob-3 {
            width: 32vw; height: 32vw; top: 10%; left: 70%;
            background: var(--glass-blob-3);
            animation: glassDrift3 19s ease-in-out infinite;
        }
        @keyframes glassDrift1 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%     { transform: translate(4%,6%) scale(1.12); }
        }
        @keyframes glassDrift2 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%     { transform: translate(-6%,-4%) scale(1.08); }
        }
        @keyframes glassDrift3 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%     { transform: translate(-4%,5%) scale(0.94); }
        }

        /* ---- Shared liquid-glass surface (sidebar / header / cards) ---- */
        .liquid-glass {
            position: relative;
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
            isolation: isolate;
        }
        .liquid-glass::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(115deg,
                transparent 30%,
                var(--glass-highlight) 47%,
                transparent 64%);
            opacity: 0.5;
            transform: translateX(-130%);
            animation: glassSheen 8s ease-in-out infinite;
            pointer-events: none;
            mix-blend-mode: overlay;
            z-index: -1;
        }
        @keyframes glassSheen {
            0%, 35%  { transform: translateX(-130%); }
            65%,100% { transform: translateX(130%); }
        }
        /* Pointer-tracked specular glint — position set via JS custom props */
        .liquid-glass::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at var(--mx,50%) var(--my,50%),
                var(--glass-highlight), transparent 42%);
            opacity: 0;
            transition: opacity .35s ease;
            pointer-events: none;
            z-index: -1;
        }
        .liquid-glass:hover::after { opacity: 0.6; }

        @media (prefers-reduced-motion: reduce) {
            .liquid-glass::before,
            #glassAmbient .g-blob { animation: none; }
        }

        /* ---- Sidebar ----
             Glass is gated to dark mode only. Light mode keeps the original
             solid navy panel. No scrolling in either theme — content must
             fit, it never scrolls. */
        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid transparent;
            box-shadow: none;
            z-index: 1000;
            overflow: hidden;
            display: flex; flex-direction: column;
            transition: background-color 0.25s ease, backdrop-filter 0.25s ease;
        }
        /* Disable the shared .liquid-glass blur for the sidebar by default
           (light mode) — specificity (#id.class) beats the plain .liquid-glass rule */
        #sidebar.liquid-glass {
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
        [data-bs-theme="dark"] #sidebar {
            background: var(--glass-sidebar-bg);
            border-right: 1px solid var(--glass-border);
            box-shadow: 8px 0 32px var(--glass-shadow);
        }
        [data-bs-theme="dark"] #sidebar.liquid-glass {
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
        }
        /* Sheen + pointer glint only exist in dark mode for the sidebar */
        #sidebar.liquid-glass::before,
        #sidebar.liquid-glass::after { content: none; }
        [data-bs-theme="dark"] #sidebar.liquid-glass::before,
        [data-bs-theme="dark"] #sidebar.liquid-glass::after {
            content: '';
            position: fixed; width: var(--sidebar-width); height: 100vh; top: 0; left: 0;
        }
        /* ---- Logo & Brand ---- */
        .sidebar-brand {
            padding: 16px 18px;
            border-bottom: 1px solid #2d2d44;
            position: relative;
            overflow: hidden;
        }
        .sidebar-brand::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 200px; height: 70px;
            background: radial-gradient(ellipse at center,
                rgba(0,150,255,0.22) 0%,
                rgba(100,80,255,0.10) 45%,
                transparent 72%);
            animation: brandAmbient 3.5s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes brandAmbient {
            0%,100% { opacity: 0.7; transform: translate(-50%,-50%) scale(1); }
            50%      { opacity: 1;   transform: translate(-50%,-50%) scale(1.15); }
        }
        .brand-logo {
            font-size: 22px; font-weight: 900; color: #fff; letter-spacing: -1px;
            position: relative; display: inline-block;
            animation: logoGlow 3s ease-in-out infinite;
        }
        .brand-logo span {
            color: var(--accent-blue);
            animation: spanGlow 3s ease-in-out infinite;
            display: inline-block;
        }
        .brand-sub { font-size: 9px; color: #888; letter-spacing: 1px; text-transform: uppercase; }
        @keyframes logoGlow {
            0%,100% {
                text-shadow:
                    0 0 6px  rgba(0,150,255,.50),
                    0 0 18px rgba(0,150,255,.28),
                    0 0 40px rgba(0,150,255,.12),
                    0 0 70px rgba(0,100,255,.06);
                filter: brightness(1);
            }
            50% {
                text-shadow:
                    0 0 10px rgba(0,170,255,1.0),
                    0 0 28px rgba(0,150,255,.75),
                    0 0 55px rgba(0,120,255,.40),
                    0 0 95px rgba(0,100,255,.18);
                filter: brightness(1.15);
            }
        }
        @keyframes spanGlow {
            0%,100% {
                text-shadow:
                    0 0 8px  rgba(0,150,255,.9),
                    0 0 22px rgba(0,180,255,.5),
                    0 0 45px rgba(0,150,255,.25);
                color: var(--accent-blue);
            }
            50% {
                text-shadow:
                    0 0 12px rgba(0,200,255,1.0),
                    0 0 30px rgba(0,170,255,.80),
                    0 0 65px rgba(0,130,255,.40),
                    0 0 100px rgba(0,100,255,.18);
                color: #55ccff;
            }
        }

        .sidebar-section {
            padding: 10px 18px 4px;
            font-size: 10px; color: #555;
            letter-spacing: 1px; text-transform: uppercase; font-weight: 700;
        }

        /* Base link style */
        #sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 18px;
            color: var(--sidebar-text);
            text-decoration: none; font-size: 13px;
            border-radius: 8px;
            margin: 1px 8px;
            transition: all 0.18s ease;
            position: relative;
        }
        #sidebar a i { font-size: 15px; width: 18px; flex-shrink: 0; }

        /* Hover state */
        #sidebar a:hover {
            background: rgba(255,255,255,0.07);
            color: #fff;
        }

        /* Active state — full blue pill */
        #sidebar a.active {
            background: var(--accent-blue);
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(0,150,255,0.4);
        }
        #sidebar a.active i { color: #fff; }

        /* Active left dot indicator */
        #sidebar a.active::before {
            content: '';
            position: absolute;
            left: -8px; top: 50%;
            transform: translateY(-50%);
            width: 4px; height: 20px;
            background: var(--accent-blue);
            border-radius: 0 4px 4px 0;
        }

        .sidebar-user {
            margin-top: auto;
            padding: 12px 14px;
            border-top: 1px solid #2d2d44;
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px;
            background: var(--accent-blue);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #fff; font-size: 13px;
            flex-shrink: 0;
        }
        .user-name { color: #fff; font-size: 12px; font-weight: 600; }
        .user-role { color: #888; font-size: 10px; }

        /* ---- Main Content ---- */
        #main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .top-header {
            height: var(--header-h);
            background: var(--glass-bg);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px;
            position: sticky; top: 0; z-index: 100;
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }
        .page-title { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .content-area { padding: 24px; }

        /* ---- Cards — liquid glass, site-wide.
             Every page extends this layout and reuses .stat-card as its card/
             panel wrapper (dashboard KPIs, list pages, alert/report panels,
             and create/edit form panels alike), so styling it once here rolls
             out to all of them. Form inputs inside are unaffected — they're
             Bootstrap .form-control elements with their own hard-coded opaque
             backgrounds, so legibility inside glass panels is preserved. ---- */
        .stat-card {
            position: relative; overflow: hidden; isolation: isolate;
            background: var(--glass-bg);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
            transition: background-color .25s ease, box-shadow .25s ease,
                        transform .35s cubic-bezier(0.34,1.56,0.64,1);
        }
        .stat-card:hover  { transform: translateY(-3px); }
        .stat-card:active { transform: scale(0.98); }
        .stat-card::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%);
            opacity: .5; transform: translateX(-130%);
            animation: glassSheen 8s ease-in-out infinite;
            pointer-events: none; mix-blend-mode: overlay; z-index: -1;
        }
        .stat-card::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%);
            opacity: 0; transition: opacity .35s ease;
            pointer-events: none; z-index: -1;
        }
        .stat-card:hover::after { opacity: .6; }
        @media (prefers-reduced-motion: reduce) {
            .stat-card::before { animation: none; }
        }
        .stat-label { font-size: 12px; color: var(--text-faint); text-transform: uppercase; letter-spacing: .5px; }
        .stat-value { font-size: 28px; font-weight: 800; color: var(--text-primary); }
        .stat-badge { font-size: 11px; font-weight: 600; border-radius: 4px; padding: 2px 8px; }

        /* ---- Status Badges ---- */
        .badge-in-stock  { background: #22c55e; color: #fff; }
        .badge-low-stock { background: #f59e0b; color: #fff; }
        .badge-out-stock { background: #ef4444; color: #fff; }
        .badge-delivered { color: #22c55e; font-weight: 600; }
        .badge-transit   { color: #3b82f6; font-weight: 600; }
        .badge-pending   { color: #f59e0b; font-weight: 600; }
        .badge-cancelled { color: #ef4444; font-weight: 600; }

        /* Category badges */
        .cat-toiletries { background: #dbeafe; color: #1d4ed8; }
        .cat-fb         { background: #dcfce7; color: #166534; }
        .cat-linens     { background: #fef3c7; color: #92400e; }
        .cat-cleaning   { background: #f3e8ff; color: #6b21a8; }
        .cat-minibar    { background: #ffe4e6; color: #9f1239; }

        /* ---- Tables ---- */
        .data-table {
            position: relative; isolation: isolate;
            background: var(--glass-bg-strong);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 8px 24px var(--glass-shadow);
            overflow: hidden;
            transition: background-color 0.25s ease, box-shadow 0.25s ease;
        }
        .data-table::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.4; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
        @media (prefers-reduced-motion: reduce) { .data-table::before { animation: none; } }
        .data-table thead th { background: var(--bg-subtle); font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); font-weight: 700; border-bottom: 2px solid var(--border-color); }
        .data-table tbody tr:hover { background: var(--bg-subtle); }
        .data-table tbody td { color: var(--text-primary); }

        /* ---- Alerts ---- */
        .alert-card {
            position: relative; overflow: hidden; isolation: isolate;
            border-radius: 10px; border: 1px solid var(--glass-border);
            padding: 16px; margin-bottom: 12px;
            background: var(--glass-bg-strong);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 6px 18px var(--glass-shadow);
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .alert-card::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%); opacity:.4; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite; pointer-events:none; mix-blend-mode:overlay; z-index:-1; }
        .alert-card::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%); opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:-1; }
        .alert-card:hover::after { opacity:.5; }
        @media (prefers-reduced-motion: reduce) { .alert-card::before { animation: none; } }
        .alert-card.critical { border-left: 4px solid #ef4444; }
        .alert-card.warning  { border-left: 4px solid #f59e0b; }
        .alert-card.info     { border-left: 4px solid #3b82f6; }

        /* ---- Flash ---- */
        .flash-alert { position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 280px; }

        /* ---- Buttons ---- */
        .btn-primary-7n { background: var(--accent-blue); border: none; color: #fff; }
        .btn-primary-7n:hover { background: #0077dd; color: #fff; }

        /* ---- Theme & Eye-Protection Toggle Group (top header) ---- */
        .theme-toggle-group {
            display: flex; align-items: center; gap: 2px;
            background: var(--bg-subtle);
            padding: 4px; border-radius: 999px;
            border: 1px solid var(--border-color);
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }
        .theme-icon-btn {
            width: 30px; height: 30px; border-radius: 50%;
            border: none; background: transparent;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text-muted); font-size: 16px;
            transition: all 0.18s ease;
        }
        .theme-icon-btn:hover { background: rgba(0,150,255,0.12); color: var(--accent-blue); }
        .theme-icon-btn.theme-active {
            background: var(--accent-blue); color: #fff;
            box-shadow: 0 2px 8px rgba(0,150,255,.4);
        }

        /* ---- Eye Protection Mode — warm overlay across the whole screen ---- */
        #eyeProtectOverlay {
            position: fixed; inset: 0; z-index: 999999;
            background: rgba(255, 183, 77, 0.12);
            mix-blend-mode: multiply;
            pointer-events: none;
            display: none;
        }

    </style>
</head>
<body>

<!-- Eye Protection Mode overlay — sits above everything, never blocks clicks -->
<div id="eyeProtectOverlay"></div>

<?php
$currentUser = Auth::user();
$flash = flash();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = parse_url(APP_URL, PHP_URL_PATH);
$currentRoute = str_replace($basePath, '', $currentPath);

function isActive(string $path): string {
    global $currentRoute;
    return ($currentRoute === $path || strpos($currentRoute, $path) === 0) ? 'active' : '';
}
?>

<!-- ======= LIQUID GLASS AMBIENT BACKGROUND ======= -->
<div id="glassAmbient" aria-hidden="true">
    <span class="g-blob g-blob-1"></span>
    <span class="g-blob g-blob-2"></span>
    <span class="g-blob g-blob-3"></span>
</div>

<!-- ======= SIDEBAR ======= -->
<div id="sidebar" class="liquid-glass">
    <div class="sidebar-brand">
        <div class="brand-logo"><span>7</span>NVENT</div>
        <div class="brand-sub">Hotel Inventory System</div>
    </div>

    <div class="sidebar-section">Main</div>
    <a href="<?= APP_URL ?>/dashboard" class="<?= isActive('/dashboard') ?>">
        <i class="ph ph-gauge"></i> Dashboard
    </a>
    <a href="<?= APP_URL ?>/inventory" class="<?= isActive('/inventory') ?>">
        <i class="ph ph-package"></i> Inventory
    </a>
    <a href="<?= APP_URL ?>/purchase-orders" class="<?= isActive('/purchase-orders') ?>">
        <i class="ph ph-receipt"></i> Purchase Orders
    </a>
    <a href="<?= APP_URL ?>/alerts" class="<?= isActive('/alerts') ?>">
        <i class="ph ph-bell"></i> Alerts
        <?php
        $alertCount = db()->fetchOne("SELECT COUNT(*) as cnt FROM alerts WHERE status='Active' AND alert_type='Critical'")['cnt'] ?? 0;
        if ($alertCount > 0): ?>
            <span class="badge bg-danger ms-auto" style="font-size:10px"><?= $alertCount ?></span>
        <?php endif; ?>
    </a>

    <div class="sidebar-section">Management</div>
    <a href="<?= APP_URL ?>/suppliers" class="<?= isActive('/suppliers') ?>">
        <i class="ph ph-truck"></i> Suppliers
    </a>
    <a href="<?= APP_URL ?>/locations" class="<?= isActive('/locations') ?>">
        <i class="ph ph-map-pin"></i> Locations
    </a>
    <a href="<?= APP_URL ?>/reports" class="<?= isActive('/reports') ?>">
        <i class="ph ph-chart-line"></i> Reports
    </a>

    <div class="sidebar-section">System</div>
    <a href="<?= APP_URL ?>/users" class="<?= isActive('/users') ?>">
        <i class="ph ph-users"></i> Users & Roles
    </a>
    <a href="<?= APP_URL ?>/qr-scanner" class="<?= isActive('/qr-scanner') ?>">
        <i class="ph ph-barcode"></i> QR Scanner
    </a>
    <a href="<?= APP_URL ?>/settings" class="<?= isActive('/settings') ?>">
        <i class="ph ph-gear"></i> Settings
    </a>
    <a href="<?= APP_URL ?>/analytics" class="<?= isActive('/analytics') ?>">
        <i class="ph ph-chart-line-up"></i> Analytics
    </a>
    <a href="#" id="exitBtn" onclick="showLogoutModal(event)">
        <i class="ph ph-sign-out"></i> Exit
    </a>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 2)) ?></div>
        <div>
            <div class="user-name"><?= clean($currentUser['full_name'] ?? '') ?></div>
            <div class="user-role"><?= clean($currentUser['role_name'] ?? '') ?></div>
        </div>
    </div>
</div>

<!-- ======= LOGOUT CONFIRMATION MODAL ======= -->


<!-- Note: Chat / Language / Text-Size trigger buttons now live in the
     top header (utility-toggle-group). The panels below are unchanged
     and still opened via toggleChat() / toggleLang() / toggleA11y(). -->

<div id="a11yPanel" style="
    display:none; position:fixed; top:64px; right:24px; z-index:8887;
    background:var(--bg-card); border-radius:18px; padding:20px;
    box-shadow:0 12px 40px rgba(0,0,0,0.18); width:240px;
    border:1px solid var(--border-color);
    transform:scale(0.85) translateY(-10px); opacity:0;
    transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    transform-origin:top right;
">
    <div style="font-size:13px;font-weight:800;color:var(--text-primary);margin-bottom:4px;display:flex;align-items:center;gap:6px">
        <i class="ph ph-text-aa"></i> Text Size
    </div>
    <div style="font-size:11px;color:var(--text-faint);margin-bottom:14px">
        Choose a comfortable reading size
    </div>

    <!-- Size options -->
    <div class="d-flex flex-column gap-2">
        <button onclick="setFontSize('normal')" id="sizeNormal"
            style="padding:12px 16px;border-radius:12px;border:2px solid var(--border-color);
                   background:var(--bg-subtle);cursor:pointer;text-align:left;
                   transition:all 0.2s;display:flex;align-items:center;gap:10px"
            onmouseover="this.style.borderColor='#0096FF';this.style.background='rgba(0,150,255,0.10)'"
            onmouseout="if(!this.classList.contains('a11y-active')){this.style.borderColor='var(--border-color)';this.style.background='var(--bg-subtle)'}">
            <span style="font-size:18px;color:var(--text-primary)">A</span>
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">Normal</div>
                <div style="font-size:10px;color:var(--text-faint)">Default size (14px)</div>
            </div>
        </button>

        <button onclick="setFontSize('large')" id="sizeLarge"
            style="padding:12px 16px;border-radius:12px;border:2px solid var(--border-color);
                   background:var(--bg-subtle);cursor:pointer;text-align:left;
                   transition:all 0.2s;display:flex;align-items:center;gap:10px"
            onmouseover="this.style.borderColor='#0096FF';this.style.background='rgba(0,150,255,0.10)'"
            onmouseout="if(!this.classList.contains('a11y-active')){this.style.borderColor='var(--border-color)';this.style.background='var(--bg-subtle)'}">
            <span style="font-size:22px;font-weight:700;color:var(--text-primary)">A</span>
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">Large</div>
                <div style="font-size:10px;color:var(--text-faint)">Bigger text (18px)</div>
            </div>
        </button>

        <button onclick="setFontSize('xlarge')" id="sizeXlarge"
            style="padding:12px 16px;border-radius:12px;border:2px solid var(--border-color);
                   background:var(--bg-subtle);cursor:pointer;text-align:left;
                   transition:all 0.2s;display:flex;align-items:center;gap:10px"
            onmouseover="this.style.borderColor='#0096FF';this.style.background='rgba(0,150,255,0.10)'"
            onmouseout="if(!this.classList.contains('a11y-active')){this.style.borderColor='var(--border-color)';this.style.background='var(--bg-subtle)'}">
            <span style="font-size:28px;font-weight:700;color:var(--text-primary)">A</span>
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text-primary)">Extra Large</div>
                <div style="font-size:10px;color:var(--text-faint)">Maximum size (22px)</div>
            </div>
        </button>
    </div>

    <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border-subtle);font-size:10px;color:var(--text-faint);text-align:center;display:flex;align-items:center;justify-content:center;gap:5px">
        <i class="ph ph-wheelchair"></i> Accessibility Feature — Setting saved automatically
    </div>
</div>

<style>
.a11y-active {
    border-color:#0096FF !important;
    background:rgba(0,150,255,0.12) !important;
    box-shadow:0 0 0 3px rgba(0,150,255,0.12) !important;
}
body.font-large    { font-size:18px !important; }
body.font-xlarge   { font-size:22px !important; }
body.font-large *,
body.font-xlarge * {
    line-height:1.7 !important;
}
body.font-large .stat-card,
body.font-xlarge .stat-card {
    padding:24px !important;
}
body.font-large table td,
body.font-large table th,
body.font-xlarge table td,
body.font-xlarge table th {
    padding:16px 14px !important;
    font-size:inherit !important;
}
body.font-large .btn,
body.font-xlarge .btn {
    font-size:inherit !important;
    padding:10px 18px !important;
}
body.font-large #sidebar a,
body.font-xlarge #sidebar a {
    font-size:16px !important;
    padding:12px 18px !important;
}
@keyframes a11yPop { from{transform:scale(0)} to{transform:scale(1)} }
</style>

<script>
// Apply saved font size on load
(function() {
    const saved = localStorage.getItem('7nvent_fontsize') || 'normal';
    applyFontSize(saved, false);
})();

function toggleA11y() {
    const panel = document.getElementById('a11yPanel');
    const isOpen = panel.style.display === 'block';
    if (isOpen) {
        panel.style.opacity = '0';
        panel.style.transform = 'scale(0.85) translateY(-10px)';
        setTimeout(() => panel.style.display = 'none', 280);
    } else {
        panel.style.display = 'block';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            panel.style.opacity = '1';
            panel.style.transform = 'scale(1) translateY(0)';
        }));
    }
}

function setFontSize(size) {
    localStorage.setItem('7nvent_fontsize', size);
    applyFontSize(size, true);
    // Close panel after brief delay
    setTimeout(toggleA11y, 600);
}

function applyFontSize(size, animate) {
    document.body.classList.remove('font-large','font-xlarge');
    if (size === 'large')   document.body.classList.add('font-large');
    if (size === 'xlarge')  document.body.classList.add('font-xlarge');

    // Update active button state
    ['normal','large','xlarge'].forEach(s => {
        const btn = document.getElementById('size' + s.charAt(0).toUpperCase() + s.slice(1));
        if (!btn) return;
        btn.classList.toggle('a11y-active', s === size);
        if (s !== size) {
            btn.style.borderColor = 'var(--border-color)';
            btn.style.background  = 'var(--bg-subtle)';
            btn.style.boxShadow   = '';
        }
    });

    // Bounce the accessibility button to confirm
    if (animate) {
        const btn = document.getElementById('a11yBtn');
        btn.style.transform = 'scale(1.3) rotate(10deg)';
        setTimeout(() => btn.style.transform = 'scale(1)', 300);
    }
}

// Close panel when clicking outside
document.addEventListener('click', function(e) {
    const panel = document.getElementById('a11yPanel');
    const btn   = document.getElementById('a11yBtn');
    if (panel.style.display === 'block' && !panel.contains(e.target) && !btn.contains(e.target)) {
        panel.style.opacity = '0';
        panel.style.transform = 'scale(0.85) translateY(-10px)';
        setTimeout(() => panel.style.display = 'none', 280);
    }
});
</script>

<!-- ======= LANGUAGE PANEL ======= -->
<div id="langPanel" style="
    display:none; position:fixed; top:64px; right:24px; z-index:8887;
    background:var(--bg-card); border-radius:18px; padding:20px; width:230px;
    box-shadow:0 12px 40px rgba(0,0,0,0.18); border:1px solid var(--border-color);
    transform:scale(0.85) translateY(-10px); opacity:0;
    transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1); transform-origin:top right;
">
    <div style="font-size:13px;font-weight:800;color:var(--text-primary);margin-bottom:4px;display:flex;align-items:center;gap:6px"><i class="ph ph-translate"></i> Pilih Bahasa / Language</div>
    <div style="font-size:11px;color:var(--text-faint);margin-bottom:14px">Tukar bahasa sistem / Change system language</div>
    <div class="d-flex flex-column gap-2">
        <button onclick="setLang('en')" id="btnEN"
            style="padding:12px 16px;border-radius:12px;border:2px solid #0096FF;background:rgba(0,150,255,0.10);cursor:pointer;text-align:left;display:flex;align-items:center;gap:10px;transition:all 0.2s">
            <span style="font-size:22px">🇬🇧</span>
            <div><div style="font-size:13px;font-weight:700;color:var(--text-primary)">English</div><div style="font-size:10px;color:var(--text-faint)">Default language</div></div>
        </button>
        <button onclick="setLang('bm')" id="btnBM"
            style="padding:12px 16px;border-radius:12px;border:2px solid var(--border-color);background:var(--bg-subtle);cursor:pointer;text-align:left;display:flex;align-items:center;gap:10px;transition:all 0.2s">
            <span style="font-size:22px">🇲🇾</span>
            <div><div style="font-size:13px;font-weight:700;color:var(--text-primary)">Bahasa Melayu</div><div style="font-size:10px;color:var(--text-faint)">Bahasa Malaysia rasmi</div></div>
        </button>
    </div>
</div>

<!-- ======= AI CHAT WIDGET ======= -->
<div id="chatPanel" style="
    display:none; position:fixed; top:64px; right:24px; z-index:8887;
    width:340px; border-radius:20px;
    transform:scale(0.85) translateY(-10px); opacity:0;
    transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s cubic-bezier(0.34,1.56,0.64,1),
               background-color 0.25s ease, box-shadow 0.25s ease;
    transform-origin:top right;
">
    <div style="background:linear-gradient(135deg,#0096FF,#6366f1);padding:16px 18px;display:flex;align-items:center;gap:12px;position:relative;z-index:1">
        <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff"><i class="ph ph-robot"></i></div>
        <div>
            <div style="font-size:14px;font-weight:800;color:#fff">7NVENT Help</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.85)"><span style="color:#4ade80">●</span> Online — Quick answers to common questions</div>
        </div>
        <button onclick="toggleChat()" style="margin-left:auto;background:rgba(255,255,255,0.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:background-color 0.15s" onmouseover="this.style.background='rgba(255,255,255,0.32)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'"><i class="ph ph-x"></i></button>
    </div>
    <div id="chatMessages" style="height:260px;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;scroll-behavior:smooth;position:relative;z-index:1">
        <div class="chat-bot-msg"><i class="ph ph-hand-waving"></i> Hello! I'm your <strong>7NVENT Help</strong> guide.<br>Click a question below or type your own!</div>
    </div>
    <div class="chat-qs-wrap" style="padding:10px 14px;position:relative;z-index:1">
        <div style="font-size:10px;color:var(--text-faint);font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Common Questions — Click to ask</div>
        <div id="quickQs" style="display:flex;flex-direction:column;gap:4px;max-height:130px;overflow-y:auto"></div>
    </div>
    <div class="chat-input-wrap" style="padding:10px 14px;display:flex;gap:8px;position:relative;z-index:1">
        <input id="chatInput" type="text" placeholder="Type a question..." class="chat-text-input"
               onkeydown="if(event.key==='Enter') sendChat()">
        <button onclick="sendChat()" class="chat-send-btn"><i class="ph ph-paper-plane-tilt"></i></button>
    </div>
</div>

<style>
#chatPanel {
    position: fixed; overflow: hidden; isolation: isolate;
    background: var(--glass-bg-strong);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border: 1px solid var(--glass-border);
    box-shadow: 0 1px 0 var(--glass-highlight) inset, 0 16px 50px var(--glass-shadow);
}
#chatPanel::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(115deg, transparent 30%, var(--glass-highlight) 47%, transparent 64%);
    opacity:.4; transform:translateX(-130%); animation:glassSheen 9s ease-in-out infinite;
    pointer-events:none; mix-blend-mode:overlay; z-index:0;
}
#chatPanel::after {
    content:''; position:absolute; inset:0;
    background:radial-gradient(circle at var(--mx,50%) var(--my,50%), var(--glass-highlight), transparent 42%);
    opacity:0; transition:opacity .35s ease; pointer-events:none; z-index:0;
}
#chatPanel:hover::after { opacity:0.5; }
@media (prefers-reduced-motion: reduce) { #chatPanel::before { animation:none; } }

.chat-qs-wrap { background:var(--glass-bg); border-top:1px solid var(--glass-border); }
.chat-input-wrap { background:var(--glass-bg); border-top:1px solid var(--glass-border); }

.chat-bot-msg { background:var(--bg-subtle); border:1px solid rgba(0,150,255,0.18); border-radius:14px 14px 14px 4px; padding:10px 13px; font-size:12px; color:var(--text-primary); line-height:1.6; animation:msgPop 0.3s cubic-bezier(0.34,1.56,0.64,1); max-width:92%; }
.chat-user-msg { background:linear-gradient(135deg,#0096FF,#6366f1); border-radius:14px 14px 4px 14px; padding:10px 13px; font-size:12px; color:#fff; line-height:1.6; animation:msgPop 0.3s cubic-bezier(0.34,1.56,0.64,1); max-width:92%; align-self:flex-end; box-shadow:0 4px 14px rgba(0,150,255,0.28); }
.quick-q { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:8px; padding:6px 10px; font-size:11px; color:var(--text-secondary); cursor:pointer; text-align:left; transition:all 0.15s; font-weight:600; width:100%; }
.quick-q:hover { background:rgba(0,150,255,0.14); border-color:#0096FF; color:#0096FF; transform:translateX(2px); }

.chat-text-input {
    flex:1; padding:9px 12px; border:2px solid var(--glass-border); border-radius:10px;
    font-size:13px; outline:none; transition:all 0.2s;
    background:var(--glass-bg); color:var(--text-primary);
}
.chat-text-input::placeholder { color:var(--text-faint); opacity:1; }
.chat-text-input:focus {
    border-color:#0096FF; background:var(--glass-bg-strong);
    box-shadow:0 0 0 3px rgba(0,150,255,0.18);
}
.chat-send-btn {
    background:linear-gradient(135deg,#0096FF,#6366f1); color:#fff; border:none;
    border-radius:10px; padding:9px 14px; cursor:pointer; font-size:14px;
    display:flex; align-items:center; justify-content:center;
    transition:all 0.2s; box-shadow:0 3px 10px rgba(0,150,255,0.3);
}
.chat-send-btn:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,150,255,0.45); }

@keyframes msgPop { from{opacity:0;transform:scale(0.85)} to{opacity:1;transform:scale(1)} }
</style>

<script>
// ======= TRANSLATIONS =======
const translations = {
    'Dashboard':'Papan Pemuka','Inventory':'Inventori','Purchase Orders':'Pesanan Pembelian',
    'Alerts':'Amaran','Suppliers':'Pembekal','Locations':'Lokasi','Reports':'Laporan',
    'Users & Roles':'Pengguna & Peranan','QR Scanner':'Pengimbas QR','Settings':'Tetapan',
    'Exit':'Keluar','MAIN':'UTAMA','MANAGEMENT':'PENGURUSAN','SYSTEM':'SISTEM',
    'Save Changes':'Simpan Perubahan','Add Item':'Tambah Item','Add Supplier':'Tambah Pembekal',
    'Add User':'Tambah Pengguna','Cancel':'Batal','Update User':'Kemaskini Pengguna',
    'Approve':'Luluskan','Dismiss':'Tolak','View Stock':'Lihat Stok',
    'Generate Report':'Jana Laporan','View All':'Lihat Semua',
    'Total Items in Stock':'Jumlah Item dalam Stok','Pending Order Value':'Nilai Pesanan Tertunda',
    'Critical Alerts':'Amaran Kritikal','Weekly Consumption':'Penggunaan Mingguan',
    'Stock Levels':'Tahap Stok','Recent Activity':'Aktiviti Terkini','Active Alerts':'Amaran Aktif',
    'In-Stock':'Dalam Stok','Low Stock':'Stok Rendah','Out of Stock':'Kehabisan Stok',
    'In Transit':'Dalam Penghantaran','Delivered':'Diterima','Pending':'Tertunda',
    'Cancelled':'Dibatalkan','Operational':'Beroperasi','Active':'Aktif','Inactive':'Tidak Aktif',
    'Today':'Hari Ini','Never':'Tidak Pernah','Item Name':'Nama Item','Category':'Kategori',
    'Quantity':'Kuantiti','Par Level':'Aras Par','Status':'Status','Actions':'Tindakan',
    'Date':'Tarikh','Rating':'Penilaian','Lead Time':'Masa Penghantaran',
    'Location':'Lokasi','Supplier':'Pembekal','Department':'Jabatan',

    // Inventory page
    'Add New Item':'Tambah Item Baharu','Item Code':'Kod Item','Current Quantity':'Kuantiti Semasa',
    'Unit Price':'Harga Seunit','Expiry Date':'Tarikh Luput','All Categories':'Semua Kategori',
    'All Locations':'Semua Lokasi','All Status':'Semua Status','Clear Filters':'Kosongkan Penapis',
    'FIFO Enforcement Queue':'Baris Giliran Penguatkuasaan FIFO','FIFO Compliance':'Pematuhan FIFO',
    'Perishable Items':'Item Mudah Rosak','Expired':'Luput','Priority Queue':'Baris Giliran Keutamaan',
    'Expires Today':'Luput Hari Ini','Use Now':'Guna Sekarang','Use Next':'Guna Seterusnya',
    'Live Preview':'Pratonton Langsung','Stock Health':'Kesihatan Stok','Quick Tips':'Petua Pantas',
    'Choose Location':'Pilih Lokasi','Item Info':'Maklumat Item','Last Updated':'Terakhir Dikemaskini',
    'Current Values':'Nilai Semasa','No Items Found':'Tiada Item Dijumpai','View Only':'Lihat Sahaja',

    // Alerts page
    'Total Active':'Jumlah Aktif','Critical':'Kritikal','Warning':'Amaran','Info':'Maklumat',
    'All Systems Normal':'Semua Sistem Normal','No Active Alerts':'Tiada Amaran Aktif',
    'Scan Inventory Now':'Imbas Inventori Sekarang','Auto Generated':'Dijana Automatik',

    // Analytics page
    'Inventory Valuation Trend':'Trend Penilaian Inventori','Fast vs Slow-Moving Items':'Item Cepat lawan Lambat Bergerak',
    'Supplier Delivery Performance':'Prestasi Penghantaran Pembekal','Consumption Heatmap':'Peta Haba Penggunaan',
    'Supplier Ratings':'Penilaian Pembekal',

    // Purchase Orders page
    'PO Number':'Nombor PO','Total Value':'Jumlah Nilai','Raised By':'Dikeluarkan Oleh',
    'New Purchase Order':'Pesanan Pembelian Baharu','Choose Supplier':'Pilih Pembekal',
    'Items to Order':'Item untuk Dipesan','Subtotal':'Jumlah Kecil','Schedule':'Jadual',
    'PO Date':'Tarikh PO','Expected Delivery':'Jangkaan Penghantaran','Additional Info':'Maklumat Tambahan',
    'Notes':'Nota','Send Purchase Order':'Hantar Pesanan Pembelian','Order Summary':'Ringkasan Pesanan',
    'Total Items':'Jumlah Item','Line Items':'Item Baris','Created By':'Dicipta Oleh',
    'Quick Guide':'Panduan Ringkas','Track':'Jejak','Deliver':'Hantar',
    'No Purchase Orders Found':'Tiada Pesanan Pembelian Dijumpai','Delivery Status Tracker':'Penjejak Status Penghantaran',
    'Order Placed':'Pesanan Dibuat','On The Way':'Dalam Perjalanan','Order Information':'Maklumat Pesanan',
    'Contact Person':'Orang Hubungan','Expected Arrival':'Jangkaan Ketibaan','Items Ordered':'Item Dipesan',
    'Grand Total':'Jumlah Besar','Current Status':'Status Semasa','Order Date':'Tarikh Pesanan',
    'Expected':'Dijangka','Approval Details':'Butiran Kelulusan','Auto-Generated':'Dijana Automatik',
    'Manual Approval':'Kelulusan Manual','Timeline':'Garis Masa','Order Created':'Pesanan Dicipta',
    'Shipment Dispatched':'Penghantaran Dihantar','Pending Dispatch':'Menunggu Penghantaran',
    'Order Received':'Pesanan Diterima','Mark In Transit':'Tandakan Dalam Penghantaran',
    'Mark Delivered':'Tandakan Diterima','Back':'Kembali',

    // Suppliers page
    'Active Suppliers':'Pembekal Aktif','Avg Rating':'Purata Penilaian','Total YTD Orders':'Jumlah Pesanan Tahun Ini',
    'Add New Supplier':'Tambah Pembekal Baharu','Company Information':'Maklumat Syarikat','Company Name':'Nama Syarikat',
    'Product Category':'Kategori Produk','Contact Details':'Butiran Hubungan','Representative Name':'Nama Wakil',
    'Phone Number':'Nombor Telefon','Email Address':'Alamat E-mel','Performance Metrics':'Metrik Prestasi',
    'Supplier Rating':'Penilaian Pembekal','Lead Time (Days)':'Masa Penghantaran (Hari)','Save Supplier':'Simpan Pembekal',
    'History':'Sejarah','Website':'Laman Web',

    // Locations page
    'Items Across All Locations':'Item Merentas Semua Lokasi','Total Storage Locations':'Jumlah Lokasi Storan',
    'Locations Low Stock':'Lokasi Stok Rendah','Storage Usage':'Penggunaan Storan','Capacity':'Kapasiti',
    'Partial Low':'Sebahagian Rendah',

    // Reports page
    'Stock Summary Reports':'Laporan Ringkasan Stok','Consumption Analytics':'Analitik Penggunaan',
    'Purchase Order History':'Sejarah Pesanan Pembelian','Inventory Valuation':'Penilaian Inventori',
    'Supplier Performance':'Prestasi Pembekal','Waste & Expiry Report':'Laporan Pembaziran & Luput',
    'Stock by Category':'Stok Mengikut Kategori','Categories':'Kategori',
    'System Performance Metrics':'Metrik Prestasi Sistem','Manual Counting Time Reduced':'Masa Kiraan Manual Dikurangkan',
    'Inventory Accuracy Rate':'Kadar Ketepatan Inventori','Waste Reduction vs Baseline':'Pengurangan Pembaziran berbanding Asas',
    'Inventory Value Breakdown':'Pecahan Nilai Inventori','Print / PDF':'Cetak / PDF',
    'Record Found':'Rekod Dijumpai','Generated':'Dijana',

    // Settings page
    'General Settings':'Tetapan Am','System Name':'Nama Sistem','Automated Reorder Alerts':'Amaran Pesanan Semula Automatik',
    'Expiry Notifications':'Notifikasi Luput','FIFO Enforcement':'Penguatkuasaan FIFO','Data Backup Frequency':'Kekerapan Sandaran Data',
    'PDPA Compliance Mode':'Mod Pematuhan PDPA','Save Settings':'Simpan Tetapan','Notification Settings':'Tetapan Notifikasi',
    'Notification Email Recipient':'Penerima E-mel Notifikasi','Notification Frequency':'Kekerapan Notifikasi',
    'Low Stock Threshold':'Ambang Stok Rendah','Active Notification Types':'Jenis Notifikasi Aktif',
    'Low Stock Alert':'Amaran Stok Rendah','Expiry Warning':'Amaran Luput','Purchase Order Update':'Kemaskini Pesanan Pembelian',
    'Save Notifications':'Simpan Notifikasi','Inventory Rules':'Peraturan Inventori','Warning Threshold':'Ambang Amaran',
    'Critical Threshold':'Ambang Kritikal','Stock Adjustment Approval':'Kelulusan Pelarasan Stok',
    'Save Inventory Rules':'Simpan Peraturan Inventori','Integrations':'Integrasi','SMTP Email Server':'Pelayan E-mel SMTP',
    'Barcode / QR Scanner':'Pengimbas Kod Bar / QR','Scanner Type':'Jenis Pengimbas','Phone Camera':'Kamera Telefon',
    'USB Scanner':'Pengimbas USB','Bluetooth':'Bluetooth','Cloud Backup':'Sandaran Awan',
    'Save Integrations':'Simpan Integrasi','Security Settings':'Tetapan Keselamatan','Session Timeout':'Had Masa Sesi',
    'Minimum Password Length':'Panjang Kata Laluan Minimum','Maximum Login Attempts':'Percubaan Log Masuk Maksimum',
    'Account Lockout Duration':'Tempoh Akaun Dikunci','Current System Users':'Pengguna Sistem Semasa',
    'Save Security Settings':'Simpan Tetapan Keselamatan','Backup Settings':'Tetapan Sandaran','Backup Frequency':'Kekerapan Sandaran',
    'Backup Retention Period':'Tempoh Simpanan Sandaran','Storage Location':'Lokasi Storan',
    'Run Manual Backup Now':'Jalankan Sandaran Manual Sekarang','Save Backup Settings':'Simpan Tetapan Sandaran',
    'Full Name':'Nama Penuh','Username':'Nama Pengguna','Role':'Peranan',

    // Users & Roles page
    'Users & Roles Management':'Pengurusan Pengguna & Peranan','Total Users':'Jumlah Pengguna',
    'Active Users':'Pengguna Aktif','Total Roles':'Jumlah Peranan','Access Level':'Tahap Akses',
    'Last Login':'Log Masuk Terakhir','User ID':'ID Pengguna','Edit User':'Sunting Pengguna',
    'Personal Information':'Maklumat Peribadi','Login Credentials':'Kelayakan Log Masuk','Password':'Kata Laluan',
    'Role & Department':'Peranan & Jabatan','Select Role':'Pilih Peranan','Save New User':'Simpan Pengguna Baharu',
    'Security Guide':'Panduan Keselamatan','User Information':'Maklumat Pengguna','Role Assignment':'Penugasan Peranan',
    'Account Status':'Status Akaun','Account Details':'Butiran Akaun','Account Created':'Akaun Dicipta',
    'Access Level Info':'Maklumat Tahap Akses','Never Logged In':'Tidak Pernah Log Masuk',

    // QR Scanner page
    'QR / Barcode Scanner':'Pengimbas QR / Kod Bar','Scanner Mode':'Mod Pengimbas',
    'Plug-in Barcode Gun':'Pengimbas Kod Bar Palam','Wireless Scanner':'Pengimbas Wayarles','Scan':'Imbas',
    'Generate QR':'Jana QR','Manual Search / Scan QR':'Carian Manual / Imbas QR','Camera':'Kamera',
    'Item Found':'Item Dijumpai','Price/Unit':'Harga/Unit','Quick Stock Update':'Kemaskini Stok Pantas',
    'Receive Stock':'Terima Stok','Issue Stock':'Keluarkan Stok','Generate QR Codes':'Jana Kod QR',
    'Search Item':'Cari Item','Scan History':'Sejarah Imbasan','Refresh':'Muat Semula',
    'Point Camera At Barcode':'Halakan Kamera ke Kod Bar','Flip':'Balik','Download QR':'Muat Turun QR',
    'No Scan History':'Tiada Sejarah Imbasan','Loading':'Memuatkan',
    'Scan with iPhone camera · Download & print to stick on item':'Imbas dengan kamera iPhone · Muat turun & cetak untuk dilekatkan pada item',
    'Search item by name or code...':'Cari item mengikut nama atau kod...',
    'Change Photo':'Tukar Foto','Upload Photo':'Muat Naik Foto',
};
let currentLang = localStorage.getItem('7nvent_lang') || 'en';
const originalTexts = new Map();

function toggleLang() {
    const panel = document.getElementById('langPanel');
    const isOpen = panel.style.display === 'block';
    closeAllPanels2();
    if (!isOpen) openPanel2(panel);
}

function setLang(lang) {
    currentLang = lang;
    localStorage.setItem('7nvent_lang', lang);
    document.getElementById('langFlag').textContent = lang === 'bm' ? '🇬🇧' : '🇲🇾';
    applyTranslations(lang);
    ['EN','BM'].forEach(l => {
        const btn = document.getElementById('btn'+l);
        const active = (l === lang.toUpperCase());
        btn.style.borderColor = active ? '#0096FF' : 'var(--border-color)';
        btn.style.background  = active ? 'rgba(0,150,255,0.10)' : 'var(--bg-subtle)';
    });
    const lb = document.getElementById('langBtn');
    lb.style.transform = 'scale(1.3) rotate(10deg)';
    setTimeout(() => { lb.style.transform='scale(1)'; closeAllPanels2(); }, 350);
}

// Sort longest phrase first so multi-word entries (e.g. "Active Suppliers")
// get fully translated before shorter substrings (e.g. "Active") can partially
// eat into them and leave a broken mixed-language result.
const translationEntries = Object.entries(translations).sort((a, b) => b[0].length - a[0].length);

function applyTranslations(lang) {
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
        acceptNode(n) {
            const p = n.parentElement;
            if (!p) return NodeFilter.FILTER_REJECT;
            if (['SCRIPT','STYLE','NOSCRIPT','INPUT','TEXTAREA'].includes(p.tagName)) return NodeFilter.FILTER_REJECT;
            if (p.closest('#chatPanel,#langPanel,#a11yPanel,#logoutModal')) return NodeFilter.FILTER_REJECT;
            return n.textContent.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
        }
    });
    const nodes = []; let n;
    while (n = walker.nextNode()) nodes.push(n);
    nodes.forEach(node => {
        if (!originalTexts.has(node)) originalTexts.set(node, node.textContent);
        const orig = originalTexts.get(node);
        if (lang === 'en') { node.textContent = orig; return; }
        let t = orig;
        translationEntries.forEach(([en, bm]) => {
            t = t.replace(new RegExp(`\\b${en.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')}\\b`, 'gi'), bm);
        });
        node.textContent = t;
    });

    // Also translate common placeholder / title / value attributes on
    // form controls, since the text-node walker above only covers visible
    // text content and not attribute values.
    document.querySelectorAll('[placeholder]').forEach(el => {
        if (!el.dataset.origPlaceholder) el.dataset.origPlaceholder = el.getAttribute('placeholder');
        let t = el.dataset.origPlaceholder;
        if (lang !== 'en') {
            translationEntries.forEach(([en, bm]) => {
                t = t.replace(new RegExp(`\\b${en.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')}\\b`, 'gi'), bm);
            });
        }
        el.setAttribute('placeholder', t);
    });
}

window.addEventListener('load', () => {
    if (currentLang === 'bm') {
        document.getElementById('langFlag').textContent = '🇬🇧';
        applyTranslations('bm');
        document.getElementById('btnBM').style.borderColor = '#0096FF';
        document.getElementById('btnBM').style.background  = 'rgba(0,150,255,0.10)';
        document.getElementById('btnEN').style.borderColor = 'var(--border-color)';
        document.getElementById('btnEN').style.background  = 'var(--bg-subtle)';
    }
});

// ======= CHATBOT =======
const qna = [
    {q:'How to add new inventory item?', a:'📦 Go to <b>Inventory</b> → click <b>+ Add Item</b> → fill Item Name, select Category, choose Location → set Quantity & Par Level → click <b>Save</b>.'},
    {q:'How to create a Purchase Order?', a:'🧾 Go to <b>Purchase Orders</b> → click <b>+ New Purchase Order</b> → select Supplier, enter items and value, set dates → click <b>Send Purchase Order</b>.'},
    {q:'How to approve an alert?', a:'🚨 Go to <b>Alerts</b> → click <b>Approve</b> to auto-generate a PO, or <b>Dismiss</b> to clear it. Only Inventory Manager & Procurement Officer can approve.'},
    {q:'How to generate a report?', a:'📊 Go to <b>Reports</b> → choose report type (Stock Summary, Consumption Analytics, PO History, etc.) → click <b>Generate Report</b>.'},
    {q:'What are the 6 user roles?', a:'👥 6 roles in 7NVENT:<br>⚙️ <b>Inventory Manager</b> — Full Admin<br>🏠 <b>Housekeeping Manager</b> — Update stock<br>🛒 <b>Procurement Officer</b> — Manage POs<br>💻 <b>IT Admin</b> — Manage users<br>🏨 <b>Hotel GM</b> — View reports<br>👁️ <b>Supervisor</b> — View only'},
    {q:'How to scan a QR code?', a:'📷 Go to <b>QR Scanner</b> → select mode (Camera/USB/Bluetooth) → click <b>Camera</b> → point at QR code. You can also generate QR codes for printing.'},
    {q:'What is Par Level?', a:'📏 Par Level is the <b>minimum stock threshold</b>. When quantity falls below par level, system auto-generates an Alert and can create a Purchase Order automatically.'},
    {q:'How to add a new user?', a:'👤 Go to <b>Users & Roles</b> → click <b>Add User</b> → fill Name, Email, Username, Password → select Role → set Department → click <b>Save New User</b>.'},
    {q:'How to change text size?', a:'🔠 Click the <b>blue 🔠 button</b> at bottom right → choose Normal (14px), Large (18px), or Extra Large (22px). Saved automatically!'},
    {q:'How to view supplier website?', a:'🌐 Go to <b>Suppliers</b> → find the supplier card → click <b>🌐 Website</b> to open their official website in a new tab.'},
    {q:'How to view stock by location?', a:'📍 Go to <b>Locations</b> → see all 6 storage areas with item count, capacity bars, and status (Operational/Low Stock).'},
    {q:'How to change language to Malay?', a:'🇲🇾 Click the <b>orange 🇲🇾 button</b> at bottom right → select <b>Bahasa Melayu</b>. Click again to switch back to English.'},
    {q:'Siapa yang membangunkan sistem ini?', a:'🎓 <b>7NVENT Hotel Inventory Management System</b> dibangunkan oleh <b>Syukri Zainal</b> sebagai Projek Tahun Akhir (Final Year Project). Hubungi pembangun melalui pautan sosial di halaman log masuk.'},
    {q:'Bagaimana sistem ini berfungsi?', a:'⚙️ 7NVENT dibina menggunakan PHP + MySQL (custom MVC) dan menguruskan seluruh kitaran inventori hotel: rekod stok, pesanan pembelian, amaran stok rendah/luput (FIFO), pembekal, lokasi storan, pengurusan pengguna & peranan, imbasan QR/barcode, serta laporan & analitik — semuanya berdasarkan data langsung dari pangkalan data, bukan data statik.'},
    {q:'Apa penambahbaikan akan datang untuk sistem ini?', a:'🚀 Antara penambahbaikan yang sedang dipertimbangkan: integrasi Google Drive sebenar untuk muat naik QR (kini terhad kepada fail peranti sahaja), notifikasi e-mel automatik, aplikasi mudah alih, dan liputan terjemahan Bahasa Melayu yang lebih menyeluruh. Ini kekal sebagai kerja lanjutan projek FYP.'},
    {q:'Ada sebarang kemas kini akan datang?', a:'🔄 Ya — sistem ini sentiasa dikemaskini sepanjang pembangunan FYP. Kemas kini terkini termasuk reka bentuk kaca (glassmorphism) di seluruh sistem, muat naik imej QR, dan penambahbaikan kebolehcapaian mod gelap/terang.'},

    // ===== Greetings & general =====
    {q:'Hi hello hey good morning good afternoon', a:'👋 Hi there! I can help with Inventory, Purchase Orders, Alerts, Suppliers, Locations, Reports, Users & Roles, and QR Scanner. What do you need?'},
    {q:'Thanks thank you appreciate it', a:'😊 You&#x2019;re welcome! Let me know if you need anything else.'},
    {q:'Who are you what can you do help me', a:'🛟 I&#x2019;m the 7NVENT Help guide — a quick-answer assistant covering common how-to questions about this system. For anything outside these topics, contact your IT Administrator.'},
    {q:'Is this system secure safe', a:'🔒 7NVENT uses role-based access control, hashed passwords, and session timeouts. Only authorized roles can perform sensitive actions like approving alerts or deleting items.'},
    {q:'What database does this system use', a:'🗄️ 7NVENT runs on MySQL/MariaDB via a custom PHP MVC framework (no external framework), served locally through XAMPP (Apache + PHP 8).'},
    {q:'Is the data real time live', a:'⚡ Yes — all figures (stock levels, KPIs, charts) are pulled live from the database on every page load, not static/mock data.'},
    {q:'How to logout sign out', a:'🚪 Click your avatar at the bottom of the sidebar → click <b>Exit / Logout</b> → confirm in the popup.'},
    {q:'Forgot password reset password', a:'🔑 Passwords can only be reset by an <b>IT Administrator</b> via Users & Roles → select the user → Update. Contact your IT Admin directly.'},
    {q:'How long does session last timeout', a:'⏱ Your session automatically expires after 30 minutes of inactivity, shown in the footer of the login page.'},

    // ===== Dashboard =====
    {q:'What is shown on the dashboard', a:'📈 The Dashboard shows total stock, pending PO value, critical alerts, stock levels by category, weekly consumption trends, and recent activity — all live.'},
    {q:'What does KPI mean on dashboard', a:'📊 KPI cards at the top summarize Total Items in Stock, Pending Order Value, and Critical Alerts at a glance.'},

    // ===== Inventory =====
    {q:'How to edit an inventory item', a:'✏️ Go to <b>Inventory</b> → find the item → click <b>Edit</b> → update fields → click <b>Save Changes</b>.'},
    {q:'How to delete remove an inventory item', a:'🗑️ Go to <b>Inventory</b> → find the item → click <b>Del</b> → confirm. Only Inventory Managers can delete items.'},
    {q:'What are the item categories', a:'🏷️ 7NVENT tracks 5 categories: Toiletries, F&amp;B, Linens, Cleaning, and Minibar.'},
    {q:'What does low stock out of stock mean', a:'📉 <b>Low Stock</b> means quantity has fallen at or below Par Level. <b>Out of Stock</b> means quantity is exactly 0.'},
    {q:'How to search filter items', a:'🔍 On the Inventory page, use the search box (name or location) plus the Category, Location, and Status dropdowns to filter the list.'},
    {q:'What is FIFO enforcement queue', a:'♻️ FIFO Queue (button on Inventory page) lists perishable items sorted by expiry date, oldest first, so staff use older stock before newer stock.'},

    // ===== Purchase Orders =====
    {q:'How to track a purchase order delivery status', a:'🚚 Go to <b>Purchase Orders</b> → click a PO → view the Delivery Status Tracker showing Order Placed, On The Way, and Delivered stages.'},
    {q:'How to mark a PO as delivered in transit', a:'📦 Open the PO detail page → click <b>Mark In Transit</b> or <b>Mark Delivered</b> depending on current status.'},
    {q:'Can I cancel a purchase order', a:'❌ Yes — a Procurement Officer or Inventory Manager can cancel a Purchase Order from its detail page before it&#x2019;s marked Delivered.'},

    // ===== Alerts =====
    {q:'What are the alert types critical warning info', a:'🚨 Alerts come in 3 severities: <b>Critical</b> (urgent, e.g. out of stock), <b>Warning</b> (approaching threshold), and <b>Info</b> (general notices).'},
    {q:'How to dismiss an alert', a:'✋ Go to <b>Alerts</b> → click <b>Dismiss</b> on the alert card to clear it without creating a Purchase Order.'},
    {q:'What happens when I approve an alert', a:'✅ Approving a Critical/Warning alert auto-generates a Purchase Order for the affected item, using its default supplier where available.'},

    // ===== Suppliers =====
    {q:'How to add a new supplier', a:'🏭 Go to <b>Suppliers</b> → click <b>Add New Supplier</b> → fill company info, contact details, and performance metrics → click <b>Save Supplier</b>.'},
    {q:'How is supplier rating calculated', a:'⭐ Supplier rating reflects delivery reliability and lead-time accuracy, editable manually on the supplier&#x2019;s profile.'},

    // ===== Locations =====
    {q:'How to add a new storage location', a:'📍 Go to <b>Locations</b> → click <b>Add Location</b> → enter name and capacity → click <b>Save</b>.'},
    {q:'How many storage locations are there', a:'🏨 7NVENT currently tracks 6 storage areas (e.g. Main Warehouse, Floor Pantries, Minibar Storage) — see the Locations page for live counts.'},

    // ===== Reports & Analytics =====
    {q:'What report formats are available PDF CSV', a:'📄 Every report on the Reports page can be exported as <b>PDF</b> (formatted document) or <b>CSV</b> (raw data for Excel).'},
    {q:'What charts are on the analytics page', a:'📈 Analytics shows Inventory Valuation Trend, Fast vs Slow-Moving Items, Supplier Delivery Performance, a Consumption Heatmap, and Supplier Ratings.'},

    // ===== Users & Roles =====
    {q:'How to deactivate a user account', a:'🚫 Go to <b>Users & Roles</b> → select the user → toggle their status to Inactive. Only IT Administrators can do this.'},
    {q:'What permissions does each role have', a:'🔐 Permissions are role-based: e.g. only Inventory Managers can delete items, only Procurement Officers/Inventory Managers approve alerts, Supervisors are view-only. See the roles list for full breakdown.'},

    // ===== QR Scanner =====
    {q:'How to generate print a QR code for an item', a:'🖨️ Go to <b>QR Scanner</b> → Generate tab → select the item → download or print the generated QR code to attach to physical stock.'},
    {q:'How to upload a QR code image instead of scanning', a:'📤 On the QR Scanner page, click <b>Upload Image</b> and choose a photo of the QR code — it will be decoded automatically.'},

    // ===== Settings =====
    {q:'How to switch between dark mode and light mode', a:'🌙 Click the sun/moon icon at the top right of any page to toggle Dark Mode / Light Mode. Your preference is saved automatically.'},
];

function toggleChat() {
    const panel = document.getElementById('chatPanel');
    const isOpen = panel.style.display === 'block';
    closeAllPanels2();
    if (!isOpen) { openPanel2(panel); renderQuickQs(); }
}

function renderQuickQs(start=0, count=6) {
    const c = document.getElementById('quickQs');
    const slice = qna.slice(start, start+count);
    c.innerHTML = slice.map((item,i) => `<button class="quick-q" onclick="askQ(${start+i})">${item.q}</button>`).join('');
    if (start+count < qna.length) c.innerHTML += `<button class="quick-q" style="color:#8b5cf6;border-color:#8b5cf6" onclick="renderQuickQs(${start+count})">More questions →</button>`;
    if (start > 0) c.innerHTML += `<button class="quick-q" style="color:var(--text-secondary)" onclick="renderQuickQs(0)">← Back</button>`;
}

function askQ(idx) {
    const item = qna[idx];
    addMsg(item.q,'user');
    setTimeout(() => addMsg(item.a,'bot'), 600);
}

function sendChat() {
    const inp = document.getElementById('chatInput');
    const text = inp.value.trim(); if (!text) return;
    addMsg(text,'user'); inp.value='';
    // Strip punctuation so "PO?" / "qr." etc still match cleanly
    const lower = text.toLowerCase().replace(/[.,!?;:'"]/g, '');
    let best=null, bestScore=0;
    const STOPWORDS = new Set(['the','and','for','are','you','how','what','can','with','this','that','from','have','has']);
    qna.forEach(item => {
        const qLower = item.q.toLowerCase();
        const words = lower.split(' ').filter(w => w.length>=2 && !STOPWORDS.has(w));
        let score = words.filter(w => qLower.includes(w)).length;
        if(score>bestScore){bestScore=score;best=item;}
    });
    setTimeout(() => addMsg(best&&bestScore>0 ? best.a : "🤔 I'm not sure about that one &#x2014; please select from the questions below, or contact your IT Administrator for anything beyond quick how-to help.", 'bot'), 700);
}

function addMsg(text, type) {
    const msgs = document.getElementById('chatMessages');
    const div  = document.createElement('div');
    div.className = type==='bot' ? 'chat-bot-msg' : 'chat-user-msg';
    div.innerHTML = text;
    msgs.appendChild(div);
    setTimeout(() => msgs.scrollTop = msgs.scrollHeight, 50);
}

function openPanel2(panel) {
    panel.style.display='block';
    requestAnimationFrame(()=>requestAnimationFrame(()=>{
        panel.style.opacity='1'; panel.style.transform='scale(1) translateY(0)';
    }));
}
function closePanel2(panel) {
    panel.style.opacity='0'; panel.style.transform='scale(0.85) translateY(-10px)';
    setTimeout(()=>panel.style.display='none',280);
}
function closeAllPanels2() {
    ['a11yPanel','langPanel','chatPanel'].forEach(id=>{
        const p=document.getElementById(id);
        if(p&&p.style.display==='block') closePanel2(p);
    });
}
document.addEventListener('click', e=>{
    const triggers=['a11yBtn','langBtn','chatBtn'];
    const panels=['a11yPanel','langPanel','chatPanel'];
    if(!triggers.some(id=>document.getElementById(id)?.contains(e.target)) &&
       !panels.some(id=>document.getElementById(id)?.contains(e.target)))
        closeAllPanels2();
});
</script>

<div id="logoutOverlay" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(10,15,30,0.55); backdrop-filter:blur(6px);
    align-items:center; justify-content:center;
">
    <div id="logoutModal" style="
        background:var(--glass-bg-strong); border-radius:20px; padding:36px 32px;
        max-width:380px; width:90%; text-align:center;
        position:relative; overflow:hidden; isolation:isolate;
        backdrop-filter:blur(24px) saturate(180%);
        -webkit-backdrop-filter:blur(24px) saturate(180%);
        border:1px solid var(--glass-border);
        box-shadow:0 1px 0 var(--glass-highlight) inset, 0 24px 60px var(--glass-shadow);
        transform:scale(0.7) translateY(30px); opacity:0;
        transition:all 0.35s cubic-bezier(0.34,1.56,0.64,1);
        will-change:transform,opacity;
    ">
        <!-- Icon with pulse ring -->
        <div style="position:relative;display:inline-block;margin-bottom:20px">
            <div style="
                width:72px; height:72px; border-radius:50%;
                background:linear-gradient(135deg,#fee2e2,#fecaca);
                display:flex; align-items:center; justify-content:center;
                font-size:30px; color:#dc2626; margin:0 auto; position:relative; z-index:1;
            "><i class="ph ph-door-open"></i></div>
            <div id="logoutRing" style="
                position:absolute; inset:-6px; border-radius:50%;
                border:3px solid rgba(239,68,68,0.4);
                animation:logoutRing 1.5s ease-in-out infinite;
            "></div>
        </div>

        <h5 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:8px">
            Sign Out of 7NVENT?
        </h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:28px;line-height:1.6">
            You are signed in as <strong><?= clean($currentUser['full_name'] ?? '') ?></strong>.<br>
            Any unsaved changes will be lost.
        </p>

        <div class="d-flex gap-3 justify-content-center">
            <button onclick="cancelLogout()"
                style="
                    flex:1; padding:12px 0; border-radius:12px;
                    border:2px solid #0096FF; background:rgba(0,150,255,0.10);
                    font-size:14px; font-weight:700; color:#0096FF;
                    cursor:pointer; transition:all 0.18s ease;
                "
                onmouseover="this.style.background='#0096FF';this.style.color='#fff'"
                onmouseout="this.style.background='rgba(0,150,255,0.10)';this.style.color='#0096FF'">
                ← Stay Here
            </button>
            <a href="<?= APP_URL ?>/logout" id="confirmLogout"
                style="
                    flex:1; padding:12px 0; border-radius:12px;
                    border:none; background:linear-gradient(135deg,#ef4444,#dc2626);
                    font-size:14px; font-weight:700; color:#fff;
                    cursor:pointer; text-decoration:none;
                    display:flex; align-items:center; justify-content:center; gap:6px;
                    box-shadow:0 4px 14px rgba(239,68,68,0.4);
                    transition:all 0.18s ease;
                "
                onmouseover="this.style.filter='brightness(1.12)';this.style.transform='translateY(-1px)'"
                onmouseout="this.style.filter='';this.style.transform=''">
                <i class="ph ph-sign-out"></i> Yes, Exit
            </a>
        </div>

        <!-- Session info -->
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-subtle);font-size:11px;color:var(--text-faint);display:flex;align-items:center;justify-content:center;gap:5px">
            <i class="ph ph-lock-key"></i> Session will be securely terminated
        </div>
    </div>
</div>

<style>
@keyframes logoutRing {
    0%,100%{transform:scale(1);opacity:0.4}
    50%{transform:scale(1.12);opacity:0.8}
}
#logoutOverlay.show { display:flex !important; }
#logoutModal.show {
    transform:scale(1) translateY(0) !important;
    opacity:1 !important;
}
</style>

<script>
function showLogoutModal(e) {
    e.preventDefault();
    const overlay = document.getElementById('logoutOverlay');
    const modal   = document.getElementById('logoutModal');
    overlay.style.display = 'flex';
    // Trigger bounce animation after paint
    requestAnimationFrame(() => requestAnimationFrame(() => {
        modal.style.transform = 'scale(1) translateY(0)';
        modal.style.opacity   = '1';
    }));
    // Close on overlay click
    overlay.onclick = function(ev) {
        if (ev.target === overlay) cancelLogout();
    };
    // Close on Escape
    document.addEventListener('keydown', escHandler);
}

function cancelLogout() {
    const overlay = document.getElementById('logoutOverlay');
    const modal   = document.getElementById('logoutModal');
    modal.style.transform = 'scale(0.85) translateY(20px)';
    modal.style.opacity   = '0';
    setTimeout(() => { overlay.style.display = 'none'; }, 300);
    document.removeEventListener('keydown', escHandler);
}

function escHandler(e) {
    if (e.key === 'Escape') cancelLogout();
}
</script>

<!-- ======= MAIN CONTENT ======= -->
<div id="main-content">
    <div class="top-header liquid-glass">
        <div class="page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div class="d-flex align-items-center gap-3">

            <!-- ===== Utility Toggle Group: Help / Language / Text Size ===== -->
            <div class="theme-toggle-group">
                <button type="button" id="chatBtn" class="theme-icon-btn" onclick="toggleChat()" title="7NVENT AI Assistant">
                    <i class="ph ph-robot"></i>
                </button>
                <button type="button" id="langBtn" class="theme-icon-btn" onclick="toggleLang()" title="Tukar Bahasa / Change Language" style="position:relative">
                    <i class="ph ph-translate"></i>
                    <span id="langFlag" style="position:absolute;bottom:-2px;right:-2px;font-size:10px;line-height:1">🇲🇾</span>
                </button>
                <button type="button" id="a11yBtn" class="theme-icon-btn" onclick="toggleA11y()" title="Text Size">
                    <i class="ph ph-text-aa"></i>
                </button>
            </div>

            <!-- ===== Theme & Eye Protection Toggle ===== -->
            <div class="theme-toggle-group">
                <button type="button" id="themeSunBtn" class="theme-icon-btn" onclick="setTheme('light')" title="Light Mode">
                    <i class="ph ph-sun"></i>
                </button>
                <button type="button" id="themeMoonBtn" class="theme-icon-btn" onclick="setTheme('dark')" title="Dark Mode">
                    <i class="ph ph-moon"></i>
                </button>
                <button type="button" id="eyeProtectBtn" class="theme-icon-btn" onclick="toggleEyeProtect()" title="Eye Protection Mode">
                    <i class="ph ph-eye"></i>
                </button>
            </div>

            <span class="text-muted small"><?= date('d M Y, H:i') ?></span>
            <div class="user-avatar" style="width:32px;height:32px;font-size:11px">
                <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 2)) ?>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="flash-alert alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <i class="ph ph-<?= $flash['type'] === 'success' ? 'check-circle' : 'warning-circle' ?> me-2"></i>
            <?= clean($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
        setTimeout(() => {
            const el = document.querySelector('.flash-alert');
            if (el) { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }
        }, 4000);
        </script>
    <?php endif; ?>

    <div class="content-area">
        <?= $content ?? '' ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extraJs)): ?>
<script><?= $extraJs ?></script>
<?php endif; ?>

<script>
// Auto-detect active sidebar item based on current URL
(function() {
    const path = window.location.pathname;
    const links = document.querySelectorAll('#sidebar a');
    let bestMatch = null;
    let bestLen = 0;

    links.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (!href || href.includes('logout')) return;
        try {
            const url = new URL(href, window.location.origin);
            const linkPath = url.pathname;
            if (linkPath !== '/' && path.startsWith(linkPath) && linkPath.length > bestLen) {
                bestLen = linkPath.length;
                bestMatch = link;
            }
        } catch(e) {}
    });

    if (bestMatch) bestMatch.classList.add('active');
})();
</script>
<!-- ======= THEME (LIGHT/DARK) & EYE PROTECTION ======= -->
<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('7nvent_theme', theme);
    updateThemeButtons(theme);
}

function updateThemeButtons(theme) {
    const sunBtn  = document.getElementById('themeSunBtn');
    const moonBtn = document.getElementById('themeMoonBtn');
    if (sunBtn)  sunBtn.classList.toggle('theme-active', theme === 'light');
    if (moonBtn) moonBtn.classList.toggle('theme-active', theme === 'dark');
}

function toggleEyeProtect() {
    const overlay = document.getElementById('eyeProtectOverlay');
    const turningOn = overlay.style.display !== 'block';
    overlay.style.display = turningOn ? 'block' : 'none';
    localStorage.setItem('7nvent_eyeprotect', turningOn ? '1' : '0');
    document.getElementById('eyeProtectBtn').classList.toggle('theme-active', turningOn);
}

window.addEventListener('load', function() {
    // Reflect saved theme on the toggle buttons (attribute itself already set in <head>)
    const savedTheme = localStorage.getItem('7nvent_theme') || 'light';
    updateThemeButtons(savedTheme);

    // Restore Eye Protection Mode state
    const eyeOn = localStorage.getItem('7nvent_eyeprotect') === '1';
    document.getElementById('eyeProtectOverlay').style.display = eyeOn ? 'block' : 'none';
    document.getElementById('eyeProtectBtn').classList.toggle('theme-active', eyeOn);
});
</script>

<!-- ======= LIQUID GLASS — pointer-tracked specular glint ======= -->
<script>
(function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const targets = document.querySelectorAll('.liquid-glass, .stat-card, .sup-kpi, .sup-card, .kpi-loc, .loc-card, .ur-kpi, .user-card, .an-card, .alert-card, .al-card, .al-kpi, #chatPanel');
    if (!targets.length) return;
    let raf = null, lastEvt = null;

    function apply() {
        raf = null;
        if (!lastEvt) return;
        const { el, x, y } = lastEvt;
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', ((x - r.left) / r.width * 100) + '%');
        el.style.setProperty('--my', ((y - r.top)  / r.height * 100) + '%');
    }

    targets.forEach(el => {
        el.addEventListener('pointermove', e => {
            lastEvt = { el, x: e.clientX, y: e.clientY };
            if (!raf) raf = requestAnimationFrame(apply);
        });
    });
})();
</script>

</body>
</html>