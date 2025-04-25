<?php
session_start();
require __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

// Génération du jeton CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$Erreurs = [];
$Message = "";
$email = "";

if (isset($_POST['submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email)) {
        $Erreurs[] = "Veuillez entrer votre email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $Erreurs[] = "Veuillez entrer un email valide";
    }
    if (empty($password)) {
        $Erreurs[] = "Veuillez entrer votre mot de passe";
    }

    if (count($Erreurs) === 0) {
        try {
            $resulta = logUser($email, $password, $pdo); // Corrigé "logg" en "log"
            if ($resulta) {
                $Message = "Vous êtes connecté avec succès";
                $_SESSION ['user'] = $resulta;
                header("Location: add_transaction.php");
                exit(); // Arrêter l'exécution après redirection
            } else {
                $Message = "Mot de passe ou email incorrect";
            }
        } catch (Exception $e) {
            $Erreurs[] = "Une erreur s'est produite. Veuillez réessayer.";
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Connexion - FarhaEvents</title>
   
</head>
<body>
    <div class="container">
        <h2>Se connecter</h2>
        <?php if (!empty($Erreurs)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($Erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($Message)): ?>
            <p class="success"><?php echo htmlspecialchars($Message); ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" value="<?php echo htmlspecialchars($email); ?>">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe">
            <input type="submit" name="submit" value="Se connecter">
            <a href="register.php">Pas de compte ? S'inscrire</a>
        </form>
    </div>
</body>
</html>