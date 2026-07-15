<section class="auth-card">
    <h1>Create account</h1>
    <form method="post" action="<?= url('register') ?>"><?= csrf_field() ?><label>Name<input name="name" required></label><label>Email<input name="email" type="email" required></label><label>Phone<input name="phone" required></label><label>Password<input name="password" type="password" minlength="8" required></label><button class="btn btn-primary wide" type="submit">Sign Up</button></form>
    <p><a href="<?= url('login') ?>">Already have an account?</a></p>
</section>
