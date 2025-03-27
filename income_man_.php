<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding income
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_income'])) {
    $income_source = $_POST['income_source'];
    $amount = $_POST['income_amount'];
    $payment_method = $_POST['payment_method'];
    $customer = $_POST['customer'];

    $sql = "INSERT INTO income (income_source, amount, payment_method, customer, date) 
            VALUES ('$income_source', '$amount', '$payment_method', '$customer', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Income added successfully!'); window.location.href='income_man_.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch total income per source
$total_income = [
    "Total" => 0,
    "Room Bookings" => 0,
    "Restaurant Sales" => 0,
    "Events" => 0
];

$sql = "SELECT income_source, SUM(amount) as total_income FROM income GROUP BY income_source";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $total_income["Total"] += $row['total_income'];
    if (stripos($row['income_source'], 'room') !== false) {
        $total_income["Room Bookings"] += $row['total_income'];
    } elseif (stripos($row['income_source'], 'restaurant') !== false) {
        $total_income["Restaurant Sales"] += $row['total_income'];
    } elseif (stripos($row['income_source'], 'event') !== false) {
        $total_income["Events"] += $row['total_income'];
    }
}

// Fetch income transactions
$sql = "SELECT * FROM income ORDER BY date DESC";
$transactions = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/income_man_style.css" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <div class="page-header">
            <h1>Income Management</h1>
            <p class="breadcrumb">Dashboard > Income Management</p>
        </div>

        <div class="income-overview">
            <div class="income-card">
                <h3>Total Income</h3>
                <p><strong>Total:</strong> LKR <?= number_format($total_income["Total"], 2) ?></p>
            </div>
            <div class="income-card">
                <h3>Income from Room Bookings</h3>
                <p><strong>Total:</strong> LKR <?= number_format($total_income["Room Bookings"], 2) ?></p>
            </div>
            <div class="income-card">
                <h3>Income from Restaurant Sales</h3>
                <p><strong>Total:</strong> LKR <?= number_format($total_income["Restaurant Sales"], 2) ?></p>
            </div>
            <div class="income-card">
                <h3>Income from Events</h3>
                <p><strong>Total:</strong> LKR <?= number_format($total_income["Events"], 2) ?></p>
            </div>
        </div>

        <div class="income-graph">
            <h3>Monthly Income Trends</h3>
            <canvas id="incomeChart"></canvas>
        </div>

        <div class="income-transactions">
            <h3>Income Transactions</h3>
            <button onclick="openModal()">Add Income</button>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Income Source</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $transactions->fetch_assoc()) { ?>
                        <tr>
                            <td><?= date("d/m/Y", strtotime($row['date'])) ?></td>
                            <td><?= $row['income_source'] ?></td>
                            <td>LKR <?= number_format($row['amount'], 2) ?></td>
                            <td><?= $row['payment_method'] ?></td>
                            <td><?= $row['customer'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div id="incomeModal" class="modal">
            <div class="modal-content">
                <h2>Add Income</h2>
                <form method="POST" action="income_man_.php">
                    <label for="income_source">Income Source:</label>
                    <select name="income_source" required>
                        <option value="Room Bookings">Room Bookings</option>
                        <option value="Restaurant Sales">Restaurant Sales</option>
                        <option value="Events">Events</option>
                    </select>

                    <label for="income_amount">Amount (LKR):</label>
                    <input type="number" name="income_amount" required>

                    <label for="payment_method">Payment Method:</label>
                    <select name="payment_method" required>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Cash">Cash</option>
                        <option value="Online Transfer">Online Transfer</option>
                    </select>

                    <label for="customer">Customer:</label>
                    <input type="text" name="customer" required>

                    <button type="submit" name="add_income">Submit</button>
                </form>
                <button class="button close" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
function openModal() { document.getElementById('incomeModal').style.display = 'block'; }
function closeModal() { document.getElementById('incomeModal').style.display = 'none'; }

        let ctx = document.getElementById('incomeChart').getContext('2d');
        let incomeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ["Total Income", "Room Bookings", "Restaurant Sales", "Events"],
                datasets: [{
                    label: 'Total Income (LKR)',
                    data: [
                        <?= $total_income["Total"] ?>, 
                        <?= $total_income["Room Bookings"] ?>, 
                        <?= $total_income["Restaurant Sales"] ?>, 
                        <?= $total_income["Events"] ?>
                    ],
                    backgroundColor: ['#667eea', '#764ba2', '#ff9f43', '#34D399']
                }]
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>
