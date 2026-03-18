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

if(!empty($data->user_id) && !empty($data->app_id) && !empty($data->titulo) && !empty($data->mensaje)) {
    
    // 1. Obtener credenciales de la App desde la DB
    $appDetails = $appModel->getById($data->app_id);

    if($appDetails) {
        $app_id_os = $appDetails['onesignal_app_id'];
        $api_key_os = $appDetails['onesignal_api_key'];

        // 2. Configurar la llamada a OneSignal
        $content = array("en" => $data->mensaje, "es" => $data->mensaje);
        $headings = array("en" => $data->titulo, "es" => $data->titulo);

        $fields = array(
            'app_id' => $app_id_os,
            'included_segments' => array('All'), // Envía a todos los suscritos
            'data' => array("foo" => "bar"),
            'headings' => $headings,
            'contents' => $content
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $api_key_os
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $resObj = json_decode($response);

        // 3. Si se envió (o OneSignal respondió), guardar en reportes_mensajes
        if(isset($resObj->id)) {
            $messageModel->create($data->user_id, $data->app_id, $data->titulo, $data->mensaje);
            
            http_response_code(200);
            echo json_encode(array("status" => "success", "onesignal_id" => $resObj->id));
        } else {
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Error en OneSignal", "details" => $resObj));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Aplicación no encontrada."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>