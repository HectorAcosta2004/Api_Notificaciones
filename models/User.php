<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Asegúrate de que esta función esté escrita exactamente así
    public function read() {
        $query = "SELECT u.id, u.name, u.email, u.roles, i.nombre as institucion 
                  FROM " . $this->table_name . " u
                  LEFT JOIN instituciones i ON u.institucion_id = i.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function findByEmail($email) {
        $query = "SELECT u.*, i.nombre as institucion_nombre, un.nombre as union_nombre 
                  FROM " . $this->table_name . " u
                  LEFT JOIN instituciones i ON u.institucion_id = i.id
                  LEFT JOIN union_iasd un ON u.union_id = un.id
                  WHERE u.email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserApps($user_id) {
        $query = "SELECT a.id, a.nombre, a.onesignal_app_id 
                  FROM apps a
                  INNER JOIN user_apps ua ON a.id = ua.app_id
                  WHERE ua.user_id = :user_id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>