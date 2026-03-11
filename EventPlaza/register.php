<?php
include 'includes/db.php';
include 'includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username         = sanitize($_POST['username']);
    $email            = sanitize($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role             = sanitize($_POST['role']);

    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'That email or username is already taken.';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role]);
            $user_id = $pdo->lastInsertId();

            if ($role === 'vendor') {
                $category_id   = isset($_POST['category_id'])   ? (int)$_POST['category_id']       : 1;
                $business_name = isset($_POST['business_name']) ? sanitize($_POST['business_name']) : $username . "'s Business";
                $location      = isset($_POST['v_location'])    ? sanitize($_POST['v_location'])    : 'City';
                $price_range   = isset($_POST['price_range'])   ? sanitize($_POST['price_range'])   : '$$';
                $description   = 'Please update your profile with more details.';

                $stmt_v = $pdo->prepare("INSERT INTO vendors (user_id, category_id, business_name, description, price_range, location) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_v->execute([$user_id, $category_id, $business_name, $description, $price_range, $location]);
            }

            $_SESSION['user_id']  = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $role;

            redirect($role === 'vendor' ? 'vendor/dashboard.php' : 'user/dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventPlaza</title>
    <meta name="description" content="Create your EventPlaza account as a customer or vendor.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        /* Registration page overrides */
        .reg-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background:
                linear-gradient(rgba(26,26,26,0.88), rgba(26,26,26,0.92)),
                url('https://images.unsplash.com/photo-1519741497674-611481863552?w=1600&q=80') center/cover no-repeat;
        }
        .reg-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 22px;
            padding: 2.5rem;
            width: 100%;
            max-width: 620px;
            box-shadow: 0 30px 70px rgba(0,0,0,0.4);
            color: #fff;
        }
        .reg-logo {
            display: block;
            text-align: center;
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            margin-bottom: 0.4rem;
        }
        .reg-logo span { color: var(--primary-color); }
        .reg-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            margin-bottom: 1.75rem;
        }
        .reg-card h2 {
            text-align: center;
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        /* Error list */
        .reg-errors {
            background: rgba(220,53,69,0.15);
            border: 1px solid rgba(220,53,69,0.45);
            border-radius: 10px;
            padding: 0.9rem 1.2rem;
            margin-bottom: 1.25rem;
            list-style: none;
        }
        .reg-errors li {
            color: #ff8080;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .reg-errors li + li { margin-top: 0.35rem; }
        .reg-errors li::before { content: '✕'; font-weight: 700; }

        /* Role Switcher */
        .reg-role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem; }
        .reg-role-opt { position: relative; }
        .reg-role-opt input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .reg-role-lbl {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            padding: 1.1rem 0.75rem;
            border-radius: 13px;
            border: 2px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.22s ease;
            text-align: center;
        }
        .reg-role-lbl i { font-size: 1.5rem; color: rgba(255,255,255,0.45); transition: color 0.22s; }
        .reg-role-name { font-weight: 600; font-size: 0.93rem; color: rgba(255,255,255,0.9); }
        .reg-role-desc { font-size: 0.73rem; line-height: 1.3; color: rgba(255,255,255,0.5); }
        .reg-role-opt input:checked + .reg-role-lbl {
            border-color: var(--primary-color);
            background: rgba(212,175,55,0.12);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.2);
        }
        .reg-role-opt input:checked + .reg-role-lbl i { color: var(--primary-color); }

        /* Form layout */
        .reg-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media(max-width:500px) { .reg-2col { grid-template-columns: 1fr; } .reg-role-grid { grid-template-columns: 1fr; } }

        /* Labels & inputs inside dark card */
        .reg-card .form-group { margin-bottom: 1.1rem; }
        .reg-card .form-group label { display: block; margin-bottom: 0.45rem; font-size: 0.87rem; font-weight: 600; color: rgba(255,255,255,0.8); }
        .reg-card .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 10px;
            font-size: 0.95rem;
        }
        .reg-card .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .reg-card .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.2);
        }
        .reg-card select.form-control option { background: #222; color: #fff; }

        /* Vendor fields panel */
        .reg-vendor-panel {
            display: none;
            border: 1px solid rgba(212,175,55,0.3);
            border-radius: 13px;
            padding: 1.2rem;
            background: rgba(212,175,55,0.06);
            margin-bottom: 1rem;
        }
        .reg-vendor-panel.open { display: block; animation: regSlide 0.28s ease; }
        @keyframes regSlide { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .reg-vendor-heading {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Category picker */
        .reg-cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px,1fr)); gap: 0.5rem; }
        .reg-cat-opt { position: relative; }
        .reg-cat-opt input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .reg-cat-lbl {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.7rem 0.4rem;
            border-radius: 9px;
            border: 1.5px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            font-size: 0.78rem;
        }
        .reg-cat-lbl i { font-size: 1.15rem; color: rgba(255,255,255,0.4); transition: color 0.2s; }
        .reg-cat-opt input:checked + .reg-cat-lbl {
            border-color: var(--primary-color);
            background: rgba(212,175,55,0.14);
            color: #fff;
        }
        .reg-cat-opt input:checked + .reg-cat-lbl i { color: var(--primary-color); }

        .reg-footer-link { text-align: center; margin-top: 1.4rem; font-size: 0.9rem; color: rgba(255,255,255,0.6); }
        .reg-footer-link a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        .reg-footer-link a:hover { text-decoration: underline; }

        .reg-submit-btn { width: 100%; border-radius: 12px !important; padding: 0.9rem !important; font-size: 1rem !important; margin-top: 0.5rem; }
    </style>
