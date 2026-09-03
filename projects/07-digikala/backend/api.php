<?php
/**
 * بک‌اند PHP برای سرو کردن داده‌های محصولات دیجی‌کالا
 * استفاده:
 *   backend/api.php?type=best-products
 *   backend/api.php?type=offer-products
 *   backend/api.php?type=market-products-on-offer
 *   backend/api.php?type=part-products-1-4
 *   backend/api.php?type=part-products-5-8
 *   backend/api.php?type=offer-all-products
 *   backend/api.php?type=laptop-category
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$map = [
    'best-products'             => 'best products.json',
    'offer-products'            => 'offer-products.json',
    'market-products-on-offer'  => 'market-products-on-offer.json',
    'part-products-1-4'         => 'part-products-1-4.json',
    'part-products-5-8'         => 'part-products-5-8.json',
    'offer-all-products'        => 'offer-all-products.json',
    'laptop-category'           => 'laptop-category.json',
];

$type = isset($_GET['type']) ? $_GET['type'] : '';

if (!isset($map[$type])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'نوع داده نامعتبر است',
        'valid_types' => array_keys($map),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = __DIR__ . '/' . $map[$type];

if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'فایل داده پیدا نشد', 'file' => basename($file)], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents($file);
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در خواندن JSON: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * مسیر عکس‌ها (کلیدهای image یا img) را از حالت مطلق (/backend/...)
 * به حالت نسبی (backend/...) تبدیل می‌کند، مستقل از عمق آرایه/آبجکت.
 */
function fixImagePaths(&$node) {
    if (!is_array($node)) {
        return;
    }
    foreach ($node as $key => &$value) {
        if (($key === 'image' || $key === 'img') && is_string($value) && strpos($value, '/') === 0) {
            $value = ltrim($value, '/');
        } elseif (is_array($value)) {
            fixImagePaths($value);
        }
    }
    unset($value);
}

fixImagePaths($data);

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
