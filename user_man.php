<?php
// Database connection
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

// Handle Add User
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Validate input
    if (!empty($username) && !empty($email) && !empty($role) && !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Hash the password before saving it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $role, $hashed_password);
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Failed to add user.";
            }
            $stmt->close();
        } else {
            $error = "Invalid email address.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Failed to delete user.";
    }
    $stmt->close();
}

// Handle Edit User
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Validate input
    if (!empty($username) && !empty($email) && !empty($role)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Hash the password if it's updated
            $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

            if ($hashed_password) {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?");
                $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
                $stmt->bind_param("sssi", $username, $email, $role, $id);
            }

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Failed to update user.";
            }
            $stmt->close();
        } else {
            $error = "Invalid email address.";
        }
    } else {
        $error = "Username, email, and role are required.";
    }
}

// Handle Search and Filter
$search = isset($_POST['search']) ? trim($_POST['search']) : "";
$role_filter = isset($_POST['role_filter']) ? trim($_POST['role_filter']) : "";

$sql = "SELECT * FROM users WHERE username LIKE ? AND (role LIKE ? OR ? = '')";
$search_param = "%$search%";
$role_param = "%$role_filter%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $search_param, $role_param, $role_filter);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">

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
            margin-left: 280px;
            padding: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .filters {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filters select, .filters input {
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            font-size: 0.875rem;
        }

        /* Modal Styles */
    /* Updated Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        background-color: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 400px;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .close-modal {
        cursor: pointer;
        font-size: 24px;
        color: #aaa;
        line-height: 1;
    }

    .close-modal:hover {
        color: #666;
    }
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #38b2ac;
            color: white;
            font-weight: bold;
        }

        td .status {
            padding: 0.5rem;
            border-radius: 10px;
        }

        td .status.pending {
            background-color: #f6ad55;
            color: white;
        }

        td .status.confirmed {
            background-color: #38b2ac;
            color: white;
        }

        td .status.canceled {
            background-color: #e53e3e;
            color: white;
        }
    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

<div class="content-area">
    <div class="page-header">
        <h1>User Management</h1>
    </div>

    <!-- Filter and Search -->
    <form method="POST" action="">
        <div class="filters">
            <input class="search-bar" type="text" name="search" placeholder="Search by username" value="<?php echo htmlspecialchars($search); ?>">
            <select class="filter-dropdown" name="role_filter" id="role_filter">
                <option value="">Select Role</option>
                <option value="admin" <?php if ($role_filter == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="user" <?php if ($role_filter == 'user') echo 'selected'; ?>>User</option>
            </select>
            <button type="submit" class="button">Filter</button>
        </div>
    </form>

    <!-- Add User Form -->
    <button class="button" onclick="toggleModal('addUserModal')">Add User</button>

    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <h3>Add User</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" name="add_user" class="button">Add User</button>
            </form>
            <button class="button close" onclick="toggleModal('addUserModal')">Close</button>

        </div>
    </div>
    <!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <h3>Edit User</h3>
        <form action="" method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group">
                <label for="edit_username">Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Email</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label for="edit_role">Role</label>
                <select name="role" id="edit_role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_password">Password (Leave blank to keep unchanged)</label>
                <input type="password" name="password" id="edit_password">
            </div>
            <button type="submit" name="edit_user" class="button">Update User</button>
        </form>
        <button class="button close" onclick="toggleModal('editUserModal')">Close</button>
    </div>
</div>


    <!-- User List Table -->
    <div class="card">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="username"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="role"><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <button class="button" onclick="editUser(<?php echo $row['id']; ?>)">Edit</button>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="button" style="background-color: #e74c3c;">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Toggle modals
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        const currentDisplay = modal.style.display;
        modal.style.display = currentDisplay === 'block' ? 'none' : 'block';
    }

    // Edit User
function editUser(userId) {
    const userRow = document.querySelector(`tr[data-id="${userId}"]`);
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_username').value = userRow.querySelector('.username').textContent;
    document.getElementById('edit_email').value = userRow.querySelector('.email').textContent;
    document.getElementById('edit_role').value = userRow.querySelector('.role').textContent;
    toggleModal('editUserModal');
}

</script>

</body>
</html>
