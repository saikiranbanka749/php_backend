<?php

// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
//     header("Access-Control-Allow-Origin: *");
//     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
//     header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
//     header("Access-Control-Max-Age: 86400"); 
//     exit(0); 
// }


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];


    $data = json_decode(file_get_contents('php://input'), true);

?>
