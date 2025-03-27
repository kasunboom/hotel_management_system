<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Connect to the database using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "Sorry, there was an issue connecting to our services. Please try again later.";
    exit();
}

// Add or edit reservation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $customerName = $_POST['customerName'];
    $reservationDate = $_POST['reservationDate'];
    $reservationTime = $_POST['reservationTime'];
    $numberOfGuests = $_POST['numberOfGuests'];
    $specialRequests = isset($_POST['specialRequests']) ? $_POST['specialRequests'] : null;
    $status = $_POST['status'];

    // Check if we're updating an existing reservation
    if (isset($_POST['reservationId']) && !empty($_POST['reservationId'])) {
        $reservationId = $_POST['reservationId'];

        // Update the existing reservation
        $query = "UPDATE reservations 
                  SET customer_name = ?, reservation_date = ?, reservation_time = ?, party_size = ?, special_requests = ?, status = ? 
                  WHERE reservation_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $customerName, $reservationDate, $reservationTime, $numberOfGuests, $specialRequests, $status, $reservationId);

        if ($stmt->execute()) {
            echo "Reservation updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Add a new reservation
        $query = "INSERT INTO reservations (customer_name, reservation_date, reservation_time, party_size, special_requests, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $customerName, $reservationDate, $reservationTime, $numberOfGuests, $specialRequests, $status);

        if ($stmt->execute()) {
            echo "Reservation added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Delete reservation
if (isset($_GET['deleteReservationId'])) {
    $reservationId = $_GET['deleteReservationId'];
    $query = "DELETE FROM reservations WHERE reservation_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reservationId);

    if ($stmt->execute()) {
        echo "Reservation deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch reservations
$query = "SELECT * FROM reservations";
$reservations = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Reservation Management</title>
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS styles */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #2d3748;
            margin: 0;
            padding: 0;
        }

        .content-area {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-header .breadcrumb {
            font-size: 0.875rem;
            color: #718096;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }

        .form-label {
            font-size: 1rem;
            color: #444;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .form-input:focus {
            border-color: #38b2ac;
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 1rem;
        }

        .button {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button.add {
            background: #38b2ac;
        }

        .button.add:hover {
            background: #2c7a7b;
        }

        .button.reset {
            background: #e53e3e;
        }

        .button.reset:hover {
            background: #c53030;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #38b2ac;
            color: white;
        }

        .table tr:hover {
            background-color: #f4f4f4;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-button {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.875rem;
            font-weight: bold;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-button.edit {
            background: #4299e1;
        }

        .action-button.edit:hover {
            background: #2b6cb0;
        }

        .action-button.delete {
            background: #e53e3e;
        }

        .action-button.delete:hover {
            background: #c53030;
        }
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-weight: bold;
        }

        .form-input {
            width: 100%;
            padding: 8px;
            font-size: 14px;
        }

        .button-group {
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
        }

        .button.add {
            background-color: #4CAF50;
            color: white;
        }

        .button.reset {
            background-color: #f44336;
            color: white;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table-actions button {
            margin-right: 10px;
        }
    </style>
</head>

<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <div class="page-header">
            <h1>Table Reservation Management</h1>
        </div>

        <div class="card">
            <h3 class="form-title">Add or Edit Reservation</h3>
            <form method="POST" action="Table Reservation Management.php" onsubmit="return handleFormSubmit(event)">
                <input type="hidden" id="reservationId" name="reservationId">
                <div class="form-group">
                    <label for="customerName" class="form-label">Customer Name</label>
                    <input type="text" id="customerName" name="customerName" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="reservationDate" class="form-label">Reservation Date</label>
                    <input type="date" id="reservationDate" name="reservationDate" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="reservationTime" class="form-label">Reservation Time</label>
                    <input type="time" id="reservationTime" name="reservationTime" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="numberOfGuests" class="form-label">Number of Guests</label>
                    <input type="number" id="numberOfGuests" name="numberOfGuests" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="specialRequests" class="form-label">Special Requests</label>
                    <textarea id="specialRequests" name="specialRequests" class="form-input" placeholder="Enter special requests (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="button-group">
                    <button type="submit" class="button add">Save Reservation</button>
                    <button type="reset" class="button reset">Reset</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Customer Name</th>
                        <th>Reservation Date</th>
                        <th>Reservation Time</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($reservations->num_rows > 0) {
                        while ($row = $reservations->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['reservation_id'] . "</td>";
                            echo "<td>" . $row['customer_name'] . "</td>";
                            echo "<td>" . $row['reservation_date'] . "</td>";
                            echo "<td>" . $row['reservation_time'] . "</td>";
                            echo "<td>" . $row['party_size'] . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td class='table-actions'>
                                <button class='action-button edit' onclick='editReservation(" . $row['reservation_id'] . ", \"" . $row['customer_name'] . "\", \"" . $row['reservation_date'] . "\", \"" . $row['reservation_time'] . "\", " . $row['party_size'] . ", \"" . $row['special_requests'] . "\", \"" . $row['status'] . "\")'>Edit</button>
                                <a href='Table Reservation Management.php?deleteReservationId=" . $row['reservation_id'] . "' class='action-button delete'>Delete</a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No reservations found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editReservation(reservationId, customerName, reservationDate, reservationTime, numberOfGuests, specialRequests, status) {
            document.getElementById("reservationId").value = reservationId;
            document.getElementById("customerName").value = customerName;
            document.getElementById("reservationDate").value = reservationDate;
            document.getElementById("reservationTime").value = reservationTime;
            document.getElementById("numberOfGuests").value = numberOfGuests;
            document.getElementById("specialRequests").value = specialRequests;
            document.getElementById("status").value = status;
        }
    </script>
</body>

</html>
