<?php

include "connect.php"; 
$key = "allow_me"; 
$method = $_SERVER['REQUEST_METHOD'];

include "Data_accessor.php";

switch($method){

    case 'POST':{

        $apartment_name = (count($_POST) > 0) ? $_POST['apartment_name'] : $data['apartment_name'];
        $community_name = (count($_POST) > 0) ? $_POST['community_name'] : $data['community_name'];
        $apartment_id = "AM".substr($apartment_name,3)."".substr($community_name,6).rand(1,1000);
        $noOfFlats = (count($_POST) > 0) ? $_POST['noOfFlats'] : $data['noOfFlats'];
        $noOfFloors = (count($_POST) > 0) ? $_POST['noOfFloors'] : $data['noOfFloors'];
        $flatsPerFloor = (count($_POST) > 0) ? $_POST['flatsPerFloor'] : $data['flatsPerFloor'];
        // $soldFats = (count($_POST) > 0) ? $_POST['soldFats'] : $data['soldFats'];
        // $unSoldFlats = (count($_POST) > 0) ? $_POST['unSoldFlats'] : $data['unSoldFlats'];
        // $bookedFlats = (count($_POST) > 0) ? $_POST['bookedFlats'] : $data['bookedFlats'];
        // $flatType = (count($_POST) > 0) ? $_POST['flatType'] : $data['flatType'];
        // $furnishedType = (count($_POST) > 0) ? $_POST['furnishedType'] : $data['furnishedType'];

      $sql = "SELECT `community_id`,`Association_name` from  `communities` WHERE `Association_name` ='$community_name' ";
        if (mysqli_query($conn, $sql)) 
            {
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    print("here");
                    $row = $result->fetch_assoc();
        
                
                        print_r($row);
                        if($row['Association_name'] == $community_name)
                        {
                            $communityId = $row['community_id'];
                           $sql1 = "INSERT INTO  `apartments` values('$apartment_id','$apartment_name','$communityId','$community_name',$noOfFlats,$noOfFloors,$flatsPerFloor)";
                            if(mysqli_query($conn,$sql1))
                            {
                                echo "success";
                                http_response_code(201);
                                echo json_encode(array("message" => "data inserted successfully"));
                            }
                            else{
                                echo mysqli_error($conn);
                            }
                        }
                        else{
                            echo  http_response_code(404);
                            echo json_encode(array("message" => "Community not found"));
                        }
                    
            }   
        else{
            echo "out of error";
            echo mysqli_error($conn);
        }


       }
       else{
        echo mysqli_error($conn);
       }
       break;
}

case 'GET':{

    $phone_number = $_GET['phone_number'];

    $sql = "SELECT * FROM `apartments` where community_name in(select community_name from users where phone=$phone_number)";
     $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        if (mysqli_num_rows($result) > 0) {
            while ($row = $result->fetch_assoc()) {
                $tblusers[] = $row;
            }
          #  echo mysqli_num_rows($result);
            echo json_encode($tblusers);
        } else {
            echo json_encode(array("message" => "Data is not avaible"));
        }

    break;
}
}


?>