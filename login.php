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
                header("Location: transactions.php");
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
    <title>Connexion - FarhaEvents</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"] {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .error ul {
            list-style: none;
            padding: 0;
        }

        .success {
            color: #4CAF50;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
            display: block;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 24px;
            }

            input[type="email"],
            input[type="password"],
            input[type="submit"] {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
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