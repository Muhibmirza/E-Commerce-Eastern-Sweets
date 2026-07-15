<form class="admin-form" method="post" action="<?= url('admin/payment-form') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h($payment['id'] ?? '') ?>">
    <section class="form-card">
        <h2>Payment Details</h2>
        <div class="form-grid">
            <label>Order
                <select name="order_id" required>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?= h($order['id']) ?>" <?= (int)($payment['order_id'] ?? 0) === (int)$order['id'] ? 'selected' : '' ?>>
                            <?= h($order['order_number']) ?> - <?= money($order['total_amount']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Method
                <select name="method" required>
                    <?php foreach (['cod'=>'Cash on Delivery','visa'=>'Visa','mastercard'=>'Mastercard','debit_card'=>'Debit Card','jazzcash'=>'JazzCash','easypaisa'=>'Easypaisa'] as $key => $label): ?>
                        <option value="<?= h($key) ?>" <?= ($payment['method'] ?? '') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Amount<input name="amount" type="number" step="0.01" required value="<?= h($payment['amount'] ?? 0) ?>"></label>
            <label>Status
                <select name="status" required>
                    <?php foreach (['pending','sandbox_pending','paid','failed','refunded','cancelled'] as $status): ?>
                        <option value="<?= h($status) ?>" <?= ($payment['status'] ?? '') === $status ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $status))) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="full">Transaction Reference<input name="transaction_reference" value="<?= h($payment['transaction_reference'] ?? '') ?>"></label>
        </div>
    </section>
    <button class="btn btn-primary" type="submit">Save Payment</button>
</form>
