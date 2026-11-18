<?php
/**
 * HTTP integration tests for Tech Store.
 *
 * Runs against a live server (php -S ... router.php) backed by a disposable MySQL
 * database loaded from database.schema.sql and database.catalog.sql. Never point it
 * at a real database: it creates users and orders.
 *
 *   TECHSTORE_TEST_URL   base URL of the running server (default http://127.0.0.1:8000)
 *   TECHSTORE_DB_*       the same database settings the server uses
 *
 * Usage: php tests/integration_test.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$base = rtrim(getenv('TECHSTORE_TEST_URL') ?: 'http://127.0.0.1:8000', '/');
$db = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('TECHSTORE_DB_HOST') ?: '127.0.0.1', getenv('TECHSTORE_DB_NAME') ?: 'tech_store'),
    getenv('TECHSTORE_DB_USER') ?: '',
    getenv('TECHSTORE_DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

final class Client
{
    private string $jar;

    public function __construct(private string $base)
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'techstore-cookies-');
    }

    /** @return array{status:int, body:string, json:mixed} */
    public function request(string $method, string $path, array $options = []): array
    {
        $curl = curl_init($this->base . $path);
        $headers = $options['headers'] ?? [];
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->jar,
            CURLOPT_COOKIEFILE => $this->jar,
            CURLOPT_TIMEOUT => 20,
        ]);
        if (array_key_exists('json', $options)) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['json']));
        } elseif (array_key_exists('form', $options)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($options['form']));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $body = curl_exec($curl);
        if ($body === false) {
            throw new RuntimeException("Request to $path failed: " . curl_error($curl));
        }
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        return ['status' => $status, 'body' => $body, 'json' => json_decode($body, true)];
    }

    public function csrf(): string
    {
        return $this->request('GET', '/php/csrf.php')['json']['token'];
    }

    /** POST JSON with this session's CSRF token. */
    public function postJson(string $path, array $payload): array
    {
        return $this->request('POST', $path, ['json' => $payload, 'headers' => ['X-CSRF-Token: ' . $this->csrf()]]);
    }
}

$passed = 0;
$failed = 0;
function check(string $name, callable $test): void
{
    global $passed, $failed;
    try {
        $test();
        $passed++;
        echo "  PASS  $name\n";
    } catch (Throwable $e) {
        $failed++;
        echo "  FAIL  $name\n        {$e->getMessage()}\n";
    }
}
function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$run = bin2hex(random_bytes(4));
$email = "shopper-$run@example.test";
$password = 'a-long-test-passphrase';
$shopper = new Client($base);

$shipping = [
    'firstName' => 'Test', 'lastName' => 'Shopper', 'email' => $email, 'phone' => '20000000',
    'address' => '1 Rue de Test', 'addressLine2' => '', 'city' => 'Tunis', 'state' => 'Tunis',
    'zipcode' => '1000', 'country' => 'Tunisia', 'payment' => 'cod', 'coupon' => '', 'orderNotes' => '',
];
function placeOrder(Client $client, array $shipping, array $items, string $token): array
{
    return $client->request('POST', '/php/process_order.php', [
        'form' => $shipping + ['items' => json_encode($items), 'checkoutToken' => $token, 'csrf_token' => $client->csrf()],
        'headers' => ['Accept: application/json'],
    ]);
}

echo "Pages and routing\n";
check('home page is served', function () use ($shopper) {
    expect($shopper->request('GET', '/')['status'] === 200, 'GET / did not return 200');
});
check('router hides SQL dumps, dotfiles and JSON files', function () use ($shopper) {
    foreach (['/database.schema.sql', '/.env.example', '/.gitignore', '/tests/../.htaccess'] as $path) {
        $status = $shopper->request('GET', $path)['status'];
        expect($status === 404, "$path returned $status");
    }
});

