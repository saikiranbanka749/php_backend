<?php
include "connect.php"; // Adjust this according to your connection file

$key = "allow_me"; // Encryption key for password encryption

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Handle POST request to create a new user
        {
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->name) && !empty($data->phone) && !empty($data->alternate_phone) && !empty($data->password)) {
                // Sanitize and escape input data
                $role = mysqli_real_escape_string($conn, $data->role);
                $name = mysqli_real_escape_string($conn, $data->name);
                $phone = mysqli_real_escape_string($conn, $data->phone);
                $alternate_phone = mysqli_real_escape_string($conn, $data->alternate_phone);
                $password = mysqli_real_escape_string($conn, $data->password);

                // Encrypt password
                $iv = random_bytes(16);
                $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
                $hashed_password = base64_encode($encrypted . '::' . $iv);

                // Set other parameters for user creation
                $created_by = "super_admin";
                $created_date = date('Y-m-d H:i:s');
                $updated_by = "super_admin";
                $updated_date = date('Y-m-d H:i:s');
                $user_id = 'AMAP' . rand(1, 10000);

                // Insert query
                $query = "INSERT INTO users (user_id, role, name, phone, alternate_phone, password, pswd, active, salt_text, created_by, created_date, updated_by, updated_date)
                                    VALUES ('$user_id', '$role', '$name', '$phone', '$alternate_phone', '$hashed_password', '$password', 1, '$encrypted', '$created_by', '$created_date', '$updated_by', '$updated_date')";

                if (mysqli_query($conn, $query)) {
                    http_response_code(201);
                    echo json_encode(array("message" => "User was created."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Failed to create user: " . mysqli_error($conn)));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
            }
            break;
        }

    case 'GET':
        // Handle GET request to retrieve users
        {
            $role = isset($_GET['role']) && $_GET['role'] == 'admin' ? $_GET['role'] :
            (isset($_GET['role']) && $_GET['role'] == 'Association president' ? $_GET['role'] :
            (isset($_GET['role']) && $_GET['role'] == 'owner' ? $_GET['role'] : 'tenant'));
            $phone = isset($_GET['phoneNumber']) ? mysqli_real_escape_string($conn, $_GET['phoneNumber']) : null;

            //previous query
           # $query = "SELECT * FROM `users` WHERE `Association_name` IS NOT NULL AND `Association_name` != '' AND role = '$role'"; 
         #  $query = "SELECT * FROM `users` WHERE `community_name` IS NOT NULL AND `community_name` != '' AND role = '$role' AND phone = '$phone'";

           #Association_name replaced with community_name
            if (empty($phone)) {
                $query = "SELECT * FROM `users` WHERE `community_name` IS NOT NULL AND `community_name` != '' and role = 'Association president'";
            } else {
                $query = "SELECT * FROM `users` WHERE `community_name` IS NOT NULL AND `community_name` != '' AND role = 'Association president' AND phone = '$phone'";
            }
#echo $query;
            $result = mysqli_query($conn, $query);

            if ($result) {
                $tblusers = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $tblusers[] = $row;
                }
                http_response_code(200);
                echo json_encode($tblusers);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No users found."));
            }
            break;
        }

    default:
        // Handle unsupported methods
        {
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
        }
}

mysqli_close($conn); // Close the database connection
?>
