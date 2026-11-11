<?php
// admin-panel.php - Tableau de bord d'administration
require_once 'php/config.php';
require_once 'php/user.php';
require_once 'php/Product.php';
require_once 'php/Orders.php';

// Vérifier si l'utilisateur est admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.html');
    exit;
}

$db = getDB();
$userClass = new User($db);
$productClass = new Product($db);
$orderClass = new Order($db);

// Gestion des actions
$message = '';
$current_tab = $_GET['tab'] ?? 'products';
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403); exit('Invalid form token');
    }
    $action = $_POST['action'] ?? '';

    // Actions utilisateurs
    if ($action === 'toggle_user') {
        $result = $userClass->toggleUserStatus($_POST['user_id']);
        $message = $result['message'];
        $current_tab = 'users';
    }
    if ($action === 'delete_user') {
        $result = $userClass->deleteUser($_POST['user_id']);
        $message = $result['message'];
        $current_tab = 'users';
    }

    // Actions produits
    if ($action === 'add_product') {
        $result = $productClass->addProduct(
            $_POST['name'], $_POST['description'], $_POST['price'],
            $_POST['old_price'], $_POST['category'], $_POST['subcategory'],
            $_POST['brand'], $_POST['sku'], $_POST['stock'], $_POST['image']
        );
        $message = $result['message'];
        $current_tab = 'products';
    }
    if ($action === 'delete_product') {
        $result = $productClass->deleteProduct($_POST['product_id']);
        $message = $result['message'];
        $current_tab = 'products';
    }

    // Actions commandes
    if ($action === 'update_order_status') {
        $result = $orderClass->updateOrderStatus($_POST['order_id'], $_POST['status']);
        $message = $result['message'];
        $current_tab = 'orders';
    }
}

// Récupérer les données
$users = $userClass->getAllUsers();
$products = $productClass->getAllProducts();
$orders = $orderClass->getAllOrders();

// Statistiques
$stats = [
    'total_users' => count($users),
    'total_products' => count($products),
    'total_orders' => count($orders),
    'pending_orders' => count(array_filter($orders, fn($o) => $o['status'] === 'pending'))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - tech store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 80px auto;
            padding: 20px;
        }
        .admin-header {
            background: #5C2A2F;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: 600;
            color: #5C2A2F;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
        }
        .tab {
            padding: 15px 30px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
        }
        .tab.active {
            color: #5C2A2F;
            border-bottom-color: #5C2A2F;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table th {
            background: #f8f8f8;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }
        .btn-primary { background: #5C2A2F; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.html"><span class="tech">tech</span> <span class="store">store</span></a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="admin-panel.php">Admin</a></li>
                        <li><a href="php/logout.php">Déconnexion</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-user-shield"></i> Tableau de bord Administrateur</h1>
            <p>Bienvenue, <?php echo clean($_SESSION['first_name']); ?></p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?php echo clean($message); ?>
        </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Utilisateurs</h3>
                <div class="value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Produits</h3>
                <div class="value"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Commandes</h3>
                <div class="value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Commandes en attente</h3>
                <div class="value"><?php echo $stats['pending_orders']; ?></div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs">
            <button class="tab <?php echo $current_tab === 'products' ? 'active' : ''; ?>" onclick="showTab('products')">Produits</button>
            <button class="tab <?php echo $current_tab === 'orders' ? 'active' : ''; ?>" onclick="showTab('orders')">Commandes</button>
            <button class="tab <?php echo $current_tab === 'users' ? 'active' : ''; ?>" onclick="showTab('users')">Utilisateurs</button>
        </div>

        <!-- Contenu Produits -->
        <div id="products" class="tab-content <?php echo $current_tab === 'products' ? 'active' : ''; ?>">
            <h2>Gestion des Produits</h2>
            
            <button class="btn btn-success" onclick="document.getElementById('addProductForm').style.display='block'">
                <i class="fas fa-plus"></i> Ajouter un produit
            </button>
            
            <div id="addProductForm" style="display:none; background: white; padding: 20px; margin: 20px 0; border-radius: 10px;">
                <h3>Nouveau Produit</h3>
                <form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="add_product">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prix *</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Ancien prix</label>
                        <input type="number" step="0.01" name="old_price">
                    </div>
                    <div class="form-group">
                        <label>Catégorie *</label>
                        <input type="text" name="category" required>
                    </div>
                    <div class="form-group">
                        <label>Sous-catégorie</label>
                        <input type="text" name="subcategory">
                    </div>
                    <div class="form-group">
                        <label>Marque *</label>
                        <input type="text" name="brand" required>
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" required>
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" name="stock" required>
                    </div>
                    <div class="form-group">
                        <label>Image (chemin)</label>
                        <input type="text" name="image" placeholder="img/produit.jpg">
                    </div>
                    <button type="submit" class="btn btn-success">Ajouter</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('addProductForm').style.display='none'">Annuler</button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo clean($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> د.ت</td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo clean($product['category']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer ce produit ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Contenu Commandes -->
        <div id="orders" class="tab-content <?php echo $current_tab === 'orders' ? 'active' : ''; ?>">
            <h2>Gestion des Commandes</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo clean($order['order_number']); ?></td>
                        <td><?php echo clean($order['customer_first_name'] . ' ' . $order['customer_last_name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total'], 2); ?> د.ت</td>
                        <td><?php echo $order['status']; ?></td>
                        <td>
                            <form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Contenu Utilisateurs -->
        <div id="users" class="tab-content <?php echo $current_tab === 'users' ? 'active' : ''; ?>">
            <h2>Gestion des Utilisateurs</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo clean($user['username']); ?></td>
                        <td><?php echo clean($user['email']); ?></td>
                        <td><?php echo clean($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['status']; ?></td>
                        <td>
                            <form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="toggle_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if ($user['status'] === 'blocked'): ?>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-unlock"></i> Débloquer
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-ban"></i> Bloquer
                                    </button>
                                <?php endif; ?>
                            </form>
                            <form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Cacher tous les contenus
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activer l'onglet et le contenu sélectionnés
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
