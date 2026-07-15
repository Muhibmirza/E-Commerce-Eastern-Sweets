<section class="page-hero compact"><div class="container"><p class="eyebrow">Eastern Sweets</p><h1><?= h($title) ?></h1></div></section>
<section class="section"><div class="container static-content">
    <p><?= nl2br(h($body)) ?></p>
    <?php if (($slug ?? '') === 'contact'): ?>
        <div class="contact-grid"><form class="form-card"><h2>Send a Message</h2><label>Name<input></label><label>Email<input type="email"></label><label>Message<textarea rows="4"></textarea></label><button class="btn btn-primary" type="button">Send Message</button></form><div class="map-card">Eastern Sweets<br><?= h(app_setting('footer_address', 'Anees Complex, Gulshan-e-Iqbal, Karachi')) ?><br><?= h(app_setting('footer_phone', '0310-4490798')) ?></div></div>
    <?php endif; ?>
</div></section>
