<div class="logo"><i data-lucide="shield-check"></i> پنل مدیریت</div>
<h2>ورود مدیر</h2>
<p class="sub">این بخش فقط برای مدیر سیستم است.</p>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/admin/login">
    <?= Auth::csrfField() ?>
    <div class="form-group">
        <label class="form-label">ایمیل</label>
        <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" required dir="ltr" autofocus>
    </div>
    <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">ورود به پنل مدیریت</button>
</form>

<div class="auth-links">
    <a href="<?= APP_URL ?>/login">بازگشت به سایت اصلی</a>
</div>
