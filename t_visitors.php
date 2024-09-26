<?php

include ("connect.php");
include ("Data_accessor.php");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');


switch ($method) {
    case 'POST': {

        $data = json_decode(file_get_contents('php://input'), true);
        print_r($data);
        $community_name = $data['community_name'];
        $apartment_name = $data['apartment_name'];

        $id = "AMV" . substr($community_name, 0, 3) . "" . substr($apartment_name, 0, 3);
        $name = $data['name'];
        $gender = $data['gender'];
        $purpose = $data['purpose'];
        $flat_number = $data['flat_number'];
        $date_time = $data['date_time'];
        $tenant_phone = $data['phone'];
        $inTime = $data['inTime'];
        $outTime = $data['outTime'];
        $visitorContactNumber = $data['visitorContactNumber'];
        $visitorAdarCardNumber = $data['visitorAdarCardNumber'];
        $teenantMobileNumber = $data['teenantMobileNumber'];
        $role = $data['role'];
        $block_name = $data['apartment_name'];
        $otp = generatePassword();
        $sql = "INSERT INTO `visits` VALUES ('$id','$name','$gender','$purpose','$flat_number','$block_name','$date_time','$tenant_phone','$inTime','$outTime','pending','$visitorAdarCardNumber','$visitorContactNumber','$date_time','$role','$date_time','$role','   ')";

        if (mysqli_query($conn, $sql)) {
            echo http_response_code(200);
            echo json_encode(array("message" => "Data inserted successfully"));
        } else {
            echo json_encode(array("message" => "Data inserytion failedd"));
        }
        break;
    }
    case 'GET': {
        if (isset($_GET['phone']) && !isset($_GET['status'])) {
            // Fetch data from 'tenant' table based on the phone number
            $phone = mysqli_real_escape_string($conn, $_GET['phone']);
           $sql = "SELECT * FROM tenant WHERE phone = '$phone'";
            $result = mysqli_query($conn, $sql);
    
            if ($result && mysqli_num_rows($result) > 0) {
                $tbldata = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $tbldata[] = $row;
                }
                http_response_code(200); // OK
                echo json_encode($tbldata);
            } else {
                http_response_code(204); // No Content
                echo json_encode(array("message" => "No data found for the given phone number."));
            }
        } else if (isset($_GET["status"]) && isset($_GET['phone'])) {
            // Fetch data from 'visits' table based on status and phone number
            $status = mysqli_real_escape_string($conn, $_GET["status"]);
            $phone = mysqli_real_escape_string($conn, $_GET["phone"]);
    
            $sql = $status != "All" ?
                "SELECT * FROM visits WHERE status='$status' AND tenant_mobile_number = '$phone'" :
                "SELECT * FROM visits WHERE tenant_mobile_number = '$phone'";
   # break;
            $result = mysqli_query($conn, $sql);
    
            if ($result && mysqli_num_rows($result) > 0) {
                $tblusers = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $tblusers[] = $row;
                }
                http_response_code(200); // OK
                echo json_encode($tblusers);
            } else {
                http_response_code(204); // No Content
                echo json_encode(array("message" => "No data found for the given criteria."));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("message" => "Invalid request parameters."));
        }
        break;
    }
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("message" => "Method not allowed."));
        break;
    

}

?>