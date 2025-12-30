<?php
include "../includes/auth.php";
include "../config/db.php";
requireRole('customer');

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$initials = strtoupper(substr($username, 0, 2));

// Get user stats
$stats_query = $conn->prepare(
    "SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as upcoming_trips,
        SUM(total_amount) as total_spent
     FROM bookings 
     WHERE customer_id = ?"
);

$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

// Get upcoming trips
$upcoming_query = $conn->prepare(
    "SELECT b.*, 
            h.name as hotel_name, 
            h.image,
            d.name as destination_name,
            d.image as destination_image
     FROM bookings b
     JOIN hotels h ON b.hotel_id = h.id
     JOIN destinations d ON h.destination_id = d.id
     WHERE b.customer_id = ? AND b.status = 'confirmed' 
     AND b.check_in >= CURDATE()
     ORDER BY b.check_in ASC
     LIMIT 3"
);
$upcoming_query->bind_param("i", $user_id);
$upcoming_query->execute();
$upcoming_trips = $upcoming_query->get_result();

// Get destinations with hotels
$destinations = $conn->query("SELECT * FROM destinations ORDER BY name LIMIT 4");

// Get featured hotels (random selection)
$featured_hotels = $conn->query(
    "SELECT h.*, d.name as destination_name 
     FROM hotels h 
     JOIN destinations d ON h.destination_id = d.id 
     ORDER BY RAND() 
     LIMIT 3"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Destinations - Luxe Voyage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer_dashboard.css">
    <link rel="stylesheet" href="../assets/css/attractions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional inline styles */
        body { font-family: 'Poppins', sans-serif; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    </style>
</head>
<body>
    <div class="customer-dashboard">
        <!-- Header -->
        <header class="customer-header">
            <div class="container">
                <div class="header-container">
                    <div class="header-left">
                        <h1><i class="fas fa-crown"></i> Luxe Voyage</h1>
                        <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($username); ?>!</p>
                    </div>
                    
                    <div class="user-menu">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo $initials; ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                                <span class="user-role">Premium Traveler</span>
                            </div>
                        </div>
                        
                        <div class="header-actions">
                            <a href="../profile.php" class="header-btn secondary">
                                <i class="fas fa-user-circle"></i> Profile
                            </a>
                            <a href="../logout.php" class="header-btn secondary">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <a href="booking.php" class="header-btn primary">
                                <i class="fas fa-suitcase"></i> My Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Banner -->
        <section class="hero-banner">
            <div class="container">
                <div class="hero-content">
                    <h2>Where will your next adventure take you?</h2>
                    <p>Discover exclusive destinations and book luxury hotels with premium experiences tailored just for you.</p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-suitcase"></i>
                            </div>
                            <div class="stat-text">
                                <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-text">
                                <h3><?php echo $stats['upcoming_trips'] ?? 0; ?></h3>
                                <p>Upcoming Trips</p>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-text">
                                <h3>$<?php echo number_format($stats['total_spent'] ?? 0, 0); ?></h3>
                                <p>Total Spent</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <div class="dashboard-content">
            <div class="container">
                <!-- Search Filters -->
                <div class="search-filters">
                    <h3><i class="fas fa-search"></i> Find Your Perfect Stay</h3>
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="destination">Destination</label>
                            <select id="destination" class="filter-select">
                                <option value="">Any Destination</option>
                                <?php 
                                $all_destinations = $conn->query("SELECT * FROM destinations ORDER BY name");
                                while($dest = $all_destinations->fetch_assoc()): ?>
                                    <option value="<?php echo $dest['id']; ?>"><?php echo htmlspecialchars($dest['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="check_in">Check-in</label>
                            <input type="date" id="check_in" class="filter-input">
                        </div>
                        
                        <div class="filter-group">
                            <label for="check_out">Check-out</label>
                            <input type="date" id="check_out" class="filter-input">
                        </div>
                        
                        <div class="filter-group">
                            <label for="guests">Guests</label>
                            <select id="guests" class="filter-select">
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5+">5+ Guests</option>
                            </select>
                        </div>
                        
                        <button class="search-btn">
                            <i class="fas fa-search"></i> Search Hotels
                        </button>
                    </div>
                </div>

                <!-- Upcoming Trips -->
                <?php if ($upcoming_trips->num_rows > 0): ?>
                    <section class="upcoming-trips">
                        <div class="trips-header">
                            <h3><i class="fas fa-calendar-alt"></i> Upcoming Trips</h3>
                            <a href="booking.php" class="view-all">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="trips-list">
                            <?php while ($trip = $upcoming_trips->fetch_assoc()): 
                                $check_in = new DateTime($trip['check_in_date']);
                                $check_out = new DateTime($trip['check_out_date']);
                            ?>
                                <div class="trip-item">
                                    <div class="trip-date">
                                        <div class="day"><?php echo $check_in->format('d'); ?></div>
                                        <div class="month"><?php echo $check_in->format('M'); ?></div>
                                    </div>
                                    
                                    <div class="trip-details">
                                        <h4><?php echo htmlspecialchars($trip['hotel_name']); ?></h4>
                                        <p>
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo htmlspecialchars($trip['location']); ?>
                                        </p>
                                        <p>
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo $check_in->format('M d') . ' - ' . $check_out->format('M d, Y'); ?>
                                        </p>
                                    </div>
                                    
                                    <span class="trip-status status-confirmed">Confirmed</span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Featured Hotels -->
                <?php if ($featured_hotels->num_rows > 0): ?>
                    <section class="featured-section">
                        <div class="section-header">
                            <h2><i class="fas fa-star"></i> Featured Hotels</h2>
                            <a href="#" class="view-all">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="featured-hotels">
                            <?php while ($hotel = $featured_hotels->fetch_assoc()): ?>
                                <div class="featured-hotel">
                                    <div class="hotel-image">
                                        <img src="../assets/images/hotels_photos/<?php echo htmlspecialchars($hotel['image'] ?? 'default_hotel.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                    </div>
                                    
                                    <div class="hotel-content">
                                        <div class="hotel-title">
                                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                            <div class="featured-price">
                                                KSh<?php echo number_format($hotel['price'], 2); ?><small>/night</small>
                                            </div>
                                        </div>
                                        
                                        <p class="hotel-location">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo htmlspecialchars($hotel['destination_name']); ?>
                                        </p>
                                        
                                        <div class="hotel-amenities">
                                            <span class="amenity">
                                                <i class="fas fa-wifi"></i> WiFi
                                            </span>
                                            <span class="amenity">
                                                <i class="fas fa-swimming-pool"></i> Pool
                                            </span>
                                            <span class="amenity">
                                                <i class="fas fa-utensils"></i> Restaurant
                                            </span>
                                        </div>
                                        
                                        <form method="POST" action="booking.php" class="featured-form">
                                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                            <button type="submit" class="featured-btn">
                                                <i class="fas fa-calendar-check"></i> Book Now
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Destinations -->
                <section class="destinations-section">
                    <div class="section-header">
                        <h2><i class="fas fa-globe-americas"></i> Popular Destinations</h2>
                        <a href="destinations.php" class="view-all">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="destinations-grid">
                        <?php 
                        // Fetch top 4 destinations with hotel counts and minimum prices
                        $destinations = $conn->query("
                            SELECT d.*, 
                                   COUNT(h.id) as hotel_count,
                                   COALESCE(MIN(h.price), 0) as min_price
                            FROM destinations d
                            LEFT JOIN hotels h ON d.id = h.destination_id
                            GROUP BY d.id
                            ORDER BY d.name
                            LIMIT 4
                        ");

                        while ($destination = $destinations->fetch_assoc()): 
                            // Set image path
                            $image_path = !empty($destination['image']) ? 
                                "../assets/images/" . $destination['image'] : 
                                "../assets/images/default-destination.jpg";
                        ?>
                            <div class="destination-card">
                                <div class="destination-image">
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($destination['name']); ?>">
                                    <span class="destination-badge">
                                        <?php echo $destination['hotel_count']; ?> Hotels
                                    </span>
                                </div>
                                
                                <div class="destination-content">
                                    <div class="destination-header">
                                        <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                                        <div class="rating">
                                            <i class="fas fa-star"></i> 4.8
                                        </div>
                                    </div>
                                    
                                    <p class="destination-description">
                                        <?php echo htmlspecialchars(substr($destination['description'], 0, 120)); ?>…
                                    </p>
                                    
                                    <?php
                                    // Get hotels for this destination
                                    $hotels_query = $conn->prepare(
                                        "SELECT * FROM hotels WHERE destination_id = ? LIMIT 3"
                                    );
                                    $hotels_query->bind_param("i", $destination['id']);
                                    $hotels_query->execute();
                                    $hotels = $hotels_query->get_result();
                                    
                                    if ($hotels->num_rows > 0): ?>
                                        <div class="hotels-list">
                                            <?php while ($hotel = $hotels->fetch_assoc()): ?>
                                                <div class="hotel-item">
                                                    <div class="hotel-header">
                                                        <span class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></span>
                                                        <span class="hotel-price">$<?php echo number_format($hotel['price'], 2); ?></span>
                                                    </div>
                                                    
                                                    <div class="hotel-details">
                                                        <span class="hotel-detail">
                                                            <i class="fas fa-map-marker-alt"></i> 
                                                            <?php echo htmlspecialchars($hotel['location']); ?>
                                                        </span>
                                                        <span class="hotel-detail">
                                                            <i class="fas fa-star"></i> 
                                                            <?php echo $hotel['rating'] ?? 'New'; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <form method="POST" action="booking.php" class="booking-form">
                                                        <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                                        
                                                        <div class="form-grid">
                                                            <div class="form-group">
                                                                <label>Check-in</label>
                                                                <input type="date" name="check_in" class="form-control" 
                                                                       min="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Check-out</label>
                                                                <input type="date" name="check_out" class="form-control" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Guests</label>
                                                                <select name="guests" class="form-control">
                                                                    <option value="1">1 Guest</option>
                                                                    <option value="2" selected>2 Guests</option>
                                                                    <option value="3">3 Guests</option>
                                                                    <option value="4">4 Guests</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <button type="submit" name="book" class="book-btn">
                                                                <i class="fas fa-calendar-check"></i> Book
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        
                                        <?php if ($hotels->num_rows >= 3): ?>
                                            <div style="text-align: center; margin-top: 1rem;">
                                                <a href="#" class="view-all" 
                                                   onclick="loadMoreHotels(<?php echo $destination['id']; ?>, this)">
                                                    Show more hotels <i class="fas fa-chevron-down"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fas fa-hotel"></i>
                                            <h4>No Hotels Available</h4>
                                            <p>Check back soon for hotels in this destination.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>

                <!-- Featured Hotels Section -->

            </div>
        </div>

        <!-- Footer -->
        <footer class="customer-footer">
            <div class="container">
                <p>&copy; 2024 Luxe Voyage. Discover luxury, experience excellence.</p>
                <p>Need help? <a href="mailto:support@luxevoyage.com" style="color: #667eea;">Contact our support team</a></p>
            </div>
        </footer>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum dates for date inputs
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
            
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.min = today;
                if (input.name === 'check_out') {
                    const checkInInput = input.closest('form').querySelector('input[name="check_in"]');
                    if (checkInInput) {
                        checkInInput.addEventListener('change', function() {
                            if (this.value) {
                                input.min = this.value;
                                if (input.value && input.value < this.value) {
                                    input.value = '';
                                }
                            }
                        });
                    }
                }
            });
            
            // Search functionality
            const searchBtn = document.querySelector('.search-btn');
            searchBtn.addEventListener('click', function() {
                const destination = document.getElementById('destination').value;
                const checkIn = document.getElementById('check_in').value;
                const checkOut = document.getElementById('check_out').value;
                const guests = document.getElementById('guests').value;
                
                let query = '';
                if (destination) query += `destination=${destination}&`;
                if (checkIn) query += `check_in=${checkIn}&`;
                if (checkOut) query += `check_out=${checkOut}&`;
                if (guests) query += `guests=${guests}`;
                
                if (query) {
                    window.location.href = `booking.php?${query}`;
                }
            });
            
            // Load more hotels
            window.loadMoreHotels = function(destinationId, linkElement) {
                const destinationCard = linkElement.closest('.destination-card');
                const hotelsList = destinationCard.querySelector('.hotels-list');
                
                // Show loading
                linkElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                
                // Simulate loading more hotels (in real app, this would be an AJAX call)
                setTimeout(() => {
                    const newHotel = document.createElement('div');
                    newHotel.className = 'hotel-item';
                    newHotel.innerHTML = `
                        <div class="hotel-header">
                            <span class="hotel-name">Additional Luxury Hotel</span>
                            <span class="hotel-price">$299.99</span>
                        </div>
                        <div class="hotel-details">
                            <span class="hotel-detail">
                                <i class="fas fa-map-marker-alt"></i> Sample Location
                            </span>
                            <span class="hotel-detail">
                                <i class="fas fa-star"></i> 4.5
                            </span>
                        </div>
                        <form method="POST" action="booking.php" class="booking-form">
                            <input type="hidden" name="hotel_id" value="0">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Check-in</label>
                                    <input type="date" name="check_in" class="form-control" min="${today}" required>
                                </div>
                                <div class="form-group">
                                    <label>Check-out</label>
                                    <input type="date" name="check_out" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Guests</label>
                                    <select name="guests" class="form-control">
                                        <option value="1">1 Guest</option>
                                        <option value="2" selected>2 Guests</option>
                                        <option value="3">3 Guests</option>
                                        <option value="4">4 Guests</option>
                                    </select>
                                </div>
                                <button type="submit" name="book" class="book-btn">
                                    <i class="fas fa-calendar-check"></i> Book
                                </button>
                            </div>
                        </form>
                    `;
                    
                    hotelsList.appendChild(newHotel);
                    linkElement.style.display = 'none';
                    
                    // Update date validation for new form
                    updateDateValidation(newHotel.querySelector('input[name="check_in"]'), 
                                        newHotel.querySelector('input[name="check_out"]'));
                }, 1000);
            };
            
            function updateDateValidation(checkIn, checkOut) {
                checkIn.addEventListener('change', function() {
                    if (this.value) {
                        checkOut.min = this.value;
                        if (checkOut.value && checkOut.value < this.value) {
                            checkOut.value = '';
                        }
                    }
                });
            }
            
            // Initialize date validation for all forms
            document.querySelectorAll('.booking-form').forEach(form => {
                const checkIn = form.querySelector('input[name="check_in"]');
                const checkOut = form.querySelector('input[name="check_out"]');
                if (checkIn && checkOut) {
                    updateDateValidation(checkIn, checkOut);
                }
            });
        });
    </script>
</body>
</html>