<section class="hero" data-slider>
    <?php foreach ($banners as $i => $banner): ?>
        <article class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>" style="background-image: linear-gradient(90deg, rgba(11,78,61,.9), rgba(11,78,61,.34)), url('<?= h(asset($banner['image_path'])) ?>')">
            <div class="container hero-content">
                <p class="eyebrow"><?= h($banner['subtitle']) ?></p>
                <h1><?= h($banner['title']) ?></h1>
                <p><?= h($banner['description']) ?></p>
                <a class="btn btn-primary" href="<?= h($banner['link_url']) ?>"><?= h($banner['button_text']) ?></a>
            </div>
        </article>
    <?php endforeach; ?>
    <button class="slider-arrow prev" data-slide-prev aria-label="Previous">&#8249;</button>
    <button class="slider-arrow next" data-slide-next aria-label="Next">&#8250;</button>
    <div class="slider-dots">
        <?php foreach ($banners as $i => $banner): ?><button data-slide-dot="<?= $i ?>" class="<?= $i === 0 ? 'is-active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div><p class="eyebrow"><?= h($settings['home_categories_eyebrow'] ?? 'Shop by mood') ?></p><h2><?= h($settings['home_categories_title'] ?? 'Fresh categories') ?></h2></div>
            <a class="btn btn-outline" href="<?= url('shop') ?>">View All</a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a class="category-card reveal" href="<?= url('category/' . $category['slug']) ?>">
                    <img src="<?= asset($category['image_path']) ?>" alt="<?= h($category['name']) ?>">
                    <span><?= h($category['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <div class="section-head centered reveal">
            <div><p class="eyebrow"><?= h($settings['home_featured_eyebrow'] ?? 'Best sellers') ?></p><h2><?= h($settings['home_featured_title'] ?? 'Most loved mithai') ?></h2></div>
        </div>
        <div class="product-grid">
            <?php foreach ($products as $product): require __DIR__ . '/partials/product-card.php'; endforeach; ?>
        </div>
        <div class="center-actions"><a class="btn btn-primary" href="<?= url('shop') ?>">Shop Menu</a></div>
    </div>
</section>

<section class="usp-strip">
    <div class="container usp-grid">
        <?php foreach ($uspBlocks as $block): ?>
            <div class="reveal">
                <?php if (!empty($block['icon_image_path'])): ?>
                    <img class="usp-icon" src="<?= asset($block['icon_image_path']) ?>" alt="">
                <?php else: ?>
                    <span><?= h($block['icon_text'] ?: '*') ?></span>
                <?php endif; ?>
                <h3><?= h($block['title']) ?></h3>
                <p><?= h($block['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section promo-band">
    <div class="container promo-inner reveal">
        <div>
            <p class="eyebrow"><?= h($settings['promo_eyebrow'] ?? 'Celebration Boxes') ?></p>
            <h2><?= h($settings['promo_title'] ?? 'Build a premium mithai box for Eid, weddings, or corporate gifting.') ?></h2>
            <p><?= h($settings['promo_body'] ?? 'Choose fresh assorted mithai, dry fruit sweets, and bakery favourites packed in Eastern Sweets signature boxes.') ?></p>
        </div>
        <?php if (!empty($settings['promo_image'])): ?>
            <img class="promo-image" src="<?= asset($settings['promo_image']) ?>" alt="">
        <?php endif; ?>
        <a class="btn btn-light" href="<?= h($settings['promo_button_link'] ?? url('shop', ['category_id' => 5])) ?>"><?= h($settings['promo_button_text'] ?? 'Explore Gift Boxes') ?></a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head centered reveal"><div><p class="eyebrow"><?= h($settings['testimonials_eyebrow'] ?? 'Customer notes') ?></p><h2><?= h($settings['testimonials_title'] ?? 'Sweet words') ?></h2></div></div>
        <div class="testimonial-grid">
            <?php foreach ($testimonials as $testimonial): ?>
                <blockquote class="reveal">
                    <?= h((int)$testimonial['rating']) ?>/5
                    <p><?= h($testimonial['review_text']) ?></p>
                    <cite><?= h($testimonial['customer_name']) ?></cite>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="gallery-strip" aria-label="Eastern Sweets product gallery">
    <div class="gallery-track">
        <?php for ($loop = 0; $loop < 2; $loop++): ?>
            <?php foreach (array_slice($products, 0, 8) as $product): ?>
                <a class="gallery-item" href="<?= url('product', ['id' => $product['id']]) ?>">
                    <img src="<?= asset($product['image_path']) ?>" alt="<?= h($product['name']) ?>">
                    <span><?= h($product['name']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</section>

<section class="section newsletter">
    <div class="container newsletter-inner reveal">
        <div><p class="eyebrow"><?= h($settings['newsletter_eyebrow'] ?? 'Fresh offers') ?></p><h2><?= h($settings['newsletter_title'] ?? 'Get celebration deals in your inbox') ?></h2></div>
        <form action="<?= url('newsletter/subscribe') ?>" method="post">
            <?= csrf_field() ?>
            <input name="email" type="email" placeholder="Email address" required>
            <button class="btn btn-primary" type="submit">Subscribe</button>
        </form>
    </div>
</section>
