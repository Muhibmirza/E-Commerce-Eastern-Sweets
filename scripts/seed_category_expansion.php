<?php
declare(strict_types=1);

require __DIR__ . '/../includes/db.php';

$pdo = Database::connection();
$migration = '20260826_storefront_refresh';
$pdo->exec('CREATE TABLE IF NOT EXISTS app_migrations (
    migration_key VARCHAR(120) PRIMARY KEY,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
$migrationCheck = $pdo->prepare('SELECT 1 FROM app_migrations WHERE migration_key = ? LIMIT 1');
$migrationCheck->execute([$migration]);
if ($migrationCheck->fetchColumn()) {
    echo "Storefront refresh already applied.\n";
    exit;
}
$pdo->beginTransaction();

function slugify_seed(string $value): string
{
    $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
    return $slug !== '' ? $slug : 'item-' . random_int(1000, 9999);
}

function category_id(PDO $pdo, array $data): int
{
    $oldSlug = $data['old_slug'] ?? null;
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$data['slug']]);
    $id = $stmt->fetchColumn();
    if (!$id && $oldSlug) {
        $stmt->execute([$oldSlug]);
        $id = $stmt->fetchColumn();
    }

    $payload = [
        $data['name'],
        $data['slug'],
        $data['image'],
        $data['banner_image'],
        $data['tagline'],
        'Shop ' . preg_replace('/\s*\(.*?\)\s*/', '', $data['name']),
        'category/' . $data['slug'],
        $data['accent'],
        $data['animation'] ?? 'slide-3d',
        $data['sort_order'],
        1,
    ];

    if ($id) {
        $update = $pdo->prepare('UPDATE categories SET name=?, slug=?, image_path=?, banner_image_path=?, banner_tagline=?, banner_cta_text=?, banner_cta_link=?, banner_accent=?, banner_animation=?, sort_order=?, is_active=? WHERE id=?');
        $update->execute([...$payload, (int)$id]);
        return (int)$id;
    }

    $insert = $pdo->prepare('INSERT INTO categories (name, slug, image_path, banner_image_path, banner_tagline, banner_cta_text, banner_cta_link, banner_accent, banner_animation, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute($payload);
    return (int)$pdo->lastInsertId();
}

