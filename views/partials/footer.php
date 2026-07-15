<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <img class="footer-logo" src="<?= asset(app_setting('site_logo', 'public/assets/images/logo.png')) ?>" alt="Eastern Sweets">
            <p><?= h(app_setting('footer_about', 'Fresh mithai, bakery favourites, nimco, and celebration-ready gift boxes from a Karachi classic.')) ?></p>
        </div>
        <div>
            <h3>Quick Links</h3>
            <a href="<?= url('shop') ?>">Shop</a>
            <a href="<?= url('about') ?>">About</a>
            <a href="<?= url('faqs') ?>">FAQs</a>
            <a href="<?= url('refund') ?>">Return Policy</a>
        </div>
        <div>
            <h3>Contact</h3>
            <p><?= h(app_setting('footer_address', 'Anees Complex, Gulshan-e-Iqbal, Karachi')) ?></p>
            <p><?= h(app_setting('footer_phone', '0310-4490798')) ?></p>
            <p><?= h(app_setting('footer_hours', '10:00 AM - 11:30 PM')) ?></p>
        </div>
        <div>
            <h3>Payments</h3>
            <div class="payment-mini"><?= h(app_setting('footer_payments', 'COD, Visa, Mastercard, JazzCash, Easypaisa')) ?></div>
            <p class="socials"><?= h(app_setting('footer_socials', 'Instagram, Facebook, WhatsApp')) ?></p>
        </div>
    </div>
    <div class="footer-bottom">&copy; <?= date('Y') ?> Eastern Sweets. Since 1969.</div>
</footer>
