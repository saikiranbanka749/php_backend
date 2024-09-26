<?php
include "connect.php";
include "Data_accessor.php";

switch ($method) {
    case 'POST': {
        print_r($_POST);
        
        $block_name =(count($_POST) > 0) ? $_POST['block_name'] : $data['block_name'];
        $community_id = "AM".rand(1,10000)."".substr($block_name,5);
        $president_name = (count($_POST) > 0) ? $_POST['president_name'] : $data['president_name'];
        $president_phone_number = (count($_POST) > 0) ? $_POST['president_phone'] : $data['president_phone'];
        $alternate_phone =(count($_POST) > 0) ? $_POST['alternate_phone'] : $data['alternate_phone'];
        $block_located = (count($_POST) > 0) ? $_POST['block_located'] : $data['block_located'];
        $no_of_blocks = (count($_POST) > 0) ? $_POST['no_of_blocks'] : $data['no_of_blocks'];
        $created_by = "super_admin";
        $created_date = date('Y-m-d H:i:s');
        $updated_by = "super_admin";
        $updated_date = date('Y-m-d H:i:s');
        $password = "welcome";
        $key="allow_me";
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
        $hashed_password = base64_encode($encrypted . '::' . $iv);
        $userId='AMAP'.rand(1,10000);   
        $role='Association president';

     echo   $query = "INSERT INTO `communities` values('$community_id','$block_name','$president_name','$president_phone_number','$alternate_phone','$block_located','$no_of_blocks')";
      #  echo "<br>";
       echo $query2 = "INSERT INTO users (user_id,role,name, community_name,phone, alternate_phone,password,pswd,active,salt_text,created_by,created_date,updated_by,updated_date)   VALUES ('$userId','$role','$president_name','$block_name', '$president_phone_number', '$alternate_phone', '$hashed_password','$password', 1, '$encrypted', '$created_by', '$created_date', '$updated_by', '$updated_date')";

     //   $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
        if (mysqli_query($conn, $query) && mysqli_query($conn,$query2)) {
            http_response_code(201);
            echo json_encode(array("message" => "data inserted successfully"));
        } else {
            echo json_encode(array("message" => "Data insertion failed"));
        }
        break;
    }
    case "GET": {
     echo   $sql = "SELECT *FROM `communities`";
      echo  $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        if (mysqli_num_rows($result) > 0) {
            while ($row = $result->fetch_assoc()) {
                $tblusers[] = $row;
            }
            echo mysqli_num_rows($result);
            echo json_encode($tblusers);
        } else {
            echo json_encode(array("message" => "Data is not avaible"));
        }

        break;
    }
    // case "PUT": {
    //     $block_id = $data['block_id'];
    //     $block_name = $data['block_name'];
    //     $president_name = $data['president_name'];
    //     $phone_number = $data['phone'];

    //     $created_by = "super_admin";
    //     $created_date = $data['created_date'];
    //     $updated_by = "super_admin";
    //     $updated_date = date('Y-m-d H:i:s');

    //     $query = 'UPDATE `communities` SET `block_id`=' . $block_id . ',`Association_name`=' . $block_name . ',`president_name`=' . $president_name . ',
    //         `phone_number`=' . $phone_number;
    // }
}


?>