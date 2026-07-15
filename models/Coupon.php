<?php
declare(strict_types=1);

final class Coupon
{
    public static function validateCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM coupons
             WHERE code = ? AND is_active = 1
             AND (expires_at IS NULL OR expires_at >= CURDATE())
             AND (usage_limit IS NULL OR used_count < usage_limit)
             LIMIT 1'
        );
        $stmt->execute([strtoupper(trim($code))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM coupons WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare('UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, expires_at = ?, usage_limit = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$data['code'], $data['discount_type'], $data['discount_value'], $data['expires_at'], $data['usage_limit'], $data['is_active'], $id]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO coupons (code, discount_type, discount_value, expires_at, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['code'], $data['discount_type'], $data['discount_value'], $data['expires_at'], $data['usage_limit'], $data['is_active']]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE coupons SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
