<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// ============================================================
// ACTIONS — Vendor
// ============================================================
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE vendors SET is_verified = 1 WHERE id = ?")->execute([(int)$_GET['approve']]);
    setFlashMessage('success', 'Vendor approved!');
    redirect('dashboard.php');
}
if (isset($_GET['reject'])) {
    $pdo->prepare("UPDATE vendors SET is_verified = 0 WHERE id = ?")->execute([(int)$_GET['reject']]);
    setFlashMessage('error', 'Vendor rejected.');
    redirect('dashboard.php');
}
if (isset($_GET['delete_vendor'])) {
    $vid = (int)$_GET['delete_vendor'];
    $pdo->prepare("DELETE FROM vendors WHERE id = ?")->execute([$vid]);
    setFlashMessage('success', 'Vendor deleted.');
    redirect('dashboard.php');
}

// ============================================================
// ACTIONS — User / Customer
// ============================================================
if (isset($_GET['delete_user'])) {
    $uid = (int)$_GET['delete_user'];
    // Don't delete yourself
    if ($uid !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
        setFlashMessage('success', 'User deleted.');
    } else {
        setFlashMessage('error', 'You cannot delete your own account!');
    }
    redirect('dashboard.php');
}

// ============================================================
// UPDATE VENDOR (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vendor'])) {
    $vid = (int)$_POST['vendor_id'];
    $business_name = htmlspecialchars(strip_tags(trim($_POST['business_name'])));
    $location      = htmlspecialchars(strip_tags(trim($_POST['location'])));
    $price_range   = htmlspecialchars(strip_tags(trim($_POST['price_range'])));
    $description   = htmlspecialchars(strip_tags(trim($_POST['description'])));
    $category_id   = (int)$_POST['category_id'];
    $is_verified   = isset($_POST['is_verified']) ? 1 : 0;
    $phone         = htmlspecialchars(strip_tags(trim($_POST['phone'] ?? '')));
    $website       = htmlspecialchars(strip_tags(trim($_POST['website'] ?? '')));
    $facebook      = htmlspecialchars(strip_tags(trim($_POST['facebook'] ?? '')));
    $instagram     = htmlspecialchars(strip_tags(trim($_POST['instagram'] ?? '')));
    $whatsapp      = htmlspecialchars(strip_tags(trim($_POST['whatsapp'] ?? '')));
    $years_exp     = (int)($_POST['years_exp'] ?? 0);
    $events_done   = (int)($_POST['events_done'] ?? 0);

    $pdo->prepare("UPDATE vendors SET business_name=?, location=?, price_range=?, description=?, category_id=?, is_verified=?, phone=?, website=?, facebook=?, instagram=?, whatsapp=?, years_exp=?, events_done=? WHERE id=?")
        ->execute([$business_name, $location, $price_range, $description, $category_id, $is_verified, $phone, $website, $facebook, $instagram, $whatsapp, $years_exp ?: null, $events_done ?: null, $vid]);
    setFlashMessage('success', 'Vendor updated successfully!');
    redirect('dashboard.php');
}


// ============================================================
// UPDATE USER (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid      = (int)$_POST['user_id'];
    $username = htmlspecialchars(strip_tags(trim($_POST['username'])));
    $email    = htmlspecialchars(strip_tags(trim($_POST['email'])));
    $role     = in_array($_POST['role'], ['admin','vendor','customer']) ? $_POST['role'] : 'customer';

    $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?")
        ->execute([$username, $email, $role, $uid]);

    // Optional: change password if provided
    if (!empty($_POST['new_password']) && strlen($_POST['new_password']) >= 6) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, $uid]);
    }
    setFlashMessage('success', 'User updated successfully!');
    redirect('dashboard.php');
}

