<?php
include "connect.php";
header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');
//echo 'break here';

#echo $method." and the dat is ".$data." only";
switch ($method) {

    case 'POST': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        } else {
            $key = "allow_me";
            $role = (count($_POST) > 0) ? $_POST['role'] : $data['role'];
            $block_name = (count($_POST) > 0) ? $_POST['block_name'] : $data['block_name'];
            $name = (count($_POST) > 0) ? $_POST['name'] : $data['name'];
            $flat_number = (count($_POST) > 0) ? $_POST['flat_number'] : $data['flat_number'];
            $id = "AM" . strtoupper(substr($role, 0, 1)) . "" . strtoupper(substr($block_name, 0, 1)) . "" . substr($flat_number, 0, 1) . rand(1, 10000);
            $age = (count($_POST) > 0) ? $_POST['age'] : $data['age'];
            $gender = (count($_POST) > 0) ? $_POST['gender'] : $data['gender'];
            $phone = (count($_POST) > 0) ? $_POST['phone'] : $data['phone'];
            $alternate_phone = (count($_POST) > 0) ? $_POST['alternate_phone'] : $data['alternate_phone'];

            $pswd = generatePassword();
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($pswd, 'aes-256-cbc', $key, 0, $iv);
            $hashed_password = base64_encode($encrypted . '::' . $iv);






            $created_by = "super_admin";
            $created_date = date('Y-m-d H:i:s');
            $updated_by = "super_admin";
            $updated_date = date('Y-m-d H:i:s');

            if (
                !empty($name) && !empty($flat_number) && !empty($age) && !empty($gender) &&
                !empty($phone) && !empty($alternate_phone) && !empty($block_name) &&!empty($role)
            ) {


             echo   $sql_query = "SELECT `flat_number` from owner where flat_number='$flat_number' and block_name='$block_name'";
                $result = mysqli_query($conn, $sql_query);
                
                if (mysqli_num_rows($result) == 0) { 
                    $query = "INSERT INTO owner (`owner_id`,`name`, `block_name`,`role`,`flat_number`,`age`,`gender`, `phone`, `alternate_phone`,`created_by`,`created_date`,`updated_by`,`update_date`)
                                        VALUES ('$id','$name', '$block_name','$role','$flat_number','$age','$gender','$phone', '$alternate_phone', '$created_by', '$created_date', '$updated_by', '$updated_date')";

                    $query2 = "INSERT INTO users values('$id','$role','$name','$block_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by,', '$created_date', '$updated_by', '$updated_date')";

                    mysqli_query($conn, $query2);

                    if (mysqli_query($conn, $query)) {
                        // Set response code - 201 Created
                        http_response_code(201);
                        echo json_encode(array("message" => "User was created."));
                    } else {
                        // Set response code - 503 Service Unavailable
                        http_response_code(503);
                        echo json_encode(array("message" => mysqli_error($conn)));
                    }
                } else {
                    http_response_code(409);

                }

            } else {
                http_response_code(400);
                echo json_encode(array("message" => "unable to save details because data is not sufficient"));
            }

            break;
        }
    }
    case 'GET': {
        //  $tblusers = array();
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());

        } else {
            $phone = $_GET['phone'];

            $sql = "SELECT * FROM `owner` where block_name in (select block_name from users where phone=$phone); ";
            $result = mysqli_query($conn, $sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $tblusers[] = $row;
                }
                echo json_encode($tblusers);
            } else {
                echo http_response_code(400);
                echo json_encode(array("message" => "Data is not availble"));
            }
            //    print_r($tblusers);


        }
        break;
    }
    case 'DELETE': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());

        } else {

            if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                $id = $_GET['id'];
                if (isset($id)) {
                    $sql = "DELETE FROM Owner WHERE owner_id ='$id'";
                    $result = mysqli_query($conn, $sql);
                    if ($result) {
                        http_response_code(200);
                        echo json_encode(array("Message" => "Entry deleted successfully"));
                    } else {
                        http_response_code(500);
                        echo json_encode(array("Message" => "Failed to delete an entry"));
                    }
                }

            }

        }
        break;
    }
    case 'PUT': {

        #UPDATE owner set `name`='sheb',`flat_number`='25',`age`='52',`gender`='1',`phone`='225558885',`alternate_phone`='112221555' WHERE `owner_id`='Allow61248'; 
        $putData = file_get_contents("php://input");
        parse_str($putData, $dataArray);
        $resultArray = [];
        foreach ($dataArray as $key => $value) {
            $resultArray[$key] = $value;
        }

        print_r($resultArray);
        echo $id = $resultArray['id'];
        $name = $resultArray['name'];
        $flat_number = $resultArray['flat_number'];
        $age = $resultArray['age'];
        $gender = $resultArray['gender'];
        $phone = $resultArray['phone'];
        $alternate_phone = $resultArray['alternate_phone'];
        $created_by = "super_admin";
        $created_date = $resultArray['created_date'];
        $updated_by = "super_admin";
        $updated_date = date('Y-m-d H:i:s');

        if (
            !empty($name) && !empty($flat_number) && !empty($age) && !empty($gender) &&
            !empty($phone) && !empty($alternate_phone) && !(empty($created_date))
        ) {

            $query = "UPDATE owner SET `name`='$name',`flat_number`='$flat_number', `age`='$age',`gender`='$gender',`phone`='$phone',`alternate_phone`='$alternate_phone' WHERE `owner_id`='$id'";
            echo $query;
            # break;     
            if (mysqli_query($conn, $query)) {
                // Set response code - 201 Created
                if (mysqli_affected_rows($conn) > 0) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Owner Updated."));
                } else {
                    http_response_code(200);
                    echo json_encode(array("messagge" => "Please do the update"));
                }
            } else {
                // Set response code - 503 Service Unavailable
                http_response_code(503);
                echo json_encode(array("message" => mysqli_error($conn)));
            }
        } else {
            print_r($resultArray);
            echo $resultArray['gender'];
            http_response_code(400);
            echo json_encode(array("message" => "unable to save details because data is values"));
        }
        break;
    }
}


function generatePassword()
{
    $length = 6;
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $chars[mt_rand(0, $charsLength - 1)];
        $password .= $randomChar;
    }

    return $password;
}
?>