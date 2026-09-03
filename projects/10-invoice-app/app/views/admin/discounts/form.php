<div class="dash-topbar">
    <h1><?= e($title) ?></h1>
    <a href="<?= APP_URL ?>/admin/discounts" class="btn btn-outline">بازگشت</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:480px;">
    <form method="POST" action="<?= $mode === 'create' ? APP_URL . '/admin/discounts/store' : APP_URL . '/admin/discounts/update' ?>">
        <?= Auth::csrfField() ?>
        <?php if ($mode === 'edit'): ?><input type="hidden" name="id" value="<?= (int) $code['id'] ?>"><?php endif; ?>

        <div class="form-group">
            <label class="form-label">کد تخفیف</label>
            <input type="text" name="code" class="form-control" dir="ltr" style="text-transform:uppercase;"
                   value="<?= e($code['code'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">نوع تخفیف</label>
            <select name="type" class="form-control">
                <option value="percent" <?= ($code['type'] ?? '') === 'percent' ? 'selected' : '' ?>>درصدی (٪)</option>
                <option value="fixed" <?= ($code['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>مبلغ ثابت (تومان)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">مقدار تخفیف</label>
            <input type="number" name="value" class="form-control" min="0" step="0.01" value="<?= e((string) ($code['value'] ?? '')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">سقف تعداد استفاده (خالی = نامحدود)</label>
            <input type="number" name="max_uses" class="form-control" min="1" value="<?= e((string) ($code['max_uses'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label">تاریخ انقضا (شمسی - خالی = بدون انقضا)</label>
            <input type="text" name="expires_at" class="form-control" placeholder="مثال: ۱۴۰۴/۰۶/۱۰"
                   value="<?= e(!empty($code['expires_at']) ? toJalali($code['expires_at']) : '') ?>">
        </div>

        <button type="submit" class="btn btn-primary"><?= $mode === 'create' ? 'ایجاد کد تخفیف' : 'ذخیره تغییرات' ?></button>
    </form>
</div>
