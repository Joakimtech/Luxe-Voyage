<?php
// Get base URL for images
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// ======== DEBUG CODE - ADD THIS RIGHT AFTER DATABASE CONNECTION ========

echo "=== DEBUG START ===<br>";

// 1. Check database connection
echo "1. Database Connection: " . ($conn ? "OK" : "FAILED") . "<br>";

// 2. Check what's actually in the hotels table
$debug_query = "SELECT id, name, image, LENGTH(image) as name_length FROM hotels WHERE is_featured = 1 LIMIT 4";
$debug_result = $conn->query($debug_query);

if ($debug_result) {
    echo "2. Database Query: OK (Found " . $debug_result->num_rows . " featured hotels)<br>";
    echo "3. Actual Image Data in Database:<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Hotel Name</th><th>Image Column Value</th><th>Length</th></tr>";
    
    while ($row = $debug_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td><pre>'" . htmlspecialchars($row['image']) . "'</pre></td>";
        echo "<td>" . $row['name_length'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "2. Database Query FAILED: " . $conn->error . "<br>";
}

// 3. Check actual files in the images folder
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$image_folder = $doc_root . '/Luxe-Voyage/assets/images/hotels_photos/';

echo "<br>4. Checking Image Folder: " . $image_folder . "<br>";

if (is_dir($image_folder)) {
    $all_files = scandir($image_folder);
    $image_files = array_filter($all_files, function($file) {
        return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });
    
    echo "Folder exists. Found " . count($image_files) . " image files:<br>";
    echo "<ul>";
    foreach ($image_files as $file) {
        echo "<li>" . htmlspecialchars($file) . " (" . filesize($image_folder . $file) . " bytes)</li>";
    }
    echo "</ul>";
    
    // 4. Test specific files mentioned in your error
    echo "<br>5. Testing specific files from error log:<br>";
    $test_files = ['governors.jpg', 'diani_serene.jpg', 'diani_serenejpg'];
    
    foreach ($test_files as $test_file) {
        $test_path = $image_folder . $test_file;
        if (file_exists($test_path)) {
            echo "✓ FOUND: " . $test_file . " (" . filesize($test_path) . " bytes)<br>";
        } else {
            echo "✗ MISSING: " . $test_file . "<br>";
        }
    }
} else {
    echo "Folder does NOT exist or is not accessible!<br>";
}

// 5. Test URL generation
echo "<br>6. Testing URL generation:<br>";
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$test_image_url = $base_url . '/Luxe-Voyage/assets/images/hotels_photos/governors.jpg';
echo "Generated URL: <a href='" . $test_image_url . "' target='_blank'>" . $test_image_url . "</a><br>";

echo "=== DEBUG END ===<br><br>";

// ======== END DEBUG CODE ========

$image_base_path = $base_url . '/Luxe-Voyage/assets/images/hotels_photos/';

// Fetch featured hotels from the database
$featured_hotels_query = "
    SELECT 
        h.*, 
        (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id) as avg_rating,
        d.name as destination_name,
        CONCAT('$image_base_path', COALESCE(h.image, 'default_hotel.jpg')) as image_url,
        CONCAT('KSh ', FORMAT(h.price_per_night, 0)) as formatted_price
    FROM hotels h
    LEFT JOIN destinations d ON h.destination_id = d.id
    WHERE h.is_featured = 1
    ORDER BY h.created_at DESC
    LIMIT 4
";
$featured_hotels = $conn->query($featured_hotels_query);

// Debug: Uncomment to see the data being fetched
// $debug_data = $featured_hotels->fetch_assoc();
// echo "<!-- Debug Data: " . print_r($debug_data, true) . " -->";
// $featured_hotels->data_seek(0);

// Debug: Show image URLs
echo "<!-- Debug Image URLs: ";
$featured_hotels->data_seek(0); // Reset pointer to beginning
while ($hotel = $featured_hotels->fetch_assoc()) {
    echo "\n" . $hotel['name'] . ": " . $hotel['image_url'];
}
$featured_hotels->data_seek(0); // Reset pointer again for the actual display
echo " -->";
?>

<section class="hotels-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-hotel"></i> Featured Hotels</h2>
            <a href="hotels.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if ($featured_hotels->num_rows > 0): ?>
            <div class="hotels-grid">
                <?php 
                while ($hotel = $featured_hotels->fetch_assoc()): 
                    $avg_rating = $hotel['avg_rating'] ? round($hotel['avg_rating'], 1) : 'N/A';
                    // Debug: Uncomment to check the hotel data
                    // echo "<!-- Hotel Data: " . print_r($hotel, true) . " -->\n";
                ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($hotel['name']); ?>"
                                 onerror="this.src='<?php echo $image_base_path; ?>default_hotel.jpg';">
                            <div class="hotel-overlay">
                                <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                            <?php if ($hotel['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="hotel-content">
                            <div class="hotel-header">
                                <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                <div class="rating">
                                    <i class="fas fa-star"></i> <?php echo $avg_rating; ?>
                                </div>
                            </div>
                            <p class="location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($hotel['location']); ?>
                            </p>
                            <p class="description">
                                <?php echo substr(htmlspecialchars($hotel['description']), 0, 100); ?>...
                            </p>
                            <div class="hotel-meta">
                                <div class="price">
                                    <span class="from">From</span>
                                    <span class="amount"><?php echo $hotel['formatted_price']; ?></span>
                                    <span class="per-night">per night</span>
                                </div>
                                <a href="destination_booking.php?destination_id=<?php echo $hotel['destination_id']; ?>" class="btn btn-book">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-hotels">
                <i class="fas fa-hotel"></i>
                <p>No featured hotels available at the moment. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Hotels Section Styles */
.hotels-section {
    padding: 4rem 0;
    background-color: #f8fafc;
}

.hotels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.hotel-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.hotel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.hotel-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.hotel-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.hotel-card:hover .hotel-image img {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #4f46e5;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.hotel-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.hotel-card:hover .hotel-overlay {
    opacity: 1;
}

.hotel-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.hotel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.hotel-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1e293b;
    font-weight: 600;
    line-height: 1.3;
}

.rating {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    color: #f59e0b;
    font-size: 0.9rem;
}

.rating i {
    margin-right: 0.25rem;
    font-size: 0.9em;
}

.location {
    color: #64748b;
    margin: 0 0 1rem;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.location i {
    color: #94a3b8;
}

.description {
    color: #475569;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex: 1;
}

.hotel-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}

.price {
    display: flex;
    flex-direction: column;
}

.price .from {
    font-size: 0.8rem;
    color: #64748b;
}

.price .amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e40af;
    line-height: 1.2;
}

.price .per-night {
    font-size: 0.75rem;
    color: #94a3b8;
}

.btn-book {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.6rem 1.25rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-book:hover {
    background: #4338ca;
    transform: translateY(-2px);
}

.no-hotels {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.no-hotels i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.no-hotels p {
    color: #64748b;
    font-size: 1.1rem;
    margin: 0;
}

/* Responsive Styles */
@media (max-width: 1200px) {
    .hotels-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .hotels-section {
        padding: 3rem 0;
    }
    
    .hotels-grid {
        grid-template-columns: 1fr;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 480px) {
    .hotel-content {
        padding: 1.25rem;
    }
    
    .hotel-header {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .hotel-meta {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .btn-book {
        width: 100%;
        text-align: center;
    }
}
</style>
