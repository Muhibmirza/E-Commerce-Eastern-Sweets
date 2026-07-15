<section class="page-hero compact"><div class="container"><p class="eyebrow">Order status</p><h1>Track Your Order</h1></div></section>
<section class="section"><div class="container narrow">
    <form class="form-card" method="post" action="<?= url('track') ?>"><?= csrf_field() ?><label>Order ID<input name="order_number" placeholder="ES-260709-1234" required></label><label>Phone Number<input name="phone" required></label><button class="btn btn-primary" type="submit">Track Order</button></form>
    <?php if ($order): $steps=['pending'=>'Order Placed','confirmed'=>'Confirmed','processing'=>'Preparing','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered']; $seen=true; ?>
        <div class="timeline"><?php foreach ($steps as $key=>$label): ?><div class="<?= $seen ? 'done' : '' ?>"><span></span><strong><?= h($label) ?></strong></div><?php if ($key===$order['status']) $seen=false; endforeach; ?></div>
    <?php elseif ($_SERVER['REQUEST_METHOD']==='POST'): ?><div class="empty-state">No matching order found.</div><?php endif; ?>
</div></section>
