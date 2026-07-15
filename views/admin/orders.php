<section class="table-card">
    <div class="card-head"><h2>Order Management</h2></div>
    <div class="status-tabs">
        <?php foreach ([''=>'All','pending'=>'Pending','processing'=>'Processing','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $key=>$label): ?>
            <a class="<?= $status===$key?'is-active':'' ?>" href="<?= url('admin/orders', ['status'=>$key]) ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
    </div>
    <form class="admin-filters admin-filters-wide" method="get" action="<?= url('admin/orders') ?>">
        <input type="hidden" name="status" value="<?= h($_GET['status'] ?? '') ?>">
        <input name="q" placeholder="Order ID, customer, phone" value="<?= h($_GET['q'] ?? '') ?>">
        <input name="date_from" type="date" value="<?= h($_GET['date_from'] ?? '') ?>">
        <input name="date_to" type="date" value="<?= h($_GET['date_to'] ?? '') ?>">
        <button class="btn btn-outline btn-sm">Filter</button>
    </form>
    <div class="responsive-table">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Total</th><th>Payment</th><th>Status</th><th class="action-col">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= h($order['order_number']) ?><span class="muted block"><?= h(date('M d, Y h:i A', strtotime($order['created_at']))) ?></span></td>
                        <td><?= h($order['customer_name']) ?></td>
                        <td><?= h($order['customer_phone']) ?></td>
                        <td class="num"><?= money($order['total_amount']) ?></td>
                        <td><?= h($order['payment_method']) ?></td>
                        <td class="center"><?php $status=$order['status']; require __DIR__ . '/../partials/status-badge.php'; ?></td>
                        <td class="actions"><a class="icon-button" href="<?= url('admin/order', ['id'=>$order['id']]) ?>">&#8599;</a><a class="icon-button" href="<?= url('admin/invoice', ['id'=>$order['id']]) ?>">&#9113;</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$orders): ?><div class="empty-state">No orders found.</div><?php endif; ?>
</section>
