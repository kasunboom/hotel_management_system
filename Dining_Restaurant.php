<?php session_start(); 
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to make a booking.'); window.location.href='login.php';</script>";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Query to fetch menu items from the database
$query = "SELECT * FROM menu";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Restaurant - GARDENIA Hotel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #5560ea;
            --accent: #4a55e7;
            --highlight: #4051e2;
            --hover: #3545df;
            --light: #ecf0f1;
            --background: #2d3580;
            --footer-bg: #2d2f3a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            margin: 0;
            background-color: var(--background);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            width: 90%;
            max-width: 800px;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease;
        }

        .hero-description {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease 0.2s;
        }

        .hero-slide.active .hero-title,
        .hero-slide.active .hero-description {
            opacity: 1;
            transform: translateY(0);
        }

        .hero-btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            color: white;
            background-color: var(--accent);
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease 0.4s;
        }

        .hero-slide.active .hero-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .hero-btn:hover {
            background-color: var(--hover);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Alternative Menu Section Design */
.menu-section {
    padding: 5rem 0;
    background-color: #f8f9fa;
    position: relative;
}

.menu-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.menu-header {
    text-align: center;
    margin-bottom: 3.5rem;
}

.menu-title {
    color: var(--background);
    font-size: 2.8rem;
    font-weight: 700;
    position: relative;
    display: inline-block;
    padding-bottom: 15px;
}

.menu-title::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background-color: var(--accent);
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.menu-box {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.menu-box:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

.menu-table {
    width: 100%;
    border-collapse: collapse;
    height: 100%;
}

.menu-table td {
    padding: 1.2rem;
}

.dish-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 0.8rem;
    margin-bottom: 0.8rem;
}

.dish-name {
    font-weight: 600;
    font-size: 1.3rem;
    color: var(--background);
    margin: 0;
}

.dish-price {
    font-weight: 700;
    color: var(--accent);
    font-size: 1.2rem;
    white-space: nowrap;
    margin-left: 1rem;
}

.dish-description {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.5;
}

.special-tag {
    display: inline-block;
    background-color: var(--accent);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    padding: 0.3rem 0.8rem;
    border-radius: 4px;
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .menu-section {
        padding: 3rem 0;
    }
    
    .menu-title {
        font-size: 2.2rem;
    }
    
    .menu-grid {
        grid-template-columns: 1fr;
    }
    
    .dish-name {
        font-size: 1.2rem;
    }
}
    </style>
</head>

<body>
    <?php include 'include/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-slide active"
            style="background: linear-gradient(rgba(102, 126, 234, 0.8), rgba(102, 126, 234, 0.8)), url('images/luxury-room.jpg') no-repeat center/cover;">
            <div class="hero-content">
                <h1 class="hero-title">Welcome Dining Restaurant</h1>
                <p class="hero-description">Indulge in a delightful dining experience with exquisite flavors, elegant ambiance, and exceptional service. Reserve your table today and savor every moment!</p>
                <a href="Reserve_table.php" class="hero-btn">Reserve your table</a>
            </div>
        </div>
    </div>

   <!-- Menu Section - REVISED DESIGN -->
<section class="menu-section">
    <div class="menu-container">
        <div class="menu-header">
            <h2 class="menu-title">Gourmet Selection</h2>
        </div>
        
        <div class="menu-grid">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="menu-box">
                    <table class="menu-table">
                        <tr>
                            <td>
                                <div class="dish-header">
                                    <h3 class="dish-name"><?php echo $row['name']; ?></h3>
                                    <span class="dish-price">LKR <?php echo number_format($row['price'], 2); ?></span>
                                </div>
                                <div class="dish-description">
                                    <?php echo $row['description']; ?>
                                </div>
                                <div>
                                    <span class="special-tag">Chef's Pick</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

    <?php include 'include/main_footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php 
// Close the database connection
mysqli_close($conn); 
?>