// ============================================================
// FETCH DATA
// ============================================================
$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_vendors  = $pdo->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_count  = $pdo->query("SELECT COUNT(*) FROM vendors WHERE is_verified = 0")->fetchColumn();

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// All vendors with category name and user email
$all_vendors = $pdo->query("
    SELECT v.*, c.name AS cat_name, u.email AS owner_email, u.username AS owner_username
    FROM vendors v
    JOIN categories c ON v.category_id = c.id
    JOIN users u ON v.user_id = u.id
    ORDER BY v.created_at DESC
")->fetchAll();

// All non-admin users (customers + vendors)
$all_users = $pdo->query("
    SELECT * FROM users ORDER BY created_at DESC
")->fetchAll();

// Which vendor_id to edit?
$edit_vendor_id = isset($_GET['edit_vendor']) ? (int)$_GET['edit_vendor'] : 0;
$edit_user_id   = isset($_GET['edit_user'])   ? (int)$_GET['edit_user']   : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — EventPlaza</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        /* ===== ADMIN LAYOUT ===== */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: var(--font-body); background: #f0f2f5; color: var(--text-color); }

        .adm-wrap { display: flex; min-height: 100vh; }

        /* Sidebar */
        .adm-sidebar {
            width: 260px;
            background: #1A1A1A;
            color: #fff;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .adm-sidebar-logo {
            padding: 1.75rem 1.5rem 1.25rem;
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: 800;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .adm-sidebar-logo span { color: var(--primary-color); }
        .adm-sidebar-logo i { color: var(--primary-color); font-size: 1.2rem; }

        .adm-nav { padding: 1rem 0.75rem; flex: 1; }
        .adm-nav-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: rgba(255,255,255,0.3);
            padding: 1rem .75rem .4rem;
        }
        .adm-nav a {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem .85rem;
            border-radius: 10px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: .9rem;
            font-weight: 500;
            transition: all .2s;
            margin-bottom: 2px;
        }
        .adm-nav a i { width: 18px; text-align: center; font-size: .95rem; }
        .adm-nav a:hover { background: rgba(255,255,255,0.07); color: #fff; }
        .adm-nav a.active { background: var(--primary-color); color: #fff; box-shadow: 0 4px 14px rgba(212,175,55,0.35); }
        .adm-nav a.danger { color: #ff7070; }
        .adm-nav a.danger:hover { background: rgba(220,53,69,0.15); color: #ff4444; }

        /* Main */
        .adm-main { flex: 1; min-width: 0; padding: 2rem; }

        /* Top bar */
        .adm-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .adm-page-title { font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--secondary-color); }
        .adm-page-sub { font-size: .87rem; color: var(--text-light); margin-top: .2rem; }
        .adm-admin-badge {
            background: var(--primary-color);
            color: #fff;
            border-radius: 100px;
            padding: .35rem .9rem;
            font-size: .8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        /* Flash messages */
        .adm-flash {
            padding: .85rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .9rem;
            font-weight: 500;
        }
        .adm-flash.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .adm-flash.error   { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        /* Stats */
        .adm-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 1.25rem; margin-bottom: 2rem; }
        .adm-stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.4rem 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid var(--primary-color);
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }
        .adm-stat-card.warn { border-left-color: #ffc107; }
        .adm-stat-card.info { border-left-color: #0dcaf0; }
        .adm-stat-card.dang { border-left-color: #dc3545; }
        .adm-stat-icon { font-size: 1.4rem; color: var(--primary-color); margin-bottom: .25rem; }
        .adm-stat-card.warn .adm-stat-icon { color: #ffc107; }
        .adm-stat-card.info .adm-stat-icon { color: #0dcaf0; }
        .adm-stat-card.dang .adm-stat-icon { color: #dc3545; }
        .adm-stat-num { font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: var(--secondary-color); line-height: 1; }
        .adm-stat-lbl { font-size: .8rem; color: var(--text-light); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }

        /* Tabs */
        .adm-tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e9ecef; }
        .adm-tab {
            padding: .65rem 1.4rem;
            border-radius: 8px 8px 0 0;
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all .2s;
            font-family: var(--font-body);
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .adm-tab:hover { color: var(--secondary-color); background: rgba(0,0,0,0.03); }
        .adm-tab.active { color: var(--primary-color); border-bottom: 3px solid var(--primary-color); margin-bottom: -2px; background: #fff; }
        .adm-tab-count { background: var(--primary-color); color: #fff; border-radius: 100px; padding: .1rem .5rem; font-size: .73rem; }

        /* Panel */
        .adm-panel { display: none; }
        .adm-panel.show { display: block; }

        /* Section card */
        .adm-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .adm-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .adm-card-title { font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--secondary-color); display: flex; align-items: center; gap: .5rem; }
        .adm-card-title i { color: var(--primary-color); }

        /* Table */
        .adm-table-wrap { overflow-x: auto; }
        table.adm-table { width: 100%; border-collapse: collapse; }
        .adm-table th { text-align: left; padding: .85rem 1rem; font-size: .77rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-light); background: #fafafa; border-bottom: 1px solid #f0f0f0; }
        .adm-table td { padding: .9rem 1rem; font-size: .88rem; color: var(--text-color); border-bottom: 1px solid #f8f8f8; vertical-align: middle; }
        .adm-table tr:last-child td { border-bottom: none; }
        .adm-table tr:hover td { background: #fafafa; }
        .adm-table .adm-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(212,175,55,0.15); color: var(--primary-color); font-weight: 700; font-size: .85rem; display: inline-flex; align-items: center; justify-content: center; }

        /* Badges */
        .badge-role { display: inline-flex; align-items: center; padding: .25rem .7rem; border-radius: 100px; font-size: .75rem; font-weight: 700; }
        .badge-admin    { background: #fff3cd; color: #856404; }
        .badge-vendor   { background: #cce5ff; color: #004085; }
        .badge-customer { background: #d4edda; color: #155724; }
        .badge-verified   { background: #d4edda; color: #155724; }
        .badge-unverified { background: #fff3cd; color: #856404; }

        /* Action buttons */
        .adm-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .38rem .85rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-family: var(--font-body); transition: all .2s; }
        .adm-btn-edit   { background: #e8f4fd; color: #0d6efd; }
        .adm-btn-edit:hover { background: #0d6efd; color: #fff; }
        .adm-btn-ok     { background: #d4edda; color: #155724; }
        .adm-btn-ok:hover { background: #28a745; color: #fff; }
        .adm-btn-warn   { background: #fff3cd; color: #856404; }
        .adm-btn-warn:hover { background: #ffc107; color: #fff; }
        .adm-btn-del    { background: #f8d7da; color: #721c24; }
        .adm-btn-del:hover { background: #dc3545; color: #fff; }
        .adm-btn-primary { background: var(--primary-color); color: #fff; }
        .adm-btn-primary:hover { background: var(--primary-dark); }
        .adm-btn-cancel  { background: #e9ecef; color: #495057; }
        .adm-btn-cancel:hover { background: #dee2e6; }

        /* Inline Edit Form */
        .adm-edit-row td { background: #fffdf0 !important; }
        .adm-edit-form { padding: 1.5rem; border-top: 2px solid var(--primary-color); background: #fffdf0; }
        .adm-form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .adm-form-group label { display: block; font-size: .78rem; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: .06em; margin-bottom: .4rem; }
        .adm-form-group input,
        .adm-form-group select,
        .adm-form-group textarea { width: 100%; padding: .6rem .85rem; border: 1.5px solid #dee2e6; border-radius: 9px; font-size: .9rem; font-family: var(--font-body); color: var(--secondary-color); background: #fff; transition: border-color .2s; }
        .adm-form-group input:focus,
        .adm-form-group select:focus,
        .adm-form-group textarea:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(212,175,55,.15); }
        .adm-form-group textarea { resize: vertical; min-height: 80px; }
        .adm-form-actions { display: flex; gap: .75rem; flex-wrap: wrap; }
        .adm-checkbox-row { display: flex; align-items: center; gap: .5rem; font-size: .9rem; font-weight: 500; }
        .adm-checkbox-row input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary-color); }

        /* Empty */
        .adm-empty { text-align: center; padding: 2.5rem; color: var(--text-light); font-size: .9rem; }
        .adm-empty i { font-size: 2rem; display: block; margin-bottom: .5rem; opacity: .4; }

        /* Search bar inside table */
        .adm-table-search { padding: .7rem 1.2rem; border: 1.5px solid #e9ecef; border-radius: 9px; font-size: .88rem; font-family: var(--font-body); width: 240px; }
        .adm-table-search:focus { outline: none; border-color: var(--primary-color); }

        @media(max-width:768px) {
            .adm-sidebar { display: none; }
            .adm-main { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="adm-wrap">

    <!-- ===== SIDEBAR ===== -->
    <aside class="adm-sidebar">
        <div class="adm-sidebar-logo">
            <i class="fas fa-shield-alt"></i>
            Event<span>Plaza</span>
        </div>
        <nav class="adm-nav">
            <div class="adm-nav-label">Management</div>
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#vendors-tab" onclick="switchTab('vendors')"><i class="fas fa-store"></i> Vendors</a>
            <a href="#users-tab" onclick="switchTab('users')"><i class="fas fa-users"></i> Customers</a>
            <div class="adm-nav-label">Site</div>
            <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Website</a>
            <a href="../search.php" target="_blank"><i class="fas fa-search"></i> Search Page</a>
            <div class="adm-nav-label">Account</div>
            <a href="../logout.php" class="danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
        <div style="padding:1rem; font-size:.75rem; color:rgba(255,255,255,.25); border-top:1px solid rgba(255,255,255,.07);">
            Logged in as <strong style="color:rgba(255,255,255,.5);"><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="adm-main">

        <!-- Top Bar -->
        <div class="adm-topbar">
            <div>
                <div class="adm-page-title">Admin Dashboard</div>
                <div class="adm-page-sub">Manage vendors, customers, and platform content</div>
            </div>
            <div class="adm-admin-badge"><i class="fas fa-shield-alt"></i> Administrator</div>
        </div>

        <!-- Flash Messages -->
        <?php if($msg = getFlashMessage('success')): ?>
            <div class="adm-flash success"><i class="fas fa-check-circle"></i> <?= $msg ?></div>
        <?php endif; ?>
        <?php if($msg = getFlashMessage('error')): ?>
            <div class="adm-flash error"><i class="fas fa-exclamation-circle"></i> <?= $msg ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="adm-stats">
            <div class="adm-stat-card">
                <div class="adm-stat-icon"><i class="fas fa-users"></i></div>
                <div class="adm-stat-num"><?= $total_users ?></div>
                <div class="adm-stat-lbl">Total Users</div>
            </div>
            <div class="adm-stat-card info">
                <div class="adm-stat-icon"><i class="fas fa-store"></i></div>
                <div class="adm-stat-num"><?= $total_vendors ?></div>
                <div class="adm-stat-lbl">Total Vendors</div>
            </div>
            <div class="adm-stat-card warn">
                <div class="adm-stat-icon"><i class="fas fa-clock"></i></div>
                <div class="adm-stat-num"><?= $pending_count ?></div>
                <div class="adm-stat-lbl">Pending Approval</div>
            </div>
            <div class="adm-stat-card dang">
                <div class="adm-stat-icon"><i class="fas fa-bookmark"></i></div>
                <div class="adm-stat-num"><?= $total_bookings ?></div>
                <div class="adm-stat-lbl">Total Bookings</div>
            </div>
        </div>

        <!-- TABS -->
        <div class="adm-tabs" id="adm-tabs">
            <button class="adm-tab active" onclick="switchTab('vendors')" id="tab-vendors">
                <i class="fas fa-store"></i> Vendors
                <span class="adm-tab-count"><?= count($all_vendors) ?></span>
            </button>
            <button class="adm-tab" onclick="switchTab('users')" id="tab-users">
                <i class="fas fa-users"></i> Users & Customers
                <span class="adm-tab-count"><?= count($all_users) ?></span>
            </button>
        </div>

        <!-- ========================
             TAB: VENDORS
        ======================== -->
        <div class="adm-panel show" id="panel-vendors">
            <div class="adm-card">
                <div class="adm-card-header">
                    <div class="adm-card-title"><i class="fas fa-store"></i> All Vendors</div>
                    <input type="text" class="adm-table-search" placeholder="Search vendors..." onkeyup="filterTable(this,'vendor-tbody')">
                </div>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Business</th>
                                <th>Owner</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vendor-tbody">
                        <?php if($all_vendors): ?>
                            <?php foreach($all_vendors as $i => $v): ?>
                                <tr>
                                    <td style="color:var(--text-light);font-size:.8rem;"><?= $i+1 ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.6rem;">
                                            <div class="adm-avatar"><?= strtoupper(substr($v['business_name'],0,1)) ?></div>
                                            <strong><?= htmlspecialchars($v['business_name']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:.85rem;"><?= htmlspecialchars($v['owner_username']) ?></div>
                                        <div style="font-size:.78rem;color:var(--text-light);"><?= htmlspecialchars($v['owner_email']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($v['cat_name']) ?></td>
                                    <td><?= htmlspecialchars($v['location'] ?: '—') ?></td>
                                    <td><?= htmlspecialchars($v['price_range'] ?: '—') ?></td>
                                    <td>
                                        <?php if($v['is_verified']): ?>
                                            <span class="badge-role badge-verified"><i class="fas fa-check-circle"></i>&nbsp;Verified</span>
                                        <?php else: ?>
                                            <span class="badge-role badge-unverified"><i class="fas fa-clock"></i>&nbsp;Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                            <?php if(!$v['is_verified']): ?>
                                                <a href="dashboard.php?approve=<?= $v['id'] ?>" class="adm-btn adm-btn-ok" title="Approve"><i class="fas fa-check"></i></a>
                                            <?php else: ?>
                                                <a href="dashboard.php?reject=<?= $v['id'] ?>" class="adm-btn adm-btn-warn" title="Revoke"><i class="fas fa-ban"></i></a>
                                            <?php endif; ?>
                                            <a href="dashboard.php?edit_vendor=<?= $v['id'] ?>#edit-vendor-<?= $v['id'] ?>" class="adm-btn adm-btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                            <a href="dashboard.php?delete_vendor=<?= $v['id'] ?>" class="adm-btn adm-btn-del" title="Delete" onclick="return confirm('Delete vendor &quot;<?= htmlspecialchars($v['business_name'], ENT_QUOTES) ?>&quot;? This cannot be undone.')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Inline Edit Row -->
                                <?php if($edit_vendor_id === $v['id']): ?>
                                <tr id="edit-vendor-<?= $v['id'] ?>">
                                    <td colspan="8" style="padding:0;">
                                        <div class="adm-edit-form">
                                            <h4 style="font-family:var(--font-heading);font-size:1rem;color:var(--secondary-color);margin-bottom:1rem;">
                                                <i class="fas fa-pen" style="color:var(--primary-color);"></i> Edit Vendor — <?= htmlspecialchars($v['business_name']) ?>
                                            </h4>
                                            <form method="POST" action="dashboard.php">
                                                <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                                <div class="adm-form-grid">
                                                    <div class="adm-form-group">
                                                        <label>Business Name</label>
                                                        <input type="text" name="business_name" value="<?= htmlspecialchars($v['business_name']) ?>" required>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Category</label>
                                                        <select name="category_id">
                                                            <?php foreach($cats as $c): ?>
                                                                <option value="<?= $c['id'] ?>" <?= $c['id']==$v['category_id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Location</label>
                                                        <input type="text" name="location" value="<?= htmlspecialchars($v['location']) ?>">
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Price Range</label>
                                                        <select name="price_range">
                                                            <option value="$"   <?= $v['price_range']==='$'  ?'selected':'' ?>>$ — Budget</option>
                                                            <option value="$$"  <?= $v['price_range']==='$$' ?'selected':'' ?>>$$ — Standard</option>
                                                            <option value="$$$" <?= $v['price_range']==='$$$'?'selected':'' ?>>$$$ — Premium</option>
                                                        </select>
                                                    </div>
                                                    <div class="adm-form-group" style="grid-column:1/-1;">
                                                        <label>Description</label>
                                                        <textarea name="description"><?= htmlspecialchars($v['description']) ?></textarea>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Verification</label>
                                                        <label class="adm-checkbox-row">
                                                            <input type="checkbox" name="is_verified" value="1" <?= $v['is_verified']?'checked':'' ?>>
                                                            Mark as Verified
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="adm-form-actions">
                                                    <button type="submit" name="update_vendor" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                                    <a href="dashboard.php" class="adm-btn adm-btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8"><div class="adm-empty"><i class="fas fa-store-slash"></i>No vendors registered yet.</div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================
             TAB: USERS
        ======================== -->
        <div class="adm-panel" id="panel-users">
            <div class="adm-card">
                <div class="adm-card-header">
                    <div class="adm-card-title"><i class="fas fa-users"></i> All Users</div>
                    <input type="text" class="adm-table-search" placeholder="Search users..." onkeyup="filterTable(this,'user-tbody')">
                </div>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user-tbody">
                        <?php if($all_users): ?>
                            <?php foreach($all_users as $i => $u): ?>
                                <tr>
                                    <td style="color:var(--text-light);font-size:.8rem;"><?= $i+1 ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.6rem;">
                                            <div class="adm-avatar"><?= strtoupper(substr($u['username'],0,1)) ?></div>
                                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                                            <?php if($u['id'] == $_SESSION['user_id']): ?><span style="font-size:.72rem;color:var(--primary-color);font-weight:600;">(You)</span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="font-size:.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge-role badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                                    </td>
                                    <td style="font-size:.82rem;color:var(--text-light);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                            <a href="dashboard.php?edit_user=<?= $u['id'] ?>#edit-user-<?= $u['id'] ?>" class="adm-btn adm-btn-edit" title="Edit"><i class="fas fa-pen"></i> Edit</a>
                                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                                <a href="dashboard.php?delete_user=<?= $u['id'] ?>" class="adm-btn adm-btn-del" title="Delete" onclick="return confirm('Delete user &quot;<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>&quot;? All their data will be removed.')"><i class="fas fa-trash"></i> Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Inline Edit Row -->
                                <?php if($edit_user_id === $u['id']): ?>
                                <tr id="edit-user-<?= $u['id'] ?>">
                                    <td colspan="6" style="padding:0;">
                                        <div class="adm-edit-form">
                                            <h4 style="font-family:var(--font-heading);font-size:1rem;color:var(--secondary-color);margin-bottom:1rem;">
                                                <i class="fas fa-user-edit" style="color:var(--primary-color);"></i> Edit User — <?= htmlspecialchars($u['username']) ?>
                                            </h4>
                                            <form method="POST" action="dashboard.php">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <div class="adm-form-grid">
                                                    <div class="adm-form-group">
                                                        <label>Username</label>
                                                        <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>Role</label>
                                                        <select name="role">
                                                            <option value="customer" <?= $u['role']==='customer'?'selected':'' ?>>Customer</option>
                                                            <option value="vendor"   <?= $u['role']==='vendor'  ?'selected':'' ?>>Vendor</option>
                                                            <option value="admin"    <?= $u['role']==='admin'   ?'selected':'' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="adm-form-group">
                                                        <label>New Password <span style="font-weight:400;color:var(--text-light);">(leave blank to keep)</span></label>
                                                        <input type="password" name="new_password" placeholder="Min. 6 characters">
                                                    </div>
                                                </div>
                                                <div class="adm-form-actions">
                                                    <button type="submit" name="update_user" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                                    <a href="dashboard.php" class="adm-btn adm-btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6"><div class="adm-empty"><i class="fas fa-users-slash"></i>No users found.</div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
// Tab switching
function switchTab(name) {
    document.querySelectorAll('.adm-panel').forEach(p => p.classList.remove('show'));
    document.querySelectorAll('.adm-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('show');
    document.getElementById('tab-' + name).classList.add('active');
    return false;
}

// Auto-open correct tab if edit_vendor or edit_user param is set
<?php if($edit_vendor_id): ?>
    switchTab('vendors');
    setTimeout(function(){ var el = document.getElementById('edit-vendor-<?= $edit_vendor_id ?>'); if(el) el.scrollIntoView({behavior:'smooth',block:'center'}); }, 200);
<?php elseif($edit_user_id): ?>
    switchTab('users');
    setTimeout(function(){ var el = document.getElementById('edit-user-<?= $edit_user_id ?>'); if(el) el.scrollIntoView({behavior:'smooth',block:'center'}); }, 200);
<?php endif; ?>

// Sidebar nav tab links
document.querySelectorAll('.adm-nav a[href^="#"]').forEach(function(link){
    link.addEventListener('click', function(e){ e.preventDefault(); });
});

// Table live search
function filterTable(input, tbodyId) {
    const val = input.value.toLowerCase();
    const rows = document.getElementById(tbodyId).querySelectorAll('tr');
    rows.forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
}

// GSAP
gsap.from('.adm-stat-card', { y: 20, opacity: 0, duration: 0.5, stagger: 0.08, ease: 'power2.out' });
</script>
</body>
</html>
