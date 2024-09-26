<?php
include "connect.php";

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
date_default_timezone_set('Asia/Kolkata');

$key = "allow_me";

if (!$conn) {
    http_response_code(500);
    echo json_encode(array("error" => mysqli_connect_error()));
    die();
}

switch ($method) {
    case "POST":
        handlePostRequest($conn, $key);
        break;

    case "GET":
        handleGetRequest($conn);
        break;

    case "PUT":
        handlePutRequest($conn);
        break;

    case "DELETE":
        handleDeleteRequest($conn);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("error" => "Method not allowed."));
        break;
}

mysqli_close($conn);

function handlePostRequest($conn, $key) {
   var_dump((file_get_contents('php://input')));
    echo file_get_contents('php://input');

   $rawdata =  file_get_contents('php://input');
 #  echo $rawdata;
    $data = json_decode($rawdata, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(array("message" => "Invalid JSON format."));
        return;
    }

  #  print_r($data);
    if (empty($data['block_name']) || empty($data['community_name']) || empty($data['name']) ||
        empty($data['owner_phone']) || empty($data['age']) || empty($data['gender']) 
        || empty($data['phone']) || empty($data['alternate_phone'])
         || empty($data['flat_number'])) {
        echo http_response_code();
        echo json_encode(array("message" => "All fields are required."));
        return;
    }

    $role = 'tenant';
    $block_name = $data['block_name'];
    $community_name = $data['community_name'];
    $tenant_name = $data['name'];
    $owner_phone = $data['owner_phone'];
    $age = $data['age'];
    $gender = $data['gender'];
    $phone = $data['phone'];
    $alternate_phone = $data['alternate_phone'];
    $flat_number = $data['flat_number'];
    $created_by = $data['created_by'];
    $created_date = date('Y-m-d H:i:s');
    $updated_by = $data['updated_by'];
    $updated_date = date('Y-m-d H:i:s');
    $pswd = "welcome";
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($pswd, 'aes-256-cbc', $key, 0, $iv);
    $hashed_password = base64_encode($encrypted . '::' . $iv);

    $owner_phone = mysqli_real_escape_string($conn, $owner_phone);
    $phone = mysqli_real_escape_string($conn, $phone);
    $alternate_phone = mysqli_real_escape_string($conn, $alternate_phone);

    $query = "SELECT owner_id, block_name, flat_number FROM owner WHERE phone = '$owner_phone' OR alternate_phone = '$owner_phone' and community_name='$community_name'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $owner_id = $row['owner_id'];
        $block_name1 = $row['block_name'];
        $flat_number1 = $row['flat_number'];
            $tenant_id = "AM" . strtoupper(substr($role, 0, 1)) . strtoupper(substr($block_name1, 0, 1)) . substr($flat_number1, 0, 1) . rand(1, 10000);

            $query = "SELECT phone, alternate_phone FROM tenant WHERE phone = '$phone' OR alternate_phone = '$alternate_phone'";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) == 0) {
              
               $sql = "select *from owner where flat_number='$flat_number' and block_name='$block_name' and community_name='$community_name' and phone='$owner_phone'";

           //  echo  $sql = "select *from owner where owner_id in(select owner_id from tenant where flat_number= and block_name= and community_name= and owner_id in(select owner_id from owner where phone=))";

               $result = mysqli_query($conn,$sql);
               if(mysqli_num_rows($result)>0)
                {
         echo       $query = "INSERT INTO users values('$tenant_id','$role','$tenant_name','$community_name','$phone','$alternate_phone','$hashed_password','$pswd',1,'$encrypted','$created_by,', '$created_date', '$updated_by', '$updated_date')";
                
                $query2 = "INSERT INTO tenant (tenant_id, owner_id, block_name, flat_number, community_name, name, age, gender, phone, alternate_phone, created_date, created_by, updated_date, updated_by) VALUES ('$tenant_id', '$owner_id', '$block_name', '$flat_number', '$community_name', '$tenant_name', '$age', '$gender', '$phone', '$alternate_phone', '$created_date', '$created_by', '$updated_date', '$updated_by')";

                
                if (mysqli_query($conn, $query) && mysqli_query($conn, $query2)) {
                        http_response_code(201);
                        echo json_encode(array("message" => "Data inserted successfully."));
                    } else {
                        http_response_code(500);
                        echo json_encode(array("message" => "Error inserting data."));
                    }
                }
                else{
                    http_response_code(401);
                }

            } else {
                http_response_code(409);
                echo json_encode(array("message" => "Tenant with the provided phone number already exists."));
            }
     
    } else {
        http_response_code(404);
        echo json_encode(array("error" => "Owner does not exist."));
    }
}

