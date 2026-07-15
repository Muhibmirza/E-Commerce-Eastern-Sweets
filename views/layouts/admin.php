<?php $flashes = flash(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= h($title ?? 'Admin') ?> - Eastern Sweets</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('public/assets/css/style.css') ?>">
    <?= app_theme_style() ?>
</head>
<body class="admin-body">
<aside class="admin-sidebar">
    <a class="admin-brand" href="<?= url('admin/dashboard') ?>"><img src="<?= asset(app_setting('site_logo', 'public/assets/images/logo.png')) ?>" alt="Eastern Sweets"></a>
    <nav class="admin-nav">
        <?php foreach (['dashboard'=>'Dashboard','products'=>'Products','categories'=>'Categories','orders'=>'Orders','customers'=>'Customers','payments'=>'Payments','coupons'=>'Coupons','reports'=>'Reports','banners'=>'Banners','content'=>'Content','settings'=>'Settings','admins'=>'Admins'] as $key => $label): ?>
            <a class="<?= active_route('admin/' . $key) ?>" href="<?= url('admin/' . $key) ?>"><span><?= h(substr($label, 0, 1)) ?></span><?= h($label) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
<div class="admin-frame">
    <header class="admin-topbar">
        <div>
            <p class="eyebrow">Eastern Sweets Admin</p>
            <h1><?= h($title ?? 'Dashboard') ?></h1>
        </div>
        <div class="admin-user">
            <span><?= h(current_admin()['name'] ?? 'Admin') ?></span>
            <a class="btn btn-outline btn-sm" href="<?= url('admin/logout') ?>" data-confirm-logout>Logout</a>
        </div>
    </header>
    <main class="admin-main">
        <?php foreach ($flashes as $type => $message): ?>
            <div class="flash flash-<?= h($type) ?>"><?= h($message) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</div>
<script src="<?= asset('public/assets/js/app.js') ?>"></script>
</body>
</html>
