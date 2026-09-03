<?php

class Invoice
{
    private Database $db;
    private const PER_PAGE = 15;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function nextInvoiceNumber(): string
    {
        do {
            $num = 'FA-' . date('Y-m-d') . '-' . self::randomCode(5);
        } while ($this->db->fetchOne('SELECT 1 FROM invoices WHERE invoice_number = ?', [$num]));

        return $num;
    }

    /** کد تصادفی کوتاه برای شماره فاکتور؛ بدون حروف/اعداد مبهم (0,O,1,I) */
    public static function randomCode(int $length = 5): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $out;
    }

    public function countForUser(int $userId): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS c FROM invoices WHERE user_id = ?', [$userId]);
        return (int) ($row['c'] ?? 0);
    }

    public function recentForUser(int $userId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT id, invoice_number, customer_name, total_amount, created_at
             FROM invoices WHERE user_id = ? ORDER BY id DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public function paginateForUser(int $userId, int $page = 1): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * self::PER_PAGE;

        $items = $this->db->fetchAll(
            'SELECT * FROM invoices WHERE user_id = ? ORDER BY id DESC LIMIT ' . self::PER_PAGE . ' OFFSET ' . $offset,
            [$userId]
        );
        $total = $this->countForUser($userId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return ['items' => $items, 'page' => $page, 'total_pages' => $totalPages, 'total' => $total];
    }

    public function findForUser(int $id, int $userId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM invoices WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public function itemsFor(int $invoiceId): array
    {
        return $this->db->fetchAll('SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC', [$invoiceId]);
    }

    /**
     * ایجاد فاکتور به همراه ردیف‌های آن در یک تراکنش اتمی.
     * $lines: آرایه‌ای از ['product_name','quantity','unit_price','discount']
     * برمی‌گرداند: آرایه فاکتور ساخته‌شده یا null در صورت خطا
     */
    public function createWithItems(
        int $userId, string $customerName, string $sellerName, string $invoiceDateShamsi, array $lines,
        string $template = 'classic', bool $hideAd = false
    ): ?array {
        if (empty($lines)) {
            return null;
        }

        $pdo = $this->db->pdo();

        try {
            $pdo->beginTransaction();

            // شماره فاکتور موقت؛ پس از دریافت ID نهایی، فرمت‌بندی می‌شود
            $stmt = $pdo->prepare(
                'INSERT INTO invoices (user_id, invoice_number, invoice_date_shamsi, seller_name, customer_name, template, hide_ad, total_amount)
                 VALUES (?, "TEMP", ?, ?, ?, ?, ?, 0)'
            );
            $stmt->execute([$userId, $invoiceDateShamsi ?: null, $sellerName ?: null, $customerName ?: null, $template, $hideAd ? 1 : 0]);
            $invoiceId = (int) $pdo->lastInsertId();

            $invoiceNumber = 'FA-' . date('Y-m-d') . '-' . Invoice::randomCode(5);

            $total = 0;
            $itemStmt = $pdo->prepare(
                'INSERT INTO invoice_items (invoice_id, product_name, quantity, unit_price, discount, row_total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            foreach ($lines as $line) {
                $qty = max(1, (int) $line['quantity']);
                $unitPrice = max(0, (float) $line['unit_price']);
                $discount = max(0, (float) ($line['discount'] ?? 0));
                $rowTotal = max(0, ($qty * $unitPrice) - $discount);
                $total += $rowTotal;

                $itemStmt->execute([$invoiceId, $line['product_name'], $qty, $unitPrice, $discount, $rowTotal]);
            }

            $updateStmt = $pdo->prepare('UPDATE invoices SET invoice_number = ?, total_amount = ? WHERE id = ?');
            $updateStmt->execute([$invoiceNumber, $total, $invoiceId]);

            $pdo->commit();

            return $this->findForUser($invoiceId, $userId);
        } catch (Exception $e) {
            $pdo->rollBack();
            return null;
        }
    }

    /**
     * ویرایش یک فاکتور موجود: بروزرسانی اطلاعات کلی و جایگزینی کامل ردیف‌ها.
     * شماره فاکتور تغییر نمی‌کند. در یک تراکنش اتمی انجام می‌شود.
     */
    public function updateWithItems(
        int $id, int $userId, string $customerName, string $sellerName, string $invoiceDateShamsi, array $lines,
        string $template = 'classic', bool $hideAd = false
    ): bool {
        if (empty($lines)) {
            return false;
        }

        $pdo = $this->db->pdo();

        try {
            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare(
                'UPDATE invoices SET invoice_date_shamsi = ?, seller_name = ?, customer_name = ?, template = ?, hide_ad = ?, total_amount = ?
                 WHERE id = ? AND user_id = ?'
            );

            $total = 0;
            foreach ($lines as $line) {
                $qty = max(1, (int) $line['quantity']);
                $unitPrice = max(0, (float) $line['unit_price']);
                $discount = max(0, (float) ($line['discount'] ?? 0));
                $total += max(0, ($qty * $unitPrice) - $discount);
            }

            $updateStmt->execute([
                $invoiceDateShamsi ?: null, $sellerName ?: null, $customerName ?: null,
                $template, $hideAd ? 1 : 0, $total, $id, $userId,
            ]);

            // حذف ردیف‌های قبلی و درج ردیف‌های جدید
            $pdo->prepare('DELETE FROM invoice_items WHERE invoice_id = ?')->execute([$id]);

            $itemStmt = $pdo->prepare(
                'INSERT INTO invoice_items (invoice_id, product_name, quantity, unit_price, discount, row_total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($lines as $line) {
                $qty = max(1, (int) $line['quantity']);
                $unitPrice = max(0, (float) $line['unit_price']);
                $discount = max(0, (float) ($line['discount'] ?? 0));
                $rowTotal = max(0, ($qty * $unitPrice) - $discount);
                $itemStmt->execute([$id, $line['product_name'], $qty, $unitPrice, $discount, $rowTotal]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /** نسخه مخصوص پنل مدیریت از ویرایش فاکتور - بدون محدودیت مالکیت کاربر */
    public function adminUpdateWithItems(
        int $id, string $customerName, string $sellerName, string $invoiceDateShamsi, array $lines
    ): bool {
        if (empty($lines)) {
            return false;
        }

        $pdo = $this->db->pdo();

        try {
            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare(
                'UPDATE invoices SET invoice_date_shamsi = ?, seller_name = ?, customer_name = ?, total_amount = ? WHERE id = ?'
            );

            $total = 0;
            foreach ($lines as $line) {
                $qty = max(1, (int) $line['quantity']);
                $unitPrice = max(0, (float) $line['unit_price']);
                $discount = max(0, (float) ($line['discount'] ?? 0));
                $total += max(0, ($qty * $unitPrice) - $discount);
            }

            $updateStmt->execute([$invoiceDateShamsi ?: null, $sellerName ?: null, $customerName ?: null, $total, $id]);

            $pdo->prepare('DELETE FROM invoice_items WHERE invoice_id = ?')->execute([$id]);

            $itemStmt = $pdo->prepare(
                'INSERT INTO invoice_items (invoice_id, product_name, quantity, unit_price, discount, row_total)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($lines as $line) {
                $qty = max(1, (int) $line['quantity']);
                $unitPrice = max(0, (float) $line['unit_price']);
                $discount = max(0, (float) ($line['discount'] ?? 0));
                $rowTotal = max(0, ($qty * $unitPrice) - $discount);
                $itemStmt->execute([$id, $line['product_name'], $qty, $unitPrice, $discount, $rowTotal]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->query('DELETE FROM invoices WHERE id = ? AND user_id = ?', [$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    // ---------------- عملیات پنل مدیریت (بدون محدودیت به یک کاربر) ----------------

    private const ADMIN_PER_PAGE = 20;

    public function adminPaginateAll(string $search = '', int $page = 1, ?int $userId = null): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * self::ADMIN_PER_PAGE;

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(i.invoice_number LIKE ? OR i.customer_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if ($userId !== null) {
            $conditions[] = 'i.user_id = ?';
            $params[] = $userId;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $items = $this->db->fetchAll(
            "SELECT i.*, u.name AS owner_name, u.email AS owner_email
             FROM invoices i JOIN users u ON u.id = i.user_id
             $where
             ORDER BY i.id DESC LIMIT " . self::ADMIN_PER_PAGE . " OFFSET $offset",
            $params
        );

        $totalRow = $this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM invoices i JOIN users u ON u.id = i.user_id $where",
            $params
        );
        $total = (int) ($totalRow['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / self::ADMIN_PER_PAGE));

        return ['items' => $items, 'page' => $page, 'total_pages' => $totalPages, 'total' => $total];
    }

    public function adminFind(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT i.*, u.name AS owner_name FROM invoices i JOIN users u ON u.id = i.user_id WHERE i.id = ?',
            [$id]
        );
    }

    public function adminDelete(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM invoices WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function countAll(): int
    {
        $row = $this->db->fetchOne('SELECT COUNT(*) AS c FROM invoices');
        return (int) ($row['c'] ?? 0);
    }
}
