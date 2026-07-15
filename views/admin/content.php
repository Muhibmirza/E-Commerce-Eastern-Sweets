<form class="admin-form" method="post" action="<?= url('admin/content') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="form-card">
        <div class="card-head"><h2>Branding</h2></div>
        <div class="form-grid">
            <label>Primary Color<input name="settings[theme_primary]" type="color" value="<?= h($settings['theme_primary'] ?? '#0b4e3d') ?>"></label>
            <label>Secondary Color<input name="settings[theme_secondary]" type="color" value="<?= h($settings['theme_secondary'] ?? '#d1ad57') ?>"></label>
            <label>Accent Color<input name="settings[theme_accent]" type="color" value="<?= h($settings['theme_accent'] ?? '#8f2432') ?>"></label>
            <label class="full">Logo Path<input name="settings[site_logo]" value="<?= h($settings['site_logo'] ?? 'public/assets/images/logo.png') ?>"></label>
            <label class="full">Upload/Replace Logo<input name="site_logo_file" type="file" accept="image/jpeg,image/png,image/webp" data-live-preview="#site-logo-preview"></label>
            <div class="upload-preview full"><img id="site-logo-preview" src="<?= asset($settings['site_logo'] ?? 'public/assets/images/logo.png') ?>" alt="" class="admin-preview-img logo-preview"></div>
            <label class="full">Background Pattern Path<input name="settings[background_pattern]" value="<?= h($settings['background_pattern'] ?? '') ?>"></label>
            <label class="full">Upload/Replace Pattern<input name="background_pattern_file" type="file" accept="image/jpeg,image/png,image/webp"></label>
        </div>
    </section>

    <section class="form-card">
        <h2>Homepage Text</h2>
        <div class="form-grid">
            <label>Categories Eyebrow<input name="settings[home_categories_eyebrow]" value="<?= h($settings['home_categories_eyebrow'] ?? 'Shop by mood') ?>"></label>
            <label>Categories Title<input name="settings[home_categories_title]" value="<?= h($settings['home_categories_title'] ?? 'Fresh categories') ?>"></label>
            <label>Featured Eyebrow<input name="settings[home_featured_eyebrow]" value="<?= h($settings['home_featured_eyebrow'] ?? 'Best sellers') ?>"></label>
            <label>Featured Title<input name="settings[home_featured_title]" value="<?= h($settings['home_featured_title'] ?? 'Most loved mithai') ?>"></label>
            <label>Testimonials Eyebrow<input name="settings[testimonials_eyebrow]" value="<?= h($settings['testimonials_eyebrow'] ?? 'Customer notes') ?>"></label>
            <label>Testimonials Title<input name="settings[testimonials_title]" value="<?= h($settings['testimonials_title'] ?? 'Sweet words') ?>"></label>
            <label>Newsletter Eyebrow<input name="settings[newsletter_eyebrow]" value="<?= h($settings['newsletter_eyebrow'] ?? 'Fresh offers') ?>"></label>
            <label>Newsletter Title<input name="settings[newsletter_title]" value="<?= h($settings['newsletter_title'] ?? 'Get celebration deals in your inbox') ?>"></label>
        </div>
    </section>

    <section class="form-card">
        <h2>Promotion Band</h2>
        <div class="form-grid">
            <label>Eyebrow<input name="settings[promo_eyebrow]" value="<?= h($settings['promo_eyebrow'] ?? 'Celebration Boxes') ?>"></label>
            <label>Button Text<input name="settings[promo_button_text]" value="<?= h($settings['promo_button_text'] ?? 'Explore Gift Boxes') ?>"></label>
            <label class="full">Title<input name="settings[promo_title]" value="<?= h($settings['promo_title'] ?? '') ?>"></label>
            <label class="full">Body<textarea name="settings[promo_body]" rows="3"><?= h($settings['promo_body'] ?? '') ?></textarea></label>
            <label class="full">Button Link<input name="settings[promo_button_link]" value="<?= h($settings['promo_button_link'] ?? url('shop')) ?>"></label>
            <label class="full">Promo Image Path<input name="settings[promo_image]" value="<?= h($settings['promo_image'] ?? '') ?>"></label>
            <label class="full">Upload Promo Image<input name="promo_image_file" type="file" accept="image/jpeg,image/png,image/webp"></label>
        </div>
    </section>

    <section class="form-card">
        <h2>Footer</h2>
        <div class="form-grid">
            <label class="full">About Text<textarea name="settings[footer_about]" rows="3"><?= h($settings['footer_about'] ?? '') ?></textarea></label>
            <label class="full">Address<input name="settings[footer_address]" value="<?= h($settings['footer_address'] ?? '') ?>"></label>
            <label>Phone<input name="settings[footer_phone]" value="<?= h($settings['footer_phone'] ?? '') ?>"></label>
            <label>Hours<input name="settings[footer_hours]" value="<?= h($settings['footer_hours'] ?? '') ?>"></label>
            <label class="full">Social Links / Labels<input name="settings[footer_socials]" value="<?= h($settings['footer_socials'] ?? '') ?>"></label>
            <label class="full">Payment Methods<input name="settings[footer_payments]" value="<?= h($settings['footer_payments'] ?? '') ?>"></label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Content Settings</button>
