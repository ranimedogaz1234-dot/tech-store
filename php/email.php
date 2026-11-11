<?php
// Send order confirmation email to customer
function sendOrderConfirmationEmail($orderId, $orderNumber, $orderData) {
    if (!mailConfigured()) return false;
    $to = $orderData['customer']['email'];
    $orderData['customer'] = array_map(fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'), $orderData['customer']);
    foreach ($orderData['items'] as &$item) { $item['name'] = htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8'); $item['color'] = htmlspecialchars((string) ($item['color'] ?? ''), ENT_QUOTES, 'UTF-8'); } unset($item);
    $orderData['notes'] = htmlspecialchars((string) ($orderData['notes'] ?? ''), ENT_QUOTES, 'UTF-8');
    $subject = "Order Confirmation - " . $orderNumber . " - " . SITE_NAME;
    
    $itemsHtml = '';
    foreach ($orderData['items'] as $item) {
        $colorInfo = isset($item['color']) ? "<br><small>Couleur: {$item['color']}</small>" : '';
        $subtotal = number_format($item['price'] * $item['quantity'], 2);
        $itemsHtml .= "
            <tr>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0;'>
                    <strong>{$item['name']}</strong>
                    {$colorInfo}
                </td>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center;'>
                    {$item['quantity']}
                </td>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: right;'>
                    {$subtotal} د.ت
                </td>
            </tr>
        ";
    }
    
    $subtotal = number_format($orderData['subtotal'], 2);
    $shipping = number_format($orderData['shippingCost'], 2);
    $total = number_format($orderData['total'], 2);
    
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
            .order-details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
            table { width: 100%; border-collapse: collapse; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
            .button { display: inline-block; padding: 12px 30px; background: #5C2A2F; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>tech store</h1>
                <p style='margin: 10px 0 0 0;'>Thank you for your order!</p>
            </div>
            
            <div class='content'>
                <h2>Order Confirmation</h2>
                <p>Hello {$orderData['customer']['firstName']} {$orderData['customer']['lastName']},</p>
                <p>Thank you for your order. Your order has been received and is being processed.</p>
                
                <div class='order-details'>
                    <h3>Order Details</h3>
                    <p><strong>Order Number:</strong> {$orderNumber}</p>
                    <p><strong>Order Date:</strong> " . date('F d, Y') . "</p>
                    
                    <h3 style='margin-top: 30px;'>Items Ordered</h3>
                    <table>
                        <thead>
                            <tr style='background: #f0f0f0;'>
                                <th style='padding: 15px; text-align: left;'>Product</th>
                                <th style='padding: 15px; text-align: center;'>Quantity</th>
                                <th style='padding: 15px; text-align: right;'>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='2' style='padding: 15px; text-align: right;'><strong>Subtotal:</strong></td>
                                <td style='padding: 15px; text-align: right;'>{$subtotal} د.ت</td>
                            </tr>
                            <tr>
                                <td colspan='2' style='padding: 15px; text-align: right;'><strong>Shipping:</strong></td>
                                <td style='padding: 15px; text-align: right;'>{$shipping} د.ت</td>
                            </tr>
                            <tr style='font-size: 18px; color: #5C2A2F;'>
                                <td colspan='2' style='padding: 15px; text-align: right;'><strong>Total:</strong></td>
                                <td style='padding: 15px; text-align: right;'><strong>{$total} د.ت</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <h3 style='margin-top: 30px;'>Shipping Address</h3>
                    <p>
                        {$orderData['customer']['firstName']} {$orderData['customer']['lastName']}<br>
                        {$orderData['customer']['address']}<br>
                        " . (!empty($orderData['customer']['addressLine2']) ? $orderData['customer']['addressLine2'] . "<br>" : "") . "
                        {$orderData['customer']['city']}, {$orderData['customer']['state']} {$orderData['customer']['zipcode']}<br>
                        {$orderData['customer']['country']}
                    </p>
                    
                    <h3>Payment Method</h3>
                    <p>Cash on Delivery (COD)</p>
                </div>
                
                <p>We will send you another email once your order has been shipped.</p>
                <p>If you have any questions about your order, please contact us at " . ADMIN_EMAIL . "</p>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " tech store. All rights reserved.</p>
                <p>Immeuble COMAR, Av. Habib Bourguiba, Tunis</p>
                <p>Phone: 72835835 | Email: contact@mysite.com</p>
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

// Send order notification email to admin with action buttons
function sendAdminNotificationEmail($orderId, $orderNumber, $orderData) {
    if (!mailConfigured()) return false;
    $to = ADMIN_EMAIL;
    $orderData['customer'] = array_map(fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'), $orderData['customer']);
    foreach ($orderData['items'] as &$item) { $item['name'] = htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8'); $item['color'] = htmlspecialchars((string) ($item['color'] ?? ''), ENT_QUOTES, 'UTF-8'); } unset($item);
    $orderData['notes'] = htmlspecialchars((string) ($orderData['notes'] ?? ''), ENT_QUOTES, 'UTF-8');
    $subject = "New Order Received - " . $orderNumber;
    
    $expires = time() + (30 * 24 * 60 * 60);
    $safeOrderNumber = (string) $orderNumber;
    $confirmToken = generateOrderActionToken('confirm', $safeOrderNumber, $expires);
    $cancelToken = generateOrderActionToken('cancel', $safeOrderNumber, $expires);
    $viewUrl = htmlspecialchars(SITE_URL . "/admin-panel.php?order=" . rawurlencode((string) $orderId), ENT_QUOTES, 'UTF-8');
    $actionButtons = '<p>Order action links are unavailable until TECHSTORE_ACTION_SECRET is configured.</p>';
    if ($confirmToken !== null && $cancelToken !== null) {
        $confirmUrl = SITE_URL . "/php/admin_action.php?action=confirm&order=" . rawurlencode($safeOrderNumber)
            . "&expires=" . $expires . "&token=" . rawurlencode($confirmToken);
        $cancelUrl = SITE_URL . "/php/admin_action.php?action=cancel&order=" . rawurlencode($safeOrderNumber)
            . "&expires=" . $expires . "&token=" . rawurlencode($cancelToken);
        $confirmUrl = htmlspecialchars($confirmUrl, ENT_QUOTES, 'UTF-8');
        $cancelUrl = htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8');
        $actionButtons = "<a href=\"{$confirmUrl}\" class=\"button btn-confirm\">Confirm Order</a>"
            . "<a href=\"{$viewUrl}\" class=\"button btn-view\">View Order</a>"
            . "<a href=\"{$cancelUrl}\" class=\"button btn-cancel\">Cancel Order</a>";
    }
    
    $itemsHtml = '';
    foreach ($orderData['items'] as $item) {
        $colorInfo = isset($item['color']) ? "<br><small>Couleur: {$item['color']}</small>" : '';
        $subtotal = number_format($item['price'] * $item['quantity'], 2);
        $itemsHtml .= "
            <tr>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0;'>
                    <strong>{$item['name']}</strong>
                    {$colorInfo}
                </td>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center;'>
                    {$item['quantity']}
                </td>
                <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: right;'>
                    {$subtotal} د.ت
                </td>
            </tr>
        ";
    }
    
    $total = number_format($orderData['total'], 2);
    
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
            .order-details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
            table { width: 100%; border-collapse: collapse; }
            .button { display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            .btn-confirm { background: #28a745; }
            .btn-cancel { background: #dc3545; }
            .btn-view { background: #007bff; }
            .actions { text-align: center; margin: 30px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>New Order Received</h1>
            </div>
            
            <div class='content'>
                <h2>Order #{$orderNumber}</h2>
                <p><strong>Date:</strong> " . date('F d, Y H:i:s') . "</p>
                
                <div class='order-details'>
                    <h3>Customer Information</h3>
                    <p>
                        <strong>Name:</strong> {$orderData['customer']['firstName']} {$orderData['customer']['lastName']}<br>
                        <strong>Email:</strong> {$orderData['customer']['email']}<br>
                        <strong>Phone:</strong> " . ($orderData['customer']['phone'] ?? 'N/A') . "
                    </p>
                    
                    <h3>Items Ordered</h3>
                    <table>
                        <thead>
                            <tr style='background: #f0f0f0;'>
                                <th style='padding: 15px; text-align: left;'>Product</th>
                                <th style='padding: 15px; text-align: center;'>Quantity</th>
                                <th style='padding: 15px; text-align: right;'>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>
                    
                    <h3 style='margin-top: 20px; color: #5C2A2F;'>Total: {$total} د.ت</h3>
                    
                    <h3 style='margin-top: 30px;'>Shipping Address</h3>
                    <p>
                        {$orderData['customer']['address']}<br>
                        " . (!empty($orderData['customer']['addressLine2']) ? $orderData['customer']['addressLine2'] . "<br>" : "") . "
                        {$orderData['customer']['city']}, {$orderData['customer']['state']} {$orderData['customer']['zipcode']}<br>
                        {$orderData['customer']['country']}
                    </p>
                    
                    <h3>Payment Method</h3>
                    <p>Cash on Delivery (COD)</p>
                    
                    " . (!empty($orderData['notes']) ? "<h3>Order Notes</h3><p>{$orderData['notes']}</p>" : "") . "
                </div>
                
                <div class='actions'>
                    {$actionButtons}
                </div>
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

// Send contact form email
function sendContactFormEmail($contactData) {
    if (!mailConfigured()) return false;
    $to = ADMIN_EMAIL;
    foreach (['name','email','phone','subject','message'] as $key) $contactData[$key] = clean($contactData[$key] ?? '');
    $subject = "New Contact Message: " . $contactData['subject'];
    
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
            .details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>New Contact Message</h1>
            </div>
            
            <div class='content'>
                <div class='details'>
                    <p><strong>From:</strong> {$contactData['name']}</p>
                    <p><strong>Email:</strong> {$contactData['email']}</p>
                    <p><strong>Phone:</strong> " . ($contactData['phone'] ?? 'N/A') . "</p>
                    <p><strong>Subject:</strong> {$contactData['subject']}</p>
                    <p><strong>Message:</strong></p>
                    <p style='padding: 15px; background: #f8f8f8; border-left: 4px solid #5C2A2F;'>
                        " . nl2br($contactData['message']) . "
                    </p>
                    <p><strong>Date:</strong> " . date('F d, Y H:i:s') . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$contactData['name']} <{$contactData['email']}>\r\n";
    $headers .= "Reply-To: {$contactData['email']}\r\n";
    
    return mail($to, $subject, $message, $headers);
}

