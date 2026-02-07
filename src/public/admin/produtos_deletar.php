<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';

// Proteção: Apenas admins
Auth::checkAdmin();

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Validar ID do produto
    $productId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
    if (!$productId) {
        throw new Exception("Produto inválido.");
    }

    // Verificar se produto existe
    $checkQuery = "SELECT id FROM products WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        throw new Exception("Produto não encontrado.");
    }

    // Deletar produto
    $deleteQuery = "DELETE FROM products WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $productId, PDO::PARAM_INT);

    if ($deleteStmt->execute()) {
        header("Location: /admin/?success=product_deleted");
        exit();
    } else {
        throw new Exception("Erro ao deletar produto.");
    }

} catch (Exception $e) {
    error_log("Erro ao deletar produto: " . $e->getMessage());
    header("Location: /admin/?error=" . urlencode($e->getMessage()));
    exit();
}
