<?php
/**
 * vendor_profile.php — Full-featured vendor profile page
 * New: phone, email, website, social links, inquiry form, stats, map, share
 */
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_GET['id'])) { redirect('search.php'); }
$vendor_id = (int)$_GET['id'];

// ── DB migrations (safe, run once) ─────────────────────────────────────────
try {
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS phone       VARCHAR(30)  DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS website     VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS facebook    VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS instagram   VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS whatsapp    VARCHAR(30)  DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS years_exp   INT          DEFAULT NULL");
    $pdo->exec("ALTER TABLE vendors ADD COLUMN IF NOT EXISTS events_done INT          DEFAULT NULL");
    $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS vendor_id   INT          DEFAULT NULL");
    $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS customer_id INT          DEFAULT NULL");
    $pdo->exec("ALTER TABLE reviews MODIFY COLUMN booking_id INT NULL");
} catch (Exception $e) { /* ignore */ }

// ── Fetch vendor ────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT v.*, c.name AS category_name, u.email AS owner_email
    FROM vendors v
    JOIN categories c ON v.category_id = c.id
    JOIN users u ON v.user_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$vendor_id]);
$vendor = $stmt->fetch();

if (!$vendor) {
    include 'includes/header.php';
    echo "<div class='container' style='padding:6rem 0;text-align:center;'><h2>Vendor not found.</h2><a href='search.php' class='btn btn-primary'>Back to Search</a></div>";
    include 'includes/footer.php';
    exit();
}

// ── Fetch gallery ───────────────────────────────────────────────────────────
$gallery = $pdo->prepare("SELECT * FROM vendor_gallery WHERE vendor_id = ?");
$gallery->execute([$vendor_id]);
$gallery_images = $gallery->fetchAll();

// ── Reviews & rating ────────────────────────────────────────────────────────
$stmt_r = $pdo->prepare("
    SELECT r.*, u.username FROM reviews r
    JOIN users u ON r.customer_id = u.id
    WHERE r.vendor_id = ? ORDER BY r.created_at DESC
");
$stmt_r->execute([$vendor_id]);
$reviews    = $stmt_r->fetchAll();
$avg_rating = 0;
$rating_dist = [5=>0,4=>0,3=>0,2=>0,1=>0];
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $r) { $sum += $r['rating']; $rating_dist[(int)$r['rating']]++; }
    $avg_rating = round($sum / count($reviews), 1);
}

// ── Total bookings count ────────────────────────────────────────────────────
$bk_count = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vendor_id = ?");
$bk_count->execute([$vendor_id]);
$total_bookings = $bk_count->fetchColumn();

// ── POST: Booking ───────────────────────────────────────────────────────────
$booking_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    if (!isLoggedIn()) { redirect('login.php'); }
    if ($_SESSION['role'] !== 'customer') {
        $booking_msg = '<div class="vp-alert error"><i class="fas fa-exclamation-circle"></i> Only customers can make bookings.</div>';
    } else {
        try {
            $s = $pdo->prepare("INSERT INTO bookings (customer_id, vendor_id, event_date, event_details) VALUES (?,?,?,?)");
            $s->execute([(int)$_SESSION['user_id'], $vendor_id, $_POST['event_date'], sanitize($_POST['event_details'])]);
            redirect('booking_confirm.php?booking_id=' . $pdo->lastInsertId());
        } catch (PDOException $e) {
            $booking_msg = '<div class="vp-alert error"><i class="fas fa-times-circle"></i> Booking failed: ' . $e->getMessage() . '</div>';
        }
    }
}

// ── POST: Review ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
        $booking_msg = '<div class="vp-alert error">Please login as a customer to review.</div>';
    } else {
        $s = $pdo->prepare("INSERT INTO reviews (vendor_id, customer_id, rating, comment) VALUES (?,?,?,?)");
        $s->execute([$vendor_id, (int)$_SESSION['user_id'], (int)$_POST['rating'], sanitize($_POST['comment'])]);
        redirect("vendor_profile.php?id=$vendor_id#reviews");
    }
}

// ── POST: Quick Inquiry ─────────────────────────────────────────────────────
$inquiry_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    $inq_name  = sanitize($_POST['inq_name']);
    $inq_email = sanitize($_POST['inq_email']);
    $inq_msg   = sanitize($_POST['inq_message']);
    $pdo->prepare("INSERT INTO inquiries (name, email, message) VALUES (?,?,?)")
        ->execute([$inq_name, $inq_email, "Inquiry to vendor '{$vendor['business_name']}' (ID:{$vendor_id}):\n$inq_msg"]);
    // Also try to mail vendor
    @mail($vendor['owner_email'], "New Inquiry — {$vendor['business_name']}",
        "From: $inq_name <$inq_email>\n\n$inq_msg",
        "From: EventPlaza <no-reply@eventplaza.com>");
    $inquiry_msg = 'sent';
}

