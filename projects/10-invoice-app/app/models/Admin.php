<?php

class Admin
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne('SELECT * FROM admins WHERE email = ?', [$email]);
    }

    public function verifyPassword(array $admin, string $password): bool
    {
        return password_verify($password, $admin['password_hash']);
    }

    // ---------------- مدیریت مدیران (فقط برای مدیر ارشد) ----------------

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM admins ORDER BY id ASC');
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM admins WHERE id = ?', [$id]);
    }

    public function create(string $name, string $email, string $password, string $role = 'admin'): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query(
            'INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)',
            [$name, $email, $hash, $role]
        );
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->query('DELETE FROM admins WHERE id = ?', [$id]);
        return $stmt->rowCount() > 0;
    }

    public function toggleActive(int $id): void
    {
        $admin = $this->find($id);
        if ($admin) {
            $this->db->query('UPDATE admins SET is_active = ? WHERE id = ?', [$admin['is_active'] ? 0 : 1, $id]);
        }
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
