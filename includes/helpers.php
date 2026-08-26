<?php
declare(strict_types=1);

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return base_url($path);
}

function url(string $route = '', array $params = []): string
{
    $route = trim($route, '/');
    $query = http_build_query($params);
    $path = $route === '' || $route === 'home' ? '' : $route;
    return base_url($path) . ($query ? '?' . $query : '');
}

function absolute_url(string $route = '', array $params = []): string
{
    $forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
    $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https') ? 'https' : 'http';
    $forwardedHost = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '')[0]);
    $host = $forwardedHost !== '' ? $forwardedHost : ($_SERVER['HTTP_HOST'] ?? 'localhost');

    return $scheme . '://' . $host . url($route, $params);
}

function base_url(string $path = ''): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = preg_replace('#/admin(?:/.*)?$#', '', $dir);
    $dir = $dir === '/' ? '' : rtrim($dir, '/');
    return $dir . '/' . ltrim($path, '/');
}

function redirect(string $route, array $params = []): void
{
    header('Location: ' . url($route, $params));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['_csrf'] ?? '';
    if (!is_string($sessionToken) || $sessionToken === '' || !is_string($token) || $token === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('Security token expired. Please refresh and try again.');
    }
}

function flash(?string $key = null, ?string $message = null)
{
    if ($key !== null && $message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    if ($key === null) {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function money($amount): string
{
    return 'Rs. ' . number_format((float)$amount, 0);
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_items() as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function require_customer(): void
{
    if (!current_user()) {
        flash('info', 'Please login to continue.');
        redirect('login');
    }
}

function require_admin(): void
{
    if (!current_admin()) {
        redirect('admin/login');
    }
    if (!empty($_SESSION['admin_last_seen']) && time() - (int)$_SESSION['admin_last_seen'] > 1800) {
        unset($_SESSION['admin'], $_SESSION['admin_last_seen']);
        flash('error', 'Admin session expired. Please login again.');
        redirect('admin/login');
    }
    $_SESSION['admin_last_seen'] = time();
}

function render(string $view, array $data = [], string $layout = 'main'): void
{
    extract($data, EXTR_SKIP);
    $viewFile = __DIR__ . '/../views/' . $view . '.php';
    if (!is_file($viewFile)) {
        http_response_code(404);
        exit('View not found: ' . h($view));
    }
    ob_start();
    require $viewFile;
    $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/' . $layout . '.php';
}

function json_response(array $payload): void
{
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function active_route(string $route): string
{
    return trim($_GET['route'] ?? '', '/') === $route ? 'is-active' : '';
}

function app_setting(string $key, string $fallback = ''): string
{
    try {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string)$value : $fallback;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function app_theme_style(): string
{
    $primary = app_setting('theme_primary', '#0b4e3d');
    $secondary = app_setting('theme_secondary', '#d1ad57');
    $accent = app_setting('theme_accent', '#8b1e2d');
    $pattern = app_setting('background_pattern', '');
    $css = ':root{--color-primary:' . h($primary) . ';--color-secondary:' . h($secondary) . ';--color-accent:' . h($accent) . ';';
    if ($pattern !== '') {
        $css .= '--site-pattern:url("' . h(asset($pattern)) . '");';
    }
    return '<style>' . $css . '}</style>';
}

function upload_image(string $field, string $folder, ?string $existing = null): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }

    $file = $_FILES[$field];
    $uploadError = (int)($file['error'] ?? UPLOAD_ERR_OK);
    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'The image is larger than the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The image is larger than the form upload limit.',
            UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server upload folder is unavailable.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded image.',
            UPLOAD_ERR_EXTENSION => 'The server rejected the uploaded image.',
        ];
        throw new RuntimeException($messages[$uploadError] ?? 'Image upload failed.');
    }
    if (($file['size'] ?? 0) > 4 * 1024 * 1024) {
        throw new RuntimeException('Image must be 4MB or smaller.');
    }

    $tmp = (string)$file['tmp_name'];
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('The uploaded image could not be verified.');
    }
    $mime = mime_content_type($tmp) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    }

    $folder = trim($folder, '/');
    $targetDir = __DIR__ . '/../uploads/' . $folder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Upload folder could not be created.');
    }
    if (!is_writable($targetDir)) {
        throw new RuntimeException('Upload folder is not writable.');
    }

    $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $allowed[$mime];
    $target = $targetDir . '/' . $name;
    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Image could not be saved.');
    }

    return 'uploads/' . $folder . '/' . $name;
}

function upload_multiple_images(string $field, string $folder): array
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'] ?? null)) {
        return [];
    }

    $paths = [];
    $files = $_FILES[$field];
    foreach ($files['name'] as $i => $name) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $_FILES['_multi_upload'] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i],
        ];
        $uploaded = upload_image('_multi_upload', $folder);
        if ($uploaded) {
            $paths[] = $uploaded;
        }
    }
    unset($_FILES['_multi_upload']);
    return $paths;
}
