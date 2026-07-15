<form class="admin-form" method="post" action="<?= url('admin/customer-form') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h($customer['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Customer Details</h2>
        <div class="form-grid">
            <label>Name<input name="name" required value="<?= h($customer['name'] ?? '') ?>"></label>
            <label>Email<input name="email" type="email" required value="<?= h($customer['email'] ?? '') ?>"></label>
            <label>Phone<input name="phone" required value="<?= h($customer['phone'] ?? '') ?>"></label>
            <label>Password<input name="password" type="password" <?= isset($customer) ? '' : 'required' ?> placeholder="<?= isset($customer) ? 'Leave blank to keep current password' : 'Minimum 8 characters' ?>"></label>
            <label class="full">Default Address<textarea name="default_address" rows="4"><?= h($customer['default_address'] ?? '') ?></textarea></label>
            <label class="checkbox"><input type="checkbox" name="is_blocked" <?= ($customer['is_blocked'] ?? 0) ? 'checked' : '' ?>> Block customer</label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Customer</button>
</form>
