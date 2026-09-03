<?php
/**
 * تنظیمات اصلی برنامه
 * این مقادیر را متناسب با هاست cPanel خودتان تغییر دهید.
 */

// ---------- تنظیمات دیتابیس ----------
// محیط محلی (XAMPP): root بدون پسورد — در هاست cPanel این مقادیر را از MySQL Databases بگیرید
define('DB_HOST', 'localhost');
define('DB_NAME', 'invoice_app');                 // نام دیتابیس
define('DB_USER', 'root');                        // یوزرنیم دیتابیس (XAMPP: root)
define('DB_PASS', '');                            // پسورد دیتابیس (XAMPP: خالی)
define('DB_CHARSET', 'utf8mb4');

// ---------- تنظیمات عمومی برنامه ----------
define('APP_NAME', 'فاکتورچی');
define('APP_URL', 'http://localhost/Projects/Web-Project/invoice-app-final'); // آدرس دامنه سایت (بدون / انتهایی)
define('APP_TIMEZONE', 'Asia/Tehran');

// ---------- تنظیمات امنیتی ----------
// این مقدار را با یک رشته تصادفی طولانی (مثلا از سایت random.org) عوض کنید
define('APP_SECRET_KEY', 'a9f1c4e8b7d24f6a9c3e5b8d1f7a2c4e9b6d8f0a3c5e7b1d9f4a6c8e2b0d3f5a7');

// مدت زمان اعتبار توکن فراموشی رمز عبور (به دقیقه)
define('RESET_TOKEN_LIFETIME_MIN', 60);

// ---------- نکته ----------
// شماره کارت، قیمت اشتراک‌ها و اطلاعات تماس دیگر در این فایل تنظیم نمی‌شوند.
// این مقادیر اکنون از پنل مدیریت (/admin/settings) قابل تغییر هستند و در جدول
// «settings» دیتابیس ذخیره می‌شوند.

// ---------- مسیرها ----------
define('BASE_PATH', dirname(__DIR__, 2));         // ریشه پروژه (همان جایی که index.php قرار دارد)
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH);                  // دیگر پوشه public جداگانه‌ای وجود ندارد
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// ---------- محیط اجرا ----------
// حالت دیباگ فقط برای درخواست‌های محلی (127.0.0.1 / ::1) فعال است؛
// روی هاست واقعی برای بازدیدکنندگان خودکار false می‌شود و خطاها لو نمی‌روند.
define('APP_DEBUG', in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true));

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
