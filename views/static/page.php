<section class="page-hero compact"><div class="container"><p class="eyebrow">Eastern Sweets</p><h1><?= h($title) ?></h1></div></section>
<section class="section"><div class="container static-content">
    <?php if (($slug ?? '') === 'contact'): ?>
        <div class="contact-intro reveal"><p class="eyebrow">We would love to hear from you</p><div><?= nl2br(h($body)) ?></div></div>
        <div class="contact-details">
            <article class="contact-detail-card reveal"><span>01</span><small>Call or WhatsApp</small><a href="tel:<?= h(preg_replace('/[^0-9+]/', '', app_setting('footer_phone', '0310-4490798'))) ?>"><?= h(app_setting('footer_phone', '0310-4490798')) ?></a></article>
            <article class="contact-detail-card reveal"><span>02</span><small>Email us</small><a href="mailto:<?= h(app_setting('contact_email', 'hello@easternsweets.pk')) ?>"><?= h(app_setting('contact_email', 'hello@easternsweets.pk')) ?></a></article>
            <article class="contact-detail-card reveal"><span>03</span><small>Visit the shop</small><strong><?= h(app_setting('footer_address', 'Anees Complex, Gulshan-e-Iqbal, Karachi')) ?></strong></article>
        </div>
        <div class="contact-grid">
            <form class="form-card contact-form reveal" action="mailto:<?= h(app_setting('contact_email', 'hello@easternsweets.pk')) ?>" method="post" enctype="text/plain">
                <p class="eyebrow">Quick enquiry</p><h2>Send a Message</h2>
                <div class="split-fields"><label>Name<input name="name" required placeholder="Your name"></label><label>Phone<input name="phone" placeholder="03xx xxxxxxx"></label></div>
                <label>Email<input name="email" type="email" required placeholder="you@example.com"></label><label>Message<textarea name="message" rows="5" required placeholder="How can we help?"></textarea></label><button class="btn btn-primary" type="submit">Send Message</button>
            </form>
            <div class="map-card contact-location reveal"><div><p class="eyebrow">Find us</p><h2><?= h(app_setting('contact_location_title', 'Eastern Sweets, Karachi')) ?></h2><p><?= h(app_setting('footer_address', 'Anees Complex, Gulshan-e-Iqbal, Karachi')) ?></p><p><?= h(app_setting('footer_hours', 'Open daily')) ?></p><a class="btn btn-outline" href="<?= h(app_setting('contact_map_link', 'https://maps.google.com/')) ?>" target="_blank" rel="noopener">Open in Maps</a></div></div>
        </div>
    <?php else: ?>
        <div class="static-copy reveal"><?= nl2br(h($body)) ?></div>
    <?php endif; ?>
</div></section>
