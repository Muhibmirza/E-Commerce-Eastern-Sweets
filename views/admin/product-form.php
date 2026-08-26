<form class="admin-form" method="post" action="<?= url('admin/product-form') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($product['id'] ?? '') ?>">
    <section class="form-card"><h2>Basic Info</h2><div class="form-grid">
        <label>Name<input name="name" required value="<?= h($product['name'] ?? '') ?>"></label>
        <label>Category<select name="category_id"><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '')==$cat['id']?'selected':'' ?>><?= h($cat['name']) ?></option><?php endforeach; ?></select></label>
        <label class="full">Short Description<input name="short_description" value="<?= h($product['short_description'] ?? '') ?>"></label>
        <label class="full">Description<textarea name="description" rows="4"><?= h($product['description'] ?? '') ?></textarea></label>
    </div></section>
    <?php $variant = $product['variants'][0] ?? null; $image = $product['images'][0] ?? null; ?>
    <section class="form-card"><h2>Pricing, Stock & Details</h2><div class="form-grid">
        <label>Stock<input name="stock" type="number" value="<?= h($product['stock'] ?? 25) ?>"></label>
        <label>Primary Variant<input name="variant_name" value="<?= h($variant['variant_name'] ?? '500g') ?>"></label>
        <label>Price<input name="price" type="number" step="0.01" value="<?= h($variant['price'] ?? 0) ?>"></label>
        <label>Compare Price<input name="compare_at_price" type="number" step="0.01" value="<?= h($variant['compare_at_price'] ?? '') ?>"></label>
        <label>Variant Stock<input name="variant_stock" type="number" value="<?= h($variant['stock'] ?? ($product['stock'] ?? 25)) ?>"></label>
        <label>Ingredients<input name="ingredients" value="<?= h($product['ingredients'] ?? 'Milk, sugar, nuts, ghee') ?>"></label>
        <label>Allergens<input name="allergens" value="<?= h($product['allergens'] ?? 'Milk, nuts') ?>"></label>
        <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($product) || ($product['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        <label class="checkbox"><input type="checkbox" name="is_featured" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>> Featured</label>
    </div></section>
    <section class="form-card"><h2>Images</h2><div class="form-grid">
        <label class="full">Primary Image Path<input name="image_path" value="<?= h($image['image_path'] ?? 'public/assets/images/products/product-01.png') ?>"></label>
        <label class="full">Upload/Replace Primary Image<input name="image_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#product-primary-preview"></label>
        <div class="upload-preview full"><img id="product-primary-preview" src="<?= asset($image['image_path'] ?? 'public/assets/images/products/product-01.png') ?>" alt="" class="admin-preview-img"></div>
        <label class="full">Upload Gallery Images<input name="gallery_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple></label>
    </div>
    <?php if (!empty($product['images'])): ?>
        <div class="preview-grid">
            <?php foreach ($product['images'] as $productImage): ?>
                <div class="image-manage">
                    <img src="<?= asset($productImage['image_path']) ?>" alt="" class="admin-preview-img">
                    <?php if (!(int)$productImage['is_primary']): ?>
                        <button class="btn btn-outline btn-sm" type="submit" form="delete-product-image-<?= h($productImage['id']) ?>">Remove</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </section>
    <button class="btn btn-primary" type="submit">Save Product</button>
</form>
<?php if (!empty($product['images'])): ?>
    <?php foreach ($product['images'] as $productImage): ?>
        <?php if (!(int)$productImage['is_primary']): ?>
            <form id="delete-product-image-<?= h($productImage['id']) ?>" method="post" action="<?= url('admin/product-image-delete') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= h($product['id']) ?>">
                <input type="hidden" name="image_id" value="<?= h($productImage['id']) ?>">
            </form>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
