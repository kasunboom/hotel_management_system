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

// Initialize messages
$message = "";
$message_type = ""; // 'success' or 'error'

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_raw = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Check if passwords match
    if ($password_raw !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_type = "error";
    } else {
        // Hash the password
        $password = password_hash($password_raw, PASSWORD_BCRYPT);

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            // Set success message and redirect to login page
            $message = "Registration successful! Redirecting to login page...";
            $message_type = "success";
            header("Refresh: 2; URL=login.php"); // Redirect after 2 seconds
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\register_style.css"> <!-- Link to your CSS file -->
</head>
<body>

    <div class="container">
        <div class="registration-container">
            <header class="header">
                <div class="logo">
                    <i class="fas fa-hotel"></i>
                </div>
                <h1 class="tagline">Create Your Account</h1>
                <p class="subtitle">Sign up to access exclusive features</p>
            </header>

            <!-- Display Message -->
            <?php if ($message !== ""): ?>
                <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>" role="alert">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="index.php" method="POST" class="registration-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <i class="fas fa-check-circle"></i>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn-primary">Register</button>

                <a href="login.php" class="text-center">Already have an account? Login here</a>
            </form>

            <footer class="footer">
                <p>By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
            </footer>
        </div>
    </div>

    <script src="script\register_script.js"></script> <!-- Link to your JS file -->

</body>
</html>
