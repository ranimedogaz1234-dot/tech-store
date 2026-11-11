<?php
ini_set('display_errors', '0');
if (!((version_compare(PHP_VERSION, '8.4.27', '>=') && version_compare(PHP_VERSION, '8.5.0', '<'))
    || (version_compare(PHP_VERSION, '8.5.12', '>=') && version_compare(PHP_VERSION, '8.6.0', '<')))) {
    http_response_code(503); exit('Use a supported patched PHP runtime: 8.4.27+ or 8.5.12+.');
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
// config.php - Configuration simple
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Configuration base de données
define('DB_HOST', getenv('TECHSTORE_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('TECHSTORE_DB_NAME') ?: 'tech_store');
define('DB_USER', getenv('TECHSTORE_DB_USER') ?: '');
define('DB_PASS', getenv('TECHSTORE_DB_PASS') ?: '');

// Configuration email
define('ADMIN_EMAIL', getenv('TECHSTORE_ADMIN_EMAIL') ?: 'admin@example.com');
define('SITE_NAME', 'tech store');
define('SITE_URL', getenv('TECHSTORE_SITE_URL') ?: 'http://localhost/tech_store');
define('MAIL_FROM', getenv('TECHSTORE_MAIL_FROM') ?: 'noreply@example.com');

// Fonction de connexion bd
function getDB() {
    if (DB_USER === '' || DB_PASS === '') {
        error_log('TECHSTORE_DB_USER and TECHSTORE_DB_PASS must be configured.');
        http_response_code(500);
        die('Database configuration is incomplete.');
    }

    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log('Tech Store database connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('Database connection failed. Check the server configuration.');
    }
}

// Compatibility for older endpoints that use the previous function name.
function getDBConnection() {
    return getDB();
}

function generateOrderActionToken(string $action, string $orderNumber, int $expires): ?string {
    $secret = getenv('TECHSTORE_ACTION_SECRET');
    if (!is_string($secret) || strlen($secret) < 32) {
        return null;
    }

    return hash_hmac('sha256', $action . "\n" . $orderNumber . "\n" . $expires, $secret);
}

function verifyOrderActionToken(string $action, string $orderNumber, int $expires, string $token): bool {
    if ($expires < time() || $expires > time() + (30 * 24 * 60 * 60)) {
        return false;
    }

    $expected = generateOrderActionToken($action, $orderNumber, $expires);
    return $expected !== null && hash_equals($expected, $token);
}

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) return false;
    $query = getDB()->prepare('SELECT role, status FROM users WHERE id = ?');
    $query->execute([$_SESSION['user_id']]);
    $account = $query->fetch(PDO::FETCH_ASSOC);
    if (!$account || $account['status'] !== 'active') {
        $_SESSION = [];
        return false;
    }
    $_SESSION['role'] = $account['role'];
    return true;
}

// Vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Protection contre XSS
function clean($data) {
    return htmlspecialchars((string) $data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string {
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function requireCsrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_string($token)
        || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['success'=>false,'message'=>'Invalid or expired form token. Reload the page.']));
    }
}

function inputString($value, int $max = 255): string {
    if (!is_string($value) || strlen($value) > $max || str_contains($value, "\0")) {
        http_response_code(400); header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['success'=>false,'message'=>'Invalid input field']));
    }
    return $value;
}

function mailConfigured(): bool {
    return getenv('TECHSTORE_MAIL_ENABLED') === '1' && filter_var(MAIL_FROM, FILTER_VALIDATE_EMAIL)
        && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL) && !preg_match('/[\r\n]/', MAIL_FROM . ADMIN_EMAIL);
}
