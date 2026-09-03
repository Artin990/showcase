<div class="dash-topbar">
    <h1>ویرایش پروفایل</h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:560px; margin-bottom:20px;">
    <h3 style="margin-bottom:18px; font-size:16px;">اطلاعات حساب</h3>
    <form method="POST" action="<?= APP_URL ?>/profile">
        <?= Auth::csrfField() ?>
        <div class="form-group">
            <label class="form-label">نام و نام خانوادگی</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">ایمیل</label>
            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled dir="ltr">
        </div>
        <div class="form-group">
            <label class="form-label">شماره موبایل</label>
            <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>" dir="ltr" required>
        </div>
        <div class="form-group">
            <label class="form-label">نام فروشگاه یا شرکت</label>
            <input type="text" name="store_name" class="form-control" value="<?= e($user['store_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">نوع فعالیت</label>
            <div class="radio-pill-group">
                <label class="radio-pill">
                    <input type="radio" name="business_type" value="shop" <?= ($user['business_type'] ?? '') === 'shop' ? 'checked' : '' ?> required>
                    <span>فروشگاه</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="business_type" value="company" <?= ($user['business_type'] ?? '') === 'company' ? 'checked' : '' ?>>
                    <span>شرکت</span>
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
    </form>
</div>

<div class="card" style="max-width:560px;">
    <h3 style="margin-bottom:18px; font-size:16px;">تغییر رمز عبور</h3>
    <form method="POST" action="<?= APP_URL ?>/profile/change-password">
        <?= Auth::csrfField() ?>
        <div class="form-group">
            <label class="form-label">رمز عبور فعلی</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">رمز عبور جدید</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
            <label class="form-label">تکرار رمز عبور جدید</label>
            <input type="password" name="new_password_confirm" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-outline">تغییر رمز عبور</button>
    </form>
</div>