</form>

<section class="table-card">
    <div class="card-head">
        <h2>Why Choose Us Blocks</h2>
        <a class="btn btn-primary btn-sm" href="<?= url('admin/usp-form') ?>">Add Block</a>
    </div>
    <div class="responsive-table">
        <table class="data-table"><thead><tr><th>Title</th><th>Icon</th><th>Order</th><th>Active</th><th class="action-col">Actions</th></tr></thead><tbody>
        <?php foreach ($uspBlocks as $block): ?>
            <tr><td><?= h($block['title']) ?></td><td><?= h($block['icon_text'] ?: $block['icon_image_path']) ?></td><td><?= h($block['sort_order']) ?></td><td><?= h($block['is_active']) ?></td><td class="actions"><a class="icon-button" href="<?= url('admin/usp-form', ['id' => $block['id']]) ?>">&#9998;</a><form method="post" action="<?= url('admin/usp-delete') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= h($block['id']) ?>"><button class="icon-button danger" type="submit">&times;</button></form></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>

<section class="table-card">
    <div class="card-head">
        <h2>Testimonials</h2>
        <a class="btn btn-primary btn-sm" href="<?= url('admin/testimonial-form') ?>">Add Testimonial</a>
    </div>
    <div class="responsive-table">
        <table class="data-table"><thead><tr><th>Name</th><th>Rating</th><th>Review</th><th>Active</th><th class="action-col">Actions</th></tr></thead><tbody>
        <?php foreach ($testimonials as $testimonial): ?>
            <tr><td><?= h($testimonial['customer_name']) ?></td><td><?= h($testimonial['rating']) ?>/5</td><td><?= h($testimonial['review_text']) ?></td><td><?= h($testimonial['is_active']) ?></td><td class="actions"><a class="icon-button" href="<?= url('admin/testimonial-form', ['id' => $testimonial['id']]) ?>">&#9998;</a><form method="post" action="<?= url('admin/testimonial-delete') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= h($testimonial['id']) ?>"><button class="icon-button danger" type="submit">&times;</button></form></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>

<section class="table-card">
    <div class="card-head"><h2>Static Pages</h2></div>
    <div class="responsive-table">
        <table class="data-table"><thead><tr><th>Slug</th><th>Title</th><th class="action-col">Actions</th></tr></thead><tbody>
        <?php foreach ($pages as $page): ?>
            <tr><td><?= h($page['slug']) ?></td><td><?= h($page['title']) ?></td><td class="actions"><a class="btn btn-outline btn-sm" href="<?= url('admin/static-page-form', ['slug' => $page['slug']]) ?>">Edit</a></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
