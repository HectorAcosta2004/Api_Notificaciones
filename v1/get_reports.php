<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if($user_id) {
    $query = "SELECT r.*, a.nombre as app_nombre 
              FROM reportes_mensajes r 
              JOIN apps a ON r.app_id = a.id 
              WHERE r.user_id = ? 
              ORDER BY r.fecha_envio DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    
    echo json_encode([
        "status" => "success", 
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta user_id"]);
}
?>