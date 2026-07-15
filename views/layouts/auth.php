<?php $flashes = flash(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= h($title ?? 'Eastern Sweets') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('public/assets/css/style.css') ?>">
    <?= app_theme_style() ?>
</head>
<body class="auth-body">
<main class="auth-shell">
    <a class="auth-logo" href="<?= url('home') ?>"><img src="<?= asset(app_setting('site_logo', 'public/assets/images/logo.png')) ?>" alt="Eastern Sweets"></a>
    <?php foreach ($flashes as $type => $message): ?>
        <div class="flash flash-<?= h($type) ?>"><?= h($message) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>
<script src="<?= asset('public/assets/js/app.js') ?>"></script>
</body>
</html>
