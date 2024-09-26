<?php
include('connect.php');
include('Data_accessor.php');
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
#print_r($data);
    if (json_last_error() === JSON_ERROR_NONE) {
        $timePeriod = isset($data['time_period']) ? $data['time_period'] : null;
        $year = isset($data['year']) ? $data['year'] : null;
        $month = isset($data['month']) ? $data['month'] : null;
        $date = isset($data['date']) ? $data['date'] : null;
        $startTime = isset($data['start_time']) ? $data['start_time'] : [];
        $endTime = isset($data['end_time']) ? $data['end_time'] : [];

        
        $startDate = isset($startTime['start_date']) ? $startTime['start_date'] : null;
        $endDate = isset($endTime['end_date']) ? $endTime['end_date'] : null;
        $startHour = isset($startTime['hour']) ? $startTime['hour'] : null;
        $startMinute = isset($startTime['minute']) ? $startTime['minute'] : null;
        $endHour = isset($endTime['hour']) ? $endTime['hour'] : null;
        $endMinute = isset($endTime['minute']) ? $endTime['minute'] : null;

        $sql = "SELECT * FROM visits WHERE 1=1";
        
        if ($timePeriod === 'Yearly' && $year) {
            $sql .= " AND YEAR(date_time) = '$year'";
        } 
         elseif ($timePeriod === 'Monthly')
         {
             $sql .= " AND MONTH(date_time) = '$month'";
         }
        elseif ($timePeriod === 'Monthly' && $month && $year) {
            $sql .= " AND MONTH(date_time) = '$month' AND YEAR(date_time) = '$year'";
        } elseif ($timePeriod === 'Custom') {
            if ($date && $year) {
                $startDateTime = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($startDate, 2, '0', STR_PAD_LEFT) . " $startHour:$startMinute:00";
                $endDateTime = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($endDate, 2, '0', STR_PAD_LEFT) . " $endHour:$endMinute:00";

                $sql .= " AND date_time BETWEEN '$startDateTime' AND '$endDateTime'";
            }
        }
 
       # echo $sql; die();

        $result = mysqli_query($conn, $sql);

        if ($result) {
            $response = [];
            if(mysqli_num_rows($result)>0){
                while ($row = $result->fetch_assoc()) {
                    $response[] = $row;
                }
                echo json_encode($response);
            }
            else{
                http_response_code(403);
            }

        } else {
            http_response_code(500); 
            echo json_encode(['error' => 'Failed to generate report.']);
        }
    } else {
        http_response_code(400); 
        echo json_encode(['error' => 'Invalid JSON format.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
