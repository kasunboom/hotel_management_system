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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $usernameOrEmail = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Query to check user credentials
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } elseif ($user['role'] === 'user') {
                header("Location: user_dashboard.php"); // Redirect to profile page after successful login
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid role. Please contact the administrator.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid password.";
        }
    } else {
        $_SESSION['error_message'] = "No account found.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Modern Hotel Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\login_style.css">
</head>

<body>
    <div class="container">
        <div class="login-container">
            <header class="header">
                <div class="logo">
                    <i class="fas fa-hotel"></i>
                </div>
                <h1 class="tagline">Welcome Back</h1>
                <p class="subtitle">Enter your credentials to access your account</p>
            </header>

            <!-- Display session error messages if they exist -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message']; ?>
                </div>
                <?php unset($_SESSION['error_message']); // Clear the session message after displaying it ?>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Enter your username or email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-primary">Sign In</button>
            </form>
            <footer class="footer">
                <p>By continuing, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
            </footer>
        </div>
    </div>
    <script src="script\login_script.js"></script>
</body>

</html>
