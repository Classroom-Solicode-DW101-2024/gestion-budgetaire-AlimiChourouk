<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

// Rediriger si non connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$erreur = "";

// Traitement de la suppression
if (isset($_POST['delete_transaction']) && isset($_POST['transaction_id'])) {
    $idTransaction = $_POST['transaction_id'];
    if (deleteTransaction($idTransaction, $pdo)) {
        $message = "Transaction supprimée avec succès.";
    } else {
        $erreur = "Erreur lors de la suppression de la transaction.";
    }
}

// Traitement de la modification
if (isset($_POST['edit_transaction'])) {
    $idTransaction = $_POST['transaction_id'];
    $newTransaction = [
        'type' => $_POST['type'],
        'categorie' => $_POST['categorie'],
        'montant' => $_POST['montant'],
        'description' => $_POST['description'],
        'date' => $_POST['date']
    ];
    
    if (editTransaction($idTransaction, $newTransaction, $pdo)) {
        $message = "Transaction modifiée avec succès.";
    } else {
        $erreur = "Erreur lors de la modification de la transaction.";
    }
}

// Récupération des filtres
$filter_type = isset($_POST['filter_type']) ? $_POST['filter_type'] : '';
$filter_date_start = isset($_POST['filter_date_start']) ? $_POST['filter_date_start'] : '';
$filter_date_end = isset($_POST['filter_date_end']) ? $_POST['filter_date_end'] : '';

// Récupération des transactions de l'utilisateur connecté
try {
    $sql = "
        SELECT t.id, t.montant, t.description, t.date_transaction, c.nom AS categorie, c.type
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
    ";
    
    $params = [$_SESSION['user']['id']];
    
    if ($filter_type) {
        $sql .= " AND c.type = ?";
        $params[] = $filter_type;
    }
    
    if ($filter_date_start) {
        $sql .= " AND t.date_transaction >= ?";
        $params[] = $filter_date_start;
    }
    
    if ($filter_date_end) {
        $sql .= " AND t.date_transaction <= ?";
        $params[] = $filter_date_end;
    }
    
    $sql .= " ORDER BY t.date_transaction DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculs pour les résumés
    $totalRevenus = 0;
    $totalDepenses = 0;
    
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === 'revenu') {
            $totalRevenus += $transaction['montant'];
        } else {
            $totalDepenses += $transaction['montant'];
        }
    }
    
    $solde = $totalRevenus - $totalDepenses;

    // Résumé du mois en cours
    $currentMonthStart = date('Y-m-01');
    $currentMonthEnd = date('Y-m-t');
    
    $sqlMonth = "
        SELECT c.type, SUM(t.montant) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND t.date_transaction BETWEEN ? AND ?
        GROUP BY c.type
    ";
    $stmtMonth = $pdo->prepare($sqlMonth);
    $stmtMonth->execute([$_SESSION['user']['id'], $currentMonthStart, $currentMonthEnd]);
    $monthSummary = $stmtMonth->fetchAll(PDO::FETCH_ASSOC);
    
    $monthRevenus = 0;
    $monthDepenses = 0;
    foreach ($monthSummary as $summary) {
        if ($summary['type'] === 'revenu') {
            $monthRevenus = $summary['total'];
        } else {
            $monthDepenses = $summary['total'];
        }
    }

    // Somme par catégorie
    $sqlCategories = "
        SELECT c.nom AS categorie, c.type, SUM(t.montant) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        GROUP BY c.nom, c.type
        ORDER BY c.type, c.nom
    ";
    $stmtCategories = $pdo->prepare($sqlCategories);
    $stmtCategories->execute([$_SESSION['user']['id']]);
    $categorySummary = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    // Transaction la plus élevée du mois (revenu et dépense)
    $sqlMax = "
        SELECT t.id, t.montant, t.description, t.date_transaction, c.nom AS categorie, c.type
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND t.date_transaction BETWEEN ? AND ? AND c.type = ?
        ORDER BY t.montant DESC
        LIMIT 1
    ";
    
    $stmtMaxRevenu = $pdo->prepare($sqlMax);
    $stmtMaxRevenu->execute([$_SESSION['user']['id'], $currentMonthStart, $currentMonthEnd, 'revenu']);
    $maxRevenu = $stmtMaxRevenu->fetch(PDO::FETCH_ASSOC);
    
    $stmtMaxDepense = $pdo->prepare($sqlMax);
    $stmtMaxDepense->execute([$_SESSION['user']['id'], $currentMonthStart, $currentMonthEnd, 'depense']);
    $maxDepense = $stmtMaxDepense->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
}

