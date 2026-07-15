<?php
declare(strict_types=1);

final class Admin
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT id, name, email, role, is_active, last_login, created_at FROM admins ORDER BY id')->fetchAll();
    }
}
