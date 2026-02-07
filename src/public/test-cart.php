<?php
session_start();

// Simular um usuário logado
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

require_once __DIR__ . '/config/Database.php';

echo "=== Teste de Adição ao Carrinho ===\n\n";

try {
    // Testar conexão
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo "✗ Erro: Não conseguiu conectar ao banco\n";
        exit;
    }
    
    echo "✓ Conexão com banco: OK\n";
    
    // Verificar tabelas existem
    $tables = $db->query("SHOW TABLES LIKE 'cart'")->fetchAll();
    if (count($tables) > 0) {
        echo "✓ Tabela 'cart' existe\n";
    } else {
        echo "✗ Tabela 'cart' não existe!\n";
        exit;
    }
    
    // Verificar se produto existe
    $checkProduct = $db->prepare('SELECT id, name FROM products WHERE id = :id LIMIT 1');
    $checkProduct->bindParam(':id', 1, PDO::PARAM_INT);
    $checkProduct->execute();
    $product = $checkProduct->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo "✓ Produto encontrado: " . $product['name'] . "\n";
    } else {
        echo "✗ Nenhum produto encontrado\n";
        exit;
    }
    
    // Tentar adicionar ao carrinho
    $userId = 1;
    $productId = 1;
    $quantity = 2;
    $size = 'M';
    
    $insert = $db->prepare('INSERT INTO cart (user_id, product_id, quantity, selected_size) VALUES (:user_id, :product_id, :quantity, :size)');
    $insert->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $insert->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $insert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $insert->bindParam(':size', $size, PDO::PARAM_STR);
    $insert->execute();
    
    echo "✓ Item adicionado ao carrinho\n";
    
    // Contar itens
    $count = $db->prepare('SELECT COUNT(*) as total FROM cart WHERE user_id = :user_id');
    $count->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $count->execute();
    $result = $count->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ Itens no carrinho: " . $result['total'] . "\n";
    echo "\n=== Teste Concluído com Sucesso ===\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
