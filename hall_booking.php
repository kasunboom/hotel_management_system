<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to make a booking.'); window.location.href='login.php';</script>";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS `hall_bookings` (
    `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `id` INT NOT NULL,
    `booking_date` DATE NOT NULL,
    `function_date` DATE NOT NULL,
    `function_time` TIME NOT NULL,
    `function_type` VARCHAR(100) NOT NULL,
    `guest_name` VARCHAR(100) NOT NULL,
    `guest_phone` VARCHAR(20) NOT NULL,
    `guest_email` VARCHAR(100) NOT NULL,
    `guest_count` INT NOT NULL,
    `special_requirements` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (!$conn->query($create_table_sql)) {
    die("Error creating table: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $booking_date = $_POST['booking_date'];
        $function_date = $_POST['function_date'];
        $function_time = $_POST['function_time'];
        $function_type = $_POST['function_type'];
        $full_name = $_POST['full_name'];
        $mobile_number = $_POST['mobile_number'];
        $email_address = $_POST['email_address'];
        $num_of_guests = $_POST['num_of_guests'];
        $special_requirements = isset($_POST['special_requirements']) ? $_POST['special_requirements'] : '';
        $user_id = $_SESSION['user_id'];

        // Check if the selected function_date and function_time are already booked
        $check_query = "SELECT * FROM hall_bookings WHERE function_date = ? AND function_time = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $function_date, $function_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // If a booking exists, show an alert and stop the booking process
            echo "<script>alert('The selected time slot is already booked. Please choose another time.'); window.location.href='hall_booking.php';</script>";
            exit();
        }

        // Insert booking if no conflict
        $query = "INSERT INTO hall_bookings (
            id, 
            booking_date, 
            function_date, 
            function_time, 
            function_type,
            guest_name, 
            guest_phone, 
            guest_email, 
            guest_count, 
            special_requirements
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param(
            "isssssssss", 
            $user_id, 
            $booking_date, 
            $function_date, 
            $function_time, 
            $function_type,
            $full_name, 
            $mobile_number, 
            $email_address, 
            $num_of_guests, 
            $special_requirements
        );

        if ($stmt->execute()) {
            echo "<script>alert('Hall Booking Successful!'); window.location.href='hall_booking.php';</script>";
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Booking Failed! Error: " . $e->getMessage() . "');</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Booking - Gardenia Hotel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/hall_booking_style.css">
</head>
<body>
    <?php include 'include/navbar.php';?>
    <section id="booking">
        <div class="container">
            <h2 class="text-center">Hall Booking</h2>
            <form method="POST" action="" class="booking-form">
    <div class="mb-3">
        <label for="booking_date" class="form-label">Booking Date</label>
        <input type="date" class="form-control" id="booking_date" name="booking_date" placeholder="Select booking date" required>
    </div>
    <div class="mb-3">
        <label for="function_date" class="form-label">Function Date</label>
        <input type="date" class="form-control" id="function_date" name="function_date" placeholder="Select function date" required>
    </div>
    <div class="mb-3">
        <label for="function_time" class="form-label">Function Time</label>
        <select class="form-control" id="function_time" name="function_time" required>
            <option value="">Select Time Slot</option>
            <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
            <option value="5:00 PM - 11:30 PM">5:00 PM - 11:30 PM</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="function_type" class="form-label">Function Type</label>
        <select class="form-control" id="function_type" name="function_type" required>
            <option value="wedding">Wedding Reception</option>
            <option value="conference">Conference</option>
            <option value="birthday">Birthday Party</option>
            <option value="corporate">Corporate Event</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" required>
    </div>
    <div class="mb-3">
        <label for="mobile_number" class="form-label">Mobile Number</label>
        <input type="tel" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter your mobile number" required>
    </div>
    <div class="mb-3">
        <label for="email_address" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email_address" name="email_address" placeholder="Enter your email address" required>
    </div>
    <div class="mb-3">
        <label for="num_of_guests" class="form-label">Number of Guests</label>
        <input type="number" class="form-control" id="num_of_guests" name="num_of_guests" min="1" placeholder="Enter number of guests" required>
    </div>
    <div class="mb-3">
        <label for="special_requirements" class="form-label">Special Requirements</label>
        <textarea class="form-control" id="special_requirements" name="special_requirements" placeholder="Enter any special requirements (optional)"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit Booking</button>
</form>

        </div>
    </section>
    <?php include 'include/main_footer.php';?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="script/hall_booking_script.js"></script>
</body>
</html>