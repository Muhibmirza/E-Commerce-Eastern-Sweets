<?php
declare(strict_types=1);

$forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require __DIR__ . '/includes/env.php';

header_remove('X-Powered-By');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

spl_autoload_register(function (string $class): void {
    foreach (['models', 'controllers'] as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$route = trim($_GET['route'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && stripos($_SERVER['REQUEST_URI'] ?? '', 'index.php') !== false) {
    $params = $_GET;
    unset($params['route']);
    redirect($route === '' ? 'home' : $route, $params);
}

try {
    $store = new StoreController();
    $admin = new AdminController();

    if (strpos($route, 'admin') === 0) {
        $admin->dispatch($route, $method);
        exit;
    }

    if (strpos($route, 'category/') === 0) {
        $store->category(substr($route, 9));
        exit;
    }

    switch ($route) {
        case '':
        case 'home':
            $store->home();
            break;
        case 'shop':
            $store->shop();
            break;
        case 'product':
            $store->product((int)($_GET['id'] ?? 0));
            break;
        case 'cart':
            $store->cart();
            break;
        case 'cart/add':
            $store->addToCart();
            break;
        case 'cart/update':
            $store->updateCart();
            break;
        case 'coupon/apply':
            $store->applyCoupon();
            break;
        case 'checkout':
            $method === 'POST' ? $store->placeOrder() : $store->checkout();
            break;
        case 'payment/safepay/return':
            $store->safepayReturn();
            break;
        case 'payment/safepay/cancel':
            $store->safepayCancel();
            break;
        case 'login':
            $method === 'POST' ? $store->login() : $store->loginForm();
            break;
        case 'register':
            $method === 'POST' ? $store->register() : $store->registerForm();
            break;
        case 'logout':
            $store->logout();
            break;
        case 'account':
            $store->account();
            break;
        case 'account/update':
            $store->updateAccount();
            break;
        case 'account/address':
            $store->updateAddress();
            break;
        case 'account/password':
            $store->changePassword();
            break;
        case 'track':
            $method === 'POST' ? $store->trackResult() : $store->trackForm();
            break;
        case 'newsletter/subscribe':
            $store->subscribeNewsletter();
            break;
        case 'about':
        case 'contact':
        case 'faqs':
        case 'terms':
        case 'privacy':
        case 'refund':
            $store->staticPage($route);
            break;
        default:
            http_response_code(404);
            render('static/page', [
                'title' => 'Page Not Found',
                'body' => 'The page you requested could not be found.',
            ]);
    }
} catch (PDOException $e) {
    http_response_code(503);
    render('static/setup', ['error' => $e->getMessage()]);
}
