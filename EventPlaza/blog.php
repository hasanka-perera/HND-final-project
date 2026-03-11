<?php  
include 'includes/db.php'; 
include 'includes/header.php';  
?>  

<!-- Hero Section --> 
<header class="hero" style="min-height: 50vh; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1517457373958-b7bdd4587205?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
    <div class="hero-content">
        <h1 class="">Our Blog</h1>
        <p class="fade-up">Latest trends, tips, and inspiration for your next big event.</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">

            <!-- Blog Posts Area -->
            <div>
                <div class="card-grid" style="grid-template-columns: 1fr;"> <!-- 1 Column for main feed -->

                    <?php
                    // Mock data if DB is empty for visual purposes
                    $posts = [
                        ['title' => 'Top 10 Wedding Trends for 2024', 'excerpt' => 'From sustainable decor to intimate ceremonies, discover what is trending in the wedding world this year.', 'img' => 'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'date' => 'October 15, 2023', 'author' => 'Sarah J.'],
                        ['title' => 'How to Choose the Perfect Caterer', 'excerpt' => 'Food is the heart of any event. Learn the key questions to ask before booking your catering service.', 'img' => 'https://images.unsplash.com/photo-1555244162-803834f70033?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'date' => 'September 22, 2023', 'author' => 'Mike R.'],
                        ['title' => 'DIY Decor vs. Professional Decorators', 'excerpt' => 'Should you do it yourself or hire a pro? We break down the pros and cons of event decoration.', 'img' => 'https://d2e5ushqwiltxm.cloudfront.net/wp-content/uploads/sites/166/2024/05/29070002/IMG_5341-1.jpg', 'date' => 'August 10, 2023', 'author' => 'Emily W.']
                    ];

                    if (!empty($posts)) {
                        foreach ($posts as $post) {
                            echo '
                            <div class="card fade-up" style="display: flex; flex-direction: row; align-items: stretch; margin-bottom: 2rem;">
                                <div style="flex: 1; overflow: hidden; min-width: 250px;">
                                    <img src="' . $post['img'] . '" alt="' . $post['title'] . '" class="card-img" style="height: 100%; object-fit: cover;">
                                </div>
                                <div class="card-content" style="flex: 1.5;">
                                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.5rem;">' . $post['date'] . ' • by ' . $post['author'] . '</div>
                                    <h3 class="card-title" style="font-size: 1.5rem;">' . $post['title'] . '</h3>
                                    <p class="card-text">' . $post['excerpt'] . '</p>
                                    <a href="#" style="color:var(--primary-color); font-weight:600; display:inline-block; margin-top:0.5rem;">Read Article &rarr;</a>
                                </div>
                            </div>
                            ';
                        }
                    } else {
                        echo '<p>No posts available.</p>';
                    }
                    ?>

                </div>

                <!-- Pagination -->
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">&larr; Prev</a>
                    <a href="#" class="btn btn-primary" style="padding: 0.5rem 1rem; margin: 0 0.5rem;">1</a>
                    <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">2</a>
                    <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">3</a>
                    <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">Next &rarr;</a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="fade-up">
                <!-- Search Box -->
                <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Search Blog</h3>
                    <div class="form-group" style="margin-bottom: 0;">
                        <input type="text" placeholder="Search articles..." class="form-control">
                    </div>
                </div>

                <!-- Categories -->
                <div style="background: var(--white); padding: 2rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Categories</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <a href="#" style="color: var(--text-color);">Wedding Tips</a> <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">12</span>
                        </li>
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <a href="#" style="color: var(--text-color);">Event Planning</a> <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">8</span>
                        </li>
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <a href="#" style="color: var(--text-color);">Photography</a> <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">5</span>
                        </li>
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <a href="#" style="color: var(--text-color);">Catering Ideas</a> <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">7</span>
                        </li>
                        <li style="margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                            <a href="#" style="color: var(--text-color);">Budgeting</a> <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">4</span>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div style="background: var(--primary-color); padding: 2rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); color: var(--white); text-align: center;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.25rem; color: var(--white);">Subscribe</h3>
                    <p style="margin-bottom: 1rem; opacity: 0.9;">Get the latest updates and offers directly in your inbox.</p>
                    <input type="email" placeholder="Your email address" class="form-control" style="margin-bottom: 1rem; background: rgba(255,255,255,0.9);">
                    <button class="btn btn-secondary" style="width: 100%;">Subscribe Now</button>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
