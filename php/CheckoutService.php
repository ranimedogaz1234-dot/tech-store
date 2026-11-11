<?php
require_once __DIR__ . '/Orders.php';

function checkoutIdentity() {
    if (isset($_SESSION['user_id']) && !isLoggedIn()) {
        throw new RuntimeException('Please sign in again before checking out.');
    }
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// Bind retry tokens to this session and account, with a bounded 24-hour lifetime.
function issueCheckoutToken(): string {
    $identity = checkoutIdentity();
    foreach ($_SESSION['checkout_attempts'] ?? [] as $key => $attempt) {
        if ($attempt['created'] < time() - 86400) unset($_SESSION['checkout_attempts'][$key]);
    }
    if (count($_SESSION['checkout_attempts'] ?? []) >= 20) {
        unset($_SESSION['checkout_attempts'][array_key_first($_SESSION['checkout_attempts'])]);
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['checkout_attempts'][hash('sha256', $token)] = ['created'=>time(), 'identity'=>$identity];
    return $token;
}

function submitCheckout(array $data, $items, $token): array {
    $identity = checkoutIdentity();
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
        return ['success'=>false, 'message'=>'Reload checkout before placing your order. Your cart has not been changed.'];
    }
    $key = hash('sha256', $token);
    $attempt = $_SESSION['checkout_attempts'][$key] ?? null;
    if (!$attempt || $attempt['created'] < time() - 86400 || $attempt['identity'] !== $identity) {
        return ['success'=>false, 'message'=>'Checkout expired. Reload checkout; your cart has not been changed.'];
    }
    $data['user_id'] = $identity;
    $data['checkoutKey'] = $key;
    $result = (new Order(getDB()))->createOrder($data, $items);
    if ($result['success']) $result['redirect'] = 'thankyoupage.php?order=' . rawurlencode($result['orderNumber']);
    return $result;
}
