<?php
/**
 * Classe para gerenciar autenticação e autorização
 */
class Auth {
    
    /**
     * Verifica se usuário está autenticado
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Verifica se usuário é administrador
     */
    public static function isAdmin() {
        return self::isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Verifica se usuário é cliente
     */
    public static function isClient() {
        return self::isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client';
    }

    /**
     * Redireciona se não autenticado
     */
    public static function checkAuth($redirectTo = '/login') {
        if (!self::isAuthenticated()) {
            header("Location: $redirectTo?error=not_authenticated");
            exit();
        }
    }

    /**
     * Redireciona se não for admin
     */
    public static function checkAdmin($redirectTo = '/login') {
        if (!self::isAdmin()) {
            header("Location: $redirectTo?error=unauthorized");
            exit();
        }
    }

    /**
     * Redireciona se não for cliente
     */
    public static function checkClient($redirectTo = '/login') {
        if (!self::isClient()) {
            header("Location: $redirectTo?error=unauthorized");
            exit();
        }
    }

    /**
     * Obtém usuário da sessão
     */
    public static function getUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        return array(
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? ''
        );
    }

    /**
     * Obtém ID do usuário
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtém nome do usuário
     */
    public static function getUserName() {
        return $_SESSION['user_name'] ?? '';
    }

    /**
     * Obtém email do usuário
     */
    public static function getUserEmail() {
        return $_SESSION['user_email'] ?? '';
    }

    /**
     * Obtém role/permissão do usuário
     */
    public static function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Faz logout do usuário
     */
    public static function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

    /**
     * Cria sessão para novo usuário
     */
    public static function createSession($userId, $userName, $userEmail, $userRole) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_email'] = $userEmail;
        $_SESSION['user_role'] = $userRole;
    }
}
