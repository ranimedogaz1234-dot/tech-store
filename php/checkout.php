<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CheckoutService.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    http_response_code(405); header('Allow: POST'); echo json_encode(['success'=>false,'message'=>'POST JSON required']); exit;
}
requireCsrf();
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !is_array($data['customer'] ?? null)) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid data']); exit;
}
$orderData = ['payment'=>$data['payment'] ?? '', 'notes'=>$data['notes'] ?? '', 'coupon'=>$data['coupon'] ?? ''];
foreach (['firstName','lastName','email','phone','address','addressLine2','city','state','zipcode','country'] as $key) $orderData[$key] = $data['customer'][$key] ?? '';
try {
    $result = submitCheckout($orderData, $data['items'] ?? [], $data['checkoutToken'] ?? null);
} catch (Throwable $e) {
    error_log('Checkout API failed');
    $result = ['success'=>false,'message'=>'Checkout is temporarily unavailable. Your cart has not been changed.'];
}
if (!$result['success']) http_response_code(400);
echo json_encode($result);
