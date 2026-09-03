<?php

/**
 * مدل تنظیمات سایت - ذخیره‌سازی کلید/مقدار در دیتابیس.
 * این مقادیر جایگزین ثابت‌های قبلی config.php (شماره کارت، قیمت‌ها، اطلاعات تماس و ...) شده‌اند
 * تا از پنل مدیریت قابل تغییر باشند.
 */
class Settings
{
    private Database $db;
    private static ?array $cache = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function loadAll(): array
    {
        if (self::$cache === null) {
            $rows = $this->db->fetchAll('SELECT setting_key, setting_value FROM settings');
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public function get(string $key, string $default = ''): string
    {
        $all = $this->loadAll();
        return $all[$key] ?? $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $val = $this->get($key, (string) $default);
        return is_numeric($val) ? (int) $val : $default;
    }

    public function all(): array
    {
        return $this->loadAll();
    }

    public function set(string $key, string $value): void
    {
        $this->db->query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache = null; // پاک کردن کش برای بارگذاری مجدد
    }

    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, (string) $value);
        }
    }
}
