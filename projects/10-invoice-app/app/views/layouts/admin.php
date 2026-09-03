<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'پنل مدیریت') ?> | <?= e(setting('site_name', APP_NAME)) ?></title>
<?php if ($fav = setting('site_favicon')): ?><link rel="icon" href="<?= APP_URL . '/uploads/branding/' . e($fav) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script defer src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script defer>document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });</script>
<script>
(function(){try{var t=localStorage.getItem('siteTheme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();
</script>
</head>
<body>
<?php
$currentPath = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
function adminNavActive($path, $current) {
    if ($path === '/admin/dashboard') return $path === $current ? 'active' : '';
    return str_starts_with($current, $path) ? 'active' : '';
}
$__pendingCount = (new Subscription())->pendingReceiptsCount();
$__isSuper = Auth::isSuperAdmin();
?>
<div class="mobile-topbar">
    <div class="brand"><i data-lucide="shield-check"></i> پنل مدیریت</div>
    <div style="display:flex; gap:8px; align-items:center;">
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
        <button id="mobile-menu-toggle" class="menu-toggle-btn" aria-label="باز کردن منو"><i data-lucide="list"></i></button>
    </div>
</div>
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="dash-shell">
    <aside class="sidebar sidebar-admin">
        <div class="brand brand-desktop"><i data-lucide="shield-check"></i> پنل مدیریت
            <button type="button" class="theme-toggle theme-toggle-desktop" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
        </div>
        <nav>
            <a href="<?= APP_URL ?>/admin/dashboard" class="<?= adminNavActive('/admin/dashboard', $currentPath) ?>"><i data-lucide="layout-grid"></i> داشبورد</a>
            <a href="<?= APP_URL ?>/admin/users" class="<?= adminNavActive('/admin/users', $currentPath) ?>"><i data-lucide="users"></i> کاربران</a>
            <a href="<?= APP_URL ?>/admin/receipts" class="<?= adminNavActive('/admin/receipts', $currentPath) ?>">
                <i data-lucide="receipt"></i> رسیدهای پرداخت
                <?php if ($__pendingCount > 0): ?><span class="nav-badge"><?= formatNumber($__pendingCount) ?></span><?php endif; ?>
            </a>
            <a href="<?= APP_URL ?>/admin/plans" class="<?= adminNavActive('/admin/plans', $currentPath) ?>"><i data-lucide="gem"></i> پلن‌های اشتراک</a>
            <a href="<?= APP_URL ?>/admin/discounts" class="<?= adminNavActive('/admin/discounts', $currentPath) ?>"><i data-lucide="tag"></i> کدهای تخفیف</a>
            <a href="<?= APP_URL ?>/admin/samples" class="<?= adminNavActive('/admin/samples', $currentPath) ?>"><i data-lucide="image"></i> نمونه فاکتورها</a>
            <a href="<?= APP_URL ?>/admin/settings" class="<?= adminNavActive('/admin/settings', $currentPath) ?>"><i data-lucide="settings"></i> تنظیمات سایت</a>
            <?php if ($__isSuper): ?>
                <a href="<?= APP_URL ?>/admin/admins" class="<?= adminNavActive('/admin/admins', $currentPath) ?>"><i data-lucide="id-card"></i> حساب‌های مدیر</a>
            <?php endif; ?>
            <form method="post" action="<?= APP_URL ?>/admin/logout" class="nav-logout">
                            <?= Auth::csrfField() ?>
                            <button type="submit" title="خروج از پنل"><i data-lucide="log-out"></i> خروج از پنل</button>
                        </form>
                    </nav>
        <div class="plan-badge">
            <a href="<?= APP_URL ?>/" class="back-to-site"><i data-lucide="corner-down-left"></i> بازگشت به سایت اصلی</a>
        </div>
    </aside>
    <main class="dash-main">
        <?= $content ?>
    </main>
</div>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
