<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // First, let's verify the tables exist
    $tableCheckSQL = "
        SELECT COUNT(*) as count 
        FROM information_schema.tables 
        WHERE table_schema = ? 
        AND table_name IN ('Expenses', 'ExpenseCategories')";
    
    $stmt = $conn->prepare($tableCheckSQL);
    $stmt->bind_param("s", $dbname);
    $stmt->execute();
    $result = $stmt->get_result();
    $tableCount = $result->fetch_assoc()['count'];
    
    if ($tableCount < 2) {
        // Create tables if they don't exist
        $createCategoriesSQL = "
            CREATE TABLE IF NOT EXISTS ExpenseCategories (
                CategoryId INT PRIMARY KEY AUTO_INCREMENT,
                CategoryName VARCHAR(100) NOT NULL,
                Description TEXT,
                CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        
        $createExpensesSQL = "
            CREATE TABLE IF NOT EXISTS Expenses (
                ExpenseId INT PRIMARY KEY AUTO_INCREMENT,
                Date DATE NOT NULL,
                CategoryId INT NOT NULL,
                Amount DECIMAL(10,2) NOT NULL,
                PaymentMethod VARCHAR(50) NOT NULL,
                Vendor VARCHAR(100) NOT NULL,
                CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (CategoryId) REFERENCES ExpenseCategories(CategoryId)
            )";
        
        $conn->query($createCategoriesSQL);
        $conn->query($createExpensesSQL);
        
        // Add some default categories if the table is empty
        $checkCategoriesSQL = "SELECT COUNT(*) as count FROM ExpenseCategories";
        $result = $conn->query($checkCategoriesSQL);
        $categoryCount = $result->fetch_assoc()['count'];
        
        if ($categoryCount == 0) {
            $defaultCategories = [
                ['Utilities', 'Electricity, water, and other utility expenses'],
                ['Maintenance', 'Building and equipment maintenance'],
                ['Supplies', 'Office and hotel supplies'],
                ['Salaries', 'Employee salaries and wages'],
                ['Marketing', 'Advertising and promotional expenses']
            ];
            
            $insertCategorySQL = "INSERT INTO ExpenseCategories (CategoryName, Description) VALUES (?, ?)";
            $stmt = $conn->prepare($insertCategorySQL);
            
            foreach ($defaultCategories as $category) {
                $stmt->bind_param("ss", $category[0], $category[1]);
                $stmt->execute();
            }
        }
    }

    // Now let's verify the structure of both tables
    echo "<!-- Database structure check started -->\n";
    
    // Check Expenses table structure
    $expenseFields = $conn->query("DESCRIBE Expenses");
    echo "<!-- Expenses table structure: -->\n";
    while ($field = $expenseFields->fetch_assoc()) {
        echo "<!-- Field: " . $field['Field'] . " - Type: " . $field['Type'] . " -->\n";
    }
    
    // Check ExpenseCategories table structure
    $categoryFields = $conn->query("DESCRIBE ExpenseCategories");
    echo "<!-- ExpenseCategories table structure: -->\n";
    while ($field = $categoryFields->fetch_assoc()) {
        echo "<!-- Field: " . $field['Field'] . " - Type: " . $field['Type'] . " -->\n";
    }

    // Fetch expenses with error handling
    $expenses = [];
    $sql = "SELECT e.*, ec.CategoryName as ExpenseSource 
            FROM Expenses e 
            LEFT JOIN ExpenseCategories ec ON e.CategoryId = ec.CategoryId 
            ORDER BY e.Date DESC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
    
    // Fetch categories
    $categories = [];
    $categorySQL = "SELECT * FROM ExpenseCategories ORDER BY CategoryName";
    $categoryResult = $conn->query($categorySQL);
    
    if ($categoryResult === false) {
        throw new Exception("Category query failed: " . $conn->error);
    }
    
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }

    // Debug output
    echo "<!-- Number of expenses found: " . count($expenses) . " -->\n";
    echo "<!-- Number of categories found: " . count($categories) . " -->\n";
    
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "<!-- Error: " . htmlspecialchars($e->getMessage()) . " -->\n";
    die("An error occurred while loading the expenses. Please check the error log for details.");
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Management - Debug Mode</title>
</head>
<body>
    <div style="background-color: #f8f9fa; padding: 20px; margin: 20px; border-radius: 5px;">
        <h3>Database Debug Information:</h3>
        <pre>
Categories found: <?php echo count($categories); ?>
Expenses found: <?php echo count($expenses); ?>

Sample Category Data:
<?php print_r(array_slice($categories, 0, 2)); ?>

Sample Expense Data:
<?php print_r(array_slice($expenses, 0, 2)); ?>
        </pre>
    </div>
</body>
</html>