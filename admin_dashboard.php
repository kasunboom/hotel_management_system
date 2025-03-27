<?php
// Database credentials
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "hotel_management";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "Sorry, there was an issue connecting to our services. Please try again later.";
    exit();
}

// -------------------------------
// Fetch Summary Data for Dashboard
// -------------------------------

// Fetch Total Bookings (overall count)
$result = $conn->query("SELECT COUNT(*) AS total_bookings FROM bookings");
$totalBookings = $result->fetch_assoc()['total_bookings'];

// Fetch Total Revenue (for this month)
// Note: Calculated as total_income - total_expenses from the revenue table.
$result = $conn->query("SELECT SUM(total_income) - SUM(total_expenses) AS total_revenue
                         FROM revenue 
                         WHERE MONTH(revenue_date) = MONTH(CURRENT_DATE()) 
                         AND YEAR(revenue_date) = YEAR(CURRENT_DATE())");
$totalRevenue = $result->fetch_assoc()['total_revenue'] ?? 0;

// -------------------------------
// Fetch Today's Data
// -------------------------------

// Today's Reservations
$reservationsQuery = "SELECT * FROM reservations WHERE reservation_date = CURDATE()";
$reservationsResult = $conn->query($reservationsQuery);

// Today's Bookings (using booking_time as the timestamp indicator)
$bookingsQuery = "SELECT * FROM bookings WHERE DATE(booking_time) = CURDATE()";
$bookingsResult = $conn->query($bookingsQuery);

// Today's Events (hall bookings scheduled for today based on function_date)
$eventsQuery = "SELECT * FROM hall_bookings WHERE function_date = CURDATE()";
$eventsResult = $conn->query($eventsQuery);

// -------------------------------
// Fetch Revenue Trend Data for Last 6 Months
// -------------------------------
$revenueData = [];
$revenueQuery = "SELECT DATE_FORMAT(revenue_date, '%b') AS month, SUM(total_revenue) AS revenue 
                 FROM revenue 
                 WHERE revenue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(revenue_date, '%Y-%m')
                 ORDER BY MIN(revenue_date) ASC";
$revResult = $conn->query($revenueQuery);
if ($revResult) {
    while($row = $revResult->fetch_assoc()){
        $revenueData[] = $row;
    }
}

// Close the connection if no further queries are needed.
// $conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Hotel Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Link to your CSS file -->
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <style>
        /* Additional CSS for tables and charts */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .table-container {
            margin: 1rem 0;
        }
        .chart-container {
            position: relative;
            height: 300px; /* Fixed height for chart */
            width: 100%;
            margin: auto;
        }
    </style>
</head>

<body>
<?php include 'include\admin dashbord_sidebar.php';?>
<!-- Main Content -->
    <div class="content-area">
        <div class="dashboard-header">
            <h2 class="welcome-text">Welcome back, Admin</h2>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-book"></i>
                    <span>Total Bookings</span>
                </div>
                <div class="stat-value"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Active reservations</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-chart-line"></i>
                    <span>Revenue</span>
                </div>
                <div class="stat-value">₨<?php echo number_format($totalRevenue); ?></div>
                <div class="stat-label">This month</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-header">Revenue Trends (Last 6 Months)</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <!-- Additional charts can be added here -->
        </div>

        <!-- Tables for Today's Data -->
        <div class="table-container">
            <h3>Today's Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Party Size</th>
                        <th>Special Requests</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservationsResult && $reservationsResult->num_rows > 0): ?>
                        <?php while($row = $reservationsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['reservation_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo $row['reservation_date']; ?></td>
                            <td><?php echo $row['reservation_time']; ?></td>
                            <td><?php echo $row['party_size']; ?></td>
                            <td><?php echo htmlspecialchars($row['special_requests']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No reservations for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Today's Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Guest Count</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Room Type</th>
                        <th>Total Amount</th>
                        <th>Booking Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookingsResult && $bookingsResult->num_rows > 0): ?>
                        <?php while($row = $bookingsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['booking_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['guest_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['guest_phone']); ?></td>
                            <td><?php echo $row['guest_count']; ?></td>
                            <td><?php echo $row['checkin_date']; ?></td>
                            <td><?php echo $row['checkout_date']; ?></td>
                            <td><?php echo htmlspecialchars($row['room_type']); ?></td>
                            <td>₨<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo $row['booking_time']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">No bookings for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Today's Events</h3>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Event ID</th>
                        <th>Function Date</th>
                        <th>Function Time</th>
                        <th>Function Type</th>
                        <th>Guest Name</th>
                        <th>Guest Phone</th>
                        <th>Guest Email</th>
                        <th>Guest Count</th>
                        <th>Special Requirements</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($eventsResult && $eventsResult->num_rows > 0): ?>
                        <?php while($row = $eventsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['booking_id']; ?></td>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['function_date']; ?></td>
                            <td><?php echo $row['function_time']; ?></td>
                            <td><?php echo htmlspecialchars($row['function_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['guest_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['guest_email']); ?></td>
                            <td><?php echo $row['guest_count']; ?></td>
                            <td><?php echo htmlspecialchars($row['special_requirements']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">No events for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Revenue Chart with dynamic data from PHP (last 6 months)
    const revenueData = <?php echo json_encode($revenueData); ?>;
    const labels = revenueData.map(item => item.month);
    const dataPoints = revenueData.map(item => parseFloat(item.revenue));

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (LKR)',
                data: dataPoints,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    </script>
</body>

</html>
