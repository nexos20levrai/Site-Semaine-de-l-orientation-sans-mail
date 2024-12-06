<?php
require_once 'config.php';

function register($nom, $prenom, $classe, $password) {
    global $pdo;
    
    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nom = ? AND prenom = ? AND classe = ?");
        $stmt->execute([$nom, $prenom, $classe]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Un élève avec ces informations existe déjà'];
        }
        
        // Hash password and create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, classe, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $classe, $hashedPassword]);
        
        return ['success' => true, 'message' => 'Inscription réussie!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
}

function login($nom, $prenom, $classe, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, classe, password, is_admin FROM users WHERE nom = ? AND prenom = ? AND classe = ?");
        $stmt->execute([$nom, $prenom, $classe]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['classe'] = $user['classe'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return ['success' => true, 'message' => 'Connexion réussie!'];
        }
        
        return ['success' => false, 'message' => 'Informations de connexion incorrectes'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la connexion'];
    }
}

function logout() {
    session_destroy();
    return ['success' => true, 'message' => 'Déconnexion réussie!'];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function getAllUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT id, nom, prenom, classe, is_admin, created_at FROM users ORDER BY classe, nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function toggleAdmin($userId) {
    global $pdo;
    
    try {
        // Get current admin status
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        
        // Toggle admin status
        $newStatus = !$user['is_admin'];
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        
        return [
            'success' => true,
            'message' => 'Statut administrateur mis à jour avec succès',
            'is_admin' => $newStatus
        ];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du statut'];
    }
}
?>
