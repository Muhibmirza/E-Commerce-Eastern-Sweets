CREATE DATABASE IF NOT EXISTS eastern_sweets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eastern_sweets;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS app_migrations, newsletter_subscribers, settings, banners, reviews, coupons, payments, order_items, orders, product_variants, product_images, products, categories, admins, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(32) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  default_address TEXT NULL,
  is_blocked TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('owner','manager','staff') NOT NULL DEFAULT 'manager',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  image_path VARCHAR(255) NOT NULL,
  banner_image_path VARCHAR(255) NULL,
  banner_tagline VARCHAR(255) NULL,
  banner_cta_text VARCHAR(100) NULL,
  banner_cta_link VARCHAR(255) NULL,
  banner_accent VARCHAR(20) NULL,
  banner_animation VARCHAR(50) NOT NULL DEFAULT 'slide-3d',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  short_description VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  ingredients TEXT NOT NULL,
  allergens VARCHAR(255) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  sales_count INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_category (category_id),
  INDEX idx_products_active (is_active, is_featured),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  INDEX idx_product_images_product (product_id),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_variants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  variant_name VARCHAR(80) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  compare_at_price DECIMAL(10,2) NULL,
  stock INT NOT NULL DEFAULT 0,
  sku VARCHAR(80) NOT NULL UNIQUE,
  INDEX idx_product_variants_product (product_id),
  CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(32) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(32) NOT NULL,
  delivery_address TEXT NOT NULL,
  city VARCHAR(80) NOT NULL,
  area VARCHAR(100) NOT NULL,
  notes TEXT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  delivery_charge DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(40) NOT NULL,
  status ENUM('pending','confirmed','processing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_status (status),
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_phone (customer_phone),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  variant_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(160) NOT NULL,
  variant_name VARCHAR(80) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  INDEX idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_order_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  method VARCHAR(40) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(40) NOT NULL,
  transaction_reference VARCHAR(120) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payments_status (status),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE coupons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  discount_type ENUM('percent','fixed') NOT NULL,
  discount_value DECIMAL(10,2) NOT NULL,
  expires_at DATE NULL,
  usage_limit INT NULL,
  used_count INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  rating TINYINT NOT NULL,
  comment TEXT NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reviews_product (product_id),
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE banners (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  subtitle VARCHAR(120) NOT NULL,
  description VARCHAR(255) NOT NULL,
  button_text VARCHAR(40) NOT NULL,
  link_url VARCHAR(255) NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  setting_group VARCHAR(60) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (name, email, password_hash, role) VALUES
('Eastern Admin', 'admin@easternsweets.com', '$2y$10$a7xmGR7A2EAj5t3txG6JF.NIPB3SP7.1k/NeVyZjjlfnRvB0LbP5W', 'owner');

INSERT INTO users (name, email, phone, password_hash, default_address) VALUES
('Muhib Mirza', 'customer@easternsweets.com', '03104490798', '$2y$10$PsO/rblVSzrEIZTnv.6uK.IYrQf0xwL4waaNgSrrq0KoK8BwBHhnO', 'Gulshan-e-Iqbal, Karachi'),
('Ayesha Khan', 'ayesha@example.com', '03001234567', '$2y$10$PsO/rblVSzrEIZTnv.6uK.IYrQf0xwL4waaNgSrrq0KoK8BwBHhnO', 'DHA, Karachi'),
('Hamza Rauf', 'hamza@example.com', '03019876543', '$2y$10$PsO/rblVSzrEIZTnv.6uK.IYrQf0xwL4waaNgSrrq0KoK8BwBHhnO', 'PECHS, Karachi');

INSERT INTO categories (name, slug, image_path, banner_image_path, banner_tagline, banner_cta_text, banner_cta_link, banner_accent, banner_animation, sort_order) VALUES
('Mithai (Sweet Box)', 'mithai', 'public/assets/images/products/product-33.png', 'uploads/categories/mithai-gold-platter.png', 'Handcrafted mithai and sweet boxes made fresh daily', 'Shop Mithai', 'category/mithai', '#d1ad57', 'mithai-spin', 11),
('Halwa', 'halwa', 'public/assets/images/products/product-41.png', 'public/assets/images/products/product-41.png', 'Rich traditional halwa in every bite', 'Shop Halwa', 'category/halwa', '#b8792b', 'slide-3d', 9),
('Dry Fruits', 'dry-fruits', 'public/assets/images/categories/dry-fruits.jpg', 'public/assets/images/products/product-37.png', 'Premium almonds, cashews, pistachios, and mixed dry fruits', 'Shop Dry Fruits', 'category/dry-fruits', '#b97738', 'slide-3d', 8),
('Bakery Items', 'bakery-items', 'public/assets/images/categories/bakery-items.jpg', 'uploads/categories/bakery-items.png', 'Freshly baked breads, donuts, biscuits, and pastries', 'Shop Bakery Items', 'category/bakery-items', '#be7b38', 'slide-3d', 10),
('Gift Boxes', 'gift-boxes', 'public/assets/images/categories/gift-boxes.jpg', 'uploads/categories/gift-boxes.png', 'Beautifully packed gifts for every occasion', 'Shop Gift Boxes', 'category/gift-boxes', '#d1ad57', 'slide-3d', 6),
('Ice Cream (Ice Scoop)', 'ice-cream-scoops', 'public/assets/images/categories/ice-cream.jpg', 'uploads/categories/ice-cream-scoops.png', 'Creamy scoops served fresh and chilled', 'Shop Ice Cream', 'category/ice-cream-scoops', '#d85f88', 'slide-3d', 1),
('Samosa & Rolls', 'samosa-rolls', 'public/assets/images/categories/samosa-rolls.jpg', 'uploads/categories/samosa-rolls.png', 'Crispy savoury bites with chutney dips', 'Shop Samosa & Rolls', 'category/samosa-rolls', '#c98228', 'slide-3d', 2),
('Cakes & Pastries', 'cakes-pastries', 'public/assets/images/categories/cakes-pastries.jpg', 'uploads/categories/cakes-pastries.png', 'Soft cakes, layered pastries, and fresh cream treats', 'Shop Cakes & Pastries', 'category/cakes-pastries', '#9b4f3f', 'slide-3d', 3),
('Kulfi & Falooda', 'kulfi-falooda', 'public/assets/images/categories/kulfi-falooda.jpg', 'uploads/categories/kulfi-falooda.png', 'Classic kulfi and chilled falooda favourites', 'Shop Kulfi & Falooda', 'category/kulfi-falooda', '#c6476f', 'slide-3d', 4),
('Dairy', 'dairy', 'public/assets/images/categories/dairy.jpg', 'uploads/categories/dairy.png', 'Fresh milk, butter, yogurt, and dairy essentials', 'Shop Dairy', 'category/dairy', '#ead9b7', 'slide-3d', 5),
('Packed Items (Chips & Snacks)', 'packed-items', 'public/assets/images/categories/packed-snacks.jpg', 'uploads/categories/packed-items.png', 'Crunchy chips, nimco, and snack packs', 'Shop Packed Items', 'category/packed-items', '#e3442f', 'slide-3d', 7);

INSERT INTO products (category_id, name, slug, short_description, description, ingredients, allergens, stock, sales_count, is_featured) VALUES
(1,'Assorted Mithai Box','assorted-mithai-box','A premium mix of Eastern Sweets classics.','A celebration-ready assortment with gulab jamun, cham cham, barfi, laddu, and dry fruit mithai packed fresh.','Milk, sugar, khoya, nuts, ghee','Milk, nuts',60,85,1),
(1,'Gulab Jamun','gulab-jamun','Soft syrup-soaked gulab jamun with almond garnish.','Warm, soft, and deeply fragrant gulab jamun prepared in fresh syrup for family servings and gifting.','Milk solids, flour, sugar syrup, cardamom','Milk, gluten',80,120,1),
(1,'Rasgulla','rasgulla','Light milk dumplings in delicate syrup.','A classic syrup mithai with a soft bite and balanced sweetness.','Milk, sugar, rose water','Milk',70,94,1),
(1,'Pink Cham Cham','pink-cham-cham','Soft coconut-topped pink cham cham.','A festive pink sweet finished with coconut for a clean, delicate finish.','Milk, sugar, coconut','Milk',45,48,0),
(1,'Laddu','laddu','Bright motichoor laddu for celebrations.','Golden laddu with a fragrant texture, made fresh for daily orders and event boxes.','Gram flour, ghee, sugar, cardamom','Gluten',75,101,1),
(1,'Balushahi','balushahi','Flaky traditional sweet with pistachio garnish.','A crisp yet tender mithai classic with a syrupy finish and nut garnish.','Flour, ghee, sugar, pistachio','Gluten, nuts',45,38,0),
(2,'Sohan Halwa','sohan-halwa','Dense caramel halwa with sliced almonds.','A chewy, nutty halwa with deep caramel notes and premium almond topping.','Wheat, ghee, sugar, almonds','Gluten, nuts',55,82,1),
(2,'Habshi Halwa','habshi-halwa','Rich dark halwa with almonds.','A slow-cooked halwa with a rich dark finish, perfect for winter gifting.','Milk, ghee, wheat, nuts','Milk, gluten, nuts',35,52,1),
(2,'Pista Coconut Halwa','pista-coconut-halwa','Green pistachio coconut halwa.','Soft coconut halwa with pistachio notes and a fresh garnish.','Coconut, pistachio, sugar, milk','Milk, nuts',52,61,1),
(2,'Badam Halwa','badam-halwa','Creamy almond halwa with premium nuts.','A refined almond-forward halwa for premium gifting and dessert tables.','Almonds, milk, ghee, sugar','Milk, nuts',38,42,0),
(3,'Kaju Barfi','kaju-barfi','Smooth cashew barfi with a delicate finish.','A neat, premium barfi cut with a rich cashew base and mild sweetness.','Cashew, sugar, ghee','Nuts',48,73,1),
(3,'Pista Barfi','pista-barfi','Nut-studded pistachio barfi squares.','Creamy square barfi finished with almonds and pistachio for gifting.','Milk, pistachio, almond, sugar','Milk, nuts',50,79,1),
(3,'Coconut Pera','coconut-pera','Soft coconut pera with almond garnish.','Round coconut pera with a gentle grain and festive topping.','Coconut, milk, sugar, almond','Milk, nuts',62,44,0),
(3,'Dry Fruit Roll','dry-fruit-roll','Rolled mithai with nuts and fruit.','A premium roll packed with nuts, dried fruit, and a smooth sweet base.','Milk, sugar, almonds, pistachio, dried fruit','Milk, nuts',44,58,1),
(4,'Nimco Mix','nimco-mix','Crunchy savoury nimco for snacking.','A spiced savoury mix to balance sweet boxes and tea-time orders.','Lentils, gram flour, spices, oil','Gluten',90,39,0),
(4,'Bakery Cookies','bakery-cookies','Tea-time bakery cookies with sesame.','Crisp cookies for family tea and mixed bakery packs.','Flour, butter, sugar, sesame','Gluten, milk, sesame',95,33,0),
(4,'Jalebi','jalebi','Fresh crispy jalebi coils.','Crisp orange jalebi made for same-day ordering and celebration trays.','Flour, sugar syrup, saffron colour','Gluten',60,88,1),
(5,'Eid Gift Basket','eid-gift-basket','Assorted mithai arranged in a festive basket.','A premium basket with assorted mithai, dry fruit sweets, and festive decoration.','Mixed mithai, nuts, milk sweets','Milk, nuts, gluten',25,68,1),
(5,'Special Moments Box','special-moments-box','Elegant gift box for special occasions.','A signature gift box suitable for birthdays, weddings, and corporate orders.','Assorted mithai and dry fruit sweets','Milk, nuts, gluten',30,74,1),
(5,'Baby Celebration Box','baby-celebration-box','Themed sweets box for family announcements.','A soft-coloured celebration box for baby announcements and family events.','Assorted mithai, coconut sweets, barfi','Milk, nuts',28,36,0);

INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1,'public/assets/images/products/product-33.png',1,1),(1,'public/assets/images/products/product-10.png',0,2),
(2,'public/assets/images/products/product-32.png',1,1),(2,'public/assets/images/products/product-24.png',0,2),
(3,'public/assets/images/products/product-09.png',1,1),(3,'public/assets/images/products/product-45.png',0,2),
(4,'public/assets/images/products/product-34.png',1,1),(4,'public/assets/images/products/product-20.png',0,2),
(5,'public/assets/images/products/product-23.png',1,1),(5,'public/assets/images/products/product-25.png',0,2),
(6,'public/assets/images/products/product-19.png',1,1),
(7,'public/assets/images/products/product-41.png',1,1),(7,'public/assets/images/products/product-11.png',0,2),
(8,'public/assets/images/products/product-35.png',1,1),(8,'public/assets/images/products/product-42.png',0,2),
(9,'public/assets/images/products/product-16.png',1,1),(9,'public/assets/images/products/product-15.png',0,2),
(10,'public/assets/images/products/product-04.png',1,1),
(11,'public/assets/images/products/product-37.png',1,1),(11,'public/assets/images/products/product-38.png',0,2),
(12,'public/assets/images/products/product-31.png',1,1),(12,'public/assets/images/products/product-12.png',0,2),
(13,'public/assets/images/products/product-18.png',1,1),(13,'public/assets/images/products/product-44.png',0,2),
(14,'public/assets/images/products/product-39.png',1,1),
(15,'public/assets/images/products/product-06.png',1,1),
(16,'public/assets/images/products/product-41.png',1,1),
(17,'public/assets/images/products/product-36.png',1,1),(17,'public/assets/images/products/product-23.png',0,2),
(18,'public/assets/images/products/product-29.png',1,1),(18,'public/assets/images/products/product-45.png',0,2),
(19,'public/assets/images/products/product-04.png',1,1),(19,'public/assets/images/products/product-06.png',0,2),
(20,'public/assets/images/products/product-23.png',1,1);

INSERT INTO product_variants (product_id, variant_name, price, compare_at_price, stock, sku) VALUES
(1,'500g',1250,1400,30,'ES-AMB-500'),(1,'1kg',2350,2500,30,'ES-AMB-1KG'),
(2,'500g',850,NULL,40,'ES-GJ-500'),(2,'1kg',1600,NULL,40,'ES-GJ-1KG'),
(3,'500g',800,NULL,35,'ES-RSG-500'),(3,'1kg',1500,NULL,35,'ES-RSG-1KG'),
(4,'500g',900,NULL,25,'ES-CHM-500'),(4,'1kg',1700,NULL,20,'ES-CHM-1KG'),
(5,'500g',780,NULL,35,'ES-LAD-500'),(5,'1kg',1480,NULL,40,'ES-LAD-1KG'),
(6,'500g',820,NULL,25,'ES-BAL-500'),(6,'1kg',1580,NULL,20,'ES-BAL-1KG'),
(7,'500g',1450,1600,28,'ES-SH-500'),(7,'1kg',2750,2950,27,'ES-SH-1KG'),
(8,'500g',1500,NULL,18,'ES-HH-500'),(8,'1kg',2900,NULL,17,'ES-HH-1KG'),
(9,'500g',1200,NULL,26,'ES-PCH-500'),(9,'1kg',2300,NULL,26,'ES-PCH-1KG'),
(10,'500g',1800,NULL,18,'ES-BH-500'),(10,'1kg',3500,NULL,20,'ES-BH-1KG'),
(11,'500g',1700,1850,24,'ES-KB-500'),(11,'1kg',3300,3500,24,'ES-KB-1KG'),
(12,'500g',1550,NULL,25,'ES-PB-500'),(12,'1kg',3000,NULL,25,'ES-PB-1KG'),
(13,'500g',1100,NULL,32,'ES-CP-500'),(13,'1kg',2100,NULL,30,'ES-CP-1KG'),
(14,'500g',1900,NULL,22,'ES-DFR-500'),(14,'1kg',3700,NULL,22,'ES-DFR-1KG'),
(15,'250g',420,NULL,45,'ES-NIM-250'),(15,'500g',790,NULL,45,'ES-NIM-500'),
(16,'500g',950,NULL,45,'ES-CK-500'),(16,'1kg',1800,NULL,50,'ES-CK-1KG'),
(17,'500g',650,NULL,30,'ES-JAL-500'),(17,'1kg',1250,NULL,30,'ES-JAL-1KG'),
(18,'Basket',4200,4500,25,'ES-EGB-BASKET'),
(19,'Medium Box',2800,NULL,15,'ES-SMB-MED'),(19,'Large Box',5200,NULL,15,'ES-SMB-LRG'),
(20,'Medium Box',2600,NULL,14,'ES-BCB-MED'),(20,'Large Box',4900,NULL,14,'ES-BCB-LRG');

INSERT INTO coupons (code, discount_type, discount_value, expires_at, usage_limit) VALUES
('SWEET10','percent',10,'2027-12-31',200),
('EASTERN500','fixed',500,'2027-12-31',100);

INSERT INTO banners (title, subtitle, description, button_text, link_url, image_path, sort_order) VALUES
('Fresh Mithai Since 1969','Daily fresh batches','Order Eastern Sweets classics, premium dry fruit sweets, and elegant celebration boxes online.','Order Now','shop','public/assets/images/products/product-45.png',1),
('Gift Boxes for Special Moments','Premium packing','Choose beautifully packed sweets for Eid, weddings, birthdays, and corporate gifting.','Shop Gifts','shop?category_id=5','public/assets/images/products/product-04.png',2),
('Best-Selling Mithai','Family favourites','From gulab jamun to sohan halwa, enjoy the sweets your table already loves.','View Menu','shop','public/assets/images/products/product-06.png',3);

INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, delivery_address, city, area, subtotal, delivery_charge, discount_amount, tax_amount, total_amount, payment_method, status, created_at) VALUES
('ES-260709-1001',1,'Muhib Mirza','customer@easternsweets.com','03104490798','Block 10-A, Gulshan-e-Iqbal','Karachi','Gulshan-e-Iqbal',3200,0,320,0,2880,'cod','processing',NOW() - INTERVAL 2 DAY),
('ES-260709-1002',2,'Ayesha Khan','ayesha@example.com','03001234567','Street 5, DHA Phase 6','Karachi','DHA',5200,0,500,0,4700,'visa','delivered',NOW() - INTERVAL 6 DAY),
('ES-260709-1003',3,'Hamza Rauf','hamza@example.com','03019876543','PECHS Block 2','Karachi','PECHS',1600,180,0,0,1780,'jazzcash','pending',NOW() - INTERVAL 1 DAY);

INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, unit_price, quantity, line_total) VALUES
(1,2,4,'Gulab Jamun','1kg',1600,2,3200),
(2,19,37,'Special Moments Box','Medium Box',2800,1,2800),(2,11,21,'Kaju Barfi','500g',1700,1,1700),(2,15,29,'Nimco Mix','250g',420,1,420),
(3,2,3,'Gulab Jamun','500g',850,1,850),(3,5,9,'Laddu','500g',780,1,780);

INSERT INTO payments (order_id, method, amount, status, transaction_reference) VALUES
(1,'cod',2880,'pending','TEST-ES-260709-1001'),
(2,'visa',4700,'paid','VISA-SANDBOX-82733'),
(3,'jazzcash',1780,'sandbox_pending','JC-SANDBOX-11210');

INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1,2,5,'The assorted box looked premium and tasted fresh.'),
(2,1,5,'Soft gulab jamun and excellent syrup balance.'),
(7,3,5,'The sohan halwa is rich without being too heavy.'),
(19,2,5,'Beautiful gift packaging for a family event.');

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name','Eastern Sweets','general'),
('logo_path','public/assets/images/logo.png','general'),
('phone','0310-4490798','contact'),
('address','Anees Complex, Gulshan-e-Iqbal, Karachi','contact'),
('delivery_charge','180','shipping'),
('tax_percent','0','payment'),
('facebook_url','#','social'),
('instagram_url','#','social'),
('whatsapp_url','#','social');

