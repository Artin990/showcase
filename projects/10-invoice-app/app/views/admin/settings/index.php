<div class="dash-topbar">
    <h1>تنظیمات سایت</h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div style="display:flex; gap:20px; flex-wrap:wrap;">

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">هویت سایت (نام، لوگو، فاوآیکون)</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/settings/general" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <div class="form-group">
                <label class="form-label">نام سایت</label>
                <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">لوگو (اختیاری - PNG/JPG/SVG)</label>
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="<?= APP_URL . '/uploads/branding/' . e($settings['site_logo']) ?>" style="height:36px; display:block; margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            </div>
            <div class="form-group">
                <label class="form-label">فاوآیکون (اختیاری - PNG/ICO)</label>
                <?php if (!empty($settings['site_favicon'])): ?>
                    <img src="<?= APP_URL . '/uploads/branding/' . e($settings['site_favicon']) ?>" style="height:24px; display:block; margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" name="favicon" class="form-control" accept="image/png,image/x-icon,image/svg+xml">
            </div>
            <button type="submit" class="btn btn-primary">ذخیره</button>
        </form>
    </div>

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">اطلاعات پرداخت (کارت‌به‌کارت)</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/settings/payment">
            <?= Auth::csrfField() ?>
            <div class="form-group">
                <label class="form-label">شماره کارت</label>
                <input type="text" name="card_number" class="form-control" dir="ltr" value="<?= e($settings['card_number'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">نام صاحب کارت</label>
                <input type="text" name="card_holder_name" class="form-control" value="<?= e($settings['card_holder_name'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">ذخیره</button>
        </form>
        <p style="font-size:12.5px; color:var(--ink-soft); margin-top:14px; padding-top:14px; border-top:1px dashed var(--border);">
            برای تغییر قیمت و مدت پلن‌های اشتراک، به بخش <a href="<?= APP_URL ?>/admin/plans" style="font-weight:800;">مدیریت پلن‌های اشتراک</a> بروید.
        </p>
    </div>

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">اطلاعات تماس</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/settings/contact">
            <?= Auth::csrfField() ?>
            <div class="form-group">
                <label class="form-label">شماره تماس</label>
                <input type="text" name="contact_phone" class="form-control" dir="ltr" value="<?= e($settings['contact_phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">آیدی تلگرام (بدون @)</label>
                <input type="text" name="contact_telegram" class="form-control" dir="ltr" value="<?= e($settings['contact_telegram'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">آیدی اینستاگرام (بدون @)</label>
                <input type="text" name="contact_instagram" class="form-control" dir="ltr" value="<?= e($settings['contact_instagram'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">ذخیره</button>
        </form>
    </div>

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">نمونه فاکتورها (Landing Page)</h3>
        <p style="font-size:13px; color:var(--ink-soft); margin-bottom:16px; line-height:1.9;">
            تصاویری که در بخش «نمونه فاکتورها» صفحه اصلی سایت نمایش داده می‌شوند را از اینجا مدیریت کنید.
        </p>
        <a href="<?= APP_URL ?>/admin/samples" class="btn btn-primary">مدیریت گالری تصاویر</a>
    </div>

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">پلن‌های اشتراک</h3>
        <p style="font-size:13px; color:var(--ink-soft); margin-bottom:16px; line-height:1.9;">
            قیمت، مدت، سقف فاکتور ماهانه، و تمام امکانات هر پلن (شامل پلن رایگان) از بخش
            <a href="<?= APP_URL ?>/admin/plans" style="font-weight:800;">مدیریت پلن‌های اشتراک</a> مدیریت می‌شود.
        </p>
        <a href="<?= APP_URL ?>/admin/plans" class="btn btn-primary">مدیریت پلن‌ها</a>
    </div>

    <div class="card" style="flex:1; min-width:320px;">
        <h3 style="font-size:15px; margin-bottom:16px;">پیام پایانی فاکتور</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/settings/ad-text">
            <?= Auth::csrfField() ?>
            <div class="form-group">
                <label class="form-label">متن نمایش‌داده‌شده در پایین فاکتورها</label>
                <textarea name="invoice_ad_text" class="form-control" rows="3"><?= e($settings['invoice_ad_text'] ?? '') ?></textarea>
                <p style="font-size:11.5px; color:var(--ink-soft); margin-top:6px;">کاربران پلن‌های دارای مجوز «حذف پیام پایانی» می‌توانند این متن را برای فاکتورهای خود غیرفعال کنند.</p>
            </div>
            <button type="submit" class="btn btn-primary">ذخیره</button>
        </form>
    </div>

</div>
