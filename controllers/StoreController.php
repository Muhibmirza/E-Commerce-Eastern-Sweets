<?php
declare(strict_types=1);

final class StoreController
{
    public function home(): void
    {
        render('home', [
            'title' => 'Eastern Sweets - Fresh Mithai, Bakers & Nimco',
            'categories' => Category::all(),
            'products' => Product::featured(8),
            'galleryProducts' => Product::gallery(10),
            'banners' => $this->banners(),
            'settings' => SiteContent::settings(),
            'uspBlocks' => SiteContent::uspBlocks(),
            'testimonials' => SiteContent::testimonials(),
        ]);
    }

    public function shop(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $filters = [
            'category_id' => (int)($_GET['category_id'] ?? 0),
            'q' => trim((string)($_GET['q'] ?? '')),
            'sort' => (string)($_GET['sort'] ?? 'popular'),
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
        ];
        render('shop', [
            'title' => 'Shop Mithai & Gift Boxes',
            'categories' => Category::all(),
            'products' => Product::search($filters, $limit, ($page - 1) * $limit),
            'total' => Product::count($filters),
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
            'category' => null,
            'categoryBanner' => null,
        ]);
    }

    public function category(string $slug): void
    {
        $category = Category::findBySlug(trim($slug, '/'));
        if (!$category) {
            http_response_code(404);
            render('static/page', [
                'title' => 'Category not found',
                'body' => 'This category is unavailable.',
            ]);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $filters = [
            'category_id' => (int)$category['id'],
            'q' => trim((string)($_GET['q'] ?? '')),
            'sort' => (string)($_GET['sort'] ?? 'popular'),
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
        ];

        render('shop', [
            'title' => $category['name'] . ' - Eastern Sweets',
            'categories' => Category::all(),
            'products' => Product::search($filters, $limit, ($page - 1) * $limit),
            'total' => Product::count($filters),
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
            'category' => $category,
            'categoryBanner' => $this->categoryBanner((string)$category['slug'], $category),
        ]);
    }

    public function product(int $id): void
    {
        $product = Product::find($id);
        if (!$product) {
            http_response_code(404);
            render('static/page', ['title' => 'Product not found', 'body' => 'This product is unavailable.']);
            return;
        }
        render('product', [
            'title' => $product['name'],
            'product' => $product,
            'related' => Product::related((int)$product['id'], (int)$product['category_id']),
        ]);
    }

    public function cart(): void
    {
        render('cart', [
            'title' => 'Your Cart',
            'cart' => $this->hydratedCart(),
            'coupon' => $_SESSION['coupon'] ?? null,
        ]);
    }

    public function addToCart(): void
    {
        verify_csrf();
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $variant = $this->variant($variantId);

        if (!$variant) {
            json_response(['ok' => false, 'message' => 'Product option not found.']);
        }

        $key = (string)$variantId;
        $_SESSION['cart'][$key] = [
            'variant_id' => $variantId,
            'quantity' => min(99, (int)($_SESSION['cart'][$key]['quantity'] ?? 0) + $quantity),
        ];

        json_response([
            'ok' => true,
            'message' => $variant['product_name'] . ' added to cart.',
            'count' => cart_count(),
            'cart' => $this->cartSummary(),
            'drawer' => $this->cartDrawerPayload(),
        ]);
    }

    public function updateCart(): void
    {
        verify_csrf();
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $quantity = max(0, (int)($_POST['quantity'] ?? 0));
        if ($quantity === 0) {
            unset($_SESSION['cart'][(string)$variantId]);
        } else {
            $_SESSION['cart'][(string)$variantId] = ['variant_id' => $variantId, 'quantity' => min(99, $quantity)];
        }
        json_response(['ok' => true, 'count' => cart_count(), 'cart' => $this->cartSummary(), 'drawer' => $this->cartDrawerPayload()]);
    }

    public function checkout(): void
    {
        if (cart_count() === 0) {
            flash('info', 'Your cart is empty.');
            redirect('shop');
        }
        render('checkout', [
            'title' => 'Checkout',
            'cart' => $this->hydratedCart(),
            'paymentMethods' => array_filter(require __DIR__ . '/../includes/payment-config.php', fn($m) => $m['enabled']),
            'coupon' => $_SESSION['coupon'] ?? null,
            'user' => current_user(),
        ]);
    }

    public function applyCoupon(): void
    {
        verify_csrf();
        $coupon = Coupon::validateCode((string)($_POST['coupon_code'] ?? ''));
        if (!$coupon) {
            unset($_SESSION['coupon']);
            flash('error', 'Coupon is invalid, expired, or fully used.');
            redirect('cart');
        }
        $_SESSION['coupon'] = $coupon;
        flash('success', 'Coupon applied.');
        redirect('cart');
    }

    public function placeOrder(): void
    {
        verify_csrf();
        if (cart_count() === 0) {
            flash('error', 'Your cart is empty.');
            redirect('cart');
        }
        if (isset($_POST['coupon_code']) && trim((string)$_POST['coupon_code']) !== '') {
            $_SESSION['coupon'] = Coupon::validateCode((string)$_POST['coupon_code']);
            if (!$_SESSION['coupon']) {
                flash('error', 'Coupon is invalid or expired.');
                redirect('checkout');
            }
        }

        $required = ['name', 'email', 'phone', 'address', 'city', 'area', 'payment_method'];
        foreach ($required as $field) {
            if (trim((string)($_POST[$field] ?? '')) === '') {
                flash('error', 'Please complete all required checkout fields.');
                redirect('checkout');
            }
        }

        $paymentMethod = (string)$_POST['payment_method'];
        $paymentMethods = require __DIR__ . '/../includes/payment-config.php';
        if (empty($paymentMethods[$paymentMethod]['enabled'])) {
            flash('error', 'The selected payment method is unavailable.');
            redirect('checkout');
        }
        if (!filter_var((string)$_POST['email'], FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('checkout');
        }

        try {
            $orderId = Order::create([
                'name' => trim((string)$_POST['name']),
                'email' => trim((string)$_POST['email']),
                'phone' => trim((string)$_POST['phone']),
                'address' => trim((string)$_POST['address']),
                'city' => trim((string)$_POST['city']),
                'area' => trim((string)$_POST['area']),
                'notes' => trim((string)($_POST['notes'] ?? '')),
            ], cart_items(), $paymentMethod, current_user(), $_SESSION['coupon'] ?? null);

            if ($paymentMethod === 'safepay') {
                $this->startSafepayCheckout($orderId);
                return;
            }

            $_SESSION['cart'] = [];
            unset($_SESSION['coupon']);
            render('order-confirmation', ['title' => 'Order Confirmed', 'order' => Order::find($orderId)]);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('checkout');
        }
    }

    public function safepayReturn(): void
    {
        $tracker = trim((string)($_POST['tracker'] ?? $_GET['tracker'] ?? ''));
        $signature = trim((string)($_POST['sig'] ?? $_GET['sig'] ?? ''));

        try {
            if ($tracker === '' || $signature === '' || !(new SafepayGateway())->verifyReturn($tracker, $signature)) {
                throw new RuntimeException('Safepay payment verification failed. Your order has not been confirmed.');
            }

            $payment = Payment::findByReference($tracker);
            $pendingOrderId = (int)($_SESSION['safepay_order_id'] ?? 0);
            if (!$payment || ($pendingOrderId > 0 && (int)$payment['order_id'] !== $pendingOrderId)) {
                throw new RuntimeException('This Safepay payment does not match your order.');
            }

            Payment::markPaid((int)$payment['order_id'], $tracker);
            $order = Order::find((int)$payment['order_id']);
            if (!$order) {
                throw new RuntimeException('The paid order could not be found.');
            }

            $_SESSION['cart'] = [];
            unset($_SESSION['coupon'], $_SESSION['safepay_order_id']);
            render('order-confirmation', ['title' => 'Payment Successful', 'order' => $order]);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('checkout');
        }
    }

    public function safepayCancel(): void
    {
        $orderId = (int)($_SESSION['safepay_order_id'] ?? 0);
        if ($orderId > 0) {
            Order::cancelUnpaid($orderId);
        }
        unset($_SESSION['safepay_order_id']);
        flash('info', 'Safepay payment was cancelled. Your cart is still available.');
        redirect('checkout');
    }

    private function startSafepayCheckout(int $orderId): void
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                throw new RuntimeException('Order could not be prepared for payment.');
            }

            $safepay = new SafepayGateway();
            $tracker = $safepay->createTracker((float)$order['total_amount'], (string)$order['order_number']);

            Payment::setReference($orderId, $tracker);
            $_SESSION['safepay_order_id'] = $orderId;

            $redirectUrl = $safepay->checkoutUrl($tracker);
            if (!preg_match('#^https://sandbox\.api\.getsafepay\.com/checkout/pay(?:\?|$)#', $redirectUrl)) {
                throw new RuntimeException('Safepay checkout link could not be created.');
            }

            header('Location: ' . $redirectUrl, true, 303);
            exit;
        } catch (Throwable $e) {
            Order::cancelUnpaid($orderId);
            throw $e;
        }
    }


    public function loginForm(): void
    {
        render('auth/login', ['title' => 'Login'], 'auth');
    }

    public function login(): void
    {
        verify_csrf();
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $attemptKey = 'login_attempts_' . sha1($email . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
        if (($_SESSION[$attemptKey] ?? 0) >= 5) {
            flash('error', 'Too many login attempts. Please try again later.');
            redirect('login');
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash']) || (int)$user['is_blocked'] === 1) {
            $_SESSION[$attemptKey] = (int)($_SESSION[$attemptKey] ?? 0) + 1;
            flash('error', 'Invalid email or password.');
            redirect('login');
        }
        unset($_SESSION[$attemptKey]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'phone' => $user['phone']];
        redirect('account');
    }

    public function registerForm(): void
    {
        render('auth/register', ['title' => 'Create Account'], 'auth');
    }

    public function register(): void
    {
        verify_csrf();
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'password' => (string)($_POST['password'] ?? ''),
        ];
        if (!$data['name'] || !$data['email'] || !$data['phone'] || strlen($data['password']) < 8) {
            flash('error', 'Please provide your details and an 8 character password.');
            redirect('register');
        }
        if (User::findByEmail($data['email'])) {
            flash('error', 'An account already exists with this email.');
            redirect('register');
        }
        $id = User::create($data);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']];
        redirect('account');
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        flash('success', 'You have been logged out.');
        redirect('home');
    }

    public function account(): void
    {
        require_customer();
        $user = User::findById((int)current_user()['id']);
        render('account/index', [
            'title' => 'My Account',
            'user' => $user,
            'orders' => Order::byUser((int)$user['id']),
        ]);
    }

    public function updateAccount(): void
    {
        require_customer();
        verify_csrf();
        $id = (int)current_user()['id'];
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
        ];
        if (!$data['name'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !$data['phone']) {
            flash('error', 'Please enter valid profile details.');
            redirect('account');
        }
        $existing = User::findByEmail($data['email']);
        if ($existing && (int)$existing['id'] !== $id) {
            flash('error', 'This email is already used by another account.');
            redirect('account');
        }
        User::updateProfile($id, $data);
        $_SESSION['user'] = array_merge($_SESSION['user'], $data);
        flash('success', 'Profile updated.');
        redirect('account');
    }

    public function updateAddress(): void
    {
        require_customer();
        verify_csrf();
        User::updateAddress((int)current_user()['id'], trim((string)($_POST['default_address'] ?? '')));
        flash('success', 'Saved address updated.');
        redirect('account');
    }

    public function changePassword(): void
    {
        require_customer();
        verify_csrf();
        $user = User::findById((int)current_user()['id']);
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        if (!$user || !password_verify($current, $user['password_hash']) || strlen($new) < 8) {
            flash('error', 'Current password is wrong or new password is too short.');
            redirect('account');
        }
        User::updatePassword((int)$user['id'], $new);
        flash('success', 'Password changed.');
        redirect('account');
    }

    public function trackForm(): void
    {
        render('track', ['title' => 'Track Order', 'order' => null]);
    }

    public function trackResult(): void
    {
        verify_csrf();
        render('track', [
            'title' => 'Track Order',
            'order' => Order::findForTracking(trim((string)$_POST['order_number']), trim((string)$_POST['phone'])),
        ]);
    }

    public function subscribeNewsletter(): void
    {
        verify_csrf();
        $email = trim((string)($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('home');
        }

        $pdo = Database::connection();
        $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(160) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1');
        $stmt->execute([$email]);

        flash('success', 'You are subscribed to Eastern Sweets offers.');
        redirect('home');
    }

    public function staticPage(string $slug): void
    {
        $page = SiteContent::staticPage($slug);
        if (!$page) {
            http_response_code(404);
            render('static/page', ['title' => 'Page not found', 'body' => 'This page is unavailable.', 'slug' => $slug]);
            return;
        }
        render('static/page', ['title' => $page['title'], 'body' => $page['body'], 'slug' => $slug]);
    }

    private function banners(): array
    {
        return Database::connection()->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order LIMIT 4')->fetchAll();
    }

    private function categoryBanner(string $slug, array $category): array
    {
        $cta = (string)($category['banner_cta_link'] ?: 'category/' . $slug);
        if (!preg_match('#^(https?://|/)#', $cta)) {
            $cta = url($cta);
        }
        return [
            'name' => (string)$category['name'],
            'tagline' => $category['banner_tagline'] ?: 'Freshly prepared Eastern Sweets favourites',
            'image' => $category['banner_image_path'] ?: ($category['image_path'] ?? 'public/assets/images/products/product-33.png'),
            'accent' => $category['banner_accent'] ?: '#d1ad57',
            'cta_text' => $category['banner_cta_text'] ?: 'Shop ' . $category['name'],
            'cta' => $cta,
            'animation' => $category['banner_animation'] ?: 'basic',
        ];
    }

    private function variant(int $variantId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT pv.*, p.name AS product_name, p.id AS product_id, img.image_path
             FROM product_variants pv
             JOIN products p ON p.id = pv.product_id
             LEFT JOIN (SELECT product_id, MIN(image_path) AS image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) img ON img.product_id = p.id
             WHERE pv.id = ? AND p.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$variantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function hydratedCart(): array
    {
        $items = [];
        foreach (cart_items() as $item) {
            $variant = $this->variant((int)$item['variant_id']);
            if (!$variant) {
                continue;
            }
            $quantity = (int)$item['quantity'];
            $items[] = [
                'variant' => $variant,
                'quantity' => $quantity,
                'line_total' => $quantity * (float)$variant['price'],
            ];
        }
        return $items;
    }

    private function cartSummary(): array
    {
        $subtotal = 0;
        foreach ($this->hydratedCart() as $item) {
            $subtotal += $item['line_total'];
        }
        return [
            'subtotal' => $subtotal,
            'delivery' => $subtotal >= 2000 || $subtotal == 0 ? 0 : (float)app_setting('delivery_charge', '180'),
        ];
    }

    private function cartDrawerPayload(): array
    {
        $items = [];
        $subtotal = 0.0;
        foreach ($this->hydratedCart() as $item) {
            $variant = $item['variant'];
            $lineTotal = (float)$item['line_total'];
            $subtotal += $lineTotal;
            $items[] = [
                'id' => (int)$variant['id'],
                'name' => (string)$variant['product_name'],
                'variant' => (string)$variant['variant_name'],
                'image' => asset((string)$variant['image_path']),
                'quantity' => (int)$item['quantity'],
                'price' => money($variant['price']),
                'line_total' => money($lineTotal),
            ];
        }

        $coupon = $_SESSION['coupon'] ?? null;
        $discount = $coupon ? ($coupon['discount_type'] === 'percent' ? $subtotal * ((float)$coupon['discount_value'] / 100) : (float)$coupon['discount_value']) : 0.0;
        $delivery = $subtotal >= 2000 || $subtotal == 0 ? 0.0 : (float)app_setting('delivery_charge', '180');
        $total = max(0, $subtotal - $discount + $delivery);

        return [
            'items' => $items,
            'subtotal' => money($subtotal),
            'delivery' => money($delivery),
            'discount' => money($discount),
            'total' => money($total),
            'cart_url' => url('cart'),
            'checkout_url' => url('checkout'),
            'shop_url' => url('shop'),
        ];
    }
}
