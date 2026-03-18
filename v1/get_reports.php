<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Recibimos el ID del usuario por la URL (ej. ?user_id=8)
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if($user_id) {
    // Consulta con JOIN para ver el nombre de la App (Punto 2)
    $query = "SELECT r.id, r.titulo, r.descripcion, r.fecha_envio, a.nombre as app_nombre 
              FROM reportes_mensajes r 
              JOIN apps a ON r.app_id = a.id 
              WHERE r.user_id = ? 
              ORDER BY r.fecha_envio DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $results
    ]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta user_id"]);
}