echo "Catalog\n";
$products = [];
check('product API returns the seeded catalog', function () use ($shopper, &$products) {
    $response = $shopper->request('GET', '/php/get_products.php');
    expect($response['status'] === 200 && is_array($response['json']), 'unexpected response');
    $products = $response['json'];
    expect(count($products) > 0, 'catalog is empty');
    foreach ($products as $product) {
        expect(is_int($product['id']) && (is_int($product['price']) || is_float($product['price'])) && $product['price'] >= 0, 'malformed product ' . json_encode($product));
    }
});
check('category filter only returns that category', function () use ($shopper) {
    $response = $shopper->request('GET', '/php/get_products.php?category=laptop');
    expect(count($response['json']) > 0, 'no laptops');
    foreach ($response['json'] as $product) {
        expect($product['category'] === 'laptop', 'wrong category: ' . $product['category']);
    }
});

echo "Accounts and CSRF\n";
check('sign-up without a CSRF token is rejected', function () use ($base, $email, $password) {
    $response = (new Client($base))->request('POST', '/php/signup.php', ['json' => ['fullname' => 'No Token', 'email' => "x$email", 'password' => $password]]);
    expect($response['status'] === 403, "returned {$response['status']}");
});
check('sign-up rejects a password shorter than 12 characters', function () use ($shopper, $email) {
    $response = $shopper->postJson('/php/signup.php', ['fullname' => 'Short Password', 'email' => "short-$email", 'password' => 'short']);
    expect(($response['json']['success'] ?? null) === false, 'short password accepted');
});
check('sign-up succeeds and stores a hashed password', function () use ($shopper, $db, $email, $password) {
    $response = $shopper->postJson('/php/signup.php', ['fullname' => 'Test Shopper', 'email' => $email, 'password' => $password]);
    expect(($response['json']['success'] ?? null) === true, 'sign-up failed: ' . $response['body']);
    $stored = $db->prepare('SELECT password FROM users WHERE email = ?');
    $stored->execute([$email]);
    $hash = (string) $stored->fetchColumn();
    expect($hash !== $password && password_verify($password, $hash), 'password is not stored as a hash');
});
check('a second sign-up with the same email is refused', function () use ($shopper, $email, $password) {
    $response = $shopper->postJson('/php/signup.php', ['fullname' => 'Test Again', 'email' => $email, 'password' => $password]);
    expect(($response['json']['success'] ?? null) === false, 'duplicate account created');
});
check('login fails with a wrong password', function () use ($base, $email) {
    $response = (new Client($base))->postJson('/php/login.php', ['email' => $email, 'password' => 'wrong-password-123']);
    expect(($response['json']['success'] ?? null) === false, 'wrong password accepted');
});
check('login succeeds with the right password', function () use ($shopper, $email, $password) {
    $response = $shopper->postJson('/php/login.php', ['email' => $email, 'password' => $password]);
    expect(($response['json']['success'] ?? null) === true, 'login failed: ' . $response['body']);
});

