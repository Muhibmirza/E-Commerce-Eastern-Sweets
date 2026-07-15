<?php
declare(strict_types=1);

final class Payment
{
    public static function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string)($filters['q'] ?? ''));
        $status = trim((string)($filters['status'] ?? ''));
        $method = trim((string)($filters['method'] ?? ''));

        if ($q !== '') {
            $where[] = '(o.order_number LIKE ? OR p.transaction_reference LIKE ? OR p.method LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($status !== '') {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }
        if ($method !== '') {
            $where[] = 'p.method = ?';
            $params[] = $method;
        }

        $stmt = Database::connection()->prepare(
            'SELECT p.*, o.order_number
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.created_at DESC
             LIMIT 100'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM payments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare('UPDATE payments SET order_id = ?, method = ?, amount = ?, status = ?, transaction_reference = ? WHERE id = ?');
            $stmt->execute([$data['order_id'], $data['method'], $data['amount'], $data['status'], $data['transaction_reference'], $id]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, method, amount, status, transaction_reference) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$data['order_id'], $data['method'], $data['amount'], $data['status'], $data['transaction_reference']]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM payments WHERE id = ?');
        $stmt->execute([$id]);
    }
}
