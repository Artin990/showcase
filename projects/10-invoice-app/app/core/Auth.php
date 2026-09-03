<?php

/**
 * کلاس مدیریت احراز هویت، Session و CSRF
 */
class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // تنظیمات امن Session
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    // ---------------- کاربر عادی ----------------

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
    }

    public static function logoutUser(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        session_regenerate_id(true);
    }

    public static function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function requireUser(): void
    {
        if (!self::isUserLoggedIn()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    // ---------------- ادمین ----------------

    public static function loginAdmin(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
    }

    public static function logoutAdmin(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
        session_regenerate_id(true);
    }

    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function adminId(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function isSuperAdmin(): bool
    {
        return ($_SESSION['admin_role'] ?? '') === 'super_admin';
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdminLoggedIn()) {
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }
    }

    public static function requireSuperAdmin(): void
    {
        self::requireAdmin();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            die('دسترسی غیرمجاز: این بخش فقط برای مدیر ارشد قابل استفاده است.');
        }
    }

    // ---------------- CSRF Protection ----------------

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(?string $token): bool
    {
        return !empty($token) && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    // ---------------- محافظت در برابر حملات Brute-force ----------------
    // محدودیت بر اساس IP + کلید (ایمیل/عملیات) در جدول سرور ذخیره می‌شود
    // تا با پاک کردن کوکی یا تغییر Session قابل دور زدن نباشد.

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 60;

    private static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private static function ipHash(): string
    {
        $key = defined('APP_SECRET_KEY') ? APP_SECRET_KEY : 'invoice-secret';
        return hash('sha256', self::clientIp() . '|' . $key);
    }

    private static function attemptKey(string $key): string
    {
        return hash('sha256', $key);
    }

    private static function attemptRow(string $key): ?array
    {
        try {
            return Database::getInstance()->fetchOne(
                'SELECT attempts, locked_until FROM login_attempts WHERE ip_hash = ? AND user_key = ? LIMIT 1',
                [self::ipHash(), self::attemptKey($key)]
            );
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function isLockedOut(string $key): bool
    {
        $row = self::attemptRow($key);
        if (!$row || (int) $row['attempts'] < self::MAX_LOGIN_ATTEMPTS) {
            return false;
        }
        return $row['locked_until'] !== null && strtotime((string) $row['locked_until']) > time();
    }

    public static function lockoutSecondsLeft(string $key): int
    {
        $row = self::attemptRow($key);
        if (!$row || (int) $row['attempts'] < self::MAX_LOGIN_ATTEMPTS || $row['locked_until'] === null) {
            return 0;
        }
        return max(0, strtotime((string) $row['locked_until']) - time());
    }

    public static function registerFailedAttempt(string $key): void
    {
        try {
            Database::getInstance()->query(
                "INSERT INTO login_attempts (ip_hash, user_key, attempts, locked_until)
                 VALUES (?, ?, 1, NULL)
                 ON DUPLICATE KEY UPDATE
                    attempts = IF(locked_until IS NOT NULL AND locked_until > NOW(), attempts, IF(locked_until IS NULL, attempts + 1, 1)),
                    locked_until = IF(attempts >= ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NULL)",
                [self::ipHash(), self::attemptKey($key), self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_SECONDS]
            );
        } catch (Throwable $e) {
            // اگر جدول هنوز ساخته نشده باشد، شکست نباید لاگین را متوقف کند
        }
    }

    public static function clearLoginAttempts(string $key): void
    {
        try {
            Database::getInstance()->query(
                'DELETE FROM login_attempts WHERE ip_hash = ? AND user_key = ?',
                [self::ipHash(), self::attemptKey($key)]
            );
        } catch (Throwable $e) {
        }
    }

    // ---------------- سیاست رمز عبور ----------------

    public static function passwordIssues(string $password): array
    {
        $issues = [];
        if (mb_strlen($password) < 8) {
            $issues[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $issues[] = 'رمز عبور باید حداقل یک حرف (انگلیسی) داشته باشد.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $issues[] = 'رمز عبور باید حداقل یک عدد داشته باشد.';
        }
        return $issues;
    }
}
