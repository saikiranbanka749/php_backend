<?php

include ("connect.php");
include ("Data_accessor.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
$method = $_SERVER['REQUEST_METHOD'];
date_default_timezone_set('Asia/Kolkata');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST': {
        print_r($data);
        $visitor_name = $_POST['name'] ?? $data['name'];
        $visitorType = $_POST['visitorType'] ?? $data['visitorType'];
        $gender = $_POST['gender'] ?? $data['gender'];
        $purpose = $_POST['purpose'] ?? $data['purpose'];
        $flat_number = $_POST['flat_number'] ?? $data['flat_number'];
        $date_time = $_POST['date_time'] ?? $data['date_time'];
        $tenantMobileNumber = $_POST['tenantMobileNumber'] ?? $data['tenantMobileNumber'];
        $inTime = $_POST['inTime'] ?? $data['inTime'];
        $outTime = $_POST['outTime'] ?? $data['outTime'];
        $visitorContactNumber = $_POST['visitorContactNumber'] ?? $data['visitorContactNumber'];
        $block_name = $_POST['block_name'] ?? $data['block_name'];
    
            $currentDateTime = new DateTime();
            $dateTimeInput = new DateTime($date_time);
            $inTimeInput = new DateTime($inTime);

            $fiveMinutesLater = clone $currentDateTime;
            $fiveMinutesLater->modify('+5 minutes');


            // if ($dateTimeInput >=  $currentDateTime && $dateTimeInput <= $fiveMinutesLater) {
            //     http_response_code(400);
            //     echo json_encode(array("message" => "Date and Time must be at least 5 minutes from the current date and time."));
            //     exit;
            // }
            if ($dateTimeInput->format('Y-m-d') == $currentDateTime->format('Y-m-d') && $inTimeInput > $currentDateTime) {
                http_response_code(400);
            
                echo json_encode(array("message" => $errorMessage));
                exit;
            }
            

            $fiveMinutesFromNow = clone $currentDateTime;
            $fiveMinutesFromNow->modify('+5 minutes');

            if ($inTimeInput > $fiveMinutesFromNow) {
                http_response_code(400);
                echo json_encode(array("message" => "In Time must be within 5 minutes from the current time."));
                exit;
            }

           
    
        $id = "AM" . "V" . substr($block_name, 3) . rand(1, 10000);
        $created_by = "security_guard";
        $created_date = date('Y-m-d H:i:s');
        $updated_by = "security_guard";
        $updated_date = date('Y-m-d H:i:s');
        $otp = generatePassword();
        $image_path = null;
    
        if ($visitorType !== 'Bill Distributor' && isset($data['visitor_image'])) {
            $base64String = $_POST['visitor_image']?? $data['visitor_image']; 
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
        $base64String = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]); 

                $base64String = base64_decode($base64String);
                
                if ($base64String === false) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Base64 decode failed.']);
                    exit;
                }

                $fileName = uniqid('visitor_', true) . '.' . $type;
                $filePath = 'uploads/' . $fileName; 
                if (file_put_contents($filePath, $base64String) !== false) {
                    $image_path = $filePath; 
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Could not save the file.']);
                    exit;
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid image data.']);
                exit;
            }
        }
        else{
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Image not avialble.']);
                exit;
        }
   # break;
        // Data validation
        if (
            !empty($visitor_name) && !empty($gender) && !empty($purpose) && !empty($flat_number) && !empty($date_time) && !empty($tenantMobileNumber) &&
            ($visitorType === 'Bill Distributor' || !empty($visitorContactNumber))
        ) {
            
            $tenantQuery = "SELECT * FROM users WHERE phone = '$tenantMobileNumber' AND role = 'tenant'";
            $result = mysqli_query($conn, $tenantQuery);
    
            if (mysqli_num_rows($result) > 0) {
               
                if ($visitorType === 'Bill Distributor') {
                    $query = "INSERT INTO visits (id, visitor_name, visitor_type, gender, purpose, flat_number, block_name, date_time, tenant_mobile_number, status, created_date, created_by, updated_date, updated_by, otp, visitor_image_path) 
                              VALUES ('$id', '$visitor_name', '$visitorType', '$gender', '$purpose', '$flat_number', '$block_name', '$date_time', '$tenantMobileNumber', 'completed', '$created_date', '$created_by', '$updated_date', '$updated_by', '$otp', '$image_path')";
                } else {
                    $query = "INSERT INTO visits (id, visitor_name, visitor_type, gender, purpose, flat_number, block_name, date_time, tenant_mobile_number, in_time, out_time, status, visitor_mobile_number, created_date, created_by, updated_date, updated_by, otp, visitor_image_path) 
                              VALUES ('$id', '$visitor_name', '$visitorType', '$gender', '$purpose', '$flat_number', '$block_name', '$date_time', '$tenantMobileNumber', '$inTime', '$outTime', 'pending', '$visitorContactNumber', '$created_date', '$created_by', '$updated_date', '$updated_by', '$otp', '$image_path')";
                }
    
                if (mysqli_query($conn, $query)) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Visitor was created."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Database error: " . mysqli_error($conn)));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Tenant not available with that number."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create visitor. Data is incomplete."));
        }
        break;
    }
    
    case 'GET': {
        if (isset($_GET["status"])) {
            $status = mysqli_real_escape_string($conn, $_GET["status"]);
            $sql = $status != "All" ? "SELECT * FROM visits WHERE status='$status' ORDER BY created_date DESC" : "SELECT * FROM visits ORDER BY created_date DESC";
            $result = mysqli_query($conn, $sql);

            if ($result->num_rows > 0) {
                $tblusers = [];
                while ($row = $result->fetch_assoc()) {
                    $tblusers[] = $row;
                }
                http_response_code(200);
                echo json_encode($tblusers);
            } else {
                http_response_code(204);
                echo json_encode(array("message" => "Data is not available"));
            }
        } else if (isset($_GET['phone_number']) && isset($_GET['community_name'])) {
            $phone = mysqli_real_escape_string($conn, $_GET['phone_number']);
            $c_name = mysqli_real_escape_string($conn, $_GET['community_name']);
            $sql = "SELECT * FROM tenant WHERE phone = ? AND community_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $phone, $c_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $tblusers = $result->fetch_assoc();
                http_response_code(200);
                echo json_encode($tblusers);
            } else {
                http_response_code(204);
                echo json_encode(array("message" => "Data is not available"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid request."));
        }
        break;
    }
}

function generatePassword() {
    $length = 5;
    $chars = '0123456789';
    $password = '';
    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $randomChar = $chars[mt_rand(0, $charsLength - 1)];
        $password .= $randomChar;
    }

    return $password;
}
?>
