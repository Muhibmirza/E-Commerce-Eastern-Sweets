<div class="stats-grid">
    <div class="stat-card"><span>O</span><p>Total Orders</p><strong><?= h($stats['orders']) ?></strong></div>
    <div class="stat-card"><span>R</span><p>Total Revenue</p><strong><?= money($stats['revenue']) ?></strong></div>
    <div class="stat-card"><span>C</span><p>Total Customers</p><strong><?= h($stats['customers']) ?></strong></div>
    <div class="stat-card"><span>P</span><p>Pending Orders</p><strong><?= h($stats['pending']) ?></strong></div>
</div>
<div class="admin-grid">
    <section class="table-card">
        <div class="card-head"><h2>Sales Overview</h2><div class="segmented"><button>Daily</button><button>Weekly</button><button>Monthly</button></div></div>
        <div class="chart-bars"><?php foreach ($chart as $row): $height=min(100,max(8,(float)$row['total']/100)); ?><div style="height:<?= h($height) ?>%"><span><?= money($row['total']) ?></span></div><?php endforeach; ?></div>
    </section>
    <section class="table-card">
        <div class="card-head"><h2>Low Stock</h2></div>
        <table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Stock</th></tr></thead><tbody><?php foreach ($lowStock as $row): ?><tr><td><?= h($row['name']) ?></td><td><?= h($row['category_name']) ?></td><td class="num"><?= h($row['stock']) ?></td></tr><?php endforeach; ?></tbody></table>
    </section>
</div>
<section class="table-card">
    <div class="card-head"><h2>Recent Orders</h2><a class="btn btn-outline btn-sm" href="<?= url('admin/orders') ?>">View All</a></div>
    <table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= h($order['order_number']) ?></td><td><?= h($order['customer_name']) ?></td><td class="num"><?= money($order['total_amount']) ?></td><td class="center"><?php $status=$order['status']; require __DIR__ . '/../partials/status-badge.php'; ?></td><td class="actions"><a class="icon-button" href="<?= url('admin/order', ['id'=>$order['id']]) ?>">↗</a></td></tr><?php endforeach; ?></tbody></table>
</section>
