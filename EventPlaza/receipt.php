<?php
/**
 * receipt.php — Printable booking receipt
 * Opens in new tab, can be printed or saved as PDF via browser Ctrl+P
 */
include 'includes/db.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['booking_id'])) {
    redirect('user/dashboard.php');
}

$booking_id  = (int)$_GET['booking_id'];
$customer_id = (int)$_SESSION['user_id'];

// Only allow the owner or admin to view receipt
$stmt = $pdo->prepare("
    SELECT b.*,
           v.business_name, v.location, v.price_range,
           c.name AS category_name,
           u.username AS customer_name, u.email AS customer_email
    FROM   bookings b
    JOIN   vendors   v ON b.vendor_id  = v.id
    JOIN   categories c ON v.category_id = c.id
    JOIN   users     u ON b.customer_id = u.id
    WHERE  b.id = ?
    AND   (b.customer_id = ? OR ? = 1)
");
$stmt->execute([$booking_id, $customer_id, (int)isAdmin()]);
$b = $stmt->fetch();

if (!$b) {
    die("<p style='font-family:sans-serif;padding:2rem;color:red;'>Receipt not found or access denied.</p>");
}

$receipt_no = 'EP-' . date('Y') . '-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
$issued_on  = date('d F Y, h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= $receipt_no ?> — EventPlaza</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            color: #333;
            padding: 2rem;
        }

        /* Print controls (hidden when printing) */
        .print-controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .print-controls button, .print-controls a {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1.6rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
        }
        .btn-print   { background: #D4AF37; color: #fff; }
        .btn-print:hover { background: #B5952F; }
        .btn-back    { background: #e9ecef; color: #495057; }
        .btn-back:hover { background: #dee2e6; }

        /* Receipt Paper */
        .receipt {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        /* Header */
        .rcpt-header {
            background: #1A1A1A;
            padding: 2.5rem 2.5rem 2rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        .rcpt-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.9rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .rcpt-logo span { color: #D4AF37; }
        .rcpt-logo p { font-size: .78rem; font-weight: 400; color: rgba(255,255,255,0.45); margin-top: .2rem; letter-spacing: 0; }
        .rcpt-title-col { text-align: right; }
        .rcpt-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #D4AF37;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .rcpt-no { font-size: .85rem; color: rgba(255,255,255,0.55); margin-top: .2rem; }
        .rcpt-date { font-size: .8rem; color: rgba(255,255,255,0.4); margin-top: .1rem; }

        /* Gold divider */
        .rcpt-divider {
            height: 4px;
            background: linear-gradient(90deg, #D4AF37, #f0d060, #D4AF37);
        }

        /* Body */
        .rcpt-body { padding: 2.5rem; }

        /* Status Badge */
        .rcpt-status {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem 1.1rem;
            border-radius: 100px;
            font-size: .85rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        .status-pending   { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .status-confirmed { background: #d4edda; color: #155724; border: 1px solid #28a745; }
        .status-completed { background: #cce5ff; color: #004085; border: 1px solid #0d6efd; }
        .status-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }

        /* Two-column info grid */
        .rcpt-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .rcpt-info-box {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 1.25rem 1.4rem;
            border-left: 3px solid #D4AF37;
        }
        .rcpt-info-box h4 {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #D4AF37;
            margin-bottom: .75rem;
        }
        .rcpt-info-box p { font-size: .88rem; color: #555; margin-bottom: .3rem; line-height: 1.5; }
        .rcpt-info-box strong { color: #1A1A1A; }

        /* Details table */
        .rcpt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .rcpt-table th {
            text-align: left;
            padding: .65rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #888;
            background: #f9f9f9;
            border-bottom: 2px solid #eee;
        }
        .rcpt-table td {
            padding: .75rem 1rem;
            font-size: .88rem;
            color: #444;
            border-bottom: 1px dashed #f0f0f0;
        }
        .rcpt-table tr:last-child td { border-bottom: none; }

        /* Event details box */
        .rcpt-event-box {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 1.1rem 1.3rem;
            margin-bottom: 2rem;
            border-left: 3px solid #D4AF37;
        }
        .rcpt-event-box h4 { font-size: .75rem; text-transform: uppercase; letter-spacing: .07em; color: #D4AF37; font-weight:700; margin-bottom:.5rem; }
        .rcpt-event-box p { font-size: .88rem; color: #555; line-height: 1.6; }

        /* Footer */
        .rcpt-footer {
            border-top: 1px dashed #e0e0e0;
            padding-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .rcpt-footer-note { font-size: .78rem; color: #aaa; }
        .rcpt-footer-note strong { color: #888; }
        .rcpt-stamp {
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: .4rem .9rem;
            color: #28a745;
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            transform: rotate(-2deg);
            display: inline-block;
        }
        .rcpt-footer-bottom {
            background: #1A1A1A;
            padding: 1.25rem 2.5rem;
            text-align: center;
            font-size: .78rem;
            color: rgba(255,255,255,0.3);
        }

        /* PRINT STYLES */
        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none !important; }
            .receipt { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>

<!-- Print Controls (hidden on print) -->
<div class="print-controls">
    <button onclick="window.print()" class="btn-print">
        🖨️ Print / Save as PDF
    </button>
    <a href="booking_confirm.php?booking_id=<?= $booking_id ?>" class="btn-back">
        ← Back to Confirmation
    </a>
    <a href="user/dashboard.php" class="btn-back">
        📋 My Bookings
    </a>
</div>

<!-- RECEIPT -->
<div class="receipt">

    <!-- Header -->
    <div class="rcpt-header">
        <div class="rcpt-logo">
            Event<span>Plaza</span>
            <p>Your Event Vendor Marketplace</p>
        </div>
        <div class="rcpt-title-col">
            <div class="rcpt-title">Booking Receipt</div>
            <div class="rcpt-no"><?= $receipt_no ?></div>
            <div class="rcpt-date">Issued: <?= $issued_on ?></div>
        </div>
    </div>

    <div class="rcpt-divider"></div>

    <!-- Body -->
    <div class="rcpt-body">

        <!-- Status -->
        <?php
        $status_class = 'status-' . $b['status'];
        $status_icons = ['pending'=>'⏳','confirmed'=>'✅','completed'=>'🎉','cancelled'=>'❌'];
        $status_icon  = $status_icons[$b['status']] ?? '📋';
        ?>
        <div class="rcpt-status <?= $status_class ?>">
            <?= $status_icon ?> Booking <?= ucfirst($b['status']) ?>
        </div>

        <!-- Info Grid: Customer + Vendor -->
        <div class="rcpt-info-grid">
            <div class="rcpt-info-box">
                <h4>👤 Customer Details</h4>
                <p><strong><?= htmlspecialchars($b['customer_name']) ?></strong></p>
                <p><?= htmlspecialchars($b['customer_email']) ?></p>
            </div>
            <div class="rcpt-info-box">
                <h4>🏪 Vendor Details</h4>
                <p><strong><?= htmlspecialchars($b['business_name']) ?></strong></p>
                <p><?= htmlspecialchars($b['category_name']) ?></p>
                <?php if($b['location']): ?>
                <p>📍 <?= htmlspecialchars($b['location']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Table -->
        <table class="rcpt-table">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Booking ID</strong></td>
                    <td><?= $receipt_no ?></td>
                </tr>
                <tr>
                    <td><strong>Service</strong></td>
                    <td><?= htmlspecialchars($b['category_name']) ?> — <?= htmlspecialchars($b['business_name']) ?></td>
                </tr>
                <tr>
                    <td><strong>Event Date</strong></td>
                    <td><?= date('l, F j, Y', strtotime($b['event_date'])) ?></td>
                </tr>
                <tr>
                    <td><strong>Price Range</strong></td>
                    <td><?= htmlspecialchars($b['price_range'] ?: 'To be discussed') ?></td>
                </tr>
                <tr>
                    <td><strong>Total Amount</strong></td>
                    <td><?= $b['total_price'] ? 'Rs. ' . number_format($b['total_price'], 2) : 'To be confirmed by vendor' ?></td>
                </tr>
                <tr>
                    <td><strong>Booked On</strong></td>
                    <td><?= date('F j, Y h:i A', strtotime($b['created_at'])) ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td><span class="rcpt-status <?= $status_class ?>" style="font-size:.78rem;padding:.25rem .7rem;"><?= $status_icon ?> <?= ucfirst($b['status']) ?></span></td>
                </tr>
            </tbody>
        </table>

        <!-- Event Details -->
        <?php if(!empty($b['event_details'])): ?>
        <div class="rcpt-event-box">
            <h4>📝 Event Details</h4>
            <p><?= nl2br(htmlspecialchars($b['event_details'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="rcpt-footer">
            <div class="rcpt-footer-note">
                <strong>EventPlaza</strong> &bull; info@eventplaza.com &bull; +94 234 567 890<br>
                This is a system-generated receipt. No signature required.
            </div>
            <?php if($b['status'] !== 'cancelled'): ?>
            <div class="rcpt-stamp">Received</div>
            <?php else: ?>
            <div class="rcpt-stamp" style="border-color:#dc3545;color:#dc3545;">Cancelled</div>
            <?php endif; ?>
        </div>

    </div>

    <div class="rcpt-footer-bottom">
        &copy; <?= date('Y') ?> EventPlaza — All rights reserved. Booking Reference: <?= $receipt_no ?>
    </div>
</div>

</body>
</html>
