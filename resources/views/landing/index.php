<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>7NVENT — Hotel Inventory Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/hospitality.css">
</head>
<body class="hospitality-public">
<header class="public-nav">
    <nav class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-3" aria-label="Main navigation">
        <a class="brand-wordmark fs-4" href="<?= APP_URL ?>"><span>7</span>NVENT</a>
        <div class="d-flex align-items-center gap-3">
            <a class="link-dark link-offset-3" href="#features">Features</a>
            <a class="btn btn-hospitality px-3" href="<?= APP_URL ?>/login">Sign in</a>
        </div>
    </nav>
</header>
<main>
    <section class="public-hero container">
        <div class="row align-items-center g-4 g-xl-5">
            <div class="col-lg-6">
                <p class="public-kicker">Hotel inventory management</p>
                <h1>Stockroom to guest room.</h1>
                <p class="public-copy fs-5 mt-4 mb-4">Track stock across your hotel, spot shortages before a shift, and manage supplier orders from one workspace.</p>
                <a href="<?= APP_URL ?>/login" class="btn btn-hospitality px-4 py-2">Open 7NVENT <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></a>
            </div>
            <div class="col-lg-6">
                <div class="public-visual">
                    <p>For the teams who keep every floor stocked and ready.</p>
                </div>
            </div>
        </div>
    </section>
    <section id="features" class="public-features py-5">
        <div class="container">
            <h2 class="public-heading mb-4">Manage the daily essentials</h2>
            <div class="row g-4">
                <?php
                $features = [
                    ['speedometer2', 'Operations dashboard', 'Review stock counts, consumption, pending order value, and recent activity.'],
                    ['bell', 'Stock and expiry alerts', 'See items that need attention before they run out or expire.'],
                    ['receipt', 'Purchase orders', 'Create orders and follow their progress through delivery.'],
                    ['geo-alt', 'Stock by location', 'Find supplies across warehouses, floor pantries, and minibar storage.'],
                    ['arrow-repeat', 'FIFO queue', 'Review perishable stock and prioritize the items due to expire first.'],
                    ['people', 'Team access', 'Assign roles to control the operations each team member can perform.'],
                ];
                foreach ($features as [$icon, $title, $description]): ?>
                    <div class="col-sm-6 col-lg-4">
                        <article class="public-feature">
                            <i class="bi bi-<?= $icon ?> d-block mb-2" aria-hidden="true"></i>
                            <h3><?= $title ?></h3>
                            <p><?= $description ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<footer class="public-footer py-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="brand-wordmark mb-2"><span>7</span>NVENT</div>
            <div class="small">CSC2854 Final Year Project · Muhammad Syukri Bin Zainal Abidin · KPM Beranang</div>
        </div>
        <a href="<?= APP_URL ?>/login">Sign in to your workspace <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
    </div>
</footer>
</body>
</html>
