<?php
$flashes = flash();
$routeName = trim((string)($_GET['route'] ?? ''), '/');
$pageTitle = (string)($title ?? 'Eastern Sweets');
$metaDescriptions = [
    '' => 'Shop fresh mithai, bakery favourites, savoury snacks and celebration gift boxes from Eastern Sweets in Karachi.',
    'home' => 'Shop fresh mithai, bakery favourites, savoury snacks and celebration gift boxes from Eastern Sweets in Karachi.',
    'shop' => 'Explore the complete Eastern Sweets menu of mithai, bakery items, snacks, desserts and premium gift boxes.',
    'about' => 'Discover the Eastern Sweets story, our commitment to freshness and the flavours loved by Karachi families.',
    'contact' => 'Contact Eastern Sweets for orders, delivery information, celebration boxes and customer support.',
];
$metaDescription = $metaDescriptions[$routeName] ?? ('Explore ' . $pageTitle . ' at Eastern Sweets — fresh favourites, quality ingredients and convenient ordering.');
$canonicalParams = $routeName === 'product' && isset($_GET['id']) ? ['id' => (int)$_GET['id']] : [];
$canonicalUrl = absolute_url($routeName, $canonicalParams);
$socialImage = absolute_url(app_setting('site_logo', 'public/assets/images/logo.png'));
$noIndexRoutes = ['cart', 'checkout', 'login', 'register', 'account', 'track', 'order-confirmation'];
$robots = in_array($routeName, $noIndexRoutes, true) ? 'noindex,follow' : 'index,follow,max-image-preview:large';
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Bakery',
    'name' => 'Eastern Sweets',
    'url' => absolute_url('home'),
    'logo' => $socialImage,
    'telephone' => app_setting('footer_phone', '0310-4490798'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => app_setting('footer_address', 'Anees Complex, Gulshan-e-Iqbal, Karachi'), 'addressLocality' => 'Karachi', 'addressCountry' => 'PK'],
    'priceRange' => 'PKR',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($metaDescription) ?>">
    <meta name="robots" content="<?= h($robots) ?>">
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Eastern Sweets">
    <meta property="og:title" content="<?= h($pageTitle) ?>">
    <meta property="og:description" content="<?= h($metaDescription) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:image" content="<?= h($socialImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('public/assets/css/style.css') ?>">
    <?= app_theme_style() ?>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
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
