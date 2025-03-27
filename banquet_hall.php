<?php
// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all menus and items
$menu_query = "SELECT * FROM banquet_hall_menu ORDER BY category_name, item_name";
$menu_result = $conn->query($menu_query);

// Organize data by category
$menus = [];
if ($menu_result->num_rows > 0) {
    while ($row = $menu_result->fetch_assoc()) {
        $category_name = $row['category_name'];
        if (!isset($menus[$category_name])) {
            $menus[$category_name] = [];
        }
        $menus[$category_name][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magnificent Banquet Hall</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/banquet_hall_style.css">
</head>

<body>
    <?php include 'include\navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-slide active" style="background-image: url('images/banquet-hall2.jpg')">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to Magnificent Banquet Hall</h1>
                <p class="hero-description">Perfect for your special events with unparalleled services and elegance.</p>
                <a href="hall_booking.php" class="hero-btn">Book Now</a>
            </div>
        </div>
    </div>

    <!-- Banquet Hall Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Banquet Hall Services</h2>
                <p class="text-muted">Make your events unforgettable with our exceptional banquet hall services.</p>
            </div>
            <div class="row g-4">
                <!-- Service 1 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-balloon-heart-fill fs-1 text-danger"></i>
                            </div>
                            <h5 class="card-title fw-bold">Event Decorations</h5>
                            <p class="card-text text-muted">Beautiful decor tailored to suit the theme of your event.</p>
                        </div>
                    </div>
                </div>
                <!-- Service 2 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-music-note-beamed fs-1 text-success"></i>
                            </div>
                            <h5 class="card-title fw-bold">Sound & Lighting</h5>
                            <p class="card-text text-muted">Advanced audio and lighting systems for a perfect ambiance.</p>
                        </div>
                    </div>
                </div>
                <!-- Service 3 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-person-fill-check fs-1 text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold">Event Planning</h5>
                            <p class="card-text text-muted">Professional event planning to ensure a stress-free experience.</p>
                        </div>
                    </div>
                </div>
                <!-- Service 4 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-egg-fried fs-1 text-warning"></i>
                            </div>
                            <h5 class="card-title fw-bold">Catering Services</h5>
                            <p class="card-text text-muted">Delicious menus crafted to suit every occasion and preference.</p>
                        </div>
                    </div>
                </div>
                <!-- Service 5 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-camera-video-fill fs-1 text-info"></i>
                            </div>
                            <h5 class="card-title fw-bold">Photography & Videography</h5>
                            <p class="card-text text-muted">Capture every precious moment with our professional services.</p>
                        </div>
                    </div>
                </div>
                <!-- Service 6 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-people-fill fs-1 text-secondary"></i>
                            </div>
                            <h5 class="card-title fw-bold">Guest Management</h5>
                            <p class="card-text text-muted">Efficient guest handling to ensure a seamless event experience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Main Menu Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Menu</h2>
            <p class="text-muted">Explore our delicious main dishes and snacks.</p>
        </div>

        <!-- Display All Menus -->
        <?php
        if (!empty($menus)) {
            foreach ($menus as $category_name => $items) {
                echo "<h3 class='category-title text-center my-4'>{$category_name}</h3>";
                echo "<ul class='list-group'>"; // Use a list-group for a modern list style

                // Loop through items and display each as a list item
                foreach ($items as $item) {
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                    echo "<span>{$item['item_name']}</span>";
                    echo "</li>";
                }

                echo "</ul>";
            }
        } else {
            echo "<p class='text-center'>No menus available.</p>";
        }
        ?>
    </div>
</section>
    <section class="terms-section">
        <h2>Function Hall Information</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3>Complimentary Items</h3>
                <ul class="complimentary-list">
                    <li>Air-Conditioned Hall (5 hours free for 175+ guests)</li>
                    <li>Air-Conditioned Changing Room with bathroom</li>
                    <li>Oil Lamp</li>
                    <li>Use of Bar Area and Freezer</li>
                    <li>Bite Plates and Liquor Glasses</li>
                    <li>Cake Baskets and Knife</li>
                    <li>Table Decorations</li>
                    <li>Setback for Function (200+ guests)</li>
                    <li>Parking</li>
                 </ul>
            </div>
        </div>
    </section>
    <?php include 'include\main_footer.php';?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>