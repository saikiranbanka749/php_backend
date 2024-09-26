<?php

include("connect.php");
include("Data_accessor.php");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
    //    print_r($_POST);

        $otp = $_POST['verification_code'] ?? '';
        $visitorContactNumber = $_POST['visitor_mobile_number'] ?? '';
        $date_time = $_POST['date_time'] ?? '';

        // if (empty($otp) || empty($visitorContactNumber) || empty($date_time)) {
        //     echo json_encode(array('status' => 'fail', 'message' => 'Missing parameters'));
        //     exit;
        // }

        $sql = "SELECT `OTP` FROM visits WHERE OTP = '$otp' AND visitor_mobile_number = '$visitorContactNumber' AND date_time = '$date_time'";
        $result = mysqli_query($conn,$sql);

        if ($result && $result->num_rows > 0) {
            
            $sql = "update visits set status='completed' WHERE OTP = '$otp' AND visitor_mobile_number = '$visitorContactNumber' AND date_time = '$date_time' ";
            $result = mysqli_query($conn,$sql);
            if($result)
            {
                echo json_encode(array('status' => 'success'));
            }
            else{
                echo json_encode(array("status"=>"something went wrong"));
            }
            

        } else {
            echo json_encode(array('status' => 'fail', 'message' => 'OTP verification failed'));
        }
        break;
    
    default:
        echo json_encode(array('status' => 'fail', 'message' => 'Invalid request method'));
        break;
}

$conn->close();

?>
