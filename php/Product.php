<?php
// Product.php - Gestion des produits
class Product {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Récupérer tous les produits
    public function getAllProducts() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Ajouter un produit
    public function addProduct($name, $description, $price, $old_price, $category, $subcategory, $brand, $sku, $stock, $image) {
        if (!is_numeric($price) || $price <= 0 || filter_var($stock, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]) === false || trim($name) === '') {
            return ['success'=>false,'message'=>'Informations produit invalides'];
        }
        $old_price = $old_price === '' ? null : $old_price;
        try {
            $stmt = $this->db->prepare("INSERT INTO products (name, description, price, old_price, category, subcategory, brand, sku, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $old_price, $category, $subcategory, $brand, $sku, $stock, $image]);
            return ['success' => true, 'message' => 'Produit ajouté avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du produit: ' . 'Service temporarily unavailable'];
        }
    }

    // Supprimer un produit
    public function deleteProduct($product_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            return ['success' => true, 'message' => 'Produit supprimé avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du produit: ' . 'Service temporarily unavailable'];
        }
    }

    // Récupérer un produit par ID
    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    // Mettre à jour un produit
    public function updateProduct($id, $data) {
        try {
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, ['name','description','price','old_price','category','subcategory','brand','sku','stock','image'], true)) {
                    throw new InvalidArgumentException('Invalid product field');
                }
                if ($key === 'price' && (!is_numeric($value) || $value <= 0)) {
                    throw new InvalidArgumentException('Invalid product price');
                }
                $fields[] = "$key = ?";
                $values[] = $value;
            }

            $values[] = $id;
            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'Produit mis à jour avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du produit: ' . 'Service temporarily unavailable'];
        }
    }
}
