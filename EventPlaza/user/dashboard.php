<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch User Bookings
$stmt = $pdo->prepare("
    SELECT b.*, v.business_name, v.id as vendor_id, c.name as category_name 
    FROM bookings b 
    JOIN vendors v ON b.vendor_id = v.id 
    JOIN categories c ON v.category_id = c.id 
    WHERE b.customer_id = ? 
    ORDER BY b.event_date DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - EventPlaza</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-heart"></i> EventPlaza
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class="fas fa-calendar-alt"></i> My Bookings</a>
            <a href="../search.php"><i class="fas fa-search"></i> Find Vendors</a>
            <a href="../logout.php" style="margin-top: auto; color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header fade-in">
            <div class="welcome-text">
                <h1>My Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?></p>
            </div>
        </header>

        <!-- Stats Overview for User -->
        <section class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?= count($bookings) ?></h3>
                    <p>My Bookings</p>
                </div>
            </div>
            <div class="stat-card" style="border-left-color: var(--accent-color);">
                <div class="stat-icon" style="color: var(--accent-color); background: rgba(230, 57, 70, 0.1);"><i class="fas fa-search"></i></div>
                <div class="stat-info">
                    <h3>Explore</h3>
                    <p><a href="../search.php">Find More Vendors</a></p>
                </div>
            </div>
        </section>

        <!-- Bookings Table -->
        <div class="table-container fade-in">
            <h2 style="margin-bottom: 1.5rem; color: var(--secondary-color);">My Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Event Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($bookings): ?>
                        <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($booking['business_name']) ?></strong></td>
                                <td><?= htmlspecialchars($booking['category_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($booking['event_date'])) ?></td>
                                <td><span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span></td>
                                <td style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;padding-top:.8rem;">
                                        <a href="../vendor_profile.php?id=<?= $booking['vendor_id'] ?>" class="btn btn-outline" style="padding: 0.3rem 0.85rem; font-size: 0.8rem; border-radius: 20px; white-space:nowrap;">View Vendor</a>
                                        <a href="../receipt.php?booking_id=<?= $booking['id'] ?>" target="_blank"
                                           style="display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .85rem;background:var(--primary-color);color:#fff;font-size:.78rem;font-weight:600;border-radius:20px;text-decoration:none;white-space:nowrap;">
                                            <i class="fas fa-download" style="font-size:.7rem;"></i> Receipt
                                        </a>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 2rem;">No bookings found. <a href="../search.php" style="color:var(--primary-color);">Browse Vendors</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
    gsap.from(".fade-in", {
        y: 20,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: "power2.out"
    });
</script>

</body>
</html>
