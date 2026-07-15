<?php
$addRoutes = [
    'categories' => 'admin/category-form',
    'customers' => 'admin/customer-form',
    'payments' => 'admin/payment-form',
    'coupons' => 'admin/coupon-form',
    'banners' => 'admin/banner-form',
];
$editRoutes = $addRoutes;
$deleteRoutes = [
    'categories' => 'admin/category-delete',
    'customers' => 'admin/customer-delete',
    'payments' => 'admin/payment-delete',
    'coupons' => 'admin/coupon-delete',
    'banners' => 'admin/banner-delete',
];
?>
<section class="table-card">
    <div class="card-head">
        <h2><?= h($title) ?></h2>
        <?php if (isset($addRoutes[$resource])): ?>
            <a class="btn btn-primary btn-sm" href="<?= url($addRoutes[$resource]) ?>">Add <?= h(ucfirst(rtrim($resource, 's'))) ?></a>
        <?php endif; ?>
    </div>

    <?php if ($resource === 'settings'): ?>
        <form method="post" action="<?= url('admin/settings-save') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="settings-grid">
                <?php foreach ($rows as $row): ?>
                    <label><?= h(ucwords(str_replace('_', ' ', $row['setting_key']))) ?>
                        <?php if (str_starts_with($row['setting_key'], 'theme_')): ?>
                            <input type="color" name="settings[<?= h($row['setting_key']) ?>]" value="<?= h($row['setting_value']) ?>">
                        <?php else: ?>
                            <input name="settings[<?= h($row['setting_key']) ?>]" value="<?= h($row['setting_value']) ?>">
                        <?php endif; ?>
                    </label>
                    <?php if ($row['setting_key'] === 'site_logo'): ?>
                        <label>Upload Logo<input name="site_logo_file" type="file" accept="image/jpeg,image/png,image/webp"></label>
                    <?php elseif ($row['setting_key'] === 'background_pattern'): ?>
                        <label>Upload Pattern<input name="background_pattern_file" type="file" accept="image/jpeg,image/png,image/webp"></label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary" type="submit">Save Settings</button>
        </form>
    <?php else: ?>
        <form class="admin-filters admin-filters-wide" method="get" action="<?= url('admin/' . $resource) ?>">
            <input name="q" placeholder="Search <?= h($resource) ?>" value="<?= h($_GET['q'] ?? '') ?>">
            <?php if ($resource === 'payments'): ?>
                <select name="method">
                    <option value="">All methods</option>
                    <?php foreach (['cod','visa','mastercard','debit_card','jazzcash','easypaisa'] as $method): ?>
                        <option value="<?= h($method) ?>" <?= ($_GET['method'] ?? '') === $method ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $method))) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach (['pending','sandbox_pending','paid','failed','refunded','cancelled'] as $status): ?>
                        <option value="<?= h($status) ?>" <?= ($_GET['status'] ?? '') === $status ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $status))) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($resource === 'customers'): ?>
                <select name="status">
                    <option value="">All customers</option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="blocked" <?= ($_GET['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                </select>
            <?php else: ?>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            <?php endif; ?>
            <button class="btn btn-outline btn-sm" type="submit">Filter</button>
        </form>

        <div class="responsive-table">
            <table class="data-table">
                <thead><tr><?php foreach ($columns as $column): ?><th><?= h(ucwords(str_replace('_', ' ', $column))) ?></th><?php endforeach; ?><th class="action-col">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?><td class="<?= is_numeric($row[$column] ?? null) ? 'num' : '' ?>"><?= h($row[$column] ?? '') ?></td><?php endforeach; ?>
                        <td class="actions">
                            <?php if (isset($editRoutes[$resource])): ?>
                                <a class="icon-button" href="<?= url($editRoutes[$resource], ['id' => $row['id']]) ?>">&#9998;</a>
                            <?php endif; ?>
                            <?php if (isset($deleteRoutes[$resource])): ?>
                                <form method="post" action="<?= url($deleteRoutes[$resource]) ?>" onsubmit="return confirm('Delete or disable this item?')">
                                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($row['id']) ?>">
                                    <button class="icon-button danger" type="submit">&times;</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($resource === 'customers'): ?>
                                <form method="post" action="<?= url('admin/customer-toggle') ?>">
                                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($row['id']) ?>">
                                    <input type="hidden" name="blocked" value="<?= (int)($row['is_blocked'] ?? 0) ? 0 : 1 ?>">
                                    <button class="btn btn-outline btn-sm" type="submit"><?= (int)($row['is_blocked'] ?? 0) ? 'Unblock' : 'Block' ?></button>
                                </form>
                            <?php elseif (!isset($editRoutes[$resource]) && !isset($deleteRoutes[$resource])): ?>
                                <span class="muted">View only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$rows): ?><div class="empty-state">No <?= h($resource) ?> found.</div><?php endif; ?>
        <div class="pagination"><a class="is-active">1</a><a>2</a><a>3</a></div>
    <?php endif; ?>
</section>
