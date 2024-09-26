<?php

include ("connect.php");
include ("Data_accessor.php");
date_default_timezone_set('Asia/Kolkata');

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");

if (!$conn) {
    die(json_encode(array("message" => "Connection failed: " . mysqli_connect_error())));
} else {
    $phone_number = isset($_GET['phone_number']) ? mysqli_real_escape_string($conn, $_GET['phone_number']) : '';

    if (!empty($phone_number)) {
        $sql = "SELECT * FROM owner WHERE phone = '$phone_number'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $tbldata = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $tbldata[] = $row;
            }
            echo json_encode($tbldata);
        } else {
            http_response_code(404); 
            echo json_encode(array("message" => "No data available"));
        }
    } else {
        http_response_code(400); 
        echo json_encode(array("message" => "Phone number is required"));
    }
}
?>
