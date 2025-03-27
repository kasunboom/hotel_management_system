<?php
session_start();

// Enable error reporting for debugging (remove these lines in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host     = 'localhost';
$dbname   = 'hotel_management';  // Replace with your actual database name
$username = 'root';              // Replace with your database username
$password = '';                  // Replace with your database password

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// -----------------------------------------------------------------------------
// DELETE FUNCTIONALITY
// -----------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM rooms WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    try {
        $stmt->execute();
        $_SESSION['message'] = "Room deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting room: " . $e->getMessage();
    }
    // Redirect to clear the GET parameters
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// -----------------------------------------------------------------------------
// EDIT FUNCTIONALITY: Load the room data if editing
// -----------------------------------------------------------------------------
$editMode = false;
$editRoom = [
    'id'           => '',
    'room_number'  => '',
    'room_type'    => '',
    'price'        => '',
    'availability' => ''
];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM rooms WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    try {
        $stmt->execute();
        $editRoom = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editRoom) {
            $editMode = true;
        } else {
            $_SESSION['message'] = "Room not found.";
            header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error fetching room: " . $e->getMessage();
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    }
}

// -----------------------------------------------------------------------------
// PROCESS FORM SUBMISSION (for both insert and update)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $roomNumber   = isset($_POST['roomNumber'])   ? trim($_POST['roomNumber'])   : '';
    $roomType     = isset($_POST['roomType'])     ? trim($_POST['roomType'])     : '';
    $price        = isset($_POST['price'])        ? trim($_POST['price'])        : '';
    $availability = isset($_POST['availability']) ? trim($_POST['availability']) : '';
    $id           = isset($_POST['id'])           ? trim($_POST['id'])           : ''; // Hidden input for update

    if ($roomNumber && $roomType && $price && $availability) {
        if ($id) {
            // Update an existing room record
            $sql = "UPDATE rooms SET room_number = :room_number, room_type = :room_type, price = :price, availability = :availability WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':room_number', $roomNumber);
            $stmt->bindParam(':room_type', $roomType);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':availability', $availability);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            try {
                $stmt->execute();
                $_SESSION['message'] = "Room updated successfully!";
            } catch (PDOException $e) {
                $_SESSION['message'] = "Error updating room: " . $e->getMessage();
            }
        } else {
            // Insert a new room record
            $sql = "INSERT INTO rooms (room_number, room_type, price, availability)
                    VALUES (:room_number, :room_type, :price, :availability)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':room_number', $roomNumber);
            $stmt->bindParam(':room_type', $roomType);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':availability', $availability);
            try {
                $stmt->execute();
                $_SESSION['message'] = "Room added successfully!";
            } catch (PDOException $e) {
                $_SESSION['message'] = "Error adding room: " . $e->getMessage();
            }
        }
        // Redirect to avoid resubmission and clear GET parameters
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all fields.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// -----------------------------------------------------------------------------
// Retrieve flash message (if any)
// -----------------------------------------------------------------------------
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// -----------------------------------------------------------------------------
// Fetch all room data to display in the table
// -----------------------------------------------------------------------------
try {
    $sql = "SELECT * FROM rooms";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching rooms: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room Management</title>
  <link href="css\sidebar_admin_style.css" rel="stylesheet">
  <link href="css/admin_dashboard_style.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    /* General Styles */
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
    .card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 1.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.18);
      margin-bottom: 2rem;
    }
    /* Header Section */
    .page-header {
      margin-bottom: 1.5rem;
    }
    .page-header h1 {
      font-size: 1.75rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .page-header .breadcrumb {
      font-size: 0.875rem;
      color: #718096;
    }
    /* Form Styles */
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 0.5rem;
      display: block;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.75rem;
      border-radius: 10px;
      border: 1px solid rgba(0, 0, 0, 0.2);
      font-size: 0.875rem;
    }
    /* Buttons */
    .button {
      display: inline-flex;
      align-items: center;
      padding: 0.75rem 1.25rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: white;
      border-radius: 10px;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
    }
    .button.save {
      background: #38b2ac;
    }
    .button.save:hover {
      background: #2c7a7b;
    }
    .button.edit {
      background: #4a90e2;
    }
    .button.edit:hover {
      background: #357ab8;
    }
    .button.delete {
      background: #e53e3e;
    }
    .button.delete:hover {
      background: #c53030;
    }
    .button i {
      margin-right: 0.5rem;
    }
    /* Table Styles */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    table th, table td {
      padding: 0.75rem;
      border: 1px solid #ccc;
      text-align: left;
    }
    table td.actions {
      white-space: nowrap;
    }
    a.button {
      text-decoration: none;
      color: white;
      margin-right: 0.5rem;
    }
  </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

  <div class="content-area">
    <!-- Header -->
    <div class="page-header">
      <h1>Room Management</h1>
    </div>

    <!-- Display Message -->
    <?php if (!empty($message)): ?>
      <div class="card">
        <p><?php echo htmlspecialchars($message); ?></p>
      </div>
    <?php endif; ?>

    <!-- Room Management Form -->
    <div class="card">
      <h2><?php echo $editMode ? 'Edit Room' : 'Add Room'; ?></h2>
      <form id="roomForm" method="POST">
        <?php if ($editMode): ?>
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($editRoom['id']); ?>">
        <?php endif; ?>
        <!-- Room Number -->
        <div class="form-group">
          <label for="roomNumber">Room Number</label>
          <input type="text" id="roomNumber" name="roomNumber" required placeholder="Enter Room Number" value="<?php echo htmlspecialchars($editMode ? $editRoom['room_number'] : ''); ?>">
        </div>
        <!-- Room Type -->
        <div class="form-group">
          <label for="roomType">Room Type</label>
          <select id="roomType" name="roomType" required>
            <option value="">Select Room Type</option>
            <option value="Deluxe Room" <?php echo ($editMode && $editRoom['room_type'] == 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room</option>
            <option value="Executive Room" <?php echo ($editMode && $editRoom['room_type'] == 'Executive Room') ? 'selected' : ''; ?>>Executive Room</option>
            <option value="Family Room" <?php echo ($editMode && $editRoom['room_type'] == 'Family Room') ? 'selected' : ''; ?>>Family Room</option>
          </select>
        </div>
        <!-- Price -->
        <div class="form-group">
          <label for="price">Price</label>
          <input type="number" id="price" name="price" required placeholder="Enter Price" step="0.01" value="<?php echo htmlspecialchars($editMode ? $editRoom['price'] : ''); ?>">
        </div>
        <!-- Availability -->
        <div class="form-group">
          <label for="availability">Availability</label>
          <select id="availability" name="availability" required>
            <option value="">Select Availability</option>
            <option value="Available" <?php echo ($editMode && $editRoom['availability'] == 'Available') ? 'selected' : ''; ?>>Available</option>
            <option value="Occupied" <?php echo ($editMode && $editRoom['availability'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
            <option value="Maintenance" <?php echo ($editMode && $editRoom['availability'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
          </select>
        </div>
        <!-- Submit Button -->
        <div class="form-group">
          <button type="submit" class="button save">
            <i class="fas fa-save"></i> <?php echo $editMode ? 'Update Room' : 'Save Changes'; ?>
          </button>
        </div>
      </form>
    </div>

    <!-- Display Rooms -->
    <div class="card">
      <h2>Existing Rooms</h2>
      <?php if (!empty($rooms)): ?>
      <table>
        <thead>
          <tr>
            <th>Room Number</th>
            <th>Room Type</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $room): ?>
            <tr>
              <td><?php echo htmlspecialchars($room['room_number']); ?></td>
              <td><?php echo htmlspecialchars($room['room_type']); ?></td>
              <td><?php echo htmlspecialchars($room['price']); ?></td>
              <td><?php echo htmlspecialchars($room['availability']); ?></td>
              <td class="actions">
                <a class="button edit" href="?action=edit&id=<?php echo htmlspecialchars($room['id']); ?>">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a class="button delete" onclick="return confirm('Are you sure you want to delete this room?');" href="?action=delete&id=<?php echo htmlspecialchars($room['id']); ?>">
                  <i class="fas fa-trash"></i> Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No rooms found.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
