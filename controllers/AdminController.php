<?php
declare(strict_types=1);

final class AdminController
{
    public function dispatch(string $route, string $method): void
    {
        if ($route === 'admin/login') {
            $method === 'POST' ? $this->login() : render('admin/login', ['title' => 'Admin Login'], 'auth');
            return;
        }
        if ($route === 'admin/logout') {
            unset($_SESSION['admin'], $_SESSION['admin_last_seen']);
            session_regenerate_id(true);
            redirect('admin/login');
        }

        require_admin();
        try {
        switch ($route) {
            case 'admin':
            case 'admin/dashboard':
                $this->dashboard();
                break;
            case 'admin/products':
                $this->products();
                break;
            case 'admin/product-form':
                $method === 'POST' ? $this->saveProduct() : $this->productForm();
                break;
            case 'admin/product-delete':
                $this->deleteProduct();
                break;
            case 'admin/product-image-delete':
                $this->deleteProductImage();
                break;
            case 'admin/category-form':
                $method === 'POST' ? $this->saveCategory() : $this->categoryForm();
                break;
            case 'admin/category-delete':
                $this->deleteCategory();
                break;
            case 'admin/coupon-form':
                $method === 'POST' ? $this->saveCoupon() : $this->couponForm();
                break;
            case 'admin/coupon-delete':
                $this->deleteCoupon();
                break;
            case 'admin/banner-form':
                $method === 'POST' ? $this->saveBanner() : $this->bannerForm();
                break;
            case 'admin/banner-delete':
                $this->deleteBanner();
                break;
            case 'admin/content':
                $method === 'POST' ? $this->saveContent() : $this->content();
                break;
            case 'admin/usp-form':
                $method === 'POST' ? $this->saveUsp() : $this->uspForm();
                break;
            case 'admin/usp-delete':
                $this->deleteUsp();
                break;
            case 'admin/testimonial-form':
                $method === 'POST' ? $this->saveTestimonial() : $this->testimonialForm();
                break;
            case 'admin/testimonial-delete':
                $this->deleteTestimonial();
                break;
            case 'admin/static-page-form':
                $method === 'POST' ? $this->saveStaticPage() : $this->staticPageForm();
                break;
            case 'admin/settings-save':
                $this->saveSettings();
                break;
            case 'admin/customer-toggle':
                $this->toggleCustomer();
                break;
            case 'admin/customer-form':
                $method === 'POST' ? $this->saveCustomer() : $this->customerForm();
                break;
            case 'admin/customer-delete':
                $this->deleteCustomer();
                break;
            case 'admin/payment-form':
                $method === 'POST' ? $this->savePayment() : $this->paymentForm();
                break;
            case 'admin/payment-delete':
                $this->deletePayment();
                break;
            case 'admin/orders':
                $this->orders();
                break;
            case 'admin/order':
                $method === 'POST' ? $this->updateOrder() : $this->orderDetail();
                break;
            case 'admin/invoice':
                $this->invoice();
                break;
            case 'admin/reports':
                $this->reports();
                break;
            case 'admin/export':
                $this->exportCsv();
                break;
            case 'admin/categories':
            case 'admin/customers':
            case 'admin/payments':
            case 'admin/coupons':
            case 'admin/banners':
            case 'admin/settings':
            case 'admin/admins':
                $this->resource($route);
                break;
            default:
                http_response_code(404);
                render('admin/resource', ['title' => 'Admin Page', 'rows' => [], 'columns' => [], 'resource' => 'Not Found'], 'admin');
        }
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $params = [];
            if (!empty($_POST['id'])) {
                $params['id'] = (int)$_POST['id'];
            }
            $fallbackRoute = $route === 'admin/settings-save' ? 'admin/settings' : $route;
            redirect($fallbackRoute, $params);
        }
    }