echo "Checkout (Cash on Delivery)\n";
$orderNumber = null;
check('order total and stock are computed on the server', function () use ($shopper, $db, $products, $shipping, &$orderNumber) {
    $product = array_values(array_filter($products, fn ($p) => $p['price'] > 0 && $p['stock'] >= 2))[0];
    $stock = fn () => (int) $db->query('SELECT stock FROM products WHERE id = ' . (int) $product['id'])->fetchColumn();
    $before = $stock();
    $token = $shopper->request('GET', '/php/checkout_token.php')['json']['checkoutToken'];
    // A tampered client price must be ignored.
    $response = placeOrder($shopper, $shipping, [['id' => $product['id'], 'quantity' => 2, 'price' => 0.01, 'color' => '']], $token);
    expect(($response['json']['success'] ?? null) === true, 'order failed: ' . $response['body']);
    $orderNumber = $response['json']['orderNumber'];
    $order = $db->prepare('SELECT total, shipping_cost, payment_method FROM orders WHERE order_number = ?');
    $order->execute([$orderNumber]);
    $row = $order->fetch(PDO::FETCH_ASSOC);
    expect(abs((float) $row['total'] - ($product['price'] * 2 + 7)) < 0.001, "total {$row['total']} is not 2 x {$product['price']} + 7 shipping");
    expect($row['payment_method'] === 'cod', 'payment method is not COD');
    expect($stock() === $before - 2, 'stock was not decremented by 2');

    // Retrying with the same token must not create a second order or take stock again.
    $retry = placeOrder($shopper, $shipping, [['id' => $product['id'], 'quantity' => 2, 'color' => '']], $token);
    expect(($retry['json']['orderNumber'] ?? null) === $orderNumber, 'retry created a different order');
    expect($stock() === $before - 2, 'retry took stock again');
});
check('only Cash on Delivery is accepted', function () use ($shopper, $products, $shipping) {
    $token = $shopper->request('GET', '/php/checkout_token.php')['json']['checkoutToken'];
    $response = placeOrder($shopper, ['payment' => 'card'] + $shipping, [['id' => $products[0]['id'], 'quantity' => 1, 'color' => '']], $token);
    expect($response['status'] === 400 && ($response['json']['success'] ?? null) === false, 'card payment accepted');
});
check('a quantity above the 100-per-line limit is refused', function () use ($shopper, $products, $shipping) {
    $token = $shopper->request('GET', '/php/checkout_token.php')['json']['checkoutToken'];
    $response = placeOrder($shopper, $shipping, [['id' => $products[0]['id'], 'quantity' => 101, 'color' => '']], $token);
    expect(($response['json']['success'] ?? null) === false, 'invalid quantity accepted');
});
check('a product without a price cannot be ordered', function () use ($shopper, $db, $shipping) {
    $db->exec("INSERT INTO products (name, price, category, brand, stock) VALUES ('Unpriced test item', 0.00, 'test', 'Test', 5)");
    $id = (int) $db->lastInsertId();
    try {
        $token = $shopper->request('GET', '/php/checkout_token.php')['json']['checkoutToken'];
        $response = placeOrder($shopper, $shipping, [['id' => $id, 'quantity' => 1, 'color' => '']], $token);
        expect(($response['json']['success'] ?? null) === false, 'a 0.00 product was sold for shipping only');
        expect((int) $db->query("SELECT stock FROM products WHERE id = $id")->fetchColumn() === 5, 'stock changed');
    } finally {
        $db->exec("DELETE FROM products WHERE id = $id");
    }
});
check('a forged checkout token is refused', function () use ($shopper, $products, $shipping) {
    $response = placeOrder($shopper, $shipping, [['id' => $products[0]['id'], 'quantity' => 1, 'color' => '']], str_repeat('a', 64));
    expect(($response['json']['success'] ?? null) === false, 'forged token accepted');
});
check('the receipt is visible to its owner only', function () use ($shopper, $base, &$orderNumber) {
    expect($orderNumber !== null, 'no order was placed');
    $own = $shopper->request('GET', '/thankyoupage.php?order=' . rawurlencode($orderNumber));
    expect($own['status'] === 200 && str_contains($own['body'], $orderNumber), 'owner cannot see the receipt');
    $other = (new Client($base))->request('GET', '/thankyoupage.php?order=' . rawurlencode($orderNumber));
    expect($other['status'] === 404 && !str_contains($other['body'], 'Test Shopper'), 'another visitor can see the receipt');
});

echo "Login throttling\n";
check('an account is locked out after 10 failed logins in 15 minutes', function () use ($base, $email) {
    $target = "throttle-$email";
    $client = new Client($base);
    for ($i = 0; $i < 10; $i++) {
        $client->postJson('/php/login.php', ['email' => $target, 'password' => "wrong-password-$i"]);
    }
    $response = $client->postJson('/php/login.php', ['email' => $target, 'password' => 'wrong-password-x']);
    expect($response['status'] === 429, "11th attempt returned {$response['status']}");
});

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
