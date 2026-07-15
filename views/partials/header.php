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
            <a class="icon-link" href="<?= current_user() ? url('account') : url('login') ?>" aria-label="Account">Account</a>
            <a class="cart-link" href="<?= url('cart') ?>" aria-label="Cart">Cart<span data-cart-count><?= cart_count() ?></span></a>
        </div>
    </div>
</header>
