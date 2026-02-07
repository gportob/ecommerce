<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');


// Permitir contar itens da sacola para usuários anônimos
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['anon_cart_id'])) {
        $_SESSION['anon_cart_id'] = session_id();
    }
    $userId = 'anon_' . $_SESSION['anon_cart_id'];
} else {
    $userId = $_SESSION['user_id'];
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(['cart_count' => 0]);
        exit;
    }


    $count = $db->prepare('SELECT COUNT(*) as total FROM cart WHERE user_id = :user_id');
    $count->bindParam(':user_id', $userId);
    $count->execute();
    $result = $count->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['cart_count' => $result['total']]);

} catch (Exception $e) {
    error_log('Erro em get-cart-count.php: ' . $e->getMessage());
    echo json_encode(['cart_count' => 0]);
}
