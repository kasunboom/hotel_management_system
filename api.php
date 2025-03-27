<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle GET request to fetch events
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM bookings";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        echo json_encode($events);
    } else {
        echo json_encode([]);
    }
}

// Handle POST request to submit a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $event_name = $data['event_name'];
    $event_date = $data['event_date'];
    $event_time = $data['event_time'];
    $num_guests = $data['num_guests'];
    $event_type = $data['event_type'];
    $full_name = $data['full_name'];
    $email_address = $data['email_address'];
    $phone_number = $data['phone_number'];
    $billing_address = $data['billing_address'];
    $special_requests = $data['special_requests'];

    $sql = "INSERT INTO bookings (
        event_name, event_date, event_time, num_guests, event_type, 
        full_name, email_address, phone_number, billing_address, special_requests
    ) VALUES (
        '$event_name', '$event_date', '$event_time', $num_guests, '$event_type', 
        '$full_name', '$email_address', '$phone_number', '$billing_address', '$special_requests'
    )";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Booking submitted successfully!"]);
    } else {
        echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
    }
}

$conn->close();
?>