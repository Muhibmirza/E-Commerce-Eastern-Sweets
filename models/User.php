<?php
declare(strict_types=1);

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function adminList(string $q = '', string $status = ''): array
    {
        $params = [];
        $where = ['1=1'];
        if ($q !== '') {
            $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $params = ["%$q%", "%$q%", "%$q%"];
        }
        if ($status === 'active') {
            $where[] = 'u.is_blocked = 0';
        } elseif ($status === 'blocked') {
            $where[] = 'u.is_blocked = 1';
        }
        $stmt = Database::connection()->prepare(
            'SELECT u.*, COUNT(o.id) AS order_count, COALESCE(SUM(o.total_amount), 0) AS total_spent
             FROM users u
             LEFT JOIN orders o ON o.user_id = u.id
             WHERE ' . implode(' AND ', $where) . '
             GROUP BY u.id
             ORDER BY u.created_at DESC LIMIT 50'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateProfile(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['email'], $data['phone'], $id]);
    }

    public static function updateAddress(int $id, string $address): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET default_address = ? WHERE id = ?');
        $stmt->execute([$address, $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function setBlocked(int $id, bool $blocked): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET is_blocked = ? WHERE id = ?');
        $stmt->execute([$blocked ? 1 : 0, $id]);
    }

    public static function adminSave(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $fields = 'name = ?, email = ?, phone = ?, default_address = ?, is_blocked = ?';
            $params = [$data['name'], $data['email'], $data['phone'], $data['default_address'], $data['is_blocked']];
            if (!empty($data['password'])) {
                $fields .= ', password_hash = ?';
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $params[] = $id;
            $stmt = $pdo->prepare('UPDATE users SET ' . $fields . ' WHERE id = ?');
            $stmt->execute($params);
            return $id;
        }

        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, default_address, is_blocked) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            password_hash($data['password'] ?: 'customer123', PASSWORD_DEFAULT),
            $data['default_address'],
            $data['is_blocked'],
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
