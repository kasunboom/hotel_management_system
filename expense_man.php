<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize message variables from session
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['messageType'] ?? '';
unset($_SESSION['message'], $_SESSION['messageType']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle expense form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addExpense'])) {
    try {
        $date = trim($_POST['date']);
        $categoryId = filter_var($_POST['categoryId'], FILTER_VALIDATE_INT);
        $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
        $paymentMethod = trim($_POST['paymentMethod']);
        $vendor = trim($_POST['vendor']);

        if (!$date || !$categoryId || !$amount || !$paymentMethod || !$vendor) {
            throw new Exception("All fields are required");
        }

        $sql = "INSERT INTO Expenses (Date, CategoryId, Amount, PaymentMethod, Vendor) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sidss", $date, $categoryId, $amount, $paymentMethod, $vendor);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "New expense added successfully!";
            $_SESSION['messageType'] = "success";
        } else {
            throw new Exception("Failed to add expense: " . $stmt->error);
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['messageType'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle category form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addCategory'])) {
    try {
        $categoryName = trim($_POST['categoryName']);

        if (!$categoryName) {
            throw new Exception("Category name is required");
        }

        $sql = "INSERT INTO ExpenseCategories (CategoryName) VALUES (?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $categoryName);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "New category added successfully!";
            $_SESSION['messageType'] = "success";
        } else {
            throw new Exception("Failed to add category: " . $stmt->error);
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['messageType'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch categories
$categories = [];
$sql = "SELECT * FROM ExpenseCategories ORDER BY CategoryName";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch expenses with category names
$expenses = [];
$sql = "SELECT e.*, ec.CategoryName as ExpenseSource 
        FROM Expenses e 
        LEFT JOIN ExpenseCategories ec ON e.CategoryId = ec.CategoryId 
        ORDER BY e.Date DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

// Calculate total expenses and other statistics
$totalExpenses = 0;
$categoryTotals = [];
$monthlyTotals = [];

foreach ($expenses as $expense) {
    $totalExpenses += $expense['Amount'];
    
    // Calculate category totals
    $category = $expense['ExpenseSource'];
    if (!isset($categoryTotals[$category])) {
        $categoryTotals[$category] = 0;
    }
    $categoryTotals[$category] += $expense['Amount'];
    
    // Calculate monthly totals
    $month = date('Y-m', strtotime($expense['Date']));
    if (!isset($monthlyTotals[$month])) {
        $monthlyTotals[$month] = 0;
    }
    $monthlyTotals[$month] += $expense['Amount'];
}

// Sort monthly totals by date
ksort($monthlyTotals);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css\expense_man_style.css">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="page-header">
            <h1>Expense Management</h1>
        </div>

        <!-- Expense Overview Cards -->
        <div class="expense-overview">
            <div class="expense-card">
                <h3>Total Expenses</h3>
                <p class="amount">LKR <?php echo number_format($totalExpenses, 2); ?></p>
            </div>
            <?php foreach ($categoryTotals as $category => $total): ?>
            <div class="expense-card">
                <h3><?php echo htmlspecialchars($category); ?></h3>
                <p class="amount">LKR <?php echo number_format($total, 2); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Expense Trends Chart -->
        <div class="expense-graph">
            <h3>Monthly Expense Trends</h3>
            <canvas id="expenseTrendsChart"></canvas>
        </div>

        <!-- Expense Transaction Table -->
        <div class="expense-transactions">
            <h3>Expense Transactions</h3>
            <?php if (empty($expenses)): ?>
                <div class="no-data">
                    <p>No expenses recorded yet. Add your first expense below.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['Date']); ?></td>
                                <td><?php echo htmlspecialchars($expense['ExpenseSource']); ?></td>
                                <td>LKR <?php echo number_format($expense['Amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($expense['PaymentMethod']); ?></td>
                                <td><?php echo htmlspecialchars($expense['Vendor']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <button id="addExpenseBtn" onclick="openModal('expenseModal')">Add Expense</button>
            <button id="addCategoryBtn" onclick="openModal('addCategoryModal')">Add Category</button>
        </div>

        <!-- Modal for Adding Expense -->
        <div id="expenseModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('expenseModal')">&times;</span>
                <h2>Add Expense</h2>
                <form method="POST" action="">
                    <input type="hidden" name="addExpense" value="1">
                    
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>

                    <label for="categoryId">Category:</label>
                    <select id="categoryId" name="categoryId" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['CategoryId']); ?>">
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="amount">Amount (LKR):</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>

                    <label for="paymentMethod">Payment Method:</label>
                    <select id="paymentMethod" name="paymentMethod" required>
                        <option value="">Select payment method</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Cash">Cash</option>
                        <option value="Online Transfer">Online Transfer</option>
                    </select>

                    <label for="vendor">Vendor:</label>
                    <input type="text" id="vendor" name="vendor" required>

                    <button type="submit">Add Expense</button>
                </form>
            </div>
        </div>

        <!-- Modal for Adding Expense Category -->
        <div id="addCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addCategoryModal')">&times;</span>
                <h2>Add Expense Category</h2>
                <form method="POST" action="">
                    <input type="hidden" name="addCategory" value="1">
                    
                    <label for="categoryName">Category Name:</label>
                    <input type="text" id="categoryName" name="categoryName" required>

                    <button type="submit">Add Category</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Chart initialization
        const ctx = document.getElementById('expenseTrendsChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthlyTotals); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData).map(date => {
                    const [year, month] = date.split('-');
                    return new Date(year, month - 1).toLocaleDateString('default', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Monthly Expenses (LKR)',
                    data: Object.values(monthlyData),
                    fill: false,
                    borderColor: '#ff6b6b',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'LKR ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'LKR ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>