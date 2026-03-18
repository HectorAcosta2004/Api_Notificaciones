<?php
// Cabeceras obligatorias para API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Obtenemos los datos enviados (JSON)
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    
    // 1. Buscar si el usuario existe
    $userData = $user->findByEmail($data->email);

    if($userData) {
        // 2. Verificar la contraseña (hash)
        if(password_verify($data->password, $userData['password'])) {
            
            // 3. Obtener las apps asociadas a este usuario
            $userApps = $user->getUserApps($userData['id']);

            http_response_code(200);
            echo json_encode(array(
                "status" => "success",
                "message" => "Login exitoso",
                "user" => array(
                    "id" => $userData['id'],
                    "name" => $userData['name'],
                    "email" => $userData['email'],
                    "role" => $userData['roles'],
                    "institucion" => $userData['institucion_nombre'],
                    "union" => $userData['union_nombre'],
                    "available_apps" => $userApps
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("status" => "error", "message" => "Contraseña incorrecta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("status" => "error", "message" => "El usuario no existe."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Datos incompletos."));
}
?>