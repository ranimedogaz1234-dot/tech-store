<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CheckoutService.php';
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); header('Allow: POST'); exit('POST required');
}
requireCsrf();
$wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
$data = [];
foreach (['firstName','lastName','email','phone','address','addressLine2','city','state','zipcode','country','payment','coupon'] as $key) {
    $data[$key] = $_POST[$key] ?? '';
}
$data['notes'] = $_POST['orderNotes'] ?? '';
$items = is_string($_POST['items'] ?? null) ? json_decode($_POST['items'], true) : null;
try {
    $result = submitCheckout($data, $items, $_POST['checkoutToken'] ?? null);
} catch (Throwable $e) {
    error_log('Checkout request failed');
    $result = ['success'=>false, 'message'=>'Checkout is temporarily unavailable. Your cart has not been changed.'];
}
if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    if (!$result['success']) http_response_code(400);
    echo json_encode($result); exit;
}
if ($result['success']) {
    // Post/Redirect/Get: refreshing the receipt never repeats an order POST.
    header('Location: ../' . $result['redirect'], true, 303); exit;
}
http_response_code(400);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Checkout — Tech Store</title><link rel="stylesheet" href="../css/style.css"></head>
<body><main class="container"><h1>Your order could not be placed</h1><p role="alert"><?php echo clean($result['message']); ?></p><p>Your cart has been kept. <a href="../checkout.html">Return to checkout</a></p></main></body></html>
