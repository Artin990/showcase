<div class="dash-topbar">
    <h1>افزودن مدیر جدید</h1>
    <a href="<?= APP_URL ?>/admin/admins" class="btn btn-outline">بازگشت</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:460px;">
    <form method="POST" action="<?= APP_URL ?>/admin/admins/store">
        <?= Auth::csrfField() ?>
        <div class="form-group">
            <label class="form-label">نام</label>
            <input type="text" name="name" class="form-control" value="<?= e($old['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">ایمیل</label>
            <input type="email" name="email" class="form-control" dir="ltr" value="<?= e($old['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">رمز عبور (حداقل ۸ کاراکتر)</label>
            <input type="password" name="password" class="form-control" required minlength="8">
        </div>
        <p style="font-size:12.5px; color:var(--ink-soft); margin-bottom:16px;">
            مدیران جدید همیشه با نقش «مدیر» (نه مدیر ارشد) ایجاد می‌شوند و به این بخش دسترسی نخواهند داشت.
        </p>
        <button type="submit" class="btn btn-primary">ایجاد مدیر</button>
    </form>
</div>
