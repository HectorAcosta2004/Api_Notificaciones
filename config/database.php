<?php
class Database {
    private $host = "localhost";
    private $db_name = "reavivados";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            // Activamos excepciones para un mejor manejo de errores (Punto 3)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            // Error en formato JSON para no romper el cliente
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Error de conexión interna"]);
            exit;
        }
        return $this->conn;
    }
}