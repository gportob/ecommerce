<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$cartId = filter_var($_POST['cart_id'] ?? '', FILTER_VALIDATE_INT);

if (!$cartId) {
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

    // Verificar que o item pertence ao usuário
    $check = $db->prepare('SELECT id FROM cart WHERE id = :id AND user_id = :user_id');
    $check->bindParam(':id', $cartId, PDO::PARAM_INT);
    $check->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $check->execute();

    if (!$check->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Item não encontrado']);
        exit;
    }

    // Remover
    $delete = $db->prepare('DELETE FROM cart WHERE id = :id');
    $delete->bindParam(':id', $cartId, PDO::PARAM_INT);
    $delete->execute();

    // Contar itens restantes
    $count = $db->prepare('SELECT COUNT(*) as total FROM cart WHERE user_id = :user_id');
    $count->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $count->execute();
    $cartCount = $count->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Produto removido do carrinho',
        'cart_count' => $cartCount
    ]);

} catch (Exception $e) {
    error_log('Erro em remove-from-cart.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao remover do carrinho']);
}
