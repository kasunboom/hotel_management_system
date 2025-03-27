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
    $supplier_name = $_POST['supplier_name'];
    mysqli_query($conn, "INSERT INTO suppliers (supplier_name) VALUES ('$supplier_name')");
}
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Supplier Management</h1>
    <form method="POST">
        <input type="text" name="supplier_name" placeholder="Supplier Name" required>
        <button type="submit">Add Supplier</button>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>Supplier Name</th>
        </tr>
        <?php while ($supplier = mysqli_fetch_assoc($suppliers)) { ?>
            <tr>
                <td><?php echo $supplier['id']; ?></td>
                <td><?php echo $supplier['supplier_name']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
