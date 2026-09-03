<?php $isFree = !empty($plan['is_free']); ?>
<div class="dash-topbar">
    <h1><?= e($title) ?></h1>
    <a href="<?= APP_URL ?>/admin/plans" class="btn btn-outline">بازگشت</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($isFree): ?>
    <div class="alert alert-success">این پلن، پلن رایگان سیستمی است. قیمت و مدت آن همیشه ۰ می‌ماند، اما بقیه امکانات و سقف فاکتور ماهانه‌اش کاملاً قابل تنظیم است.</div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <form method="POST" action="<?= $mode === 'create' ? APP_URL . '/admin/plans/store' : APP_URL . '/admin/plans/update' ?>">
        <?= Auth::csrfField() ?>
        <?php if ($mode === 'edit'): ?><input type="hidden" name="id" value="<?= (int) $plan['id'] ?>"><?php endif; ?>

        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <div class="form-group" style="flex:2; min-width:200px;">
                <label class="form-label">نام پلن</label>
                <input type="text" name="name" class="form-control" value="<?= e($plan['name'] ?? '') ?>" required placeholder="مثال: شش ماهه">
            </div>
            <div class="form-group" style="flex:1; min-width:120px;">
                <label class="form-label">ترتیب نمایش</label>
                <input type="number" name="sort_order" class="form-control" min="0" value="<?= e((string) ($plan['sort_order'] ?? 0)) ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">توضیحات کوتاه (اختیاری)</label>
            <input type="text" name="description" class="form-control" value="<?= e($plan['description'] ?? '') ?>" placeholder="مثال: بیشترین صرفه‌جویی سالانه">
        </div>

        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:160px;">
                <label class="form-label">آیکون (نام Bootstrap Icons بدون bi-)</label>
                <input type="text" name="icon" class="form-control" dir="ltr" value="<?= e($plan['icon'] ?? 'gem') ?>" placeholder="gem">
                <p style="font-size:11px; color:var(--ink-soft); margin-top:5px;">فهرست آیکون‌ها: <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">icons.getbootstrap.com</a></p>
            </div>
            <div class="form-group" style="flex:1; min-width:160px;">
                <label class="form-label">رنگ اختصاصی پلن</label>
                <input type="color" name="color" class="form-control" value="<?= e($plan['color'] ?? '#0D9488') ?>" style="height:44px; padding:4px;">
            </div>
        </div>

        <?php if (!$isFree): ?>
        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:150px;">
                <label class="form-label">مدت زمان (ماه)</label>
                <input type="number" name="duration_months" class="form-control" min="1" value="<?= e((string) ($plan['duration_months'] ?? 1)) ?>" required>
            </div>
            <div class="form-group" style="flex:1; min-width:150px;">
                <label class="form-label">قیمت نهایی (تومان)</label>
                <input type="number" name="price" class="form-control" min="0" value="<?= e((string) ($plan['price'] ?? '')) ?>" required>
            </div>
            <div class="form-group" style="flex:1; min-width:150px;">
                <label class="form-label">قیمت اصلی (اختیاری)</label>
                <input type="number" name="original_price" class="form-control" min="0" value="<?= e($plan['original_price'] ?? '') ?>">
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">سقف تعداد فاکتور در ماه (خالی = نامحدود)</label>
            <input type="number" name="monthly_invoice_limit" class="form-control" min="0" value="<?= e($plan['monthly_invoice_limit'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">امکانات این پلن</label>
            <div class="plan-permissions-grid">
                <label class="checkbox-row"><input type="checkbox" name="allow_pdf" value="1" <?= !empty($plan['allow_pdf']) ? 'checked' : '' ?>> دانلود PDF</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_excel" value="1" <?= !empty($plan['allow_excel']) ? 'checked' : '' ?>> دانلود Excel</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_image" value="1" <?= !empty($plan['allow_image']) ? 'checked' : '' ?>> دانلود تصویر</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_edit" value="1" <?= !empty($plan['allow_edit']) ? 'checked' : '' ?>> ویرایش فاکتور</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_custom_templates" value="1" <?= !empty($plan['allow_custom_templates']) ? 'checked' : '' ?>> دسترسی به قالب‌های اختصاصی</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_hide_ad" value="1" <?= !empty($plan['allow_hide_ad']) ? 'checked' : '' ?>> امکان حذف پیام پایانی فاکتور</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:10px;"><?= $mode === 'create' ? 'ایجاد پلن' : 'ذخیره تغییرات' ?></button>
    </form>
</div>
