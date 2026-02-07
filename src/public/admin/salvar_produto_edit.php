<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../config/StockManager.php';

// Proteção: Apenas admins e método POST
Auth::checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            throw new Exception("Erro de conexão com o banco de dados");
        }

        // Validação e limpeza de entrada
        $productId = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $category_id = filter_var($_POST['category_id'] ?? '', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
        $image_url = trim($_POST['image_url'] ?? '');
        $is_offer = isset($_POST['is_offer']) ? 1 : 0;

        // Validações
        if (!$productId) {
            throw new Exception("Produto inválido.");
        }
        if (!$category_id) {
            throw new Exception("Categoria inválida.");
        }
        if (!$name || strlen($name) < 3) {
            throw new Exception("Nome do produto inválido.");
        }
        if ($price === false || $price <= 0) {
            throw new Exception("Preço inválido.");
        }

        // Processar tamanhos e estoque por tamanho
        $sizes = $_POST['sizes'] ?? [];
        if (empty($sizes)) {
            throw new Exception("Pelo menos um tamanho deve ser selecionado.");
        }

        $sizes_json = json_encode($sizes);
        $stock_by_size = StockManager::generateStockFromPost($_POST);
        
        // Validar estoque
        $total_stock = StockManager::getTotalStock($stock_by_size);
        if ($total_stock <= 0) {
            throw new Exception("Estoque total deve ser maior que zero.");
        }
        
        $stock_json = StockManager::arrayToJson($stock_by_size);

        // Verificar se produto existe
        $checkQuery = "SELECT id FROM products WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Produto não encontrado.");
        }

        // Atualizar produto
        $sql = "UPDATE products SET 
            category_id = :category_id, 
            name = :name, 
            codigo = :codigo, 
            description = :description, 
            price = :price, 
            image_url = :image_url, 
            sizes = :sizes,
            stock_by_size = :stock_by_size,
            is_offer = :is_offer, 
            updated_at = NOW()
            WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);
        $stmt->bindParam(':sizes', $sizes_json, PDO::PARAM_STR);
        $stmt->bindParam(':stock_by_size', $stock_json, PDO::PARAM_STR);
        $stmt->bindParam(':is_offer', $is_offer, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: /admin/?success=product_updated");
            exit();
        } else {
            throw new Exception("Erro ao atualizar produto.");
        }

    } catch (Exception $e) {
        error_log("Erro ao atualizar produto: " . $e->getMessage());
        header("Location: /admin/produtos_editar?id=" . ($_POST['id'] ?? '') . "&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: /admin/");
    exit();
}
