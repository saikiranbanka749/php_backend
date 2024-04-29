<?php

include ("connect.php");
include ("Data_accessor.php");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');


switch ($method) {
    case 'POST': {
        break;
    }
    case 'GET': {
        if (isset($_GET["status"]) && isset($_GET['phone'])) {

            $status = $_GET["status"];
            $phone = $_GET["phone"];
          echo  $sql = $status != "All" ? "SElECT *FROM visits WHERE status='$status' and teenant_phone = " . $phone : "SELECT *FROM visits where teenant_phone=$phone ";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {

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

        }
    }
}
?>