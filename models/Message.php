<?php
class Message {
    private $conn;
    private $table_name = "reportes_mensajes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $app_id, $titulo, $descripcion) {
        // Validaciones de seguridad (Punto 3)
        if(empty($titulo) || empty($descripcion)) {
            return false;
        }

        // Limpieza de etiquetas HTML para evitar inyecciones básicas
        $titulo = strip_tags($titulo);
        $descripcion = strip_tags($descripcion);

        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, app_id=:app_id, titulo=:titulo, descripcion=:descripcion, fecha_envio=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":app_id", $app_id);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":descripcion", $descripcion);

        return $stmt->execute();
    }
}