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
        // Generamos un token manual simple sin librerías
        $token = bin2hex(random_bytes(16)); 

        echo json_encode([
            "status" => "success",
            "token" => $token,
            "user" => [
                "id" => $userData['id'],
                "name" => $userData['name'],
                "role" => $userData['roles'],
                "available_apps" => $user->getUserApps($userData['id'])
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Acceso denegado"]);
    }
}
?>