<?php

include "connect.php";
header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
date_default_timezone_set('Asia/Kolkata');
#echo $method;
$data = json_decode(file_get_contents('php://input'), true);
$key = "allow_me";
switch ($method) {
    case 'GET': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());

        } else {
            $phone = $_GET['phone_number'];
           $sql = "SELECT * FROM `securityguard` where block_name in (SELECT block_name from users WHERE  phone = '$phone')";
            $result = mysqli_query($conn, $sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $tblusers[] = $row;
                }
                // echo   $shifts = $tblusers['shift'];
                echo json_encode($tblusers);
            } else {
                echo http_response_code(404);
                echo json_encode(array("message" => "Data is not availble"));
            }
            //    print_r($tblusers);


        }

        break;
    }
    case 'POST': {

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        } else {
            // $data = json_decode(file_get_contents("php://input"));
            //  echo gettype($data);


            if (isset($_POST)) {
                
                $name = (count($_POST) > 0) ? $_POST['name'] : $data['name'];
                $age = (count($_POST) > 0) ? $_POST['age'] : $data['age'];
                $address = (count($_POST) > 0) ? $_POST['address'] : $data['address'];
                $block_name = (count($_POST) > 0) ? $_POST['block_name'] : $data['block_name'];
                $service_start_date = (count($_POST) > 0) ? $_POST['service_start_date'] : $data['service_starting_date'];
                $service_end_date = (count($_POST) > 0) ? $_POST['service_end_date'] : $data['service_ending_date'];
                $active = (count($_POST) > 0) ? $_POST['active'] : $data['active'];
                $ashift = (count($_POST) > 0) ? $_POST['shiftA'] : $data['shiftA'];
                $bshift = (count($_POST) > 0) ? $_POST['shiftB'] : $data['shiftB'];
                $cshift = (count($_POST) > 0) ? $_POST['shiftC'] : $data['shiftC'];
                $phone = (count($_POST) > 0) ? $_POST['mobileNumber'] : $data['mobileNumber'];
                $alternate_phone = (count($_POST) > 0) ? $_POST['alternate_phone_number'] : $data['alternate_phone_number'];
                $role= "Security";
                $created_by = "super_admin";
                $created_date = date('Y-m-d H:i:s');
                $updated_by = "super_admin";
                $updated_date = date('Y-m-d H:i:s');
                $security_guard_id = "AM" . strtoupper(substr($role, 0, 1)) . "" . strtoupper(substr($block_name, 0, 1)) . rand(1, 10000);
                $shift = array();
                array_push($shift, $ashift);
                array_push($shift, $bshift);
                array_push($shift, $cshift);
                $shifts = implode(',', $shift);

                $shifts = str_replace(',', ' ', $shifts);
                print_r(json_encode($shifts));

                $pswd = generatePassword();
                $iv = random_bytes(16);
                $encrypted = openssl_encrypt($pswd, 'aes-256-cbc', $key, 0, $iv);
                $hashed_password = base64_encode($encrypted . '::' . $iv);
    
   # echo "data reached to here";

echo $security_guard_id."      ".$name."    ".$age."    ".$address."    ".$service_start_date."         ".$service_end_date."   ".$active." ".$shifts."     ".$phone."      ".$alternate_phone;

                if (
                    !empty($security_guard_id) && !empty($name) && !empty($age) && !empty($address) && !empty($service_start_date)
                    && !empty($service_end_date) && !empty($active) && !empty($shifts) && !empty($phone) && !empty($alternate_phone)
                ) {
                    echo "data in if condition";
                    // SQL query to insert data into the database
                    $query = "INSERT INTO `securityguard` (`security_guard_id`,`name`,`block_name`,`age`,`address`, `service_start_date`, `service_end_date`,`active`,`shift`,`phone`,`alternate_phone`,`created_by`,`created_date`,`updated_by`,`updated_date`)
                                                VALUES ('$security_guard_id','$name','$block_name','$age','$address', '$service_start_date', '$service_end_date','$active', '$shifts','$phone','$alternate_phone', '$created_by', '$created_date', '$updated_by', '$updated_date')";

                  //  echo $query;
                    $query2 = "INSERT INTO users values('$security_guard_id','$role','$name','$block_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by,', '$created_date', '$updated_by', '$updated_date')";
                    if (mysqli_query($conn, $query) && mysqli_query($conn,$query2)) {
                        // Set response code - 201 Created
                        http_response_code(201);
                        echo json_encode(array("message" => "User was created."));
                    } else {
                        // Set response code - 503 Service Unavailable
                        http_response_code(503);
                        echo json_encode(array("message" => mysqli_error($conn)));
                    }
                }
            } else {
                echo "data is in else condition";
                print_r($data);
                // Set response code - 400 Bad Request
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
            }
        }

        break;
    }

    case 'DELETE': {

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());

        } else {

            if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {


                $id = $_GET['id'];
                echo $id;

                if (!empty($id)) {
                    $sql = "DELETE FROM `securityguard` WHERE security_guard_id ='$id'";
                    $result = mysqli_query($conn, $sql);
                    echo $result;
                    if ($result) {
                        if (mysqli_affected_rows($conn) > 0) {
                            http_response_code(200);
                            echo json_encode(array("Message" => "Entry deleted successfully"));
                        } else {

                            http_response_code(200);
                            echo json_encode(array("message" => "Records not avaible"));
                        }
                    } else {
                        echo mysqli_error($conn);
                        http_response_code(500);
                        echo json_encode(array("Message" => "Failed to delete an entry"));
                    }
                } else {
                    echo mysqli_error($conn);
                    echo json_encode(array("Message" => "Data not recieved"));
                }

            }

        }
        break;
    }
    case 'PUT': {


        $id = $data['security_guard_id'];
        $name = $data['name'];
        $age = $data['age'];
        $address = $data['address'];
        $block_name = $data['block_name'];
        $service_start_date = $data['service_starting_date'];
        $service_end_date = $data['service_ending_date'];
        $active = $data['active'];
        $ashift = (count($_POST) > 0) ? $_POST['shiftA'] : $data['shiftA'];
        $bshift = (count($_POST) > 0) ? $_POST['shiftB'] : $data['shiftB'];
        $cshift = (count($_POST) > 0) ? $_POST['shiftC'] : $data['shiftC'];
        $phone = $data['mobileNumber'];
        $alternate_phone = $data['alternate_phone_number'];
        $created_by = "super_admin";
        $created_date = $data['created_date'];
        $updated_by = "super_admin";
        $updated_date = date('Y-m-d H:i:s');
        $shift = array();
        array_push($shift, $ashift);
        array_push($shift, $bshift);
        array_push($shift, $cshift);


        $shifts = implode(',', $shift);

        //     echo $shifts;

        $shifts = str_replace(',', ' ', $shifts);



        if (
            !empty($id) && !empty($name) && !empty($age) && !empty($address) && !empty($service_start_date) && !empty($service_end_date) && !empty($active)
            && !empty($shifts) && !empty($phone) && !empty($alternate_phone) && !(empty($created_date))
        ) {

            $query = "UPDATE securityguard SET `name`='$name', `block_name`='$block_name',`age`='$age',`address`='$address',`service_start_date`='$service_start_date',`service_end_date`='$service_end_date', `active`='$active',`shift`='$shifts',`phone`='$phone',`alternate_phone`='$alternate_phone',`created_by`= '$created_by',`created_date`='$created_date', `updated_by`='$updated_by',`updated_date`='$updated_date' WHERE `security_guard_id`='$id';";
            echo $query;
            #break;     
            if (mysqli_query($conn, $query)) {
                // Set response code - 201 Created
                if (mysqli_affected_rows($conn) > 0) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Updated successfully."));
                } else {
                    http_response_code(200);
                    echo json_encode(array("messagge" => "Updatation Failed"));
                }
            } else {
                // Set response code - 503 Service Unavailable
                http_response_code(503);
                echo json_encode(array("message" => mysqli_error($conn)));
            }
        } else {

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