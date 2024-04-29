<?php
include "connect.php";
include "Data_accessor.php";

switch ($method) {

  case 'POST':
        $data = json_decode(file_get_contents('php://input'),true);
  //    echo "here";
        $key = "allow_me";
        if($conn)
        {

          $phone  = (count($_POST)>0)?$_POST['phone']:$data['phone'];
          $password = (count($_POST)>0)?$_POST['password']:$data['password'];
          $role = (count($_POST)> 0)?$_POST['role']:$data['role'];
          $query = "SELECT * FROM users WHERE phone = $phone";
          $result = mysqli_query($conn,$query);
          $count = mysqli_num_rows($result);

          if($count>0)
          {
            $row = $result->fetch_assoc();
          //   print_r($row);
          // explode('::', base64_decode($row['password']), 2);
            $pwd = $row["password"];
                list($encrypted, $iv) = explode('::', base64_decode($pwd));
          $r_password =  openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv); 
          if($r_password == $password)
          {
              http_response_code(200);
              echo json_encode("Success ".$row['block_name']);
          
          }
          else
          echo json_encode("Error");
        }
          else{
                echo json_encode("Error");
          }
          
        }
        else{
            echo json_encode("Error");
        }
}
?>