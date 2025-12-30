<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxe Voyage - Luxury Travel & Hotels</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-crown"></i>
                <span>Luxe Voyage</span>
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#destinations">Destinations</a>
                <a href="#hotels">Hotels</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user'])): ?>
                    <a href="<?php echo $_SESSION['user']['role'] == 'host' ? 'host/dashboard.php' : 'customer/dashboard.php'; ?>" class="btn-secondary">
                        <i class="fas fa-user-circle"></i> Dashboard
                    </a>
                    <a href="logout.php" class="btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <button onclick="openLogin()" class="btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <button onclick="openRegisterChoice()" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Experience Luxury Beyond Imagination</h1>
            <p>Discover exclusive destinations, premium hotels, and unforgettable experiences tailored just for you.</p>
            <div class="hero-buttons">
                <button onclick="openRegisterChoice()" class="btn-hero-primary">
                    <i class="fas fa-gem"></i> Start Your Journey
                </button>
                <button onclick="document.getElementById('destinations').scrollIntoView()" class="btn-hero-secondary">
                    <i class="fas fa-binoculars"></i> Explore Destinations
                </button>
            </div>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Luxe Voyage?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Premium Quality</h3>
                    <p>Curated luxury experiences with 5-star ratings and exceptional service.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Booking</h3>
                    <p>Your transactions and personal data are protected with advanced security.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock concierge service to assist with all your travel needs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Account Type Section -->
    <section class="account-types" id="destinations">
        <div class="container">
            <h2 class="section-title">Choose Your Experience</h2>
            <div class="type-cards">
                <div class="type-card customer-card" onclick="openRegister('customer')">
                    <div class="type-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Traveler</h3>
                    <p>Explore luxury destinations, book premium hotels, and create unforgettable memories.</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Browse exclusive destinations</li>
                        <li><i class="fas fa-check"></i> Book luxury hotels</li>
                        <li><i class="fas fa-check"></i> Manage bookings & itineraries</li>
                    </ul>
                    <button class="type-btn">Join as Traveler</button>
                </div>
                
                <div class="type-card host-card" onclick="openRegister('host')">
                    <div class="type-icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <h3>Hotel Partner</h3>
                    <p>Showcase your luxury properties, manage bookings, and connect with premium travelers.</p>
                    <ul>
                        <li><i class="fas fa-check"></i> List your hotels</li>
                        <li><i class="fas fa-check"></i> Manage bookings & availability</li>
                        <li><i class="fas fa-check"></i> Access premium clientele</li>
                    </ul>
                    <button class="type-btn">Join as Partner</button>
                </div>
            </div>
        </div>
    </section>

    <!-- MODALS -->
    <!-- Register Choice Modal -->
    <div id="registerChoiceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Choose Account Type</h2>
                <button class="close-modal" onclick="closeModals()">&times;</button>
            </div>
            <div class="modal-body">
                <p class="modal-subtitle">Select the type of account that best fits your needs</p>
                
                <div class="choice-cards">
                    <div class="choice-card" onclick="openRegister('customer')">
                        <div class="choice-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Traveler Account</h3>
                        <p>For individuals seeking luxury travel experiences</p>
                        <ul>
                            <li><i class="fas fa-check"></i> Book luxury hotels</li>
                            <li><i class="fas fa-check"></i> Explore destinations</li>
                            <li><i class="fas fa-check"></i> Manage itineraries</li>
                        </ul>
                        <button class="choice-btn">Continue as Traveler</button>
                    </div>
                    
                    <div class="choice-card" onclick="openRegister('host')">
                        <div class="choice-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h3>Business Account</h3>
                        <p>For hotel owners and hospitality businesses</p>
                        <ul>
                            <li><i class="fas fa-check"></i> List properties</li>
                            <li><i class="fas fa-check"></i> Manage bookings</li>
                            <li><i class="fas fa-check"></i> Access analytics</li>
                        </ul>
                        <button class="choice-btn">Continue as Partner</button>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <p>Already have an account? <a href="#" onclick="openLogin()">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <form method="POST" action="register.php" class="auth-form">
                <div class="modal-header">
                    <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                    <button type="button" class="close-modal" onclick="closeModals()">&times;</button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" name="role" id="registerRole">
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <small class="password-hint">At least 8 characters with letters and numbers</small>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" name="register" class="submit-btn">
                        <i class="fas fa-check-circle"></i> Create Account
                    </button>
                    <button type="button" class="cancel-btn" onclick="closeModals()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <form method="POST" action="login.php" class="auth-form">
                <div class="modal-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Welcome Back</h2>
                    <button type="button" class="close-modal" onclick="closeModals()">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="identity"><i class="fas fa-user-circle"></i> Email or Username</label>
                        <input type="text" id="identity" name="identity" placeholder="Enter email or username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="loginPassword"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                        <div class="forgot-password">
                            <a href="#">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" name="login" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                    <button type="button" class="cancel-btn" onclick="closeModals()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    
                    <div class="register-link">
                        <p>Don't have an account? <a href="#" onclick="openRegisterChoice()">Sign Up</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="footer-logo">
                    <i class="fas fa-crown"></i>
                    <span>Luxe Voyage</span>
                </div>
                <p class="footer-description">Experience luxury travel at its finest. Premium destinations, exclusive hotels, and unforgettable journeys.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/wintercreed254?t=0RF16LB6TJ1sX0iS1GHApQ&s=09"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/wintercreed/"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/loren-deklerk"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#destinations">Destinations</a></li>
                    <li><a href="#hotels">Hotels</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Account</h3>
                <ul class="footer-links">
                    <?php if(!isset($_SESSION['user'])): ?>
                        <li><a href="#" onclick="openLogin()">Login</a></li>
                        <li><a href="#" onclick="openRegisterChoice()">Register</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $_SESSION['user']['role'] == 'host' ? 'host/dashboard.php' : 'customer/dashboard.php'; ?>">Dashboard</a></li>
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> Parliament Road, Nairobi</li>
                    <li><i class="fas fa-phone"></i> +254 717574728</li>
                    <li><i class="fas fa-envelope"></i> info@luxevoyage.com</li>
                    <li><i class="fas fa-clock"></i> 24/7 Customer Support</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Luxe Voyage. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        // Overlay for modals
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        document.body.appendChild(modalOverlay);

        // Modal functions
        function openRegisterChoice() {
            document.getElementById('registerChoiceModal').style.display = 'block';
            modalOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function openRegister(role) {
            document.getElementById('registerRole').value = role;
            document.getElementById('registerChoiceModal').style.display = 'none';
            document.getElementById('registerModal').style.display = 'block';
            modalOverlay.style.display = 'block';
        }

        function openLogin() {
            document.getElementById('loginModal').style.display = 'block';
            modalOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
            modalOverlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modals when clicking overlay
        modalOverlay.addEventListener('click', closeModals);

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModals();
        });

        // Prevent modal close when clicking inside modal content
        document.querySelectorAll('.modal-content').forEach(content => {
            content.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>