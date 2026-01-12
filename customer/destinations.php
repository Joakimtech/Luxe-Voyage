<?php
// Include necessary files
include "../includes/header.php";
include "../config/db.php";

// Set page title and additional CSS
$page_title = "All Destinations";
$additional_css = [
    '../assets/css/destinations.css'
];

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$initials = strtoupper(substr($username, 0, 2));

// Fetch all destinations with hotel counts and minimum prices
$destinations = $conn->query("
    SELECT d.*, 
           COUNT(h.id) as hotel_count,
           COALESCE(MIN(h.price), 0) as min_price
    FROM destinations d
    LEFT JOIN hotels h ON d.id = h.destination_id
    GROUP BY d.id
    ORDER BY d.name
");
?>

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
                        <a href="dashboard.php" class="header-btn secondary">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="../profile.php" class="header-btn secondary">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <a href="../logout.php" class="header-btn secondary">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-content">
        <div class="container">
            <section class="destinations-section">
                <div class="section-header">
                    <h2><i class="fas fa-globe-americas"></i> All Destinations</h2>
                    <p class="section-subtitle">Explore our wide range of amazing destinations across Kenya</p>
                </div>

    <div class="destinations-grid">
        <?php while ($destination = $destinations->fetch_assoc()): 
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
                        <?php echo htmlspecialchars(substr($destination['description'], 0, 120)); ?>...
                    </p>
                    
                    <div class="destination-footer">
                        <div class="price">From KES <?php echo number_format($destination['min_price']); ?></div>
                        <a href="destination_booking.php?destination_id=<?php echo $destination['id']; ?>" 
                           class="btn btn-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="customer-footer">
        <div class="container">
            <p>&copy; 2025 Luxe Voyage. All rights reserved.</p>
            <p>Need help? <a href="mailto:support@luxevoyage.com">Contact our support team</a></p>
        </div>
    </footer>
</div>

<?php include "../includes/footer.php"; ?>
