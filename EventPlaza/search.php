<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

// Build Query — fetch ALL vendors, grouped by category
$query = "SELECT v.*, c.name as category_name, c.id as cat_id FROM vendors v 
          JOIN categories c ON v.category_id = c.id 
          WHERE 1=1";
$params = [];

$search_term = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_term = trim($_GET['q']);
    $query .= " AND (v.business_name LIKE ? OR v.description LIKE ? OR v.location LIKE ?)";
    $params[] = "%" . $search_term . "%";
    $params[] = "%" . $search_term . "%";
    $params[] = "%" . $search_term . "%";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND v.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $query .= " AND v.location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}

if (isset($_GET['price']) && !empty($_GET['price'])) {
    $query .= " AND v.price_range = ?";
    $params[] = $_GET['price'];
}

$query .= " ORDER BY c.name ASC, v.business_name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vendors = $stmt->fetchAll();

// Fetch Categories
$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Count vendors per category
$cat_counts = [];
$all_count_stmt = $pdo->query("SELECT category_id, COUNT(*) as cnt FROM vendors GROUP BY category_id");
foreach ($all_count_stmt->fetchAll() as $row) {
    $cat_counts[$row['category_id']] = $row['cnt'];
}

// Group results by category
$grouped_vendors = [];
if ($vendors) {
    foreach ($vendors as $v) {
        $grouped_vendors[$v['category_name']][] = $v;
    }
}

$total_vendors   = array_sum(array_map('count', $grouped_vendors));
$active_category = isset($_GET['category']) ? $_GET['category'] : '';
$total_all       = array_sum($cat_counts);
?>

