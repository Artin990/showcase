<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? setting('site_name', APP_NAME)) ?> | <?= e(setting('site_name', APP_NAME)) ?></title>
<?php if ($fav = setting('site_favicon')): ?><link rel="icon" href="<?= APP_URL . '/uploads/branding/' . e($fav) ?>"><?php endif; ?>

<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script>
(function(){try{var t=localStorage.getItem('siteTheme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();
</script>
</head>
<body>
<button type="button" class="theme-toggle auth-theme-toggle" data-theme-toggle aria-label="تغییر حالت روشن/تاریک"></button>
<div class="auth-shell">
    <div class="auth-visual">
        <span class="badge"><i data-lucide="star"></i> <?= e(setting('site_name', APP_NAME)) ?></span>
        <h1>فاکتورهای حرفه‌ای بسازید،<br>در چند ثانیه.</h1>
        <p>اقلام فاکتور را وارد کنید، تخفیف اعمال کنید، و خروجی تصویر، PDF یا اکسل بگیرید — همه در یک پنل ساده.</p>

        <div class="mock-invoice">
            <div class="row"><span>فاکتور شماره</span><b class="num">۱۰۴۲</b></div>
            <div class="row"><span>پیراهن مردانه × ۲</span><b class="num">۲,۴۰۰,۰۰۰</b></div>
            <div class="row"><span>کیف چرم × ۱</span><b class="num">۱,۸۵۰,۰۰۰</b></div>
            <div class="row total"><span>جمع کل</span><b class="num">۴,۲۵۰,۰۰۰ ریال</b></div>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <?= $content ?>
        </div>
    </div>
</div>
<script defer src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script defer>document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });</script>
</body>
</html>
