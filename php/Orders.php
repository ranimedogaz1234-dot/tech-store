<?php
// classes/Order.php - Classe simple pour gérer les commandes
class Order {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Créer une commande
    public function createOrder($orderData, $items) {
        try {
            $this->db->beginTransaction();
            if (!is_array($items) || count($items) === 0 || count($items) > 100) {
                throw new InvalidArgumentException('Invalid cart');
            }
            if (!is_string($orderData['email'] ?? null) || strlen($orderData['email']) > 75
                || !filter_var($orderData['email'], FILTER_VALIDATE_EMAIL)
                || ($orderData['payment'] ?? '') !== 'cod') {
                throw new InvalidArgumentException('Invalid customer or payment details');
            }
            foreach (['firstName'=>50,'lastName'=>50,'address'=>250,'city'=>100,'state'=>100,'zipcode'=>20,'country'=>100] as $key=>$maximum) {
                if (!is_string($orderData[$key] ?? null) || trim($orderData[$key]) === '' || strlen($orderData[$key]) > $maximum || str_contains($orderData[$key], "\0")) {
                    throw new InvalidArgumentException('Invalid shipping details');
                }
            }
            $subtotal = 0;
            foreach (['phone'=>50,'addressLine2'=>2000,'notes'=>10000,'coupon'=>50] as $key=>$maximum) {
                if (!is_string($orderData[$key] ?? '') || strlen($orderData[$key] ?? '') > $maximum) throw new InvalidArgumentException('Invalid order field');
            }
            if (!is_string($orderData['checkoutKey'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $orderData['checkoutKey'])) {
                throw new InvalidArgumentException('Invalid checkout attempt');
            }
            // Existing UNIQUE order_number is the database idempotency constraint.
            // No schema migration or historical-payment conversion is required.
            $orderNumber = 'ORD' . strtoupper(substr($orderData['checkoutKey'], 0, 32));
            $existing = $this->getOrderByNumber($orderNumber);
            if ($existing) {
                if (($existing['user_id'] === null ? null : (int) $existing['user_id']) !== $orderData['user_id']) {
                    throw new RuntimeException('Checkout unavailable');
                }
                $this->db->commit();
                $this->rememberGuestOrder($existing['id'], $orderData['user_id']);
                return ['success'=>true, 'message'=>'Commande déjà enregistrée', 'orderNumber'=>$orderNumber, 'orderId'=>$existing['id']];
            }
            foreach ($items as &$item) {
                if (!is_array($item) || !is_string($item['color'] ?? '') || strlen($item['color'] ?? '') > 50) throw new InvalidArgumentException('Invalid item color');
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>100]]);
                $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
                if (!$quantity || !$id) throw new InvalidArgumentException('Invalid cart item');
                $query = $this->db->prepare('SELECT id,name,price,image,stock FROM products WHERE id=? FOR UPDATE');
                $query->execute([$id]);
                $product = $query->fetch(PDO::FETCH_ASSOC);
                if (!$product || $product['stock'] < $quantity) throw new InvalidArgumentException('Product unavailable');
                $stock = $this->db->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
                $stock->execute([$quantity,$id,$quantity]);
                if ($stock->rowCount() !== 1) throw new RuntimeException('Stock changed');
                $item = ['id'=>$id,'name'=>$product['name'],'price'=>$product['price'],'image'=>$product['image'],'quantity'=>$quantity,'color'=>$item['color'] ?? ''];
                $subtotal += (int) round($product['price'] * 100) * $quantity;
            }
            unset($item);
            if (strtoupper($orderData['coupon'] ?? '') === 'DEMO20') $subtotal = (int) round($subtotal * 0.8);
            $orderData['subtotal'] = $subtotal / 100;
            $orderData['shippingCost'] = 7;
            $orderData['total'] = ($subtotal + 700) / 100;
            
