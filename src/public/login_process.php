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
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            header("Location: /login?error=invalid_input");
            exit();
        }

        // Consulta segura ao banco
        $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redireciona conforme role
            if ($user['role'] === 'admin') {
                header("Location: /admin/");
            } else {
                header("Location: /perfil");
            }
            exit();
        } else {
            // Falha de login
            header("Location: /login?error=invalid_credentials");
            exit();
        }
    } catch (Exception $e) {
        error_log("Erro no login: " . $e->getMessage());
        header("Location: /login?error=system_error");
        exit();
    }
} else {
    header("Location: /login");
    exit();
}