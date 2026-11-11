<?php

require_once 'config.php';
require_once __DIR__ . '/Orders.php';

// Get parameters
$action = $_GET['action'] ?? '';
$orderNumber = $_GET['order'] ?? '';
$expires = filter_input(INPUT_GET, 'expires', FILTER_VALIDATE_INT);
$token = $_GET['token'] ?? '';

header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'], true) || !in_array($action, ['confirm', 'cancel'], true)
    || !is_string($orderNumber) || !preg_match('/^[A-Za-z0-9-]{1,64}$/', $orderNumber)
    || !$expires || !is_string($token) || !verifyOrderActionToken($action, $orderNumber, $expires, $token)) {
    http_response_code(403);
    exit('This order action link is invalid or expired.');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Confirm order action</h1><p>'.clean($action).' '.clean($orderNumber).'</p><form method="post"><input type="hidden" name="csrf_token" value="'.clean(csrfToken()).'"><button>Confirm action</button></form>';
    exit;
}
requireCsrf();

try {
    $conn = getDBConnection();
    
    // Get order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = :order_number");
    $stmt->execute([':order_number' => $orderNumber]);
    $order = $stmt->fetch();
    
    if (!$order || $order['status'] !== 'pending') {
        http_response_code(404);
        exit('This order is unavailable or has already been processed.');
    }
    
    // Perform action
    switch ($action) {
        case 'confirm':
            if (!(new Order($conn))->updateOrderStatus($order['id'], 'confirmed', 'pending')['success']) { http_response_code(409); exit('Order already processed.'); }
            
            // Send confirmation email to customer
            sendOrderStatusEmail($order, 'confirmed');
            
            $message = 'Order confirmed successfully';
            $color = '#28a745';
            break;
            
        case 'cancel':
            if (!(new Order($conn))->updateOrderStatus($order['id'], 'cancelled', 'pending')['success']) { http_response_code(409); exit('Order already processed.'); }
            
            // Send cancellation email to customer
            sendOrderStatusEmail($order, 'cancelled');
            
            $message = 'Order cancelled';
            $color = '#dc3545';
            break;
            
    }
    
    // Display success page
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Action Completed - tech store</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: #f8f8f8;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
            }
            .icon {
                font-size: 60px;
                color: <?php echo $color; ?>;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 10px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: #5C2A2F;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .button:hover {
                background: #8B4049;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">✓</div>
            <h1>Success!</h1>
            <p><?php echo $message; ?></p>
            <p><strong>Order:</strong> <?php echo htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="<?php echo SITE_URL; ?>/admin-panel.php" class="button">View All Orders</a>
        </div>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    error_log("Admin action error: " . $e->getMessage());
    http_response_code(500);
    die('The order action could not be completed.');
}

// Send order status email to customer
function sendOrderStatusEmail($order, $status) {
    if (!mailConfigured()) return false;
    $to = $order['customer_email'];
    $order['customer_first_name'] = clean($order['customer_first_name']);
    $subject = "Order " . ucfirst($status) . " - " . $order['order_number'];
    
    $statusMessages = [
        'confirmed' => 'Your order has been confirmed and is being prepared for shipment.',
        'cancelled' => 'Your order has been cancelled. If you did not request this cancellation, please contact us.'
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
            .status-confirmed { background: #28a745; color: white; }
            .status-cancelled { background: #dc3545; color: white; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>tech store</h1>
            </div>
            
            <div class='content'>
                <h2>Order Status Update</h2>
                <p>Hello {$order['customer_first_name']},</p>
                
                <div class='status-badge status-{$status}'>
                    Order {$status}
                </div>
                
                <p>{$statusMessages[$status]}</p>
                
                <p><strong>Order Number:</strong> {$order['order_number']}</p>
                <p><strong>Order Total:</strong> " . number_format($order['total'], 2) . " د.ت</p>
                
                <p>If you have any questions, please contact us at " . ADMIN_EMAIL . "</p>
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

