<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? setting('site_name', APP_NAME)) ?> | <?= e(setting('site_name', APP_NAME)) ?></title>
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
function navActive($path, $current) { return $current === $path || str_starts_with($current, rtrim($path, '/') . '/') ? 'active' : ''; }

$__subModel = new Subscription();
$__isPaid = $__subModel->isPaidActive(Auth::userId());
$__daysLeft = $__subModel->daysLeft(Auth::userId());
$__logo = setting('site_logo');
$__siteName = setting('site_name', APP_NAME);
?>
<div class="mobile-topbar">
    <div class="brand">
        <?php if ($__logo): ?><img src="<?= APP_URL . '/uploads/branding/' . e($__logo) ?>" class="brand-logo-img"><?php else: ?><i data-lucide="receipt"></i><?php endif; ?>
        <?= e($__siteName) ?>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
        <button id="mobile-menu-toggle" class="menu-toggle-btn" aria-label="باز کردن منو"><i data-lucide="list"></i></button>
    </div>
</div>
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="dash-shell">
    <aside class="sidebar sidebar-admin">
        <div class="brand brand-desktop brand-user-panel">
            <span>پنل کاربری</span>
            <button type="button" class="theme-toggle theme-toggle-desktop" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
        </div>
        <nav>
            <a href="<?= APP_URL ?>/dashboard" class="<?= navActive('/dashboard', $currentPath) ?>"><i data-lucide="layout-grid"></i> داشبورد</a>
            <a href="<?= APP_URL ?>/invoices" class="<?= navActive('/invoices', $currentPath) ?>"><i data-lucide="receipt-text"></i> فاکتورها</a>
            <a href="<?= APP_URL ?>/subscription" class="<?= navActive('/subscription', $currentPath) ?>"><i data-lucide="gem"></i> اشتراک</a>
            <a href="<?= APP_URL ?>/contact" class="<?= navActive('/contact', $currentPath) ?>"><i data-lucide="headphones"></i> تماس با ما</a>
            <a href="<?= APP_URL ?>/profile" class="<?= navActive('/profile', $currentPath) ?>"><i data-lucide="user-round"></i> پروفایل</a>
            <form method="post" action="<?= APP_URL ?>/logout" class="nav-logout">
                            <?= Auth::csrfField() ?>
                            <button type="submit" title="خروج از حساب"><i data-lucide="log-out"></i> خروج از حساب</button>
                        </form>
                    </nav>
        <div class="plan-badge">
            <?php if ($__isPaid): ?>
                <i data-lucide="badge-check" style="color:var(--gold);"></i> پلن فعلی: <span class="gold-text">پولی</span><br>
                <?php if ($__daysLeft !== null): ?>
                    <?= formatNumber($__daysLeft) ?> روز باقی‌مانده
                <?php endif; ?>
            <?php else: ?>
                پلن فعلی: <span class="gold-text">رایگان</span><br>
                برای دانلود PDF و اکسل، اشتراک تهیه کنید.
            <?php endif; ?>
        </div>
    </aside>
    <main class="dash-main">
        <?= $content ?>
    </main>
</div>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
