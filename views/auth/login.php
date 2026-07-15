<section class="auth-card">
    <h1>Welcome back</h1>
    <form method="post" action="<?= url('login') ?>"><?= csrf_field() ?><label>Email<input name="email" type="email" required></label><label>Password<input name="password" type="password" required></label><button class="btn btn-primary wide" type="submit">Login</button></form>
    <p><a href="<?= url('register') ?>">Create an account</a> · <a href="#">Forgot Password</a></p>
</section>
