<section class="page-hero compact">
    <div class="container">
        <p class="eyebrow">Account</p>
        <h1>Hello, <?= h($user['name']) ?></h1>
    </div>
</section>

<section class="section">
    <div class="container account-layout">
        <aside class="account-sidebar" data-account-tabs>
            <button class="is-active" type="button" data-account-tab="profile">Profile Info</button>
            <button type="button" data-account-tab="orders">Order History</button>
            <button type="button" data-account-tab="addresses">Saved Addresses</button>
            <button type="button" data-account-tab="password">Change Password</button>
            <a href="<?= url('logout') ?>" data-confirm-logout>Logout</a>
        </aside>

        <div class="account-content">
            <section class="account-panel is-active" data-account-panel="profile">
                <div class="form-card">
                    <h2>Profile Info</h2>
                    <div class="profile-grid">
                        <p><span>Name</span><strong><?= h($user['name']) ?></strong></p>
                        <p><span>Email</span><strong><?= h($user['email']) ?></strong></p>
                        <p><span>Phone</span><strong><?= h($user['phone']) ?></strong></p>
                    </div>
                    <form class="account-form" method="post" action="<?= url('account/update') ?>">
                        <?= csrf_field() ?>
                        <div class="form-grid">
                            <label>Name<input name="name" value="<?= h($user['name']) ?>" required></label>
                            <label>Email<input name="email" type="email" value="<?= h($user['email']) ?>" required></label>
                            <label>Phone<input name="phone" value="<?= h($user['phone']) ?>" required></label>
                        </div>
                        <button class="btn btn-primary" type="submit">Save Profile</button>
                    </form>
                </div>
            </section>

            <section class="account-panel" data-account-panel="orders">
                <div class="table-card">
                    <h2>Order History</h2>
                    <div class="responsive-table">
                        <table class="data-table">
                            <thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= h($order['order_number']) ?></td>
                                    <td><?= h(date('M d, Y', strtotime($order['created_at']))) ?></td>
                                    <td class="num"><?= h($order['item_count']) ?></td>
                                    <td class="num"><?= money($order['total_amount']) ?></td>
                                    <td class="center"><?php $status=$order['status']; require __DIR__ . '/../partials/status-badge.php'; ?></td>
                                    <td class="actions"><a class="btn btn-outline btn-sm" href="<?= url('track') ?>">Track</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$orders): ?><div class="empty-state">No orders yet.</div><?php endif; ?>
                </div>
            </section>

            <section class="account-panel" data-account-panel="addresses">
                <form class="form-card" method="post" action="<?= url('account/address') ?>">
                    <?= csrf_field() ?>
                    <h2>Saved Address</h2>
                    <label>Default Delivery Address
                        <textarea name="default_address" rows="5" placeholder="House, street, area, city"><?= h($user['default_address'] ?? '') ?></textarea>
                    </label>
                    <button class="btn btn-primary" type="submit">Save Address</button>
                </form>
            </section>

            <section class="account-panel" data-account-panel="password">
                <form class="form-card" method="post" action="<?= url('account/password') ?>">
                    <?= csrf_field() ?>
                    <h2>Change Password</h2>
                    <div class="form-grid">
                        <label>Current Password<input name="current_password" type="password" required></label>
                        <label>New Password<input name="new_password" type="password" minlength="8" required></label>
                    </div>
                    <button class="btn btn-primary" type="submit">Change Password</button>
                </form>
            </section>
        </div>
    </div>
</section>
