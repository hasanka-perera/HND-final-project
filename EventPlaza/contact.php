<?php 
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php'; 

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $msg = sanitize($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($msg)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $msg]);
            $message = '<div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border-left: 4px solid #28a745;">Message sent successfully! We will get back to you soon.</div>';
        } catch (PDOException $e) {
            $message = '<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border-left: 4px solid #dc3545;">Error sending message. Please try again.</div>';
        }
    } else {
        $message = '<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border-left: 4px solid #dc3545;">All fields are required.</div>';
    }
}
?>

<!-- Hero Section -->
<header class="hero" style="min-height: 50vh; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://www.wedinspire.com/wp-content/uploads/2019/08/Wedding-Decoration-Ideas-Themes.jpg') center/cover;">
    <div class="hero-content">
        <h1 class="">Get in Touch</h1>
        <p class="fade-up">Have questions? We are here to help you plan your dream event.</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div style="display: flex; gap: 4rem; flex-wrap: wrap;">
            
            <!-- Contact Info -->
            <div class="fade-up" style="flex: 1; min-width: 300px;">
                <h2 class="section-title" style="text-align: left; margin-bottom: 1.5rem;">Contact Information</h2>
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 50px; height: 50px; background: rgba(212, 175, 55, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.2rem;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Address</h4>
                            <p style="color: var(--text-light);">123 Event St, Party City, PC 12345</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 50px; height: 50px; background: rgba(212, 175, 55, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.2rem;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Email</h4>
                            <p style="color: var(--text-light);">support@eventplaza.com</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                         <div style="width: 50px; height: 50px; background: rgba(212, 175, 55, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.2rem;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Phone</h4>
                            <p style="color: var(--text-light);">+1 (555) 123-4567</p>
                        </div>
                    </div>
                </div>
                
                <!-- Map Image -->
                <div class="fade-up" style="width: 100%; height: 300px; border-radius: 10px; overflow: hidden; box-shadow: var(--shadow-small); margin-top: 2rem;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3168.639290621062!2d-122.08624618469227!3d37.421999879825215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808fba02425dad8f%3A0x2c388015361138f1!2sGoogleplex!5e0!3m2!1sen!2slk!4v1650000000000!5m2!1sen!2slk" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

            </div>

            <!-- Contact Form -->
            <div class="fade-up" style="flex: 1; min-width: 300px;">
                <div style="background: var(--white); padding: 2.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
                    <h3 style="margin-bottom: 1.5rem; color: var(--secondary-color);">Send us a Message</h3>
                    <?php echo $message; ?>
                    <form action="contact.php" method="POST">
                        <div class="form-group">
                            <label for="name" style="font-weight: 500;">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required placeholder="Your Full Name">
                        </div>
                        <div class="form-group">
                            <label for="email" style="font-weight: 500;">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="Your Email Address">
                        </div>
                        <div class="form-group">
                            <label for="message" style="font-weight: 500;">Message</label>
                            <textarea id="message" name="message" rows="5" class="form-control" required placeholder="How can we help you?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>
            
        </div>
        
        <!-- FAQ Section -->
        <div style="margin-top: 6rem;">
            <h2 class="section-title fade-up">Frequently Asked Questions</h2>
            <div class="card-grid">
                <div class="card fade-up" style="padding: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">How do I book a vendor?</h4>
                    <p style="color: var(--text-light);">Simply browse through our categories, select a vendor you like, and click "View Profile" to contact them directly or request a quote.</p>
                </div>
                <div class="card fade-up" style="padding: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Is it free to use?</h4>
                    <p style="color: var(--text-light);">Yes! Browsing and contacting vendors is completely free for users. Vendors may have subscription plans.</p>
                </div>
                <div class="card fade-up" style="padding: 2rem;">
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">How can I register as a vendor?</h4>
                    <p style="color: var(--text-light);">Click on the "Register" button, select "Vendor" as your account type, and fill in your details to get started.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
