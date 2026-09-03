<div class="logo"><i data-lucide="receipt"></i> <?= e(APP_NAME) ?></div>
<h2>فراموشی رمز عبور</h2>
<p class="sub">ایمیل خود را وارد کنید تا لینک بازیابی برایتان ارسال شود.</p>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($link = getFlash('debug_reset_link')): ?>
    <div class="alert alert-success">
        حالت توسعه فعال است — لینک بازیابی (چون ایمیل روی سرور تست ارسال نمی‌شود):<br>
        <a href="<?= e($link) ?>" dir="ltr"><?= e($link) ?></a>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/forgot-password">
    <?= Auth::csrfField() ?>
    <div class="form-group">
        <label class="form-label">ایمیل</label>
        <input type="email" name="email" class="form-control" required dir="ltr" autofocus>
    </div>
    <button type="submit" class="btn btn-primary btn-block">ارسال لینک بازیابی</button>
</form>

<div class="auth-links">
    <a href="<?= APP_URL ?>/login">بازگشت به صفحه ورود</a>
</div>
