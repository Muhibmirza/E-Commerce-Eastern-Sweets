<form class="admin-form" method="post" action="<?= url('admin/testimonial-form') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($testimonial['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Testimonial</h2>
        <div class="form-grid">
            <label>Customer Name<input name="customer_name" required value="<?= h($testimonial['customer_name'] ?? '') ?>"></label>
            <label>Rating<input name="rating" type="number" min="1" max="5" value="<?= h($testimonial['rating'] ?? 5) ?>"></label>
            <label class="full">Review<textarea name="review_text" rows="4" required><?= h($testimonial['review_text'] ?? '') ?></textarea></label>
            <label class="full">Photo Path<input name="customer_photo_path" value="<?= h($testimonial['customer_photo_path'] ?? '') ?>"></label>
            <label class="full">Upload/Replace Photo<input name="customer_photo_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#testimonial-photo-preview"></label>
            <?php if (!empty($testimonial['customer_photo_path'])): ?><div class="upload-preview full"><img id="testimonial-photo-preview" class="admin-preview-img" src="<?= asset($testimonial['customer_photo_path']) ?>" alt=""></div><?php endif; ?>
            <label>Sort Order<input name="sort_order" type="number" value="<?= h($testimonial['sort_order'] ?? 0) ?>"></label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($testimonial) || ($testimonial['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Testimonial</button>
</form>
