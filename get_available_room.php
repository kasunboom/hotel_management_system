<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_type'])) {
    $room_type = $_POST['room_type'];
    
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "hotel_management";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
        exit();
    }
    
    // Query for an available room of the given type; also fetch its price per day.
    $query = "SELECT room_number, price FROM rooms WHERE room_type = ? AND availability = 'Available' LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit();
    }
    $stmt->bind_param("s", $room_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "room_number" => $row['room_number'],
            "price" => $row['price']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No available room found"]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
