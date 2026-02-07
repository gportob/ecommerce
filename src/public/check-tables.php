<?php
require_once __DIR__ . '/config/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Verificar tabelas
$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='essence_lingerie_db' ORDER BY TABLE_NAME";
$result = $conn->query($sql);
$tables = $result->fetchAll(PDO::FETCH_COLUMN);

echo "Tabelas:\n";
foreach ($tables as $table) {
    echo "- " . $table . "\n";
}
?>