function product_seed(PDO $pdo, int $categoryId, array $data): void
{
    $slug = slugify_seed($data['name']);
    $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $id = $stmt->fetchColumn();
    $payload = [
        $categoryId,
        $data['name'],
        $slug,
        $data['short'],
        $data['description'],
        $data['ingredients'],
        $data['allergens'],
        $data['stock'],
        1,
        $data['featured'],
    ];

    if ($id) {
        $update = $pdo->prepare('UPDATE products SET category_id=?, name=?, slug=?, short_description=?, description=?, ingredients=?, allergens=?, stock=?, is_active=?, is_featured=? WHERE id=?');
        $update->execute([...$payload, (int)$id]);
        $productId = (int)$id;
    } else {
        $insert = $pdo->prepare('INSERT INTO products (category_id, name, slug, short_description, description, ingredients, allergens, stock, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->execute($payload);
        $productId = (int)$pdo->lastInsertId();
    }

    $variant = $pdo->prepare('SELECT id FROM product_variants WHERE product_id = ? ORDER BY id LIMIT 1');
    $variant->execute([$productId]);
    $variantId = $variant->fetchColumn();
    if ($variantId) {
        $updateVariant = $pdo->prepare('UPDATE product_variants SET variant_name=?, price=?, compare_at_price=?, stock=? WHERE id=?');
        $updateVariant->execute([$data['variant'], $data['price'], null, $data['stock'], (int)$variantId]);
    } else {
        $sku = 'ES-' . $productId . '-' . strtoupper(substr(sha1($slug), 0, 8));
        $insertVariant = $pdo->prepare('INSERT INTO product_variants (product_id, variant_name, price, compare_at_price, stock, sku) VALUES (?, ?, ?, NULL, ?, ?)');
        $insertVariant->execute([$productId, $data['variant'], $data['price'], $data['stock'], $sku]);
    }

    $image = $pdo->prepare('SELECT id FROM product_images WHERE product_id = ? AND is_primary = 1 ORDER BY id LIMIT 1');
    $image->execute([$productId]);
    $imageId = $image->fetchColumn();
    if ($imageId) {
        $updateImage = $pdo->prepare('UPDATE product_images SET image_path=?, sort_order=1 WHERE id=?');
        $updateImage->execute([$data['image'], (int)$imageId]);
    } else {
        $insertImage = $pdo->prepare('INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 1)');
        $insertImage->execute([$productId, $data['image']]);
    }
}

$categories = [
    'ice-cream-scoops' => ['name' => 'Ice Cream (Ice Scoop)', 'slug' => 'ice-cream-scoops', 'image' => 'public/assets/images/categories/ice-cream.jpg', 'banner_image' => 'uploads/categories/ice-cream-scoops.png', 'tagline' => 'Creamy scoops served fresh and chilled', 'accent' => '#d85f88', 'sort_order' => 1],
    'samosa-rolls' => ['name' => 'Samosa & Rolls', 'slug' => 'samosa-rolls', 'image' => 'public/assets/images/categories/samosa-rolls.jpg', 'banner_image' => 'uploads/categories/samosa-rolls.png', 'tagline' => 'Crispy savoury bites with chutney dips', 'accent' => '#c98228', 'sort_order' => 2],
    'cakes-pastries' => ['name' => 'Cakes & Pastries', 'slug' => 'cakes-pastries', 'image' => 'public/assets/images/categories/cakes-pastries.jpg', 'banner_image' => 'uploads/categories/cakes-pastries.png', 'tagline' => 'Soft cakes, layered pastries, and fresh cream treats', 'accent' => '#9b4f3f', 'sort_order' => 3],
    'kulfi-falooda' => ['name' => 'Kulfi & Falooda', 'slug' => 'kulfi-falooda', 'image' => 'public/assets/images/categories/kulfi-falooda.jpg', 'banner_image' => 'uploads/categories/kulfi-falooda.png', 'tagline' => 'Classic kulfi and chilled falooda favourites', 'accent' => '#c6476f', 'sort_order' => 4],
    'dairy' => ['name' => 'Dairy', 'slug' => 'dairy', 'image' => 'public/assets/images/categories/dairy.jpg', 'banner_image' => 'uploads/categories/dairy.png', 'tagline' => 'Fresh milk, butter, yogurt, and dairy essentials', 'accent' => '#ead9b7', 'sort_order' => 5],
    'gift-boxes' => ['name' => 'Gift Boxes', 'slug' => 'gift-boxes', 'image' => 'public/assets/images/categories/gift-boxes.jpg', 'banner_image' => 'uploads/categories/gift-boxes.png', 'tagline' => 'Beautifully packed gifts for every occasion', 'accent' => '#d1ad57', 'sort_order' => 6],
    'packed-items' => ['name' => 'Packed Items (Chips & Snacks)', 'slug' => 'packed-items', 'image' => 'public/assets/images/categories/packed-snacks.jpg', 'banner_image' => 'uploads/categories/packed-items.png', 'tagline' => 'Crunchy chips, nimco, and snack packs', 'accent' => '#e3442f', 'sort_order' => 7],
    'dry-fruits' => ['name' => 'Dry Fruits', 'slug' => 'dry-fruits', 'old_slug' => 'dry-fruit-sweets', 'image' => 'public/assets/images/categories/dry-fruits.jpg', 'banner_image' => 'public/assets/images/products/product-37.png', 'tagline' => 'Premium almonds, cashews, pistachios, and mixed dry fruits', 'accent' => '#b97738', 'sort_order' => 8],
    'halwa' => ['name' => 'Halwa', 'slug' => 'halwa', 'image' => 'public/assets/images/products/product-41.png', 'banner_image' => 'public/assets/images/products/product-41.png', 'tagline' => 'Rich traditional halwa in every bite', 'accent' => '#b8792b', 'sort_order' => 9],
    'bakery-items' => ['name' => 'Bakery Items', 'slug' => 'bakery-items', 'image' => 'public/assets/images/categories/bakery-items.jpg', 'banner_image' => 'uploads/categories/bakery-items.png', 'tagline' => 'Freshly baked breads, donuts, biscuits, and pastries', 'accent' => '#be7b38', 'sort_order' => 10],
    'mithai' => ['name' => 'Mithai (Sweet Box)', 'slug' => 'mithai', 'image' => 'public/assets/images/products/product-33.png', 'banner_image' => 'uploads/categories/mithai-gold-platter.png', 'tagline' => 'Handcrafted mithai and sweet boxes made fresh daily', 'accent' => '#d1ad57', 'animation' => 'mithai-spin', 'sort_order' => 11],
];

$ids = [];
foreach ($categories as $key => $category) {
    $ids[$key] = category_id($pdo, $category);
}

$products = [
    'ice-cream-scoops' => [
        ['name' => 'Assorted Ice Cream Bowl', 'short' => 'A colourful bowl of premium ice cream scoops.', 'description' => 'Fresh assorted scoops with classic flavours and toppings.', 'ingredients' => 'Milk, cream, sugar, flavouring', 'allergens' => 'Milk, nuts', 'variant' => 'Bowl', 'price' => 650, 'stock' => 30, 'featured' => 1],
        ['name' => 'Chocolate Ice Scoop', 'short' => 'Rich chocolate scoop with smooth cream texture.', 'description' => 'A deep chocolate ice cream scoop served chilled.', 'ingredients' => 'Milk, cocoa, cream, sugar', 'allergens' => 'Milk', 'variant' => 'Single scoop', 'price' => 220, 'stock' => 45, 'featured' => 0],
        ['name' => 'Pistachio Ice Scoop', 'short' => 'Pista flavoured ice cream with nutty finish.', 'description' => 'Creamy pistachio scoop for classic dessert lovers.', 'ingredients' => 'Milk, pistachio, cream, sugar', 'allergens' => 'Milk, nuts', 'variant' => 'Single scoop', 'price' => 240, 'stock' => 45, 'featured' => 0],
    ],
    'samosa-rolls' => [
        ['name' => 'Chicken Samosa Platter', 'short' => 'Crispy chicken samosas with chutney.', 'description' => 'Golden fried samosas filled with spiced chicken.', 'ingredients' => 'Flour, chicken, spices, oil', 'allergens' => 'Wheat', 'variant' => '12 pcs', 'price' => 900, 'stock' => 35, 'featured' => 1],
        ['name' => 'Vegetable Samosa', 'short' => 'Classic vegetable samosas with potato filling.', 'description' => 'Crispy samosas filled with seasoned potatoes and peas.', 'ingredients' => 'Flour, potato, peas, spices', 'allergens' => 'Wheat', 'variant' => '12 pcs', 'price' => 720, 'stock' => 40, 'featured' => 0],
        ['name' => 'Chicken Roll Platter', 'short' => 'Fresh fried rolls with chicken filling.', 'description' => 'Crispy rolls packed with savoury chicken filling.', 'ingredients' => 'Flour, chicken, vegetables, spices', 'allergens' => 'Wheat', 'variant' => '12 pcs', 'price' => 980, 'stock' => 30, 'featured' => 0],
    ],
    'cakes-pastries' => [
        ['name' => 'Chocolate Pastry Box', 'short' => 'Layered chocolate pastries with cream.', 'description' => 'Soft sponge and chocolate cream pastries for dessert tables.', 'ingredients' => 'Flour, cocoa, cream, sugar, eggs', 'allergens' => 'Wheat, milk, eggs', 'variant' => '6 pcs', 'price' => 1350, 'stock' => 25, 'featured' => 1],
        ['name' => 'Strawberry Cake Slice', 'short' => 'Fresh strawberry cream cake slice.', 'description' => 'Light sponge layered with cream and strawberry glaze.', 'ingredients' => 'Flour, cream, strawberry, sugar, eggs', 'allergens' => 'Wheat, milk, eggs', 'variant' => 'Slice', 'price' => 420, 'stock' => 35, 'featured' => 0],
        ['name' => 'Mini Pastry Assortment', 'short' => 'Mixed pastries for small gatherings.', 'description' => 'A mixed box of fresh mini pastries and cream desserts.', 'ingredients' => 'Flour, cream, chocolate, fruit', 'allergens' => 'Wheat, milk, eggs', 'variant' => '12 pcs', 'price' => 2400, 'stock' => 20, 'featured' => 0],
    ],
    'kulfi-falooda' => [
        ['name' => 'Special Falooda Glass', 'short' => 'Chilled falooda with kulfi and rose syrup.', 'description' => 'Classic falooda layered with milk, vermicelli, kulfi, and rose syrup.', 'ingredients' => 'Milk, kulfi, vermicelli, basil seeds, rose syrup', 'allergens' => 'Milk, nuts', 'variant' => 'Glass', 'price' => 520, 'stock' => 35, 'featured' => 1],
        ['name' => 'Pista Kulfi Stick', 'short' => 'Creamy pista kulfi served on stick.', 'description' => 'Traditional pistachio kulfi with dense milk flavour.', 'ingredients' => 'Milk, pistachio, sugar, cardamom', 'allergens' => 'Milk, nuts', 'variant' => '6 pcs', 'price' => 780, 'stock' => 35, 'featured' => 0],
        ['name' => 'Kulfi Falooda Cup', 'short' => 'Kulfi falooda in a handy cup.', 'description' => 'A chilled cup with kulfi, falooda, and sweet syrup.', 'ingredients' => 'Milk, kulfi, falooda, syrup', 'allergens' => 'Milk, nuts', 'variant' => 'Cup', 'price' => 450, 'stock' => 35, 'featured' => 0],
    ],
    'dairy' => [
        ['name' => 'Fresh Milk Bottle', 'short' => 'Fresh dairy milk for daily use.', 'description' => 'Clean packed fresh milk for homes and offices.', 'ingredients' => 'Milk', 'allergens' => 'Milk', 'variant' => '1 litre', 'price' => 260, 'stock' => 60, 'featured' => 0],
        ['name' => 'Fresh Yogurt Bowl', 'short' => 'Thick plain yogurt made fresh.', 'description' => 'Creamy yogurt for meals, desserts, and raita.', 'ingredients' => 'Milk, yogurt culture', 'allergens' => 'Milk', 'variant' => '500g', 'price' => 320, 'stock' => 45, 'featured' => 0],
        ['name' => 'Butter Pack', 'short' => 'Fresh butter for bakery and breakfast.', 'description' => 'Smooth butter packed for fresh use.', 'ingredients' => 'Cream, salt', 'allergens' => 'Milk', 'variant' => '250g', 'price' => 520, 'stock' => 30, 'featured' => 0],
    ],
    'packed-items' => [
        ['name' => 'Potato Chips Pack', 'short' => 'Crispy potato chips for snack time.', 'description' => 'Crunchy salted chips packed fresh.', 'ingredients' => 'Potato, oil, salt', 'allergens' => 'May contain gluten', 'variant' => 'Pack', 'price' => 160, 'stock' => 80, 'featured' => 0],
        ['name' => 'Masala Chips Pack', 'short' => 'Spicy masala chips with bold flavour.', 'description' => 'Packed chips with savoury masala seasoning.', 'ingredients' => 'Potato, oil, spices', 'allergens' => 'May contain gluten', 'variant' => 'Pack', 'price' => 180, 'stock' => 80, 'featured' => 0],
        ['name' => 'Snack Combo Pack', 'short' => 'Mixed chips and snacks combo.', 'description' => 'A quick snack combo for sharing.', 'ingredients' => 'Potato, corn, spices, oil', 'allergens' => 'May contain gluten', 'variant' => 'Combo', 'price' => 650, 'stock' => 35, 'featured' => 0],
    ],
    'dry-fruits' => [
        ['name' => 'Premium Almonds', 'short' => 'Selected quality almonds.', 'description' => 'Premium almonds for gifting, snacking, and desserts.', 'ingredients' => 'Almonds', 'allergens' => 'Nuts', 'variant' => '250g', 'price' => 980, 'stock' => 35, 'featured' => 0],
        ['name' => 'Roasted Cashews', 'short' => 'Fresh roasted cashews.', 'description' => 'Rich cashews roasted for a premium snack.', 'ingredients' => 'Cashews', 'allergens' => 'Nuts', 'variant' => '250g', 'price' => 1250, 'stock' => 30, 'featured' => 0],
        ['name' => 'Mixed Dry Fruit Box', 'short' => 'Almonds, cashews, pistachios, and raisins.', 'description' => 'A balanced dry fruit mix for gifting and daily use.', 'ingredients' => 'Almonds, cashews, pistachios, raisins', 'allergens' => 'Nuts', 'variant' => '500g', 'price' => 2250, 'stock' => 25, 'featured' => 1],
    ],
    'gift-boxes' => [
        ['name' => 'Luxury Gift Box', 'short' => 'Colourful premium gift box arrangement.', 'description' => 'A premium gifting box for celebrations and corporate occasions.', 'ingredients' => 'Gift packaging, assorted sweets', 'allergens' => 'Milk, nuts, wheat', 'variant' => 'Box', 'price' => 3600, 'stock' => 20, 'featured' => 1],
    ],
    'bakery-items' => [
        ['name' => 'Fresh Croissant Basket', 'short' => 'Flaky baked croissants.', 'description' => 'Fresh croissants baked golden and soft inside.', 'ingredients' => 'Flour, butter, yeast, milk', 'allergens' => 'Wheat, milk', 'variant' => '6 pcs', 'price' => 900, 'stock' => 28, 'featured' => 0],
        ['name' => 'Assorted Donuts', 'short' => 'Soft donuts with classic glazes.', 'description' => 'Fresh donuts for tea time and dessert tables.', 'ingredients' => 'Flour, sugar, yeast, glaze', 'allergens' => 'Wheat, milk, eggs', 'variant' => '6 pcs', 'price' => 960, 'stock' => 30, 'featured' => 0],
        ['name' => 'Bakery Bread Basket', 'short' => 'Mixed breads and baked favourites.', 'description' => 'Freshly baked breads and bakery treats.', 'ingredients' => 'Flour, yeast, butter, sugar', 'allergens' => 'Wheat, milk', 'variant' => 'Basket', 'price' => 1450, 'stock' => 22, 'featured' => 1],
    ],
    'mithai' => [
        ['name' => 'Signature Sweet Box', 'short' => 'Assorted mithai packed in a premium sweet box.', 'description' => 'A signature Eastern Sweets box with fresh assorted mithai.', 'ingredients' => 'Milk, sugar, nuts, ghee', 'allergens' => 'Milk, nuts', 'variant' => 'Box', 'price' => 3200, 'stock' => 25, 'featured' => 1],
    ],
];

foreach ($products as $categoryKey => $items) {
    foreach ($items as $item) {
        $item['image'] = $categories[$categoryKey]['image'];
        product_seed($pdo, $ids[$categoryKey], $item);
    }
}

$settings = $pdo->prepare('INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group)');
$settings->execute(['background_pattern', 'public/assets/images/pattern.png', 'branding']);
$recordMigration = $pdo->prepare('INSERT INTO app_migrations (migration_key) VALUES (?)');
$recordMigration->execute([$migration]);
$pdo->commit();
echo "Category and product expansion seeded.\n";
