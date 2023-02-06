<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: GET");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'DB.php';

$output = [];
$required = ['key'];

if(count(array_intersect_key(array_flip($required), $_GET)) === count($required)) {
    $database = new Database();
    $db = $database->getConnection();

    $queryResult = $database->checkKey($_GET['key']);

    $success = $queryResult ['success'];
    $error = $queryResult ['error'];
    $info = $queryResult ['info'];
    $apikey = $_GET['key'];
    $role = $queryResult ['role'];

    $db->close();   
            
    $output = [
        'success' => $success,
        'error' => $error,
        'info' => $info,
        'apikey' => $apikey,
        'role' => $role
    ];
} else {
    $output = [
        'success' => false,
        'error' => "Niet alle vereisten parameters zijn gegeven.",
        'info' => ['method-expected' => 'GET', 'error-code' => '1', 'parameters-expected' => $required, 'parameters-recieved' => array_keys($_POST)]
    ];
}

echo json_encode($output);
?>