<!-- Hero Section -->
<header class="hero" style="min-height: 50vh; background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?w=1600&q=80') center/cover no-repeat; display:flex; align-items:center; justify-content:center;">
    <div class="hero-content" style="text-align:center; padding: 3rem 1.5rem; max-width: 760px; width:100%;">
        <h1 style="color:#fff; font-size: clamp(1.9rem,5vw,3.2rem); font-weight:800; margin-bottom:0.75rem;">Find Your Perfect Vendor</h1>
        <p style="color:rgba(255,255,255,0.8); font-size:1.05rem; margin-bottom:2rem;">Browse <?= $total_all ?: 'our curated list of' ?> top-rated event professionals — all in one place.</p>
        <form action="search.php" method="GET" class="sp-hero-form">
            <div class="sp-hero-input-wrap">
                <i class="fas fa-search sp-hero-icon"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search by name, location or service..." class="sp-hero-input">
                <?php if($active_category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($active_category) ?>"><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary sp-hero-btn">Search</button>
        </form>
    </div>
</header>

<!-- Main Search Section -->
<section style="padding: 2.5rem 0 5rem; background: var(--background-color);">
    <div class="container">

        <!-- Stats Bar -->
        <div class="sp-stats-bar fade-up">
            <div class="sp-stat">
                <span class="sp-stat-num"><?= $total_all ?: 0 ?></span>
                <span class="sp-stat-lbl">Total Vendors</span>
            </div>
            <div class="sp-stat-div"></div>
            <div class="sp-stat">
                <span class="sp-stat-num"><?= count($cats) ?></span>
                <span class="sp-stat-lbl">Categories</span>
            </div>
            <div class="sp-stat-div"></div>
            <div class="sp-stat">
                <span class="sp-stat-num"><?= $total_vendors ?></span>
                <span class="sp-stat-lbl">Showing Now</span>
            </div>
            <div class="sp-stat-cta">
                <a href="register.php" class="btn btn-primary sp-register-btn">
                    <i class="fas fa-store"></i> Register Your Business
                </a>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="sp-tabs-wrapper fade-up">
            <div class="sp-tabs" id="spTabs">
                <a href="search.php<?= !empty($search_term) ? '?q='.urlencode($search_term) : '' ?>" 
                   class="sp-tab <?= empty($active_category) ? 'sp-tab-active' : '' ?>">
                    <i class="fas fa-th-large"></i> All
                    <span class="sp-tab-count"><?= $total_all ?: 0 ?></span>
                </a>
                <?php foreach($cats as $c): ?>
                    <?php
                    $count = $cat_counts[$c['id']] ?? 0;
                    $cat_icons = ['Photographers'=>'fa-camera','Caterers'=>'fa-utensils','Decorators'=>'fa-paint-brush','Venues'=>'fa-building','Musicians'=>'fa-music'];
                    $icon = $cat_icons[$c['name']] ?? 'fa-star';
                    ?>
                    <a href="search.php?category=<?= $c['id'] ?><?= !empty($search_term) ? '&q='.urlencode($search_term) : '' ?>"
                       class="sp-tab <?= ($active_category == $c['id']) ? 'sp-tab-active' : '' ?>">
                        <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($c['name']) ?>
                        <span class="sp-tab-count"><?= $count ?></span>
                    </a>
                <?php endforeach; ?>
                <button class="sp-tab sp-filter-btn" id="spFilterBtn">
                    <i class="fas fa-sliders-h"></i> Filters
                </button>
            </div>
        </div>

        <!-- Collapsible Filter Panel -->
        <div class="sp-filter-panel" id="spFilterPanel" style="display:none;">
            <form action="search.php" method="GET" class="sp-filter-form">
                <?php if(!empty($search_term)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search_term) ?>"><?php endif; ?>
                <?php if(!empty($active_category)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($active_category) ?>"><?php endif; ?>
                <div class="sp-filter-row">
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700;display:block;margin-bottom:.5rem;">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary-color);"></i> Location
                        </label>
                        <input type="text" name="location" value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>" placeholder="e.g. Colombo" class="form-control">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);font-weight:700;display:block;margin-bottom:.5rem;">
                            <i class="fas fa-tag" style="color:var(--primary-color);"></i> Price Range
                        </label>
                        <select name="price" class="form-control">
                            <option value="">Any Price</option>
                            <option value="$"   <?= (isset($_GET['price']) && $_GET['price']=='$')   ? 'selected' : '' ?>>$ — Budget Friendly</option>
                            <option value="$$"  <?= (isset($_GET['price']) && $_GET['price']=='$$')  ? 'selected' : '' ?>>$$ — Standard</option>
                            <option value="$$$" <?= (isset($_GET['price']) && $_GET['price']=='$$$') ? 'selected' : '' ?>>$$$ — Premium</option>
                        </select>
                    </div>
                    <div class="sp-filter-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Apply</button>
                        <a href="search.php" class="btn btn-outline">Clear All</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Meta -->
        <?php if(!empty($search_term) || !empty($active_category) || isset($_GET['location']) || isset($_GET['price'])): ?>
        <div class="sp-results-meta fade-up">
            <p style="color:var(--text-light);">
                <?php if($total_vendors > 0): ?>
                    Found <strong style="color:var(--secondary-color);"><?= $total_vendors ?></strong> vendor<?= $total_vendors!=1?'s':'' ?>
                    <?= !empty($search_term) ? ' for "<strong style=\"color:var(--secondary-color);\">' . htmlspecialchars($search_term) . '</strong>"' : '' ?>
                <?php else: ?>
                    No vendors found<?= !empty($search_term) ? ' for "<strong>' . htmlspecialchars($search_term) . '</strong>"' : '' ?>
                <?php endif; ?>
            </p>
            <a href="search.php" class="sp-clear-link"><i class="fas fa-times-circle"></i> Clear Search</a>
        </div>
        <?php endif; ?>

        <!-- Results -->
        <div class="sp-results">
            <?php if(!empty($grouped_vendors)): ?>

                <?php foreach($grouped_vendors as $category_name => $category_vendors): ?>
                    <?php
                    $cat_icons2 = ['Photographers'=>'fa-camera','Caterers'=>'fa-utensils','Decorators'=>'fa-paint-brush','Venues'=>'fa-building','Musicians'=>'fa-music'];
                    $icon2 = $cat_icons2[$category_name] ?? 'fa-star';
                    ?>
                    <div class="sp-category-block fade-up">
                        <!-- Category Header -->
                        <div class="sp-cat-header">
                            <div class="sp-cat-title-row">
                                <div class="sp-cat-icon-box">
                                    <i class="fas <?= $icon2 ?>"></i>
                                </div>
                                <div>
                                    <h2 class="sp-cat-name"><?= htmlspecialchars($category_name) ?></h2>
                                    <span class="sp-cat-count-label"><?= count($category_vendors) ?> vendor<?= count($category_vendors)!=1?'s':'' ?> available</span>
                                </div>
                            </div>
                            <a href="search.php?category=<?php
                                foreach($cats as $c){ if($c['name']===$category_name){ echo $c['id']; break; } }
                            ?>" class="sp-view-all">View All <i class="fas fa-arrow-right"></i></a>
                        </div>

                        <!-- Vendor Cards -->
                        <div class="sp-vendor-grid">
                            <?php foreach($category_vendors as $v): ?>
                            <div class="sp-vcard">
                                <div class="sp-vcard-img">
                                    <?php if(!empty($v['profile_image'])): ?>
                                        <img src="<?= htmlspecialchars($v['profile_image']) ?>" alt="<?= htmlspecialchars($v['business_name']) ?>">
                                    <?php else: ?>
                                        <div class="sp-vcard-placeholder"><?= strtoupper(substr($v['business_name'],0,1)) ?></div>
                                    <?php endif; ?>
                                    <span class="sp-vcard-cat-badge"><?= htmlspecialchars($v['category_name']) ?></span>
                                    <?php if($v['price_range']): ?>
                                        <span class="sp-vcard-price-badge"><?= htmlspecialchars($v['price_range']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="sp-vcard-body">
                                    <h3 class="sp-vcard-name"><?= htmlspecialchars($v['business_name']) ?></h3>
                                    <?php if(!empty($v['location'])): ?>
                                    <div class="sp-vcard-location">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($v['location']) ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(!empty($v['description']) && $v['description'] !== 'Please update your profile.'): ?>
                                    <p class="sp-vcard-desc"><?= htmlspecialchars(substr($v['description'],0,90)) . (strlen($v['description'])>90?'...':'') ?></p>
                                    <?php endif; ?>
                                    <a href="vendor_profile.php?id=<?= $v['id'] ?>" class="btn btn-outline sp-vcard-btn">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>

                <!-- Empty State -->
                <div class="sp-empty fade-up">
                    <div class="sp-empty-icon"><i class="fas fa-store-slash"></i></div>
                    <h3>No Vendors Found</h3>
                    <p>
                        <?php if(!empty($search_term)||!empty($active_category)||isset($_GET['location'])||isset($_GET['price'])): ?>
                            We couldn't find any vendors matching your criteria. Try adjusting your search.
                        <?php else: ?>
                            No vendors have registered yet. Be the first to list your business!
                        <?php endif; ?>
                    </p>
                    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-top:1.5rem;">
                        <?php if(!empty($search_term)||!empty($active_category)||isset($_GET['location'])||isset($_GET['price'])): ?>
                            <a href="search.php" class="btn btn-primary"><i class="fas fa-redo"></i> Clear Filters</a>
                        <?php endif; ?>
                        <a href="register.php" class="btn btn-outline"><i class="fas fa-store"></i> Register as Vendor</a>
                    </div>
                </div>

            <?php endif; ?>
        </div>

        <!-- CTA Banner -->
        <?php if($total_vendors > 0): ?>
        <div class="sp-cta-banner fade-up">
            <div class="sp-cta-inner">
                <div>
                    <h3 class="sp-cta-title"><i class="fas fa-rocket"></i> Grow Your Business with EventPlaza</h3>
                    <p class="sp-cta-sub">Join event professionals and get discovered by clients looking for your services.</p>
                </div>
                <div style="display:flex; gap:1rem; flex-wrap:wrap; flex-shrink:0;">
                    <a href="register.php" class="btn btn-primary"><i class="fas fa-store"></i> Register as Vendor</a>
                    <a href="about.php" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<style>
/* ===== SEARCH PAGE COMPONENT STYLES ===== */

/* Hero search form */
.sp-hero-form {
    display: flex;
    gap: 0.6rem;
    max-width: 660px;
    margin: 0 auto;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 14px;
    padding: 0.5rem;
}
.sp-hero-input-wrap {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0 1rem;
}
.sp-hero-icon { color: rgba(255,255,255,0.6); font-size: 1rem; }
.sp-hero-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 1rem;
    font-family: var(--font-body);
}
.sp-hero-input::placeholder { color: rgba(255,255,255,0.5); }
.sp-hero-btn { border-radius: 10px !important; padding: 0.65rem 1.8rem !important; white-space: nowrap; }

/* Stats Bar */
.sp-stats-bar {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: var(--surface-color);
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1rem 1.75rem;
    margin-bottom: 1.75rem;
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}
.sp-stat { display: flex; flex-direction: column; align-items: center; gap: 0.15rem; }
.sp-stat-num { font-family: var(--font-heading); font-size: 1.6rem; font-weight: 700; color: var(--primary-color); line-height: 1; }
.sp-stat-lbl { font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.04em; }
.sp-stat-div { width: 1px; height: 36px; background: #e9ecef; }
.sp-stat-cta { margin-left: auto; }
.sp-register-btn { font-size: 0.87rem !important; padding: 0.55rem 1.2rem !important; border-radius: 8px !important; }
@media(max-width:600px) { .sp-stat-cta,.sp-stat-div:last-of-type { display:none; } .sp-stats-bar { justify-content:center; } }

/* Category Tabs */
.sp-tabs-wrapper { margin-bottom: 1.25rem; overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.sp-tabs-wrapper::-webkit-scrollbar { display:none; }
.sp-tabs { display: flex; gap: 0.5rem; padding-bottom: 0.4rem; white-space: nowrap; }
.sp-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    border-radius: 100px;
    font-size: 0.88rem;
    font-weight: 500;
    color: var(--text-light);
    background: var(--surface-color);
    border: 1.5px solid #e9ecef;
    text-decoration: none;
    transition: all 0.22s ease;
    cursor: pointer;
    font-family: var(--font-body);
}
.sp-tab:hover { background: rgba(212,175,55,0.08); border-color: var(--primary-color); color: var(--secondary-color); }
.sp-tab-active { background: var(--primary-color) !important; border-color: var(--primary-color) !important; color: #fff !important; box-shadow: 0 4px 14px rgba(212,175,55,0.35); }
.sp-tab-count { background: rgba(0,0,0,0.1); border-radius: 100px; padding: 0.08rem 0.5rem; font-size: 0.73rem; font-weight: 600; }
.sp-tab-active .sp-tab-count { background: rgba(255,255,255,0.25); }
.sp-filter-btn { margin-left: auto; border-color: rgba(212,175,55,0.4); color: var(--primary-color); }
.sp-filter-btn.is-open { background: var(--primary-color); border-color: var(--primary-color); color: #fff; }

/* Filter Panel */
.sp-filter-panel {
    background: var(--surface-color);
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1.4rem 1.75rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
    animation: spSlide 0.28s ease;
}
@keyframes spSlide { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
.sp-filter-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end; }
.sp-filter-actions { display: flex; gap: 0.75rem; }
@media(max-width:680px){ .sp-filter-row { grid-template-columns:1fr; } }

/* Results Meta */
.sp-results-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.sp-clear-link { color: var(--primary-color); text-decoration: none; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.3rem; }
.sp-clear-link:hover { opacity: 0.75; }

/* Category Block */
.sp-category-block { margin-bottom: 3.5rem; }
.sp-cat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; gap: 1rem; }
.sp-cat-title-row { display: flex; align-items: center; gap: 0.9rem; }
.sp-cat-icon-box {
    width: 50px; height: 50px;
    border-radius: 13px;
    background: var(--primary-color);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: #fff; flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(212,175,55,0.35);
}
.sp-cat-name { font-family: var(--font-heading); font-size: 1.35rem; font-weight: 700; color: var(--secondary-color); margin: 0; line-height: 1.2; }
.sp-cat-count-label { font-size: 0.8rem; color: var(--text-light); }
.sp-view-all { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--primary-color); text-decoration: none; font-size: 0.88rem; font-weight: 600; white-space: nowrap; transition: gap 0.2s; }
.sp-view-all:hover { gap: 0.7rem; }

/* Vendor Grid */
.sp-vendor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(255px,1fr)); gap: 1.5rem; }

/* Vendor Card */
.sp-vcard { background: var(--surface-color); border-radius: 18px; overflow: hidden; border: 1px solid #ebebeb; box-shadow: 0 4px 16px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
.sp-vcard:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(0,0,0,0.12); }
.sp-vcard-img { position: relative; height: 185px; overflow: hidden; background: #f0f0f0; }
.sp-vcard-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.sp-vcard:hover .sp-vcard-img img { transform: scale(1.06); }
.sp-vcard-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; font-family: var(--font-heading); font-size: 4rem; font-weight: 800; color: var(--primary-color); opacity: 0.35; background: linear-gradient(135deg,#fdf6e3,#fdf0b8); }
.sp-vcard-cat-badge { position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.92); color: var(--secondary-color); font-size: 0.7rem; font-weight: 700; padding: 0.28rem 0.7rem; border-radius: 100px; letter-spacing: 0.02em; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.sp-vcard-price-badge { position: absolute; top: 12px; right: 12px; background: var(--primary-color); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 0.28rem 0.6rem; border-radius: 7px; }
.sp-vcard-body { padding: 1.2rem; display: flex; flex-direction: column; flex: 1; }
.sp-vcard-name { font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.4rem; line-height: 1.3; }
.sp-vcard-location { font-size: 0.82rem; color: var(--text-light); margin-bottom: 0.55rem; display: flex; align-items: center; gap: 0.35rem; }
.sp-vcard-location i { color: var(--primary-color); font-size: 0.78rem; }
.sp-vcard-desc { font-size: 0.83rem; color: var(--text-light); line-height: 1.5; margin-bottom: 1rem; flex: 1; }
.sp-vcard-btn { display: flex !important; align-items: center; justify-content: center; gap: 0.4rem; width: 100%; border-radius: 9px !important; padding: 0.5rem 1rem !important; font-size: 0.875rem !important; border-width: 1.5px !important; margin-top: auto; }

/* Empty State */
.sp-empty { text-align: center; padding: 4.5rem 2rem; border: 2px dashed #e0e0e0; border-radius: 18px; background: var(--surface-color); margin: 1rem 0; }
.sp-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: rgba(212,175,55,0.1); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: var(--primary-color); margin: 0 auto 1.25rem; }
.sp-empty h3 { font-family: var(--font-heading); font-size: 1.4rem; color: var(--secondary-color); margin-bottom: 0.6rem; }
.sp-empty p { color: var(--text-light); max-width: 400px; margin: 0 auto; line-height: 1.6; }

/* CTA Banner */
.sp-cta-banner { margin-top: 3rem; border-radius: 18px; background: linear-gradient(135deg, rgba(212,175,55,0.12) 0%, rgba(212,175,55,0.06) 100%); border: 1.5px solid rgba(212,175,55,0.35); padding: 2rem 2.5rem; }
.sp-cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap; }
.sp-cta-title { font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.4rem; }
.sp-cta-title i { color: var(--primary-color); margin-right: 0.4rem; }
.sp-cta-sub { color: var(--text-light); font-size: 0.92rem; }
</style>

<script>
// Filter panel toggle
const spFilterBtn   = document.getElementById('spFilterBtn');
const spFilterPanel = document.getElementById('spFilterPanel');
if (spFilterBtn && spFilterPanel) {
    <?php if(isset($_GET['location']) || isset($_GET['price'])): ?>
    spFilterPanel.style.display = 'block';
    spFilterBtn.classList.add('is-open');
    <?php endif; ?>
    spFilterBtn.addEventListener('click', function() {
        const isOpen = spFilterPanel.style.display !== 'none';
        spFilterPanel.style.display = isOpen ? 'none' : 'block';
        this.classList.toggle('is-open', !isOpen);
    });
}
// GSAP fade-ups
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gsap !== 'undefined') {
        gsap.from('.fade-up', { y: 28, opacity: 0, duration: 0.6, stagger: 0.08, ease: 'power2.out', clearProps: 'all' });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
