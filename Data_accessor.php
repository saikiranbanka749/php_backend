<?php

header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

$data = json_decode(file_get_contents('php://input'),true);



?>