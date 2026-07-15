<form class="admin-form" method="post" action="<?= url('admin/usp-form') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($block['id'] ?? '') ?>">
    <section class="form-card">
        <h2>USP Block</h2>
        <div class="form-grid">
            <label>Title<input name="title" required value="<?= h($block['title'] ?? '') ?>"></label>
            <label>Icon Text<input name="icon_text" value="<?= h($block['icon_text'] ?? '') ?>"></label>
            <label class="full">Description<textarea name="description" rows="3" required><?= h($block['description'] ?? '') ?></textarea></label>
            <label class="full">Icon Image Path<input name="icon_image_path" value="<?= h($block['icon_image_path'] ?? '') ?>"></label>
            <label class="full">Upload Icon Image<input name="icon_image_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#usp-icon-preview"></label>
            <?php if (!empty($block['icon_image_path'])): ?><div class="upload-preview full"><img id="usp-icon-preview" class="admin-preview-img" src="<?= asset($block['icon_image_path']) ?>" alt=""></div><?php endif; ?>
            <label>Sort Order<input name="sort_order" type="number" value="<?= h($block['sort_order'] ?? 0) ?>"></label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($block) || ($block['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Block</button>
</form>
