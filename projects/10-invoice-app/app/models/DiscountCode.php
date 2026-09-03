<?php

class DiscountCode
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM discount_codes ORDER BY id DESC');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM discount_codes WHERE id = ?', [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->db->fetchOne('SELECT * FROM discount_codes WHERE code = ?', [strtoupper(trim($code))]);
    }

    public function countActive(): int
    {
        $row = $this->db->fetchOne(
            'SELECT COUNT(*) AS c FROM discount_codes WHERE is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE())'
        );
        return (int) ($row['c'] ?? 0);
    }

    public function create(string $code, string $type, float $value, ?int $maxUses, ?string $expiresAt): bool
    {
        $this->db->query(
            'INSERT INTO discount_codes (code, type, value, max_uses, expires_at) VALUES (?, ?, ?, ?, ?)',
            [strtoupper(trim($code)), $type, $value, $maxUses, $expiresAt ?: null]
        );
        return true;
    }

    public function update(int $id, string $code, string $type, float $value, ?int $maxUses, ?string $expiresAt): bool
    {
        $this->db->query(
            'UPDATE discount_codes SET code = ?, type = ?, value = ?, max_uses = ?, expires_at = ? WHERE id = ?',
            [strtoupper(trim($code)), $type, $value, $maxUses, $expiresAt ?: null, $id]
        );
        return true;
    }

    public function toggleActive(int $id): void
    {
        $code = $this->find($id);
        if ($code) {
            $this->db->query('UPDATE discount_codes SET is_active = ? WHERE id = ?', [$code['is_active'] ? 0 : 1, $id]);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM discount_codes WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function incrementUsage(int $id): void
    {
        // اتمیک: فقط اگر هنوز به سقف مصرف نرسیده باشد، شمارنده افزایش می‌یابد
        $this->db->query(
            'UPDATE discount_codes SET used_count = used_count + 1
             WHERE id = ? AND (max_uses IS NULL OR used_count < max_uses)',
            [$id]
        );
    }

    /**
     * بررسی معتبر بودن یک کد تخفیف برای استفاده.
     * برمی‌گرداند: آرایه کد در صورت معتبر بودن، یا null در صورت نامعتبر بودن.
     */
    public function validateCode(string $code): ?array
    {
        $row = $this->findByCode($code);
        if (!$row) {
            return null;
        }
        if (!$row['is_active']) {
            return null;
        }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < strtotime(date('Y-m-d'))) {
            return null;
        }
        if ($row['max_uses'] !== null && (int) $row['used_count'] >= (int) $row['max_uses']) {
            return null;
        }
        return $row;
    }

    /** محاسبه قیمت نهایی پس از اعمال تخفیف */
    public function applyDiscount(array $discountCode, float $price): float
    {
        if ($discountCode['type'] === 'percent') {
            $final = $price - ($price * $discountCode['value'] / 100);
        } else {
            $final = $price - $discountCode['value'];
        }
        return max(0, round($final));
    }
}
