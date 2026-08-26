<?php $subtotal = array_sum(array_column($cart, 'line_total')); $delivery = $subtotal >= 2000 ? 0 : (float)app_setting('delivery_charge', '180'); $total = $subtotal + $delivery; ?>
<section class="page-hero compact"><div class="container"><p class="eyebrow">Cart → Details → Payment → Confirmation</p><h1>Checkout</h1></div></section>
<section class="section"><div class="container checkout-layout">
    <form class="checkout-form" action="<?= url('checkout') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-card"><h2>Delivery Details</h2>
            <div class="form-grid">
                <label>Name<input name="name" required value="<?= h($user['name'] ?? '') ?>"></label><label>Phone<input name="phone" required value="<?= h($user['phone'] ?? '') ?>"></label>
                <label>Email<input name="email" type="email" required value="<?= h($user['email'] ?? '') ?>"></label><label>City<input name="city" required value="Karachi"></label>
                <label>Area<input name="area" required placeholder="Gulshan, PECHS, DHA..."></label><label>Address<input name="address" required placeholder="House, street, nearest landmark"></label>
                <label class="full">Order Notes<textarea name="notes" rows="3"></textarea></label>
            </div>
        </div>
        <div class="form-card"><h2>Payment Method</h2><div class="payment-grid">
            <?php foreach ($paymentMethods as $key => $method): ?><label class="payment-option"><input type="radio" name="payment_method" value="<?= h($key) ?>" <?= $key==='cod'?'checked':'' ?>><img src="<?= asset($method['logo']) ?>" alt="<?= h($method['label']) ?>"><span><?= h($method['label']) ?></span></label><?php endforeach; ?>
        </div></div>
        <p class="form-help">Online payments open Safepay's secure hosted checkout. Available cards and wallets depend on the methods enabled in the Safepay sandbox account.</p>
        <button class="btn btn-primary" type="submit">Place Order</button>
    </form>
    <aside class="summary-card sticky-summary"><h2>Your Order</h2><?php foreach ($cart as $item): ?><p><span><?= h($item['variant']['product_name']) ?> × <?= h($item['quantity']) ?></span><strong><?= money($item['line_total']) ?></strong></p><?php endforeach; ?><p><span>Delivery</span><strong><?= money($delivery) ?></strong></p><p class="total"><span>Total</span><strong><?= money($total) ?></strong></p></aside>
</div></section>
