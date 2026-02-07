<?php
echo "✓ PHP está funcionando corretamente\n";
echo "Versão: " . phpversion() . "\n";
echo "Sessão iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? "Sim" : "Não") . "\n";

// Testar conexão com banco
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "✓ Conexão com banco de dados: OK\n";
} else {
    echo "✗ Conexão com banco de dados: FALHOU\n";
}

// Verificar tabelas
echo "\nTabelas do banco:\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    echo "  - " . $row[0] . "\n";
}
?>
