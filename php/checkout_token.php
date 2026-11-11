<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CheckoutService.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405); header('Allow: GET'); echo json_encode(['success'=>false]); exit;
}
try {
    echo json_encode(['success'=>true, 'checkoutToken'=>issueCheckoutToken()]);
} catch (Throwable $e) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'message'=>'Please sign in again before checking out.']);
}
