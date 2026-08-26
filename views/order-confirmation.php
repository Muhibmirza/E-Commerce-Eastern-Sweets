<section class="section"><div class="container narrow confirmation">
    <img src="<?= asset('public/assets/images/logo.png') ?>" alt="Eastern Sweets">
    <p class="eyebrow"><?= ($order['payment_method'] ?? '') === 'cod' ? 'Order confirmed' : 'Payment successful' ?></p>
    <h1>Thank you, <?= h($order['customer_name']) ?>.</h1>
    <p>Your order <strong><?= h($order['order_number']) ?></strong> has been received. Estimated delivery is 45-75 minutes after confirmation.</p>
    <div class="button-row center"><a class="btn btn-primary" href="<?= url('track') ?>">Track Order</a><a class="btn btn-outline" href="<?= url('shop') ?>">Continue Shopping</a></div>
</div></section>
