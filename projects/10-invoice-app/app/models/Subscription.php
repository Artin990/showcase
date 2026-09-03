<?php

class Subscription
{
    private Database $db;
    /** کش درون‌درخواستی برای جلوگیری از کوئری‌های تکراری */
    private static array $memo = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** دریافت رکورد اشتراک کاربر (همراه با تمام امکانات پلن)؛ اگر وجود نداشت، به پلن رایگان متصل می‌شود */
    public function getForUser(int $userId): array
    {
        if (isset(self::$memo[$userId])) {
            return self::$memo[$userId];
        }
        $sub = $this->fetchWithPlan($userId);
        if ($sub !== null) {
            self::$memo[$userId] = $sub;
        }
        return $sub ?? [];
    }

    private function fetchWithPlan(int $userId): ?array
    {
        return $this->db->fetchOne(
            'SELECT s.*,
                    p.name AS plan_name, p.icon AS plan_icon, p.color AS plan_color,
                    p.duration_months AS plan_duration_months, p.price AS plan_price,
                    p.monthly_invoice_limit AS plan_monthly_invoice_limit,
                    p.allow_pdf AS plan_allow_pdf, p.allow_excel AS plan_allow_excel,
                    p.allow_image AS plan_allow_image, p.allow_edit AS plan_allow_edit,
                    p.allow_custom_templates AS plan_allow_custom_templates,
                    p.allow_hide_ad AS plan_allow_hide_ad, p.is_free AS plan_is_free
             FROM subscriptions s
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE s.user_id = ?',
            [$userId]
        );
    }

    /**
     * آیا کاربر در حال حاضر اشتراک پولی فعال دارد؟
     * (پولی محسوب می‌شود اگر پلن سیستمی رایگان نباشد و تاریخ انقضا نگذشته باشد)
     */
    public function isPaidActive(int $userId): bool
    {
        $sub = $this->getForUser($userId);

        if (empty($sub['plan_id']) || !empty($sub['plan_is_free'])) {
            return false;
        }
        if ($sub['status'] !== 'active') {
            return false;
        }
        if (empty($sub['end_date'])) {
            return false;
        }
        // اشتراکی که امروز تمام می‌شود دیگر فعال محسوب نمی‌شود
        return strtotime($sub['end_date']) > strtotime(date('Y-m-d'));
    }

    public function daysLeft(int $userId): ?int
    {
        $sub = $this->getForUser($userId);
        if (empty($sub['end_date'])) {
            return null;
        }
        $diff = (strtotime($sub['end_date']) - strtotime(date('Y-m-d'))) / 86400;
        return (int) ceil($diff);
    }

    /**
     * امکانات فعال پلن کاربر - برای بررسی مجوز PDF/Excel/ویرایش/قالب اختصاصی/مخفی‌کردن تبلیغ/سقف فاکتور.
     * اگر کاربر اشتراک پولی منقضی‌شده داشته باشد (نه رایگان، نه فعال)، امکانات به‌صورت پلن رایگان محاسبه می‌شود.
     */
    public function getPlanFeatures(int $userId): array
    {
        $sub = $this->getForUser($userId);
        $isPaid = $this->isPaidActive($userId);

        if (!$isPaid) {
            // یا واقعا رایگان است، یا اشتراک پولی‌اش منقضی شده - در هر دو حالت باید محدودیت‌های پلن رایگان اعمال شود
            $freePlan = $this->db->fetchOne('SELECT * FROM plans WHERE is_free = 1 LIMIT 1');
            return [
                'plan_name' => $freePlan['name'] ?? 'رایگان',
                'monthly_invoice_limit' => $freePlan['monthly_invoice_limit'] ?? null,
                'allow_pdf' => (bool) ($freePlan['allow_pdf'] ?? false),
                'allow_excel' => (bool) ($freePlan['allow_excel'] ?? false),
                'allow_image' => (bool) ($freePlan['allow_image'] ?? true),
                'allow_edit' => (bool) ($freePlan['allow_edit'] ?? true),
                'allow_custom_templates' => (bool) ($freePlan['allow_custom_templates'] ?? false),
                'allow_hide_ad' => (bool) ($freePlan['allow_hide_ad'] ?? false),
            ];
        }

        return [
            'plan_name' => $sub['plan_name'],
            'monthly_invoice_limit' => $sub['plan_monthly_invoice_limit'],
            'allow_pdf' => (bool) $sub['plan_allow_pdf'],
            'allow_excel' => (bool) $sub['plan_allow_excel'],
            'allow_image' => (bool) $sub['plan_allow_image'],
            'allow_edit' => (bool) $sub['plan_allow_edit'],
            'allow_custom_templates' => (bool) $sub['plan_allow_custom_templates'],
            'allow_hide_ad' => (bool) $sub['plan_allow_hide_ad'],
        ];
    }

    // ---------------- رسیدهای پرداخت ----------------

