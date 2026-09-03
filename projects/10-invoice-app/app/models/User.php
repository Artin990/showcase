<?php

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function create(string $name, string $email, string $password, string $phone, string $storeName = '', ?string $businessType = null): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->db->query(
            'INSERT INTO users (name, email, password_hash, phone, store_name, business_type) VALUES (?, ?, ?, ?, ?, ?)',
            [$name, $email, $hash, $phone, $storeName ?: null, $businessType]
        );
        $userId = (int) $this->db->lastInsertId();

        // ایجاد رکورد اشتراک رایگان پیش‌فرض برای کاربر جدید (متصل به پلن رایگان سیستمی)
        $freePlan = $this->db->fetchOne('SELECT id FROM plans WHERE is_free = 1 LIMIT 1');
        $this->db->query(
            'INSERT INTO subscriptions (user_id, plan_id, status) VALUES (?, ?, "active")',
            [$userId, $freePlan['id'] ?? null]
        );

        return $userId;
    }

    public function updateProfile(int $id, string $name, string $phone, string $storeName = '', ?string $businessType = null): bool
    {
        $this->db->query(
            'UPDATE users SET name = ?, phone = ?, store_name = ?, business_type = ? WHERE id = ?',
            [$name, $phone, $storeName ?: null, $businessType, $id]
        );
        return true;
    }

    /** ویرایش کامل اطلاعات کاربر توسط ادمین (شامل نام، ایمیل، موبایل، فروشگاه) */
    public function adminUpdate(int $id, string $name, string $email, string $phone, string $storeName, ?string $businessType): bool
    {
        $this->db->query(
            'UPDATE users SET name = ?, email = ?, phone = ?, store_name = ?, business_type = ? WHERE id = ?',
            [$name, $email, $phone, $storeName ?: null, $businessType, $id]
        );
        return true;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $id]);
        return true;
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    private static function hashResetToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function setResetToken(int $id, string $token, string $expiresAt): void
    {
        // فقط هش توکن ذخیره می‌شود تا با افشای دیتابیس، توکن قابل استفاده نباشد
        $this->db->query(
            'UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?',
            [self::hashResetToken($token), $expiresAt, $id]
        );
    }

    public function findByValidResetToken(string $token): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()',
            [self::hashResetToken($token)]
        );
    }

    public function clearResetToken(int $id): void
    {
        $this->db->query('UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = ?', [$id]);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    // ---------------- عملیات پنل مدیریت ----------------

    private const ADMIN_PER_PAGE = 20;

    public function adminPaginate(string $search = '', int $page = 1): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * self::ADMIN_PER_PAGE;

        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }

        $items = $this->db->fetchAll(
            "SELECT u.*, s.plan_id, p.name AS plan_name, p.is_free AS plan_is_free, s.status AS sub_status, s.end_date,
                    (SELECT COUNT(*) FROM invoices i WHERE i.user_id = u.id) AS invoice_count
             FROM users u
             LEFT JOIN subscriptions s ON s.user_id = u.id
             LEFT JOIN plans p ON p.id = s.plan_id
             $where
             ORDER BY u.id DESC LIMIT " . self::ADMIN_PER_PAGE . " OFFSET $offset",
            $params
        );

        $totalRow = $this->db->fetchOne("SELECT COUNT(*) AS c FROM users u $where", $params);
        $total = (int) ($totalRow['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / self::ADMIN_PER_PAGE));

        return ['items' => $items, 'page' => $page, 'total_pages' => $totalPages, 'total' => $total];
    }

    public function countAll(): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS c FROM users');
        return (int) ($row['c'] ?? 0);
    }

    public function toggleActive(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }
        $newStatus = $user['is_active'] ? 0 : 1;
        $this->db->query('UPDATE users SET is_active = ? WHERE id = ?', [$newStatus, $id]);
        return true;
    }

    // ---------------- سهمیه ماهانه فاکتور (بر اساس سقف تعیین‌شده در پلن کاربر) ----------------

    private function currentMonthKey(): string
    {
        // دوره سهمیه بر اساس ماه شمسی (جلالی) است تا برای کاربر ایرانی منطقی باشد
        return substr(str_replace('/', '-', toJalali(date('Y-m-d'))), 0, 7);
    }

    /** اطمینان از اینکه شمارنده سهمیه مربوط به ماه جاری است؛ در غیر این‌صورت صفر می‌شود */
    private function ensureCurrentMonth(int $userId): void
    {
        $user = $this->findById($userId);
        if (!$user) {
            return;
        }
        if ($user['quota_month'] !== $this->currentMonthKey()) {
            $this->db->query(
                'UPDATE users SET quota_used = 0, quota_month = ? WHERE id = ?',
                [$this->currentMonthKey(), $userId]
            );
        }
    }

    /**
     * وضعیت فعلی سهمیه ماهانه کاربر: تعداد استفاده‌شده، سقف (بر اساس پلن فعلی)، و باقی‌مانده.
     * سقف از پلن کاربر خوانده می‌شود (نه یک عدد ثابت در کد) - اگر پلن سقفی نداشته باشد یعنی نامحدود است.
     */
    public function getQuotaStatus(int $userId): array
    {
        $this->ensureCurrentMonth($userId);
        $user = $this->findById($userId);
        $used = (int) ($user['quota_used'] ?? 0);

        $features = (new Subscription())->getPlanFeatures($userId);
        $limit = $features['monthly_invoice_limit']; // null = نامحدود

        if ($limit === null) {
            return ['used' => $used, 'limit' => null, 'remaining' => null, 'unlimited' => true];
        }

        $limit = max(0, (int) $limit);
        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
            'unlimited' => false,
        ];
    }

    /**
         * تلاش برای مصرف یک سهمیه (هنگام ایجاد یا ویرایش فاکتور).
         * اگر پلن کاربر نامحدود باشد همیشه true برمی‌گرداند. اگر سهمیه تمام شده باشد، چیزی مصرف نمی‌شود و false برمی‌گرداند.
         * مصرف به‌صورت اتمیک (یک UPDATE شرطی) انجام می‌شود تا دو درخواست هم‌زمان نتوانند از سقف رد شوند.
         */
        public function consumeQuota(int $userId): bool
        {
            $this->ensureCurrentMonth($userId);
            $month = $this->currentMonthKey();

            // UPDATE شرطی: فقط اگر سقف پلن اجازه دهد، شمارنده +1 می‌شود
            $sql = "
                UPDATE users
                SET quota_used = quota_used + 1, quota_month = ?
                WHERE id = ? AND quota_month = ?
                  AND (
                    (SELECT monthly_invoice_limit
                     FROM subscriptions s JOIN plans p ON p.id = s.plan_id
                     WHERE s.user_id = users.id AND s.status = 'active'
                     ORDER BY s.id DESC LIMIT 1) IS NULL
                    OR quota_used < (SELECT monthly_invoice_limit
                                     FROM subscriptions s JOIN plans p ON p.id = s.plan_id
                                     WHERE s.user_id = users.id AND s.status = 'active'
                                     ORDER BY s.id DESC LIMIT 1)
                  )";
            $stmt = $this->db->query($sql, [$month, $userId, $month]);
            return $stmt->rowCount() > 0;
        }
    }
