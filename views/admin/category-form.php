<?php
$imagePath = $category['image_path'] ?? 'public/assets/images/products/product-01.png';
$bannerPath = $category['banner_image_path'] ?? '';
?>
<form class="admin-form" method="post" action="<?= url('admin/category-form') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($category['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Category Details</h2>
        <div class="form-grid">
            <label>Name<input name="name" required value="<?= h($category['name'] ?? '') ?>"></label>
            <label>Sort Order<input name="sort_order" type="number" value="<?= h($category['sort_order'] ?? 0) ?>"></label>
            <label class="full">Thumbnail Path<input name="image_path" value="<?= h($imagePath) ?>"></label>
            <label class="full">Upload/Replace Thumbnail<input name="image_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#category-thumb-preview"></label>
            <div class="upload-preview full">
                <img id="category-thumb-preview" src="<?= asset($imagePath) ?>" alt="" class="admin-preview-img">
            </div>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($category) || ($category['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        </div>
    </section>

    <section class="form-card">
        <h2>Category Banner</h2>
        <div class="form-grid">
            <label>Tagline<input name="banner_tagline" value="<?= h($category['banner_tagline'] ?? '') ?>"></label>
            <label>Accent Color<input name="banner_accent" type="color" value="<?= h($category['banner_accent'] ?? '#d1ad57') ?>"></label>
            <label>CTA Text<input name="banner_cta_text" value="<?= h($category['banner_cta_text'] ?? 'Shop ' . ($category['name'] ?? 'Category')) ?>"></label>
            <label>CTA Link<input name="banner_cta_link" value="<?= h($category['banner_cta_link'] ?? url('shop')) ?>"></label>
            <label>Animation
                <select name="banner_animation">
                    <?php foreach (['slide-3d' => '3D slide + float', 'basic' => 'Basic fade/parallax', 'mithai-spin' => 'Mithai spinning platter'] as $value => $label): ?>
                        <option value="<?= h($value) ?>" <?= ($category['banner_animation'] ?? 'basic') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="full">Banner Image Path<input name="banner_image_path" value="<?= h($bannerPath) ?>"></label>
            <label class="full">Upload/Replace Banner Image<input name="banner_image_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#category-banner-preview"></label>
            <?php if ($bannerPath): ?>
                <div class="upload-preview full"><img id="category-banner-preview" src="<?= asset($bannerPath) ?>" alt="" class="admin-preview-img"></div>
            <?php else: ?>
                <div class="upload-preview full"><img id="category-banner-preview" alt="" class="admin-preview-img is-empty"><span>No banner image uploaded yet.</span></div>
            <?php endif; ?>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Category</button>
</form>
