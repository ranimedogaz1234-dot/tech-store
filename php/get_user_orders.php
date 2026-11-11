<?php
// get_user_orders.php - Récupérer les commandes de l'utilisateur connecté
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $conn = getDB();

    // Récupérer les commandes avec les items
    $stmt = $conn->prepare("
        SELECT
            o.id,
            o.order_number,
            o.customer_first_name,
            o.customer_last_name,
            o.customer_email,
            o.customer_phone,
            o.shipping_address,
            o.shipping_city,
            o.shipping_state,
            o.shipping_zipcode,
            o.shipping_country,
            o.subtotal,
            o.shipping_cost,
            o.total,
            o.status,
            o.created_at,
            GROUP_CONCAT(
                CONCAT(oi.product_name, ' (x', oi.quantity, ')')
                SEPARATOR ', '
            ) as items_list
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");

    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupérer les items détaillés
    foreach ($orders as &$order) {
        $stmtItems = $conn->prepare("
            SELECT
                oi.product_name,
                oi.quantity,
                oi.product_price,
                oi.subtotal
            FROM order_items oi
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$order['id']]);
        $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . 'Service temporarily unavailable'
    ]);
}
