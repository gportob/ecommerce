<?php
session_start();
require_once __DIR__ . '/../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            throw new Exception("Erro de conexão com o banco de dados");
        }

        // Validação de entrada
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validações
        if (!$name || !$email || !$password) {
            header("Location: /login?error=empty_fields");
            exit();
        }

        if ($password !== $password_confirm) {
            header("Location: /login?error=password_mismatch");
            exit();
        }

        if (strlen($password) < 6) {
            header("Location: /login?error=weak_password");
            exit();
        }

        // Hash da senha
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Verificar se email já existe
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            header("Location: /login?error=email_exists");
            exit();
        }

        // Inserir novo usuário
        $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'client')";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Registro bem-sucedido
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'client';
            
            header("Location: /perfil?msg=welcome");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Erro ao registrar: " . $e->getMessage());
        
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            header("Location: /login?error=email_exists");
        } else {
            header("Location: /login?error=registration_failed");
        }
        exit();
    } catch (Exception $e) {
        error_log("Erro geral no registro: " . $e->getMessage());
        header("Location: /login?error=system_error");
        exit();
    }
} else {
    header("Location: /login");
    exit();
}