<?php

include ("connect.php");
include ("Data_accessor.php");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
 $data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    $data = json_decode(file_get_contents("php://input"));



    switch ($method) {
        case 'POST': {
            print_r($_POST);
            $visitor_name = (count($_POST) > 0) ? $_POST['name'] : $data['name'];
            $gender = (count($_POST) > 0) ? $_POST['gender'] : $data['gender'];
            $purpose = (count($_POST) > 0) ? $_POST['purpose'] : $data['gender'];
            //$image = mysqli_real_escape_string($conn, $data->image);
            $flat_number = (count($_POST) > 0) ? $_POST['flat_number'] : $data['flat_number'];
            $date_time = (count($_POST) > 0) ? $_POST['date_time'] : $data['date_time'];
            $teenantMobileNumber = (count($_POST) > 0) ? $_POST['teenantMobileNumber'] : $data['teenantMobileNumber'];
            $inTime = (count($_POST) > 0) ? $_POST['inTime'] : $data['inTime'];
            $outTime = (count($_POST) > 0) ? $_POST['outTime'] : $data['outTime'];
            $visitorContactNumber = (count($_POST) > 0) ? $_POST['visitorContactNumber'] : $data['visitorContactNumber'];
            $visitorAdarCardNumber = (count($_POST) > 0) ? $_POST['visitorAdarCardNumber'] : $data['visitorAdarCardNumber'];
            $id = "AM" . "V" . rand(1, 10000);

            $created_by = "super_admin";
            $created_date = date('Y-m-d H:i:s');
            $updated_by = "super_admin";
            $updated_date = date('Y-m-d H:i:s');
            $otp = generatePassword();
            if (
                !empty($visitor_name) && !(empty($gender)) && !(empty($purpose)) && !(empty($flat_number)) && !(empty($date_time)) && !(empty($teenantMobileNumber))
                && !empty($inTime) && !empty($outTime) && !empty($visitorContactNumber) && !empty($visitorAdarCardNumber)
            ) {

                $sql = "SELECT *FROM users where phone =".$teenantMobileNumber." and  role='teenantMobileNumber'";
               $result = mysqli_query($conn,$sql);
                    if(mysqli_num_rows($result)>0)
                    {
                        $query = "INSERT INTO visits values('$id','$visitor_name','$gender','$purpose','$flat_number','$date_time','$teenantMobileNumber','$inTime','$outTime','pending','$visitorAdarCardNumber'
                        ,'$visitorContactNumber', '$created_date','$created_by','$updated_date','$updated_by','$otp')";
                    
                        if (mysqli_query($conn, $query)) {
                            // Set response code - 201 Created
                            http_response_code(201);
                            echo json_encode(array("message" => "vistor was created."));
                        } else {
                            // Set response code - 503 Service Unavailable
                            http_response_code(503);
                            echo json_encode(array("message" => mysqli_error($conn)));
                        }
                    }
                    else{
                        http_response_code(400);
                        echo json_encode(array("message" => "Teenant availble with that number"));

                    }
        
               
            } else {
                // Set response code - 400 Bad Request
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
            }

            break;
        }
        case "GET": {
            if (isset($_GET["status"])) {
                $status = mysqli_real_escape_string($conn, $_GET["status"]);
                $sql = $status!="All"?"SElECT *FROM visits WHERE status='$status'":"SELECT *FROM visits";
                $result = mysqli_query($conn, $sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tblusers[] = $row;
                    }
                  //  echo http_response_code(200);
                    //   echo json_encode(array("message"=> "Success"));
                    echo json_encode($tblusers);
                } else {
                    echo http_response_code(400);
                    echo json_encode(array("message" => "Data is not availble"));
                }


            } else {
                echo "status not printed";
            }
            break;
        }
    }
}

function generatePassword()
{
    $length = 5;
    $chars = '0123456789';
    $password = '';
    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $chars[mt_rand(0, $charsLength - 1)];
        $password .= $randomChar;
    }

    return $password;
}
?>