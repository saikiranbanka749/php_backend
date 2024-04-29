<?php

$secretKey = "Allow_me";
    
function encryptPassword($password, $key) {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
    $combined = $encrypted . '::' . $iv;
    return base64_encode($combined);
}
// function decryptPassword($key) {
//     list($encrypted, $iv) = explode('::', base64_decode("RG1hUkY4M1k0Tm0zVDI2KytNZUJvdz09Ojq/3ZuVFuQ+FKpX65Pm7cY5"), 2);
//     return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv); 
// }
// $password = "8074665974";

function decryptPassword($encryptedPasswordd, $key) {
    $decoded = base64_decode($encryptedPasswordd);
    list($encryptedData, $iv) = explode('::', $decoded);
    $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    
    if ($decrypted === false) {
        $error = openssl_error_string();
        return array('error' => $error);
    }
    
    return array('password' => $decrypted);
}

// Example usage
$key = $secretKey; // Replace with your actual encryption key
$encryptedPasswordd =  $encryptedPassword; // Replace with your actual encrypted password



 $encryptedPassword = encryptPassword("578670",$secretKey);
echo "Encrypted Password: " . $encryptedPassword . "\n";

$decryptedPassword = decryptPassword($encryptedPassword, $key);

echo "Decrypted Password: " . $decryptedPassword;

print_r($decryptedPassword);

// $decryptedPassword = decryptPassword($secretKey);
// echo "Decrypted Password: " . $decryptedPassword . "\n";

// if($password == $decryptedPassword)
//     echo "both are same";
// else    
//     echo "both are not same";


//$userInput = 'password123';



?>