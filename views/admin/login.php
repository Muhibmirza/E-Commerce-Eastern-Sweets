<section class="auth-card">
    <h1>Admin Login</h1>
    <p class="muted">Hidden admin access for Eastern Sweets staff.</p>
    <form method="post" action="<?= url('admin/login') ?>/" autocomplete="off">
        <?= csrf_field() ?>
        <input class="anti-autofill" type="text" name="fake_user" autocomplete="off" tabindex="-1" aria-hidden="true">
        <input class="anti-autofill" type="password" name="fake_pass" autocomplete="new-password" tabindex="-1" aria-hidden="true">
        <label>Email<input name="email" type="email" required autocomplete="off" value=""></label>
        <label>Password<input name="password" type="password" required autocomplete="new-password" value=""></label>
        <button class="btn btn-primary wide" type="submit">Login</button>
    </form>
</section>
