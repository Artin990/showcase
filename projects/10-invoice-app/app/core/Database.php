<?php

/**
 * کلاس اتصال به دیتابیس با استفاده از PDO
 * از الگوی Singleton استفاده می‌شود تا فقط یک اتصال باز شود.
 * تمام کوئری‌ها با Prepared Statement اجرا می‌شوند تا در برابر SQL Injection امن باشند.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die('خطای اتصال به دیتابیس: ' . $e->getMessage());
            }
            die('خطا در اتصال به سرور. لطفا بعدا تلاش کنید.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /** اجرای کوئری با پارامترهای امن (Prepared Statement) */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    // جلوگیری از کلون شدن و unserialize (اصول Singleton)
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
