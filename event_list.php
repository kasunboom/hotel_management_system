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

// Handle Edit Event Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventId']) && isset($_POST['status'])) {
    $eventId = $_POST['eventId'];
    $status = $_POST['status'];
    $query = "UPDATE hall_bookings SET status = '$status' WHERE booking_id = '$eventId'";

    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Handle Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteEventId'])) {
    $eventId = $_POST['deleteEventId'];
    $query = "DELETE FROM hall_bookings WHERE booking_id = '$eventId'";

    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Fetch Events without Filters
$query = "SELECT * FROM hall_bookings";
$result = $conn->query($query);
$events = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* General Styles */
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

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #38b2ac;
            color: white;
            font-weight: bold;
        }

        td {
            background-color: rgba(255, 255, 255, 0.9);
        }

        td .status {
            padding: 0.5rem;
            border-radius: 10px;
        }

        td .status.pending {
            background-color: #f6ad55;
            color: white;
        }

        td .status.completed {
            background-color: #48bb78;
            color: white;
        }

        td .status.canceled {
            background-color: #e53e3e;
            color: white;
        }

        /* Remove filter options */
        .filters {
            display: none;
        }
    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <!-- Header -->
        <div class="page-header">
            <h1>Event List</h1>
        </div>

        <!-- Event List Table -->
        <div class="card">
            <table id="eventTable">
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Event Name</th>
                        <th>Date & Time</th>
                        <th>Guest Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Event rows will be populated dynamically -->
                    <?php foreach ($events as $event): ?>
                    <tr id="event-<?= $event['booking_id'] ?>">
                        <td><?= $event['booking_id'] ?></td>
                        <td><?= $event['function_type'] ?></td>
                        <td><?= $event['function_date'] ?> <?= $event['function_time'] ?></td>
                        <td><?= $event['guest_count'] ?></td>
                        <td>
                            <span class="status <?= $event['status'] ?>"><?= ucfirst($event['status']) ?></span>
                            <select class="statusSelect" onchange="updateEventStatus(<?= $event['booking_id'] ?>, this.value)">
                                <option value="pending" <?= $event['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="completed" <?= $event['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="canceled" <?= $event['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                            </select>
                        </td>
                        <td>
                            <button onclick="deleteEvent(<?= $event['booking_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Update Event Status (AJAX request)
        function updateEventStatus(eventId, status) {
            fetch('event_list.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `eventId=${eventId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the status text dynamically in the table row
                    const statusCell = document.querySelector(`#event-${eventId} .status`);
                    statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusCell.className = 'status ' + status; // Change the color of status dynamically
                } else {
                    alert('Failed to update event status');
                }
            })
            .catch(error => {
                console.error('Error updating event status:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Delete Event (AJAX request)
        function deleteEvent(eventId) {
            const confirmed = confirm('Are you sure you want to delete this event?');
            if (confirmed) {
                fetch('event_list.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `deleteEventId=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event deleted successfully');
                        document.getElementById(`event-${eventId}`).remove();  // Remove the row dynamically
                    } else {
                        alert('Failed to delete event');
                    }
                })
                .catch(error => {
                    console.error('Error deleting event:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>
