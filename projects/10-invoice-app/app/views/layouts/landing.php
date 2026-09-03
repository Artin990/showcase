<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? setting('site_name', APP_NAME)) ?></title>
<meta name="description" content="<?= e($title ?? setting('site_name', APP_NAME)) ?> — صدور فاکتور حرفه‌ای، سریع و آنلاین با ۵ قالب آماده، خروجی PDF/اکسل و پلن‌های منعطف">
<meta property="og:type" content="website">
<meta property="og:locale" content="fa_IR">
<meta property="og:title" content="<?= e(setting('site_name', APP_NAME)) ?> — صدور فاکتور آنلاین">
<meta property="og:description" content="فاکتور حرفه‌ای خود را در چند ثانیه بسازید؛ با قالب‌های متنوع، خروجی PDF/PNG/اکسل و سامانه اشتراک.">
<meta property="og:url" content="<?= APP_URL ?>/">
<meta name="theme-color" content="#0D9488">
<?php if ($fav = setting('site_favicon')): ?><link rel="icon" href="<?= APP_URL . '/uploads/branding/' . e($fav) ?>"><?php endif; ?>

<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>
(function(){try{var t=localStorage.getItem('siteTheme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();
</script>
</head>
<body class="landing-body">
<?php $__logo = setting('site_logo'); $__siteName = setting('site_name', APP_NAME); ?>
<header class="landing-nav">
    <div class="landing-nav-inner">
        <a href="<?= APP_URL ?>/" class="landing-brand">
            <?php if ($__logo): ?><img src="<?= APP_URL . '/uploads/branding/' . e($__logo) ?>" class="brand-logo-img"><?php else: ?><i data-lucide="receipt"></i><?php endif; ?>
            <?= e($__siteName) ?>
        </a>
        <nav class="landing-nav-links">
            <a href="#features">امکانات</a>
            <a href="#samples">نمونه فاکتور</a>
            <a href="#pricing">قیمت‌گذاری</a>
            <a href="#faq">سوالات متداول</a>
            <div class="landing-nav-actions">
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
                <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm">ورود</a>
                <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-sm">ثبت‌نام رایگان</a>
            </div>
        </nav>
    </div>
</header>

<main>
    <?= $content ?>
</main>

<footer class="landing-footer">
    <div class="landing-footer-inner">
        <div class="landing-footer-brand">
            <?php if ($__logo): ?><img src="<?= APP_URL . '/uploads/branding/' . e($__logo) ?>" class="brand-logo-img"><?php else: ?><i data-lucide="receipt"></i><?php endif; ?>
            <?= e($__siteName) ?>
        </div>
        <div class="landing-footer-links">
            <a href="<?= APP_URL ?>/contact"><i data-lucide="headphones"></i> تماس با ما</a>
            <?php if ($t = setting('contact_telegram')): ?><a href="https://t.me/<?= e($t) ?>" target="_blank" rel="noopener"><i data-lucide="send"></i> تلگرام</a><?php endif; ?>
            <?php if ($ig = setting('contact_instagram')): ?><a href="https://instagram.com/<?= e($ig) ?>" target="_blank" rel="noopener"><i data-lucide="instagram"></i> اینستاگرام</a><?php endif; ?>
        </div>
        <div class="landing-footer-copy">© <?= date('Y') ?> <?= e($__siteName) ?> — تمام حقوق محفوظ است.</div>
    </div>
</footer>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script defer src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script defer>document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });</script>
</body>
</html>
