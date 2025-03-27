<?php
// Database connection settings
$host = "localhost";
$user = "root";
$password = "";
$dbname = "hotel_management";

// Establishing connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    $booking_id = $conn->real_escape_string($_POST["delete"]);
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        echo "Booking deleted successfully.";
    } else {
        echo "Error deleting booking: " . $conn->error;
    }
    exit;
}

// Handle Edit Request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit"])) {
    $booking_id = $conn->real_escape_string($_POST["booking_id"]);
    $guest_name = $conn->real_escape_string($_POST["guest_name"]);
    $guest_email = $conn->real_escape_string($_POST["guest_email"]);
    $guest_phone = $conn->real_escape_string($_POST["guest_phone"]);
    $checkin_date = $conn->real_escape_string($_POST["checkin_date"]);
    $checkout_date = $conn->real_escape_string($_POST["checkout_date"]);
    $room_type = $conn->real_escape_string($_POST["room_type"]);

    $stmt = $conn->prepare("UPDATE bookings SET 
                    guest_name = ?, 
                    guest_email = ?, 
                    guest_phone = ?, 
                    checkin_date = ?, 
                    checkout_date = ?, 
                    room_type = ? 
                    WHERE booking_id = ?");
    
    $stmt->bind_param("ssssssi", $guest_name, $guest_email, $guest_phone, 
                     $checkin_date, $checkout_date, $room_type, $booking_id);

    if ($stmt->execute()) {
        echo "Booking updated successfully.";
    } else {
        echo "Error updating booking: " . $conn->error;
    }
    exit;
}

// Fetch booking records
$sql = "SELECT booking_id, guest_name, guest_email, guest_phone, 
               checkin_date, checkout_date, room_type 
        FROM bookings";
$result = $conn->query($sql);

$bookings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking List</title>
      <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #2d3748;
            margin: 0;
            padding: 0;
        }

        .content-area {
            padding: 2rem;
            margin-left: 250px;
        }
        h1 {
    text-align: left; /* Aligns text to the left */
    margin-left: 0; /* Ensures it stays at the leftmost side */
    padding-left: 10px; /* Adds some spacing from the left edge */
}

       

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f4f8;
        }

        th {
            background-color: #38b2ac;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        .actions button {
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-radius: 5px;
            margin: 2px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .delete-btn {
            background-color: #e53e3e;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c53030;
        }

        .edit-btn {
            background-color: #3182ce;
            color: white;
        }

        .edit-btn:hover {
            background-color: #2b6cb0;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .close-modal {
            cursor: pointer;
            font-size: 24px;
            color: #666;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4a5568;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        button[type="submit"] {
            background-color: #38b2ac;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        button[type="submit"]:hover {
            background-color: #319795;
        }
    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <h1>Booking List</h1>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Room Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                        <td><?= htmlspecialchars($booking['guest_name']) ?></td>
                        <td><?= htmlspecialchars($booking['guest_email']) ?></td>
                        <td><?= htmlspecialchars($booking['guest_phone']) ?></td>
                        <td><?= htmlspecialchars($booking['checkin_date']) ?></td>
                        <td><?= htmlspecialchars($booking['checkout_date']) ?></td>
                        <td><?= htmlspecialchars($booking['room_type']) ?></td>
                        <td class="actions">
                            <button class="edit-btn" onclick="openEditModal(<?= htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                            <button class="delete-btn" onclick="deleteBooking('<?= htmlspecialchars($booking['booking_id']) ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Booking</h3>
                <span class="close-modal" onclick="toggleModal()">&times;</span>
            </div>
            <form id="editForm" onsubmit="handleFormSubmit(event)">
                <input type="hidden" id="editBookingId" name="booking_id">
                <div class="form-group">
                    <label for="editGuestName">Guest Name</label>
                    <input type="text" id="editGuestName" name="guest_name" required>
                </div>
                <div class="form-group">
                    <label for="editGuestEmail">Email</label>
                    <input type="email" id="editGuestEmail" name="guest_email" required>
                </div>
                <div class="form-group">
                    <label for="editGuestPhone">Phone</label>
                    <input type="text" id="editGuestPhone" name="guest_phone" required>
                </div>
                <div class="form-group">
                    <label for="editCheckinDate">Check-in Date</label>
                    <input type="date" id="editCheckinDate" name="checkin_date" required>
                </div>
                <div class="form-group">
                    <label for="editCheckoutDate">Check-out Date</label>
                    <input type="date" id="editCheckoutDate" name="checkout_date" required>
                </div>
                <div class="form-group">
                    <label for="editRoomType">Room Type</label>
                    <input type="text" id="editRoomType" name="room_type" required>
                </div>
                <button type="submit">Update Booking</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('editModal');
            modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        }

        function openEditModal(booking) {
            document.getElementById('editBookingId').value = booking.booking_id;
            document.getElementById('editGuestName').value = booking.guest_name;
            document.getElementById('editGuestEmail').value = booking.guest_email;
            document.getElementById('editGuestPhone').value = booking.guest_phone;
            document.getElementById('editCheckinDate').value = booking.checkin_date;
            document.getElementById('editCheckoutDate').value = booking.checkout_date;
            document.getElementById('editRoomType').value = booking.room_type;
            toggleModal();
        }

        async function handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        edit: true,
                        ...data
                    })
                });

                if (response.ok) {
                    alert(await response.text());
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the booking');
            }
        }

        function deleteBooking(id) {
            if (confirm("Are you sure you want to delete this booking?")) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ delete: id })
                })
                .then(response => {
                    if (response.ok) {
                        alert('Booking deleted successfully');
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the booking');
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                toggleModal();
            }
        }
    </script>
</body>
</html>