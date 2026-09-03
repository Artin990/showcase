<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$type = isset($_GET['type']) ? $_GET['type'] : 'first';

if ($type === 'second') {
    $file = __DIR__ . '/top slider games data img second.json';
} else {
    $file = __DIR__ . '/top-slider-games-data.json';
}

if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'داده‌ای پیدا نشد', 'file' => basename($file)]);
    exit;
}

$raw = file_get_contents($file);
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در خواندن JSON: ' . json_last_error_msg()]);
    exit;
}

foreach ($data as &$item) {
    if (isset($item['image']) && strpos($item['image'], '/') === 0) {
        $item['image'] = ltrim($item['image'], '/');
    }
}
unset($item);

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
