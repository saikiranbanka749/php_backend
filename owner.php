<?php
include "connect.php";
header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Kolkata');

// Generate a random password function
function generatePassword($length = 6) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $chars[mt_rand(0, $charsLength - 1)];
        $password .= $randomChar;
    }

    return $password;
}

// Function to sanitize inputs
function sanitize($data, $conn) {
    return mysqli_real_escape_string($conn, trim($data));
}

switch ($method) {
    case 'POST': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        } else {
            $key = "allow_me";
            $role = sanitize((count($_POST) > 0) ? $_POST['role'] : $data['role'], $conn);
            $community_name = sanitize((count($_POST) > 0) ? $_POST['community_name'] : $data['community_name'], $conn);
            $apartment_name = sanitize((count($_POST) > 0) ? $_POST['apartment_name'] : $data['apartment_name'], $conn);
            $name = sanitize((count($_POST) > 0) ? $_POST['name'] : $data['name'], $conn);
            $flat_number = sanitize((count($_POST) > 0) ? $_POST['flat_number'] : $data['flat_number'], $conn);
          #  $id = "AM" . strtoupper(substr($role, 0, 1)) . strtoupper(substr($community_name, 0, 1)) . substr($flat_number, 0, 1) . rand(1, 10000);
            $age = sanitize((count($_POST) > 0) ? $_POST['age'] : $data['age'], $conn);
            $gender = sanitize((count($_POST) > 0) ? $_POST['gender'] : $data['gender'], $conn);
            $phone = sanitize((count($_POST) > 0) ? $_POST['phone'] : $data['phone'], $conn);
            $alternate_phone = sanitize((count($_POST) > 0) ? $_POST['alternate_phone'] : $data['alternate_phone'], $conn);
            $id = "AM" . strtoupper(substr($role, 0, 1)) . strtoupper(substr($community_name, 0, 1)) . substr($flat_number, 0, 1) . rand(1, 10000);
            $pswd = "welcome";
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($pswd, 'aes-256-cbc', $key, 0, $iv);
            $hashed_password = base64_encode($encrypted . '::' . $iv);

            $created_by = sanitize((count($_POST) > 0) ? $_POST['role'] : $data['role'], $conn);
            $created_date = date('Y-m-d H:i:s');
            $updated_by = sanitize((count($_POST) > 0) ? $_POST['role'] : $data['role'], $conn);
            $updated_date = date('Y-m-d H:i:s');

            if (!empty($name) && !empty($flat_number) && !empty($age) && !empty($gender) && !empty($phone) && !empty($alternate_phone) && !empty($community_name) && !empty($role)) {

                $sql_query = "SELECT `flat_number` FROM owner WHERE `flat_number`='$flat_number' AND `block_name`='$apartment_name'";
                $result = mysqli_query($conn, $sql_query);
            
                if (mysqli_num_rows($result) == 0) {
            
                    $sql_query = "SELECT * FROM users WHERE `phone`='$phone'";
                    $users_result = mysqli_query($conn, $sql_query);
                    
                    if (mysqli_num_rows($users_result) > 0) {
                        $row = mysqli_fetch_assoc($users_result);
                        $id = $row['user_id'];
                    } else {
                        $id = "AM" . strtoupper(substr($role, 0, 1)) . strtoupper(substr($community_name, 0, 1)) . substr($flat_number, 0, 1) . rand(1, 10000);
                        
                        $query2 = "INSERT INTO users VALUES ('$id','$role','$name','$community_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by', '$created_date', '$updated_by', '$updated_date')";
                        if (!mysqli_query($conn, $query2)) {
                            http_response_code(503); // Service Unavailable
                            echo json_encode(array("message" => "Failed to create user: " . mysqli_error($conn)));
                            exit;
                        }
                    }
            
                    
                    $query = "INSERT INTO owner (`owner_id`, `name`, `community_name`, `block_name`, `role`, `flat_number`, `age`, `gender`, `phone`, `alternate_phone`, `created_by`, `created_date`, `updated_by`, `updated_date`)
                              VALUES ('$id','$name', '$community_name','$apartment_name','$role','$flat_number','$age','$gender','$phone', '$alternate_phone', '$created_by', '$created_date', '$updated_by', '$updated_date')";
            
                    if (mysqli_query($conn, $query)) {
                        http_response_code(201);
                        echo json_encode(array("message" => "User and Owner were created."));
                    } else {
                        http_response_code(503); 
                        echo json_encode(array("message" => "Failed to create owner: " . mysqli_error($conn)));
                    }
            
                } else {
                    http_response_code(409); 
                    echo json_encode(array("message" => "Flat number already exists."));
                }
            } else {
                http_response_code(400); 
                echo json_encode(array("message" => "Incomplete data provided."));
            }
            
            
        }
        break;
    }

    case 'GET': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        } else {
            if (isset($_GET['phone'])) {
                $phone = sanitize($_GET['phone'], $conn);
            $sql = "SELECT * FROM `owner` WHERE `community_name` IN (SELECT `community_name` FROM `users` WHERE `phone`='$phone' )order by flat_number asc";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $tblusers = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tblusers[] = $row;
                    }
                    echo json_encode($tblusers);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Data not available."));
                }
            } else if (isset($_GET['community_name'])) {
                $community_name = sanitize($_GET['community_name'], $conn);
                $sql = "SELECT `apartment_name` FROM `apartments` WHERE `community_name` = '$community_name'";
                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $tblusers = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tblusers[] = $row;
                    }
                    echo json_encode($tblusers);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Data not available."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Invalid request parameters."));
            }
        }
        break;
    }

    case 'DELETE': {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        } else {
            if (isset($_GET['id'])) {
                $id = sanitize($_GET['id'], $conn);
                $sql = "DELETE FROM `owner` WHERE `owner_id`='$id'";
                $sql1 = "DELETE FROM `users` WHERE `user_id`='$id'";

                if (mysqli_query($conn, $sql) && mysqli_query($conn, $sql1)) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Entry deleted successfully."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Failed to delete entry."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "ID parameter is required."));
            }
        }
        break;
    }

    case 'PUT': {
        $putData = file_get_contents("php://input");
        parse_str($putData, $dataArray);

        $id = sanitize($dataArray['id'], $conn);
        $name = sanitize($dataArray['name'], $conn);
        $flat_number = sanitize($dataArray['flat_number'], $conn);
        $age = sanitize($dataArray['age'], $conn);
        $gender = sanitize($dataArray['gender'], $conn);
        $phone = sanitize($dataArray['phone'], $conn);
        $block_name = sanitize($dataArray['block_name'], $conn);
        $alternate_phone = sanitize($dataArray['alternate_phone'], $conn);
        $community_name = sanitize($dataArray['community_name'], $conn);
        $updated_date = date('Y-m-d H:i:s');

        print_r($dataArray);


        if (!empty($name) && !empty($flat_number) && !empty($age) && !empty($gender) && !empty($phone) && !empty($alternate_phone)) {

        

           echo $query1 = "UPDATE users SET `role`='Owner', `name`='$name', `community_name`='$community_name', `phone`='$phone', `alternate_phone`='$alternate_phone', `updated_date`='$updated_date' WHERE `user_id`='$id'";
            if(mysqli_query($conn,$query1))
            {
            $query = "UPDATE owner SET `name`='$name', `block_name`='$block_name', `flat_number`='$flat_number', `age`='$age', `gender`='$gender', `phone`='$phone', `alternate_phone`='$alternate_phone' WHERE `owner_id`='$id'";

            if ( mysqli_query($conn, $query1) && mysqli_query($conn, $query)) {
                if (mysqli_affected_rows($conn) > 0) {
                   echo http_response_code(200);
                    echo json_encode(array("message" => "Owner updated successfully."));
                } else {
                    echo http_response_code(200);
                    echo json_encode(array("message" => "No changes detected."));
                }
            } else {
                echo http_response_code(503);
                echo json_encode(array("message" => mysqli_error($conn)));
            }
        }
        else{
            echo http_response_code(401);
        }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data provided."));
        }
        break;
    }

    default: {
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("message" => "Method not allowed."));
        break;
    }
}
?>
