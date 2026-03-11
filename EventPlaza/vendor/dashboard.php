<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'vendor') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch Vendor Details
$stmt = $pdo->prepare("SELECT * FROM vendors WHERE user_id = ?");
$stmt->execute([$user_id]);
$vendor = $stmt->fetch();

// Update Profile Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $business_name = sanitize($_POST['business_name']);
        $description = sanitize($_POST['description']);
        $price_range = sanitize($_POST['price_range']);
        $location = sanitize($_POST['location']);

        // Handle File Upload
        $profile_image = $vendor['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "../uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES["profile_image"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = "uploads/" . $file_name;
            }
        }

        $stmt_update = $pdo->prepare("UPDATE vendors SET business_name = ?, description = ?, price_range = ?, location = ?, profile_image = ? WHERE user_id = ?");
        if ($stmt_update->execute([$business_name, $description, $price_range, $location, $profile_image, $user_id])) {
            $message = '<div style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">Profile updated successfully!</div>';
            // Refresh data
            $stmt->execute([$user_id]);
            $vendor = $stmt->fetch();
        } else {
            $message = '<div style="background: #f8d7da; color: #721c24; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">Update failed.</div>';
        }
    } elseif (isset($_POST['upload_gallery'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $count = count($_FILES['gallery_images']['name']);
        $uploaded = 0;
        
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['gallery_images']['error'][$i] == 0) {
                $file_name = time() . '_' . $i . '_' . basename($_FILES['gallery_images']['name'][$i]);
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $target_file)) {
                    $image_path = "uploads/" . $file_name;
                    $stmt_gallery = $pdo->prepare("INSERT INTO vendor_gallery (vendor_id, image_path) VALUES (?, ?)");
                    $stmt_gallery->execute([$vendor['id'], $image_path]);
                    $uploaded++;
                }
            }
        }
        $message = '<div style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">' . $uploaded . ' images uploaded successfully!</div>';
    } elseif (isset($_POST['delete_image'])) {
        $image_id = $_POST['image_id'];
        // Ideally should verify ownership but simplified for now
        $stmt_del = $pdo->prepare("DELETE FROM vendor_gallery WHERE id = ? AND vendor_id = ?");
        $stmt_del->execute([$image_id, $vendor['id']]);
        $message = '<div style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">Image deleted.</div>';
    } elseif (isset($_POST['update_booking'])) {
        $booking_id = $_POST['booking_id'];
        $status = $_POST['status'];
        $stmt_book = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND vendor_id = ?");
        $stmt_book->execute([$status, $booking_id, $vendor['id']]);
        $message = '<div style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">Booking updated to ' . $status . '!</div>';
    }
}

// Fetch Gallery Images
$stmt_gallery = $pdo->prepare("SELECT * FROM vendor_gallery WHERE vendor_id = ?");
$stmt_gallery->execute([$vendor['id']]);
$gallery_images = $stmt_gallery->fetchAll();

// Fetch Bookings
$stmt_bookings = $pdo->prepare("
    SELECT b.*, u.username as customer_name, u.email as customer_email 
    FROM bookings b 
    JOIN users u ON b.customer_id = u.id 
    WHERE b.vendor_id = ? 
    ORDER BY b.event_date DESC
");
$stmt_bookings->execute([$vendor['id']]);
$bookings = $stmt_bookings->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - EventPlaza</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-crown"></i> EventPlaza
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="../vendor_profile.php?id=<?= $vendor['id'] ?>"><i class="fas fa-user-circle"></i> Public Profile</a>
            <a href="../index.php"><i class="fas fa-globe"></i> View Website</a>
            <a href="../logout.php" style="margin-top: auto; color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header fade-in">
            <div class="welcome-text">
                <h1>Welcome, <?= htmlspecialchars($vendor['business_name']) ?></h1>
                <p>Manage your business and bookings</p>
            </div>
            <div class="user-menu">
                <?php if($vendor['profile_image']): ?>
                    <img src="../<?= $vendor['profile_image'] ?>" alt="Profile" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                <?php else: ?>
                    <div style="width:40px; height:40px; background:var(--primary-color); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold;">
                        <?= substr($vendor['business_name'], 0, 1) ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <?= $message ?>

        <!-- Stats Overview -->
        <section class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?= count($bookings) ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3>4.8</h3>
                    <p>Average Rating</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <h3><?= $vendor['price_range'] ?></h3>
                    <p>Price Tier</p>
                </div>
            </div>
        </section>

        <!-- Profile Form -->
        <div class="table-container fade-in" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; color: var(--secondary-color);">Update Profile</h2>
            <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Business Name</label>
                        <input type="text" name="business_name" value="<?= htmlspecialchars($vendor['business_name']) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($vendor['location']) ?>" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Price / Packages</label>
                    <input type="text" name="price_range" value="<?= htmlspecialchars($vendor['price_range'] ?? '') ?>" class="form-control" placeholder="e.g. Starting from $200" required>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Description</label>
                    <textarea name="description" required><?= htmlspecialchars($vendor['description']) ?></textarea>
                    <script>CKEDITOR.replace('description');</script>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Profile Image</label>
                    <input type="file" name="profile_image" class="form-control" style="padding: 0.5rem;">
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Save Changes</button>
            </form>
        </div>

        <!-- Gallery Management -->
        <div class="table-container fade-in" style="margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1.5rem; color: var(--secondary-color);">Gallery Management</h2>
            
            <!-- Upload Form -->
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 2rem; background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                <input type="hidden" name="upload_gallery" value="1">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Upload New Images</label>
                    <input type="file" name="gallery_images[]" multiple class="form-control" style="padding: 0.5rem;" accept="image/*" required>
                    <small style="display:block; margin-top:0.5rem; color:#666;">You can select multiple images at once.</small>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Upload Images</button>
            </form>

            <!-- Display Images -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                <?php if(isset($gallery_images) && $gallery_images): ?>
                    <?php foreach($gallery_images as $img): ?>
                        <div style="position: relative; border-radius: 8px; overflow: hidden; height: 150px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <img src="../<?= $img['image_path'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <form action="dashboard.php" method="POST" style="position: absolute; top: 5px; right: 5px; margin: 0;">
                                <input type="hidden" name="delete_image" value="1">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" style="background: rgba(255,0,0,0.7); color: white; border: none; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;" onclick="return confirm('Delete this image?')"><i class="fas fa-times" style="font-size: 12px;"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #888;">No images in gallery yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="table-container fade-in">
            <h2 style="margin-bottom: 1.5rem; color: var(--secondary-color);">Recent Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Event Date</th>
                        <th>Details</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($bookings): ?>
                        <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($booking['customer_name']) ?></strong><br>
                                    <span style="font-size:0.85rem; color:var(--text-light);"><?= $booking['customer_email'] ?></span>
                                </td>
                                <td><?= date('M j, Y', strtotime($booking['event_date'])) ?></td>
                                <td><?= htmlspecialchars($booking['event_details']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                                    <?php if($booking['status'] == 'pending'): ?>
                                        <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                                            <form action="dashboard.php" method="POST" style="display:inline; margin:0;">
                                                <input type="hidden" name="update_booking" value="1">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" style="background:none; border:none; cursor:pointer; color:green;" title="Confirm"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="dashboard.php" method="POST" style="display:inline; margin:0;">
                                                <input type="hidden" name="update_booking" value="1">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" style="background:none; border:none; cursor:pointer; color:red;" title="Cancel"><i class="fas fa-times"></i></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-light);">No bookings yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
    // Simple entry animation
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
