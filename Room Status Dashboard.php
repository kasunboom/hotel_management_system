<?php
session_start();

// Enable error reporting for debugging (remove these lines in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host     = 'localhost';
$dbname   = 'hotel_management';  // Replace with your actual database name
$username = 'root';              // Replace with your database username
$password = '';                  // Replace with your database password

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch useful room data
try {
    // Total number of rooms
    $sqlTotalRooms = "SELECT COUNT(*) as total_rooms FROM rooms";
    $stmtTotalRooms = $pdo->query($sqlTotalRooms);
    $totalRooms = $stmtTotalRooms->fetch(PDO::FETCH_ASSOC)['total_rooms'];

    // Room availability (count of available, occupied, and maintenance rooms)
    $sqlAvailability = "SELECT availability, COUNT(*) as count FROM rooms GROUP BY availability";
    $stmtAvailability = $pdo->query($sqlAvailability);
    $availabilityData = $stmtAvailability->fetchAll(PDO::FETCH_ASSOC);

    // Room types (count of each room type)
    $sqlRoomTypes = "SELECT room_type, COUNT(*) as count FROM rooms GROUP BY room_type";
    $stmtRoomTypes = $pdo->query($sqlRoomTypes);
    $roomTypesData = $stmtRoomTypes->fetchAll(PDO::FETCH_ASSOC);

    // Today's Bookings
    $sqlTodayBookings = "SELECT * FROM bookings WHERE DATE(booking_time) = CURDATE()";
    $stmtTodayBookings = $pdo->query($sqlTodayBookings);
    $todayBookings = $stmtTodayBookings->fetchAll(PDO::FETCH_ASSOC);

    // Yesterday's Checkouts
    $sqlYesterdayCheckouts = "SELECT * FROM bookings WHERE DATE(checkout_date) = CURDATE() - INTERVAL 1 DAY";
    $stmtYesterdayCheckouts = $pdo->query($sqlYesterdayCheckouts);
    $yesterdayCheckouts = $stmtYesterdayCheckouts->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching room data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room Data Dashboard</title>
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
    .card h2 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #2d3748;
    }
    .card p {
      font-size: 1rem;
      color: #4a5568;
    }
    .card .data-item {
      margin-bottom: 1rem;
    }
    .card .data-item strong {
      font-weight: 600;
      color: #2d3748;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    table th, table td {
      padding: 0.75rem;
      border: 1px solid #ccc;
      text-align: left;
    }
    table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
  </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

  <div class="content-area">
    <!-- Header -->
    <div class="card">
      <h2>Room Data Dashboard</h2>
      <p>Useful insights about room availability, types, and totals.</p>
    </div>

    <!-- Total Rooms -->
    <div class="card">
      <h2>Total Rooms</h2>
      <div class="data-item">
        <strong>Total Rooms:</strong> <?php echo $totalRooms; ?>
      </div>
    </div>

    <!-- Room Availability -->
    <div class="card">
      <h2>Room Availability</h2>
      <?php if (!empty($availabilityData)): ?>
        <?php foreach ($availabilityData as $availability): ?>
          <div class="data-item">
            <strong><?php echo htmlspecialchars($availability['availability']); ?>:</strong> <?php echo htmlspecialchars($availability['count']); ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No availability data found.</p>
      <?php endif; ?>
    </div>

    <!-- Room Types -->
    <div class="card">
      <h2>Room Types</h2>
      <?php if (!empty($roomTypesData)): ?>
        <?php foreach ($roomTypesData as $roomType): ?>
          <div class="data-item">
            <strong><?php echo htmlspecialchars($roomType['room_type']); ?>:</strong> <?php echo htmlspecialchars($roomType['count']); ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No room type data found.</p>
      <?php endif; ?>
    </div>

    <!-- Today's Bookings -->
    <div class="card">
      <h2>Today's Bookings</h2>
      <?php if (!empty($todayBookings)): ?>
        <table>
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Guest Name</th>
              <th>Room Type</th>
              <th>Check-in Date</th>
              <th>Check-out Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($todayBookings as $booking): ?>
              <tr>
                <td><?php echo htmlspecialchars($booking['id']); ?></td>
                <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No bookings found for today.</p>
      <?php endif; ?>
    </div>

    <!-- Yesterday's Checkouts -->
    <div class="card">
      <h2>Yesterday's Checkouts</h2>
      <?php if (!empty($yesterdayCheckouts)): ?>
        <table>
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Guest Name</th>
              <th>Room Type</th>
              <th>Check-in Date</th>
              <th>Check-out Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($yesterdayCheckouts as $checkout): ?>
              <tr>
                <td><?php echo htmlspecialchars($checkout['id']); ?></td>
                <td><?php echo htmlspecialchars($checkout['guest_name']); ?></td>
                <td><?php echo htmlspecialchars($checkout['room_type']); ?></td>
                <td><?php echo htmlspecialchars($checkout['checkin_date']); ?></td>
                <td><?php echo htmlspecialchars($checkout['checkout_date']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No checkouts found for yesterday.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>