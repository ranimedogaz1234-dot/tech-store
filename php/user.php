<?php
// classes/User.php - Classe simple pour gérer les utilisateurs
class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Inscription d'un utilisateur
    public function register($username, $email, $password, $firstName, $lastName, $phone = null) {
        if (!is_string($password) || strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
            return ['success'=>false,'message'=>'Le mot de passe doit contenir de 12 à 72 caractères.'];
        }
        
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Tous les champs sont requis'];
        }
       
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Nom d\'utilisateur ou email déjà utilisé'];
        }
       
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, first_name, last_name, phone, role, status) 
                  VALUES (?, ?, ?, ?, ?, ?, 'user', 'active')";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $phone]);
            return ['success' => true, 'message' => 'Inscription réussie'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }
    
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $username]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            if (password_verify($password, $user['password'])) {
              
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'firstName' => $user['first_name'],
                        'lastName' => $user['last_name']
                    ]
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Identifiants incorrects'];
    }
    
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateProfile($id, $firstName, $lastName, $email, $phone) {
        $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([$firstName, $lastName, $email, $phone, $id]);
            return ['success' => true, 'message' => 'Profil mis à jour'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
        }
    }

    public function getAllUsers() {
        $query = "SELECT id, username, email, first_name, last_name, phone, role, status, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function toggleUserStatus($id) {
        $query = "UPDATE users SET status = CASE WHEN status = 'active' THEN 'blocked' ELSE 'active' END WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Statut modifié'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur'];
        }
    }
    
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Utilisateur supprimé'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur'];
        }
    }
}
