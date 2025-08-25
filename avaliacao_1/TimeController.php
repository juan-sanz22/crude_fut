<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Time.php';

$database = new Database();
$db = $database->getConnection();
$time = new Time($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $time->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $times_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($times_arr, $row);
            }
            echo json_encode($times_arr);
        } else {
            echo json_encode(array("message" => "Nenhum time encontrado."));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        $time->nome = $data->nome;
        $time->cidade = $data->cidade;

        if ($time->create()) {
            echo json_encode(array("message" => "Time criado."));
        } else {
            echo json_encode(array("message" => "Não foi possível criar o time."));
        }
        break;
}
?>