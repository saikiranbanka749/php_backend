<?php

include "connect.php";
$key = "allow_me";
header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

  case 'POST':
        {
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        else{
            $data = json_decode(file_get_contents("php://input"));


        if (!empty($data->name) && !empty($data->phone)  && !empty($data->alterante_phone)  && !empty($data->password)) {
            $role = mysqli_real_escape_string($conn, $data->role);
            $name = mysqli_real_escape_string($conn, $data->name);
            $phone  = mysqli_real_escape_string($conn, $data->phone);
            $alterante_phone = mysqli_real_escape_string($conn, $data->alterante_phone);


            $password = mysqli_real_escape_string($conn, $data->password);
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv); 
            $hashed_password = base64_encode($encrypted . '::' . $iv);

            $created_by = "super_admin";
            $created_date = date('Y-m-d H:i:s');
            $updated_by = "super_admin";
            $updated_date = date('Y-m-d H:i:s');

            // $sql = "SELECT `phone`,`alternate_phone` from users where `phone`='$phone' or `alternate_phone`='$alternate_phone'";

            // $result = $conn->query($sql);
            // if ($result->num_rows > 0) {
            //     http_response_code(201);
            // }

    
            $query = "INSERT INTO users (role,name, phone, alterante_phone,password,pswd,active,salt_text,created_by,created_date,updated_by,updated_date)
                                    VALUES ('$role','$name', '$phone', '$alterante_phone', '$hashed_password',$password, 1, '$encrypted', '$created_by', '$created_date', '$updated_by', '$updated_date')";

            if (mysqli_query($conn, $query)) {
                http_response_code(201);
                echo json_encode(array("message" => "User was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => mysqli_error($conn)));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
        }
        }
        break;
    }
    case "GET":
        {
             $query =  "SELECT *FROM   `users` where `block_name` IS NOT NULL and `block_name`!='' and role='owner' ";
                $result = mysqli_query($conn, $query);

              mysqli_num_rows($result);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                            $tblusers[] = $row;
                    }  
                }
                else{
                    echo "no data availble";
                }
                 // print_r($row);

       echo json_encode($tblusers);
          break;      
        

}
}
?>