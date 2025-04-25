<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

// Rediriger si non connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - FinTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="styles.css?v=2">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-wallet"></i> FinTrack
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Accueil</a></li>
                    <li><a href="add_transaction.php">Ajouter</a></li>
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
        <section class="hero">
            <h1>Bienvenue sur FinTrack</h1>
            <p>Gérez vos finances personnelles facilement et efficacement.</p>
            <a href="add_transaction.php" class="button button-primary button-lg">
                <i class="fas fa-plus"></i> Ajouter une transaction
            </a>
        </section>

        <section id="how-to-use" class="card">
            <div class="card-header">
                <h2>Comment utiliser FinTrack</h2>
            </div>
            <div class="card-body">
                <div class="guide">
                    <div class="guide-item">
                        <i class="fas fa-plus-circle"></i>
                        <h3>1. Ajouter des transactions</h3>
                        <p>Enregistrez vos revenus et dépenses en sélectionnant une catégorie, un montant et une date.</p>
                    </div>
                    <div class="guide-item">
                        <i class="fas fa-list"></i>
                        <h3>2. Suivre vos transactions</h3>
                        <p>Consultez toutes vos transactions, filtrez par type ou date, et modifiez/supprimez si nécessaire.</p>
                    </div>
                    <div class="guide-item">
                        <i class="fas fa-chart-line"></i>
                        <h3>3. Analyser votre budget</h3>
                        <p>Voyez votre solde actuel, vos revenus totaux et vos dépenses pour mieux gérer votre budget.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="card">
            <div class="card-header">
                <h2>À propos de FinTrack</h2>
            </div>
            <div class="card-body">
                <p>FinTrack est une application simple et intuitive conçue pour vous aider à suivre vos finances personnelles. Que vous souhaitiez surveiller vos dépenses quotidiennes ou planifier votre budget mensuel, FinTrack vous offre les outils nécessaires pour rester organisé.</p>
            </div>
        </section>

        <section id="contact" class="card">
            <div class="card-header">
                <h2>Contact</h2>
            </div>
            <div class="card-body">
                <p>Pour toute question ou suggestion, contactez-nous à : <a href="mailto:support@fintrack.com">support@fintrack.com</a></p>
            </div>
        </section>

        <section id="privacy" class="card">
            <div class="card-header">
                <h2>Politique de confidentialité</h2>
            </div>
            <div class="card-body">
                <p>Vos données sont sécurisées et utilisées uniquement pour fournir les services de FinTrack. Nous ne partageons pas vos informations personnelles avec des tiers.</p>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> FinTrack. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="#about">À propos</a>
                <a href="#contact">Contact</a>
                <a href="#privacy">Politique de confidentialité</a>
            </div>
        </div>
    </footer>
</body>
</html>