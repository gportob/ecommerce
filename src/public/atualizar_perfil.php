<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

// Proteção: Apenas usuários autenticados
Auth::checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            throw new Exception("Erro de conexão com o banco de dados");
        }

        $id = Auth::getUserId();
        $name = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $phone = trim($_POST['telefone'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $address = trim($_POST['endereco'] ?? '');
        $number = trim($_POST['numero'] ?? '');
        $neighborhood = trim($_POST['bairro'] ?? '');
        $city = trim($_POST['cidade'] ?? '');
        $state = trim($_POST['estado'] ?? '');
        $complement = trim($_POST['complemento'] ?? '');

        $sql = "UPDATE users SET 
                name = :name, cpf = :cpf, telefone = :phone, cep = :cep, 
                endereco = :address, numero = :number, bairro = :neighborhood, 
                cidade = :city, estado = :state, complemento = :complement 
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        
        // Bind dos parâmetros
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':number', $number, PDO::PARAM_STR);
        $stmt->bindParam(':neighborhood', $neighborhood, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':complement', $complement, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Dados atualizados com sucesso!";
            // Atualiza o nome da sessão também
            $_SESSION['user_name'] = $name;
        } else {
            $_SESSION['error'] = "Erro ao atualizar dados.";
        }

        header("Location: /perfil");
        exit();
    } catch (Exception $e) {
        error_log("Erro ao atualizar perfil: " . $e->getMessage());
        $_SESSION['error'] = "Erro ao processar sua solicitação.";
        header("Location: /perfil");
        exit();
    }
}