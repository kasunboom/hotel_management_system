<?php
// Database credentials
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

// Initialize a message variable
$message = "";
$messageType = "";

// Start the session to get user ID
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $message = "You must be logged in to make a reservation.";
    $messageType = "danger";
} else {
    $userId = $_SESSION['user_id']; // Get user ID from session

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Collect form data
        $reservationDate = $_POST['reservation_date'];
        $reservationTime = $_POST['reservation_time'];
        $partySize = $_POST['party_size'];
        $specialRequests = $_POST['special_requests'];

        // Perform validation
        if (empty($reservationDate) || empty($reservationTime) || empty($partySize)) {
            $message = "All fields are required.";
            $messageType = "danger";
        } else {
            // Prepare SQL query to insert data into the database
            $sql = "INSERT INTO reservations (id, reservation_date, reservation_time, party_size, special_requests) 
                    VALUES (?, ?, ?, ?, ?)";

            // Prepare statement
            if ($stmt = $conn->prepare($sql)) {
                // Bind parameters
                $stmt->bind_param("issis", $userId, $reservationDate, $reservationTime, $partySize, $specialRequests);

                // Execute the statement
                if ($stmt->execute()) {
                    $message = "Reservation made successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . $stmt->error;
                    $messageType = "danger";
                }

                // Close statement
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $messageType = "danger";
            }
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restaurant Reservation - Grand Plaza Hotel</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/reserve_table_style.css">
</head>

<body>
  <?php include 'include/navbar.php'?>

  <!-- Reservation Section -->
  <section id="reservation">
    <div class="container">
      <h2>Reserve Your Table</h2>

      <!-- Success/Error Message -->
      <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <form class="reservation-form" id="reservation-form" action="Reserve_table.php" method="POST">
        <div class="mb-3">
          <label for="reservation_date" class="form-label">Reservation Date</label>
          <input type="date" id="reservation_date" name="reservation_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="reservation_time" class="form-label">Reservation Time</label>
          <input type="time" id="reservation_time" name="reservation_time" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="party_size" class="form-label">Party Size</label>
          <input type="number" id="party_size" name="party_size" class="form-control" min="1" max="20" value="2" required>
        </div>
        <div class="mb-3">
          <label for="special_requests" class="form-label">Special Requests</label>
          <textarea id="special_requests" name="special_requests" class="form-control" rows="4" placeholder="Enter any special requests..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Reserve Now</button>
      </form>
    </div>
  </section>

  <?php include 'include/main_footer.php';?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="script/reserve_table_script.js"></script>
  
  <!-- JavaScript for form validation -->
  <script>
    // Wait for the page to load fully
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('reservation-form');
      
      // Adding form submit listener
      form.addEventListener('submit', function(e) {
        let isValid = true;
        const reservationDate = document.getElementById('reservation_date').value;
        const reservationTime = document.getElementById('reservation_time').value;
        const partySize = document.getElementById('party_size').value;
        
        // Simple validation to check if all required fields are filled
        if (!reservationDate || !reservationTime || !partySize) {
          isValid = false;
          alert('Please fill in all required fields.');
        }
        
        // If validation fails, prevent form submission
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  </script>

</body>

</html>
