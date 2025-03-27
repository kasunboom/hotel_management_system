

<?php
// Database connection settings
$host = "localhost"; // Change if your database is hosted elsewhere
$user = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty by default)
$dbname = "hotel_management"; // Change to your actual database name

// Establishing connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$totalStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory"))['count'];
$totalSuppliers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM suppliers"))['count'];
$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Pending'"))['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="Inventory_styles.css">
</head>
<body>
    <div class="dashboard">
        <h1>Inventory Dashboard</h1>
        <div class="stats">
            <p>Total Stock: <?php echo $totalStock; ?></p>
            <p>Total Suppliers: <?php echo $totalSuppliers; ?></p>
            <p>Pending Orders: <?php echo $pendingOrders; ?></p>
        </div>
        <div class="links">
            <a href="stock_management.php">Stock Management</a>
            <a href="supplier_management.php">Supplier Management</a>
            <a href="orders_management.php">Orders Management</a>
        </div>
    </div>
</body>
</html>
