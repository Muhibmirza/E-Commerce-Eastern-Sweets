<div class="stats-grid">
    <div class="stat-card"><span>O</span><p>Total Orders</p><strong><?= h($stats['orders']) ?></strong></div>
    <div class="stat-card"><span>R</span><p>Total Revenue</p><strong><?= money($stats['revenue']) ?></strong></div>
    <div class="stat-card"><span>C</span><p>Total Customers</p><strong><?= h($stats['customers']) ?></strong></div>
    <div class="stat-card"><span>P</span><p>Pending Orders</p><strong><?= h($stats['pending']) ?></strong></div>
</div>
<div class="admin-grid">
    <section class="table-card">
        <div class="card-head"><div><h2>Sales Overview</h2><p class="muted">Revenue from non-cancelled orders</p></div><div class="segmented"><?php foreach (['daily'=>'7 Days','weekly'=>'4 Weeks','monthly'=>'6 Months'] as $key=>$label): ?><a class="<?= $chartPeriod === $key ? 'is-active' : '' ?>" href="<?= url('admin/dashboard', ['period'=>$key]) ?>"><?= h($label) ?></a><?php endforeach; ?></div></div>
        <?php $maxSale = max(array_map(static fn($row) => (float)$row['total'], $chart) ?: [0]); ?>
        <div class="chart-bars" style="--chart-columns:<?= count($chart) ?>" role="img" aria-label="Sales revenue chart">
            <?php foreach ($chart as $row): $height = $maxSale > 0 ? max(3, ((float)$row['total'] / $maxSale) * 100) : 3; ?>
                <div class="chart-column" title="<?= h($row['label']) ?>: <?= money($row['total']) ?> from <?= h($row['orders']) ?> order(s)">
                    <span class="chart-value"><?= money($row['total']) ?></span>
                    <i style="height:<?= h(round($height, 2)) ?>%"></i>
                    <small><?= h($row['label']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="table-card">
        <div class="card-head"><div><h2>Low Stock</h2><p class="muted">Products with 15 or fewer units</p></div><a class="btn btn-outline btn-sm" href="<?= url('admin/products') ?>">Manage</a></div>
        <div class="responsive-table"><table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Status</th></tr></thead><tbody><?php if (!$lowStock): ?><tr><td colspan="4" class="empty-cell">All products are sufficiently stocked.</td></tr><?php else: foreach ($lowStock as $row): ?><tr><td><?= h($row['name']) ?></td><td><?= h($row['category_name']) ?></td><td class="num"><?= h($row['stock']) ?></td><td><span class="stock-pill <?= (int)$row['stock'] === 0 ? 'is-out' : '' ?>"><?= (int)$row['stock'] === 0 ? 'Out of stock' : 'Low' ?></span></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>
</div>
<section class="table-card">
    <div class="card-head"><h2>Recent Orders</h2><a class="btn btn-outline btn-sm" href="<?= url('admin/orders') ?>">View All</a></div>
    <table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= h($order['order_number']) ?></td><td><?= h($order['customer_name']) ?></td><td class="num"><?= money($order['total_amount']) ?></td><td class="center"><?php $status=$order['status']; require __DIR__ . '/../partials/status-badge.php'; ?></td><td class="actions"><a class="icon-button" href="<?= url('admin/order', ['id'=>$order['id']]) ?>">↗</a></td></tr><?php endforeach; ?></tbody></table>
</section>