    public function createReceipt(int $userId, int $planId, string $receiptImage, ?string $discountCode = null, ?float $amount = null): int
    {
        $this->db->query(
            'INSERT INTO payment_receipts (user_id, plan_id, receipt_image, discount_code, amount, status)
             VALUES (?, ?, ?, ?, ?, "pending")',
            [$userId, $planId, $receiptImage, $discountCode, $amount]
        );
        return (int) $this->db->lastInsertId();
    }

    public function receiptsForUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, p.name AS plan_name
             FROM payment_receipts r LEFT JOIN plans p ON p.id = r.plan_id
             WHERE r.user_id = ? ORDER BY r.id DESC',
            [$userId]
        );
    }

    public function hasPendingReceipt(int $userId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT id FROM payment_receipts WHERE user_id = ? AND status = "pending" LIMIT 1',
            [$userId]
        );
        return $row !== null;
    }

    // ---------------- عملیات پنل مدیریت ----------------

    public function pendingReceiptsCount(): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS c FROM payment_receipts WHERE status = "pending"');
        return (int) ($row['c'] ?? 0);
    }

    private const ADMIN_PER_PAGE = 20;

    public function adminAllReceipts(?string $status = null, int $page = 1): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * self::ADMIN_PER_PAGE;

        $where = '';
        $params = [];
        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $where = 'WHERE pr.status = ?';
            $params[] = $status;
        }

        $items = $this->db->fetchAll(
            "SELECT pr.*, u.name AS user_name, u.email AS user_email, p.name AS plan_name
             FROM payment_receipts pr
             JOIN users u ON u.id = pr.user_id
             LEFT JOIN plans p ON p.id = pr.plan_id
             $where
             ORDER BY pr.id DESC LIMIT " . self::ADMIN_PER_PAGE . " OFFSET $offset",
            $params
        );

        $totalRow = $this->db->fetchOne("SELECT COUNT(*) AS c FROM payment_receipts pr $where", $params);
        $total = (int) ($totalRow['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / self::ADMIN_PER_PAGE));

        return ['items' => $items, 'page' => $page, 'total_pages' => $totalPages, 'total' => $total];
    }

    public function findReceiptById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT pr.*, u.name AS user_name, u.email AS user_email, p.duration_months AS plan_duration_months
             FROM payment_receipts pr
             JOIN users u ON u.id = pr.user_id
             LEFT JOIN plans p ON p.id = pr.plan_id
             WHERE pr.id = ?',
            [$id]
        );
    }

    /** فعال‌سازی خودکار اشتراک بر اساس مدت پلن (از امروز یا ادامه اشتراک فعلی در صورت هنوز فعال بودن) */
    public function activatePlan(int $userId, int $planId): void
    {
        $plan = $this->db->fetchOne('SELECT * FROM plans WHERE id = ?', [$planId]);
        if (!$plan) {
            return;
        }
        $months = max(1, (int) $plan['duration_months']);
        $current = $this->getForUser($userId);

        // اگر اشتراک فعلی هنوز منقضی نشده، از تاریخ پایان فعلی تمدید می‌شود (امروز در غیر این صورت)
        $base = date('Y-m-d');
        if (!empty($current['end_date']) && strtotime($current['end_date']) > strtotime(date('Y-m-d'))) {
            $base = date('Y-m-d', strtotime($current['end_date']));
        }

        $this->db->query(
            "UPDATE subscriptions
             SET plan_id = ?, status = 'active',
                 start_date = COALESCE(start_date, CURDATE()),
                 end_date = DATE_ADD(?, INTERVAL ? MONTH)
             WHERE user_id = ?",
            [$planId, $base, $months, $userId]
        );
        // پس از به‌روزرسانی، کش محلی را بی‌اعتبار می‌کنیم
        unset(self::$memo[$userId]);
    }

    public function approveReceipt(int $receiptId): bool
    {
        $receipt = $this->findReceiptById($receiptId);
        if (!$receipt || $receipt['status'] !== 'pending') {
            return false;
        }

        $this->activatePlan((int) $receipt['user_id'], (int) $receipt['plan_id']);
        $this->db->query('UPDATE payment_receipts SET status = "approved" WHERE id = ?', [$receiptId]);
        return true;
    }

    public function rejectReceipt(int $receiptId): bool
    {
        $stmt = $this->db->query(
            'UPDATE payment_receipts SET status = "rejected" WHERE id = ? AND status = "pending"',
            [$receiptId]
        );
        return $stmt->rowCount() > 0;
    }

    /** تغییر دستی اشتراک یک کاربر توسط ادمین */
    public function adminSetSubscription(int $userId, int $planId, string $status, ?string $endDate): void
    {
        $this->getForUser($userId); // اطمینان از وجود رکورد
        $this->db->query(
            'UPDATE subscriptions SET plan_id = ?, status = ?, end_date = ?,
             start_date = COALESCE(start_date, CURDATE()) WHERE user_id = ?',
            [$planId, $status, $endDate, $userId]
        );
    }
}
