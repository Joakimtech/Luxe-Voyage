<?php
// Database connection details
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password is empty
$dbname = "luxe_voyage";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Fix Script</h2>";

try {
    // Add is_featured and created_at columns to hotels table if they don't exist
    $alter_hotels_sql = "
        ALTER TABLE hotels 
        ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ";
    
    if ($conn->query($alter_hotels_sql) === TRUE) {
        echo "<p>✅ Successfully updated hotels table</p>";
    } else {
        echo "<p>❌ Error updating hotels table: " . $conn->error . "</p>";
    }

    // Create reviews table if it doesn't exist
    $create_reviews_sql = "
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hotel_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_hotel_review (user_id, hotel_id)
        )
    ";
    
    if ($conn->query($create_reviews_sql) === TRUE) {
        echo "<p>✅ Successfully created reviews table</p>";
    } else {
        echo "<p>❌ Error creating reviews table: " . $conn->error . "</p>";
    }
    
    // Add some sample data if the table is empty
    $check_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews");
    $review_count = $check_reviews->fetch_assoc()['count'];
    
    if ($review_count == 0) {
        // Get some hotel and user IDs to create sample reviews
        $sample_data = $conn->query("
            INSERT INTO reviews (hotel_id, user_id, rating, comment) VALUES
            (1, 1, 5, 'Amazing hotel with great service!'),
            (1, 2, 4, 'Very comfortable stay, would recommend!'),
            (2, 1, 3, 'Good location but could be cleaner')
        ") or die("Error adding sample data: " . $conn->error);
        
        echo "<p>✅ Added sample review data</p>";
    }
    
    echo "<p>✅ Database fix completed successfully!</p>";
    echo "<p>You can now <a href='customer/dashboard.php'>return to the dashboard</a>.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
