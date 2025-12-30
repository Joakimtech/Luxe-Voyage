<?php 
session_start();
$page_title = $page_title ?? 'Luxe Voyage';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Luxe Voyage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php 
    if (isset($additional_css)) {
        foreach ($additional_css as $css) {
            echo "<link rel=\"stylesheet\" href=\"$css\">\n";
        }
    }
    ?>
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 1.5rem; 
        }
    </style>
</head>
<body>