// ── Helpers ─────────────────────────────────────────────────────────────────
$cat_icons = ['Photographers'=>'fa-camera','Caterers'=>'fa-utensils','Decorators'=>'fa-paint-brush','Venues'=>'fa-building','Musicians'=>'fa-music'];
$cat_icon  = $cat_icons[$vendor['category_name']] ?? 'fa-star';
$member_since = date('F Y', strtotime($vendor['created_at']));

include 'includes/header.php';
?>

<style>
/* ===== VENDOR PROFILE STYLES ===== */
.vp-cover {
    position: relative;
    height: 380px;
    overflow: hidden;
    background: #1a1a1a;
}
.vp-cover-img {
    width:100%; height:100%; object-fit:cover;
    filter: brightness(0.45);
    transition: transform 6s ease;
}
.vp-cover:hover .vp-cover-img { transform: scale(1.04); }
.vp-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
}

/* Top gold bar */
.vp-cover::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:4px;
    background: linear-gradient(90deg,transparent,#D4AF37,#f0d060,#D4AF37,transparent);
    z-index:3;
}

.vp-cover-content {
    position:absolute; bottom:0; left:0; right:0; z-index:2;
    padding:0 0 2rem;
}
.vp-meta {
    display:flex; align-items:flex-end; gap:1.75rem; flex-wrap:wrap;
}
.vp-logo {
    width:120px; height:120px;
    border-radius:20px;
    border:4px solid #fff;
    background:#fff;
    overflow:hidden;
    box-shadow:0 8px 30px rgba(0,0,0,0.3);
    flex-shrink:0;
}
.vp-logo-fallback {
    width:100%; height:100%;
    background: linear-gradient(135deg,#D4AF37,#c9a227);
    display:flex; align-items:center; justify-content:center;
    font-size:2.5rem; color:#fff; font-weight:800;
    font-family:var(--font-heading);
}
.vp-name-block { flex:1; min-width:220px; }
.vp-name-block h1 {
    font-family:var(--font-heading); font-size:2.2rem; font-weight:800;
    color:#fff; text-shadow:0 2px 8px rgba(0,0,0,0.5);
    margin-bottom:.35rem; line-height:1.1;
}
.vp-tags { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; }
.vp-tag {
    display:inline-flex; align-items:center; gap:.35rem;
    background:rgba(255,255,255,0.15); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,0.25);
    color:#fff; font-size:.8rem; font-weight:600;
    border-radius:100px; padding:.3rem .85rem;
}
.vp-tag.gold { background:rgba(212,175,55,0.25); border-color:rgba(212,175,55,0.5); color:#f0d060; }
.vp-tag i { font-size:.75rem; }

/* Share/action buttons top-right of cover */
.vp-cover-actions {
    position:absolute; top:1.25rem; right:1.25rem; z-index:3;
    display:flex; gap:.5rem;
}
.vp-ico-btn {
    width:38px; height:38px; border-radius:50%;
    background:rgba(255,255,255,0.15); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,0.25);
    color:#fff; font-size:.9rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .2s; text-decoration:none;
}
.vp-ico-btn:hover { background:rgba(212,175,55,0.5); border-color:#D4AF37; }

/* ── Main layout ── */
.vp-body { background:#f4f5f7; padding:2rem 0 4rem; }
.vp-layout { display:grid; grid-template-columns:1fr 360px; gap:1.75rem; }
@media(max-width:900px){ .vp-layout{grid-template-columns:1fr;} }

/* ── Stats bar ── */
.vp-stats {
    background:#fff; border-radius:16px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    display:grid; grid-template-columns:repeat(4,1fr);
    margin-bottom:1.5rem; overflow:hidden;
}
.vp-stat {
    text-align:center; padding:1.2rem .75rem;
    border-right:1px solid #f0f0f0;
    transition:background .2s;
}
.vp-stat:last-child{border-right:none;}
.vp-stat:hover{background:#fffdf5;}
.vp-stat-num { font-family:var(--font-heading); font-size:1.6rem; font-weight:800; color:var(--secondary-color); line-height:1; }
.vp-stat-lbl { font-size:.72rem; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:.05em; margin-top:.25rem; }
.vp-stat-icon { font-size:.85rem; color:var(--primary-color); margin-bottom:.25rem; }
@media(max-width:500px){.vp-stats{grid-template-columns:1fr 1fr;} .vp-stat:nth-child(2){border-right:none;}}

/* ── Section card ── */
.vp-card {
    background:#fff; border-radius:18px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    margin-bottom:1.5rem; overflow:hidden;
}
.vp-card-hdr {
    padding:.9rem 1.5rem;
    border-bottom:1px solid #f5f5f5;
    display:flex; align-items:center; justify-content:space-between;
    font-family:var(--font-heading); font-size:1.05rem; font-weight:700;
    color:var(--secondary-color);
}
.vp-card-hdr i { color:var(--primary-color); margin-right:.45rem; }
.vp-card-body { padding:1.5rem; }

/* ── Contact info ── */
.vp-contact-item {
    display:flex; align-items:center; gap:1rem;
    padding:.7rem 0; border-bottom:1px dashed #f0f0f0;
    font-size:.9rem;
}
.vp-contact-item:last-child{border-bottom:none;}
.vp-contact-icon {
    width:38px; height:38px; border-radius:10px;
    background:rgba(212,175,55,0.09); border:1px solid rgba(212,175,55,0.2);
    color:var(--primary-color); font-size:.9rem;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.vp-contact-lbl { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-light); }
.vp-contact-val { color:var(--secondary-color); font-weight:500; }
.vp-contact-val a { color:var(--secondary-color); text-decoration:none; }
.vp-contact-val a:hover { color:var(--primary-color); }

/* ── Social links ── */
.vp-socials { display:flex; gap:.6rem; flex-wrap:wrap; }
.vp-social-btn {
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.45rem 1rem; border-radius:100px;
    font-size:.82rem; font-weight:600; text-decoration:none;
    transition:all .22s; border:1.5px solid transparent;
}
.vp-social-fb  { background:#e8eef7; color:#1877F2; border-color:#bdd0f0; }
.vp-social-fb:hover  { background:#1877F2; color:#fff; }
.vp-social-ig  { background:#fceaf6; color:#E1306C; border-color:#f2b8d8; }
.vp-social-ig:hover  { background:#E1306C; color:#fff; }
.vp-social-wa  { background:#e6f9ef; color:#25D366; border-color:#a8e6c0; }
.vp-social-wa:hover  { background:#25D366; color:#fff; }
.vp-social-web { background:#f0f0f0; color:#555; border-color:#ddd; }
.vp-social-web:hover { background:#333; color:#fff; }

/* ── Rating breakdown ── */
.rating-big {
    display:flex; align-items:center; gap:1.5rem; margin-bottom:1.25rem;
}
.rating-score {
    font-family:var(--font-heading); font-size:3.5rem; font-weight:800;
    color:var(--secondary-color); line-height:1;
}
.rating-stars { color:#FFD700; font-size:1.1rem; letter-spacing:.05em; }
.rating-count { font-size:.82rem; color:var(--text-light); }
.rating-bars { flex:1; }
.rating-bar-row { display:flex; align-items:center; gap:.6rem; margin-bottom:.3rem; font-size:.8rem; }
.rating-bar-row .star-lbl { width:14px; text-align:right; color:var(--text-light); font-weight:600; }
.rating-bar-bg { flex:1; height:7px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
.rating-bar-fill { height:100%; background:#FFD700; border-radius:4px; transition:width 1s ease; }
.rating-bar-cnt { width:22px; text-align:right; color:var(--text-light); font-weight:600; }

/* ── Review cards ── */
.review-card {
    border-bottom:1px solid #f5f5f5; padding-bottom:1.25rem; margin-bottom:1.25rem;
}
.review-card:last-child { border-bottom:none; margin-bottom:0; }
.review-avatar {
    width:38px; height:38px; border-radius:50%;
    background:rgba(212,175,55,0.1); color:var(--primary-color);
    font-weight:700; font-size:.95rem;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.review-stars { color:#FFD700; font-size:.85rem; }

/* ── Review form ── */
.star-picker { display:flex; gap:.3rem; flex-direction:row-reverse; justify-content:flex-end; }
.star-picker input { display:none; }
.star-picker label { font-size:1.6rem; color:#ddd; cursor:pointer; transition:color .15s; }
.star-picker input:checked ~ label,
.star-picker label:hover,
.star-picker label:hover ~ label { color:#FFD700; }

/* ── Booking sidebar ── */
.vp-book-card {
    background:#fff; border-radius:18px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    overflow:hidden; position:sticky; top:90px;
}
.vp-book-hdr {
    background:linear-gradient(135deg,#1A1A1A,#2d2d2d);
    padding:1.25rem 1.5rem;
    display:flex; align-items:center; gap:.6rem;
}
.vp-book-hdr h3 { font-family:var(--font-heading); color:#fff; font-size:1.05rem; font-weight:700; margin:0; }
.vp-book-hdr i { color:var(--primary-color); }
.vp-book-body { padding:1.5rem; }
.vp-form-group { margin-bottom:1.1rem; }
.vp-form-group label { display:block; font-size:.8rem; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:.05em; margin-bottom:.4rem; }
.vp-form-group input,
.vp-form-group textarea,
.vp-form-group select {
    width:100%; border:1.5px solid #e9ecef; border-radius:11px;
    padding:.65rem .9rem; font-size:.9rem; font-family:var(--font-body);
    color:var(--secondary-color); transition:border-color .2s; background:#fff;
}
.vp-form-group input:focus,
.vp-form-group textarea:focus { outline:none; border-color:var(--primary-color); box-shadow:0 0 0 3px rgba(212,175,55,.12); }
.vp-form-group textarea { resize:vertical; min-height:90px; }
.vp-book-btn {
    width:100%; padding:.9rem; border:none; border-radius:13px;
    background:var(--primary-color); color:#fff;
    font-size:1rem; font-weight:700; cursor:pointer; font-family:var(--font-heading);
    box-shadow:0 6px 20px rgba(212,175,55,.35);
    transition:all .25s; display:flex; align-items:center; justify-content:center; gap:.55rem;
}
.vp-book-btn:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:0 10px 28px rgba(212,175,55,.45); }
.vp-book-divider { text-align:center; font-size:.78rem; color:var(--text-light); margin:1rem 0; display:flex; align-items:center; gap:.5rem; }
.vp-book-divider::before,.vp-book-divider::after { content:''; flex:1; height:1px; background:#f0f0f0; }

/* ── Inquiry form ── */
.vp-inquiry-tab { display:none; }
.vp-inquiry-tab.active { display:block; }
.vp-book-tabs { display:flex; border-bottom:2px solid #f0f0f0; }
.vp-book-tab-btn {
    flex:1; padding:.75rem; border:none; background:transparent;
    font-size:.85rem; font-weight:700; color:var(--text-light); cursor:pointer;
    font-family:var(--font-body); transition:all .2s; border-bottom:2px solid transparent; margin-bottom:-2px;
}
.vp-book-tab-btn.active { color:var(--primary-color); border-bottom-color:var(--primary-color); background:#fffdf5; }

/* ── Gallery ── */
.vp-gallery { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; }
.vp-gallery-item {
    border-radius:12px; overflow:hidden; aspect-ratio:1;
    cursor:pointer; position:relative;
}
.vp-gallery-item img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
.vp-gallery-item:hover img { transform:scale(1.08); }
.vp-gallery-item::after {
    content:'\f065'; font-family:'Font Awesome 6 Free'; font-weight:900;
    position:absolute; inset:0; background:rgba(0,0,0,0); color:transparent;
    display:flex; align-items:center; justify-content:center; font-size:1.5rem;
    transition:all .3s;
}
.vp-gallery-item:hover::after { background:rgba(0,0,0,0.4); color:#fff; }

/* ── Lightbox ── */
.vp-lightbox {
    position:fixed; inset:0; background:rgba(0,0,0,0.92);
    z-index:9999; display:none; align-items:center; justify-content:center;
}
.vp-lightbox.open { display:flex; animation:lbFade .25s ease; }
@keyframes lbFade { from{opacity:0} to{opacity:1} }
.vp-lightbox img { max-width:90vw; max-height:87vh; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.5); }
.vp-lb-close {
    position:absolute; top:1.25rem; right:1.5rem;
    font-size:2rem; color:#fff; cursor:pointer; opacity:.7; transition:opacity .2s; line-height:1;
}
.vp-lb-close:hover { opacity:1; }

/* ── Alerts ── */
.vp-alert { display:flex; align-items:center; gap:.6rem; padding:.85rem 1.1rem; border-radius:11px; margin-bottom:1rem; font-size:.9rem; font-weight:500; }
.vp-alert.error   { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
.vp-alert.success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }

/* ── Share toast ── */
#vp-share-toast { position:fixed; bottom:80px; left:50%; transform:translateX(-50%) translateY(15px); background:#1A1A1A; color:#fff; padding:.6rem 1.4rem; border-radius:100px; font-size:.875rem; font-weight:600; opacity:0; transition:all .3s; pointer-events:none; z-index:9990; white-space:nowrap; }
#vp-share-toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
</style>

<!-- ===== COVER ===== -->
<div class="vp-cover">
    <img class="vp-cover-img"
         src="https://images.unsplash.com/photo-1519225421980-715cb0202128?w=1600&q=80"
         alt="<?= htmlspecialchars($vendor['business_name']) ?> cover">
    <div class="vp-cover-overlay"></div>

    <!-- Top-right actions -->
    <div class="vp-cover-actions">
        <button class="vp-ico-btn" onclick="sharePage()" title="Share"><i class="fas fa-share-alt"></i></button>
        <a href="search.php" class="vp-ico-btn" title="Back to search"><i class="fas fa-arrow-left"></i></a>
    </div>

    <div class="vp-cover-content">
        <div class="container">
            <div class="vp-meta">
                <!-- Logo / Avatar -->
                <div class="vp-logo">
                    <?php if($vendor['profile_image']): ?>
                        <img src="<?= htmlspecialchars($vendor['profile_image']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="logo">
                    <?php else: ?>
                        <div class="vp-logo-fallback"><?= strtoupper(substr($vendor['business_name'],0,1)) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Name & Tags -->
                <div class="vp-name-block">
                    <h1><?= htmlspecialchars($vendor['business_name']) ?></h1>
                    <div class="vp-tags">
                        <span class="vp-tag"><i class="fas <?= $cat_icon ?>"></i><?= htmlspecialchars($vendor['category_name']) ?></span>
                        <?php if($vendor['location']): ?>
                        <span class="vp-tag"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($vendor['location']) ?></span>
                        <?php endif; ?>
                        <?php if($vendor['price_range']): ?>
                        <span class="vp-tag gold"><i class="fas fa-tag"></i><?= htmlspecialchars($vendor['price_range']) ?></span>
                        <?php endif; ?>
                        <?php if($vendor['is_verified']): ?>
                        <span class="vp-tag gold"><i class="fas fa-check-circle"></i>Verified</span>
                        <?php endif; ?>
                        <?php if($avg_rating > 0): ?>
                        <span class="vp-tag gold"><i class="fas fa-star"></i><?= $avg_rating ?>/5</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== BODY ===== -->
<div class="vp-body">
<div class="container">

    <!-- Stats bar -->
    <div class="vp-stats">
        <div class="vp-stat">
            <div class="vp-stat-icon"><i class="fas fa-star"></i></div>
            <div class="vp-stat-num"><?= $avg_rating ?: '—' ?></div>
            <div class="vp-stat-lbl">Avg Rating</div>
        </div>
        <div class="vp-stat">
            <div class="vp-stat-icon"><i class="fas fa-comments"></i></div>
            <div class="vp-stat-num"><?= count($reviews) ?></div>
            <div class="vp-stat-lbl">Reviews</div>
        </div>
        <div class="vp-stat">
            <div class="vp-stat-icon"><i class="fas fa-bookmark"></i></div>
            <div class="vp-stat-num"><?= $total_bookings ?></div>
            <div class="vp-stat-lbl">Bookings</div>
        </div>
        <div class="vp-stat">
            <div class="vp-stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="vp-stat-num"><?= $vendor['years_exp'] ?: '—' ?></div>
            <div class="vp-stat-lbl">Years Exp</div>
        </div>
    </div>

    <div class="vp-layout">

        <!-- ===== LEFT COLUMN ===== -->
        <div>

            <!-- About -->
            <div class="vp-card" id="about">
                <div class="vp-card-hdr"><span><i class="fas fa-info-circle"></i>About <?= htmlspecialchars($vendor['business_name']) ?></span></div>
                <div class="vp-card-body">
                    <div style="line-height:1.8;color:var(--text-color);font-size:.95rem;">
                        <?= $vendor['description'] ? nl2br(htmlspecialchars($vendor['description'])) : '<em style="color:var(--text-light)">No description provided yet.</em>' ?>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:1.25rem;">
                        <span style="background:#f0f0f0;border-radius:8px;padding:.4rem .85rem;font-size:.82rem;color:#555;display:inline-flex;align-items:center;gap:.35rem;">
                            <i class="fas fa-calendar-check" style="color:var(--primary-color);"></i> Member since <?= $member_since ?>
                        </span>
                        <?php if($vendor['events_done']): ?>
                        <span style="background:#f0f0f0;border-radius:8px;padding:.4rem .85rem;font-size:.82rem;color:#555;display:inline-flex;align-items:center;gap:.35rem;">
                            <i class="fas fa-check-circle" style="color:var(--primary-color);"></i> <?= $vendor['events_done'] ?>+ Events Completed
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="vp-card" id="contact">
                <div class="vp-card-hdr"><span><i class="fas fa-address-card"></i>Contact Information</span></div>
                <div class="vp-card-body">
                    <?php
                    $contacts = [
                        ['icon'=>'fa-envelope',     'label'=>'Email',    'val'=>$vendor['owner_email'], 'href'=>'mailto:'.$vendor['owner_email'],   'show'=>true],
                        ['icon'=>'fa-phone',        'label'=>'Phone',    'val'=>$vendor['phone'],       'href'=>'tel:'.$vendor['phone'],            'show'=>!empty($vendor['phone'])],
                        ['icon'=>'fa-map-marker-alt','label'=>'Location','val'=>$vendor['location'],   'href'=>null,                               'show'=>!empty($vendor['location'])],
                        ['icon'=>'fa-tag',          'label'=>'Price Range','val'=>$vendor['price_range'],'href'=>null,                             'show'=>!empty($vendor['price_range'])],
                        ['icon'=>'fa-globe',        'label'=>'Website',  'val'=>$vendor['website'],     'href'=>$vendor['website'],                'show'=>!empty($vendor['website'])],
                    ];
                    foreach($contacts as $c):
                        if(!$c['show']) continue;
                    ?>
                    <div class="vp-contact-item">
                        <div class="vp-contact-icon"><i class="fas <?= $c['icon'] ?>"></i></div>
                        <div>
                            <div class="vp-contact-lbl"><?= $c['label'] ?></div>
                            <div class="vp-contact-val">
                                <?php if($c['href']): ?>
                                    <a href="<?= htmlspecialchars($c['href']) ?>" target="<?= $c['label']==='Website'?'_blank':'' ?>">
                                        <?= htmlspecialchars($c['val']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($c['val']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Social Links -->
                    <?php $hasSocials = $vendor['facebook'] || $vendor['instagram'] || $vendor['whatsapp']; ?>
                    <?php if ($hasSocials): ?>
                    <div style="margin-top:1.1rem;padding-top:1.1rem;border-top:1px dashed #f0f0f0;">
                        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-light);margin-bottom:.75rem;">Follow / Connect</div>
                        <div class="vp-socials">
                            <?php if($vendor['facebook']): ?>
                            <a href="<?= htmlspecialchars($vendor['facebook']) ?>" target="_blank" class="vp-social-btn vp-social-fb">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <?php endif; ?>
                            <?php if($vendor['instagram']): ?>
                            <a href="<?= htmlspecialchars($vendor['instagram']) ?>" target="_blank" class="vp-social-btn vp-social-ig">
                                <i class="fab fa-instagram"></i> Instagram
                            </a>
                            <?php endif; ?>
                            <?php if($vendor['whatsapp']): ?>
                            <a href="https://wa.me/<?= preg_replace('/\D/','',$vendor['whatsapp']) ?>" target="_blank" class="vp-social-btn vp-social-wa">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <?php endif; ?>
                            <?php if($vendor['website']): ?>
                            <a href="<?= htmlspecialchars($vendor['website']) ?>" target="_blank" class="vp-social-btn vp-social-web">
                                <i class="fas fa-globe"></i> Website
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Portfolio Gallery -->
            <div class="vp-card" id="portfolio">
                <div class="vp-card-hdr">
                    <span><i class="fas fa-images"></i>Portfolio</span>
                    <span style="font-size:.8rem;font-weight:500;color:var(--text-light);"><?= count($gallery_images) ?> photos</span>
                </div>
                <div class="vp-card-body">
                    <?php if($gallery_images): ?>
                    <div class="vp-gallery">
                        <?php foreach($gallery_images as $img): ?>
                        <div class="vp-gallery-item" onclick="openLightbox('<?= htmlspecialchars($img['image_path']) ?>')">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Portfolio image" loading="lazy">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align:center;padding:2.5rem;color:var(--text-light);">
                        <i class="fas fa-images" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                        No portfolio images yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews -->
            <div class="vp-card" id="reviews">
                <div class="vp-card-hdr">
                    <span><i class="fas fa-star"></i>Reviews</span>
                    <span style="font-size:.8rem;font-weight:500;color:var(--text-light);"><?= count($reviews) ?> reviews</span>
                </div>
                <div class="vp-card-body">

                    <!-- Rating Summary -->
                    <?php if(count($reviews) > 0): ?>
                    <div class="rating-big">
                        <div>
                            <div class="rating-score"><?= $avg_rating ?></div>
                            <div class="rating-stars">
                                <?php for($i=1;$i<=5;$i++) echo $i<=$avg_rating ? '<i class="fas fa-star"></i>' : ($avg_rating>$i-1 ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>'); ?>
                            </div>
                            <div class="rating-count"><?= count($reviews) ?> reviews</div>
                        </div>
                        <div class="rating-bars">
                            <?php for($s=5;$s>=1;$s--): ?>
                            <div class="rating-bar-row">
                                <span class="star-lbl"><?= $s ?></span>
                                <div class="rating-bar-bg"><div class="rating-bar-fill" style="width:<?= count($reviews)>0 ? round($rating_dist[$s]/count($reviews)*100) : 0 ?>%"></div></div>
                                <span class="rating-bar-cnt"><?= $rating_dist[$s] ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <hr style="border:none;border-top:1px dashed #f0f0f0;margin:1.25rem 0;">
                    <?php endif; ?>

                    <!-- Write a Review -->
                    <?php if(isLoggedIn() && $_SESSION['role'] === 'customer'): ?>
                    <div style="background:#fffdf5;border:1.5px solid rgba(212,175,55,0.2);border-radius:14px;padding:1.25rem;margin-bottom:1.5rem;">
                        <h4 style="font-family:var(--font-heading);font-size:.95rem;margin-bottom:1rem;color:var(--secondary-color);display:flex;align-items:center;gap:.4rem;">
                            <i class="fas fa-pen" style="color:var(--primary-color);font-size:.85rem;"></i> Write a Review
                        </h4>
                        <form action="vendor_profile.php?id=<?= $vendor_id ?>#reviews" method="POST">
                            <input type="hidden" name="submit_review" value="1">
                            <div style="margin-bottom:.85rem;">
                                <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-light);display:block;margin-bottom:.4rem;">Your Rating</label>
                                <div class="star-picker">
                                    <?php for($s=5;$s>=1;$s--): ?>
                                    <input type="radio" id="star<?=$s?>" name="rating" value="<?=$s?>" <?=$s===5?'required':''?>>
                                    <label for="star<?=$s?>" title="<?=$s?> star">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="vp-form-group">
                                <label>Your Experience</label>
                                <textarea name="comment" rows="3" placeholder="Share your experience with this vendor..." required></textarea>
                            </div>
                            <button type="submit" class="vp-book-btn" style="font-size:.9rem;padding:.7rem;">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    </div>
                    <?php elseif(!isLoggedIn()): ?>
                    <div style="text-align:center;background:#f9f9f9;border-radius:12px;padding:1rem;margin-bottom:1.5rem;font-size:.9rem;color:var(--text-light);">
                        <a href="login.php" style="color:var(--primary-color);font-weight:700;">Login</a> to leave a review
                    </div>
                    <?php endif; ?>

                    <!-- Review list -->
                    <?php if($reviews): ?>
                        <?php foreach($reviews as $rv): ?>
                        <div class="review-card">
                            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                                <div class="review-avatar"><?= strtoupper(substr($rv['username'],0,1)) ?></div>
                                <div>
                                    <div style="font-weight:700;font-size:.9rem;color:var(--secondary-color);"><?= htmlspecialchars($rv['username']) ?></div>
                                    <div class="review-stars">
                                        <?php for($i=1;$i<=5;$i++) echo $i<=$rv['rating']?'<i class="fas fa-star"></i>':'<i class="far fa-star"></i>'; ?>
                                        <span style="font-size:.75rem;color:var(--text-light);margin-left:.25rem;"><?= date('d M Y',strtotime($rv['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div style="margin-left:auto;background:#f9f6ee;color:var(--primary-color);border-radius:6px;padding:.2rem .6rem;font-size:.8rem;font-weight:700;"><?= $rv['rating'] ?>/5</div>
                            </div>
                            <p style="color:#555;font-size:.88rem;line-height:1.6;margin:0;padding-left:50px;">"<?= htmlspecialchars($rv['comment']) ?>"</p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align:center;padding:1.5rem;color:var(--text-light);font-size:.9rem;">
                            <i class="fas fa-star" style="font-size:1.5rem;display:block;margin-bottom:.4rem;opacity:.3;"></i>
                            No reviews yet — be the first to review!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /left col -->

        <!-- ===== RIGHT SIDEBAR ===== -->
        <div>
            <div class="vp-book-card">

                <!-- Tab Switcher -->
                <div class="vp-book-tabs">
                    <button class="vp-book-tab-btn active" onclick="switchTab('book',this)"><i class="fas fa-calendar-alt"></i> Book Now</button>
                    <button class="vp-book-tab-btn" onclick="switchTab('inquiry',this)"><i class="fas fa-envelope"></i> Inquiry</button>
                </div>

                <!-- Booking Form -->
                <div id="tab-book" class="vp-inquiry-tab active">
                    <div class="vp-book-hdr">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Request Booking</h3>
                    </div>
                    <div class="vp-book-body">
                        <?= $booking_msg ?>
                        <?php if(isLoggedIn() && $_SESSION['role']==='customer'): ?>
                        <form action="vendor_profile.php?id=<?= $vendor_id ?>" method="POST">
                            <div class="vp-form-group">
                                <label><i class="fas fa-calendar"></i> Event Date</label>
                                <input type="date" name="event_date" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="vp-form-group">
                                <label><i class="fas fa-align-left"></i> Event Details</label>
                                <textarea name="event_details" placeholder="Venue, guest count, theme, special requirements..." required></textarea>
                            </div>
                            <button type="submit" name="book_now" class="vp-book-btn">
                                <i class="fas fa-paper-plane"></i> Send Booking Request
                            </button>
                        </form>
                        <?php elseif(!isLoggedIn()): ?>
                        <div style="text-align:center;padding:1rem 0;">
                            <i class="fas fa-lock" style="font-size:2rem;color:#ccc;margin-bottom:.75rem;display:block;"></i>
                            <p style="font-size:.88rem;color:var(--text-light);margin-bottom:1rem;">Please login to book this vendor</p>
                            <a href="login.php" class="vp-book-btn" style="text-decoration:none;"><i class="fas fa-sign-in-alt"></i> Login to Book</a>
                        </div>
                        <?php else: ?>
                        <div style="background:#fff3cd;border-radius:10px;padding:1rem;font-size:.88rem;color:#856404;text-align:center;">
                            <i class="fas fa-info-circle"></i> Only customers can make bookings.
                        </div>
                        <?php endif; ?>

                        <div class="vp-book-divider">or contact directly</div>
                        <div style="display:flex;flex-direction:column;gap:.6rem;">
                            <?php if($vendor['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($vendor['phone']) ?>" style="display:flex;align-items:center;gap:.65rem;padding:.7rem 1rem;border:1.5px solid #e9ecef;border-radius:11px;text-decoration:none;color:var(--secondary-color);font-size:.88rem;font-weight:600;transition:all .2s;" onmouseover="this.style.borderColor='#D4AF37'" onmouseout="this.style.borderColor='#e9ecef'">
                                <div style="width:34px;height:34px;background:rgba(212,175,55,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--primary-color);flex-shrink:0;"><i class="fas fa-phone"></i></div>
                                <?= htmlspecialchars($vendor['phone']) ?>
                            </a>
                            <?php endif; ?>
                            <?php if($vendor['whatsapp']): ?>
                            <a href="https://wa.me/<?= preg_replace('/\D/','',$vendor['whatsapp']) ?>" target="_blank" style="display:flex;align-items:center;gap:.65rem;padding:.7rem 1rem;border:1.5px solid #a8e6c0;border-radius:11px;text-decoration:none;color:#155724;font-size:.88rem;font-weight:600;background:#edf9f1;transition:all .2s;">
                                <div style="width:34px;height:34px;background:#d4edda;border-radius:9px;display:flex;align-items:center;justify-content:center;color:#25D366;flex-shrink:0;"><i class="fab fa-whatsapp"></i></div>
                                Chat on WhatsApp
                            </a>
                            <?php endif; ?>
                            <a href="mailto:<?= htmlspecialchars($vendor['owner_email']) ?>" style="display:flex;align-items:center;gap:.65rem;padding:.7rem 1rem;border:1.5px solid #e9ecef;border-radius:11px;text-decoration:none;color:var(--secondary-color);font-size:.88rem;font-weight:600;transition:all .2s;" onmouseover="this.style.borderColor='#D4AF37'" onmouseout="this.style.borderColor='#e9ecef'">
                                <div style="width:34px;height:34px;background:rgba(212,175,55,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--primary-color);flex-shrink:0;"><i class="fas fa-envelope"></i></div>
                                <?= htmlspecialchars($vendor['owner_email']) ?>
                            </a>
                        </div>

                        <p style="text-align:center;font-size:.75rem;color:var(--text-light);margin-top:1rem;">
                            <i class="fas fa-shield-alt"></i> Secure & trusted via EventPlaza
                        </p>
                    </div>
                </div>

                <!-- Inquiry Form -->
                <div id="tab-inquiry" class="vp-inquiry-tab">
                    <div class="vp-book-hdr">
                        <i class="fas fa-envelope"></i>
                        <h3>Send Inquiry</h3>
                    </div>
                    <div class="vp-book-body">
                        <?php if($inquiry_msg === 'sent'): ?>
                        <div class="vp-alert success"><i class="fas fa-check-circle"></i> Inquiry sent successfully! The vendor will respond to you soon.</div>
                        <?php else: ?>
                        <form action="vendor_profile.php?id=<?= $vendor_id ?>#contact" method="POST">
                            <input type="hidden" name="send_inquiry" value="1">
                            <div class="vp-form-group">
                                <label>Your Name</label>
                                <input type="text" name="inq_name" placeholder="Your full name"
                                       value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['username']) : '' ?>" required>
                            </div>
                            <div class="vp-form-group">
                                <label>Your Email</label>
                                <input type="email" name="inq_email" placeholder="your@email.com" required>
                            </div>
                            <div class="vp-form-group">
                                <label>Message</label>
                                <textarea name="inq_message" rows="4" placeholder="Ask about availability, pricing, packages..." required></textarea>
                            </div>
                            <button type="submit" class="vp-book-btn">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /vp-book-card -->
        </div><!-- /right col -->

    </div><!-- /vp-layout -->
</div><!-- /container -->
</div><!-- /vp-body -->

<!-- Lightbox -->
<div class="vp-lightbox" id="vp-lightbox" onclick="closeLightbox()">
    <span class="vp-lb-close" onclick="closeLightbox()">✕</span>
    <img id="vp-lb-img" src="" alt="Portfolio">
</div>

<div id="vp-share-toast">🔗 Link copied!</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
// Tab switcher
function switchTab(name, btn) {
    document.querySelectorAll('.vp-inquiry-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.vp-book-tab-btn').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}

// Lightbox
function openLightbox(src) {
    document.getElementById('vp-lb-img').src = src;
    document.getElementById('vp-lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('vp-lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeLightbox(); });

// Share
function sharePage() {
    if (navigator.share) {
        navigator.share({ title: '<?= addslashes(htmlspecialchars($vendor['business_name'])) ?>', url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href).then(function(){
            const t = document.getElementById('vp-share-toast');
            t.classList.add('show');
            setTimeout(function(){ t.classList.remove('show'); }, 2500);
        });
    }
}

// GSAP
gsap.from('.vp-meta .vp-logo',       { y: 30, opacity: 0, duration: 0.7, ease: 'power3.out' });
gsap.from('.vp-name-block',           { y: 20, opacity: 0, duration: 0.6, delay: 0.15, ease: 'power3.out' });
gsap.from('.vp-stat',                 { y: 20, opacity: 0, duration: 0.4, stagger: 0.08, delay: 0.2, ease: 'power2.out' });
gsap.from('.vp-card',                 { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, delay: 0.3, ease: 'power2.out' });
gsap.from('.vp-book-card',            { x: 30, opacity: 0, duration: 0.6, delay: 0.4, ease: 'power2.out' });
</script>
