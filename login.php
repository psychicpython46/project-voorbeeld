<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: GET");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'DB.php';

$output = [];
$required = ['username', 'password'];

if(count(array_intersect_key(array_flip($required), $_POST)) === count($required)) {
    $database = new Database();
    $db = $database->getConnection();

    $queryResult = $database->login($_POST['username'], $_POST['password']);

    $success = $queryResult ['success'];
    $error = $queryResult ['error'];
    $info = $queryResult ['info'];
    $apikey = $queryResult ['key'];
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
        'info' => ['method-expected' => 'POST', 'error-code' => '1', 'parameters-expected' => $required, 'parameters-recieved' => array_keys($_POST)]
    ];
}

echo json_encode($output);
?>