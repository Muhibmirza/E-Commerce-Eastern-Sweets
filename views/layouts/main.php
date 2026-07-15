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
<body>
<?php require __DIR__ . '/../partials/header.php'; ?>
<main>
    <?php foreach ($flashes as $type => $message): ?>
        <div class="container flash flash-<?= h($type) ?>"><?= h($message) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= asset('public/assets/js/app.js') ?>"></script>
</body>
</html>
