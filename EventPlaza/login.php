<?php
include 'includes/db.php';
include 'includes/functions.php';


$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['role'] === 'vendor') {
                redirect('vendor/dashboard.php');
            } else {
                redirect('user/dashboard.php');
            }
        } else {
            $message = '<div style="color:red;">Invalid username or password!</div>';
        }
    } catch (PDOException $e) {
        $message = '<div style="color:red;">Login failed.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventPlaza</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card fade-in">
        <div style="text-align: center; margin-bottom: 2rem;">
            <a href="index.php" style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: var(--white); text-decoration: none;">
                Event<span style="color: var(--primary-color);">Plaza</span>
            </a>
        </div>
        
        <h2>Welcome Back</h2>
        <?php echo $message; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 8px;">Login</button>
            <p style="margin-top: 1.5rem; text-align: center;">
                Don't have an account? <a href="register.php" style="font-weight: 600;">Register Now</a>
            </p>
        </form>
    </div>
</div>

<script>
    gsap.from(".fade-in", {
        y: 30,
        opacity: 0,
        duration: 1,
        ease: "power3.out"
    });
</script>

</body>
</html>
