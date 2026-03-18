<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';
include_once '../models/Message.php';
include_once '../models/App.php';

$database = new Database();
$db = $database->getConnection();
$messageModel = new Message($db);
$appModel = new App($db);

$data = json_decode(file_get_contents("php://input"));

// Validación de entrada (Punto 3)
if(!empty($data->user_id) && !empty($data->app_id) && !empty($data->titulo) && !empty($data->mensaje)) {
    
    $appDetails = $appModel->getById($data->app_id);

    if($appDetails) {
        $fields = [
            'app_id' => $appDetails['onesignal_app_id'],
            'included_segments' => ['All'],
            'headings' => ["en" => $data->titulo, "es" => $data->titulo],
            'contents' => ["en" => $data->mensaje, "es" => $data->mensaje]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $appDetails['onesignal_api_key']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $resObj = json_decode($response);
        curl_close($ch);

        if(isset($resObj->id)) {
            // Guardar reporte en BD
            $messageModel->create($data->user_id, $data->app_id, $data->titulo, $data->mensaje);
            echo json_encode(["status" => "success", "onesignal_id" => $resObj->id]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error en OneSignal"]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}
?>