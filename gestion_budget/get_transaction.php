<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

$idTransaction = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idTransaction) {
    echo json_encode(['success' => false, 'error' => 'ID de transaction invalide']);
    exit;
}

$transaction = getTransactionById($idTransaction, $pdo);

if ($transaction) {
    echo json_encode(['success' => true, 'transaction' => $transaction]);
} else {
    echo json_encode(['success' => false, 'error' => 'Transaction non trouvée']);
}
?>