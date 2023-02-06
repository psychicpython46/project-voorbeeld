<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: GET");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'DB.php';

$output = [];
$required = ['id', 'api-key'];

if(count(array_intersect_key(array_flip($required), $_GET)) === count($required)) {
    $database = new Database();
    $db = $database->getConnection();

    $queryResult = $database->approveFestival($_GET['api-key'], $_GET['id']);

    $success = $queryResult ['success'];
    $error = $queryResult ['error'];
    $info = $queryResult ['info'];

    $db->close();   
            
    $output = [
        'success' => $success,
        'error' => $error,
        'info' => $info
    ];
} else {
    $output = [
        'success' => false,
        'error' => "Niet alle vereisten parameters zijn gegeven.",
        'info' => ['error-code' => '1', 'parameters-expected' => $required, 'parameters-recieved' => array_keys($_GET)]
    ];
}

echo json_encode($output);
?>