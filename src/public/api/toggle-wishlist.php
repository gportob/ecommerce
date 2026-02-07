<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
    exit;
}

$productId = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);

if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Erro de conexão');
    }

    $userId = $_SESSION['user_id'];

    // Validar que o produto existe
    $checkProduct = $db->prepare('SELECT id FROM products WHERE id = :id');
    $checkProduct->bindParam(':id', $productId, PDO::PARAM_INT);
    $checkProduct->execute();

    if (!$checkProduct->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }

    // Verificar se já está nos favoritos
    $checkFav = $db->prepare('SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id');
    $checkFav->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $checkFav->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $checkFav->execute();
    $favItem = $checkFav->fetch(PDO::FETCH_ASSOC);

    if ($favItem) {
        // Remover
        $delete = $db->prepare('DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id');
        $delete->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $delete->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $delete->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Removido dos favoritos',
            'action' => 'removed'
        ]);
    } else {
        // Adicionar
        $insert = $db->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)');
        $insert->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $insert->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $insert->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Adicionado aos favoritos!',
            'action' => 'added'
        ]);
    }

} catch (Exception $e) {
    error_log('Erro em toggle-wishlist.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao realizar esta ação']);
}
