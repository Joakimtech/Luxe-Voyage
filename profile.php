<?php
include "includes/auth.php";
requireLogin();

$user = $_SESSION['user'];

// Get user stats based on role
include "config/db.php";
$user_id = $user['id'];
$stats = [];

if ($user['role'] === 'customer') {
    $stats_query = $conn->query(
        "SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as upcoming_trips
         FROM bookings 
         WHERE customer_id = $user_id"
    );
    $stats = $stats_query->fetch_assoc();
} elseif ($user['role'] === 'host') {
    $stats_query = $conn->query(
        "SELECT 
            COUNT(DISTINCT h.id) as total_hotels,
            COUNT(b.id) as total_bookings,
            SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings
         FROM hotels h
         LEFT JOIN bookings b ON h.id = b.hotel_id
         WHERE h.host_id = $user_id"
    );
    $stats = $stats_query->fetch_assoc();
} elseif ($user['role'] === 'admin') {
    $stats_query = $conn->query(
        "SELECT 
            COUNT(*) as total_users,
            (SELECT COUNT(*) FROM hotels) as total_hotels,
            (SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()) as today_bookings
        FROM users
    ") or die($conn->error);
    $stats = $stats_query->fetch_assoc();
}

// Get recent activity
$recent_activity_query = "
    (SELECT 
        'booking' as activity_type,
        CONCAT('Booking #', b.id) as title,
        b.booking_date as activity_date
     FROM bookings b
     WHERE b.customer_id = $user_id 
     ORDER BY b.booking_date DESC
     LIMIT 2)
    UNION ALL
    (SELECT 
        'login' as activity_type,
        'Account Login' as title,
        last_login as activity_date
     FROM users 
     WHERE id = $user_id AND last_login IS NOT NULL
     ORDER BY last_login DESC
     LIMIT 1)
    ORDER BY activity_date DESC 
    LIMIT 3";

// Execute the query with error handling
try {
    $recent_activity = $conn->query($recent_activity_query);
    if (!$recent_activity) {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $recent_activity = false;
}

// Format join date
$join_date = isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Luxe Voyage</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional inline styles */
        body { font-family: 'Poppins', sans-serif; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
    </style>
</head>
<body>
    <div class="profile-page">
        <!-- Header -->
        <header class="profile-header">
            <div class="container">
                <div class="header-container">
                    <a href="<?php echo $user['role'] === 'admin' ? 'admin/dashboard.php' : 
                                    ($user['role'] === 'host' ? 'host/dashboard.php' : 'customer/dashboard.php'); ?>" 
                       class="back-home">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    
                    <div class="header-title">
                        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                        <p>Manage your account and view your activity</p>
                    </div>
                    
                    <div style="width: 150px;"></div> <!-- Spacer for alignment -->
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="profile-container">
            <div class="profile-card">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar-container">
                        <img src="assets/images/profiles/<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                             alt="<?php echo htmlspecialchars($user['username']); ?>" 
                             class="profile-avatar"
                             onerror="this.src='assets/images/profiles/default.png'">
                        <a href="edit_profile.php" class="avatar-overlay" title="Change Photo">
                            <i class="fas fa-camera"></i>
                        </a>
                    </div>
                    
                    <h2 style="margin: 0 0 0.5rem; color: white;"><?php echo htmlspecialchars($user['username']); ?></h2>
                    
                    <div class="profile-role <?php echo $user['role']; ?>">
                        <?php echo ucfirst($user['role']); ?> Account
                    </div>
                    
                    <div class="sidebar-stats">
                        <?php if ($user['role'] === 'customer'): ?>
                            <div class="stat-item">
                                <i class="fas fa-suitcase"></i>
                                <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-calendar-check"></i>
                                <h3><?php echo $stats['upcoming_trips'] ?? 0; ?></h3>
                                <p>Upcoming Trips</p>
                            </div>
                        <?php elseif ($user['role'] === 'host'): ?>
                            <div class="stat-item">
                                <i class="fas fa-hotel"></i>
                                <h3><?php echo $stats['total_hotels'] ?? 0; ?></h3>
                                <p>Hotels Listed</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-calendar-alt"></i>
                                <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                        <?php elseif ($user['role'] === 'admin'): ?>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $stats['total_users'] ?? 0; ?></h3>
                                <p>Total Users</p>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-chart-line"></i>
                                <h3><?php echo $stats['today_bookings'] ?? 0; ?></h3>
                                <p>Today's Bookings</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="profile-content">
                    <!-- Personal Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <i class="fas fa-id-card"></i>
                            <h2>Personal Information</h2>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Username</span>
                                <p class="info-value"><?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email Address</span>
                                <p class="info-value email"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Account Role</span>
                                <p class="info-value role <?php echo $user['role']; ?>">
                                    <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : 
                                                        ($user['role'] === 'host' ? 'hotel' : 'user'); ?>"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </p>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Member Since</span>
                                <p class="info-value"><?php echo $join_date; ?></p>
                                <p class="member-since">
                                    <i class="far fa-calendar"></i>
                                    <?php 
                                        $days_ago = isset($user['created_at']) ? 
                                            floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)) : 0;
                                        echo $days_ago . ' days ago';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                        <div class="profile-section">
                            <div class="section-header">
                                <i class="fas fa-history"></i>
                                <h2>Recent Activity</h2>
                            </div>
                            
                            <div class="activity-feed">
                                <?php while($activity = $recent_activity->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <i class="fas fa-<?php echo $activity['activity_type'] === 'booking' ? 'hotel' : 'sign-in-alt'; ?>"></i>
                                        <div>
                                            <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                            <p class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($activity['activity_date'])); ?>
                                            <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                            <p class="activity-time">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('F j, Y - g:i A', strtotime($activity['time'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Account Status -->
                    <div class="account-status">
                        <div class="status-header">
                            <h3><i class="fas fa-shield-alt"></i> Account Status</h3>
                            <span class="status-badge active">Verified & Active</span>
                        </div>
                        <div class="status-details">
                            <p>Your account is in good standing. 
                            <?php if ($user['role'] === 'customer'): ?>
                                You have <?php echo $stats['total_bookings'] ?? 0; ?> booking(s) in your history.
                            <?php elseif ($user['role'] === 'host'): ?>
                                You have <?php echo $stats['total_hotels'] ?? 0; ?> hotel(s) listed and <?php echo $stats['total_bookings'] ?? 0; ?> booking(s).
                            <?php elseif ($user['role'] === 'admin'): ?>
                                You have full administrative access to manage the platform.
                            <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="profile-actions">
                        <a href="edit_profile.php" class="action-btn primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        
                        <?php if ($user['role'] === 'customer'): ?>
                            <a href="customer/dashboard.php" class="action-btn secondary">
                                <i class="fas fa-suitcase"></i> My Bookings
                            </a>
                        <?php elseif ($user['role'] === 'host'): ?>
                            <a href="host/dashboard.php" class="action-btn secondary">
                                <i class="fas fa-hotel"></i> Host Dashboard
                            </a>
                        <?php elseif ($user['role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="action-btn secondary">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="action-btn warning">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to profile card
            const profileCard = document.querySelector('.profile-card');
            profileCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            profileCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
            
            // Image error handling
            const profileImage = document.querySelector('.profile-avatar');
            profileImage.addEventListener('error', function() {
                this.src = 'assets/images/profiles/default.png';
                this.alt = 'Default Profile Picture';
            });
            
            // Role-specific animations
            const roleElement = document.querySelector('.info-value.role');
            if (roleElement) {
                roleElement.addEventListener('click', function() {
                    const roles = {
                        'customer': 'Traveler Account - Book luxury stays worldwide',
                        'host': 'Hotel Partner Account - Manage your properties',
                        'admin': 'Administrator Account - Full system access'
                    };
                    const role = '<?php echo $user['role']; ?>';
                    alert(roles[role]);
                });
            }
            
            // Update last seen time
            function updateLastSeen() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                console.log(`Last seen: ${timeString}`);
            }
            
            // Update every minute
            setInterval(updateLastSeen, 60000);
        });
    </script>
</body>
</html>