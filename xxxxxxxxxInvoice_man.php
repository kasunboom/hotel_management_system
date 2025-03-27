<?php
// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for new invoice creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_invoice'])) {
    $customer = $_POST['customer'];
    $services = $_POST['services'];
    $amount = $_POST['amount'];
    $taxes = $_POST['taxes'];
    $discounts = $_POST['discounts'];
    $terms = $_POST['terms'];

    // Insert the new invoice into the database
    $stmt = $conn->prepare("INSERT INTO invoices (customer, services, amount, taxes, discounts, terms, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssss", $customer, $services, $amount, $taxes, $discounts, $terms);
    $stmt->execute();
    $stmt->close();
}

// Fetch invoices from the database
$invoices = $conn->query("SELECT * FROM invoices ORDER BY date_issued DESC");

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-header .breadcrumb {
            font-size: 1rem;
            color: #718096;
        }

        /* Invoice List */
        .invoice-list {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        .invoice-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-list th,
        .invoice-list td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
        }

        .invoice-list th {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            font-weight: 600;
        }

        .invoice-list tbody tr:hover {
            background-color: #f7fafc;
        }

        /* Create New Invoice */
        .create-invoice {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        .create-invoice form {
            display: flex;
            flex-direction: column;
        }

        .create-invoice label {
            margin-bottom: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .create-invoice input,
        .create-invoice select,
        .create-invoice textarea {
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Invoice History */
        .invoice-history {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="content-area">
        <!-- Header -->
        <div class="page-header">
            <h1>Invoice Management</h1>
            <p class="breadcrumb">Dashboard > Invoice Management</p>
        </div>

        <!-- Invoice List -->
        <div class="invoice-list">
            <h3>Invoice List</h3>
            <table id="invoice-table">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Customer</th>
                        <th>Date Issued</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $invoices->fetch_assoc()) : ?>
                        <tr>
                            <td>#<?= $row['invoice_number'] ?></td>
                            <td><?= $row['customer'] ?></td>
                            <td><?= date('m/d/Y', strtotime($row['date_issued'])) ?></td>
                            <td>LKR <?= $row['amount'] ?></td>
                            <td><?= $row['status'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Create New Invoice -->
        <div class="create-invoice">
            <h3>Create New Invoice</h3>
            <form method="POST" id="invoice-form">
            <label for="customer">customer</label>
            <input type="text" name="customer" id="customer" placeholder="Name" required />

                <label for="services">Services Rendered</label>
                <select name="services" id="services">
                    <option value="Room Stay">Room Stay</option>
                    <option value="Restaurant Charges">Restaurant Charges</option>
                    <option value="Event Booking">Event Booking</option>
                </select>

                <label for="amount">Amount</label>
                <input type="text" name="amount" id="amount" placeholder="LKR" required />

                <label for="taxes">Taxes</label>
                <input type="text" name="taxes" id="taxes" placeholder="5%" />

                <label for="discounts">Discounts</label>
                <input type="text" name="discounts" id="discounts" placeholder="10%" />

                <label for="terms">Terms & Payment Instructions</label>
                <textarea name="terms" id="terms" rows="4" placeholder="Payment due within 30 days..." required></textarea>

                <button type="submit" name="generate_invoice">Generate Invoice</button>
            </form>
        </div>

        <!-- Invoice History -->
        <div class="invoice-history">
            <h3>Invoice History</h3>
            <p>View and resend past invoices to customers.</p>
            <button id="view-invoice-history">View Invoice History</button>
        </div>
    </div>
</body>

</html>
