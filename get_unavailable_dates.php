<?php
session_start(); // Start the session to identify the logged-in user

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$room_type = $_GET['room_type'];

$query = "SELECT MIN(checkout_date) AS minDate FROM bookings WHERE room_type = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $room_type);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$minDate = $row['minDate'] ?? date("Y-m-d");

echo json_encode(["minDate" => $minDate]);

$stmt->close();
$conn->close();
?>
