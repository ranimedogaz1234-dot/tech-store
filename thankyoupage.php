<?php
// thank-you.php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/Orders.php';
header('Cache-Control: private, no-store');
if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET','HEAD'], true)) {
    http_response_code(405); header('Allow: GET, HEAD'); exit('GET required');
}

// Get order number from URL
$orderNumber = $_GET['order'] ?? null;
$order = null;
$items = [];

if (is_string($orderNumber) && preg_match('/^[A-Za-z0-9-]{1,64}$/', $orderNumber)) {
    try {
        $conn = getDBConnection();

        // Create Orders class instance
        $orders = new Order($conn);

        // Get order details using Orders class
        $order = $orders->getOrderByNumber($orderNumber);

        if ($order && !((isLoggedIn() && (isAdmin() || (int) $order['user_id'] === (int) $_SESSION['user_id'])) || ($order['user_id'] === null && isset($_SESSION['guest_orders'][$order['id']])))) { $order=null; http_response_code(404); }
        if ($order) {
            // Get order items using Orders class
            $items = $orders->getOrderItems($order['id']);
        }
    } catch (Throwable $e) {
        $order = null;
        error_log('Order confirmation unavailable');
        http_response_code(503);
    }
}
if (!$order && http_response_code() !== 503) http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - tech store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
    <style>
        .thank-you-container {
            max-width: 800px;
            margin: 80px auto;
            padding: 60px 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }
        
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .thank-you-container h1 {
            color: #5C2A2F;
            margin-bottom: 15px;
            font-size: 36px;
        }
        
        .thank-you-container p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .order-info {
            background: #f8f8f8;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .order-info-row:last-child {
            border-bottom: none;
        }
        
        .order-info-label {
            color: #666;
            font-weight: 600;
        }
        
        .order-info-value {
            color: #333;
        }
        
        .order-items {
            margin: 30px 0;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .order-item-info {
            flex: 1;
            text-align: left;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-item-quantity {
            color: #666;
            font-size: 14px;
        }
        
        .order-item-price {
            font-weight: 600;
            color: #5C2A2F;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #5C2A2F;
            color: white;
        }
        
        .btn-primary:hover {
            background: #8B4049;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: white;
            color: #5C2A2F;
            border: 2px solid #5C2A2F;
        }
        
        .btn-secondary:hover {
            background: #5C2A2F;
            color: white;
        }
        
        .error-message {
            color: #dc3545;
            padding: 20px;
            background: #f8d7da;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.html"><span class="tech">tech</span> <span class="store">store</span></a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="informatique.html">informatique</a></li>
                        <li class="dropdown">
                            <a href="ordinateurs.html">Ordinateurs <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="ordinateurs-bureau.html">Ordinateurs de Bureau</a></li>
                                <li><a href="ordinateurs-portable.html">Ordinateurs portable</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="peripheriques-accessoires.html">Périphériques & Accessoires <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="peripherique-pc.html">Périphérique PC</a></li>
                                <li><a href="accessoires-pc-portable.html">Accessoires PC Portable</a></li>
                            </ul>
                        </li>
                        <li><a href="contact.html">contact</a></li>
                    </ul>
                </nav>

                <div class="header-icons">
                    <a href="wishlist.html" class="icon-link"><i class="far fa-heart"></i><span class="badge">0</span></a>
                    <a href="#" class="icon-link search-icon"><i class="fas fa-search"></i></a>
                    <a href="dashboard.html" class="icon-link"><i class="far fa-user"></i></a>
                    <a href="cart.html" class="icon-link"><i class="fas fa-shopping-cart"></i><span class="badge">0</span></a>
                </div>

                <div class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Thank You Content -->
    <div class="thank-you-container">
        <?php if ($order): ?>
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1>Merci pour votre commande !</h1>
            <p>Votre commande a été enregistrée avec succès. Conservez votre numéro de commande pour le suivi.</p>
            
            <div class="order-info">
                <div class="order-info-row">
                    <span class="order-info-label">Numéro de commande :</span>
                    <span class="order-info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Date de commande :</span>
                    <span class="order-info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Statut :</span>
                    <span class="order-info-value">
                        <?php 
                        $statusLabels = [
                            'pending' => 'En attente',
                            'confirmed' => 'Confirmée',
                            'processing' => 'En traitement',
                            'shipped' => 'Expédiée',
                            'delivered' => 'Livrée',
                            'cancelled' => 'Annulée'
                        ];
                        echo clean($statusLabels[$order['status']] ?? $order['status']);
                        ?>
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Payment method :</span>
                    <span class="order-info-value"><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery (COD)' : 'Historical order — contact support'; ?></span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Delivery information :</span>
                    <span class="order-info-value">
                        <?php echo clean($order['customer_first_name'] . ' ' . $order['customer_last_name']); ?><br>
                        <?php echo clean($order['shipping_address']); ?><br>
                        <?php if ($order['shipping_address_line2']): ?><?php echo clean($order['shipping_address_line2']); ?><br><?php endif; ?>
                        <?php echo clean($order['shipping_zipcode'] . ' ' . $order['shipping_city'] . ', ' . $order['shipping_state']); ?><br>
                        <?php echo clean($order['shipping_country']); ?><br>
                        <?php echo clean($order['customer_email']); ?>
                        <?php if ($order['customer_phone']): ?><br><?php echo clean($order['customer_phone']); ?><?php endif; ?>
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Montant total :</span>
                    <span class="order-info-value" style="color: #5C2A2F; font-weight: 600; font-size: 18px;">
                        <?php echo number_format($order['total'], 2, ',', ' '); ?> د.ت
                    </span>
                </div>
            </div>
            
            <?php if (!empty($items)): ?>
                <h3 style="margin: 30px 0 20px 0; text-align: left;">Articles commandés</h3>
                <div class="order-items">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="order-item-image"
                                 onerror="this.src='img/placeholder.svg'">
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <?php if (!empty($item['color'])): ?>
                                    <small style="color: #999;">Couleur: <?php echo htmlspecialchars($item['color']); ?></small><br>
                                <?php endif; ?>
                                <div class="order-item-quantity">Quantité: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="order-item-price">
                                <?php echo number_format($item['subtotal'], 2, ',', ' '); ?> د.ت
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.html" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir mes commandes
                    </a>
                <?php endif; ?>
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
            
        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Commande introuvable</strong><br>
                Impossible de trouver les détails de votre commande. Veuillez vérifier le numéro de commande ou contacter notre service client.
            </div>
            
            <div class="action-buttons">
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
                <a href="contact.html" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <div class="footer-logo">
                        <span class="tech">tech</span> <span class="store">store</span>
                    </div>
                    <p>Votre destination pour les ordinateurs, périphériques et accessoires au meilleur rapport qualité-prix.</p>
                </div>
                <div class="footer-col">
                    <h4>Liens</h4>
                    <ul>
                        <li><a href="index.html">accueil</a></li>
                        <li><a href="peripheriques-accessoires.html">Périphériques & Accessoires</a></li>
                        <li><a href="ordinateurs.html">ordinateur</a></li>
                        <li><a href="contact.html">contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Suivez-nous</h4>
                    <ul class="social-links">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-tiktok"></i> TikTok</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact</h4>
                    <p><strong>Adresse:</strong> À configurer pour votre démonstration</p>
                    <p><strong>Horaires :</strong> Du lundi au vendredi de 9h00 à 17h00</p>
                    <p><strong>Téléphone:</strong> À configurer</p>
                    <p><strong>Email:</strong> contact@example.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/security.js"></script>
    <script src="js/products.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
