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

// Assuming user is logged in, use session to get the logged-in user ID
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch current user profile data from the database
$user_query = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
if (!$user_query) {
    die("SQL error: " . $conn->error); // Debugging message in case query preparation fails
}

$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_query->bind_result($username, $email);
$user_query->fetch();
$user_query->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['email'])) {
    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);

    // Check if the new username or email already exists
    $check_query = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    if (!$check_query) {
        die("SQL error: " . $conn->error); // Debugging message in case query preparation fails
    }

    $check_query->bind_param("ssi", $new_username, $new_email, $user_id);
    $check_query->execute();
    $check_query->store_result();

    if ($check_query->num_rows > 0) {
        $message = "Username or email already in use by another account.";
        $message_type = "error";
    } else {
        // Update the user's profile details
        $update_query = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if (!$update_query) {
            die("SQL error: " . $conn->error); // Debugging message in case query preparation fails
        }

        $update_query->bind_param("ssi", $new_username, $new_email, $user_id);

        if ($update_query->execute()) {
            $message = "Profile updated successfully!";
            $message_type = "success";
            // Update session variables if needed
            $_SESSION['username'] = $new_username;
            $_SESSION['email'] = $new_email;
        } else {
            $message = "Error updating profile: " . $update_query->error;
            $message_type = "error";
        }

        $update_query->close();
    }

    $check_query->close();
}

// Fetch ongoing bookings for the user
$bookings_query = $conn->prepare("SELECT booking_id, room_type, checkin_date, checkout_date FROM bookings WHERE id = ?");
if (!$bookings_query) {
    die("SQL error: " . $conn->error); // Debugging message in case query preparation fails
}

$bookings_query->bind_param("i", $user_id);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();

// Fetch ongoing hall bookings for the user
$hall_bookings_query = $conn->prepare("SELECT booking_id, booking_date, function_date, function_time, special_requirements, status FROM hall_bookings WHERE id = ? AND (status = 'Active' OR status = 'Pending')");
if (!$hall_bookings_query) {
    die("SQL error: " . $conn->error);
}

$hall_bookings_query->bind_param("i", $user_id);
$hall_bookings_query->execute();
$hall_bookings_result = $hall_bookings_query->get_result();

// Fetch ongoing reservations for the user
$reservations_query = $conn->prepare("SELECT reservation_id, reservation_date, reservation_time, party_size, special_requests, status FROM reservations WHERE user_id = ? AND (status = 'Active' OR status = 'Pending')");
if (!$reservations_query) {
    die("SQL error: " . $conn->error);
}

$reservations_query->bind_param("i", $user_id);
$reservations_query->execute();
$reservations_result = $reservations_query->get_result();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="css\profile_css.css" rel="stylesheet">
</head>

<body>
    <?php include 'include\navbar.php'; ?>

    <div class="profile-container">
        <h1>Your Profile</h1>

        <!-- Show notification message -->
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>Profile Information</h2>
            <form method="POST" action="profile.php">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                <button class="save-btn">Save Changes</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Ongoing room Bookings</h2>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Room Type</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['booking_id'] ?></td>
                        <td><?= $row['room_type'] ?></td>
                        <td><?= $row['checkin_date'] ?></td>
                        <td><?= $row['checkout_date'] ?></td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="profile-section">
            <h2>Ongoing Hall Bookings</h2>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Booking Date</th>
                        <th>Function Date</th>
                        <th>Function Time</th>
                        <th>Special Requirements</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $hall_bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['booking_id'] ?></td>
                            <td><?= $row['booking_date'] ?></td>
                            <td><?= $row['function_date'] ?></td>
                            <td><?= $row['function_time'] ?></td>
                            <td><?= $row['special_requirements'] ?></td>
                            <td><span class="badge bg-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h2>Ongoing Reservations</h2>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Reservation Date</th>
                        <th>Reservation Time</th>
                        <th>Party Size</th>
                        <th>Special Requests</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $reservations_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['reservation_id'] ?></td>
                            <td><?= $row['reservation_date'] ?></td>
                            <td><?= $row['reservation_time'] ?></td>
                            <td><?= $row['party_size'] ?></td>
                            <td><?= $row['special_requests'] ?></td>
                            <td><span class="badge bg-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'include\main_footer.php';?>
</body>

</html>
