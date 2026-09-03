<?php

class Plan
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM plans ORDER BY sort_order ASC, id ASC');
    }

    /** پلن‌های فعال و قابل خرید (پلن رایگان در این لیست نمی‌آید چون قابل خرید نیست) */
    public function purchasable(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM plans WHERE is_active = 1 AND is_free = 0 ORDER BY sort_order ASC, id ASC'
        );
    }

    /** همه پلن‌های فعال شامل رایگان - برای دراپ‌داون‌های مدیریتی */
    public function allActive(): array
    {
        return $this->db->fetchAll('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM plans WHERE id = ?', [$id]);
    }

    public function getFreePlan(): ?array
    {
        return $this->db->fetchOne('SELECT * FROM plans WHERE is_free = 1 LIMIT 1');
    }

    public function create(array $data): int
    {
        $this->db->query(
            'INSERT INTO plans
                (name, description, icon, color, duration_months, price, original_price, monthly_invoice_limit,
                 allow_pdf, allow_excel, allow_image, allow_edit, allow_custom_templates, allow_hide_ad, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'], $data['description'], $data['icon'], $data['color'],
                $data['duration_months'], $data['price'], $data['original_price'], $data['monthly_invoice_limit'],
                $data['allow_pdf'], $data['allow_excel'], $data['allow_image'], $data['allow_edit'],
                $data['allow_custom_templates'], $data['allow_hide_ad'], $data['sort_order'],
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $plan = $this->find($id);
        if (!$plan) {
            return false;
        }

        // پلن رایگان سیستمی همیشه رایگان باقی می‌ماند، حتی اگر مقدار دیگری فرستاده شود
        if ($plan['is_free']) {
            $data['price'] = 0;
            $data['original_price'] = null;
            $data['duration_months'] = 0;
        }

        $this->db->query(
            'UPDATE plans SET
                name = ?, description = ?, icon = ?, color = ?, duration_months = ?, price = ?, original_price = ?,
                monthly_invoice_limit = ?, allow_pdf = ?, allow_excel = ?, allow_image = ?, allow_edit = ?,
                allow_custom_templates = ?, allow_hide_ad = ?, sort_order = ?
             WHERE id = ?',
            [
                $data['name'], $data['description'], $data['icon'], $data['color'],
                $data['duration_months'], $data['price'], $data['original_price'], $data['monthly_invoice_limit'],
                $data['allow_pdf'], $data['allow_excel'], $data['allow_image'], $data['allow_edit'],
                $data['allow_custom_templates'], $data['allow_hide_ad'], $data['sort_order'], $id,
            ]
        );
        return true;
    }

    public function toggleActive(int $id): bool
    {
        $plan = $this->find($id);
        if (!$plan) {
            return false;
        }
        if ($plan['is_free']) {
            return false; // پلن رایگان همیشه باید فعال بماند
        }
        $this->db->query('UPDATE plans SET is_active = ? WHERE id = ?', [$plan['is_active'] ? 0 : 1, $id]);
        return true;
    }

    public function delete(int $id): bool
    {
        $plan = $this->find($id);
        if (!$plan || $plan['is_free']) {
            return false; // پلن رایگان سیستمی قابل حذف نیست
        }

        $freePlan = $this->getFreePlan();

        // کاربرانی که این پلن را دارند به پلن رایگان منتقل می‌شوند تا رکوردشان یتیم نماند
        if ($freePlan) {
            $this->db->query(
                'UPDATE subscriptions SET plan_id = ?, status = "expired" WHERE plan_id = ?',
                [$freePlan['id'], $id]
            );
        }

        $stmt = $this->db->query('DELETE FROM plans WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function countActive(): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS c FROM plans WHERE is_active = 1');
        return (int) ($row['c'] ?? 0);
    }
}
