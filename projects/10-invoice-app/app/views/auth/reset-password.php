<div class="logo"><i data-lucide="receipt"></i> <?= e(APP_NAME) ?></div>
<h2>تعیین رمز عبور جدید</h2>
<p class="sub">یک رمز عبور جدید و امن انتخاب کنید.</p>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/reset-password">
    <?= Auth::csrfField() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-group">
        <label class="form-label">رمز عبور جدید</label>
        <input type="password" name="password" class="form-control" required minlength="6" autofocus>
    </div>

    <div class="form-group">
        <label class="form-label">تکرار رمز عبور جدید</label>
        <input type="password" name="password_confirm" class="form-control" required minlength="6">
    </div>

    <button type="submit" class="btn btn-primary btn-block">تغییر رمز عبور</button>
</form>
