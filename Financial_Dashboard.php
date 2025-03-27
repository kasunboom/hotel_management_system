<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get today's date dynamically
$today = date('Y-m-d');

// Fetch Daily Income and Expenses Data (Including Today's Data)
$query = "
    SELECT DATE(i.date) AS date, 
           SUM(i.amount) AS daily_income, 
           COALESCE((SELECT SUM(e.Amount) FROM expenses e WHERE DATE(e.Date) = DATE(i.date)), 0) AS daily_expenses
    FROM income i
    WHERE DATE(i.date) <= CURDATE()
    GROUP BY DATE(i.date)
    ORDER BY DATE(i.date) DESC
";
$result = $conn->query($query);
$daily_data = $result->fetch_all(MYSQLI_ASSOC);

// Calculate Total Income and Expenses (Including Today)
$total_income = 0;
$total_expenses = 0;
foreach ($daily_data as $data) {
    $total_income += $data['daily_income'];
    $total_expenses += $data['daily_expenses'];
}

// Calculate Profit/Loss Summary
$net_income = $total_income - $total_expenses;
$profit_margin = ($total_income > 0) ? ($net_income / $total_income) * 100 : 0; // Ensure no division by zero
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">
    <style>
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

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-header .breadcrumb {
            font-size: 1rem;
            color: #718096;
        }

        .overview-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .overview-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .overview-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .overview-card p {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .financial-graph {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 2rem;
        }

        .financial-graph canvas {
            width: 100%;
            height: 400px;
        }

        .recent-transactions {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .recent-transactions table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-transactions th,
        .recent-transactions td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 1rem;
        }

        .recent-transactions th {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            font-weight: 600;
        }

        .recent-transactions tbody tr:hover {
            background-color: #f7fafc;
        }
    </style>
</head>

<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <div class="content-area">
        <div class="page-header">
            <h1>Financial Dashboard</h1>
        </div>

        <div class="overview-cards">
            <div class="overview-card">
                <h3>Total Income and Expenses</h3>
                <p><strong>Income (Total):</strong> LKR<?php echo number_format($total_income, 0, ',', '.'); ?></p>
                <p><strong>Expenses (Total):</strong> LKR<?php echo number_format($total_expenses, 0, ',', '.'); ?></p>
                <p><strong>Net Profit:</strong> LKR<?php echo number_format($net_income, 0, ',', '.'); ?></p>
            </div>

            <div class="overview-card">
                <h3>Profit/Loss Summary</h3>
                <p><strong>Net Income:</strong> LKR<?php echo number_format($net_income, 0, ',', '.'); ?></p>
                <p><strong>Profit Margin:</strong> <?php echo number_format($profit_margin, 2); ?>%</p>
            </div>
        </div>

        <div class="financial-graph">
            <h3>Daily Income and Expenses (Last 7 Days)</h3>
            <canvas id="dailyIncomeExpensesChart"></canvas>
        </div>
    </div>

    <script>
        var dailyCtx = document.getElementById('dailyIncomeExpensesChart').getContext('2d');
        var dailyIncomeExpensesChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($daily_data, 'date')); ?>,
                datasets: [{
                    label: 'Daily Income (LKR)',
                    data: <?php echo json_encode(array_column($daily_data, 'daily_income')); ?>,
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    fill: false
                }, {
                    label: 'Daily Expenses (LKR)',
                    data: <?php echo json_encode(array_column($daily_data, 'daily_expenses')); ?>,
                    backgroundColor: '#f6ad55',
                    borderColor: '#f6ad55',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (LKR)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': LKR' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
