<?php

include("connect.php");
include("Data_accessor.php");
switch ($method) {

   
    case 'POST': {
    
        $otp = $_POST['verfication_code'];
        $visitorContactNumber = $_POST['visitor_mobile_number'];
        $date_time = $_POST['date_time'];
        $sql = "SELECT `OTP` FROM VISITS WHERE OTP = '" . $_POST['verfication_code'] . "' and visitor_mobile_number = '" . $visitorContactNumber . "' and date_time = '" . $date_time . "'";
       
        $result = mysqli_query($conn, $sql);
    // echo   mysqli_num_rows($result);   
                if (mysqli_num_rows($result) > 0) {
            echo json_encode(array('status'=> 'success'));
        }
        else {
            echo json_encode(array('status'=> 'fail'));

        }
        break;
    }

}



?>