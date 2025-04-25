<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

// Rediriger si non connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Liste des catégories
$categories = [
    'revenu' => ['Salaire', 'Bourse', 'Ventes', 'Autres'],
    'depense' => ['Logement', 'Transport', 'Alimentation', 'Santé', 'Divertissement', 'Éducation', 'Autres']
];

$message = "";
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $montant = $_POST['montant'] ?? '';
    $categorie = $_POST['categorie'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';

    if (!in_array($type, ['revenu', 'depense'])) {
        $erreurs[] = "Type invalide.";
    }
    if (!is_numeric($montant) || $montant <= 0) {
        $erreurs[] = "Montant invalide.";
    }
    if (empty($categorie)) {
        $erreurs[] = "Catégorie requise.";
    }

    if (empty($erreurs)) {
        $transaction = [
            'user_id' => $_SESSION['user']['id'],
            'type' => $type,
            'montant' => $montant,
            'categorie' => $categorie,
            'description' => $description,
            'date' => $date
        ];
        if (addTransaction($transaction, $pdo)) {
            $message = "Transaction ajoutée avec succès.";
        } else {
            $erreurs[] = "Erreur lors de l’ajout.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une transaction - FinTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-wallet"></i> FinTrack
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="add_transaction.php" class="active">Ajouter</a></li>
                    <li><a href="view_transactions.php">Transactions</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['user']['nom'], 0, 1); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['user']['nom']); ?></span>
                <a href="logout.php" class="button button-sm button-outline">Déconnexion</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Ajouter une transaction</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($erreurs as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="type">Type :</label>
                        <select name="type" id="type" onchange="updateCategories()" required>
                            <option value="">-- Choisir --</option>
                            <option value="revenu">Revenu</option>
                            <option value="depense">Dépense</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categorie">Catégorie :</label>
                        <select name="categorie" id="categorie" required>
                            <option value="">-- Sélectionnez le type d'abord --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="montant">Montant :</label>
                        <input type="number" step="0.01" name="montant" id="montant" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description :</label>
                        <input type="text" name="description" id="description">
                    </div>

                    <div class="form-group">
                        <label for="date">Date :</label>
                        <input type="date" name="date" id="date" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                        <a href="view_transactions.php" class="button button-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> FinTrack. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="index.php#about">À propos</a>
                <a href="index.php#contact">Contact</a>
                <a href="index.php#privacy">Politique de confidentialité</a>
            </div>
        </div>
    </footer>

    <script>
        const categories = {
            revenu: <?php echo json_encode($categories['revenu']); ?>,
            depense: <?php echo json_encode($categories['depense']); ?>
        };

        function updateCategories() {
            const type = document.getElementById('type').value;
            const select = document.getElementById('categorie');
            select.innerHTML = '';

            if (categories[type]) {
                categories[type].forEach(cat => {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    select.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Sélectionnez le type d\'abord --';
                select.appendChild(opt);
            }
        }
    </script>
</body>
</html>