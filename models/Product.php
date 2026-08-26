<?php
declare(strict_types=1);

final class Product
{
    public static function featured(int $limit = 8): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id LIMIT 1) AS image_path,
                (SELECT MIN(price) FROM product_variants WHERE product_id = p.id) AS min_price
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function gallery(int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.id, p.name,
                (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS image_path
             FROM products p
             WHERE p.is_active = 1
               AND EXISTS (SELECT 1 FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1)
             ORDER BY p.is_featured DESC, p.sales_count DESC, p.id
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function search(array $filters = [], int $limit = 24, int $offset = 0): array
    {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        if (($filters['status'] ?? '') === 'active') {
            $where[] = 'p.is_active = 1';
        } elseif (($filters['status'] ?? '') === 'hidden') {
            $where[] = 'p.is_active = 0';
        }
        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR p.short_description LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['min_price'])) {
            $where[] = 'COALESCE(v.min_price, 0) >= ?';
            $params[] = (float)$filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'COALESCE(v.min_price, 0) <= ?';
            $params[] = (float)$filters['max_price'];
        }

        $order = 'p.created_at DESC';
        if (($filters['sort'] ?? '') === 'price_asc') {
            $order = 'v.min_price ASC';
        } elseif (($filters['sort'] ?? '') === 'price_desc') {
            $order = 'v.min_price DESC';
        } elseif (($filters['sort'] ?? '') === 'popular') {
            $order = 'p.sales_count DESC, p.name ASC';
        }

        $sql = 'SELECT p.*, c.name AS category_name, img.image_path, v.min_price
                FROM products p
                JOIN categories c ON c.id = p.category_id
                LEFT JOIN (SELECT product_id, MIN(price) AS min_price FROM product_variants GROUP BY product_id) v ON v.product_id = p.id
                LEFT JOIN (SELECT product_id, MIN(image_path) AS image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) img ON img.product_id = p.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY ' . $order . '
                LIMIT ? OFFSET ?';

        $stmt = Database::connection()->prepare($sql);
        $i = 1;
        foreach ($params as $param) {
            $stmt->bindValue($i++, $param);
        }
        $stmt->bindValue($i++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($i, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(array $filters = []): int
    {
        $where = ['is_active = 1'];
        $params = [];
        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(name LIKE ? OR short_description LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM products WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ? AND p.is_active = 1
             LIMIT 1'
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) {
            return null;
        }
        $product['images'] = self::images($id);
        $product['variants'] = self::variants($id);
        $product['reviews'] = self::reviews($id);
        return $product;
    }

    public static function adminFind(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['variants'] = self::variants($id);
        $row['images'] = self::images($id);
        return $row;
    }

    public static function images(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function variants(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY price');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function reviews(int $id): array
    {
        $stmt = Database::connection()->prepare('SELECT r.*, u.name FROM reviews r LEFT JOIN users u ON u.id = r.user_id WHERE product_id = ? AND is_approved = 1 ORDER BY r.created_at DESC LIMIT 8');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function related(int $productId, int $categoryId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.*, img.image_path, v.min_price
             FROM products p
             LEFT JOIN (SELECT product_id, MIN(price) AS min_price FROM product_variants GROUP BY product_id) v ON v.product_id = p.id
             LEFT JOIN (SELECT product_id, MIN(image_path) AS image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) img ON img.product_id = p.id
             WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1
             ORDER BY p.sales_count DESC LIMIT 4'
        );
        $stmt->execute([$categoryId, $productId]);
        return $stmt->fetchAll();
    }

    public static function adminList(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR c.name LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        $stmt = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name, img.image_path, v.min_price
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN (SELECT product_id, MIN(price) AS min_price FROM product_variants GROUP BY product_id) v ON v.product_id = p.id
             LEFT JOIN (SELECT product_id, MIN(image_path) AS image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) img ON img.product_id = p.id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.id DESC LIMIT 50'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, name=?, slug=?, short_description=?, description=?, ingredients=?, allergens=?, stock=?, is_active=?, is_featured=? WHERE id=?');
            $stmt->execute([
                $data['category_id'], $data['name'], $data['slug'], $data['short_description'], $data['description'],
                $data['ingredients'], $data['allergens'], $data['stock'], $data['is_active'], $data['is_featured'], $id,
            ]);
            self::savePrimaryVariant($pdo, $id, $data);
            self::savePrimaryImage($pdo, $id, $data['image_path'] ?? '');
            self::saveGalleryImages($pdo, $id, $data['gallery_images'] ?? []);
            $pdo->commit();
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO products (category_id, name, slug, short_description, description, ingredients, allergens, stock, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['category_id'], $data['name'], $data['slug'], $data['short_description'], $data['description'],
            $data['ingredients'], $data['allergens'], $data['stock'], $data['is_active'], $data['is_featured'],
        ]);
        $newId = (int)$pdo->lastInsertId();
        self::savePrimaryVariant($pdo, $newId, $data);
        self::savePrimaryImage($pdo, $newId, $data['image_path'] ?? 'public/assets/images/products/product-01.png');
        self::saveGalleryImages($pdo, $newId, $data['gallery_images'] ?? []);
        $pdo->commit();
        return $newId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE products SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function deleteImage(int $imageId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM product_images WHERE id = ? AND is_primary = 0');
        $stmt->execute([$imageId]);
    }

    private static function savePrimaryVariant(PDO $pdo, int $productId, array $data): void
    {
        $variant = self::variants($productId)[0] ?? null;
        $variantName = trim((string)($data['variant_name'] ?? '500g')) ?: '500g';
        $price = max(0, (float)($data['price'] ?? 0));
        $compare = ($data['compare_at_price'] ?? '') === '' ? null : (float)$data['compare_at_price'];
        $stock = max(0, (int)($data['variant_stock'] ?? $data['stock'] ?? 0));
        if ($variant) {
            $stmt = $pdo->prepare('UPDATE product_variants SET variant_name = ?, price = ?, compare_at_price = ?, stock = ? WHERE id = ?');
            $stmt->execute([$variantName, $price, $compare, $stock, $variant['id']]);
            return;
        }
        $sku = 'ES-' . $productId . '-' . strtoupper(substr(sha1($variantName . microtime(true)), 0, 8));
        $stmt = $pdo->prepare('INSERT INTO product_variants (product_id, variant_name, price, compare_at_price, stock, sku) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$productId, $variantName, $price, $compare, $stock, $sku]);
    }

    private static function savePrimaryImage(PDO $pdo, int $productId, string $imagePath): void
    {
        $imagePath = trim($imagePath);
        if ($imagePath === '') {
            return;
        }
        $image = self::images($productId)[0] ?? null;
        if ($image) {
            $stmt = $pdo->prepare('UPDATE product_images SET image_path = ?, is_primary = 1 WHERE id = ?');
            $stmt->execute([$imagePath, $image['id']]);
            return;
        }
        $stmt = $pdo->prepare('INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 1)');
        $stmt->execute([$productId, $imagePath]);
    }

    private static function saveGalleryImages(PDO $pdo, int $productId, array $paths): void
    {
        $stmt = $pdo->prepare('INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 0, ?)');
        $sort = count(self::images($productId)) + 1;
        foreach ($paths as $path) {
            $path = trim((string)$path);
            if ($path === '') {
                continue;
            }
            $stmt->execute([$productId, $path, $sort++]);
        }
    }
}
