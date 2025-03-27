<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "Sorry, there was an issue connecting to our services. Please try again later.";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $supplier_id = $_POST['supplier_id'];
    mysqli_query($conn, "INSERT INTO inventory (item_name, quantity, price, supplier_id) VALUES ('$item_name', $quantity, $price, $supplier_id)");
}
$stockItems = mysqli_query($conn, "SELECT * FROM inventory");
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Stock Management</h1>
    <form method="POST">
        <input type="text" name="item_name" placeholder="Item Name" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <input type="number" name="price" placeholder="Price" required>
        <select name="supplier_id">
            <?php while ($supplier = mysqli_fetch_assoc($suppliers)) { ?>
                <option value="<?php echo $supplier['id']; ?>"> <?php echo $supplier['supplier_name']; ?> </option>
            <?php } ?>
        </select>
        <button type="submit">Add Item</button>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Supplier</th>
        </tr>
        <?php while ($item = mysqli_fetch_assoc($stockItems)) { ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td><?php echo $item['item_name']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo $item['price']; ?></td>
                <td><?php echo $item['supplier_id']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
