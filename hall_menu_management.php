<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add menu item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_menu'])) {
    $category_name = $_POST['category_name'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];
    $category_type = $_POST['category_type'];

    $stmt = $conn->prepare("INSERT INTO banquet_hall_menu (category_name, item_name, item_price, category_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $category_name, $item_name, $item_price, $category_type);
    $stmt->execute();
    header("Location: hall_menu_management.php?add_success=1");
    exit();
}

// Delete menu item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM banquet_hall_menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: hall_menu_management.php?delete_success=1");
    exit();
}

// Edit menu item (fetch data)
$edit_mode = false;
$edit_id = "";
$edit_category_name = "";
$edit_item_name = "";
$edit_item_price = "";
$edit_category_type = "";

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM banquet_hall_menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_id = $row['id'];
        $edit_category_name = $row['category_name'];
        $edit_item_name = $row['item_name'];
        $edit_item_price = $row['item_price'];
        $edit_category_type = $row['category_type'];
    }
}

// Update menu item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_menu'])) {
    $id = $_POST['id'];
    $category_name = $_POST['category_name'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];
    $category_type = $_POST['category_type'];

    $stmt = $conn->prepare("UPDATE banquet_hall_menu SET category_name=?, item_name=?, item_price=?, category_type=? WHERE id=?");
    $stmt->bind_param("ssdsi", $category_name, $item_name, $item_price, $category_type, $id);
    
    if ($stmt->execute()) {
        header("Location: hall_menu_management.php?update_success=1");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$result = $conn->query("SELECT * FROM banquet_hall_menu");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Menu Management</title>
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
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

        .content-wrapper {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            max-width: 1000px;
            margin: 2rem auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-section input {
            background: white;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-section input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #764ba2;
            color: white;
            font-weight: bold;
        }

        .btn-custom-edit {
            background-color: #4a90e2;
            color: white;
        }

        .btn-custom-delete {
            background-color: #e53e3e;
            color: white;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .content-wrapper {
                margin: 1rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'include\admin dashbord_sidebar.php';?>

    <div class="main-content">
        <div class="content-wrapper">
            <h2 class="text-center mb-4">Hall Menu Management</h2>
            
            <div class="form-section">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit_id ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="category_name" placeholder="Category Name" required class="form-control" value="<?= $edit_category_name ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" name="item_name" placeholder="Item Name" required class="form-control" value="<?= $edit_item_name ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="number" name="item_price" placeholder="Price (LKR)" required class="form-control" value="<?= $edit_item_price ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" name="category_type" placeholder="Category Type" required class="form-control" value="<?= $edit_category_type ?>">
                        </div>
                    </div>

                    <div class="text-center">
                        <?php if ($edit_mode): ?>
                            <button type="submit" name="update_menu" class="btn btn-success me-2">Update Menu Item</button>
                            <a href="hall_menu_management.php" class="btn btn-secondary">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="add_menu" class="btn btn-primary">Add Menu Item</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Price (LKR)</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['category_name'] ?></td>
                            <td><?= $row['item_name'] ?></td>
                            <td><?= number_format($row['item_price'], 2) ?></td>
                            <td><?= $row['category_type'] ?></td>
                            <td>
                                <a href="hall_menu_management.php?edit=<?= $row['id'] ?>" class="btn btn-custom-edit btn-sm me-1">Edit</a>
                                <a href="hall_menu_management.php?delete=<?= $row['id'] ?>" class="btn btn-custom-delete btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>