<?php $imagePath = $banner['image_path'] ?? 'public/assets/images/products/product-01.png'; ?>
<form class="admin-form" method="post" action="<?= url('admin/banner-form') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($banner['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Banner Details</h2>
        <p class="muted">Only a banner image is needed. Text and link fields are optional and are kept for future use.</p>
        <div class="form-grid">
            <label>Title (optional)<input name="title" value="<?= h($banner['title'] ?? '') ?>"></label>
            <label>Subtitle (optional)<input name="subtitle" value="<?= h($banner['subtitle'] ?? '') ?>"></label>
            <label class="full">Description (optional)<input name="description" value="<?= h($banner['description'] ?? '') ?>"></label>
            <label>Button Text (optional)<input name="button_text" value="<?= h($banner['button_text'] ?? '') ?>"></label>
            <label>Link URL (optional)<input name="link_url" value="<?= h($banner['link_url'] ?? '') ?>"></label>
            <label class="full">Image Path<input name="image_path" value="<?= h($imagePath) ?>"></label>
            <label class="full">Upload/Replace Image<input name="image_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#banner-preview"></label>
            <div class="upload-preview full"><img id="banner-preview" src="<?= asset($imagePath) ?>" alt="" class="admin-preview-img"></div>
            <label>Sort Order<input name="sort_order" type="number" value="<?= h($banner['sort_order'] ?? 0) ?>"></label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($banner) || ($banner['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Banner</button>
</form>
