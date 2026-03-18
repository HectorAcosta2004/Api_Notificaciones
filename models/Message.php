<?php
class Message {
    private $conn;
    private $table_name = "reportes_mensajes";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Guardar el registro del mensaje enviado
    public function create($user_id, $app_id, $titulo, $descripcion) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, app_id=:app_id, titulo=:titulo, descripcion=:descripcion, fecha_envio=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":app_id", $app_id);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":descripcion", $descripcion);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>