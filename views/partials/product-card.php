<?php $variant = Product::variants((int)$product['id'])[0] ?? null; ?>
<article class="product-card reveal">
    <a class="product-media" href="<?= url('product', ['id' => $product['id']]) ?>">
        <img src="<?= asset($product['image_path'] ?? 'public/assets/images/products/product-01.png') ?>" alt="<?= h($product['name'] ?? 'Eastern Sweets Product') ?>">
        <?php if ((float)($product['compare_at_price'] ?? 0) > (float)($product['min_price'] ?? 0)): ?><span class="sale-badge">Sale</span><?php endif; ?>
        <span class="product-name-chip"><?= h($product['name'] ?? 'Eastern Sweets Product') ?></span>
    </a>
    <div class="product-info">
        <p class="product-category"><?= h($product['category_name'] ?? '') ?></p>
        <h3 class="product-title"><a href="<?= url('product', ['id' => $product['id']]) ?>"><?= h($product['name'] ?? 'Eastern Sweets Product') ?></a></h3>
        <p><?= h($product['short_description'] ?? '') ?></p>
        <div class="rating">&#9733;&#9733;&#9733;&#9733;&#9733; <span>4.8</span></div>
        <div class="product-row">
            <strong><?= money($product['min_price'] ?? 0) ?></strong>
            <?php if ($variant): ?>
                <form class="ajax-cart compact-cart" action="<?= url('cart/add') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="variant_id" value="<?= h($variant['id']) ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="btn btn-primary btn-sm" type="submit">Add</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
