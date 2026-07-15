<?php $mainImage = $product['images'][0]['image_path'] ?? 'public/assets/images/products/product-01.png'; $firstVariant = $product['variants'][0] ?? null; ?>
<section class="section product-detail">
    <div class="container product-detail-grid">
        <div class="gallery">
            <img class="main-product-image" data-main-image src="<?= asset($mainImage) ?>" alt="<?= h($product['name']) ?>">
            <div class="thumbs">
                <?php foreach ($product['images'] as $image): ?><button type="button" data-thumb="<?= asset($image['image_path']) ?>"><img src="<?= asset($image['image_path']) ?>" alt=""></button><?php endforeach; ?>
            </div>
        </div>
        <div class="product-buy">
            <p class="eyebrow"><?= h($product['category_name']) ?></p>
            <h1><?= h($product['name']) ?></h1>
            <p><?= h($product['short_description']) ?></p>
            <div class="detail-price" data-price><?= $firstVariant ? money($firstVariant['price']) : 'Unavailable' ?></div>
            <form class="ajax-cart detail-cart" action="<?= url('cart/add') ?>" method="post">
                <?= csrf_field() ?>
                <label>Weight / Size
                    <select name="variant_id" data-variant-select>
                        <?php foreach ($product['variants'] as $variant): ?><option value="<?= $variant['id'] ?>" data-price="<?= h(money($variant['price'])) ?>"><?= h($variant['variant_name']) ?> - <?= money($variant['price']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>Quantity
                    <div class="qty"><button type="button" data-step="-1">−</button><input name="quantity" value="1" min="1" type="number"><button type="button" data-step="1">+</button></div>
                </label>
                <div class="button-row"><button class="btn btn-primary" type="submit">Add to Cart</button></div>
            </form>
            <div class="tabs" data-tabs>
                <button class="is-active" data-tab="desc">Description</button><button data-tab="ingredients">Ingredients</button><button data-tab="reviews">Reviews</button>
            </div>
            <div class="tab-panel is-active" data-panel="desc"><?= nl2br(h($product['description'])) ?></div>
            <div class="tab-panel" data-panel="ingredients"><strong>Ingredients:</strong> <?= h($product['ingredients']) ?><br><strong>Allergens:</strong> <?= h($product['allergens']) ?></div>
            <div class="tab-panel" data-panel="reviews"><?php foreach ($product['reviews'] as $review): ?><p>★★★★★ <?= h($review['comment']) ?> <strong>- <?= h($review['name'] ?? 'Customer') ?></strong></p><?php endforeach; ?></div>
        </div>
    </div>
</section>
<section class="section section-tint">
    <div class="container"><div class="section-head"><div><p class="eyebrow">Pair it with</p><h2>Related products</h2></div></div><div class="product-grid"><?php foreach ($related as $product): require __DIR__ . '/partials/product-card.php'; endforeach; ?></div></div>
</section>
