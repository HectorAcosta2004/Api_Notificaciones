<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los usuarios con el nombre de su institución
    public function read() {
        $query = "SELECT u.id, u.name, u.email, u.roles, i.nombre as institucion 
                  FROM " . $this->table_name . " u
                  LEFT JOIN instituciones i ON u.institucion_id = i.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>