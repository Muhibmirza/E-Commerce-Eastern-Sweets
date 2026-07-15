<?php
$subtotal = array_sum(array_column($cart, 'line_total'));
$discount = $coupon ? ($coupon['discount_type'] === 'percent' ? $subtotal * ((float)$coupon['discount_value'] / 100) : (float)$coupon['discount_value']) : 0;
$delivery = $subtotal >= 2000 || $subtotal == 0 ? 0 : (float)app_setting('delivery_charge', '180');
$total = max(0, $subtotal - $discount + $delivery);
?>
<section class="page-hero compact"><div class="container"><p class="eyebrow">Review</p><h1>Your Cart</h1></div></section>
<section class="section"><div class="container cart-layout">
    <div class="table-card">
        <table class="data-table cart-table"><thead><tr><th>Item</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead><tbody>
            <?php foreach ($cart as $item): $v=$item['variant']; ?>
                <tr data-cart-row="<?= h($v['id']) ?>">
                    <td><div class="cart-product"><img src="<?= asset($v['image_path']) ?>" alt=""><div><strong><?= h($v['product_name']) ?></strong><span><?= h($v['variant_name']) ?></span></div></div></td>
                    <td><?= money($v['price']) ?></td>
                    <td><form class="cart-update" action="<?= url('cart/update') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="variant_id" value="<?= h($v['id']) ?>"><input name="quantity" type="number" min="0" value="<?= h($item['quantity']) ?>"></form></td>
                    <td><?= money($item['line_total']) ?></td>
                    <td><button class="icon-button" data-remove-cart="<?= h($v['id']) ?>">×</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table>
        <?php if (!$cart): ?><div class="empty-state">Your cart is empty.</div><?php endif; ?>
    </div>
    <aside class="summary-card">
        <h2>Order Summary</h2>
        <p><span>Subtotal</span><strong><?= money($subtotal) ?></strong></p><p><span>Delivery</span><strong><?= money($delivery) ?></strong></p><p><span>Discount</span><strong>-<?= money($discount) ?></strong></p><p class="total"><span>Total</span><strong><?= money($total) ?></strong></p>
        <form action="<?= url('coupon/apply') ?>" method="post" class="coupon-form"><?= csrf_field() ?><input name="coupon_code" placeholder="Coupon code"><button class="btn btn-outline" type="submit">Apply</button></form>
        <a class="btn btn-primary wide" href="<?= url('checkout') ?>">Proceed to Checkout</a>
        <a class="continue-link" href="<?= url('shop') ?>">Continue Shopping</a>
    </aside>
</div></section>
