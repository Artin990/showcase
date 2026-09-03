<?php

/** دسترسی سریع به تنظیمات سایت در ویوها */
function setting(string $key, string $default = ''): string
{
    static $model = null;
    if ($model === null) {
        $model = new Settings();
    }
    return $model->get($key, $default);
}

/**
 * تبدیل تاریخ میلادی به شمسی (الگوریتم استاندارد و تست‌شده)
 * ورودی: سال، ماه، روز میلادی -> خروجی: آرایه [سال, ماه, روز] شمسی
 */
function gregorianToJalali(int $gy, int $gm, int $gd): array
{
    $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

    $gy2 = $gy - 1600;
    $gm2 = $gm - 1;
    $gd2 = $gd - 1;

    $g_day_no = 365 * $gy2 + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400);
    for ($i = 0; $i < $gm2; $i++) {
        $g_day_no += $g_days_in_month[$i];
    }
    if ($gm2 > 1 && (($gy2 % 4 === 0 && $gy2 % 100 !== 0) || $gy2 % 400 === 0)) {
        $g_day_no++;
    }
    $g_day_no += $gd2;

    $j_day_no = $g_day_no - 79;
    $j_np = intdiv($j_day_no, 12053);
    $j_day_no %= 12053;

    $jy = 979 + 33 * $j_np + 4 * intdiv($j_day_no, 1461);
    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += intdiv($j_day_no - 1, 365);
        $j_day_no = ($j_day_no - 1) % 365;
    }

    $i = 0;
    while ($i < 11 && $j_day_no >= $j_days_in_month[$i]) {
        $j_day_no -= $j_days_in_month[$i];
        $i++;
    }
    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return [$jy, $jm, $jd];
}

/** تبدیل تاریخ شمسی به میلادی (الگوریتم استاندارد و تست‌شده)؛ خروجی: آرایه [سال, ماه, روز] میلادی */
function jalaliToGregorian(int $jy, int $jm, int $jd): array
{
    $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

    $jy2 = $jy - 979;
    $jm2 = $jm - 1;
    $jd2 = $jd - 1;

    $j_day_no = 365 * $jy2 + intdiv($jy2, 33) * 8 + intdiv(($jy2 % 33) + 3, 4);
    for ($i = 0; $i < $jm2; $i++) {
        $j_day_no += $j_days_in_month[$i];
    }
    $j_day_no += $jd2;

    $g_day_no = $j_day_no + 79;

    $gy = 1600 + 400 * intdiv($g_day_no, 146097);
    $g_day_no %= 146097;

    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * intdiv($g_day_no, 36524);
        $g_day_no %= 36524;

        if ($g_day_no >= 365) {
            $g_day_no++;
        } else {
            $leap = false;
        }
    }

    $gy += 4 * intdiv($g_day_no, 1461);
    $g_day_no %= 1461;

    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += intdiv($g_day_no, 365);
        $g_day_no %= 365;
    }

    $i = 0;
    while ($g_day_no >= $g_days_in_month[$i] + (($i === 1 && $leap) ? 1 : 0)) {
        $g_day_no -= $g_days_in_month[$i] + (($i === 1 && $leap) ? 1 : 0);
        $i++;
    }
    $gm = $i + 1;
    $gd = $g_day_no + 1;

    return [$gy, $gm, $gd];
}

/** تبدیل رشته میلادی (Y-m-d یا Y-m-d H:i:s) به رشته شمسی با فرمت دلخواه */
function toJalali(?string $gregorianDateTime, string $separator = '/'): string
{
    if (empty($gregorianDateTime)) {
        return '';
    }
    $ts = strtotime($gregorianDateTime);
    if ($ts === false) {
        return '';
    }
    [$jy, $jm, $jd] = gregorianToJalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $jy . $separator . str_pad((string) $jm, 2, '0', STR_PAD_LEFT) . $separator . str_pad((string) $jd, 2, '0', STR_PAD_LEFT);
}

/** تبدیل رشته میلادی به تاریخ و ساعت شمسی (برای لاگ‌ها و رکوردهای دقیق) */
function toJalaliDateTime(?string $gregorianDateTime): string
{
    if (empty($gregorianDateTime)) {
        return '';
    }
    $ts = strtotime($gregorianDateTime);
    if ($ts === false) {
        return '';
    }
    return toJalali($gregorianDateTime) . ' - ' . date('H:i', $ts);
}

/** تبدیل تاریخ شمسی با فرمت Y/m/d یا Y-m-d به رشته میلادی Y-m-d (برای ذخیره در دیتابیس) */
function fromJalali(?string $jalaliDate): ?string
{
    if (empty($jalaliDate)) {
        return null;
    }
    $parts = preg_split('/[\/\-]/', trim($jalaliDate));
    if (count($parts) !== 3) {
        return null;
    }
    [$jy, $jm, $jd] = array_map('intval', $parts);
    // محدوده معتبر سال شمسی (۱۲۴۰ تا ۱۵۰۰) و بررسی تعداد روز هر ماه
    if ($jy < 1240 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1) {
        return null;
    }
    $maxDays = ($jm <= 6) ? 31 : (($jm <= 11) ? 30 : 30); // ماه ۱۲: ۲۹ یا ۳۰ (سال کبیسه)
    if ($jd > $maxDays) {
        return null;
    }
    [$gy, $gm, $gd] = jalaliToGregorian($jy, $jm, $jd);
    // برگرداندن تاریخ و تطبیق آن با ورودی (جلوگیری از تاریخ‌های جعلی مثل ۳۱ اسفند)
    $expected = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    if (toJalali(sprintf('%04d-%02d-%02d', $gy, $gm, $gd), '/') !== $expected) {
        return null;
    }
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

/** تاریخ شمسی امروز - فرمت Y/m/d */
function todayJalali(string $separator = '/'): string
{
    return toJalali(date('Y-m-d'), $separator);
}

/** خروجی امن HTML برای جلوگیری از XSS */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** ریدایرکت به یک مسیر داخلی */
function redirect(string $path): void
{
    header('Location: ' . APP_URL . $path);
    exit;
}

/** ذخیره پیام یک‌بار مصرف (Flash Message) */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

/** خواندن و پاک کردن پیام Flash */
function getFlash(string $type): ?string
{
    if (!empty($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/** فرمت عدد به صورت جدا شده با کاما */
function formatNumber($number): string
{
    return number_format((float) $number, 0);
}

/** بارگذاری یک view با یک لایه مشترک (layout) */
function view(string $viewPath, array $data = [], string $layout = 'layouts/main'): void
{
    extract($data);
    $viewFile = APP_PATH . '/views/' . $viewPath . '.php';
    if (!file_exists($viewFile)) {
        die('View یافت نشد: ' . e($viewPath));
    }

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    if ($layout) {
        require APP_PATH . '/views/' . $layout . '.php';
    } else {
        echo $content;
    }
}
