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

// Add menu item to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $description = $_POST['description'];  // Changed from category to description
    $price = $_POST['price'];

    if ($action === 'add') {
        // Insert query using MySQLi
        $stmt = $conn->prepare("INSERT INTO menu (name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $price);
        if ($stmt->execute()) {
            echo "Menu item added successfully!";
        } else {
            echo "Error adding menu item: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'update' && $id) {
        // Update query using MySQLi
        $stmt = $conn->prepare("UPDATE menu SET name = ?, description = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $name, $description, $price, $id);
        if ($stmt->execute()) {
            echo "Menu item updated successfully!";
        } else {
            echo "Error updating menu item: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'delete' && $id) {
        // Delete query using MySQLi
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Menu item deleted successfully!";
        } else {
            echo "Error deleting menu item: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all menu items
function getMenuItems() {
    global $conn;
    $result = $conn->query("SELECT * FROM menu");
    return $result->fetch_all(MYSQLI_ASSOC);
}

$menuItems = getMenuItems();  // Get all menu items
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* (Same as before for styling) */
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

        /* Card Styling for Form */
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        /* Form Title */
        .form-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }

        /* Form Label */
        .form-label {
            font-size: 1rem;
            color: #444;
            margin-bottom: 0.5rem;
        }

        /* Form Input */
        .form-input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .form-input:focus {
            border-color: #38b2ac;
            outline: none;
        }

        /* Button Group */
        .button-group {
            display: flex;
            gap: 1rem;
        }

        /* Reset Button Styling */
        .button.reset {
            background: #e53e3e;
        }

        .button.reset:hover {
            background: #c53030;
        }

        .button.add {
            background: #38b2ac;
        }

        .button.add:hover {
            background: #2c7a7b;
        }

        /* Additional Button Styles */
        .button {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button i {
            margin-right: 0.5rem;
        }

        /* Header Section */
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-header .breadcrumb {
            font-size: 0.875rem;
            color: #718096;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        .table th, .table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #38b2ac;
            color: white;
        }

        .table tr:hover {
            background-color: #f4f4f4;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.875rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>


    <div class="content-area">
        <!-- Header Section -->
        <div class="page-header">
            <h1>Restaurant Menu Management</h1>
        </div>

        <!-- Add/Edit Menu Item Section -->
        <div class="card" id="menuFormContainer">
            <h3 id="formTitle" class="form-title">Add or Edit Menu Item</h3>
            <form id="menuForm" method="POST" action="">
                <input type="hidden" id="itemId" name="id" value="">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="itemName" class="form-label">Item Name</label>
                    <input type="text" id="itemName" name="name" required placeholder="Enter item name" class="form-input">
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" name="description" required placeholder="Enter item description" class="form-input">
                </div>
                <div class="form-group">
                    <label for="price" class="form-label">Price (LKR)</label>
                    <input type="number" id="price" name="price" required placeholder="Enter item price" class="form-input">
                </div>
                <div class="form-group button-group">
                    <button type="submit" class="button add" id="submitButton">
                        <i class="fas fa-save"></i> Save Item
                    </button>
                    <button type="reset" class="button reset">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Menu Items List Table -->
        <div class="card">
            <h3 class="form-title">Existing Menu Items</h3>
            <table class="table" id="menuItemsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price (LKR)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                    <tr id="item-<?php echo $item['id']; ?>">
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td>LKR <?php echo htmlspecialchars($item['price']); ?></td>
                        <td>
                            <button class="button add" onclick="editItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="button reset" onclick="deleteItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editItem(id) {
            const row = document.getElementById("item-" + id);
            if (row) {
                const cells = row.querySelectorAll("td");
                // Fill the form with the current data
                document.getElementById("itemName").value = cells[0].textContent;
                document.getElementById("description").value = cells[1].textContent;
                document.getElementById("price").value = cells[2].textContent.replace("LKR", "").trim();
                document.getElementById("itemId").value = id;
                document.getElementById("submitButton").textContent = "Update Item";
                document.querySelector("input[name='action']").value = "update"; // Set action to update
            }
        }

        function deleteItem(id) {
            const confirmDelete = confirm("Are you sure you want to delete this item?");
            if (confirmDelete) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', 'delete');

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    alert(result);
                    if (result.includes('successfully')) {
                        // Remove the item row from the table
                        document.getElementById("item-" + id).remove();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
</body>
</html>
