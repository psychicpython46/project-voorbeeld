<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: GET");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'DB.php';

$output = [];
$required = [];

if(true) {
    $database = new Database();
    $db = $database->getConnection();

    if(!empty($_GET['approved'])){
        $queryResult = $database->getFestivals(1);
    } else {
        $queryResult = $database->getFestivals(0);
    }

    $success = $queryResult ['success'];
    $error = $queryResult ['error'];
    $info = $queryResult ['info'];
    $data = $queryResult ['data'];

    $db->close();   
            
    $output = [
        'success' => $success,
        'error' => $error,
        'info' => $info,
        'data' => $data
    ];
}

echo json_encode($output);
?>