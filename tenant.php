<?php
include "connect.php";
header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');

$key = "allow_me";
if (!$conn) {
    echo json_encode(array("error" => mysqli_connect_error()));
    die("Connection failed: " . mysqli_connect_error());

} else {
    switch ($method) {
        case "POST": {
            $role = (count($_POST) > 0) ? $_POST['role'] : $data['role'];
            $block_name = (count($_POST) > 0) ? $_POST['block_name'] : $data['block_name'];
            $tenant_name = $data["name"];
            $owner_phone = $data["owner_phone"];
            //    $flat_number = $data["flat_number"];
            $age = $data['age'];
            $gender = $data['gender'];
            $phone = $data['phone'];
         echo   $alternate_phone = $data['alternate_phone'];

            $created_by = "super_admin";
            $created_date = date('Y-m-d H:i:s');
            $updated_by = "super_admin";
            $updated_date = date('Y-m-d H:i:s');
            $pswd = generatePassword();
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($pswd, 'aes-256-cbc', $key, 0, $iv);
            $hashed_password = base64_encode($encrypted . '::' . $iv);

            $query1 = "SELECT `owner_id`,`block_name`,`flat_number`  FROM owner where phone = $owner_phone or alternate_phone = '$alternate_phone'";
            $result = mysqli_query($conn, $query1);
            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $row = $result->fetch_assoc();
                    print_r($row);
                    $owner_id = $row['owner_id'];
                    $block_name = $row['block_name'];
                    $flat_number = $row['flat_number'];
                    $tenant_id = "AM" . strtoupper(substr($role, 0, 1)) . "" . strtoupper(substr($block_name, 0, 1)) . "" . substr($flat_number, 0, 1) . rand(1, 10000);
                  echo  $query2 = "INSERT INTO users values('$tenant_id','$role','$tenant_name','$block_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by,', '$created_date', '$updated_by', '$updated_date')";
                  //     break;      
                    if (!empty($tenant_id) && !empty($owner_phone) && !empty($tenant_name) && !empty($age) && !empty($gender) && !empty($phone) && !empty($alternate_phone)) {                   
                          echo  $query = "INSERT INTO `tenant` values('$tenant_id','$owner_id','$block_name','$tenant_name','$age','$gender','$phone','$alternate_phone',
                      '$created_date','$created_by','$updated_date','$updated_by') ";

                        $query2 = "INSERT INTO users values('$tenant_id','$role','$tenant_name','$block_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by,', '$created_date', '$updated_by', '$updated_date')";
                        //   break;      
                        $result2 = mysqli_query($conn, $query2);
                        echo $result = mysqli_query($conn, $query);
                        if ($result && $result2) {
                            http_response_code(201);
                            echo json_encode(array("message" => "Data inserted Successfully"));
                        } else {
                            http_response_code(400);
                            echo json_encode(array("message" => mysqli_error($conn)));
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(array("message" => "Please enter all the fields(error comes from)   "));
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array('error' => "owner does not exist"));
                }
            } else {
                mysqli_error($conn);
            }


            break;
        }
        case "GET": {
            $phone_number = $_GET['phone_number'];

            $query = "SELECT * FROM tenant where block_name in (select block_name from users where phone=$phone_number)";
            $result = mysqli_query($conn, $query);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $tblusers[] = $row;
                }
                $count = 0;
                //       print_r($tblusers);
                foreach ($tblusers as $userData) {
                    $ownerIds = $userData['owner_id'];

                    //   print_r($ownerIds);
                    $query1 = "SELECT `phone` FROM `owner` WHERE `owner_id` =  '$ownerIds'";

                    //     echo $query1;

                    $result1 = mysqli_query($conn, $query1);

                    if ($result1) {
                        if ($result1->num_rows > 0) {
                            while ($row = $result1->fetch_assoc()) {
                                $newKey = "owner_phone_number";
                                $newValue = $row['phone'];
                                //  $tblusers = array_merge(array($newKey => $newValue), $tblusers);
                                $tblusers[$count]['owner_phone_number'] = $newValue;
                            }
                        } else {
                            mysqli_error($conn);
                        }

                    } else {
                        echo mysqli_error($conn);
                    }
                    $count++;
                }
                //   print_r($tblusers);
                echo json_encode($tblusers);
            } else {
                echo http_response_code(400);
                echo json_encode(array("message" => "Data is not availble"));
            }
            break;
        }
        case "PUT": {
            $name = $data['name'];
            $age = $data['age'];
            $gender = $data['gender'];
            $tenant_id = $data['tenant_id'];
            $phone = $data['phone'];
            $alternate_phone = $data['alternate_phone'];
            $created_date = $data['created_date'];
            $owner_id = $data['owner_id'];
            $updated_date = date('Y-m-d H:i:s');

            echo $sql = "UPDATE TENANT SET `name`='$name',`age`='$age',`gender`='$gender',`phone`='$phone',`alternate_phone`='$alternate_phone',`created_date`='$created_date',`updated_date`='$updated_date' WHERE  `owner_id`='$owner_id';";
            if (mysqli_query($conn, $sql)) {
                // Set response code - 201 Created 
                if (mysqli_affected_rows($conn) > 0) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Owner Updated."));
                } else {
                    echo mysqli_error($conn);
                    echo json_encode(array("messagge" => "Please do the update"));
                }
            } else {
                // Set response code - 503 Service Unavailable
                http_response_code(503);
                echo json_encode(array("message" => mysqli_error($conn)));
            }


            break;
        }
        case "DELETE": {

            echo $id = $_GET['id'];
            echo $sql = "DELETE FROM `tenant` WHERE tenant_id = '$id';";

            if (mysqli_query($conn, $sql)) {
                if (mysqli_affected_rows($conn) > 0) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Data Deleted successfull"));
                } else {
                    http_response_code(0);
                    echo json_encode(array("message" => "Entry not found"));
                }
            } else {
                echo json_decode(mysqli_error($conn));
            }

            break;
        }
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