<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
        $special_requirements = $_POST['special_requirements'] ?? '';
        $user_id = $_SESSION['user_id'];

        // Check if the function_date and function_time are already booked
        $check_query = "SELECT * FROM hall_bookings WHERE function_date = ? AND function_time = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $function_date, $function_time);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('The selected time slot is already booked. Please choose another time.'); window.location.href='hall_booking.php';</script>";
            exit();
        }

        // Insert booking if no conflict
        $query = "INSERT INTO hall_bookings (
            booking_date, function_date, function_time, function_type,
            guest_name, guest_phone, guest_email, guest_count, special_requirements
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssis", 
            $booking_date, $function_date, $function_time, $function_type,
            $full_name, $mobile_number, $email_address, $num_of_guests, $special_requirements
        );

        if ($stmt->execute()) {
            echo "<script>alert('Hall Booking Successful!'); window.location.href='banquet_hall_booking_admin.php';</script>";
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
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        :root {
  --primary: #667eea;
  --secondary: #5560ea;
  --accent: #4a55e7;
  --highlight: #4051e2;
  --hover: #3545df;
  --light: #ecf0f1;
}

body {
  font-family: 'Poppins', sans-serif;
  background-color: var(--light);
  padding-top: 60px;
}

.reservation-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
  background-color: var(--secondary);
  border-radius: 10px;
}

.reservation-form input,
.reservation-form select,
.reservation-form textarea {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1rem;
}

.reservation-form button {
  background-color: var(--highlight);
  color: white;
}

    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <section id="booking">
        <div class="container">
            <h2 class="text-center">Hall Booking</h2>
            <form method="POST" action="hall_booking.php" class="reservation-form">
                <label for="booking_date" class="form-label">Booking Date</label>
                <input type="date" class="form-control" id="booking_date" name="booking_date" required>

                <label for="function_date" class="form-label">Function Date</label>
                <input type="date" class="form-control" id="function_date" name="function_date" required>

                <label for="function_time" class="form-label">Function Time</label>
                <select class="form-control" id="function_time" name="function_time" required>
                    <option value="">Select Time Slot</option>
                    <option value="9:00 AM - 5:00 PM">9:00 AM - 5:00 PM</option>
                    <option value="5:00 PM - 11:30 PM">5:00 PM - 11:30 PM</option>
                </select>

                <label for="function_type" class="form-label">Function Type</label>
                <select class="form-control" id="function_type" name="function_type" required>
                    <option value="wedding">Wedding Reception</option>
                    <option value="conference">Conference</option>
                    <option value="birthday">Birthday Party</option>
                    <option value="corporate">Corporate Event</option>
                </select>

                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>

                <label for="mobile_number" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" required>

                <label for="email_address" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email_address" name="email_address" required>

                <label for="num_of_guests" class="form-label">Number of Guests</label>
                <input type="number" class="form-control" id="num_of_guests" name="num_of_guests" min="1" required>

                <label for="special_requirements" class="form-label">Special Requirements</label>
                <textarea class="form-control" id="special_requirements" name="special_requirements"></textarea>

                <button type="submit" class="btn btn-primary">Submit Booking</button>
            </form>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