</head>
<body>
<div class="reg-wrapper">
    <div class="reg-card fade-in">
        <a href="index.php" class="reg-logo">Event<span>Plaza</span></a>
        <p class="reg-subtitle">Join our community of event professionals & clients</p>
        <h2>Create Your Account</h2>

        <?php if(!empty($errors)): ?>
        <ul class="reg-errors">
            <?php foreach($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form action="register.php" method="POST" id="regForm">

            <!-- Role Switcher -->
            <div class="reg-role-grid">
                <div class="reg-role-opt">
                    <input type="radio" name="role" id="role_customer" value="customer"
                           <?= (!isset($_POST['role']) || $_POST['role']==='customer') ? 'checked' : '' ?>>
                    <label class="reg-role-lbl" for="role_customer">
                        <i class="fas fa-user"></i>
                        <span class="reg-role-name">Customer</span>
                        <span class="reg-role-desc">Browse & book services</span>
                    </label>
                </div>
                <div class="reg-role-opt">
                    <input type="radio" name="role" id="role_vendor" value="vendor"
                           <?= (isset($_POST['role']) && $_POST['role']==='vendor') ? 'checked' : '' ?>>
                    <label class="reg-role-lbl" for="role_vendor">
                        <i class="fas fa-store"></i>
                        <span class="reg-role-name">Vendor</span>
                        <span class="reg-role-desc">Offer your services</span>
                    </label>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="reg-2col">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user" style="color:var(--primary-color);margin-right:.3rem;"></i>Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                           placeholder="yourname" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope" style="color:var(--primary-color);margin-right:.3rem;"></i>Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           placeholder="you@example.com" required>
                </div>
            </div>
            <div class="reg-2col">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock" style="color:var(--primary-color);margin-right:.3rem;"></i>Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle" style="color:var(--primary-color);margin-right:.3rem;"></i>Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <!-- Vendor-Specific Fields -->
            <div class="reg-vendor-panel <?= (isset($_POST['role']) && $_POST['role']==='vendor') ? 'open' : '' ?>" id="vendorPanel">
                <div class="reg-vendor-heading"><i class="fas fa-store"></i> Vendor Details</div>

                <div class="form-group">
                    <label>Your Service Category</label>
                    <div class="reg-cat-grid">
                        <?php
                        $cat_icons = ['Photographers'=>'fa-camera','Caterers'=>'fa-utensils','Decorators'=>'fa-paint-brush','Venues'=>'fa-building','Musicians'=>'fa-music'];
                        $first = true;
                        foreach ($cats as $c):
                            $icon = $cat_icons[$c['name']] ?? 'fa-star';
                            $checked = (isset($_POST['category_id']) && $_POST['category_id']==$c['id']) || $first;
                            $first = false;
                        ?>
                        <div class="reg-cat-opt">
                            <input type="radio" name="category_id" id="cat_<?= $c['id'] ?>" value="<?= $c['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                            <label class="reg-cat-lbl" for="cat_<?= $c['id'] ?>">
                                <i class="fas <?= $icon ?>"></i>
                                <?= htmlspecialchars($c['name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="business_name"><i class="fas fa-briefcase" style="color:var(--primary-color);margin-right:.3rem;"></i>Business Name</label>
                    <input type="text" id="business_name" name="business_name" class="form-control"
                           value="<?= isset($_POST['business_name']) ? htmlspecialchars($_POST['business_name']) : '' ?>"
                           placeholder="e.g. Sunshine Photography">
                </div>
                <div class="reg-2col">
                    <div class="form-group">
                        <label for="v_location"><i class="fas fa-map-marker-alt" style="color:var(--primary-color);margin-right:.3rem;"></i>Location</label>
                        <input type="text" id="v_location" name="v_location" class="form-control"
                               value="<?= isset($_POST['v_location']) ? htmlspecialchars($_POST['v_location']) : '' ?>"
                               placeholder="e.g. Colombo">
                    </div>
                    <div class="form-group">
                        <label for="price_range"><i class="fas fa-tag" style="color:var(--primary-color);margin-right:.3rem;"></i>Price Range</label>
                        <select id="price_range" name="price_range" class="form-control">
                            <option value="$"   <?= (isset($_POST['price_range']) && $_POST['price_range']==='$')   ? 'selected' : '' ?>>$ — Budget Friendly</option>
                            <option value="$$"  <?= (!isset($_POST['price_range']) || $_POST['price_range']==='$$') ? 'selected' : '' ?>>$$ — Standard</option>
                            <option value="$$$" <?= (isset($_POST['price_range']) && $_POST['price_range']==='$$$') ? 'selected' : '' ?>>$$$ — Premium</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary reg-submit-btn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="reg-footer-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="role"]').forEach(function(r) {
        r.addEventListener('change', function() {
            const panel = document.getElementById('vendorPanel');
            if (panel) panel.classList.toggle('open', this.value === 'vendor');
        });
    });
    gsap.from('.reg-card', { y: 40, opacity: 0, duration: 0.8, ease: 'power3.out' });
</script>
</body>
</html>
