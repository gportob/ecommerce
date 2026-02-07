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
$quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);

if (!$cartId || $quantity < 1) {
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

    // Buscar item do carrinho
    $check = $db->prepare('SELECT c.id, c.product_id, c.selected_size, p.stock_by_size FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = :id AND c.user_id = :user_id');
    $check->bindParam(':id', $cartId, PDO::PARAM_INT);
    $check->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $check->execute();
    $cartItem = $check->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item não encontrado']);
        exit;
    }

    // Validar estoque
    $stockBySize = json_decode($cartItem['stock_by_size'], true) ?? [];
    if (isset($stockBySize[$cartItem['selected_size']]) && $stockBySize[$cartItem['selected_size']] < $quantity) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantidade não disponível em estoque']);
        exit;
    }

    // Atualizar
    $update = $db->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id');
    $update->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $update->bindParam(':id', $cartId, PDO::PARAM_INT);
    $update->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Quantidade atualizada'
    ]);

} catch (Exception $e) {
    error_log('Erro em update-cart-quantity.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar quantidade']);
}