function handleGetRequest($conn) {
    if (isset($_GET['phone_number'])) {
        $phone_number = mysqli_real_escape_string($conn, $_GET['phone_number']);
        $role = $_GET['role'];

        if ($role == 'Super Admin') {
            $query = "SELECT * FROM tenant WHERE community_name IN (SELECT community_name FROM users WHERE phone = '$phone_number')";
        } elseif ($role != 'AssociationPresident') {
            $query = "SELECT * FROM tenant WHERE owner_id IN (SELECT user_id FROM users WHERE phone = '$phone_number')";
        } else {
            $query = "SELECT * FROM tenant WHERE community_name IN (SELECT community_name FROM users WHERE phone = '$phone_number')";
        }

        $result = mysqli_query($conn, $query);

        if ($result) {
            $tblusers = [];

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $tblusers[] = $row;
                }

                foreach ($tblusers as &$userData) {
                    $ownerIds = $userData['owner_id'];
                    $query1 = "SELECT phone FROM owner WHERE owner_id = '$ownerIds'";
                    $result1 = mysqli_query($conn, $query1);

                    if ($result1 && mysqli_num_rows($result1) > 0) {
                        $row = mysqli_fetch_assoc($result1);
                        $userData['owner_phone_number'] = $row['phone'];
                    }
                }

                echo json_encode($tblusers);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No results found."));
            }
        } else {
            http_response_code(500);
            echo json_encode(array("error" => "Query execution failed."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Phone number not provided."));
    }
}

function handlePutRequest($conn) {
    parse_str(file_get_contents("php://input"), $data);

    $data = json_decode(key($data), true);

    if (!isset($data['tenant_id']) || !isset($data['owner_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Tenant ID and Owner ID are required."));
        return;
    }

    $name = $data['name'];
    $age = $data['age'];
    $block_name = $data['block_name'];
    $gender = $data['gender'];
    $tenant_id = $data['tenant_id'];
    $phone = $data['phone'];
    $alternate_phone = $data['alternate_phone'];
    $created_date = $data['created_date'];
    $owner_id = $data['owner_id'];
    $updated_date = date('Y-m-d H:i:s');

    $query = "UPDATE tenant SET name = '$name', age = '$age', block_name = '$block_name', gender = '$gender', phone = '$phone', alternate_phone = '$alternate_phone', created_date = '$created_date', updated_date = '$updated_date' WHERE owner_id = '$owner_id' AND tenant_id = '$tenant_id'";
    if (mysqli_query($conn, $query)) {
        if (mysqli_affected_rows($conn) > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Tenant updated successfully."));
        } else {
            http_response_code(304); // Not Modified
            echo json_encode(array("message" => "No changes were made."));
        }
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Error updating tenant: " . mysqli_error($conn)));
    }
}

function handleDeleteRequest($conn) {
    if (isset($_GET['id'])) {
        $id = mysqli_real_escape_string($conn, $_GET['id']);

        $query = "DELETE FROM tenant WHERE tenant_id = '$id'";
        mysqli_query($conn, $query);
      echo  $sql = "SELECT * FROM owner WHERE owner_id IN (SELECT owner_id FROM tenant WHERE tenant_id = $id)";

        $result = mysqli_query($conn,$sql);

        if($result)
        {
            if(mysqli_num_rows($result)>0)
            {
                echo http_response_code(401);
            }
            else{
                
                $query1 = "DELETE FROM users WHERE user_id = '$id'";
                if (mysqli_query($conn, $query1)) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Data deleted successfully."));
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Entry not found."));
                }
            }
        }


       
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "ID not provided."));
    }
}
?>
