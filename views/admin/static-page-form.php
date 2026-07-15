<form class="admin-form" method="post" action="<?= url('admin/static-page-form') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="slug" value="<?= h($page['slug']) ?>">
    <section class="form-card">
        <h2><?= h(ucwords(str_replace('-', ' ', $page['slug']))) ?></h2>
        <div class="form-grid">
            <label class="full">Title<input name="title" required value="<?= h($page['title'] ?? '') ?>"></label>
            <label class="full">Page Content<textarea name="body" rows="14" required><?= h($page['body'] ?? '') ?></textarea></label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Page</button>
</form>
