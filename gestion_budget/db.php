<?php
$host = 'localhost';
$dbname = 'gestion_budget';
$username = 'root';
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie à la base de données.";

    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

?>
   