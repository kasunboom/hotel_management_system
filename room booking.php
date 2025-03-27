<?php
// Start session
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Define room capacities
$roomCapacities = [
    'deluxe'    => 5,
    'executive' => 2,
    'family'    => 1
];

$bookingMessage = '';
$alertClass = 'alert-success';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $guestName    = trim($_POST['guest_name']);
    $guestEmail   = trim($_POST['email']);
    $guestPhone   = trim($_POST['phone']);
    $guestCount   = intval($_POST['guests']);
    $checkinDate  = $_POST['check_in'];
    $checkoutDate = $_POST['check_out'];
    $roomType     = $_POST['room_type'];
    $services     = isset($_POST['services']) ? $_POST['services'] : [];
    $totalAmount  = $_POST['total_amount'];
    
    $servicesStr = implode(',', $services);
    $totalNumeric = floatval(preg_replace('/[^\d.]/', '', $totalAmount));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                           WHERE room_type = :room_type 
                           AND checkin_date < :checkout_date 
                           AND checkout_date > :checkin_date");
    $stmt->execute([
        ':room_type'    => $roomType,
        ':checkin_date' => $checkinDate,
        ':checkout_date'=> $checkoutDate
    ]);
    $existingBookings = $stmt->fetchColumn();
    
    if ($existingBookings < $roomCapacities[$roomType]) {
        $insertStmt = $pdo->prepare("INSERT INTO bookings (guest_name, guest_email, guest_phone, guest_count, checkin_date, checkout_date, room_type, services, total_amount) 
                                     VALUES (:guest_name, :guest_email, :guest_phone, :guest_count, :checkin_date, :checkout_date, :room_type, :services, :total_amount)");
        $insertStmt->execute([
            ':guest_name'    => $guestName,
            ':guest_email'   => $guestEmail,
            ':guest_phone'   => $guestPhone,
            ':guest_count'   => $guestCount,
            ':checkin_date'  => $checkinDate,
            ':checkout_date' => $checkoutDate,
            ':room_type'     => $roomType,
            ':services'      => $servicesStr,
            ':total_amount'  => $totalNumeric
        ]);
        
        echo "<script>alert('Booking Successful!'); window.location.href='hotel_booking.php';</script>";
    } else {
        echo "<script>alert('Sorry, all " . ucfirst($roomType) . " rooms are booked for the selected dates. Please select another date or room type.'); window.location.href='hotel_booking.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Room Booking - Gardenia Hotel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="css\sidebar_admin_style.css" rel="stylesheet">
    <link href="css/admin_dashboard_style.css" rel="stylesheet">

    <style>
        :root {
            --primary: #667eea;
            --secondary: #5560ea;
            --accent: #4a55e7;
            --highlight: #4051e2;
            --hover: #3545df;
            --light: #ecf0f1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            padding-top: 60px;
        }
        .page-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }


        .reservation-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background-color: var(--secondary);
            border-radius: 10px;
            color: white;
        }

        .reservation-form input,
        .reservation-form select,
        .reservation-form textarea {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .reservation-form button {
            background-color: var(--highlight);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-weight: 600;
        }

        .reservation-form button:hover {
            background-color: var(--hover);
        }

        .services-group {
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .services-group label {
            margin-right: 1rem;
            cursor: pointer;
        }

        .services-group input[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
<?php include 'include\admin dashbord_sidebar.php';?>

    <section id="booking">
        <div class="container">
        <div class="page-header">
        <h1>Hotel Room Booking</h1>
    </div>
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="reservation-form">
    <label for="guest_name" class="form-label">Guest Name</label>
    <input type="text" class="form-control" id="guest_name" name="guest_name" required placeholder="Enter your full name">

    <label for="email" class="form-label">Email Address</label>
    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email address">

    <label for="phone" class="form-label">Phone Number</label>
    <input type="tel" class="form-control" id="phone" name="phone" required placeholder="Enter your phone number">

    <label for="guests" class="form-label">Number of Guests</label>
    <input type="number" class="form-control" id="guests" name="guests" min="1" required placeholder="Enter number of guests">

    <label for="check_in" class="form-label">Check-in Date</label>
    <input type="date" class="form-control" id="check_in" name="check_in" required placeholder="Select your check-in date">

    <label for="check_out" class="form-label">Check-out Date</label>
    <input type="date" class="form-control" id="check_out" name="check_out" required placeholder="Select your check-out date">

    <label for="room_type" class="form-label">Room Type</label>
    <select class="form-control" id="room_type" name="room_type" required>
        <option value="deluxe">Deluxe Room</option>
        <option value="executive">Executive Room</option>
        <option value="family">Family Room</option>
    </select>

    <div class="services-group">
        <label class="form-label">Additional Services</label>
        <div>
            <label><input type="checkbox" name="services[]" value="breakfast"> Breakfast</label>
            <label><input type="checkbox" name="services[]" value="lunch"> Lunch</label>
            <label><input type="checkbox" name="services[]" value="dinner"> Dinner</label>
            <label><input type="checkbox" name="services[]" value="wifi"> Wi-Fi</label>
            <label><input type="checkbox" name="services[]" value="gym"> Gym</label>
        </div>
    </div>

    <label for="total_amount" class="form-label">Total Amount</label>
    <input type="text" class="form-control" id="total_amount" name="total_amount" readonly placeholder="Total amount will be calculated">

    <button type="submit" class="btn">Submit Booking</button>
</form>

        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const roomTypeField = document.getElementById("room_type");
            const totalAmountField = document.getElementById("total_amount");

            const roomPrices = {
                deluxe: 6000,
                executive: 8000,
                family: 10000
            };

            const servicePrices = {
                breakfast: 1250,
                lunch: 1400,
                dinner: 1400,
                wifi: 500,
                gym: 2000
            };

            function calculateTotal() {
                let total = 0;
                const roomType = roomTypeField.value;
                if (roomPrices[roomType]) {
                    total += roomPrices[roomType];
                }
                document.querySelectorAll("input[name='services[]']:checked").forEach(function(service) {
                    if (servicePrices[service.value]) {
                        total += servicePrices[service.value];
                    }
                });
                totalAmountField.value = "LKR " + total.toLocaleString();
            }

            roomTypeField.addEventListener("change", calculateTotal);
            document.querySelectorAll("input[name='services[]']").forEach(function(service) {
                service.addEventListener("change", calculateTotal);
            });

            calculateTotal();
        });
    </script>

</body>
</html>