<div class="logo"><i data-lucide="receipt"></i> <?= e(APP_NAME) ?></div>
<h2>ورود به حساب</h2>
<p class="sub">برای مدیریت فاکتورهای خود وارد شوید.</p>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/login">
    <?= Auth::csrfField() ?>

    <div class="form-group">
        <label class="form-label">ایمیل</label>
        <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" required dir="ltr" autofocus>
    </div>

    <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">ورود</button>
</form>

<div class="auth-links">
    <a href="<?= APP_URL ?>/forgot-password">رمز عبور خود را فراموش کرده‌اید؟</a>
    <br><br>
    حساب کاربری ندارید؟ <a href="<?= APP_URL ?>/register">ثبت‌نام کنید</a>
</div>
