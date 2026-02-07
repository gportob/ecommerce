<?php
class Database {
    private $host = "db"; // Docker service name
    private $db_name = "essence_lingerie_db";
    private $username = "root";
    private $password = "root_password";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Tenta conectar com PDO
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $this->conn->exec("SET CHARACTER SET utf8mb4");
        } catch(PDOException $exception) {
            // Log do erro para debug
            error_log("Erro de conexão com banco: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }

    public function isConnected() {
        return $this->conn !== null;
    }
}