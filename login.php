<?php
// Enable CORS

include "connect.php"; 
include "Data_accessor.php"; 
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
print_r($data);
        if ($conn) {
            $phone = isset($_POST['phone']) ? $_POST['phone'] : $data['phone'];
            $password = isset($_POST['password']) ? $_POST['password'] : $data['password'];
            $role = isset($_POST['role']) ? $_POST['role'] : $data['role'];

            // Map roles to database roles
            $role = strpos($role, 'President') !== false ? "Association president" :
            (strpos($role, 'Owner Login') !== false ? "owner" :
            (strpos($role, 'SecurityGuard Login') !== false ? "security" :
            (strpos($role, 'Tenant\'s Login') !== false ? "tenant" :
            "Super admin")));

            // Use prepared statement to prevent SQL injection
            // echo $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ? AND role = ?");
            // $stmt->bind_param("ss", $phone, $role);
            // $stmt->execute();
            // $result = $stmt->get_result();

            $sql = "SELECT * FROM users WHERE phone='$phone' and role='$role'";
            $result = mysqli_query($conn, $sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_password = $row["password"];
                list($encrypted, $iv) = explode('::', base64_decode($stored_password));
                $decrypted_password = openssl_decrypt($encrypted, 'aes-256-cbc', 'allow_me', 0, $iv);
            #  echo  "d".$decrypted_password .= "   " ."e". $password;
                if ($decrypted_password == $password) {
                    http_response_code(200);
                    echo json_encode("Success " . $row['community_name']); // Assuming 'community_name' is a field in your 'users' table
                } else {
                    echo json_encode("Error: Invalid password");
                }
            } else {
                http_response_code(404);
                echo json_encode("Error: User not found or invalid role");
            }
        } else {
            echo json_encode("Error: Database connection error");
        }
        break;

    default:
        echo json_encode("Error: Method not allowed");
        break;
}
?>
