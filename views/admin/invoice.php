<article class="invoice">
    <header><img src="<?= asset('public/assets/images/logo.png') ?>" alt="Eastern Sweets"><div><h1>Invoice</h1><p><?= h($order['order_number']) ?></p></div></header>
    <section class="invoice-meta"><div><strong>Customer</strong><p><?= h($order['customer_name']) ?><br><?= h($order['customer_phone']) ?><br><?= h($order['delivery_address']) ?>, <?= h($order['area']) ?></p></div><div><strong>Date</strong><p><?= h(date('M d, Y h:i A', strtotime($order['created_at']))) ?></p><strong>Status</strong><p><?= h(ucwords(str_replace('_',' ',$order['status']))) ?></p></div></section>
    <table class="data-table"><thead><tr><th>Item</th><th>Variant</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach ($order['items'] as $item): ?><tr><td><?= h($item['product_name']) ?></td><td><?= h($item['variant_name']) ?></td><td><?= money($item['unit_price']) ?></td><td><?= h($item['quantity']) ?></td><td><?= money($item['line_total']) ?></td></tr><?php endforeach; ?></tbody></table>
    <footer><p>Subtotal: <?= money($order['subtotal']) ?></p><p>Delivery: <?= money($order['delivery_charge']) ?></p><h2>Total: <?= money($order['total_amount']) ?></h2><p>Thank you for ordering from Eastern Sweets.</p></footer>
    <button class="btn btn-primary print-button" onclick="window.print()">Print Invoice</button>
</article>
