<?php
session_start(); // Start the session to identify the logged-in user

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

// Initialize messages
$message = "";
$message_type = ""; // 'success' or 'error'

// Assuming user is logged in, use session to get the logged-in user ID
$user_id = $_SESSION['user_id']; // Assuming you store user ID in session after login

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Profile Update
    if (isset($_POST['username'], $_POST['email'])) {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);

        // Check if the new username or email already exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username or email already in use by another account.";
            $message_type = "error";
        } else {
            // Update username and email in the database
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);

            if ($stmt->execute()) {
                $message = "Profile updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating profile: " . $stmt->error;
                $message_type = "error";
            }

            $stmt->close();
        }
    }

    // Password Change
    if (isset($_POST['old-password'], $_POST['new-password'], $_POST['confirm-password'])) {
        $oldPassword = htmlspecialchars($_POST['old-password']);
        $newPassword = htmlspecialchars($_POST['new-password']);
        $confirmPassword = htmlspecialchars($_POST['confirm-password']);

        // Check if new password and confirm password match
        if ($newPassword === $confirmPassword) {
            // Fetch current password from database for comparison
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            $stmt->close();

            // Verify if old password is correct
            if (password_verify($oldPassword, $hashedPassword)) {
                // Hash the new password
                $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update password in the database
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $newHashedPassword, $user_id);

                if ($stmt->execute()) {
                    $message = "Password changed successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error changing password: " . $stmt->error;
                    $message_type = "error";
                }

                $stmt->close();
            } else {
                $message = "Old password is incorrect.";
                $message_type = "error";
            }
        } else {
            $message = "New passwords do not match.";
            $message_type = "error";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Page</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\settings_style.css">
</head>

<body>
    <?php include 'include\navbar.php';?>

    <div class="settings-container container mt-5">
        <h1>Settings</h1>

        <!-- Show notification message -->
        <?php if ($message !== ""): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Profile Settings -->
        <form method="POST" action="settings.php" class="settings-section">
            <h2>Profile Settings</h2>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter email">
            </div>
            <button type="submit" class="btn btn-primary save-btn">Save Changes</button>
        </form>

        <!-- Notification Settings -->
        <div class="settings-section mt-4">
            <h2>Notification Settings</h2>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="notifications">
                <label class="form-check-label" for="notifications">Enable Notifications</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="email-notifications">
                <label class="form-check-label" for="email-notifications">Email Notifications</label>
            </div>
        </div>

        <!-- Change Password -->
        <form method="POST" action="settings.php" class="settings-section mt-4">
            <h2>Change Password</h2>
            <div class="mb-3">
                <label for="old-password" class="form-label">Old Password</label>
                <input type="password" name="old-password" id="old-password" class="form-control" placeholder="Enter old password">
            </div>
            <div class="mb-3">
                <label for="new-password" class="form-label">New Password</label>
                <input type="password" name="new-password" id="new-password" class="form-control" placeholder="Enter new password">
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm-password" id="confirm-password" class="form-control" placeholder="Confirm new password">
            </div>
            <button type="submit" class="btn btn-danger save-btn">Change Password</button>
        </form>
    </div>

    <?php include 'include\main_footer.php'; ?>

    <script src="script\settings_script.js"></script>
</body>

</html>
