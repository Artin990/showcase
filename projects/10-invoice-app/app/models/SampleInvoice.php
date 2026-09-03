<?php

class SampleInvoice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM sample_invoices ORDER BY sort_order ASC, id ASC');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM sample_invoices WHERE id = ?', [$id]);
    }

    public function create(string $image, int $sortOrder = 0): int
    {
        $this->db->query(
            'INSERT INTO sample_invoices (image, sort_order) VALUES (?, ?)',
            [$image, $sortOrder]
        );
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM sample_invoices WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }
}
