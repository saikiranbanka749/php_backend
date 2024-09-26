<?php
include "connect.php";
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];
date_default_timezone_set('Asia/Kolkata');

if (!$conn) {
    echo json_encode(array("error" => mysqli_connect_error()));
    die();
} else {
    switch ($method) {
        case "GET":
            if (isset($_GET['screen'])) {
                $screen = $_GET['screen'];

                if (isset($_GET['community_name']) && isset($_GET['selectedblock'])) {
                    $community_name = mysqli_real_escape_string($conn, $_GET['community_name']);
                    $selectedblock = mysqli_real_escape_string($conn, $_GET['selectedblock']);

                    // Fetch the number of plots
                    $sql = "SELECT * FROM `apartments` WHERE `community_name`='$community_name' AND `apartment_name`='$selectedblock'";
                    $result = mysqli_query($conn, $sql);

                    if ($result) {
                        if (mysqli_num_rows($result) > 0) {
                            $row2 = mysqli_fetch_assoc($result);
                            $no_of_plots = $row2['no_of_plots'];
                            $available_flats = [];

                            if ($screen == 'owner') {
                                // Fetch occupied flats
                                $query = "SELECT `flat_number` FROM `owner` WHERE `community_name`='$community_name' AND `block_name`='$selectedblock'";
                                $result1 = mysqli_query($conn, $query);

                                if ($result1) {
                                    $occupied_flats = [];
                                    while ($row = mysqli_fetch_assoc($result1)) {
                                        $occupied_flats[] = $row['flat_number'];
                                    }

                                    // Determine available flats
                                    for ($count = 1; $count <= $no_of_plots; $count++) {
                                        if (!in_array($count, $occupied_flats)) {
                                            $available_flats[] = $count;
                                        }
                                    }
                                } else {
                                    echo json_encode(array("error" => "Failed to execute query for owners: " . mysqli_error($conn)));
                                    exit;
                                }
                            } elseif ($screen == 'tenant') {
                                // Fetch occupied flats
                                $query = "SELECT `flat_number` FROM `owner` WHERE `community_name`='$community_name' AND `block_name`='$selectedblock'";
                                $result1 = mysqli_query($conn, $query);

                                if ($result1) {
                                    $occupied_flats = [];
                                    while ($row = mysqli_fetch_assoc($result1)) {
                                        $occupied_flats[] = $row['flat_number'];
                                    }

                                    // Determine available flats
                                    for ($count = 1; $count <= $no_of_plots; $count++) {
                                        if (in_array($count, $occupied_flats)) {
                                            $available_flats[] = $count;
                                        }
                                    }
                                } else {
                                    echo json_encode(array("error" => "Failed to execute query for owners: " . mysqli_error($conn)));
                                    exit;
                                }
                            } else {
                                echo json_encode(array("error" => "Invalid screen parameter."));
                                exit;
                            }
                            $tbldata = array();
                            $tbldata['available_flats'] = $available_flats;
                            echo json_encode($tbldata);
                        } else {
                            echo json_encode(array("available_flats" => []));
                        }
                    } else {
                        echo json_encode(array("error" => "Failed to execute query for apartments: " . mysqli_error($conn)));
                    }
                } elseif ($screen == 'president') {
                    $sql = "SELECT * FROM apartments WHERE community_name IN (SELECT community_name FROM users WHERE phone='" . mysqli_real_escape_string($conn, $_GET['phoneNumber']) . "')";
                    $result = mysqli_query($conn, $sql);
                    $data = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[$row['apartment_name']] = $row; 
                     //   print_r($row);
                    }
                  
                    $sql = "SELECT * FROM owner WHERE community_name IN (SELECT community_name FROM users WHERE phone='" . mysqli_real_escape_string($conn, $_GET['phoneNumber']) . "')";
                    $owner_result = mysqli_query($conn, $sql);

                    $owner_apartments = [];
                    while ($owner_row = mysqli_fetch_assoc($owner_result)) {
                        $owner_apartments[$owner_row['block_name']] = isset($owner_apartments[$owner_row['block_name']]) ? $owner_apartments[$owner_row['block_name']] + 1 : 1;
                  
                    }

                    foreach ($data as $apartment_name => &$apartment) {
                        if (isset($owner_apartments[$apartment_name])) {
                            $apartment['filled_flats'] = $owner_apartments[$apartment_name];
                            $apartment['available_flats'] = $apartment['no_of_plots'] - $apartment['filled_flats'];
                        } else {
                            $apartment['available_flats'] = $apartment['no_of_plots'];
                            $apartment['filled_flats'] = 0;
                        }
                    }

                    $response = [
                        'data' => array_values($data) // Convert associative array to indexed array
                    ];
                    echo json_encode($response);
                } else {
                    echo json_encode(array("error" => "Invalid screen parameter."));
                }
            } else {
                echo json_encode(array("error" => "Missing screen parameter."));
            }
            break;

        default:
            echo json_encode(array("error" => "Invalid request method."));
            break;
    }
}

mysqli_close($conn);
?>
