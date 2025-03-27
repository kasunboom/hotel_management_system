<?php
session_start(); // Start session at the top of the script

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Employees for the dropdown and list
$employeeSql = "SELECT * FROM employees";
$employeeResult = $conn->query($employeeSql);

// Fetch Payroll Records
$payrollSql = "SELECT pr.*, e.name FROM payroll_records pr JOIN employees e ON pr.employee_id = e.id";
$payrollResult = $conn->query($payrollSql);

// Handle the form submission for adding a payroll record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addPayrollRecord'])) {
    $employeeId = $_POST['employeeName']; // Employee ID from dropdown
    $paymentDate = $_POST['paymentDate'];
    $hoursWorked = $_POST['hoursWorked'] ? $_POST['hoursWorked'] : NULL; // Handle nullable value for hoursWorked
    $salaryPaid = floatval($_POST['salaryPaid']); // Ensure salaryPaid is a float
    $deductions = floatval($_POST['deductions']); // Ensure deductions are a float
    $bonuses = floatval($_POST['bonuses']); // Ensure bonuses are a float

    // Prepare the statement with the correct bind_param
    $stmt = $conn->prepare("INSERT INTO payroll_records (employee_id, payment_date, hours_worked, salary_paid, deductions, bonuses) VALUES (?, ?, ?, ?, ?, ?)");

    // Bind parameters
    if ($hoursWorked === null) {
        $stmt->bind_param("issddd", $employeeId, $paymentDate, $hoursWorked, $salaryPaid, $deductions, $bonuses);
    } else {
        $stmt->bind_param("issddd", $employeeId, $paymentDate, $hoursWorked, $salaryPaid, $deductions, $bonuses);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect after form submission to prevent resubmission on page reload
        header("Location: payroll_man.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // Close the prepared statement
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="css/payroll_man_style.css" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <!-- Header -->
        <div class="page-header">
            <h1>Payroll Management</h1>
        </div>

        <!-- Employee List -->
        <div class="employee-list">
            <h3>Employee List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Job Title</th>
                        <th>Salary/Hourly Rate</th>
                        <th>Employment Status</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($employeeResult->num_rows > 0) {
                        while ($row = $employeeResult->fetch_assoc()) {
                            echo "<tr>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['nic_no'] . "</td>
                                <td>LKR " . number_format($row['basic_salary'], 2) . "</td>
                                <td>" . $row['employment_status'] . "</td>
                                <td>" . $row['role'] . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No employees found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Payroll Records -->
        <div class="payroll-records">
            <h3>Payroll Records</h3>
            <table id="payrollRecordsTable">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Payment Date</th>
                        <th>Hours Worked</th>
                        <th>Salary Paid</th>
                        <th>Deductions</th>
                        <th>Bonuses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($payrollResult->num_rows > 0) {
                        while ($row = $payrollResult->fetch_assoc()) {
                            echo "<tr>
                                <td>" . $row['name'] . "</td>
                                <td>" . $row['payment_date'] . "</td>
                                <td>" . ($row['hours_worked'] ? $row['hours_worked'] : '-') . "</td>
                                <td>LKR " . number_format($row['salary_paid'], 2) . "</td>
                                <td>LKR " . number_format($row['deductions'], 2) . "</td>
                                <td>LKR " . number_format($row['bonuses'], 2) . "</td>
                                <td><button class='invoice-btn' onclick=\"generateInvoice('" . $row['name'] . "', '" . $row['payment_date'] . "', 'LKR " . number_format($row['salary_paid'], 2) . "', 'LKR " . number_format($row['deductions'], 2) . "', 'LKR " . number_format($row['bonuses'], 2) . "')\">Generate Invoice</button></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No payroll records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add Payroll Record Form -->
        <div class="payroll-record-form">
            <h3>Add Payroll Record</h3>
            <form id="payrollRecordForm" action="payroll_man.php" method="POST">
                <label for="employeeName">Employee Name</label>
                <select name="employeeName" required>
                    <option value="">Select Employee</option>
                    <?php
                    // Populate employee dropdown
                    $employeeResult->data_seek(0); // Reset result pointer to the beginning
                    while ($row = $employeeResult->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . " - " . $row['role'] . "</option>"; // Added role to dropdown
                    }
                    ?>
                </select>

                <label for="paymentDate">Payment Date</label>
                <input type="date" name="paymentDate" required />

                <label for="hoursWorked">Hours Worked</label>
                <input type="number" name="hoursWorked" placeholder="Enter hours worked (if applicable)" />

                <label for="salaryPaid">Salary Paid</label>
                <input type="number" name="salaryPaid" placeholder="Enter salary paid" required />

                <label for="deductions">Deductions</label>
                <input type="number" name="deductions" placeholder="Enter deductions" />

                <label for="bonuses">Bonuses</label>
                <input type="number" name="bonuses" placeholder="Enter bonuses" />

                <button type="submit" name="addPayrollRecord">Add Payroll Record</button>
            </form>
        </div>
    </div>

    <script>
        // Function to generate PDF invoice for payroll record
        function generateInvoice(employeeName, paymentDate, salaryPaid, deductions, bonuses) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text(`Payroll Invoice for ${employeeName}`, 20, 20);
            doc.text(`Payment Date: ${paymentDate}`, 20, 30);
            doc.text(`Salary Paid: ${salaryPaid}`, 20, 40);
            doc.text(`Deductions: ${deductions}`, 20, 50);
            doc.text(`Bonuses: ${bonuses}`, 20, 60);

            doc.save(`${employeeName}_Payroll_Invoice.pdf`);
        }
    </script>
</body>
</html>
