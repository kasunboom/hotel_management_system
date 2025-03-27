<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle Add Employee
if (isset($_POST['add_employee'])) {
    $name = htmlspecialchars($_POST['name']);
    $nic_no = htmlspecialchars($_POST['nic_no']);
    $role = htmlspecialchars($_POST['role']);
    $basic_salary = htmlspecialchars($_POST['basic_salary']);
    $joined_date = htmlspecialchars($_POST['joined_date']);
    $employment_status = htmlspecialchars($_POST['employment_status']);

    $query = "INSERT INTO employees (name, nic_no, role, basic_salary, joined_date, employment_status) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $name, $nic_no, $role, $basic_salary, $joined_date, $employment_status);
    $stmt->execute();
    header("Location: employee_man.php");
    exit();
}

// Handle Delete Employee
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM employees WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: employee_man.php");
    exit();
}

// Handle Edit Employee
if (isset($_POST['edit_employee'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $nic_no = htmlspecialchars($_POST['nic_no']);
    $role = htmlspecialchars($_POST['role']);
    $basic_salary = htmlspecialchars($_POST['basic_salary']);
    $joined_date = htmlspecialchars($_POST['joined_date']);
    $employment_status = htmlspecialchars($_POST['employment_status']);

    $query = "UPDATE employees SET name=?, nic_no=?, role=?, basic_salary=?, joined_date=?, employment_status=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $name, $nic_no, $role, $basic_salary, $joined_date, $employment_status, $id);
    $stmt->execute();
    header("Location: employee_man.php");
    exit();
}

// Handle Search and Filter
$search = isset($_POST['search']) ? trim($_POST['search']) : "";
$role_filter = isset($_POST['role_filter']) ? trim($_POST['role_filter']) : "";

// Build the SQL query based on filters
$sql = "SELECT * FROM employees WHERE name LIKE ? AND (role LIKE ? OR ? = '')";
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
    <title>Employee Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">


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
            background-color:rgb(16, 134, 253);
            color: black;
            font-weight: bold;
        }

        td .status {
            padding: 0.5rem;
            border-radius: 10px;
        }

        td .status.pending {
            background-color:rgb(27, 177, 215);
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
        <h1>Employee Management</h1>
    </div>

    <!-- Filter and Search -->
    <form method="POST" action="">
        <div class="filters">
            <input class="search-bar" type="text" name="search" placeholder="Search by name" value="<?php echo htmlspecialchars($search); ?>">
            <select class="filter-dropdown" name="role_filter" id="role_filter">
                <option value="">Select Role</option>
                <option value="Manager" <?php if ($role_filter == 'Manager') echo 'selected'; ?>>Manager</option>
                <option value="Waiter" <?php if ($role_filter == 'Waiter') echo 'selected'; ?>>Waiter</option>
                <option value="Chef" <?php if ($role_filter == 'Chef') echo 'selected'; ?>>Chef</option>
                <option value="Kitchen Helper" <?php if ($role_filter == 'Kitchen Helper') echo 'selected'; ?>>Kitchen Helper</option>
            </select>
            <button type="submit" class="button">Filter</button>
        </div>
    </form>

    <!-- Add Employee Form -->
    <button class="button" onclick="toggleModal('addEmployeeModal')">Add Employee</button>


    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <h3>Add Employee</h3>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <div class="form-group">
                    <label for="nic_no">NIC No</label>
                    <input type="text" name="nic_no" id="nic_no" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="Manager">Manager</option>
                        <option value="Waiter">Waiter</option>
                        <option value="Chef">Chef</option>
                        <option value="Kitchen Helper">Kitchen Helper</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="basic_salary">Basic Salary</label>
                    <input type="number" name="basic_salary" id="basic_salary" required>
                </div>
                <div class="form-group">
                    <label for="joined_date">Joined Date</label>
                    <input type="date" name="joined_date" id="joined_date" required>
                </div>
                <div class="form-group">
                    <label for="employment_status">Employment Status</label>
                    <select name="employment_status" id="employment_status" required>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                    </select>
                </div>
                <button type="submit" name="add_employee" class="button">Add Employee</button>
            </form>
            <button class="button close" onclick="toggleModal('addEmployeeModal')">Close</button>



        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal" id="editEmployeeModal">
        <div class="modal-content">
            <h3>Edit Employee</h3>

            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_name">Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_nic_no">NIC No</label>
                    <input type="text" name="nic_no" id="edit_nic_no" required>
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select name="role" id="edit_role" required>
                        <option value="Manager">Manager</option>
                        <option value="Waiter">Waiter</option>
                        <option value="Chef">Chef</option>
                        <option value="Kitchen Helper">Kitchen Helper</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_basic_salary">Basic Salary</label>
                    <input type="number" name="basic_salary" id="edit_basic_salary" required>
                </div>
                <div class="form-group">
                    <label for="edit_joined_date">Joined Date</label>
                    <input type="date" name="joined_date" id="edit_joined_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_employment_status">Employment Status</label>
                    <select name="employment_status" id="edit_employment_status" required>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                    </select>
                </div>
                <button type="submit" name="edit_employee" class="button">Update Employee</button>
            </form>
            <button class="button close" onclick="toggleModal('editEmployeeModal')">Close</button>
        </div>
    </div>

    <!-- Employee List Table -->
    <div class="card">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>NIC No</th>
                    <th>Role</th>
                    <th>Basic Salary</th>
                    <th>Joined Date</th>
                    <th>Employment Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr data-id="<?php echo $row['id']; ?>">
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="name"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="nic_no"><?php echo htmlspecialchars($row['nic_no']); ?></td>
                        <td class="role"><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="basic_salary"><?php echo htmlspecialchars($row['basic_salary']); ?></td>
                        <td class="joined_date"><?php echo htmlspecialchars($row['joined_date']); ?></td>
                        <td class="employment_status"><?php echo htmlspecialchars($row['employment_status']); ?></td>
                        <td>
                            <button class="button" onclick="editEmployee(<?php echo $row['id']; ?>)">Edit</button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="button" style="background-color: #e74c3c;">Delete</a>
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

    // Edit Employee
    function editEmployee(employeeId) {
        const employeeRow = document.querySelector(`tr[data-id="${employeeId}"]`);
        document.getElementById('edit_id').value = employeeId;
        document.getElementById('edit_name').value = employeeRow.querySelector('.name').textContent;
        document.getElementById('edit_nic_no').value = employeeRow.querySelector('.nic_no').textContent;
        document.getElementById('edit_role').value = employeeRow.querySelector('.role').textContent;
        document.getElementById('edit_basic_salary').value = employeeRow.querySelector('.basic_salary').textContent;
        document.getElementById('edit_joined_date').value = employeeRow.querySelector('.joined_date').textContent;
        document.getElementById('edit_employment_status').value = employeeRow.querySelector('.employment_status').textContent;
        toggleModal('editEmployeeModal');
    }
</script>

</body>
</html>