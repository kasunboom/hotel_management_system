<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "Sorry, there was an issue connecting to our services. Please try again later.";
    exit();
}

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['feedback'], ENT_QUOTES, 'UTF-8');

    // Basic validation (e.g. check if fields are not empty)
    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "<script>alert('Thank you for your feedback!');</script>"; // Redirect after submission
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grand Plaza Hotel - Modern Luxury</title>
  <link href="css\user_dashboard.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
<?php include 'include\mainpages_navbar.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-slide active"
      style="background: linear-gradient(rgba(102, 126, 234, 0.8), rgba(102, 126, 234, 0.8)), url('images/luxury-room.jpg') no-repeat center/cover;">
      <div class="hero-content">
        <h1 class="hero-title">Luxurious Rooms</h1>
        <p class="hero-description">Experience unparalleled comfort in our elegantly designed spaces</p>
        <a href="Our Rooms.php" class="hero-btn">Book Now</a>
      </div>
    </div>
    <div class="hero-slide"
      style="background: linear-gradient(rgba(85, 96, 234, 0.8), rgba(85, 96, 234, 0.8)), url('images/banquet-hall.jpg') no-repeat center/cover;">
      <div class="hero-content">
        <h1 class="hero-title">Magnificent Banquet Hall</h1>
        <p class="hero-description">Create unforgettable memories in our grand celebration spaces</p>
        <a href="banquet_hall.php" class="hero-btn">Plan Event</a>
      </div>
    </div>
    <div class="hero-slide"
      style="background: linear-gradient(rgba(74, 85, 231, 0.8), rgba(74, 85, 231, 0.8)), url('images/fine-dining.jpg') no-repeat center/cover;">
      <div class="hero-content">
        <h1 class="hero-title">Fine Dining Restaurant</h1>
        <p class="hero-description">Indulge in culinary excellence with our master chefs</p>
        <a href="Dining_Restaurant.php" class="hero-btn">Reserve Table</a>
      </div>
    </div>
    <div class="slide-indicators">
      <button class="slide-indicator active" data-slide="0"></button>
      <button class="slide-indicator" data-slide="1"></button>
      <button class="slide-indicator" data-slide="2"></button>
    </div>
  </section>

  <!-- About Us Section -->
  <section id="about">
    <div class="container">
      <h2>About Us</h2>
      <p>Welcome to GARDENIA Hotel, where luxury meets comfort. We provide a perfect blend of style, elegance, and
        modern amenities to make every stay unforgettable.</p>
      <ul>
        <li><strong>Spacious Rooms:</strong> Designed for relaxation, equipped with all modern conveniences.</li>
        <li><strong>Banquet Hall:</strong> A versatile space for weddings, events, and meetings.</li>
        <li><strong>Restaurant:</strong> Offering a variety of delicious dishes crafted by expert chefs.</li>
      </ul>
      <p>At GARDENIA, we are dedicated to delivering exceptional hospitality, ensuring your stay is both comfortable and
        memorable.</p>
    </div>
  </section>

  <!-- Feedback Section -->
<section id="feedback">
  <div class="container">
    <h2>Feedback</h2>
    <form action="user_dashboard.php" method="POST" class="feedback-form">
      <input type="text" name="name" class="form-control" placeholder="Your Name" required>
      <input type="email" name="email" class="form-control" placeholder="Your Email" required>
      <textarea name="feedback" class="form-control" rows="4" placeholder="Your Feedback" required></textarea>
      <button type="submit">Submit Feedback</button>
    </form>
  </div>
</section>

  <!-- Loading Spinner -->
  <div id="loader" class="loader-container">
    <div class="spinner"></div>
  </div>
  <?php include 'include\main_footer.php'; ?>


  <script src="script\user_dashboard_script.js"></script>
  

</body>

</html>
