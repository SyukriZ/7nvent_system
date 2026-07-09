<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>7NVENT — Hotel Inventory Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <style>
        body { font-family: Arial, sans-serif; }

        /* ======= LIQUID GLASS — ambient background ======= */
        #glassAmbient { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        #glassAmbient .g-blob { position: absolute; border-radius: 50%; filter: blur(100px); opacity: .55; will-change: transform; }
        #glassAmbient .g-blob-1 { width: 42vw; height: 42vw; top: -12%; left: -10%; background: rgba(0,150,255,0.35); animation: glassDrift1 22s ease-in-out infinite; }
        #glassAmbient .g-blob-2 { width: 36vw; height: 36vw; top: 40%; right: -10%; background: rgba(255,215,0,0.28); animation: glassDrift2 26s ease-in-out infinite; }
        #glassAmbient .g-blob-3 { width: 30vw; height: 30vw; bottom: -10%; left: 30%; background: rgba(168,85,247,0.25); animation: glassDrift3 19s ease-in-out infinite; }
        @keyframes glassDrift1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(4%,6%) scale(1.12)} }
        @keyframes glassDrift2 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-6%,-4%) scale(1.08)} }
        @keyframes glassDrift3 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-4%,5%) scale(.94)} }

        /* ======= NAVBAR — liquid glass header ======= */
        .navbar {
            background: rgba(255,255,255,0.55) !important;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 8px 24px rgba(31,41,55,0.06);
        }
        .brand-logo { font-size: 22px; font-weight: 900; color: #1a1a2e; letter-spacing: -1px; }
        .brand-logo span { color: #0096FF; }
        .hero { background: linear-gradient(135deg, #f8faff 0%, #eef4ff 100%); min-height: 85vh; display: flex; align-items: center; }
        .vm2026-badge { background: #0096FF; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px; letter-spacing: 1px; display: inline-block; margin-bottom: 16px; }
        .hero-title { font-size: 48px; font-weight: 900; color: #1a1a2e; line-height: 1.05; }
        .btn-cta { background: #0096FF; border: none; color: #fff; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; }
        .btn-cta:hover { background: #0077dd; color: #fff; }
        .hotel-img { width: 100%; border-radius: 12px; border: 3px solid #0096FF; box-shadow: 0 10px 40px rgba(0,150,255,.2); }
        .feature-icon { width: 44px; height: 44px; background: #eef4ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

        /* ======= FEATURE CARDS — liquid glass ======= */
        .feature-card {
            position: relative; overflow: hidden; isolation: isolate;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 12px; padding: 24px;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s ease;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }
        .feature-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,0.55) 47%, transparent 64%);
            opacity: .6; transform: translateX(-130%);
            animation: glassSheen 8s ease-in-out infinite;
            pointer-events: none; mix-blend-mode: overlay; z-index: -1;
        }
        .feature-card:nth-child(2)::before { animation-delay: -2.5s; }
        .feature-card:nth-child(3)::before { animation-delay: -5s; }
        @keyframes glassSheen { 0%,35%{transform:translateX(-130%)} 65%,100%{transform:translateX(130%)} }
        .feature-card::after {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at var(--mx,50%) var(--my,50%), rgba(255,255,255,.8), transparent 42%);
            opacity: 0; transition: opacity .35s ease; pointer-events: none; z-index: -1;
        }
        .feature-card:hover::after { opacity: .7; }
        @media (prefers-reduced-motion: reduce) {
            .feature-card::before, #glassAmbient .g-blob { animation: none; }
        }

        footer { background: #1a1a2e; color: #888; }
    </style>
</head>
<body>

<!-- Liquid glass ambient background -->
<div id="glassAmbient" aria-hidden="true">
    <span class="g-blob g-blob-1"></span>
    <span class="g-blob g-blob-2"></span>
    <span class="g-blob g-blob-3"></span>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand brand-logo" href="<?= APP_URL ?>"><span>7</span>NVENT</a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="#features" class="nav-link">Features</a>
            <a href="#about" class="nav-link">About Us</a>
            <a href="#" class="nav-link">Contact Us</a>
            <a href="<?= APP_URL ?>/login" class="btn btn-cta btn-sm">Launch System</a>
            <i class="ph ph-globe text-muted"></i>
            <i class="ph ph-gear text-muted"></i>
            <i class="ph ph-user-circle text-muted"></i>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6" data-aos="fade-right" data-aos-duration="700">
                <div class="vm2026-badge">VISIT MALAYSIA 2026 READY</div>
                <h1 class="hero-title">HOTEL INVENTORY MANAGEMENT SYSTEM</h1>
                <p class="text-muted mt-3" style="font-size:15px">
                    7NVENT is a comprehensive web-based inventory management system for hotels that can automating stock tracking, reorder points, expiration alerts and supplier integration in one unified platform.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="<?= APP_URL ?>/login" class="btn btn-cta">Open Dashboard</a>
                    <a href="#features" class="btn btn-outline-dark px-4">View Features</a>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left" data-aos-duration="700" data-aos-delay="150">
                <div class="position-relative">
                    <!-- Hotel illustration using CSS -->
                    <div style="background:linear-gradient(180deg,#1a237e,#283593);border-radius:12px;border:3px solid #0096FF;box-shadow:0 10px 40px rgba(0,150,255,.2);height:280px;display:flex;align-items:center;justify-content:center">
                        <div class="text-white text-center">
                            <i class="ph ph-buildings" style="font-size:80px;opacity:.7"></i>
                            <div class="mt-2 fw-bold" style="font-size:18px;letter-spacing:2px">HOTEL 7NVENT</div>
                            <div style="font-size:12px;opacity:.7">Smart Inventory Management</div>
                            <div class="mt-3 d-flex justify-content-center gap-2">
                                <span class="badge bg-success">2,087 Items</span>
                                <span class="badge bg-warning text-dark">7 Alerts</span>
                                <span class="badge bg-info">6 Suppliers</span>
                            </div>
                        </div>
                    </div>
                    <!-- Dot indicators -->
                    <div class="text-center mt-2">
                        <span style="display:inline-block;width:8px;height:8px;background:#0096FF;border-radius:50%;margin:2px"></span>
                        <span style="display:inline-block;width:8px;height:8px;background:#ccc;border-radius:50%;margin:2px"></span>
                        <span style="display:inline-block;width:8px;height:8px;background:#ccc;border-radius:50%;margin:2px"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features" class="py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-2" data-aos="fade-up">Core Features</h2>
        <p class="text-center text-muted mb-5" data-aos="fade-up" data-aos-delay="100">Everything you need to manage hotel inventory efficiently</p>
        <div class="row g-4">
            <?php
            $features = [
                ['icon'=>'ph-gauge','title'=>'Real-Time Dashboard','desc'=>'Visual overview of total stock, consumption trends, and critical stock levels at a glance.'],
                ['icon'=>'ph-bell','title'=>'Automated Alert System','desc'=>'Instant notifications for low-stock items and upcoming expiry to ensure zero service downtime.'],
                ['icon'=>'ph-receipt','title'=>'Purchase Order Management','desc'=>'Streamlined PO generation with automated supplier integration and delivery tracking.'],
                ['icon'=>'ph-map-pin','title'=>'Location-Based Tracking','desc'=>'Manage inventory across Main Warehouse, Floor Pantries, Minibar Storage and more.'],
                ['icon'=>'ph-arrows-clockwise','title'=>'FIFO Enforcement','desc'=>'First-In-First-Out logic enforced automatically for perishable goods management.'],
                ['icon'=>'ph-shield-check','title'=>'PDPA Compliance','desc'=>'Data protection compliant with Malaysia PDPA 2010 with AES-256 encryption.'],
            ];
            foreach ($features as $i => $f):
                $delay = ($i % 3) * 100;
            ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <div class="feature-card">
                    <div class="feature-icon mb-3"><i class="ph <?= $f['icon'] ?> text-primary" style="font-size:20px"></i></div>
                    <div class="fw-bold mb-2"><?= $f['title'] ?></div>
                    <div class="text-muted small"><?= $f['desc'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-primary text-white text-center" data-aos="zoom-in" data-aos-duration="600">
    <div class="container">
        <h3 class="fw-bold mb-2">Sedia untuk Visit Malaysia 2026?</h3>
        <p class="mb-4 opacity-75">Upgrade hotel inventory management anda sekarang.</p>
        <a href="<?= APP_URL ?>/login" class="btn btn-light fw-bold px-5">Mulakan Sekarang</a>
    </div>
</section>

<!-- Footer -->
<footer class="py-4">
    <div class="container text-center" style="font-size:12px">
        <div class="brand-logo" style="color:#fff;font-size:18px;display:inline-block;margin-bottom:8px"><span style="color:#0096FF">7</span>NVENT</div>
        <div>CSC2854 Final Year Project &bull; Muhammad Syukri Bin Zainal Abidin (BCS2402-042) &bull; DCS 6B &bull; KPM Beranang</div>
        <div class="mt-1 opacity-50">Supervisor: Pn. Nini Aniza Binti Zakaria &bull; Session 1 2026/2027</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({ once: true, offset: 60 });
</script>

<!-- Liquid glass — pointer-tracked specular glint on feature cards -->
<script>
(function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    let raf = null, lastEvt = null;
    function apply() {
        raf = null;
        if (!lastEvt) return;
        const { el, x, y } = lastEvt;
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', ((x - r.left) / r.width * 100) + '%');
        el.style.setProperty('--my', ((y - r.top)  / r.height * 100) + '%');
    }
    document.querySelectorAll('.feature-card').forEach(el => {
        el.addEventListener('pointermove', e => {
            lastEvt = { el, x: e.clientX, y: e.clientY };
            if (!raf) raf = requestAnimationFrame(apply);
        });
    });
})();
</script>
</body>
</html>