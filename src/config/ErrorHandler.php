<?php
declare(strict_types=1);

/**
 * Utilidades gerais para tratamento de erros e logging
 */
class ErrorHandler {
    
    /**
     * Log de erros em arquivo
     */
    public static function logError(string $message, string $context = ''): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        if ($context) {
            $logMessage .= " | Context: $context";
        }
        $logMessage .= "\n";
        
        error_log($logMessage, 3, __DIR__ . '/../../logs/errors.log');
    }
    
    /**
     * Log de ações importantes (auditoria)
     */
    public static function logAudit(string $action, string $user_id = '', string $details = ''): void {
        $timestamp = date('Y-m-d H:i:s');
        $auditMessage = "[$timestamp] User: $user_id | Action: $action";
        if ($details) {
            $auditMessage .= " | Details: $details";
        }
        $auditMessage .= "\n";
        
        error_log($auditMessage, 3, __DIR__ . '/../../logs/audit.log');
    }
    
    /**
     * Tratamento de exceções
     */
    public static function handleException(Exception $e, bool $production = false): void {
        self::logError($e->getMessage(), $e->getFile() . ':' . $e->getLine());
        
        if (!$production) {
            // Modo desenvolvimento: mostrar erro
            echo "<div class='alert alert-danger'>";
            echo "<strong>Erro:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        } else {
            // Modo produção: mensagem genérica
            echo "<div class='alert alert-danger'>";
            echo "<strong>Erro:</strong> Ocorreu um erro no sistema. Por favor, tente mais tarde.";
            echo "</div>";
        }
    }
    
    /**
     * Valida e sanitiza entrada
     */
    public static function sanitizeInput(string $input, string $type = 'string'): mixed {
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT);
            case 'float':
                return filter_var($input, FILTER_VALIDATE_FLOAT);
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL);
            case 'string':
            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }
}