            // Insérer la commande
            $query = "INSERT INTO orders (order_number, user_id, customer_first_name, customer_last_name, 
                      customer_email, customer_phone, shipping_address, shipping_address_line2, 
                      shipping_city, shipping_state, shipping_zipcode, shipping_country, 
                      subtotal, shipping_cost, total, payment_method, status, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $orderNumber,
                $orderData['user_id'],
                $orderData['firstName'],
                $orderData['lastName'],
                $orderData['email'],
                $orderData['phone'],
                $orderData['address'],
                $orderData['addressLine2'],
                $orderData['city'],
                $orderData['state'],
                $orderData['zipcode'],
                $orderData['country'],
                $orderData['subtotal'],
                $orderData['shippingCost'],
                $orderData['total'],
                $orderData['payment'],
                $orderData['notes']
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Insérer les articles
            $itemQuery = "INSERT INTO order_items (order_id, product_id, product_name, product_price, 
                          product_image, quantity, color, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $itemStmt = $this->db->prepare($itemQuery);
            
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['price'],
                    $item['image'],
                    $item['quantity'],
                    $item['color'] ?? '',
                    $item['price'] * $item['quantity']
                ]);
            }
            
            $this->db->commit();
            $this->rememberGuestOrder($orderId, $orderData['user_id']);
            if (getenv('TECHSTORE_MAIL_ENABLED') === '1') {
                try {
                    require_once __DIR__ . '/email.php';
                    $mailData = ['customer'=>array_intersect_key($orderData, array_flip(['firstName','lastName','email','phone','address','addressLine2','city','state','zipcode','country'])), 'items'=>$items, 'subtotal'=>$orderData['subtotal'], 'shippingCost'=>$orderData['shippingCost'], 'total'=>$orderData['total'], 'payment'=>$orderData['payment'], 'notes'=>$orderData['notes'] ?? ''];
                    sendOrderConfirmationEmail($orderId, $orderNumber, $mailData);
                    sendAdminNotificationEmail($orderId, $orderNumber, $mailData);
                } catch (Throwable $mailError) { error_log('Order notification failed'); }
            }
            
            return [
                'success' => true,
                'message' => 'Commande créée',
                'orderNumber' => $orderNumber,
                'orderId' => $orderId
            ];
            
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Order creation failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Commande impossible. Vérifiez les produits et les informations saisies.'];
        }
    }

    private function rememberGuestOrder($id, $userId): void {
        if ($userId === null) {
            $_SESSION['guest_orders'][$id] = time();
            if (count($_SESSION['guest_orders']) > 20) unset($_SESSION['guest_orders'][array_key_first($_SESSION['guest_orders'])]);
        }
    }
    
    // Obtenir une commande par numéro
    public function getOrderByNumber($orderNumber) {
        $query = "SELECT * FROM orders WHERE order_number = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtenir les articles d'une commande
    public function getOrderItems($orderId) {
        $query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtenir toutes les commandes
    public function getAllOrders() {
        $query = "SELECT * FROM orders ORDER BY created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtenir les commandes d'un utilisateur
    public function getUserOrders($userId) {
        $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mettre à jour le statut d'une commande
    public function updateOrderStatus($orderId, $status, $expectedStatus = null) {
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!is_string($status) || !in_array($status, $validStatuses, true) || !filter_var($orderId, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]])) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }
        
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $previous = $stmt->fetchColumn();
            if ($previous === false || ($expectedStatus !== null && $previous !== $expectedStatus)) throw new RuntimeException('Order unavailable');
            if (($previous === 'cancelled') !== ($status === 'cancelled')) {
                $items = $this->getOrderItems($orderId);
                foreach ($items as $item) {
                    if ($status === 'cancelled') {
                        $stock = $this->db->prepare('UPDATE products SET stock=stock+? WHERE id=?');
                        $stock->execute([$item['quantity'], $item['product_id']]);
                    } else {
                        $stock = $this->db->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
                        $stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                        if ($stock->rowCount() !== 1) throw new RuntimeException('Stock unavailable');
                    }
                }
            }
            $stmt = $this->db->prepare('UPDATE orders SET status=? WHERE id=?');
            $stmt->execute([$status,$orderId]);
            $this->db->commit();
            return ['success' => true, 'message' => 'Statut mis à jour'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['success' => false, 'message' => 'Erreur'];
        }
    }
}
