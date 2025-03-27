<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS suppliers (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    SupplierName VARCHAR(255) NOT NULL,
    SupplierEmail VARCHAR(255),
    SupplierPhone VARCHAR(20),
    SupplierAddress TEXT,
    ItemsSupplied TEXT,
    PaymentTerms TEXT,
    Status ENUM('active', 'inactive') DEFAULT 'active',
    Rating DECIMAL(3,2),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    SupplierID INT,
    items_ordered VARCHAR(255),
    quantity INT,
    order_date DATE,
    status ENUM('pending', 'delivered', 'canceled') DEFAULT 'pending',
    FOREIGN KEY (SupplierID) REFERENCES suppliers(SupplierID)
)");

// Handle order creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {
    $supplier_id = (int)$_POST['supplier_id'];
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $quantity = (int)$_POST['quantity'];
    $order_date = date('Y-m-d');

    $query = "INSERT INTO orders (SupplierID, items_ordered, quantity, order_date, status) 
              VALUES ('$supplier_id', '$item', '$quantity', '$order_date', 'pending')";

    if (mysqli_query($conn, $query)) {
        header("Location: orders_man.php");
        exit();
    } else {
        die("Error: " . $conn->error);
    }
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $conn->query("UPDATE orders SET status='delivered' WHERE order_id=$order_id");
    header("Location: orders_man.php");
    exit();
}

// Fetch suppliers
$suppliers_result = $conn->query("SELECT SupplierID, SupplierName FROM suppliers");
if (!$suppliers_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1, h2 { text-align: center; }
        form { padding: 10px 20px; }
        label, select, input, button { display: block; width: 100%; margin-top: 10px; padding: 8px; }
        button { background: #38b2ac; color: white; border: none; cursor: pointer; }
        button:hover { background: #2c7a7b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #f8f8f8; }
        .status { padding: 5px 10px; border-radius: 5px; }
        .status.pending { background: #f6ad55; color: white; }
        .status.delivered { background: #38b2ac; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Purchase Orders</h1>

        <!-- Create Purchase Order -->
        <h2>Create New Order</h2>
        <form action="orders_man.php" method="POST">
            <label>Supplier:</label>
            <select name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while ($supplier = $suppliers_result->fetch_assoc()): ?>
                    <option value="<?= $supplier['SupplierID'] ?>"><?= $supplier['SupplierName'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Item:</label>
            <select name="item" required>
                <option value="">Select Item</option>
                <option value="Rice">Rice</option>
                <option value="Juice">Juice</option>
            </select>

            <label>Quantity:</label>
            <input type="number" name="quantity" placeholder="Quantity" required />

            <button type="submit" name="create_order">Create Order</button>
        </form>

        <!-- Display Orders -->
        <h2>Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Items Ordered</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT orders.*, suppliers.SupplierName FROM orders
                          JOIN suppliers ON orders.SupplierID = suppliers.SupplierID";
                $result = $conn->query($query);

                if (!$result) {
                    die("Query failed: " . $conn->error);
                }

                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['order_id'] ?></td>
                        <td><?= $row['SupplierName'] ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><?= $row['items_ordered'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>" />
                                    <button type="submit" name="update_status">Mark as Delivered</button>
                                </form>
                            <?php else: ?>
                                <span>✔ Delivered</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
