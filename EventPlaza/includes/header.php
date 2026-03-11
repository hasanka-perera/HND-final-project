<?php
// Include the functions file which already calls session_start() if needed
require_once('includes/functions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Vendor Management Platform</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">EventPlaza</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="search.php">Find Vendors</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="blog.php">Blog</a></li>
            <?php if(isLoggedIn()): ?>
                <li><a href="logout.php" class="btn-login">Logout</a></li>
                <?php if(isAdmin()): ?>
                    <li><a href="admin/dashboard.php" class="btn-dashboard">Admin Dashboard</a></li>
                <?php elseif($_SESSION['role'] === 'vendor'): ?>
                    <li><a href="vendor/dashboard.php" class="btn-dashboard">Vendor Dashboard</a></li>
                <?php else: ?>
                    <li><a href="user/dashboard.php" class="btn-dashboard">My Dashboard</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="login.php" class="btn-login">Login / Register</a></li>
            <?php endif; ?>
        </ul>
        <div class="nav-toggle" id="navToggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Add your other content here -->

</body>
</html>
