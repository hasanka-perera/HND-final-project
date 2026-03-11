<?php
/**
 * ADMIN RESET SCRIPT
 * Run this ONCE in your browser: http://localhost/darshana/admin_reset.php
 * DELETE this file immediately after running it!
 */
include 'includes/db.php';

// ---- CHANGE THESE IF YOU WANT ----
$admin_username = 'admin';
$admin_email    = 'admin@eventplaza.com';
$admin_password = 'admin1234';
// ----------------------------------

$hashed = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$admin_username, $admin_email]);
$existing = $stmt->fetch();

if ($existing) {
    // Update existing admin's password
    $stmt2 = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE id = ?");
    $stmt2->execute([$hashed, $existing['id']]);
    $action = "✅ Admin password updated successfully!";
} else {
    // Insert fresh admin user
    $stmt2 = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt2->execute([$admin_username, $admin_email, $hashed]);
    $action = "✅ Admin user created successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reset</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Outfit',sans-serif; background:#f4f1e8; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .box { background:#fff; border-radius:18px; padding:3rem; max-width:500px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.1); border-top:4px solid #D4AF37; text-align:center; }
        h1 { font-size:1.8rem; font-weight:800; color:#1A1A1A; margin-bottom:0.5rem; }
        .badge { display:inline-block; background:#D4AF37; color:#fff; padding:.4rem 1.2rem; border-radius:100px; font-size:.85rem; font-weight:600; margin-bottom:1.5rem; }
        .result { background:#f0f9f0; border:1.5px solid #4caf50; border-radius:12px; padding:1.2rem; color:#2e7d32; font-size:1.05rem; margin-bottom:1.5rem; }
        table { width:100%; border-collapse:collapse; text-align:left; margin-bottom:1.5rem; }
        td { padding:.55rem .75rem; font-size:.9rem; }
        td:first-child { color:#888; font-weight:600; width:140px; }
        td:last-child { color:#1A1A1A; font-weight:700; font-family:monospace; background:#f9f6ee; border-radius:6px; }
        .warning { background:#fff8e1; border:1.5px solid #ffc107; border-radius:12px; padding:1rem 1.2rem; color:#795548; font-size:.88rem; margin-bottom:1.5rem; }
        .btns { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; }
        a.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.65rem 1.5rem; border-radius:10px; font-size:.9rem; font-weight:600; text-decoration:none; transition:all .2s; }
        .btn-gold { background:#D4AF37; color:#fff; }
        .btn-gold:hover { background:#B5952F; }
        .btn-dark { background:#1A1A1A; color:#fff; }
        .btn-dark:hover { background:#333; }
    </style>
</head>
<body>
<div class="box">
    <h1>🔐 Admin Reset</h1>
    <div class="badge">One-time Setup Script</div>

    <div class="result"><?= $action ?></div>

    <table>
        <tr><td>Username</td><td><?= htmlspecialchars($admin_username) ?></td></tr>
        <tr><td>Password</td><td><?= htmlspecialchars($admin_password) ?></td></tr>
        <tr><td>Email</td><td><?= htmlspecialchars($admin_email) ?></td></tr>
        <tr><td>Role</td><td>admin</td></tr>
    </table>

    <div class="warning">
        ⚠️ <strong>Security Warning:</strong> Delete <code>admin_reset.php</code> immediately after using this page! Anyone with this URL can reset your admin password.
    </div>

    <div class="btns">
        <a href="login.php" class="btn btn-gold">🔑 Go to Login</a>
        <a href="admin/dashboard.php" class="btn btn-dark">⚡ Admin Dashboard</a>
    </div>
</div>
</body>
</html>