// Liste des catégories
$categories = [
    'revenu' => ['Salaire', 'Bourse', 'Ventes', 'Autres'],
    'depense' => ['Logement', 'Transport', 'Alimentation', 'Santé', 'Divertissement', 'Éducation', 'Autres']
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Transactions - FinTrack</title>
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
                    <li><a href="add_transaction.php">Ajouter</a></li>
                    <li><a href="view_transactions.php" class="active">Transactions</a></li>
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
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($erreur): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($erreur); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Tableau de bord</h2>
            </div>
            
            <div class="summary-cards">
                <div class="summary-card income">
                    <h3>Total des revenus</h3>
                    <div class="amount"><?php echo number_format($totalRevenus, 2); ?> €</div>
                </div>
                
                <div class="summary-card expense">
                    <h3>Total des dépenses</h3>
                    <div class="amount"><?php echo number_format($totalDepenses, 2); ?> €</div>
                </div>
                
                <div class="summary-card balance">
                    <h3>Solde actuel</h3>
                    <div class="amount"><?php echo number_format($solde, 2); ?> €</div>
                </div>
            </div>

            <!-- Résumé du mois en cours -->
            <div class="summary-cards">
                <div class="summary-card income">
                    <h3>Revenus ce mois-ci</h3>
                    <div class="amount"><?php echo number_format($monthRevenus, 2); ?> €</div>
                </div>
                
                <div class="summary-card expense">
                    <h3>Dépenses ce mois-ci</h3>
                    <div class="amount"><?php echo number_format($monthDepenses, 2); ?> €</div>
                </div>
            </div>

            <!-- Transactions les plus élevées du mois -->
            <div class="summary-cards">
                <?php if ($maxRevenu): ?>
                    <div class="summary-card income">
                        <h3>Revenu le plus élevé</h3>
                        <div class="amount"><?php echo number_format($maxRevenu['montant'], 2); ?> €</div>
                        <p><?php echo htmlspecialchars($maxRevenu['categorie']); ?> - <?php echo htmlspecialchars($maxRevenu['description'] ?: 'N/A'); ?></p>
                        <p><?php echo htmlspecialchars($maxRevenu['date_transaction']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="summary-card income">
                        <h3>Revenu le plus élevé</h3>
                        <p>Aucun revenu ce mois-ci.</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($maxDepense): ?>
                    <div class="summary-card expense">
                        <h3>Dépense la plus élevée</h3>
                        <div class="amount"><?php echo number_format($maxDepense['montant'], 2); ?> €</div>
                        <p><?php echo htmlspecialchars($maxDepense['categorie']); ?> - <?php echo htmlspecialchars($maxDepense['description'] ?: 'N/A'); ?></p>
                        <p><?php echo htmlspecialchars($maxDepense['date_transaction']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="summary-card expense">
                        <h3>Dépense la plus élevée</h3>
                        <p>Aucune dépense ce mois-ci.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Somme par catégorie -->
            <div class="card-header">
                <h3>Résumé par catégorie</h3>
            </div>
            <div class="summary-cards">
                <?php
                $revenusByCategory = [];
                $depensesByCategory = [];
                foreach ($categorySummary as $cat) {
                    if ($cat['type'] === 'revenu') {
                        $revenusByCategory[] = $cat;
                    } else {
                        $depensesByCategory[] = $cat;
                    }
                }
                ?>
                <div class="summary-card">
                    <h3>Revenus par catégorie</h3>
                    <?php if (empty($revenusByCategory)): ?>
                        <p>Aucun revenu enregistré.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($revenusByCategory as $cat): ?>
                                <li><?php echo htmlspecialchars($cat['categorie']); ?>: <span class="amount income"><?php echo number_format($cat['total'], 2); ?> €</span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="summary-card">
                    <h3>Dépenses par catégorie</h3>
                    <?php if (empty($depensesByCategory)): ?>
                        <p>Aucune dépense enregistrée.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($depensesByCategory as $cat): ?>
                                <li><?php echo htmlspecialchars($cat['categorie']); ?>: <span class="amount expense"><?php echo number_format($cat['total'], 2); ?> €</span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Mes Transactions</h2>
                <a href="add_transaction.php" class="button button-success">
                    <i class="fas fa-plus"></i> Nouvelle transaction
                </a>
            </div>

            <!-- Formulaire de filtrage -->
            <div class="filter-form">
                <form method="POST" class="form-inline">
                    <div class="form-group">
                        <label for="filter_type">Type :</label>
                        <select name="filter_type" id="filter_type">
                            <option value="">Tous</option>
                            <option value="revenu" <?php echo $filter_type === 'revenu' ? 'selected' : ''; ?>>Revenu</option>
                            <option value="depense" <?php echo $filter_type === 'depense' ? 'selected' : ''; ?>>Dépense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter_date_start">Date début :</label>
                        <input type="date" name="filter_date_start" id="filter_date_start" value="<?php echo htmlspecialchars($filter_date_start); ?>">
                    </div>
                    <div class="form-group">
                        <label for="filter_date_end">Date fin :</label>
                        <input type="date" name="filter_date_end" id="filter_date_end" value="<?php echo htmlspecialchars($filter_date_end); ?>">
                    </div>
                    <button type="submit" class="button button-primary">Filtrer</button>
                    <button type="button" class="button button-outline" onclick="resetFilters()">Réinitialiser</button>
                </form>
            </div>
            
            <?php if (empty($transactions)): ?>
                <p>Aucune transaction enregistrée.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Montant</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['date_transaction']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $transaction['type']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($transaction['type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['categorie']); ?></td>
                                <td class="<?php echo $transaction['type']; ?>">
                                    <?php 
                                    if ($transaction['type'] === 'revenu') {
                                        echo '+';
                                    } else {
                                        echo '-';
                                    }
                                    echo number_format($transaction['montant'], 2); 
                                    ?> €
                                </td>
                                <td><?php echo htmlspecialchars($transaction['description'] ?: 'N/A'); ?></td>
                                <td class="actions">
                                    <button class="icon-button edit-button" onclick="editTransaction(<?php echo $transaction['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="icon-button delete-button" onclick="confirmDelete(<?php echo $transaction['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmer la suppression</h3>
                <button class="close" onclick="closeModal('deleteModal')">×</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette transaction ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="transaction_id" id="delete_transaction_id">
                    <input type="hidden" name="delete_transaction" value="1">
                    <button type="button" class="button button-outline" onclick="closeModal('deleteModal')">Annuler</button>
                    <button type="submit" class="button button-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'édition de transaction -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier la transaction</h3>
                <button class="close" onclick="closeModal('editModal')">×</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="edit_transaction_id">
                    <input type="hidden" name="edit_transaction" value="1">
                    
                    <div class="form-group">
                        <label for="edit_type">Type :</label>
                        <select name="type" id="edit_type" onchange="updateEditCategories()" required>
                            <option value="revenu">Revenu</option>
                            <option value="depense">Dépense</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_categorie">Catégorie :</label>
                        <select name="categorie" id="edit_categorie" required>
                            <!-- Options seront ajoutées par JavaScript -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_montant">Montant :</label>
                        <input type="number" step="0.01" name="montant" id="edit_montant" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description :</label>
                        <input type="text" name="description" id="edit_description">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_date">Date :</label>
                        <input type="date" name="date" id="edit_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button-outline" onclick="closeModal('editModal')">Annuler</button>
                    <button type="submit" class="button button-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Définition des catégories
        const categories = {
            revenu: <?php echo json_encode($categories['revenu']); ?>,
            depense: <?php echo json_encode($categories['depense']); ?>
        };
        
        // Fonction pour confirmer la suppression
        function confirmDelete(id) {
            document.getElementById('delete_transaction_id').value = id;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        // Fonction pour éditer une transaction
        function editTransaction(id) {
            fetch(`get_transaction.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const transaction = data.transaction;
                        document.getElementById('edit_transaction_id').value = transaction.id;
                        document.getElementById('edit_type').value = transaction.type;
                        updateEditCategories();
                        document.getElementById('edit_categorie').value = transaction.categorie;
                        document.getElementById('edit_montant').value = transaction.montant;
                        document.getElementById('edit_description').value = transaction.description;
                        document.getElementById('edit_date').value = transaction.date_transaction;
                        document.getElementById('editModal').classList.add('show');
                    } else {
                        alert('Erreur lors de la récupération de la transaction');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la récupération de la transaction');
                });
        }
        
        // Fonction pour fermer les modals
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Fonction pour mettre à jour les catégories dans le formulaire d'édition
        function updateEditCategories() {
            const type = document.getElementById('edit_type').value;
            const select = document.getElementById('edit_categorie');
            select.innerHTML = '';
            
            if (categories[type]) {
                categories[type].forEach(cat => {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    select.appendChild(opt);
                });
            }
        }
        
        // Fonction pour réinitialiser les filtres
        function resetFilters() {
            document.getElementById('filter_type').value = '';
            document.getElementById('filter_date_start').value = '';
            document.getElementById('filter_date_end').value = '';
            document.querySelector('.filter-form form').submit();
        }
        
        // Fermeture des modals en cliquant à l'extérieur
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target === modals[i]) {
                    modals[i].classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>