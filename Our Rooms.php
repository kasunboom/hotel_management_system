<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Rooms - GARDENIA Hotel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\ourrooms_style.css">

    
</head>

<body>
    <?php include 'include\navbar.php';?>
    <!-- Hero Section -->
    <div class="hero-section">
    <div class="hero-slide active" style="background: linear-gradient(rgba(102, 126, 234, 0.8), rgba(102, 126, 234, 0.8)), url('images/luxury-room.jpg') no-repeat center/cover;">
            <div class="hero-content">
                <h1 class="hero-title">Welcome Luxurious Rooms</h1>
                <p class="hero-description">Experience the perfect blend of luxury, comfort, and modern amenities. Book
                    your stay with us today!</p>
                <a href="#rooms" class="hero-btn">Explore Our Rooms</a>
            </div>
        </div>
    </div>

    <!-- Room Section -->
    <section id="rooms" class="py-5">
        <div class="container">
            <h2>Our Rooms</h2>
            <div class="row">
                <!-- Room Card Template -->
                <div class="col-md-4 mb-4">
                    <div class="card room-card">
                        <img src="images/room4.jpg" class="card-img-top room-image" alt="Deluxe Room">
                        <div class="room-info">
                            <h5>Deluxe Room</h5>
                            <p>"Cozy and elegant room offering a relaxing atmosphere with vibrant decor and amenities."
                            </p>
                            <p class="room-price">Rs6000 per day</p>
                            <a href="room booking.php" class="book-btn w-100">
                                <button>Book Now</button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card room-card">
                        <img src="images\Executive Suite.jpg" class="card-img-top room-image" alt="Executive Suite">
                        <div class="room-info">
                            <h5>Executive Suite</h5>
                            <p>"Beautifully designed room with cozy furnishings and natural tones for a serene
                                stay."</p>
                            <p class="room-price">Rs8000 per day</p>
                            <a href="room booking.php" class="book-btn w-100">
                                <button>Book Now</button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card room-card">
                        <img src="images/room3.jpg" class="card-img-top room-image" alt="Family Room">
                        <div class="room-info">
                            <h5>Family Room</h5>
                            <p>"Modern and spacious room with elegant decor, a cozy seating area, and essential
                                amenities."</p>
                            <p class="room-price">Rs11000 per day</p>
                            <a href="room booking.php" class="book-btn w-100">
                                <button>Book Now</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include 'include\main_footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>