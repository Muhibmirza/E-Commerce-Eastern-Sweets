<?php
declare(strict_types=1);

final class Order
{
    public static function create(array $customer, array $cart, string $paymentMethod, ?array $user = null, ?array $coupon = null): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $subtotal = 0;
            $prepared = [];
            foreach ($cart as $item) {
                $stmt = $pdo->prepare('SELECT pv.*, p.name, p.stock FROM product_variants pv JOIN products p ON p.id = pv.product_id WHERE pv.id = ? FOR UPDATE');
                $stmt->execute([(int)$item['variant_id']]);
                $variant = $stmt->fetch();
                if (!$variant || (int)$variant['stock'] < (int)$item['quantity']) {
                    throw new RuntimeException('One or more items are out of stock.');
                }
                $line = (float)$variant['price'] * (int)$item['quantity'];
                $subtotal += $line;
                $prepared[] = [$variant, (int)$item['quantity'], $line];
            }

            $discount = 0;
            if ($coupon) {
                $discount = $coupon['discount_type'] === 'percent'
                    ? round($subtotal * ((float)$coupon['discount_value'] / 100), 2)
                    : min($subtotal, (float)$coupon['discount_value']);
            }
            $delivery = $subtotal >= 2000 ? 0 : (float)app_setting('delivery_charge', '180');
            $tax = round(($subtotal - $discount) * ((float)app_setting('tax_percent', '0') / 100), 2);
            $total = max(0, $subtotal - $discount + $delivery + $tax);
            $orderNumber = 'ES-' . date('ymd') . '-' . random_int(1000, 9999);

            $stmt = $pdo->prepare('INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, delivery_address, city, area, notes, subtotal, delivery_charge, discount_amount, tax_amount, total_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $orderNumber,
                $user['id'] ?? null,
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $customer['city'],
                $customer['area'],
                $customer['notes'] ?? '',
                $subtotal,
                $delivery,
                $discount,
                $tax,
                $total,
                $paymentMethod,
                'pending',
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE product_variants SET stock = stock - ? WHERE id = ?');
            $productStockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0), sales_count = sales_count + ? WHERE id = ?');
            foreach ($prepared as [$variant, $quantity, $line]) {
                $itemStmt->execute([
                    $orderId,
                    $variant['product_id'],
                    $variant['id'],
                    $variant['name'],
                    $variant['variant_name'],
                    $variant['price'],
                    $quantity,
                    $line,
                ]);
                $stockStmt->execute([$quantity, $variant['id']]);
                $productStockStmt->execute([$quantity, $quantity, $variant['product_id']]);
            }

            $paymentStmt = $pdo->prepare('INSERT INTO payments (order_id, method, amount, status, transaction_reference) VALUES (?, ?, ?, ?, ?)');
            $paymentStmt->execute([$orderId, $paymentMethod, $total, $paymentMethod === 'cod' ? 'pending' : 'initiated', null]);

            if ($coupon) {
                $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$coupon['id']]);
            }

            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $order['items'] = self::items($id);
        $order['payment'] = self::payment($id);
        return $order;
    }

    public static function findForTracking(string $orderNumber, string $phone): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE order_number = ? AND customer_phone = ? LIMIT 1');
        $stmt->execute([$orderNumber, $phone]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function items(int $orderId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function payment(int $orderId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function byUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT o.*, COUNT(oi.id) AS item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function adminList(array $filters = [], int $limit = 50): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT ' . (int)$limit);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function cancelUnpaid(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT o.status, p.status AS payment_status FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order || $order['status'] === 'cancelled' || $order['payment_status'] === 'paid') {
                $pdo->commit();
                return;
            }

            $items = self::items($id);
            $variantStmt = $pdo->prepare('UPDATE product_variants SET stock = stock + ? WHERE id = ?');
            $productStmt = $pdo->prepare('UPDATE products SET stock = stock + ?, sales_count = GREATEST(sales_count - ?, 0) WHERE id = ?');
            foreach ($items as $item) {
                $quantity = (int)$item['quantity'];
                $variantStmt->execute([$quantity, (int)$item['variant_id']]);
                $productStmt->execute([$quantity, $quantity, (int)$item['product_id']]);
            }

            $pdo->prepare("UPDATE payments SET status = 'cancelled' WHERE order_id = ? AND status <> 'paid'")->execute([$id]);
            $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function dashboardStats(): array
    {
        $pdo = Database::connection();
        return [
            'orders' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn(),
            'customers' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'pending' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','processing')")->fetchColumn(),
        ];
    }

    public static function salesReport(string $from, string $to): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT DATE(created_at) AS sale_date, COUNT(*) AS orders, SUM(subtotal) AS subtotal, SUM(discount_amount) AS discounts, SUM(total_amount) AS total
             FROM orders
             WHERE DATE(created_at) BETWEEN ? AND ? AND status <> "cancelled"
             GROUP BY DATE(created_at)
             ORDER BY sale_date'
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }
}
