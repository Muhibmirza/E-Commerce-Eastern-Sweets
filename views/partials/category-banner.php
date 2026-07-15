<?php
$banner = $categoryBanner ?? [
    'name' => 'Eastern Sweets',
    'tagline' => 'Freshly prepared favourites',
    'image' => 'public/assets/images/products/product-33.png',
    'accent' => '#d1ad57',
    'cta' => url('shop'),
    'cta_text' => 'Shop Menu',
    'animation' => 'basic',
];
$animation = $banner['animation'] ?? 'basic';
$animationClass = 'is-basic-banner';
if ($animation === 'mithai-spin') {
    $animationClass = 'is-mithai-spin';
} elseif ($animation === 'slide-3d') {
    $animationClass = 'is-slide-3d';
}
?>
<section class="category-hero-3d <?= $animationClass ?>" style="--category-accent: <?= h($banner['accent']) ?>" data-category-banner>
    <div class="container category-hero-inner">
        <div class="category-hero-copy">
            <p class="eyebrow">Fresh category</p>
            <h1><?= h($banner['name']) ?></h1>
            <p><?= h($banner['tagline']) ?></p>
            <a class="btn btn-primary" href="<?= h($banner['cta']) ?>"><?= h($banner['cta_text'] ?? ('Shop ' . $banner['name'])) ?></a>
        </div>
        <div class="category-plate-stage">
            <div class="category-plate-shadow" aria-hidden="true"></div>
            <div class="category-plate-wrap">
                <div class="category-plate-float">
                    <img src="<?= asset($banner['image']) ?>" alt="<?= h($banner['name']) ?>">
                </div>
            </div>
        </div>
    </div>
</section>