    private function login(): void
    {
        verify_csrf();
        $email = trim((string)($_POST['email'] ?? ''));
        $attemptKey = 'admin_login_attempts_' . sha1($email . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
        if (($_SESSION[$attemptKey] ?? 0) >= 5) {
            flash('error', 'Too many admin login attempts. Please try again later.');
            redirect('admin/login');
        }
        $admin = Admin::findByEmail($email);
        if (!$admin || !password_verify((string)($_POST['password'] ?? ''), $admin['password_hash'])) {
            $_SESSION[$attemptKey] = (int)($_SESSION[$attemptKey] ?? 0) + 1;
            flash('error', 'Invalid admin credentials.');
            redirect('admin/login');
        }
        unset($_SESSION[$attemptKey]);
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'role' => $admin['role']];
        $_SESSION['admin_last_seen'] = time();
        Database::connection()->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$admin['id']]);
        redirect('admin/dashboard');
    }

    private function dashboard(): void
    {
        render('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => Order::dashboardStats(),
            'orders' => Order::adminList([], 8),
            'lowStock' => Database::connection()->query('SELECT p.name, p.stock, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock <= 15 ORDER BY p.stock ASC LIMIT 6')->fetchAll(),
            'chart' => Order::salesReport(date('Y-m-d', strtotime('-6 days')), date('Y-m-d')),
        ], 'admin');
    }

    private function products(): void
    {
        render('admin/products', [
            'title' => 'Products',
            'products' => Product::adminList($_GET),
            'categories' => Category::adminAll(),
        ], 'admin');
    }

    private function productForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/product-form', [
            'title' => $id ? 'Edit Product' : 'Add Product',
            'product' => $id ? Product::adminFind($id) : null,
            'categories' => Category::adminAll(),
        ], 'admin');
    }

    private function saveProduct(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $existing = $id ? Product::adminFind($id) : null;
        $existingImage = $existing['images'][0]['image_path'] ?? 'public/assets/images/products/product-01.png';
        $imagePath = upload_image('image_file', 'products', trim((string)($_POST['image_path'] ?? $existingImage)));
        $galleryImages = upload_multiple_images('gallery_images', 'products');
        $name = trim((string)$_POST['name']);
        Product::save([
            'category_id' => (int)$_POST['category_id'],
            'name' => $name,
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)),
            'short_description' => trim((string)$_POST['short_description']),
            'description' => trim((string)$_POST['description']),
            'ingredients' => trim((string)$_POST['ingredients']),
            'allergens' => trim((string)$_POST['allergens']),
            'stock' => (int)$_POST['stock'],
            'variant_name' => trim((string)($_POST['variant_name'] ?? '500g')),
            'price' => (float)($_POST['price'] ?? 0),
            'compare_at_price' => trim((string)($_POST['compare_at_price'] ?? '')),
            'variant_stock' => (int)($_POST['variant_stock'] ?? $_POST['stock']),
            'image_path' => $imagePath ?: 'public/assets/images/products/product-01.png',
            'gallery_images' => $galleryImages,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ], $id);
        flash('success', 'Product saved.');
        redirect('admin/products');
    }

    private function deleteProduct(): void
    {
        verify_csrf();
        Product::delete((int)$_POST['id']);
        flash('success', 'Product hidden from storefront.');
        redirect('admin/products');
    }

    private function deleteProductImage(): void
    {
        verify_csrf();
        Product::deleteImage((int)$_POST['image_id']);
        flash('success', 'Gallery image removed.');
        redirect('admin/product-form', ['id' => (int)$_POST['product_id']]);
    }

    private function categoryForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/category-form', [
            'title' => $id ? 'Edit Category' : 'Add Category',
            'category' => $id ? Category::find($id) : null,
        ], 'admin');
    }

    private function saveCategory(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $existing = $id ? Category::find($id) : null;
        $imagePath = upload_image('image_file', 'categories', trim((string)($_POST['image_path'] ?? ($existing['image_path'] ?? 'public/assets/images/products/product-01.png'))));
        $bannerPath = upload_image('banner_image_file', 'categories', trim((string)($_POST['banner_image_path'] ?? ($existing['banner_image_path'] ?? ''))));
        $name = trim((string)$_POST['name']);
        Category::save([
            'name' => $name,
            'slug' => $this->slug($name),
            'image_path' => $imagePath ?: 'public/assets/images/products/product-01.png',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'banner_image_path' => $bannerPath ?: null,
            'banner_tagline' => trim((string)($_POST['banner_tagline'] ?? '')),
            'banner_cta_text' => trim((string)($_POST['banner_cta_text'] ?? 'Shop ' . $name)),
            'banner_cta_link' => trim((string)($_POST['banner_cta_link'] ?? url('shop'))),
            'banner_accent' => trim((string)($_POST['banner_accent'] ?? '#d1ad57')),
            'banner_animation' => trim((string)($_POST['banner_animation'] ?? 'slide-3d')),
        ], $id);
        flash('success', 'Category saved.');
        redirect('admin/categories');
    }

    private function deleteCategory(): void
    {
        verify_csrf();
        Category::delete((int)$_POST['id']);
        flash('success', 'Category hidden.');
        redirect('admin/categories');
    }

    private function couponForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/coupon-form', [
            'title' => $id ? 'Edit Coupon' : 'Add Coupon',
            'coupon' => $id ? Coupon::find($id) : null,
        ], 'admin');
    }

    private function saveCoupon(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        Coupon::save([
            'code' => strtoupper(trim((string)$_POST['code'])),
            'discount_type' => (string)$_POST['discount_type'],
            'discount_value' => (float)$_POST['discount_value'],
            'expires_at' => trim((string)($_POST['expires_at'] ?? '')) ?: null,
            'usage_limit' => trim((string)($_POST['usage_limit'] ?? '')) === '' ? null : (int)$_POST['usage_limit'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], $id);
        flash('success', 'Coupon saved.');
        redirect('admin/coupons');
    }

    private function deleteCoupon(): void
    {
        verify_csrf();
        Coupon::delete((int)$_POST['id']);
        flash('success', 'Coupon disabled.');
        redirect('admin/coupons');
    }

    private function bannerForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $banner = null;
        if ($id) {
            $stmt = Database::connection()->prepare('SELECT * FROM banners WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $banner = $stmt->fetch() ?: null;
        }
        render('admin/banner-form', ['title' => $id ? 'Edit Banner' : 'Add Banner', 'banner' => $banner], 'admin');
    }

    private function saveBanner(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $existing = null;
        if ($id) {
            $stmt = Database::connection()->prepare('SELECT image_path FROM banners WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $existing = $stmt->fetchColumn() ?: null;
        }
        $imagePath = upload_image('image_file', 'banners', trim((string)($_POST['image_path'] ?? ($existing ?: 'public/assets/images/products/product-01.png'))));
        $data = [
            trim((string)$_POST['title']),
            trim((string)$_POST['subtitle']),
            trim((string)$_POST['description']),
            trim((string)$_POST['button_text']),
            trim((string)$_POST['link_url']),
            $imagePath ?: 'public/assets/images/products/product-01.png',
            (int)($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($id) {
            $stmt = Database::connection()->prepare('UPDATE banners SET title=?, subtitle=?, description=?, button_text=?, link_url=?, image_path=?, sort_order=?, is_active=? WHERE id=?');
            $stmt->execute(array_merge($data, [$id]));
        } else {
            $stmt = Database::connection()->prepare('INSERT INTO banners (title, subtitle, description, button_text, link_url, image_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute($data);
        }
        flash('success', 'Banner saved.');
        redirect('admin/banners');
    }

    private function deleteBanner(): void
    {
        verify_csrf();
        $stmt = Database::connection()->prepare('UPDATE banners SET is_active = 0 WHERE id = ?');
        $stmt->execute([(int)$_POST['id']]);
        flash('success', 'Banner disabled.');
        redirect('admin/banners');
    }

    private function saveSettings(): void
    {
        verify_csrf();
        $_POST['settings']['site_logo'] = upload_image('site_logo_file', 'logo', $_POST['settings']['site_logo'] ?? app_setting('site_logo', 'public/assets/images/logo.png'));
        $_POST['settings']['background_pattern'] = upload_image('background_pattern_file', 'content', $_POST['settings']['background_pattern'] ?? app_setting('background_pattern', ''));
        SiteContent::setSettings($_POST['settings'] ?? []);
        flash('success', 'Settings saved.');
        redirect('admin/settings');
    }

    private function content(): void
    {
        render('admin/content', [
            'title' => 'Site Content',
            'settings' => SiteContent::settings(),
            'uspBlocks' => SiteContent::uspBlocks(true),
            'testimonials' => SiteContent::testimonials(true),
            'pages' => SiteContent::staticPages(),
        ], 'admin');
    }

    private function saveContent(): void
    {
        verify_csrf();
        $settings = $_POST['settings'] ?? [];
        $settings['site_logo'] = upload_image('site_logo_file', 'logo', $settings['site_logo'] ?? app_setting('site_logo', 'public/assets/images/logo.png'));
        $settings['background_pattern'] = upload_image('background_pattern_file', 'content', $settings['background_pattern'] ?? app_setting('background_pattern', ''));
        $settings['promo_image'] = upload_image('promo_image_file', 'banners', $settings['promo_image'] ?? app_setting('promo_image', ''));
        SiteContent::setSettings($settings);
        flash('success', 'Site content settings saved.');
        redirect('admin/content');
    }

    private function uspForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/usp-form', [
            'title' => $id ? 'Edit USP Block' : 'Add USP Block',
            'block' => $id ? SiteContent::uspFind($id) : null,
        ], 'admin');
    }

    private function saveUsp(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $existing = $id ? SiteContent::uspFind($id) : null;
        SiteContent::uspSave([
            'title' => trim((string)$_POST['title']),
            'description' => trim((string)$_POST['description']),
            'icon_text' => trim((string)($_POST['icon_text'] ?? '')),
            'icon_image_path' => upload_image('icon_image_file', 'content', trim((string)($_POST['icon_image_path'] ?? ($existing['icon_image_path'] ?? '')))),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], $id);
        flash('success', 'USP block saved.');
        redirect('admin/content');
    }

    private function deleteUsp(): void
    {
        verify_csrf();
        SiteContent::uspDelete((int)$_POST['id']);
        flash('success', 'USP block disabled.');
        redirect('admin/content');
    }

    private function testimonialForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/testimonial-form', [
            'title' => $id ? 'Edit Testimonial' : 'Add Testimonial',
            'testimonial' => $id ? SiteContent::testimonialFind($id) : null,
        ], 'admin');
    }

    private function saveTestimonial(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $existing = $id ? SiteContent::testimonialFind($id) : null;
        SiteContent::testimonialSave([
            'customer_name' => trim((string)$_POST['customer_name']),
            'customer_photo_path' => upload_image('customer_photo_file', 'testimonials', trim((string)($_POST['customer_photo_path'] ?? ($existing['customer_photo_path'] ?? '')))),
            'rating' => max(1, min(5, (int)($_POST['rating'] ?? 5))),
            'review_text' => trim((string)$_POST['review_text']),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ], $id);
        flash('success', 'Testimonial saved.');
        redirect('admin/content');
    }

    private function deleteTestimonial(): void
    {
        verify_csrf();
        SiteContent::testimonialDelete((int)$_POST['id']);
        flash('success', 'Testimonial disabled.');
        redirect('admin/content');
    }

    private function staticPageForm(): void
    {
        $slug = (string)($_GET['slug'] ?? 'about');
        render('admin/static-page-form', [
            'title' => 'Edit Static Page',
            'page' => SiteContent::staticPage($slug) ?? ['slug' => $slug, 'title' => ucwords($slug), 'body' => ''],
        ], 'admin');
    }

    private function saveStaticPage(): void
    {
        verify_csrf();
        SiteContent::staticPageSave((string)$_POST['slug'], trim((string)$_POST['title']), trim((string)$_POST['body']));
        flash('success', 'Page content saved.');
        redirect('admin/content');
    }

    private function toggleCustomer(): void
    {
        verify_csrf();
        User::setBlocked((int)$_POST['id'], (int)($_POST['blocked'] ?? 0) === 1);
        flash('success', 'Customer status updated.');
        redirect('admin/customers');
    }

    private function customerForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/customer-form', [
            'title' => $id ? 'Edit Customer' : 'Add Customer',
            'customer' => $id ? User::findById($id) : null,
        ], 'admin');
    }

    private function saveCustomer(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $email = trim((string)$_POST['email']);
        $existing = User::findByEmail($email);
        if ($existing && (!$id || (int)$existing['id'] !== $id)) {
            flash('error', 'This email already exists.');
            redirect('admin/customer-form', $id ? ['id' => $id] : []);
        }
        User::adminSave([
            'name' => trim((string)$_POST['name']),
            'email' => $email,
            'phone' => trim((string)$_POST['phone']),
            'password' => (string)($_POST['password'] ?? ''),
            'default_address' => trim((string)($_POST['default_address'] ?? '')),
            'is_blocked' => isset($_POST['is_blocked']) ? 1 : 0,
        ], $id);
        flash('success', 'Customer saved.');
        redirect('admin/customers');
    }

    private function deleteCustomer(): void
    {
        verify_csrf();
        User::delete((int)$_POST['id']);
        flash('success', 'Customer deleted.');
        redirect('admin/customers');
    }

    private function paymentForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        render('admin/payment-form', [
            'title' => $id ? 'Edit Payment' : 'Add Payment',
            'payment' => $id ? Payment::find($id) : null,
            'orders' => Database::connection()->query('SELECT id, order_number, total_amount FROM orders ORDER BY created_at DESC LIMIT 200')->fetchAll(),
        ], 'admin');
    }

    private function savePayment(): void
    {
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        Payment::save([
            'order_id' => (int)$_POST['order_id'],
            'method' => trim((string)$_POST['method']),
            'amount' => (float)$_POST['amount'],
            'status' => trim((string)$_POST['status']),
            'transaction_reference' => trim((string)($_POST['transaction_reference'] ?? '')),
        ], $id);
        flash('success', 'Payment saved.');
        redirect('admin/payments');
    }

    private function deletePayment(): void
    {
        verify_csrf();
        Payment::delete((int)$_POST['id']);
        flash('success', 'Payment deleted.');
        redirect('admin/payments');
    }

    private function orders(): void
    {
        render('admin/orders', [
            'title' => 'Orders',
            'orders' => Order::adminList($_GET),
            'status' => (string)($_GET['status'] ?? ''),
        ], 'admin');
    }

    private function orderDetail(): void
    {
        $order = Order::find((int)($_GET['id'] ?? 0));
        if (!$order) {
            redirect('admin/orders');
        }
        render('admin/order-detail', ['title' => 'Order ' . $order['order_number'], 'order' => $order], 'admin');
    }

    private function updateOrder(): void
    {
        verify_csrf();
        Order::updateStatus((int)$_POST['id'], (string)$_POST['status']);
        flash('success', 'Order status updated.');
        redirect('admin/order', ['id' => (int)$_POST['id']]);
    }

    private function invoice(): void
    {
        $order = Order::find((int)($_GET['id'] ?? 0));
        if (!$order) {
            redirect('admin/orders');
        }
        render('admin/invoice', ['title' => 'Invoice ' . $order['order_number'], 'order' => $order], 'invoice');
    }

    private function reports(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        render('admin/reports', [
            'title' => 'Reports',
            'from' => $from,
            'to' => $to,
            'rows' => Order::salesReport((string)$from, (string)$to),
        ], 'admin');
    }

    private function exportCsv(): void
    {
        $rows = Order::salesReport((string)($_GET['from'] ?? date('Y-m-01')), (string)($_GET['to'] ?? date('Y-m-d')));
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="eastern-sweets-sales-report.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date', 'Orders', 'Subtotal', 'Discounts', 'Total']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['sale_date'], $row['orders'], $row['subtotal'], $row['discounts'], $row['total']]);
        }
        exit;
    }

    private function resource(string $route): void
    {
        $resource = str_replace('admin/', '', $route);
        $rows = [];
        $columns = [];
        if ($resource === 'categories') {
            $rows = $this->filteredCategories();
            $columns = ['id', 'name', 'slug', 'sort_order', 'is_active'];
        } elseif ($resource === 'customers') {
            $rows = User::adminList((string)($_GET['q'] ?? ''), (string)($_GET['status'] ?? ''));
            $columns = ['id', 'name', 'email', 'phone', 'order_count', 'total_spent', 'is_blocked'];
        } elseif ($resource === 'payments') {
            $rows = Payment::all($_GET);
            $columns = ['id', 'order_number', 'method', 'amount', 'status', 'transaction_reference'];
        } elseif ($resource === 'coupons') {
            $rows = $this->filteredCoupons();
            $columns = ['id', 'code', 'discount_type', 'discount_value', 'expires_at', 'usage_limit', 'used_count', 'is_active'];
        } elseif ($resource === 'banners') {
            $rows = $this->filteredBanners();
            $columns = ['id', 'title', 'button_text', 'link_url', 'sort_order', 'is_active'];
        } elseif ($resource === 'settings') {
            $rows = Database::connection()->query('SELECT setting_key, setting_value, setting_group FROM settings ORDER BY setting_group, setting_key')->fetchAll();
            $columns = ['setting_group', 'setting_key', 'setting_value'];
        } elseif ($resource === 'admins') {
            $rows = $this->filteredAdmins();
            $columns = ['id', 'name', 'email', 'role', 'is_active', 'last_login'];
        }
        render('admin/resource', [
            'title' => ucwords(str_replace('-', ' ', $resource)),
            'resource' => $resource,
            'rows' => $rows,
            'columns' => $columns,
        ], 'admin');
    }

    private function slug(string $value): string
    {
        $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
        return $slug !== '' ? $slug : 'item-' . random_int(1000, 9999);
    }

    private function filteredCategories(): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        if ($q !== '') {
            $where[] = '(name LIKE ? OR slug LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE ' . implode(' AND ', $where) . ' ORDER BY sort_order, name');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function filteredCoupons(): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        if ($q !== '') {
            $where[] = '(code LIKE ? OR discount_type LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }
        $stmt = Database::connection()->prepare('SELECT * FROM coupons WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function filteredBanners(): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        if ($q !== '') {
            $where[] = '(title LIKE ? OR subtitle LIKE ? OR button_text LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }
        $stmt = Database::connection()->prepare('SELECT id, title, subtitle, button_text, link_url, image_path, sort_order, is_active FROM banners WHERE ' . implode(' AND ', $where) . ' ORDER BY sort_order');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function filteredAdmins(): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        if ($q !== '') {
            $where[] = '(name LIKE ? OR email LIKE ? OR role LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($status !== '') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        }
        $stmt = Database::connection()->prepare('SELECT id, name, email, role, is_active, last_login, created_at FROM admins WHERE ' . implode(' AND ', $where) . ' ORDER BY id');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
