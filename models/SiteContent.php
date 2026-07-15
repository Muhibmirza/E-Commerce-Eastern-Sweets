<?php
declare(strict_types=1);

final class SiteContent
{
    public static function settings(): array
    {
        $rows = Database::connection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function setSettings(array $settings): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group)');
        foreach ($settings as $key => $value) {
            $group = str_starts_with((string)$key, 'footer_') ? 'footer' : (str_starts_with((string)$key, 'theme_') || in_array($key, ['site_logo', 'background_pattern'], true) ? 'branding' : 'homepage');
            $stmt->execute([(string)$key, (string)$value, $group]);
        }
    }

    public static function uspBlocks(bool $admin = false): array
    {
        $sql = 'SELECT * FROM usp_blocks' . ($admin ? '' : ' WHERE is_active = 1') . ' ORDER BY sort_order, id';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function uspFind(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM usp_blocks WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function uspSave(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare('UPDATE usp_blocks SET title=?, description=?, icon_text=?, icon_image_path=?, sort_order=?, is_active=? WHERE id=?');
            $stmt->execute([$data['title'], $data['description'], $data['icon_text'], $data['icon_image_path'], $data['sort_order'], $data['is_active'], $id]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO usp_blocks (title, description, icon_text, icon_image_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['title'], $data['description'], $data['icon_text'], $data['icon_image_path'], $data['sort_order'], $data['is_active']]);
        return (int)$pdo->lastInsertId();
    }

    public static function uspDelete(int $id): void
    {
        Database::connection()->prepare('UPDATE usp_blocks SET is_active = 0 WHERE id = ?')->execute([$id]);
    }

    public static function testimonials(bool $admin = false): array
    {
        $sql = 'SELECT * FROM testimonials' . ($admin ? '' : ' WHERE is_active = 1') . ' ORDER BY sort_order, id';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function testimonialFind(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM testimonials WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function testimonialSave(array $data, ?int $id = null): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare('UPDATE testimonials SET customer_name=?, customer_photo_path=?, rating=?, review_text=?, sort_order=?, is_active=? WHERE id=?');
            $stmt->execute([$data['customer_name'], $data['customer_photo_path'], $data['rating'], $data['review_text'], $data['sort_order'], $data['is_active'], $id]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO testimonials (customer_name, customer_photo_path, rating, review_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['customer_name'], $data['customer_photo_path'], $data['rating'], $data['review_text'], $data['sort_order'], $data['is_active']]);
        return (int)$pdo->lastInsertId();
    }

    public static function testimonialDelete(int $id): void
    {
        Database::connection()->prepare('UPDATE testimonials SET is_active = 0 WHERE id = ?')->execute([$id]);
    }

    public static function staticPages(): array
    {
        return Database::connection()->query('SELECT * FROM static_pages ORDER BY FIELD(slug, "about", "contact", "faqs", "terms", "privacy", "refund"), slug')->fetchAll();
    }

    public static function staticPage(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM static_pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function staticPageSave(string $slug, string $title, string $body): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO static_pages (slug, title, body) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body)');
        $stmt->execute([$slug, $title, $body]);
    }
}
