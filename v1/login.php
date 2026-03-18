<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    $userData = $user->findByEmail($data->email);

    if($userData && password_verify($data->password, $userData['password'])) {
        // Generamos un token aleatorio manual (Punto 1)
        $token = bin2hex(random_bytes(16)); 

        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso",
            "token" => $token,
            "user" => [
                "id" => $userData['id'],
                "name" => $userData['name'],
                "email" => $userData['email'],
                "role" => $userData['roles'],
                "institucion" => $userData['institucion_nombre'],
                "available_apps" => $user->getUserApps($userData['id'])
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}