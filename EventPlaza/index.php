<?php 
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<header class="hero">
    <div class="hero-content">
        <h1 class="">Create Unforgettable Memories</h1>
        <p class="fade-up">Connect with world-class photographers, elite caterers, and visionary decorators to craft the perfect event.</p>
        <a href="#categories" class="btn btn-primary fade-up">Find Vendors</a>
    </div>
</header>

<!-- Categories Section -->
<section id="categories" class="section">
    <div class="container">
        <h2 class="section-title fade-up">Explore Excellence</h2>
        <div class="card-grid">
            <?php
            // Mock data for display (Replace with DB fetch later if needed)
            $categories = [
                ['name' => 'Photography', 'desc' => 'Capture every emotion with cinematic precision.', 'img' => 'https://images.unsplash.com/photo-1537633552985-df8429e8048b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'id' => 1],
                ['name' => 'Catering', 'desc' => 'Exquisite culinary experiences for your guests.', 'img' => 'https://images.unsplash.com/photo-1555244162-803834f70033?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'id' => 2],
                ['name' => 'Decoration', 'desc' => 'Transform venues into breathtaking landscapes.', 'img' => 'https://omastylebride.com/wp-content/uploads/2021/04/NwandosSignature-Events-Decor-on-OmaStyle-Bride-700x585.jpeg', 'id' => 3],
            ];

            foreach($categories as $cat) {
                 echo '
                <div class="card fade-up">
                    <div style="overflow:hidden;">
                        <img src="' . $cat['img'] . '" alt="' . $cat['name'] . '" class="card-img">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">' . $cat['name'] . '</h3>
                        <p class="card-text">' . $cat['desc'] . '</p>
                        <a href="search.php?category=' . $cat['id'] . '" class="btn btn-outline" style="width:100%; border-radius:8px;">View Vendors</a>
                    </div>
                </div>
                ';
            }
            ?>
        </div>
    </div>
</section>

<!-- Featured Vendors Section (New) -->
<section class="section" style="background-color: #f9f9f9;">
    <div class="container">
        <h2 class="section-title fade-up">Featured Professionals</h2>
        <div class="card-grid">
            <!-- Mock Featured Vendors -->
             <div class="card fade-up">
                <div style="position: relative;">
                    <img src="https://www.shutterstock.com/image-photo/couple-flower-confetti-outdoor-wedding-600nw-2366543069.jpg" class="card-img" alt="Vendor">
                    <span style="position: absolute; top: 10px; right: 10px; background: #D4AF37; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Top Rated</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Luxe Weddings</h3>
                    <p class="card-text">Premium wedding planning and photography services.</p>
                     <div style="color: #D4AF37; margin-bottom: 0.5rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <a href="#" class="btn btn-outline" style="width:100%; border-radius:8px;">View Profile</a>
                </div>
            </div>
             <div class="card fade-up">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="card-img" alt="Vendor">
                    <span style="position: absolute; top: 10px; right: 10px; background: #D4AF37; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Trending</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Floral Dreams</h3>
                    <p class="card-text">Bespoke floral arrangements for any occasion.</p>
                     <div style="color: #D4AF37; margin-bottom: 0.5rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                    <a href="#" class="btn btn-outline" style="width:100%; border-radius:8px;">View Profile</a>
                </div>
            </div>
             <div class="card fade-up">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="card-img" alt="Vendor">
                    <span style="position: absolute; top: 10px; right: 10px; background: #D4AF37; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Verified</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Gourmet Bites</h3>
                    <p class="card-text">Award-winning catering with a modern twist.</p>
                     <div style="color: #D4AF37; margin-bottom: 0.5rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <a href="#" class="btn btn-outline" style="width:100%; border-radius:8px;">View Profile</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section" style="background: var(--surface-color);">
    <div class="container">
        <h2 class="section-title fade-up">Why Choose EventPlaza?</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 2rem; justify-content: center;">
            <div class="feature-box fade-up" style="flex:1; min-width: 280px; text-align: left; padding: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
                <div style="background: rgba(212, 175, 55, 0.1); padding: 1rem; border-radius: 50%; color: var(--primary-color); font-size: 1.5rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 style="margin-bottom: 0.5rem;">Verified Vendors</h3>
                    <p style="color: var(--text-light);">Every professional on our platform passes a strict background check.</p>
                </div>
            </div>
            <div class="feature-box fade-up" style="flex:1; min-width: 280px; text-align: left; padding: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
                <div style="background: rgba(212, 175, 55, 0.1); padding: 1rem; border-radius: 50%; color: var(--primary-color); font-size: 1.5rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 style="margin-bottom: 0.5rem;">Secure Booking</h3>
                    <p style="color: var(--text-light);">Your payments and details are protected with bank-grade security.</p>
                </div>
            </div>
            <div class="feature-box fade-up" style="flex:1; min-width: 280px; text-align: left; padding: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
                <div style="background: rgba(212, 175, 55, 0.1); padding: 1rem; border-radius: 50%; color: var(--primary-color); font-size: 1.5rem;">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h3 style="margin-bottom: 0.5rem;">24/7 Support</h3>
                    <p style="color: var(--text-light);">Our dedicated team is always here to help you plan the perfect event.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title fade-up">Happy Stories</h2>
        <div class="card-grid">
            <div class="card fade-up" style="padding: 2rem; text-align: center;">
                 <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Client" style="width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;">
                 <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"EventPlaza made finding a photographer so easy! We loved the portfolio browsing feature."</p>
                 <h4 style="color: var(--secondary-color);">Sarah & James</h4>
                 <div style="color: #FFD700; font-size: 0.9rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            </div>
            <div class="card fade-up" style="padding: 2rem; text-align: center;">
                 <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Client" style="width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;">
                 <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"The catering service we booked through this site was phenomenal. Highly recommended!"</p>
                 <h4 style="color: var(--secondary-color);">Michael T.</h4>
                 <div style="color: #FFD700; font-size: 0.9rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            </div>
            <div class="card fade-up" style="padding: 2rem; text-align: center;">
                 <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Client" style="width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;">
                 <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"A lifesaver for our corporate event. We found everything from AV to decor in one place."</p>
                 <h4 style="color: var(--secondary-color);">Emily R.</h4>
                 <div style="color: #FFD700; font-size: 0.9rem;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section" style="background-color: var(--white);">
    <div class="container">
        <h2 class="section-title fade-up">Seamless Experience</h2>
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:2rem; text-align:center;">
            <div class="feature-box fade-up" style="flex:1; min-width:280px;">
                <div class="feature-icon"><i class="fas fa-search"></i></div>
                <h3>Discover</h3>
                <p>Browse a curated list of top-tier verified vendors.</p>
            </div>
            <div class="feature-box fade-up" style="flex:1; min-width:280px;">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Book</h3>
                <p>Secure your date with transparent pricing and instant booking.</p>
            </div>
            <div class="feature-box fade-up" style="flex:1; min-width:280px;">
                <div class="feature-icon"><i class="fas fa-champagne-glasses"></i></div>
                <h3>Celebrate</h3>
                <p>Relax and enjoy your event while we handle the details.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action / Contact Block (New) -->
<section style="background: url('https://www.uauu.cat/wp-content/uploads/2024/07/decoracion-boda-photocall-1024x683.jpg') center/cover fixed; padding: 6rem 0; position: relative;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(31, 31, 31, 0.6);"></div>
    <div class="container" style="position: relative; z-index: 2; text-align: center; color: #fff;">
        <h2 class="fade-up" style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Plan Your Dream Event?</h2>
        <p class="fade-up" style="font-size: 1.2rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">Join thousands of happy customers who found their perfect vendors with us.</p>
        <div class="fade-up" style="display: flex; gap: 1rem; justify-content: center;">
            <a href="register.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">Get Started</a>
            <a href="contact.php" class="btn btn-outline" style="border-color: #fff; color: #fff; padding: 1rem 2.5rem; font-size: 1.1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
