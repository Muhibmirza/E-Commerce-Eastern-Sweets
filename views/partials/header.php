<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= url('home') ?>"><img src="<?= asset(app_setting('site_logo', 'public/assets/images/logo.png')) ?>" alt="Eastern Sweets"></a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu">Menu</button>
        <nav class="site-nav" data-nav>
            <a class="<?= active_route('home') ?>" href="<?= url('home') ?>">Home</a>
            <a class="<?= active_route('shop') ?>" href="<?= url('shop') ?>">Menu</a>
            <a class="<?= active_route('about') ?>" href="<?= url('about') ?>">About</a>
            <a class="<?= active_route('contact') ?>" href="<?= url('contact') ?>">Contact</a>
            <a class="<?= active_route('track') ?>" href="<?= url('track') ?>">Track</a>
        </nav>
        <form class="nav-search" action="<?= url('shop') ?>" method="get">
            <input name="q" type="search" placeholder="Search mithai..." value="<?= h($_GET['q'] ?? '') ?>">
        </form>
        <div class="nav-actions">
            <a class="icon-link" href="<?= current_user() ? url('account') : url('login') ?>" aria-label="Account" title="Account">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 12a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Zm-7.25 8.5c.45-4 3.13-6.25 7.25-6.25s6.8 2.25 7.25 6.25"/></svg>
            </a>
            <a class="cart-link" href="<?= url('cart') ?>" aria-label="Cart" title="Cart">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3.5 4.5h2l1.8 9.15a2 2 0 0 0 2 1.6h7.85a2 2 0 0 0 1.95-1.55l1.05-4.7H6.4M9.5 20a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Zm8 0a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z"/></svg>
                <span data-cart-count><?= cart_count() ?></span>
            </a>
        </div>
    </div>
</header>
