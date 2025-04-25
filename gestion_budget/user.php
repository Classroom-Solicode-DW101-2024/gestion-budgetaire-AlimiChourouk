<?php

function addUser($user, $pdo) {
    // Vérifie si l'email existe déjà
    $sql = "SELECT * FROM users WHERE email = :email";
    $stm = $pdo->prepare($sql);
    $stm->execute(['email' => $user['email']]);

    if ($stm->fetch()) {
        return "Ce compte existe déjà !";
    }

    // Insertion du nouvel utilisateur
    $sql = "INSERT INTO users (nom, email, password) VALUES (:nom, :email, :password)";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        'nom' => $user['nom'],
        'email' => $user['email'],
        'password' => password_hash($user['password'], PASSWORD_DEFAULT)
    ]);

    return true;
}
// function pour vérifier si l'utilisateur existe , si le mot de passe est correct et enregister le user dans session .

function logUser($email, $password, $pdo) {
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            return $user; // Connexion réussie
        } else {
            return "Mot de passe incorrect.";
        }
    } else {
        return "Email non trouvé.";
    }
}

// Récupérer les catégories de revenu et dépense
function getCategoriesByType($type, $connection) {
    $stmt = $connection->prepare("SELECT id, nom FROM categories WHERE type = ?");
    $stmt->execute([$type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function addTransaction(array $transaction, PDO $pdo): bool {
    // Chercher l’ID de la catégorie
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom = :nom AND type = :type");
    $stmt->execute([
        'nom' => $transaction['categorie'],
        'type' => $transaction['type']
    ]);
    $categorie = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categorie) {
        return false; // Catégorie non trouvée
    }

    $sql = "INSERT INTO transactions (user_id, category_id, montant, description, date_transaction)
            VALUES (:user_id, :category_id, :montant, :description, :date_transaction)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'user_id' => $transaction['user_id'],
        'category_id' => $categorie['id'],
        'montant' => $transaction['montant'],
        'description' => $transaction['description'],
        'date_transaction' => $transaction['date']
    ]);
}
function deleteTransaction($idTransaction, $connection) {
    try {
        // Vérifier que la transaction appartient bien à l'utilisateur connecté
        $stmt = $connection->prepare("
            SELECT user_id 
            FROM transactions 
            WHERE id = ?
        ");
        $stmt->execute([$idTransaction]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la transaction n'existe pas ou n'appartient pas à l'utilisateur
        if (!$transaction || $transaction['user_id'] != $_SESSION['user']['id']) {
            return false;
        }
        
        // Supprimer la transaction
        $stmt = $connection->prepare("DELETE FROM transactions WHERE id = ?");
        return $stmt->execute([$idTransaction]);
    } catch (PDOException $e) {
        // Log l'erreur et retourner false
        error_log("Erreur de suppression de transaction: " . $e->getMessage());
        return false;
    }
}

// Fonction pour modifier une transaction
function editTransaction($idTransaction, $newTransaction, $connection) {
    try {
        // Vérifier que la transaction appartient bien à l'utilisateur connecté
        $stmt = $connection->prepare("
            SELECT user_id 
            FROM transactions 
            WHERE id = ?
        ");
        $stmt->execute([$idTransaction]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la transaction n'existe pas ou n'appartient pas à l'utilisateur
        if (!$transaction || $transaction['user_id'] != $_SESSION['user']['id']) {
            return false;
        }
        
        // Récupérer l'ID de la catégorie
        $stmt = $connection->prepare("
            SELECT id 
            FROM categories 
            WHERE nom = ? AND type = ?
        ");
        $stmt->execute([
            $newTransaction['categorie'],
            $newTransaction['type']
        ]);
        $categorie = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categorie) {
            return false; // Catégorie non trouvée
        }
        
        // Mettre à jour la transaction
        $stmt = $connection->prepare("
            UPDATE transactions 
            SET category_id = ?, montant = ?, description = ?, date_transaction = ? 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $categorie['id'],
            $newTransaction['montant'],
            $newTransaction['description'],
            $newTransaction['date'],
            $idTransaction
        ]);
    } catch (PDOException $e) {
        // Log l'erreur et retourner false
        error_log("Erreur de modification de transaction: " . $e->getMessage());
        return false;
    }
}

// Fonction pour récupérer une transaction par son ID
function getTransactionById($idTransaction, $connection) {
    try {
        $stmt = $connection->prepare("
            SELECT t.id, t.montant, t.description, t.date_transaction, c.nom AS categorie, c.type
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.id = ? AND t.user_id = ?
        ");
        $stmt->execute([$idTransaction, $_SESSION['user']['id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur de récupération de transaction: " . $e->getMessage());
        return null;
    }
}
?>
