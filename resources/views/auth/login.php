<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — 7NVENT Hotel Inventory System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height:100vh; font-family:'Arial',sans-serif; overflow:hidden; background:#0a0e1a; }

        /* ======= ANIMATED LIQUID-GLASS BACKGROUND ======= */
        /* Soft, blurred, organic color blobs — no shapes, no particles, no
           visible edges, in the spirit of Apple "liquid glass" / Linear.app
           style backgrounds. Six blobs, each animated purely via `transform`
           (translate3d + scale + slight rotate) so the browser composites
           everything on the GPU without ever touching layout — no canvas,
           no SVG, no per-frame JS. Every blob has its own duration (28-58s)
           and a negative animation-delay so none of them move in lockstep,
           plus a slow "breathing" opacity wash for a subtle sense of life
           even where no blob happens to be passing through.
           Kept dark (rather than switching to the bright variant on
           prefers-color-scheme: light) because the rest of this page's
           chrome — white text, dark glass panels — is built for a dark
           backdrop only; auto-switching just the background would break
           legibility rather than adapt to it. */
        .animated-background {
            position:fixed; inset:0; z-index:0; overflow:hidden;
            background:radial-gradient(120% 120% at 50% 0%, #07142b 0%, #020617 60%, #020617 100%);
        }
        .animated-background::after {
            content:''; position:absolute; inset:0; pointer-events:none;
            background:linear-gradient(180deg, rgba(255,255,255,0.06), transparent 45%);
            animation:bgBreathe 20s ease-in-out infinite;
        }
        @keyframes bgBreathe { 0%,100%{opacity:0.55;} 50%{opacity:1;} }

        .gradient-blob {
            position:absolute; border-radius:50%; filter:blur(110px);
            will-change:transform; mix-blend-mode:screen; pointer-events:none;
            transform:translate3d(0,0,0);
        }
        .blob-1 {
            width:60vw; height:60vw; top:-12%; left:-10%; opacity:0.65;
            background:radial-gradient(circle, #2563eb 0%, transparent 70%);
            animation:float1 42s cubic-bezier(0.45,0.05,0.55,0.95) infinite;
        }
        .blob-2 {
            width:55vw; height:55vw; top:-15%; right:-12%; opacity:0.6;
            background:radial-gradient(circle, #38bdf8 0%, transparent 70%);
            animation:float2 50s cubic-bezier(0.45,0.05,0.55,0.95) infinite; animation-delay:-12s;
        }
        .blob-3 {
            width:72vw; height:72vw; top:18%; left:16%; opacity:0.4; filter:blur(140px);
            background:radial-gradient(circle, #1e3a8a 0%, transparent 72%);
            animation:float3 58s cubic-bezier(0.45,0.05,0.55,0.95) infinite; animation-delay:-25s;
        }
        .blob-4 {
            width:50vw; height:50vw; bottom:-16%; left:-6%; opacity:0.55;
            background:radial-gradient(circle, #0096FF 0%, transparent 70%);
            animation:float4 36s cubic-bezier(0.45,0.05,0.55,0.95) infinite; animation-delay:-6s;
        }
        .blob-5 {
            width:48vw; height:48vw; bottom:-12%; right:-8%; opacity:0.5;
            background:radial-gradient(circle, #0ea5e9 0%, transparent 70%);
            animation:float5 46s cubic-bezier(0.45,0.05,0.55,0.95) infinite; animation-delay:-18s;
        }
        .blob-6 {
            width:30vw; height:30vw; top:32%; right:22%; opacity:0.35; filter:blur(80px);
            background:radial-gradient(circle, rgba(255,255,255,0.14) 0%, transparent 70%);
            animation:float6 28s cubic-bezier(0.45,0.05,0.55,0.95) infinite; animation-delay:-9s;
        }

        /* Every keyframe returns EXACTLY to its 0% transform at 100% so each
           loop is seamless — no jump when the animation restarts. */
        @keyframes float1 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg);} 25%{transform:translate3d(6%,4%,0) scale(1.10) rotate(6deg);} 50%{transform:translate3d(2%,10%,0) scale(0.95) rotate(-4deg);} 75%{transform:translate3d(-4%,5%,0) scale(1.05) rotate(3deg);} }
        @keyframes float2 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg);} 25%{transform:translate3d(-5%,6%,0) scale(0.92) rotate(-8deg);} 50%{transform:translate3d(-9%,2%,0) scale(1.08) rotate(5deg);} 75%{transform:translate3d(-3%,-5%,0) scale(1.0) rotate(-3deg);} }
        @keyframes float3 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg);} 33%{transform:translate3d(5%,-6%,0) scale(1.12) rotate(7deg);} 66%{transform:translate3d(-6%,4%,0) scale(0.90) rotate(-5deg);} }
        @keyframes float4 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg);} 25%{transform:translate3d(4%,-5%,0) scale(1.06) rotate(4deg);} 50%{transform:translate3d(8%,-2%,0) scale(0.94) rotate(-6deg);} 75%{transform:translate3d(3%,4%,0) scale(1.03) rotate(2deg);} }
        @keyframes float5 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg);} 25%{transform:translate3d(-6%,-4%,0) scale(0.93) rotate(-7deg);} 50%{transform:translate3d(-2%,-9%,0) scale(1.10) rotate(5deg);} 75%{transform:translate3d(4%,-3%,0) scale(1.0) rotate(-2deg);} }
        @keyframes float6 { 0%,100%{transform:translate3d(0,0,0) scale(1) rotate(0deg); opacity:0.35;} 50%{transform:translate3d(3%,-6%,0) scale(1.3) rotate(10deg); opacity:0.55;} }

        @media (max-width:640px) {
            .gradient-blob { filter:blur(60px); }
            .blob-3 { filter:blur(80px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .gradient-blob, .animated-background::after { animation:none; }
        }

        /* ======= PAGE WRAP ======= */
        .page-wrap { position:relative;z-index:10;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px; }

        /* ======= MAIN CONTAINER ======= */
        .main-container {
            display:flex; width:920px; min-height:590px;
            border-radius:28px; overflow:hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.08);
            animation: popIn 0.7s cubic-bezier(0.23,1,0.32,1) forwards;
            position: relative;
        }
        @keyframes popIn { from{opacity:0;transform:scale(0.92) translateY(24px)} to{opacity:1;transform:scale(1) translateY(0)} }

        /* ======= LEFT PANEL — LIQUID GLASS ======= */
        .left-panel {
            width:380px; flex-shrink:0; position:relative; overflow:hidden;
            background: linear-gradient(160deg, rgba(26,26,46,0.90) 0%, rgba(15,32,70,0.90) 45%, rgba(0,60,120,0.80) 100%);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-right: 1px solid rgba(255,255,255,0.15);
            display:flex; flex-direction:column; align-items:center; justify-content:center; padding:36px 28px;
        }

        /* Left panel glow border */
        .left-panel::before {
            content:''; position:absolute; inset:0;
            background: linear-gradient(180deg,rgba(255,255,255,0.12) 0%,rgba(255,255,255,0.03) 50%,rgba(255,255,255,0.08) 100%);
            pointer-events:none;
        }

        /* Decorative rings */
        .ring {
            position:absolute; border-radius:50%;
            border:1px solid rgba(255,255,255,0.1);
        }
        .ring1 { width:320px;height:320px;top:-60px;right:-80px; }
        .ring2 { width:220px;height:220px;bottom:-40px;left:-50px; }
        .ring3 { width:120px;height:120px;bottom:80px;right:20px;background:rgba(255,255,255,0.04); animation:ringPulse 3s ease-in-out infinite; }
        @keyframes ringPulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.1);opacity:0.6} }

        /* Brand mark — professional badge with the same blue trademark
           glow used on the in-app sidebar logo, instead of a hotel emoji. */
        .hotel-art { position:relative;z-index:2;text-align:center;margin-bottom:18px; }
        .brand-badge {
            width:64px; height:64px; margin:0 auto; border-radius:18px;
            display:flex; align-items:center; justify-content:center;
            background:rgba(0,150,255,0.12);
            border:1px solid rgba(0,150,255,0.35);
            box-shadow:0 0 24px rgba(0,150,255,0.35), inset 0 0 16px rgba(0,150,255,0.12);
            animation:badgePulse 3s ease-in-out infinite;
        }
        .brand-badge i { font-size:30px; color:#4fc3ff; text-shadow:0 0 14px rgba(0,170,255,0.9); }
        @keyframes badgePulse {
            0%,100% { box-shadow:0 0 24px rgba(0,150,255,0.35), inset 0 0 16px rgba(0,150,255,0.12); }
            50%     { box-shadow:0 0 38px rgba(0,170,255,0.55), inset 0 0 22px rgba(0,150,255,0.20); }
        }

        /* Brand — same trademark blue-glow treatment as the in-app sidebar logo */
        .panel-logo {
            font-size:38px; font-weight:900; color:#fff; letter-spacing:-2px; line-height:1;
            position:relative; z-index:2; display:inline-block;
            animation: loginLogoGlow 3s ease-in-out infinite;
        }
        .panel-logo span { color:#0096FF; display:inline-block; animation: loginSpanGlow 3s ease-in-out infinite; }
        @keyframes loginLogoGlow {
            0%,100% { text-shadow:0 0 8px rgba(0,150,255,.55), 0 0 22px rgba(0,150,255,.30), 0 0 48px rgba(0,100,255,.14); filter:brightness(1); }
            50%     { text-shadow:0 0 14px rgba(0,170,255,1.0), 0 0 34px rgba(0,150,255,.75), 0 0 68px rgba(0,100,255,.30); filter:brightness(1.15); }
        }
        @keyframes loginSpanGlow {
            0%,100% { text-shadow:0 0 10px rgba(0,150,255,.9), 0 0 26px rgba(0,180,255,.5), 0 0 52px rgba(0,150,255,.25); color:#0096FF; }
            50%     { text-shadow:0 0 16px rgba(0,200,255,1.0), 0 0 38px rgba(0,170,255,.80), 0 0 80px rgba(0,130,255,.40); color:#55ccff; }
        }
        .panel-tagline { font-size:9px;color:rgba(255,255,255,0.55);letter-spacing:3px;text-transform:uppercase;margin-top:6px;position:relative;z-index:2; }
        .panel-stars { color:#4fc3ff;font-size:13px;letter-spacing:5px;margin:12px 0 8px;text-shadow:0 0 12px rgba(0,170,255,0.6);position:relative;z-index:2; }
        .panel-slogan { font-size:11px;color:rgba(255,255,255,0.6);line-height:1.6;max-width:230px;margin:0 auto;text-align:center;position:relative;z-index:2; }

        /* Mini stat cards */
        .mini-stats { display:flex;gap:8px;margin-top:20px;position:relative;z-index:2;width:100%; }
        .mini-stat {
            flex:1; text-align:center;
            background:rgba(255,255,255,0.1);
            backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.18);
            border-radius:14px; padding:10px 6px;
            transition: all 0.3s;
        }
        .mini-stat:hover { background:rgba(255,255,255,0.18); transform:translateY(-2px); }
        .mini-stat .val { font-size:20px;font-weight:800;color:#fff;line-height:1; }
        .mini-stat .lbl { font-size:9px;color:rgba(255,255,255,0.5);margin-top:2px;letter-spacing:0.3px; }

        /* Inventory floating cards */
        .inv-cards { display:flex;flex-direction:column;gap:6px;margin-top:14px;width:100%;position:relative;z-index:2; }
        .inv-card {
            display:flex;align-items:center;gap:10px;
            background:rgba(255,255,255,0.08);
            border:1px solid rgba(255,255,255,0.12);
            border-radius:10px; padding:8px 12px;
            animation:slideInCard 0.6s ease forwards;
            opacity:0;
        }
        .inv-card:nth-child(1){animation-delay:0.8s;}
        .inv-card:nth-child(2){animation-delay:1.0s;}
        .inv-card:nth-child(3){animation-delay:1.2s;}
        @keyframes slideInCard { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }
        .inv-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
        .inv-label { font-size:10px;color:rgba(255,255,255,0.65);flex:1; }
        .inv-val { font-size:11px;font-weight:700;color:#fff; }

        /* ======= RIGHT PANEL — LIQUID GLASS ======= */
        /* Was rgba(255,255,255,0.10) — nearly see-through. Fine against the
           old soft bubble background, but the ASCII field is much busier and
           bled straight through onto the form. Dark, mostly-opaque glass
           keeps the login form legible no matter what's animating behind it. */
        .right-panel {
            flex:1; position:relative;
            background:rgba(6,9,17,0.97);
            backdrop-filter:blur(20px) saturate(140%);
            -webkit-backdrop-filter:blur(20px) saturate(140%);
            border-left:1px solid rgba(255,255,255,0.12);
            display:flex; flex-direction:column; justify-content:center;
            padding:44px 40px;
        }
        .right-panel::before {
            content:''; position:absolute; inset:0;
            background:linear-gradient(135deg,rgba(255,255,255,0.08) 0%,rgba(255,255,255,0.03) 100%);
            pointer-events:none; border-radius:0 28px 28px 0;
        }

        /* ======= ANIMATED SPECULAR SHEEN — moving light catch on the glass ======= */
        .left-panel::after,
        .right-panel::after {
            content:''; position:absolute; inset:0;
            background:linear-gradient(115deg, transparent 30%, rgba(255,255,255,0.22) 47%, transparent 64%);
            transform:translateX(-130%);
            animation:glassSheen 9s ease-in-out infinite;
            pointer-events:none; mix-blend-mode:overlay;
        }
        .right-panel::after { animation-delay:-3s; }
        @keyframes glassSheen {
            0%,35%  { transform:translateX(-130%); }
            65%,100%{ transform:translateX(130%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .left-panel::after, .right-panel::after { animation:none; }
        }

        /* ======= CARD HOVER GLOW ======= */
        .card-glow {
            position:absolute; inset:-2px; border-radius:30px;
            background:conic-gradient(from 0deg, #0096FF, #38bdf8, #2563eb, #0ea5e9, #0096FF);
            opacity:0; z-index:-1; filter:blur(8px);
            transition:opacity 0.3s;
            animation:rotateBorder 4s linear infinite;
        }
        @keyframes rotateBorder { 0%{filter:blur(8px) hue-rotate(0deg)} 100%{filter:blur(8px) hue-rotate(360deg)} }
        .main-container:hover .card-glow { opacity:0.6; }

        /* ======= FORM TEXT COLORS (glass adaptation) ======= */
        /* margin-top clears the absolutely-positioned Demo Accounts pill
           (.quick-access, top:16px) which was overlapping this text. */
        .welcome-txt { font-size:11px;font-weight:700;color:rgba(79,195,255,0.95);letter-spacing:2px;text-transform:uppercase;margin-bottom:4px;margin-top:22px;position:relative;z-index:2; }
        .title-txt { font-size:26px;font-weight:800;color:#fff;margin-bottom:4px;position:relative;z-index:2; }
        .sub-txt { font-size:13px;color:rgba(255,255,255,0.55);margin-bottom:26px;position:relative;z-index:2; }

        .form-label-custom { font-size:10px;font-weight:700;color:rgba(255,255,255,0.6);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;display:block;position:relative;z-index:2; }

        /* Glass inputs */
        .input-group .form-control,
        .input-group .input-group-text {
            background:rgba(255,255,255,0.10) !important;
            border:1.5px solid rgba(255,255,255,0.18) !important;
            color:#fff !important;
            font-size:14px;
            transition:all 0.25s;
        }
        .input-group .form-control::placeholder { color:rgba(255,255,255,0.3); }
        .input-group .form-control:focus {
            background:rgba(255,255,255,0.15) !important;
            border-color:rgba(0,150,255,0.8) !important;
            box-shadow:0 0 0 3px rgba(0,150,255,0.25), 0 0 20px rgba(0,150,255,0.2) !important;
            color:#fff !important;
        }
        .input-group > :first-child { border-radius:10px 0 0 10px !important; }
        .input-group > :last-child  { border-radius:0 10px 10px 0 !important; }
        .input-group > .form-control:not(:first-child):not(:last-child) { border-radius:0 !important; }
        .input-group-text { color:rgba(255,255,255,0.4) !important; cursor:pointer; }
        .form-check-label { color:rgba(255,255,255,0.55);font-size:12px; }
        .form-check-input { background-color:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.3); }

        /* ======= LOGIN BUTTON ======= */
        .btn-login {
            width:100%; border:none; border-radius:12px; padding:14px;
            color:#fff; font-weight:700; font-size:15px; letter-spacing:0.5px;
            background:linear-gradient(135deg, #0096FF, #2563eb, #0ea5e9);
            background-size:200% 200%; animation:btnShift 3s ease infinite;
            box-shadow:0 4px 20px rgba(0,150,255,0.5);
            transition:all 0.3s; position:relative; z-index:2;
        }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,150,255,0.7), 0 0 20px rgba(14,165,233,0.4); color:#fff; }
        @keyframes btnShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }

        /* ======= DIVIDER ======= */
        .or-divider { display:flex;align-items:center;gap:12px;margin:18px 0;color:rgba(255,255,255,0.3);font-size:11px;position:relative;z-index:2; }
        .or-divider::before,.or-divider::after { content:'';flex:1;height:1px;background:rgba(255,255,255,0.12); }

        /* ======= SOCIAL BUTTONS ======= */
        .social-btn {
            width:46px;height:46px;border-radius:13px;
            display:flex;align-items:center;justify-content:center;
            text-decoration:none;font-size:19px;
            transition:all 0.25s;position:relative;z-index:2;
            background:rgba(255,255,255,0.10);
            border:1.5px solid rgba(255,255,255,0.15);
            color:rgba(255,255,255,0.7);
        }
        .social-btn:hover { transform:translateY(-4px); color:#fff; }
        .social-btn.ig:hover { background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); border-color:transparent; box-shadow:0 6px 20px rgba(220,39,67,0.4); }
        .social-btn.fb:hover { background:#1877f2; border-color:transparent; box-shadow:0 6px 20px rgba(24,119,242,0.4); }
        .social-btn.li:hover { background:#0a66c2; border-color:transparent; box-shadow:0 6px 20px rgba(10,102,194,0.4); }
        .social-btn.gh:hover { background:#181717; border-color:transparent; box-shadow:0 6px 20px rgba(0,150,255,0.4); }

        /* ======= ALERT ======= */
        .alert-custom { border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;position:relative;z-index:2; }
        .alert-err { background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#fca5a5; }
        .alert-ok  { background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#86efac; }

        /* ======= DEMO ACCOUNTS DROPDOWN ======= */
        .quick-access { position:absolute;top:16px;right:16px;z-index:20; }
        .cred-toggle {
            background:rgba(255,255,255,0.12);
            border:1px solid rgba(255,255,255,0.2);
            backdrop-filter:blur(10px);
            border-radius:20px;padding:6px 14px;
            font-size:11px;color:rgba(255,255,255,0.8);
            cursor:pointer;transition:all 0.2s;
            display:flex;align-items:center;gap:5px;
        }
        .cred-toggle:hover { background:rgba(255,255,255,0.2); }
        .cred-dropdown {
            position:absolute;right:0;top:38px;
            background:rgba(10,20,40,0.90);
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.15);
            border-radius:16px;padding:14px;
            width:310px;box-shadow:0 16px 40px rgba(0,0,0,0.4);
            display:none;
        }
        .cred-dropdown.show { display:block; }
        .cred-title { font-size:10px;font-weight:700;color:rgba(255,255,255,0.5);margin-bottom:10px;text-transform:uppercase;letter-spacing:1px; }
        .cd-row { display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:9px;cursor:pointer;transition:all 0.15s; }
        .cd-row:hover { background:rgba(255,255,255,0.1);transform:translateX(3px); }
        .cd-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
        .cd-name { font-size:11px;font-weight:600;color:rgba(255,255,255,0.8);min-width:135px; }
        .cd-pw { font-size:10px;color:#4fc3ff;font-family:monospace; }

        /* ======= FOOTER ======= */
        .footer-note { font-size:10px;color:rgba(255,255,255,0.25);text-align:center;margin-top:14px;position:relative;z-index:2; }
    </style>
</head>
<body>

<div class="animated-background" aria-hidden="true">
    <div class="gradient-blob blob-1"></div>
    <div class="gradient-blob blob-2"></div>
    <div class="gradient-blob blob-3"></div>
    <div class="gradient-blob blob-4"></div>
    <div class="gradient-blob blob-5"></div>
    <div class="gradient-blob blob-6"></div>
</div>

<div class="page-wrap">
<div class="main-container" id="mainCard">
    <div class="card-glow"></div>

    <!-- ======= LEFT PANEL ======= -->
    <div class="left-panel">
        <div class="ring ring1"></div>
        <div class="ring ring2"></div>
        <div class="ring ring3"></div>

        <div class="hotel-art">
            <div class="brand-badge"><i class="bi bi-buildings"></i></div>
        </div>

        <div style="text-align:center;position:relative;z-index:2">
            <div class="panel-logo"><span>7</span>NVENT</div>
            <div class="panel-tagline">Hotel Inventory Management System</div>
            <div class="panel-stars">★ ★ ★ ★ ★</div>
            <div class="panel-slogan">Precision in every detail.<br>Excellence in every stay.</div>
        </div>

        <!-- Mini stats -->
        <div class="mini-stats">
            <div class="mini-stat">
                <div class="val">6</div>
                <div class="lbl">Roles</div>
            </div>
            <div class="mini-stat">
                <div class="val">10</div>
                <div class="lbl">Modules</div>
            </div>
            <div class="mini-stat">
                <div class="val">24/7</div>
                <div class="lbl">Live</div>
            </div>
        </div>

        <!-- Live inventory cards -->
        <div class="inv-cards">
            <div class="inv-card">
                <div class="inv-dot" style="background:#4ade80;box-shadow:0 0 6px #4ade80"></div>
                <div class="inv-label">Total Items in Stock</div>
                <div class="inv-val" id="statItems" data-target="<?= $liveStats['total_items'] ?>">0</div>
            </div>
            <div class="inv-card">
                <div class="inv-dot" style="background:#f87171;box-shadow:0 0 6px #f87171"></div>
                <div class="inv-label">Critical Alerts</div>
                <div class="inv-val" id="statAlerts" data-target="<?= $liveStats['critical_alerts'] ?>">0</div>
            </div>
            <div class="inv-card">
                <div class="inv-dot" style="background:#fbbf24;box-shadow:0 0 6px #fbbf24"></div>
                <div class="inv-label">Pending Orders</div>
                <div class="inv-val" id="statOrders" data-target="<?= round($liveStats['pending_value']) ?>">RM 0</div>
            </div>
        </div>
    </div>

    <!-- ======= RIGHT PANEL ======= -->
    <div class="right-panel">

        <!-- Demo accounts dropdown -->
        <div class="quick-access">
            <div class="cred-toggle" onclick="toggleCreds()">
                <i class="bi bi-people"></i> Demo Accounts
                <i class="bi bi-chevron-down" id="chevron"></i>
            </div>
            <div class="cred-dropdown" id="credDropdown">
                <div class="cred-title">🔑 Click any role to auto-fill</div>
                <?php
                $roles = [
                    ['color'=>'#818cf8','name'=>'Inventory Manager',   'user'=>'elizabeth.lee','pass'=>'Admin@7nvent'],
                    ['color'=>'#fbbf24','name'=>'Housekeeping Manager','user'=>'alvin.yuan',   'pass'=>'House@7nvent'],
                    ['color'=>'#a78bfa','name'=>'Procurement Officer', 'user'=>'sarah.qinn',   'pass'=>'PO@7nvent123'],
                    ['color'=>'#f87171','name'=>'IT Administrator',    'user'=>'abdul.hakim',  'pass'=>'ITadmin@7nvent'],
                    ['color'=>'#4ade80','name'=>'Hotel GM',            'user'=>'farah.nabilah','pass'=>'GM@7nvent2026'],
                    ['color'=>'#fb923c','name'=>'Supervisor',          'user'=>'melissa.yee',  'pass'=>'Super@7nvent'],
                ];
                foreach ($roles as $r): ?>
                <div class="cd-row" onclick="fillCreds('<?= $r['user'] ?>','<?= $r['pass'] ?>')">
                    <div class="cd-dot" style="background:<?= $r['color'] ?>;box-shadow:0 0 6px <?= $r['color'] ?>"></div>
                    <div class="cd-name"><?= $r['name'] ?></div>
                    <div class="cd-pw"><?= $r['pass'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="welcome-txt">Welcome Back 👋</div>
        <div class="title-txt">Sign In to 7NVENT</div>
        <div class="sub-txt">Manage your hotel inventory with ease and precision.</div>

        <?php if ($flash = flash()): ?>
        <div class="alert-custom <?= $flash['type']==='success'?'alert-ok':'alert-err' ?>">
            <i class="bi bi-<?= $flash['type']==='success'?'check-circle-fill':'exclamation-triangle-fill' ?> mt-1 flex-shrink-0"></i>
            <div>
                <?= htmlspecialchars($flash['message']) ?>
                <?php if (isset($_SESSION['attempts_remaining']) && $_SESSION['attempts_remaining'] > 0): ?>
                    <br><strong><?= $_SESSION['attempts_remaining'] ?> attempt(s) remaining before lockout.</strong>
                    <?php unset($_SESSION['attempts_remaining']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['lockout_remaining'])): ?>
                    <br><strong>⏱ Account locked. Try again in <?= $_SESSION['lockout_remaining'] ?> minute(s).</strong>
                    <?php unset($_SESSION['lockout_remaining']); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/login" style="position:relative;z-index:2">
            <div class="mb-3">
                <label class="form-label-custom">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="usernameField" class="form-control"
                           placeholder="Enter your username" required autofocus autocomplete="username">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label-custom">Password</label>
                <div class="input-group" id="pwGroup">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="passwordField" class="form-control"
                           placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="input-group-text" onclick="togglePass()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4" style="position:relative;z-index:2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <small style="font-size:11px;color:rgba(255,255,255,0.35)">
                    <i class="bi bi-shield-lock me-1"></i>Secure Login
                </small>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="or-divider">Connect with Developer</div>
        <div class="d-flex justify-content-center gap-3">
            <a href="https://www.instagram.com/jooseon_987" target="_blank" class="social-btn ig" title="@jooseon_987">
                <i class="bi bi-instagram"></i>
            </a>
            <a href="https://www.facebook.com/share/17jJYz8xq6/?mibextid=wwXIfr" target="_blank" class="social-btn fb" title="Syuk Deong">
                <i class="bi bi-facebook"></i>
            </a>
            <a href="https://www.linkedin.com/in/syukri-zainal-5589142ab" target="_blank" class="social-btn li" title="Syukri Zainal">
                <i class="bi bi-linkedin"></i>
            </a>
            <a href="https://github.com/SyukriZ" target="_blank" class="social-btn gh" title="SyukriZ on GitHub">
                <i class="bi bi-github"></i>
            </a>
        </div>

        <div class="footer-note">
            <i class="bi bi-clock me-1"></i>Session expires after 30 minutes
            &nbsp;·&nbsp;
            <i class="bi bi-shield-check me-1"></i>PDPA Compliant
        </div>
    </div>

</div>
</div>

<script>
// Background is now the pure-CSS .animated-background liquid-glass layer
// (see <style> above) — no canvas render loop needed. Everything below
// only touches
// #mainCard.

// ======= CARD PROXIMITY GLOW =======
document.addEventListener('mousemove', e => {
    const card = document.getElementById('mainCard');
    const rect  = card.getBoundingClientRect();
    const cx    = rect.left + rect.width/2;
    const cy    = rect.top  + rect.height/2;
    const dist  = Math.sqrt((e.clientX-cx)**2 + (e.clientY-cy)**2);
    const maxD  = 500;
    const intensity = Math.max(0, 1 - dist/maxD);
    card.querySelector('.card-glow').style.opacity = intensity * 0.7;
});

// ======= 3D TILT =======
const card = document.getElementById('mainCard');
card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const dx = (e.clientX - rect.left - rect.width/2)  / (rect.width/2);
    const dy = (e.clientY - rect.top  - rect.height/2) / (rect.height/2);
    card.style.transform = `perspective(1200px) rotateX(${dy*-6}deg) rotateY(${dx*6}deg) scale(1.015)`;
    card.style.transition = 'transform 0.1s';
});
card.addEventListener('mouseleave', () => {
    card.style.transform = 'perspective(1200px) rotateX(0) rotateY(0) scale(1)';
    card.style.transition = 'transform 0.5s ease';
});

// ======= FORM HELPERS =======
function fillCreds(u,p){
    document.getElementById('usernameField').value=u;
    document.getElementById('passwordField').value=p;
    document.getElementById('credDropdown').classList.remove('show');
    document.getElementById('usernameField').focus();
}
function toggleCreds(){
    const d=document.getElementById('credDropdown');
    const c=document.getElementById('chevron');
    d.classList.toggle('show');
    c.className=d.classList.contains('show')?'bi bi-chevron-up':'bi bi-chevron-down';
}
function togglePass(){
    const f=document.getElementById('passwordField');
    const i=document.getElementById('eyeIcon');
    if(f.type==='password'){f.type='text';i.className='bi bi-eye-slash';}
    else{f.type='password';i.className='bi bi-eye';}
}
document.addEventListener('click',e=>{
    if(!e.target.closest('.quick-access')){
        document.getElementById('credDropdown').classList.remove('show');
        document.getElementById('chevron').className='bi bi-chevron-down';
    }
});

// Live stats counter animation
window.addEventListener('load', function() {
    function countUp(el, target, dur, fmt) {
        if (!el) return;
        const start = performance.now();
        (function tick(now) {
            const p = Math.min((now - start) / dur, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            const val = Math.floor(ease * target);
            if (fmt === 'rm')       el.textContent = 'RM ' + val.toLocaleString();
            else if (fmt === 'alert') el.textContent = val + (val === 1 ? ' Active' : ' Active');
            else                    el.textContent = val.toLocaleString();
            if (p < 1) requestAnimationFrame(tick);
            else {
                if (fmt === 'rm')        el.textContent = 'RM ' + target.toLocaleString();
                else if (fmt === 'alert') el.textContent = target + ' Active';
                else                     el.textContent = target.toLocaleString();
            }
        })(start);
    }

    const items   = document.getElementById('statItems');
    const alerts  = document.getElementById('statAlerts');
    const orders  = document.getElementById('statOrders');

    if (items)  countUp(items,  parseInt(items.dataset.target)  || 0, 1400, 'num');
    if (alerts) countUp(alerts, parseInt(alerts.dataset.target) || 0, 900,  'alert');
    if (orders) countUp(orders, parseInt(orders.dataset.target) || 0, 1600, 'rm');
});
</script>
</body>
</html>