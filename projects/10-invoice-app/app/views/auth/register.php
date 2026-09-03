<div class="logo"><i data-lucide="receipt"></i> <?= e(APP_NAME) ?></div>
<h2>ساخت حساب کاربری</h2>
<p class="sub">رایگان ثبت‌نام کنید و همین حالا شروع به صدور فاکتور کنید.</p>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/register">
    <?= Auth::csrfField() ?>

    <div class="form-group">
        <label class="form-label">نام و نام خانوادگی</label>
        <input type="text" name="name" class="form-control" value="<?= e($old['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label class="form-label">ایمیل</label>
        <input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? '') ?>" required dir="ltr">
    </div>

    <div class="form-group">
        <label class="form-label">شماره موبایل</label>
        <input type="text" name="phone" class="form-control" value="<?= e($old['phone'] ?? '') ?>" dir="ltr" required>
    </div>

    <div class="form-group">
        <label class="form-label">نام فروشگاه یا شرکت</label>
        <input type="text" name="store_name" class="form-control" value="<?= e($old['store_name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label class="form-label">نوع فعالیت</label>
        <div class="radio-pill-group">
            <label class="radio-pill">
                <input type="radio" name="business_type" value="shop" <?= ($old['business_type'] ?? '') === 'shop' ? 'checked' : '' ?> required>
                <span>فروشگاه</span>
            </label>
            <label class="radio-pill">
                <input type="radio" name="business_type" value="company" <?= ($old['business_type'] ?? '') === 'company' ? 'checked' : '' ?>>
                <span>شرکت</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">رمز عبور</label>
        <input type="password" name="password" class="form-control" required minlength="6">
    </div>

    <div class="form-group">
        <label class="form-label">تکرار رمز عبور</label>
        <input type="password" name="password_confirm" class="form-control" required minlength="6">
    </div>

    <button type="submit" class="btn btn-primary btn-block">ثبت‌نام</button>
</form>

<div class="auth-links">
    قبلا ثبت‌نام کرده‌اید؟ <a href="<?= APP_URL ?>/login">وارد شوید</a>
</div>