-- Content-management upgrade: homepage blocks, testimonials, and static pages.
CREATE TABLE IF NOT EXISTS usp_blocks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  icon_text VARCHAR(30) NULL,
  icon_image_path VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  customer_photo_path VARCHAR(255) NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  review_text TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS static_pages (
  slug VARCHAR(80) PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE categories SET
  banner_image_path = 'uploads/categories/mithai-gold-platter.png',
  banner_tagline = 'Handcrafted Mithai, Made Fresh Daily',
  banner_cta_text = 'Shop Mithai',
  banner_cta_link = 'category/mithai',
  banner_accent = '#d1ad57',
  banner_animation = 'mithai-spin'
WHERE slug = 'mithai';

UPDATE categories SET
  banner_tagline = COALESCE(banner_tagline, CONCAT('Fresh ', name, ' made daily')),
  banner_cta_text = COALESCE(banner_cta_text, CONCAT('Shop ', name)),
  banner_cta_link = COALESCE(banner_cta_link, CONCAT('category/', slug)),
  banner_accent = COALESCE(banner_accent, '#d1ad57'),
  banner_animation = CASE
    WHEN banner_animation IS NULL OR banner_animation = '' OR banner_animation IN ('basic', 'mithai-spin') THEN 'slide-3d'
    ELSE banner_animation
  END
WHERE slug <> 'mithai';

INSERT INTO usp_blocks (title, description, icon_text, sort_order, is_active) VALUES
('Fresh Ingredients','Daily batches with premium nuts, khoya, ghee, and careful packing.','*',1,1),
('Fast Delivery','Reliable delivery for homes, offices, and event orders.','+',2,1),
('Hygienic Packing','Gift-ready boxes and sealed product packaging.','#',3,1),
('Fair Prices','Classic quality with transparent weights and totals.','Rs',4,1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO testimonials (customer_name, rating, review_text, sort_order, is_active) VALUES
('Ayesha K.',5,'The packaging looked premium and the gulab jamun arrived warm and fresh.',1,1),
('Hamza R.',5,'Reliable for office orders. The assorted box was balanced and beautifully packed.',2,1),
('Sana M.',5,'Classic mithai taste with a cleaner, modern ordering experience.',3,1);

INSERT INTO static_pages (slug, title, body) VALUES
('about','About Us','Eastern Sweets has served fresh mithai, bakery items, nimco, and celebration boxes since 1969.'),
('contact','Contact Us','Reach Eastern Sweets for daily orders, gift boxes, and event catering.'),
('faqs','FAQs','Find answers about delivery, payment methods, freshness, and custom orders.'),
('terms','Terms & Conditions','Orders are accepted subject to product availability, delivery coverage, and confirmation.'),
('privacy','Privacy Policy','Customer information is used only to process orders, support accounts, and improve service.'),
('refund','Return & Refund Policy','Please contact us quickly for any order concern so the team can review and assist.')
ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body);

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_logo','public/assets/images/logo.png','branding'),
('background_pattern','public/assets/images/pattern.png','branding'),
('theme_primary','#0b4e3d','branding'),
('theme_secondary','#d1ad57','branding'),
('theme_accent','#8f2432','branding'),
('home_categories_eyebrow','Shop by mood','homepage'),
('home_categories_title','Fresh categories','homepage'),
('home_featured_eyebrow','Best sellers','homepage'),
('home_featured_title','Most loved mithai','homepage'),
('promo_eyebrow','Celebration Boxes','homepage'),
('promo_title','Build a premium mithai box for Eid, weddings, or corporate gifting.','homepage'),
('promo_body','Choose fresh assorted mithai, dry fruit sweets, and bakery favourites packed in Eastern Sweets signature boxes.','homepage'),
('promo_button_text','Explore Gift Boxes','homepage'),
('promo_button_link','shop?category_id=5','homepage'),
('promo_image','','homepage'),
('testimonials_eyebrow','Customer notes','homepage'),
('testimonials_title','Sweet words','homepage'),
('newsletter_eyebrow','Fresh offers','homepage'),
('newsletter_title','Get celebration deals in your inbox','homepage'),
('footer_about','Fresh mithai, bakery favourites, nimco, and celebration-ready gift boxes from a Karachi classic.','footer'),
('footer_address','Anees Complex, Gulshan-e-Iqbal, Karachi','footer'),
('footer_phone','0310-4490798','footer'),
('footer_hours','10:00 AM - 11:30 PM','footer'),
('footer_socials','Instagram, Facebook, WhatsApp','footer'),
('footer_payments','COD, Visa, Mastercard, JazzCash, Easypaisa','footer')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group);
