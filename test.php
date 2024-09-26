<?php
// Get the JSON data
$data = json_decode(file_get_contents("php://input"));

// Check if the SendImage field is set
if (isset($data->SendImage)) {
    $base64String = $data->SendImage;

    // Extract the base64 part from the data URI
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
        $base64String = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        // Decode the base64 string
        $base64String = base64_decode($base64String);
        
        // Check if the decoding was successful
        if ($base64String === false) {
            die('Base64 decode failed');
        }

        // Create a unique file name
        $fileName = uniqid() . '.' . $type;
        $filePath = 'uploads/' . $fileName; // Make sure this directory exists

        // Save the file
        if (file_put_contents($filePath, $base64String)) {
            echo json_encode(['status' => 'success', 'file' => $fileName]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not save the file.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image data.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No image provided.']);
}
?>
