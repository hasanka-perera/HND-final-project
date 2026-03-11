<?php
/**
 * booking_confirm.php — Beautiful enhanced booking confirmation page
 * Features: Email, Countdown, Add to Calendar, Checklist, Share, Related vendors
 */
include 'includes/db.php';
include 'includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    redirect('login.php');
}
if (!isset($_GET['booking_id'])) {
    redirect('search.php');
}

$booking_id  = (int)$_GET['booking_id'];
$customer_id = (int)$_SESSION['user_id'];

// Fetch booking
$stmt = $pdo->prepare("
    SELECT b.*,
           v.id AS vid, v.business_name, v.location, v.price_range, v.category_id, v.description AS vendor_desc,
           c.name AS category_name,
           u.username AS customer_name, u.email AS customer_email
    FROM   bookings b
    JOIN   vendors   v ON b.vendor_id  = v.id
    JOIN   categories c ON v.category_id = c.id
    JOIN   users     u ON b.customer_id = u.id
    WHERE  b.id = ? AND b.customer_id = ?
");
$stmt->execute([$booking_id, $customer_id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('user/dashboard.php');
}

// Related vendors (same category, different vendor)
$rel_stmt = $pdo->prepare("
    SELECT v.*, c.name AS cat_name
    FROM vendors v JOIN categories c ON v.category_id = c.id
    WHERE v.category_id = ? AND v.id != ? AND v.is_verified = 1
    ORDER BY RAND() LIMIT 3
");
$rel_stmt->execute([$booking['category_id'], $booking['vid']]);
$related = $rel_stmt->fetchAll();

// Send email (once per booking)
$email_sent  = false;
$session_key = 'email_sent_' . $booking_id;

if (!isset($_SESSION[$session_key])) {
    $to      = $booking['customer_email'];
    $subject = "🎉 Booking Confirmed — EventPlaza #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
    $receipt_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                 . '://' . $_SERVER['HTTP_HOST']
                 . '/darshana/receipt.php?booking_id=' . $booking_id;

    $body = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
    body{font-family:Arial,sans-serif;background:#f0f2f5;margin:0;padding:24px;}
    .wrap{max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.1);}
    .hdr{background:linear-gradient(135deg,#1A1A1A,#2d2d2d);padding:2.5rem;text-align:center;}
    .logo{font-size:2rem;font-weight:800;color:#fff;}.logo span{color:#D4AF37;}
    .success-icon{width:70px;height:70px;background:rgba(212,175,55,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:1rem auto 0;font-size:2rem;border:2px solid #D4AF37;}
    .body{padding:2rem;}
    h2{color:#1A1A1A;font-size:1.4rem;margin-bottom:.25rem;}
    .ref{display:inline-block;background:#f9f6ee;border:1px solid #D4AF37;border-radius:8px;padding:.4rem 1rem;font-size:.85rem;font-weight:700;color:#D4AF37;margin:1rem 0;}
    table{width:100%;border-collapse:collapse;margin:1rem 0;border-radius:10px;overflow:hidden;}
    td{padding:.7rem 1rem;font-size:.88rem;border-bottom:1px solid #f5f5f5;}
    tr:last-child td{border-bottom:none;}
    td:first-child{font-weight:700;color:#888;width:150px;background:#fafafa;}
    .cta{display:block;text-align:center;background:#D4AF37;color:#fff!important;text-decoration:none;padding:1rem 2rem;border-radius:12px;font-weight:800;font-size:1rem;margin:1.5rem 0;letter-spacing:.02em;}
    .steps{background:#f9f9f9;border-radius:12px;padding:1.25rem;margin:1.5rem 0;}
    .step{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.75rem;font-size:.88rem;}
    .step:last-child{margin-bottom:0;}
    .step-num{width:24px;height:24px;background:#D4AF37;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;flex-shrink:0;}
    .ftr{background:#f9f9f9;text-align:center;padding:1.25rem;font-size:.78rem;color:#aaa;border-top:1px solid #eee;}
    </style></head><body>
    <div class='wrap'>
        <div class='hdr'>
            <div class='logo'>Event<span>Plaza</span></div>
            <div class='success-icon'>🎉</div>
        </div>
        <div class='body'>
            <h2>Booking Received!</h2>
            <p style='color:#666;'>Dear <strong>" . htmlspecialchars($booking['customer_name']) . "</strong>, your request has been successfully submitted.</p>
            <div class='ref'>Booking Reference: #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . "</div>
            <table>
                <tr><td>Vendor</td><td><strong>" . htmlspecialchars($booking['business_name']) . "</strong></td></tr>
                <tr><td>Category</td><td>" . htmlspecialchars($booking['category_name']) . "</td></tr>
                <tr><td>Event Date</td><td><strong>" . date('l, F j, Y', strtotime($booking['event_date'])) . "</strong></td></tr>
                <tr><td>Location</td><td>" . htmlspecialchars($booking['location'] ?: 'TBD') . "</td></tr>
                <tr><td>Price Range</td><td>" . htmlspecialchars($booking['price_range'] ?: 'To be confirmed') . "</td></tr>
                <tr><td>Status</td><td><strong style='color:#D4AF37;'>⏳ Pending Confirmation</strong></td></tr>
            </table>
            <a href='" . $receipt_url . "' class='cta'>📄 View &amp; Download Receipt</a>
            <div class='steps'>
                <strong style='font-size:.85rem;color:#888;display:block;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em;'>What happens next?</strong>
                <div class='step'><div class='step-num'>1</div><div>The vendor will review your request and contact you to confirm details.</div></div>
                <div class='step'><div class='step-num'>2</div><div>Once confirmed, your booking status will update to <strong>Confirmed</strong>.</div></div>
                <div class='step'><div class='step-num'>3</div><div>On your event day, enjoy a seamless experience! 🎊</div></div>
            </div>
            <p style='font-size:.82rem;color:#aaa;text-align:center;'>Questions? Email us at <a href='mailto:info@eventplaza.com' style='color:#D4AF37;'>info@eventplaza.com</a></p>
        </div>
        <div class='ftr'>© " . date('Y') . " EventPlaza &bull; Colombo, Sri Lanka</div>
    </div></body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: EventPlaza <no-reply@eventplaza.com>\r\n";

    $email_sent = @mail($to, $subject, $body, $headers);
    $_SESSION[$session_key] = true;
}

// Build Google Calendar link
$gcal_start  = date('Ymd', strtotime($booking['event_date']));
$gcal_end    = date('Ymd', strtotime($booking['event_date'] . ' +1 day'));
$gcal_title  = urlencode('Event with ' . $booking['business_name']);
$gcal_detail = urlencode('Booked via EventPlaza | Ref: #' . str_pad($booking_id,6,'0',STR_PAD_LEFT));
$gcal_url    = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcal_title}&dates={$gcal_start}/{$gcal_end}&details={$gcal_detail}";

// Days until event
$today       = new DateTime('today');
$event_date  = new DateTime($booking['event_date']);
$days_left   = (int)$today->diff($event_date)->days;
$is_past     = $today > $event_date;

// Category icons
$cat_icons = ['Photographers'=>'fa-camera','Caterers'=>'fa-utensils','Decorators'=>'fa-paint-brush','Venues'=>'fa-building','Musicians'=>'fa-music'];
$cat_icon = $cat_icons[$booking['category_name']] ?? 'fa-star';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — EventPlaza</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
    /* ===== CONFIRMATION PAGE ===== */
    .confirm-page {
        min-height: 100vh;
        background: linear-gradient(160deg, #f8f9fa 0%, #fff8e7 100%);
        padding: 3rem 0 5rem;
    }
    .confirm-wrap { max-width: 800px; margin: 0 auto; padding: 0 1rem; }

    /* ===== HERO BANNER ===== */
    .confirm-hero {
        background: linear-gradient(135deg, #1A1A1A 0%, #2d2d2d 100%);
        border-radius: 24px;
        padding: 3rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }
    .confirm-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 120%, rgba(212,175,55,0.25) 0%, transparent 70%);
    }
    .confirm-hero::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #D4AF37, #f0d060, #D4AF37, transparent);
    }
    .confirm-checkmark {
        width: 90px; height: 90px;
        border-radius: 50%;
        background: rgba(212,175,55,0.15);
        border: 3px solid #D4AF37;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        position: relative; z-index: 1;
        animation: checkPop 0.6s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes checkPop { 0%{transform:scale(0);opacity:0} 100%{transform:scale(1);opacity:1} }
    .confirm-hero h1 {
        font-family: var(--font-heading);
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: .5rem;
        position: relative; z-index: 1;
    }
    .confirm-hero p {
        color: rgba(255,255,255,0.65);
        font-size: 1rem;
        position: relative; z-index: 1;
    }
    .confirm-ref {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: rgba(212,175,55,0.15);
        border: 1px solid rgba(212,175,55,0.4);
        color: #D4AF37;
        border-radius: 100px;
        padding: .45rem 1.2rem;
        font-size: .85rem;
        font-weight: 700;
        margin-top: 1rem;
        position: relative; z-index: 1;
    }

    /* ===== CONFETTI PARTICLES ===== */
    .confetti-dot {
        position: absolute;
        width: 8px; height: 8px;
        border-radius: 2px;
        animation: confettiFall linear infinite;
        z-index: 0;
    }
    @keyframes confettiFall {
        0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(130px) rotate(360deg); opacity: 0; }
    }

    /* ===== GRID LAYOUT ===== */
    .confirm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
    @media(max-width:640px) { .confirm-grid { grid-template-columns: 1fr; } }

    /* ===== CARDS ===== */
    .conf-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        overflow: hidden;
    }
    .conf-card-hdr {
        padding: .9rem 1.3rem;
        background: #fafafa;
        border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; gap: .5rem;
        font-family: var(--font-heading);
        font-size: .82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-light);
    }
    .conf-card-hdr i { color: var(--primary-color); font-size: .95rem; }
    .conf-card-body { padding: 1.3rem; }

    /* ===== BOOKING DETAILS TABLE ===== */
    .conf-table { width: 100%; border-collapse: collapse; }
    .conf-table td { padding: .6rem 0; font-size: .88rem; border-bottom: 1px dashed #f0f0f0; vertical-align: top; }
    .conf-table tr:last-child td { border-bottom: none; }
    .conf-table td:first-child { color: var(--text-light); font-weight: 600; width: 110px; font-size: .8rem; }
    .conf-table td:last-child { color: var(--secondary-color); font-weight: 500; }

    /* ===== COUNTDOWN ===== */
    .countdown-grid { display: flex; gap: .75rem; justify-content: center; }
    .cd-unit {
        text-align: center;
        flex: 1;
    }
    .cd-num {
        background: linear-gradient(135deg, #1A1A1A, #333);
        color: #D4AF37;
        font-family: var(--font-heading);
        font-size: 2rem;
        font-weight: 800;
        border-radius: 12px;
        padding: .75rem .5rem;
        display: block;
        line-height: 1;
        min-width: 60px;
    }
    .cd-lbl { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-light); margin-top: .35rem; display: block; }

    /* ===== EMAIL NOTICE ===== */
    .email-notice {
        border-radius: 12px;
        padding: 1rem 1.2rem;
        display: flex; align-items: flex-start; gap: .75rem;
        font-size: .88rem;
    }
    .email-notice.sent   { background: #edf7ee; border: 1px solid #b2dfb7; color: #2e7d32; }
    .email-notice.unsent { background: #fffde7; border: 1px solid #ffe082; color: #795548; }
    .email-notice i { font-size: 1.4rem; flex-shrink: 0; margin-top: 1px; }

    /* ===== STATUS STEPS ===== */
    .steps-track { display: flex; gap: 0; align-items: flex-start; margin: .5rem 0; }
    .step-item { flex: 1; text-align: center; position: relative; }
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15px; left: 50%; right: -50%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }
    .step-item.done:not(:last-child)::after { background: #D4AF37; }
    .step-circle {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto .5rem;
        font-size: .8rem;
        font-weight: 700;
        color: #aaa;
        position: relative; z-index: 1;
        transition: all .4s;
    }
    .step-item.done .step-circle { background: #D4AF37; color: #fff; box-shadow: 0 3px 10px rgba(212,175,55,0.4); }
    .step-item.active .step-circle { background: #fff; border: 2px solid #D4AF37; color: #D4AF37; animation: stepPulse 1.5s ease infinite; }
    @keyframes stepPulse { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,0.4)} 50%{box-shadow:0 0 0 6px rgba(212,175,55,0)} }
    .step-lbl { font-size: .72rem; font-weight: 600; color: var(--text-light); }
    .step-item.done .step-lbl { color: var(--secondary-color); }
    .step-item.active .step-lbl { color: var(--primary-color); font-weight: 700; }

    /* ===== CHECKLIST ===== */
    .checklist { list-style: none; padding: 0; margin: 0; }
    .checklist li {
        display: flex; align-items: center; gap: .75rem;
        font-size: .88rem;
        padding: .55rem 0;
        border-bottom: 1px dashed #f5f5f5;
        color: #555;
        cursor: pointer;
        transition: color .2s;
    }
    .checklist li:last-child { border-bottom: none; }
    .checklist li .chk-box {
        width: 20px; height: 20px;
        border: 2px solid #ddd;
        border-radius: 5px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        transition: all .2s;
        font-size: .7rem;
        color: transparent;
    }
    .checklist li.checked { color: #aaa; text-decoration: line-through; }
    .checklist li.checked .chk-box { background: #D4AF37; border-color: #D4AF37; color: #fff; }

    /* ===== CALENDAR BUTTONS ===== */
    .cal-btns { display: flex; flex-direction: column; gap: .6rem; }
    .cal-btn {
        display: flex; align-items: center; gap: .75rem;
        padding: .75rem 1rem;
        border-radius: 12px;
        font-size: .88rem;
        font-weight: 600;
        text-decoration: none;
        transition: all .22s;
        border: 1.5px solid #e9ecef;
        color: var(--secondary-color);
        background: #fff;
    }
    .cal-btn:hover { border-color: var(--primary-color); box-shadow: 0 4px 14px rgba(212,175,55,0.18); transform: translateY(-1px); }
    .cal-btn .cal-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }

    /* ===== ACTION BUTTONS ===== */
    .action-btns { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .action-btns a, .action-btns button {
        flex: 1;
        min-width: 140px;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        padding: .85rem 1rem;
        border-radius: 14px;
        font-size: .9rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-family: var(--font-body);
        transition: all .22s;
    }
    .ab-primary   { background: var(--primary-color); color: #fff; box-shadow: 0 6px 20px rgba(212,175,55,0.35); }
    .ab-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 28px rgba(212,175,55,.45); }
    .ab-secondary { background: #1A1A1A; color: #fff; }
    .ab-secondary:hover { background: #333; transform: translateY(-2px); }
    .ab-outline   { background: #fff; color: var(--secondary-color); border: 1.5px solid #e9ecef; }
    .ab-outline:hover { border-color: var(--primary-color); color: var(--primary-color); }
    .ab-share     { background: #e8f4fd; color: #0d6efd; }
    .ab-share:hover { background: #0d6efd; color: #fff; }

    /* ===== RELATED VENDORS ===== */
    .related-card {
        background: #fff;
        border-radius: 14px;
        padding: 1rem;
        border: 1.5px solid #f0f0f0;
        display: flex; align-items: center; gap: 1rem;
        text-decoration: none;
        color: var(--text-color);
        transition: all .2s;
    }
    .related-card:hover { border-color: var(--primary-color); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,175,55,0.15); }
    .related-avatar {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        color: var(--primary-color);
    }
    .related-info .rn { font-weight: 700; font-size: .9rem; color: var(--secondary-color); }
    .related-info .rc { font-size: .78rem; color: var(--text-light); margin-top: .15rem; }

    /* ===== SHARE TOAST ===== */
    #share-toast {
        position: fixed;
        bottom: 100px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #1A1A1A;
        color: #fff;
        padding: .65rem 1.4rem;
        border-radius: 100px;
        font-size: .875rem;
        font-weight: 600;
        opacity: 0;
        transition: all .3s;
        pointer-events: none;
        z-index: 9999;
        white-space: nowrap;
    }
    #share-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="confirm-page">
<div class="confirm-wrap">

    <!-- ===== HERO ===== -->
    <div class="confirm-hero" id="heroCard">
        <!-- Confetti dots (JS generated) -->
        <div id="confetti-container"></div>

        <div class="confirm-checkmark">🎉</div>
        <h1>Booking Confirmed!</h1>
        <p>Your request has been submitted. We'll keep you updated every step of the way.</p>
        <div class="confirm-ref">
            <i class="fas fa-tag"></i>
            Ref: #<?= str_pad($booking_id, 6, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <!-- ===== EMAIL NOTICE ===== -->
    <div class="email-notice <?= $email_sent ? 'sent' : 'unsent' ?>" style="margin-bottom:1.25rem;">
        <i class="fas fa-<?= $email_sent ? 'envelope-circle-check' : 'envelope' ?>"></i>
        <div>
            <?php if ($email_sent): ?>
                <strong>Confirmation email sent!</strong><br>
                <span style="font-size:.82rem;">Check your inbox at <strong><?= htmlspecialchars($booking['customer_email']) ?></strong></span>
            <?php else: ?>
                <strong>Email notification pending.</strong><br>
                <span style="font-size:.82rem;">Download the receipt below to save your booking details.</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== MAIN GRID ===== -->
    <div class="confirm-grid">

        <!-- Booking Details -->
        <div class="conf-card">
            <div class="conf-card-hdr"><i class="fas fa-receipt"></i> Booking Details</div>
            <div class="conf-card-body">
                <table class="conf-table">
                    <tr><td>Vendor</td><td><strong><?= htmlspecialchars($booking['business_name']) ?></strong></td></tr>
                    <tr><td>Category</td><td><i class="fas <?= $cat_icon ?>" style="color:var(--primary-color);margin-right:.3rem;"></i><?= htmlspecialchars($booking['category_name']) ?></td></tr>
                    <tr><td>Date</td><td><strong><?= date('d M Y', strtotime($booking['event_date'])) ?></strong><br><small style="color:var(--text-light);"><?= date('l', strtotime($booking['event_date'])) ?></small></td></tr>
                    <tr><td>Location</td><td><?= htmlspecialchars($booking['location'] ?: 'TBD') ?></td></tr>
                    <tr><td>Price</td><td><?= htmlspecialchars($booking['price_range'] ?: 'To confirm') ?></td></tr>
                    <tr><td>Status</td><td><span style="background:#fff3cd;color:#856404;border-radius:100px;padding:.2rem .7rem;font-size:.78rem;font-weight:700;">⏳ Pending</span></td></tr>
                </table>
                <?php if($booking['event_details']): ?>
                <div style="margin-top:.75rem;padding:.75rem;background:#f9f9f9;border-radius:10px;font-size:.82rem;color:#555;border-left:3px solid var(--primary-color);">
                    <strong style="display:block;margin-bottom:.25rem;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-light);">Event Details</strong>
                    <?= nl2br(htmlspecialchars($booking['event_details'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Countdown -->
        <div class="conf-card">
            <div class="conf-card-hdr"><i class="fas fa-clock"></i> Event Countdown</div>
            <div class="conf-card-body">
                <?php if (!$is_past): ?>
                <div style="text-align:center;margin-bottom:1rem;">
                    <span style="font-size:.82rem;color:var(--text-light);font-weight:600;"><?= $days_left ?> days until your event</span>
                </div>
                <div class="countdown-grid" id="countdown">
                    <div class="cd-unit"><span class="cd-num" id="cd-days">--</span><span class="cd-lbl">Days</span></div>
                    <div class="cd-unit"><span class="cd-num" id="cd-hrs">--</span><span class="cd-lbl">Hours</span></div>
                    <div class="cd-unit"><span class="cd-num" id="cd-min">--</span><span class="cd-lbl">Mins</span></div>
                    <div class="cd-unit"><span class="cd-num" id="cd-sec">--</span><span class="cd-lbl">Secs</span></div>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:1rem 0;">
                    <div style="font-size:2.5rem;">🎊</div>
                    <div style="font-weight:700;color:var(--secondary-color);margin-top:.5rem;">Your event day has passed!</div>
                    <div style="font-size:.85rem;color:var(--text-light);margin-top:.25rem;">We hope it was amazing!</div>
                </div>
                <?php endif; ?>

                <!-- Progress Steps -->
                <div style="margin-top:1.5rem;">
                    <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-light);margin-bottom:.85rem;">Booking Progress</div>
                    <div class="steps-track">
                        <div class="step-item done">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-lbl">Submitted</div>
                        </div>
                        <div class="step-item active">
                            <div class="step-circle"><i class="fas fa-clock"></i></div>
                            <div class="step-lbl">Review</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">3</div>
                            <div class="step-lbl">Confirmed</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle">4</div>
                            <div class="step-lbl">Complete</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Checklist -->
        <div class="conf-card">
            <div class="conf-card-hdr"><i class="fas fa-list-check"></i> Event Checklist</div>
            <div class="conf-card-body">
                <p style="font-size:.8rem;color:var(--text-light);margin-bottom:.85rem;">Click items to mark them complete 👇</p>
                <ul class="checklist" id="checklist">
                    <li onclick="toggleCheck(this)" data-id="chk1"><div class="chk-box">✓</div> Confirm booking with vendor</li>
                    <li onclick="toggleCheck(this)" data-id="chk2"><div class="chk-box">✓</div> Share event details with guests</li>
                    <li onclick="toggleCheck(this)" data-id="chk3"><div class="chk-box">✓</div> Add event to your calendar</li>
                    <li onclick="toggleCheck(this)" data-id="chk4"><div class="chk-box">✓</div> Arrange transport & accommodation</li>
                    <li onclick="toggleCheck(this)" data-id="chk5"><div class="chk-box">✓</div> Download & save your receipt</li>
                    <li onclick="toggleCheck(this)" data-id="chk6"><div class="chk-box">✓</div> Review vendor 2 weeks before event</li>
                </ul>
                <div style="margin-top:.75rem;background:#f9f9f9;border-radius:8px;padding:.5rem .75rem;display:flex;align-items:center;justify-content:space-between;font-size:.8rem;">
                    <span style="color:var(--text-light);">Progress</span>
                    <div style="flex:1;margin:0 .75rem;background:#e9ecef;border-radius:4px;height:6px;overflow:hidden;">
                        <div id="chk-progress-bar" style="height:100%;background:var(--primary-color);border-radius:4px;width:0%;transition:width .4s;"></div>
                    </div>
                    <span id="chk-progress-txt" style="font-weight:700;color:var(--primary-color);">0/6</span>
                </div>
            </div>
        </div>

        <!-- Add to Calendar -->
        <div class="conf-card">
            <div class="conf-card-hdr"><i class="fas fa-calendar-plus"></i> Add to Calendar</div>
            <div class="conf-card-body">
                <div class="cal-btns">
                    <a href="<?= $gcal_url ?>" target="_blank" class="cal-btn">
                        <div class="cal-icon" style="background:#e8f0fe;">
                            <svg viewBox="0 0 24 24" width="20" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="#4285F4" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke="#4285F4" stroke-width="2" stroke-linecap="round"/><line x1="8" y1="2" x2="8" y2="6" stroke="#4285F4" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="10" x2="21" y2="10" stroke="#4285F4" stroke-width="2"/></svg>
                        </div>
                        <div><div style="font-size:.88rem;">Google Calendar</div><div style="font-size:.75rem;color:var(--text-light);font-weight:400;">Add to your Google account</div></div>
                        <i class="fas fa-external-link-alt" style="margin-left:auto;font-size:.75rem;color:var(--text-light);"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="downloadICS()" class="cal-btn">
                        <div class="cal-icon" style="background:#f0f0f0;">
                            <svg viewBox="0 0 24 24" width="20" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="#555" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke="#555" stroke-width="2" stroke-linecap="round"/><line x1="8" y1="2" x2="8" y2="6" stroke="#555" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="10" x2="21" y2="10" stroke="#555" stroke-width="2"/></svg>
                        </div>
                        <div><div style="font-size:.88rem;">Apple / Outlook / Any</div><div style="font-size:.75rem;color:var(--text-light);font-weight:400;">Download .ICS file</div></div>
                        <i class="fas fa-download" style="margin-left:auto;font-size:.75rem;color:var(--text-light);"></i>
                    </a>
                </div>

                <div style="margin-top:1.25rem;padding:.85rem;background:#f9f9f9;border-radius:12px;font-size:.82rem;color:#555;">
                    <div style="display:flex;align-items:center;gap:.4rem;font-weight:700;color:var(--secondary-color);margin-bottom:.3rem;"><i class="fas fa-map-marker-alt" style="color:var(--primary-color);"></i> Event Info</div>
                    <div><?= htmlspecialchars($booking['business_name']) ?></div>
                    <div style="color:var(--text-light);"><?= date('D, d M Y', strtotime($booking['event_date'])) ?> &bull; <?= htmlspecialchars($booking['location'] ?: 'TBD') ?></div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== ACTION BUTTONS ===== -->
    <div class="action-btns">
        <a href="receipt.php?booking_id=<?= $booking_id ?>" target="_blank" class="ab-primary">
            <i class="fas fa-download"></i> Download Receipt
        </a>
        <a href="user/dashboard.php" class="ab-secondary">
            <i class="fas fa-calendar-alt"></i> My Bookings
        </a>
        <a href="vendor_profile.php?id=<?= $booking['vid'] ?>" class="ab-outline">
            <i class="fas fa-store"></i> View Vendor
        </a>
        <button class="ab-share" onclick="shareBooking()">
            <i class="fas fa-share-alt"></i> Share
        </button>
    </div>

    <!-- ===== RELATED VENDORS ===== -->
    <?php if ($related): ?>
    <div class="conf-card" style="margin-bottom:1.5rem;">
        <div class="conf-card-hdr"><i class="fas fa-store"></i> More <?= htmlspecialchars($booking['category_name']) ?> Vendors You May Like</div>
        <div class="conf-card-body">
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <?php foreach($related as $r): ?>
                <a href="vendor_profile.php?id=<?= $r['id'] ?>" class="related-card">
                    <div class="related-avatar">
                        <i class="fas <?= $cat_icon ?>"></i>
                    </div>
                    <div class="related-info">
                        <div class="rn"><?= htmlspecialchars($r['business_name']) ?></div>
                        <div class="rc">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary-color);font-size:.7rem;"></i>
                            <?= htmlspecialchars($r['location'] ?: 'Sri Lanka') ?>
                            &nbsp;&bull;&nbsp;
                            <?= htmlspecialchars($r['price_range'] ?: '$$') ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:.8rem;"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Trust note -->
    <div style="text-align:center;font-size:.82rem;color:var(--text-light);padding-bottom:2rem;">
        <i class="fas fa-shield-alt" style="color:var(--primary-color);"></i>
        Secure booking via <strong>EventPlaza</strong> &mdash; The vendor will contact you shortly to confirm.
    </div>

</div>
</div>

<!-- Share toast -->
<div id="share-toast">🔗 Link copied to clipboard!</div>

<?php include 'includes/footer.php'; ?>

<script>
// ===== CONFETTI =====
(function(){
    const container = document.getElementById('confetti-container');
    const colors = ['#D4AF37','#f0d060','#fff','#c9a227','#FFD700'];
    for(let i=0;i<18;i++){
        const dot = document.createElement('div');
        dot.className = 'confetti-dot';
        dot.style.cssText = `
            left:${Math.random()*100}%;
            top:${Math.random()*120}%;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            width:${6+Math.random()*7}px;
            height:${6+Math.random()*7}px;
            border-radius:${Math.random()>.5?'50%':'2px'};
            animation-duration:${2+Math.random()*3}s;
            animation-delay:${Math.random()*2}s;
        `;
        container.appendChild(dot);
    }
})();

// ===== COUNTDOWN =====
<?php if(!$is_past): ?>
(function(){
    const target = new Date('<?= $booking['event_date'] ?>T23:59:59');
    function tick(){
        const now  = new Date();
        const diff = target - now;
        if(diff <= 0){
            ['days','hrs','min','sec'].forEach(function(u){ const el=document.getElementById('cd-'+u); if(el)el.textContent='00'; });
            return;
        }
        const days = Math.floor(diff/86400000);
        const hrs  = Math.floor((diff%86400000)/3600000);
        const min  = Math.floor((diff%3600000)/60000);
        const sec  = Math.floor((diff%60000)/1000);
        function pad(n){ return String(n).padStart(2,'0'); }
        document.getElementById('cd-days').textContent = pad(days);
        document.getElementById('cd-hrs').textContent  = pad(hrs);
        document.getElementById('cd-min').textContent  = pad(min);
        document.getElementById('cd-sec').textContent  = pad(sec);
    }
    tick(); setInterval(tick, 1000);
})();
<?php endif; ?>

// ===== CHECKLIST =====
const checkState = {};
function toggleCheck(el){
    const id = el.dataset.id;
    checkState[id] = !checkState[id];
    el.classList.toggle('checked', checkState[id]);
    const total = document.querySelectorAll('#checklist li').length;
    const done  = Object.values(checkState).filter(Boolean).length;
    document.getElementById('chk-progress-bar').style.width = (done/total*100) + '%';
    document.getElementById('chk-progress-txt').textContent = done + '/' + total;
    localStorage.setItem('ep_checklist_<?= $booking_id ?>', JSON.stringify(checkState));
}
// Restore from localStorage
(function(){
    const saved = localStorage.getItem('ep_checklist_<?= $booking_id ?>');
    if(!saved) return;
    Object.assign(checkState, JSON.parse(saved));
    document.querySelectorAll('#checklist li').forEach(function(li){
        if(checkState[li.dataset.id]){
            li.classList.add('checked');
        }
    });
    const total = document.querySelectorAll('#checklist li').length;
    const done  = Object.values(checkState).filter(Boolean).length;
    document.getElementById('chk-progress-bar').style.width = (done/total*100)+'%';
    document.getElementById('chk-progress-txt').textContent = done+'/'+total;
})();

// ===== SHARE =====
function shareBooking(){
    const url  = window.location.href;
    const text = 'I just booked <?= addslashes(htmlspecialchars($booking['business_name'])) ?> for my event via EventPlaza!';
    if(navigator.share){
        navigator.share({ title:'EventPlaza Booking', text:text, url:url });
    } else {
        navigator.clipboard.writeText(url).then(function(){
            const t = document.getElementById('share-toast');
            t.classList.add('show');
            setTimeout(function(){ t.classList.remove('show'); }, 2500);
        });
    }
}

// ===== DOWNLOAD ICS =====
function downloadICS(){
    const start = '<?= date('Ymd', strtotime($booking['event_date'])) ?>';
    const end   = '<?= date('Ymd', strtotime($booking['event_date'] . ' +1 day')) ?>';
    const summary = 'Event with <?= addslashes($booking['business_name']) ?>';
    const desc    = 'Booked via EventPlaza\\nRef: #<?= str_pad($booking_id,6,'0',STR_PAD_LEFT) ?>\\nCategory: <?= addslashes($booking['category_name']) ?>';
    const location= '<?= addslashes($booking['location'] ?: 'TBD') ?>';
    const ics = [
        'BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//EventPlaza//EN',
        'BEGIN:VEVENT',
        'DTSTART;VALUE=DATE:'+start,
        'DTEND;VALUE=DATE:'+end,
        'SUMMARY:'+summary,
        'DESCRIPTION:'+desc,
        'LOCATION:'+location,
        'STATUS:TENTATIVE',
        'END:VEVENT','END:VCALENDAR'
    ].join('\r\n');
    const blob = new Blob([ics], {type:'text/calendar'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'eventplaza-booking-<?= $booking_id ?>.ics';
    a.click();
}

// ===== GSAP ANIMATIONS =====
gsap.from('#heroCard',      { y: -30, opacity: 0, duration: 0.7, ease: 'power3.out' });
gsap.from('.email-notice',  { y: 20, opacity: 0, duration: 0.5, delay: 0.2, ease: 'power2.out' });
gsap.from('.conf-card',     { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, delay: 0.3, ease: 'power2.out' });
gsap.from('.action-btns a, .action-btns button', { y: 20, opacity: 0, duration: 0.4, stagger: 0.07, delay: 0.6, ease: 'power2.out' });
gsap.from('.related-card',  { x: -20, opacity: 0, duration: 0.4, stagger: 0.1, delay: 0.8, ease: 'power2.out' });
</script>
</body>
</html>
