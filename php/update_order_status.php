<?php
require_once 'config.php';
requireCsrf();
require_once __DIR__ . '/Orders.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    http_response_code(415);
    echo json_encode(['success' => false, 'message' => 'JSON required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orderId']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$orderId = filter_var($data['orderId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$status = $data['status'];

// Validate status
$validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!$orderId || !is_string($status) || !in_array($status, $validStatuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $conn = getDBConnection();
    
    $result = (new Order($conn))->updateOrderStatus($orderId, $status);
    if (!$result['success']) {
        http_response_code(409); echo json_encode($result); exit;
    }
    
    // Get order details for email notification
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();
    
    // Send status update email to customer
    if ($order) {
        sendOrderStatusUpdateEmail($order, $status);
    }
    
    http_response_code($order ? 200 : 404);
    echo json_encode([
        'success' => (bool) $order,
        'message' => $order ? 'Order status updated successfully' : 'Order not found'
    ]);
    
} catch (Exception $e) {
    error_log("Update order status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update order status'
    ]);
}

function sendOrderStatusUpdateEmail($order, $status) {
    if (!mailConfigured()) return false;
    $to = $order['customer_email'];
    $order['customer_first_name'] = clean($order['customer_first_name']);
    $subject = "Order Status Update - " . $order['order_number'];
    
    $statusMessages = [
        'pending' => 'Your order is awaiting processing.',
        'confirmed' => 'Your order has been confirmed and is being prepared.',
        'processing' => 'Your order is currently being processed.',
        'shipped' => 'Your order has been shipped and is on its way to you.',
        'delivered' => 'Your order has been delivered. Thank you for shopping with us!',
        'cancelled' => 'Your order has been cancelled.'
    ];
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #5C2A2F; color: white; padding: 30px; text-align: center; }
            .content { background: #f8f8f8; padding: 30px; }
            .status-badge { display: inline-block; padding: 10px 20px; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .status-confirmed { background: #d4edda; color: #155724; }
            .status-processing { background: #cce5ff; color: #004085; }
            .status-shipped { background: #d1ecf1; color: #0c5460; }
            .status-delivered { background: #d4edda; color: #155724; }
            .status-cancelled { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>tech store</h1>
                <p style='margin: 10px 0 0 0;'>Order Status Update</p>
            </div>
            
            <div class='content'>
                <h2>Hello {$order['customer_first_name']},</h2>
                
                <div class='status-badge status-{$status}'>
                    Status: " . ucfirst($status) . "
                </div>
                
                <p>{$statusMessages[$status]}</p>
                
                <p><strong>Order Number:</strong> {$order['order_number']}</p>
                <p><strong>Order Total:</strong> " . number_format($order['total'], 2) . " د.ت</p>
                
                <p>If you have any questions, please contact us at " . ADMIN_EMAIL . "</p>
                
                <p>Thank you for shopping with us!</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . MAIL_FROM . ">\r\n";
    
    return mail($to, $subject, $message, $headers);
}

