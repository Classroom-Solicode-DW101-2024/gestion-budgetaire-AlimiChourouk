<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

if (!isset($_GET['id'])) {
    header("Location: history.php");
    exit;
}

$idTransaction = $_GET['id'];

// Récupérer la transaction à modifier
$stmt = $connection->prepare("
    SELECT t.*, c.nom AS categorie, c.type
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$idTransaction, $_SESSION['user_id']]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header("Location: history.php");
    exit;
}

// Gestion du formulaire de modification
if (isset($_POST['submit'])) {
    $newTransaction = [
        'type_transaction' => $_POST['type_transaction'],
        'montant' => $_POST['montant'],
        'categorie' => $_POST['categorie'],
        'description' => $_POST['description'],
        'date' => $_POST['date']
    ];
    editTransaction($idTransaction, $newTransaction, $connection);
    header("Location: history.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une transaction</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; }
        .checkbox-group { margin-bottom: 15px; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        input[type="submit"] { background-color: #4CAF50; color: white; border: none; padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Modifier une transaction</h2>
        <form action="" method="POST">
            <div class="checkbox-group">
                <label>Type de transaction</label>
                <div>
                    <input type="checkbox" name="type_transaction" id="depense" value="depense" <?php if ($transaction['type'] == 'depense') echo 'checked'; ?>>
                    <label for="depense">Dépense</label>
                </div>
                <div>
                    <input type="checkbox" name="type_transaction" id="revenu" value="revenu" <?php if ($transaction['type'] == 'revenu') echo 'checked'; ?>>
                    <label for="revenu">Revenu</label>
                </div>
            </div>
            <label for="montant">Montant</label>
            <input type="number" min="1" name="montant" id="montant" value="<?php echo $transaction['montant']; ?>" required>
            <label for="categorie">Catégorie</label>
            <select name="categorie" id="categorie" required>
                <optgroup label="Revenu">
                    <option value="Salaire" <?php if ($transaction['categorie'] == 'Salaire') echo 'selected'; ?>>Salaire</option>
                    <option value="Bourse" <?php if ($transaction['categorie'] == 'Bourse') echo 'selected'; ?>>Bourse</option>
                    <option value="Ventes" <?php if ($transaction['categorie'] == 'Ventes') echo 'selected'; ?>>Ventes</option>
                    <option value="AutresRevenu" <?php if ($transaction['categorie'] == 'AutresRevenu') echo 'selected'; ?>>Autres</option>
                </optgroup>
                <optgroup label="Dépense">
                    <option value="Logement" <?php if ($transaction['categorie'] == 'Logement') echo 'selected'; ?>>Logement</option>
                    <option value="Transport" <?php if ($transaction['categorie'] == 'Transport') echo 'selected'; ?>>Transport</option>
                    <option value="Alimentation" <?php if ($transaction['categorie'] == 'Alimentation') echo 'selected'; ?>>Alimentation</option>
                    <option value="Santé" <?php if ($transaction['categorie'] == 'Santé') echo 'selected'; ?>>Santé</option>
                    <option value="Divertissement" <?php if ($transaction['categorie'] == 'Divertissement') echo 'selected'; ?>>Divertissement</option>
                    <option value="Éducation" <?php if ($transaction['categorie'] == 'Éducation') echo 'selected'; ?>>Éducation</option>
                    <option value="AutresDepense" <?php if ($transaction['categorie'] == 'AutresDepense') echo 'selected'; ?>>Autres</option>
                </optgroup>
            </select>
            <label for="description">Description</label>
            <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($transaction['description']); ?>">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" value="<?php echo $transaction['date_transaction']; ?>" required>
            <input type="submit" name="submit" value="Modifier la transaction">
        </form>
    </div>

    <script>
        // Assurer qu'un seul checkbox est sélectionné à la fois
        document.getElementById('depense').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('revenu').checked = false;
            }
        });

        document.getElementById('revenu').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('depense').checked = false;
            }
        });
    </script>
</body>
</html>