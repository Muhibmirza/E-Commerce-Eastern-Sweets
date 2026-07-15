<?php
declare(strict_types=1);

final class Category
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name');
        return $stmt->fetchAll();
    }

    public static function adminAll(): array
    {
        return Database::connection()->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, image_path = ?, sort_order = ?, is_active = ?, banner_image_path = ?, banner_tagline = ?, banner_cta_text = ?, banner_cta_link = ?, banner_accent = ?, banner_animation = ? WHERE id = ?');
            $stmt->execute([
                $data['name'], $data['slug'], $data['image_path'], $data['sort_order'], $data['is_active'],
                $data['banner_image_path'] ?? null, $data['banner_tagline'] ?? null, $data['banner_cta_text'] ?? null,
                $data['banner_cta_link'] ?? null, $data['banner_accent'] ?? null, $data['banner_animation'] ?? 'slide-3d', $id,
            ]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug, image_path, sort_order, is_active, banner_image_path, banner_tagline, banner_cta_text, banner_cta_link, banner_accent, banner_animation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'], $data['slug'], $data['image_path'], $data['sort_order'], $data['is_active'],
            $data['banner_image_path'] ?? null, $data['banner_tagline'] ?? null, $data['banner_cta_text'] ?? null,
            $data['banner_cta_link'] ?? null, $data['banner_accent'] ?? null, $data['banner_animation'] ?? 'slide-3d',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE categories SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
