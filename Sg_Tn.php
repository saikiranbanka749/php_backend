<?php
include "connect.php";

header('Access-Control-Allow-Origin:*');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
#echo $method;
$data = json_decode(file_get_contents('php://input'),true);

switch ($method)
{
    case 'POST':{
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        else{
            $visitor_id = "Allow".rand(1,100000);
            $visitor_name  = (count($_POST)>0)?$_POST['visitor_name']:$data['visitor_name'];
            $gender = (count($_POST)>0)?$_POST['gender']:$data['gender'];
            $pupose  = (count($_POST)>0)?$_POST['pupose']:$data['pupose'];
            $flat_number = (count($_POST)>0)?$_POST['flat_number']:$data['flat_number'];
            $phone  = (count($_POST)>0)?$_POST['phone']:$data['phone'];
            $alternate_phone = (count($_POST)>0)?$_POST['alternate_phone']:$data['alternate_phone'];
            $created_by = "super_admin";
            $created_date = date('Y-m-d H:i:s');
            $updated_by = "super_admin";
            $updated_date = date('Y-m-d H:i:s');
        }

        break;

    }
    case 'GET':{
        break;
        
    }
    case 'DELETE':{
        break;
        
    }
    case 'PUT':{
        break;
        
    }

}

?>