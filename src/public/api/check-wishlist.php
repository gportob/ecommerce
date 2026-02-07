<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_favorite' => false]);
    exit;
}

$productId = filter_var($_GET['product_id'] ?? '', FILTER_VALIDATE_INT);

if (!$productId) {
    http_response_code(400);
    echo json_encode(['is_favorite' => false]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(['is_favorite' => false]);
        exit;
    }

    $userId = $_SESSION['user_id'];


    $check = $db->prepare('SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id');
    $check->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $check->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $check->execute();

    echo json_encode(['is_favorite' => (bool)$check->fetch()]);

} catch (Exception $e) {
    error_log('Erro em check-wishlist.php: ' . $e->getMessage());
    echo json_encode(['is_favorite' => false]);
}
