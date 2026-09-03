<div class="dash-topbar">
    <h1>داشبورد مدیریت</h1>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">تعداد کاربران</div>
        <div class="value num"><?= formatNumber($userCount) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">تعداد فاکتورها</div>
        <div class="value num"><?= formatNumber($invoiceCount) ?></div>
    </div>
    <div class="stat-card" style="border-color:var(--gold);">
        <div class="label">رسیدهای در انتظار بررسی</div>
        <div class="value num" style="color:var(--color-primary-hover);"><?= formatNumber($pendingReceipts) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">کدهای تخفیف فعال</div>
        <div class="value num"><?= formatNumber($activeDiscountCount) ?></div>
    </div>
</div>

<?php if ($pendingReceipts > 0): ?>
<div class="alert alert-success">
    <?= formatNumber($pendingReceipts) ?> رسید پرداخت در انتظار بررسی است.
    <a href="<?= APP_URL ?>/admin/receipts" style="font-weight:800;">مشاهده و بررسی</a>
</div>
<?php endif; ?>

<div class="quick-links-grid">
    <a href="<?= APP_URL ?>/admin/users" class="quick-link-card">
        <div class="qlc-icon"><i data-lucide="users"></i></div>
        <div class="qlc-title">مدیریت کاربران</div>
        <div class="qlc-sub">مشاهده، جستجو، تغییر اشتراک</div>
    </a>
    <a href="<?= APP_URL ?>/admin/receipts" class="quick-link-card">
        <div class="qlc-icon"><i data-lucide="receipt-text"></i></div>
        <div class="qlc-title">رسیدهای پرداخت</div>
        <div class="qlc-sub">تایید یا رد درخواست‌های خرید</div>
    </a>
    <a href="<?= APP_URL ?>/admin/discounts" class="quick-link-card">
        <div class="qlc-icon"><i data-lucide="tag"></i></div>
        <div class="qlc-title">کدهای تخفیف</div>
        <div class="qlc-sub">ایجاد و مدیریت کد تخفیف</div>
    </a>
    <a href="<?= APP_URL ?>/admin/settings" class="quick-link-card">
        <div class="qlc-icon"><i data-lucide="settings"></i></div>
        <div class="qlc-title">تنظیمات سایت</div>
        <div class="qlc-sub">لوگو، قیمت‌ها، اطلاعات تماس</div>
    </a>
</div>
