<?php
require_once 'db.php';
session_start(); 

$Erreurs = [];
$Message = "";
$nom = "";
$email = "";
$passworde = "";

// Générer un jeton CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passworde = trim($_POST['passworde'] ?? '');

    // Vérification des champs
    if (empty($nom)) {
        $Erreurs[] = "Veuillez entrer votre nom !";
    }
    if (empty($email)) {
        $Erreurs[] = "Veuillez entrer votre email !";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $Erreurs[] = "Veuillez entrer un email valide.";
    }
    if (empty($passworde)) {
        $Erreurs[] = "Veuillez entrer un mot de passe !";
    } elseif (strlen($passworde) < 8) {
        $Erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Gestion de la présence de l'email
    if (count($Erreurs) == 0) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stm = $pdo->prepare($sql);
        $stm->execute(['email' => $email]);

        if ($stm->fetch()) {
            $Erreurs[] = "Ce compte existe déjà !";
        } else {
            $sql = "INSERT INTO users (nom, email, password) VALUES (:nom, :email, :passworde)";
            $stm = $pdo->prepare($sql);
            $stm->execute([
                'nom' => $nom,
                'email' => $email,
                'passworde' => password_hash($passworde, PASSWORD_DEFAULT)
            ]);
            $Message = "Vous êtes inscrit avec succès !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        form {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <form action="" method="post">
        <h2>Inscrivez-vous</h2>
        <?php if (!empty($Erreurs)): ?>
            <ul class="error">
                <?php foreach ($Erreurs as $erreur): ?>
                    <li><?= htmlspecialchars($erreur); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (!empty($Message)): ?>
            <p class="success"><?=  htmlspecialchars($Message); ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" value="<?php echo htmlspecialchars($nom); ?>">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" value="<?php echo htmlspecialchars($email); ?>">
        <label for="passworde">Mot de passe :</label>
        <input type="password" id="passworde" name="passworde" placeholder="Entrez votre mot de passe">
        <input type="submit" name="submit" value="S'inscrire">
        <a href="login.php">connexion</a>
    </form>
    
</body>
</html>