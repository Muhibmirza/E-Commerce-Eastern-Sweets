<form class="admin-form" method="post" action="<?= url('admin/coupon-form') ?>">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($coupon['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Coupon Details</h2>
        <div class="form-grid">
            <label>Code<input name="code" required value="<?= h($coupon['code'] ?? '') ?>"></label>
            <label>Discount Type<select name="discount_type"><option value="percent" <?= ($coupon['discount_type'] ?? '')==='percent'?'selected':'' ?>>Percent</option><option value="fixed" <?= ($coupon['discount_type'] ?? '')==='fixed'?'selected':'' ?>>Fixed</option></select></label>
            <label>Discount Value<input name="discount_value" type="number" step="0.01" required value="<?= h($coupon['discount_value'] ?? 10) ?>"></label>
            <label>Expiry Date<input name="expires_at" type="date" value="<?= h($coupon['expires_at'] ?? '') ?>"></label>
            <label>Usage Limit<input name="usage_limit" type="number" value="<?= h($coupon['usage_limit'] ?? '') ?>"></label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= !isset($coupon) || ($coupon['is_active'] ?? 0) ? 'checked' : '' ?>> Active</label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Coupon</button>
</form>
