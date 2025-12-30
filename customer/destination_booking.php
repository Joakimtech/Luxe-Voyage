<?php
session_start();
include "../includes/auth.php";
include "../config/db.php";
requireRole('customer');

$user_id = $_SESSION['user']['id'];
$success_message = '';
$error_message = '';

// Get destination details if destination_id is provided
$destination = null;
if (isset($_GET['destination_id']) || isset($_POST['destination_id'])) {
    $destination_id = $_GET['destination_id'] ?? $_POST['destination_id'];
    $destination_query = $conn->prepare(
        "SELECT * FROM destinations WHERE id = ?"
    );
    $destination_query->bind_param("i", $destination_id);
    $destination_query->execute();
    $destination_result = $destination_query->get_result();
    $destination = $destination_result->fetch_assoc();
    
    // Get available hotels for this destination
    $hotels_query = $conn->prepare(
        "SELECT * FROM hotels WHERE destination_id = ?"
    );
    $hotels_query->bind_param("i", $destination_id);
    $hotels_query->execute();
    $hotels_result = $hotels_query->get_result();
    $hotels = [];
    while ($row = $hotels_result->fetch_assoc()) {
        $hotels[] = $row;
    }
}

if (isset($_POST['book']) && $destination) {
    $destination_id = $_POST['destination_id'];
    $hotel_id = $_POST['hotel_id'] ?? null;
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $travelers = $_POST['travelers'] ?? 1;
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Validate dates
    $today = date('Y-m-d');
    if ($check_in < $today) {
        $error_message = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $error_message = "Check-out date must be after check-in date.";
    } elseif (empty($hotel_id)) {
        $error_message = "Please select a hotel for your stay.";
    } else {
        // Get hotel price
        $hotel_stmt = $conn->prepare("SELECT price FROM hotels WHERE id = ?");
        $hotel_stmt->bind_param("i", $hotel_id);
        $hotel_stmt->execute();
        $hotel = $hotel_stmt->get_result()->fetch_assoc();
        $hotel_stmt->close();
        
        if (!$hotel) {
            $error_message = "Selected hotel not found.";
        } else {
            // Calculate total price
            $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
            $total_amount = $nights * $hotel['price'] * $travelers;
            
            $stmt = $conn->prepare(
                "INSERT INTO bookings (user_id, destination_id, hotel_id, check_in_date, check_out_date, guests, total_amount, special_requests, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->bind_param("iiissids", $user_id, $destination_id, $hotel_id, $check_in, $check_out, $travelers, $total_amount, $special_requests);
            
            if ($stmt->execute()) {
                $success_message = "Destination booking request submitted successfully!";
                $booking_id = $conn->insert_id;
                
                // Clear form
                $_POST = array();
                $destination = null;
            } else {
                $error_message = "Error creating booking: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Destination - Luxe Voyage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer_booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles specific to destination booking */
        .destination-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('../uploads/destinations/<?php echo htmlspecialchars($destination['image'] ?? 'default_destination.jpg'); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 16px;
        }
        
        .destination-hero h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .destination-hero p {
            font-size: 1.25rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hotel-options {
            margin: 2rem 0;
        }
        
        .hotel-option {
            display: flex;
            gap: 2rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .hotel-option:hover {
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .hotel-option.selected {
            border: 2px solid #4f46e5;
            background-color: #f8fafc;
        }
        
        .hotel-option input[type="radio"] {
            margin-top: 0.5rem;
        }
        
        .hotel-image {
            width: 200px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .hotel-details {
            flex-grow: 1;
        }
        
        .hotel-price {
            text-align: right;
            min-width: 150px;
        }
        
        .hotel-price .price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4f46e5;
        }
        
        .hotel-price .per-night {
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="booking-page">
        <!-- Header -->
        <header class="booking-header">
            <div class="container">
                <div class="booking-header-content">
                    <div class="booking-title">
                        <i class="fas fa-map-marked-alt"></i>
                        <div>
                            <h1>Plan Your Dream Getaway</h1>
                            <p class="booking-subtitle">Book your perfect vacation in just a few steps</p>
                        </div>
                    </div>
                    
                    <a href="dashboard.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <!-- Booking Steps -->
                <div class="process-steps">
                    <div class="step active">
                        <div class="step-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="step-label">Select Destination</span>
                    </div>
                    
                    <div class="step <?php echo $destination ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <span class="step-label">Choose Hotel</span>
                    </div>
                    
                    <div class="step <?php echo isset($_POST['book']) ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span class="step-label">Payment</span>
                    </div>
                    
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="step-label">Confirmation</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="booking-process">
            <div class="container">
                <?php if ($success_message): ?>
                    <div class="booking-card">
                        <div class="success-message">
                            <div class="success-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2>Booking Request Submitted!</h2>
                            <p>Your booking request has been received. We'll review and confirm your reservation within 24 hours. You'll receive an email confirmation once approved.</p>
                            
                            <div class="success-actions">
                                <a href="dashboard.php" class="btn-primary">
                                    <i class="fas fa-home"></i> Back to Dashboard
                                </a>
                                <a href="booking.php?view=mybookings" class="btn-secondary">
                                    <i class="fas fa-list"></i> View My Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                
                <?php elseif (!$destination && !isset($_GET['destination_id'])): ?>
                    <!-- Destination Selection -->
                    <div class="booking-card">
                        <div class="destination-preview-section">
                            <div class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                <h2>Select a Destination</h2>
                            </div>
                            
                            <div style="text-align: center; padding: 3rem;">
                                <i class="fas fa-globe-africa fa-3x" style="color: #cbd5e0; margin-bottom: 1rem;"></i>
                                <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No Destination Selected</h3>
                                <p style="color: #718096; margin-bottom: 1.5rem;">Please select a destination from the dashboard to book</p>
                                <a href="dashboard.php" class="btn-primary">
                                    <i class="fas fa-search"></i> Browse Destinations
                                </a>
                            </div>
                        </div>
                    </div>
                
                <?php elseif ($destination): ?>
                    <!-- Booking Form -->
                    <form method="POST" action="" class="booking-card">
                        <input type="hidden" name="destination_id" value="<?php echo $destination['id']; ?>">
                        
                        <!-- Destination Hero -->
                        <div class="destination-hero">
                            <h1><?php echo htmlspecialchars($destination['name']); ?></h1>
                            <p><?php echo htmlspecialchars($destination['description']); ?></p>
                        </div>
                        
                        <!-- Hotel Selection -->
                        <div class="hotel-options">
                            <div class="section-title">
                                <i class="fas fa-hotel"></i>
                                <h2>Select Your Hotel</h2>
                                <p>Choose from our curated selection of hotels in <?php echo htmlspecialchars($destination['name']); ?></p>
                            </div>
                            
                            <?php if (empty($hotels)): ?>
                                <div class="no-hotels" style="text-align: center; padding: 2rem; background: #f8fafc; border-radius: 8px;">
                                    <i class="fas fa-hotel fa-3x" style="color: #cbd5e0; margin-bottom: 1rem;"></i>
                                    <h3>No Hotels Available</h3>
                                    <p>There are currently no hotels available for this destination. Please check back later or contact us for assistance.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($hotels as $hotel): ?>
                                    <label class="hotel-option">
                                        <input type="radio" name="hotel_id" value="<?php echo $hotel['id']; ?>" 
                                               required <?php echo (isset($_POST['hotel_id']) && $_POST['hotel_id'] == $hotel['id']) ? 'checked' : ''; ?>>
                                        <img src="../uploads/hotels/<?php echo htmlspecialchars($hotel['image'] ?? 'default_hotel.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                                        <div class="hotel-details">
                                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                            <div class="hotel-location" style="color: #64748b; margin-bottom: 0.5rem;">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                <?php echo htmlspecialchars($hotel['location']); ?>
                                            </div>
                                            <div class="hotel-amenities" style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                                                <?php for ($i = 0; $i < min(5, $hotel['rating']); $i++): ?>
                                                    <i class="fas fa-star" style="color: #f59e0b;"></i>
                                                <?php endfor; ?>
                                                <span>•</span>
                                                <span><i class="fas fa-wifi"></i> Free WiFi</span>
                                                <span>•</span>
                                                <span><i class="fas fa-parking"></i> Parking</span>
                                            </div>
                                            <p style="color: #4b5563; margin: 0.5rem 0;">
                                                <?php echo substr(htmlspecialchars($hotel['description'] ?? 'Luxury accommodation with modern amenities.'), 0, 150); ?>...
                                            </p>
                                        </div>
                                        <div class="hotel-price">
                                            <div class="price">Ksh <?php echo number_format($hotel['price']); ?></div>
                                            <div class="per-night">per night</div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Booking Details -->
                        <div class="booking-form-section">
                            <div class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                <h2>Travel Details</h2>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="check_in">Check-in Date <span class="required">*</span></label>
                                    <input type="date" id="check_in" name="check_in" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['check_in'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="check_out">Check-out Date <span class="required">*</span></label>
                                    <input type="date" id="check_out" name="check_out" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['check_out'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="travelers">Number of Travelers <span class="required">*</span></label>
                                    <select id="travelers" name="travelers" class="form-control" required>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (isset($_POST['travelers']) && $_POST['travelers'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> <?php echo $i === 1 ? 'Traveler' : 'Travelers'; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="special_requests">Special Requests</label>
                                <textarea id="special_requests" name="special_requests" class="form-control" rows="4" 
                                          placeholder="Any special requirements or requests for your stay..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                            </div>
                            
                            <?php if (!empty($hotels)): ?>
                                <div class="price-summary">
                                    <h3 class="summary-title">
                                        <i class="fas fa-receipt"></i> Price Summary
                                    </h3>
                                    
                                    <div class="summary-row">
                                        <span>Base Price (per night)</span>
                                        <span id="base-price">Ksh 0</span>
                                    </div>
                                    
                                    <div class="summary-row">
                                        <span>Number of Nights</span>
                                        <span id="nights-count">0 nights</span>
                                    </div>
                                    
                                    <div class="summary-row total">
                                        <span>Total Amount</span>
                                        <span id="total-amount">Ksh 0</span>
                                    </div>
                                </div>
                                
                                <div class="booking-actions-footer">
                                    <div class="secure-notice">
                                        <i class="fas fa-lock"></i>
                                        <span>Secure Payment. Your information is protected.</span>
                                    </div>
                                    
                                    <button type="submit" name="book" class="btn-primary">
                                        <i class="fas fa-credit-card"></i> Complete Booking
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date for check-in to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('check_in').min = today;
            
            // Update check-out min date when check-in date changes
            document.getElementById('check_in').addEventListener('change', function() {
                const checkInDate = this.value;
                const checkOutInput = document.getElementById('check_out');
                checkOutInput.min = checkInDate;
                
                // If current check-out date is before new check-in date, update it
                if (checkOutInput.value && checkOutInput.value < checkInDate) {
                    checkOutInput.value = '';
                }
                
                calculatePrice();
            });
            
            // Update price when check-out date changes
            document.getElementById('check_out').addEventListener('change', calculatePrice);
            
            // Update price when hotel selection changes
            const hotelRadios = document.querySelectorAll('input[name="hotel_id"]');
            hotelRadios.forEach(radio => {
                radio.addEventListener('change', calculatePrice);
            });
            
            // Update price when number of travelers changes
            document.getElementById('travelers').addEventListener('change', calculatePrice);
            
            // Initial price calculation
            calculatePrice();
            
            // Style hotel selection
            const hotelOptions = document.querySelectorAll('.hotel-option');
            hotelOptions.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                
                // Style on load if checked
                if (radio.checked) {
                    option.classList.add('selected');
                }
                
                // Style on click
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    hotelOptions.forEach(opt => opt.classList.remove('selected'));
                    // Add to clicked option
                    this.classList.add('selected');
                    // Check the radio
                    radio.checked = true;
                    // Trigger change event for price calculation
                    radio.dispatchEvent(new Event('change'));
                });
            });
            
            // Calculate price function
            function calculatePrice() {
                const checkIn = document.getElementById('check_in').value;
                const checkOut = document.getElementById('check_out').value;
                const travelers = parseInt(document.getElementById('travelers').value);
                const selectedHotel = document.querySelector('input[name="hotel_id"]:checked');
                
                if (!checkIn || !checkOut || !selectedHotel) {
                    return;
                }
                
                // Calculate number of nights
                const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
                const startDate = new Date(checkIn);
                const endDate = new Date(checkOut);
                const nights = Math.round(Math.abs((startDate - endDate) / oneDay));
                
                // Get hotel price from data attribute or calculate it
                const hotelOption = selectedHotel.closest('.hotel-option');
                const priceMatch = hotelOption.querySelector('.price').textContent.match(/\d+/);
                const pricePerNight = priceMatch ? parseInt(priceMatch[0].replace(/,/g, '')) : 0;
                
                // Calculate total
                const totalAmount = nights * pricePerNight * travelers;
                
                // Update UI
                document.getElementById('base-price').textContent = `Ksh ${pricePerNight.toLocaleString()}`;
                document.getElementById('nights-count').textContent = `${nights} night${nights !== 1 ? 's' : ''}`;
                document.getElementById('total-amount').textContent = `Ksh ${totalAmount.toLocaleString()}`;
            }
        });
    </script>
</body>
</html>
