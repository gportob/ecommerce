<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');


// Permitir adicionar à sacola mesmo deslogado
if (!isset($_SESSION['user_id'])) {
    // Usuário anônimo: usar session_id como identificador temporário
    if (!isset($_SESSION['anon_cart_id'])) {
        $_SESSION['anon_cart_id'] = session_id();
    }
    $userId = 'anon_' . $_SESSION['anon_cart_id'];
    $isAnon = true;
} else {
    $userId = $_SESSION['user_id'];
    $isAnon = false;
}

// Validar dados
$productId = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
$size = trim($_POST['size'] ?? '');

if (!$productId || $quantity < 1) {
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

    // Validar que o produto existe
    $checkProduct = $db->prepare('SELECT id, stock_by_size FROM products WHERE id = :id');
    $checkProduct->bindParam(':id', $productId, PDO::PARAM_INT);
    $checkProduct->execute();
    $product = $checkProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }

    // Validar tamanho se necessário
    $stockBySize = json_decode($product['stock_by_size'], true) ?? [];
    if (count($stockBySize) > 0 && !array_key_exists($size, $stockBySize)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tamanho inválido']);
        exit;
    }

    // Validar estoque
    if (isset($stockBySize[$size]) && $stockBySize[$size] < $quantity) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantidade indisponível em estoque']);
        exit;
    }

    // Adicionar ou atualizar na sacola (carrinho)
    $checkCart = $db->prepare('SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id AND selected_size = :size');
    $checkCart->bindParam(':user_id', $userId);
    $checkCart->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $checkCart->bindParam(':size', $size, PDO::PARAM_STR);
    $checkCart->execute();
    $cartItem = $checkCart->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Atualizar quantidade
        $newQuantity = $cartItem['quantity'] + $quantity;
        $update = $db->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id');
        $update->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
        $update->bindParam(':id', $cartItem['id'], PDO::PARAM_INT);
        $update->execute();
    } else {
        // Inserir novo item
        $insert = $db->prepare('INSERT INTO cart (user_id, product_id, quantity, selected_size) VALUES (:user_id, :product_id, :quantity, :size)');
        $insert->bindParam(':user_id', $userId);
        $insert->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $insert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $insert->bindParam(':size', $size, PDO::PARAM_STR);
        $insert->execute();
    }

    // Contar itens no carrinho

    $countCart = $db->prepare('SELECT COUNT(*) as total FROM cart WHERE user_id = :user_id');
    $countCart->bindParam(':user_id', $userId);
    $countCart->execute();
    $cartCount = $countCart->fetch(PDO::FETCH_ASSOC)['total'];


    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Produto adicionado à sacola com sucesso!',
        'cart_count' => $cartCount,
        'require_login' => $isAnon ? true : false
    ]);

} catch (Exception $e) {
    error_log('Erro em add-to-cart.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar ao carrinho']);
}
