<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title ?? 'Invoice') ?></title>
    <link rel="stylesheet" href="<?= asset('public/assets/css/style.css') ?>">
    <?= app_theme_style() ?>
</head>
<body class="invoice-body">
<?= $content ?>
</body>
</html>
