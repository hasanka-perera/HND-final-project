<style>
/* ===== BEAUTIFUL FOOTER ===== */
.site-footer {
    background: #111111;
    color: rgba(255,255,255,0.75);
    font-family: var(--font-body);
    position: relative;
    overflow: hidden;
}

/* Decorative top border glow */
.site-footer::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--primary-color), #f0d060, var(--primary-color), transparent);
}

/* Subtle background pattern */
.site-footer::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 80% 20%, rgba(212,175,55,0.06) 0%, transparent 50%),
                      radial-gradient(circle at 10% 80%, rgba(212,175,55,0.04) 0%, transparent 40%);
    pointer-events: none;
}

.footer-main {
    position: relative;
    z-index: 1;
    padding: 4rem 0 2.5rem;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.8fr 1fr 1fr 1.5fr;
    gap: 3rem;
}

/* Brand Column */
.footer-brand .footer-logo {
    font-family: var(--font-heading);
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}
.footer-brand .footer-logo span { color: var(--primary-color); }

.footer-brand p {
    font-size: 0.92rem;
    line-height: 1.7;
    color: rgba(255,255,255,0.55);
    margin-bottom: 1.5rem;
    max-width: 270px;
}

/* Social Icons */
.footer-socials {
    display: flex;
    gap: 0.6rem;
}
.footer-social-link {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.25s ease;
    background: rgba(255,255,255,0.04);
}
.footer-social-link:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(212,175,55,0.35);
}

/* Nav Columns */
.footer-col h4 {
    font-family: var(--font-heading);
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary-color);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 1.2rem;
    position: relative;
    padding-bottom: 0.75rem;
}
.footer-col h4::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0;
    width: 28px; height: 2px;
    background: var(--primary-color);
    border-radius: 1px;
}
.footer-col ul { list-style: none; padding: 0; margin: 0; }
.footer-col ul li { margin-bottom: 0.65rem; }
.footer-col ul li a {
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.22s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.footer-col ul li a:hover {
    color: var(--primary-color);
    padding-left: 5px;
}
.footer-col ul li a i {
    font-size: 0.65rem;
    opacity: 0.5;
    transition: opacity 0.22s;
}
.footer-col ul li a:hover i { opacity: 1; }

/* Contact Items */
.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.footer-contact-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 1px;
}
.footer-contact-text {
    font-size: 0.88rem;
    line-height: 1.5;
    color: rgba(255,255,255,0.55);
}
.footer-contact-text strong {
    display: block;
    color: rgba(255,255,255,0.8);
    font-size: 0.82rem;
    margin-bottom: 0.1rem;
}

/* Newsletter Column */
.footer-newsletter-form {
    display: flex;
    gap: 0.4rem;
    margin-top: 0.5rem;
}
.footer-newsletter-form input {
    flex: 1;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 9px;
    padding: 0.65rem 1rem;
    color: #fff;
    font-size: 0.875rem;
    font-family: var(--font-body);
    outline: none;
    transition: border-color 0.2s;
}
.footer-newsletter-form input::placeholder { color: rgba(255,255,255,0.35); }
.footer-newsletter-form input:focus { border-color: var(--primary-color); }
.footer-newsletter-form button {
    background: var(--primary-color);
    border: none;
    border-radius: 9px;
    padding: 0.65rem 1rem;
    color: #fff;
    cursor: pointer;
    font-size: 0.95rem;
    transition: all 0.22s;
    flex-shrink: 0;
}
.footer-newsletter-form button:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(212,175,55,0.3);
}
.footer-newsletter-note {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.35);
    margin-top: 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* Divider */
.footer-divider {
    position: relative;
    z-index: 1;
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), rgba(212,175,55,0.25), rgba(255,255,255,0.1), transparent);
    margin: 0;
}

/* Footer Bottom */
.footer-bottom {
    position: relative;
    z-index: 1;
    padding: 1.5rem 0;
}
.footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.footer-copy {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.38);
}
.footer-copy strong { color: var(--primary-color); font-weight: 600; }
.footer-bottom-links {
    display: flex;
    gap: 1.5rem;
}
.footer-bottom-links a {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.38);
    text-decoration: none;
    transition: color 0.2s;
}
.footer-bottom-links a:hover { color: var(--primary-color); }

/* Responsive */
@media (max-width: 1024px) {
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
}
@media (max-width: 580px) {
    .footer-grid { grid-template-columns: 1fr; gap: 1.75rem; }
    .footer-main { padding: 2.5rem 0 1.5rem; }
    .footer-bottom-inner { flex-direction: column; text-align: center; }
    .footer-bottom-links { justify-content: center; }
}
</style>

<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand -->
                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">Event<span>Plaza</span></a>
                    <p>Your one-stop destination for discovering and booking the best event vendors. From photographers to caterers — we have it all.</p>
                    <div class="footer-socials">
                        <a href="#" class="footer-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="footer-social-link" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" class="footer-social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="footer-social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="search.php"><i class="fas fa-chevron-right"></i> Find Vendors</a></li>
                        <li><a href="about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="blog.php"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="contact.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Services / Categories -->
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <li><a href="search.php?category=1"><i class="fas fa-chevron-right"></i> Photographers</a></li>
                        <li><a href="search.php?category=2"><i class="fas fa-chevron-right"></i> Caterers</a></li>
                        <li><a href="search.php?category=3"><i class="fas fa-chevron-right"></i> Decorators</a></li>
                        <li><a href="search.php?category=4"><i class="fas fa-chevron-right"></i> Venues</a></li>
                        <li><a href="search.php?category=5"><i class="fas fa-chevron-right"></i> Musicians</a></li>
                    </ul>
                </div>

                <!-- Contact + Newsletter -->
                <div class="footer-col">
                    <h4>Get In Touch</h4>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon"><i class="fas fa-envelope"></i></div>
                        <div class="footer-contact-text">
                            <strong>Email Us</strong>
                            info@eventplaza.com
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon"><i class="fas fa-phone"></i></div>
                        <div class="footer-contact-text">
                            <strong>Call Us</strong>
                            +94 234 567 890
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="footer-contact-text">
                            <strong>Location</strong>
                            Colombo, Sri Lanka
                        </div>
                    </div>

                    <h4 style="margin-top:1.5rem;">Newsletter</h4>
                    <form class="footer-newsletter-form" onsubmit="return false;">
                        <input type="email" placeholder="Your email address">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <p class="footer-newsletter-note"><i class="fas fa-shield-alt"></i> No spam, unsubscribe anytime.</p>
                </div>

            </div>
        </div>
    </div>

    <hr class="footer-divider">

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <p class="footer-copy">
                    &copy; <?php echo date("Y"); ?> <strong>EventPlaza</strong>. All rights reserved. Crafted with <i class="fas fa-heart" style="color:var(--primary-color);"></i> for great events.
                </p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="register.php">Become a Vendor</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/script.js"></script>

<!-- =====================================================
     EVENTPLAZA CHATBOT WIDGET
     Appears on every page via footer.php
====================================================== -->
<style>
/* ===== CHATBOT WIDGET ===== */
#ep-chat-btn {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37, #f0d060);
    border: none;
    box-shadow: 0 8px 30px rgba(212,175,55,0.5);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), box-shadow 0.3s;
    animation: chatPulse 2.5s ease-in-out infinite;
}
#ep-chat-btn:hover { transform: scale(1.12); box-shadow: 0 12px 40px rgba(212,175,55,0.65); }
#ep-chat-btn svg, #ep-chat-btn i { color: #fff; font-size: 1.5rem; transition: all 0.25s; }
@keyframes chatPulse {
    0%,100% { box-shadow: 0 8px 30px rgba(212,175,55,0.5); }
    50%      { box-shadow: 0 8px 40px rgba(212,175,55,0.75), 0 0 0 10px rgba(212,175,55,0.12); }
}

/* Notification badge */
#ep-chat-badge {
    position: absolute;
    top: -2px; right: -2px;
    width: 18px; height: 18px;
    background: #dc3545;
    border-radius: 50%;
    border: 2px solid #fff;
    font-size: .65rem;
    font-weight: 700;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    animation: badgeBounce 0.6s 2s ease both;
}
@keyframes badgeBounce { 0%{transform:scale(0)}60%{transform:scale(1.3)}100%{transform:scale(1)} }

/* Chat Window */
#ep-chat-window {
    position: fixed;
    bottom: 100px;
    right: 28px;
    z-index: 9998;
    width: 360px;
    max-height: 560px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 80px rgba(0,0,0,0.18), 0 0 0 1px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.85) translateY(30px);
    opacity: 0;
    pointer-events: none;
    transition: all 0.32s cubic-bezier(.34,1.56,.64,1);
    transform-origin: bottom right;
}
#ep-chat-window.open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Chat Header */
.ep-chat-hdr {
    background: linear-gradient(135deg, #1A1A1A, #2d2d2d);
    padding: 1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: .85rem;
    flex-shrink: 0;
}
.ep-chat-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37, #f0d060);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    position: relative;
}
.ep-chat-avatar::after {
    content: '';
    position: absolute;
    bottom: 1px; right: 1px;
    width: 10px; height: 10px;
    background: #28a745;
    border-radius: 50%;
    border: 2px solid #2d2d2d;
}
.ep-chat-hdr-text { flex: 1; }
.ep-chat-name { font-family: 'Outfit', sans-serif; font-weight: 700; color: #fff; font-size: .95rem; }
.ep-chat-status { font-size: .72rem; color: #28a745; display: flex; align-items: center; gap: .3rem; }
.ep-chat-status::before { content:''; width:6px; height:6px; background:#28a745; border-radius:50%; display:inline-block; }
.ep-chat-close {
    background: rgba(255,255,255,0.1);
    border: none;
    color: rgba(255,255,255,0.7);
    width: 30px; height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    transition: all 0.2s;
}
.ep-chat-close:hover { background: rgba(255,255,255,0.2); color: #fff; }

/* Messages */
.ep-chat-msgs {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    min-height: 280px;
    max-height: 340px;
    display: flex;
    flex-direction: column;
    gap: .6rem;
    background: #f8f9fa;
    scroll-behavior: smooth;
}
.ep-chat-msgs::-webkit-scrollbar { width: 4px; }
.ep-chat-msgs::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }

/* Message bubbles */
.ep-msg {
    max-width: 82%;
    padding: .65rem .95rem;
    border-radius: 16px;
    font-size: .875rem;
    line-height: 1.5;
    animation: msgIn 0.25s ease;
    word-break: break-word;
}
@keyframes msgIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.ep-msg.bot {
    background: #fff;
    color: #333;
    border-bottom-left-radius: 4px;
    align-self: flex-start;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.ep-msg.user {
    background: linear-gradient(135deg, #D4AF37, #c9a227);
    color: #fff;
    border-bottom-right-radius: 4px;
    align-self: flex-end;
    box-shadow: 0 2px 8px rgba(212,175,55,0.3);
}
.ep-msg-time { font-size: .68rem; opacity: .55; margin-top: .25rem; display: block; }

/* Typing indicator */
.ep-typing {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .6rem .95rem;
    background: #fff;
    border-radius: 16px;
    border-bottom-left-radius: 4px;
    align-self: flex-start;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    width: fit-content;
}
.ep-typing span {
    width: 7px; height: 7px;
    background: #D4AF37;
    border-radius: 50%;
    animation: typeDot 1.2s ease-in-out infinite;
}
.ep-typing span:nth-child(2) { animation-delay: 0.2s; }
.ep-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typeDot { 0%,80%,100%{transform:scale(0.6);opacity:.5} 40%{transform:scale(1);opacity:1} }

/* Quick Replies */
.ep-quick-wrp {
    padding: .6rem 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    background: #f8f9fa;
    border-top: 1px solid #f0f0f0;
}
.ep-quick-btn {
    background: #fff;
    border: 1.5px solid rgba(212,175,55,0.4);
    color: #333;
    border-radius: 100px;
    padding: .3rem .8rem;
    font-size: .76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}
.ep-quick-btn:hover { background: var(--primary-color, #D4AF37); color: #fff; border-color: var(--primary-color, #D4AF37); }

/* Input */
.ep-chat-input-row {
    display: flex;
    gap: .5rem;
    padding: .75rem 1rem;
    background: #fff;
    border-top: 1px solid #f0f0f0;
    flex-shrink: 0;
}
#ep-chat-input {
    flex: 1;
    border: 1.5px solid #e9ecef;
    border-radius: 100px;
    padding: .55rem 1rem;
    font-size: .875rem;
    font-family: 'Inter', sans-serif;
    outline: none;
    transition: border-color 0.2s;
    color: #333;
    background: #f8f9fa;
}
#ep-chat-input:focus { border-color: var(--primary-color, #D4AF37); background: #fff; }
#ep-chat-send {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37, #c9a227);
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    transition: all 0.2s;
    flex-shrink: 0;
}
#ep-chat-send:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(212,175,55,0.4); }

/* Mobile */
@media(max-width: 420px) {
    #ep-chat-window { width: calc(100vw - 32px); right: 16px; bottom: 90px; }
    #ep-chat-btn { right: 16px; bottom: 16px; }
}
</style>

<!-- Chat Button -->
<button id="ep-chat-btn" onclick="epToggleChat()" aria-label="Open chat assistant">
    <span id="ep-chat-badge">1</span>
    <i class="fas fa-comments" id="ep-chat-icon"></i>
</button>

<!-- Chat Window -->
<div id="ep-chat-window" role="dialog" aria-label="EventPlaza Chat Assistant">
    <div class="ep-chat-hdr">
        <div class="ep-chat-avatar">🤖</div>
        <div class="ep-chat-hdr-text">
            <div class="ep-chat-name">EventPlaza Assistant</div>
            <div class="ep-chat-status">Online — typically replies instantly</div>
        </div>
        <button class="ep-chat-close" onclick="epToggleChat()" aria-label="Close chat">✕</button>
    </div>

    <div class="ep-chat-msgs" id="ep-chat-msgs"></div>

    <div class="ep-quick-wrp" id="ep-quick-replies">
        <button class="ep-quick-btn" onclick="epAsk('How do I book a vendor?')">📅 How to book?</button>
        <button class="ep-quick-btn" onclick="epAsk('What categories are available?')">🗂️ Categories</button>
        <button class="ep-quick-btn" onclick="epAsk('How do I register as a vendor?')">🏪 Be a Vendor</button>
        <button class="ep-quick-btn" onclick="epAsk('What is the pricing?')">💰 Pricing</button>
        <button class="ep-quick-btn" onclick="epAsk('How to contact support?')">📞 Support</button>
    </div>

    <div class="ep-chat-input-row">
        <input type="text" id="ep-chat-input" placeholder="Ask me anything..." autocomplete="off"
               onkeydown="if(event.key==='Enter') epSend()">
        <button id="ep-chat-send" onclick="epSend()" aria-label="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
(function() {
    'use strict';

    /* ===== KNOWLEDGE BASE ===== */
    const KB = [
        // Booking
        { k: ['book','booking','reserve','hire','appoint'],
          a: `To book a vendor, follow these easy steps:\n\n1. 🔍 Go to <a href="/darshana/search.php" style="color:#D4AF37;">Find Vendors</a>\n2. 🏪 Choose a vendor and open their profile\n3. 📅 Select your event date and fill in event details\n4. ✅ Click <strong>Request Booking</strong>\n\nYou'll receive a confirmation email instantly! 📧` },

        // Categories
        { k: ['categ','type','service','kind','what vendor'],
          a: `We have <strong>6 vendor categories</strong>:\n\n📸 <strong>Photographers</strong> — Capture every moment\n🍽️ <strong>Caterers</strong> — Delicious food & beverages\n🎨 <strong>Decorators</strong> — Beautiful event décor\n🏛️ <strong>Venues</strong> — Perfect event spaces\n🎵 <strong>Musicians</strong> — Live music & entertainment\n🎤 <strong>Others</strong> — Emcees, planners & more\n\n<a href="/darshana/search.php" style="color:#D4AF37;">Browse all vendors →</a>` },

        // Register as vendor
        { k: ['register','signup','sign up','become vendor','join','vendor account','create account'],
          a: `Becoming an EventPlaza vendor is FREE! 🎉\n\n1. Click <a href="/darshana/register.php" style="color:#D4AF37;">Register</a>\n2. Choose <strong>Vendor</strong> role\n3. Fill in your business name, category, and location\n4. Submit — you're in!\n\nOnce approved by admin, your profile goes live for customers to find you. 🚀` },

        // Pricing
        { k: ['price','pricing','cost','fee','charge','expensive','cheap','afford','budget'],
          a: `Vendors set their own pricing. You can filter by price range:\n\n💵 <strong>$</strong> — Budget Friendly\n💵💵 <strong>$$</strong> — Standard\n💵💵💵 <strong>$$$</strong> — Premium\n\nThe actual quote is provided by the vendor after your booking request. EventPlaza is <strong>completely free</strong> to use for customers! 😊` },

        // Contact / Support
        { k: ['contact','support','help','phone','email','reach','problem','issue'],
          a: `You can reach our support team:\n\n📧 <strong>Email:</strong> info@eventplaza.com\n📞 <strong>Phone:</strong> +94 234 567 890\n📍 <strong>Location:</strong> Colombo, Sri Lanka\n\nOr visit our <a href="/darshana/contact.php" style="color:#D4AF37;">Contact Page</a> to send us a message. We reply within 24 hours! ⏰` },

        // Login / account
        { k: ['login','log in','sign in','password','forgot','account'],
          a: `You can log in here: <a href="/darshana/login.php" style="color:#D4AF37;">Login Page</a>\n\nForgot your password? Contact us at <strong>info@eventplaza.com</strong> and we'll reset it for you. 🔑\n\nDon't have an account? <a href="/darshana/register.php" style="color:#D4AF37;">Register free →</a>` },

        // Receipt / download
        { k: ['receipt','download','invoice','proof','confirmation'],
          a: `After every booking you can:\n\n📧 Receive an <strong>email confirmation</strong> automatically\n📄 Download a <strong>printable PDF receipt</strong> from your <a href="/darshana/user/dashboard.php" style="color:#D4AF37;">Dashboard</a>\n\nSimply click the gold <strong>Receipt</strong> button next to any booking! 🎫` },

        // Dashboard
        { k: ['dashboard','my booking','history','past','upcoming'],
          a: `Your <a href="/darshana/user/dashboard.php" style="color:#D4AF37;">Customer Dashboard</a> shows:\n\n📅 All your bookings\n🏷️ Booking status (Pending / Confirmed / Completed)\n📄 Receipt download for each booking\n\nLogin to view your dashboard! 👤` },

        // About
        { k: ['about','who','what is','eventplaza','platform','company'],
          a: `<strong>EventPlaza</strong> is Sri Lanka's #1 event vendor marketplace! 🇱🇰\n\nWe connect customers with the best event professionals:\n✅ Verified vendors\n🔍 Easy search & filtering\n📅 Simple booking system\n⭐ Customer reviews\n\n<a href="/darshana/about.php" style="color:#D4AF37;">Learn more about us →</a>` },

        // Hello / greeting
        { k: ['hello','hi','hey','good morning','good afternoon','good evening','wassup','howdy','hola'],
          a: `Hey there! 👋 Welcome to <strong>EventPlaza</strong>!\n\nI'm your AI assistant. I can help you with:\n🔍 Finding vendors\n📅 Booking questions\n🧾 Receipts & confirmations\n💬 General info\n\nWhat would you like to know?` },

        // Thanks
        { k: ['thank','thanks','thx','ty','appreciate','great','awesome','perfect'],
          a: `You're very welcome! 😊 Happy to help!\n\nIs there anything else I can assist you with? Feel free to ask anytime! 🌟` },

        // Bye
        { k: ['bye','goodbye','see you','later','cya'],
          a: `Goodbye! 👋 Have a wonderful event!\n\nFeel free to come back anytime if you have more questions. EventPlaza is always here for you! 🎉` },

        // Reviews
        { k: ['review','rating','feedback','star','testimonial'],
          a: `You can leave a <strong>review</strong> for any vendor after visiting their profile! ⭐\n\n1. Open a vendor's profile\n2. Scroll to the <strong>Reviews</strong> section\n3. Select a star rating and write your experience\n\nYour feedback helps other customers make better decisions! 🙏` },

        // Vendor profile
        { k: ['vendor profile','profile','portfolio','gallery','photos'],
          a: `Each vendor has a detailed profile page with:\n\n📸 Portfolio gallery\n📝 Business description\n📍 Location & price range\n⭐ Customer reviews & ratings\n📅 Booking form\n\nVisit <a href="/darshana/search.php" style="color:#D4AF37;">Find Vendors</a> to explore! 🧭` },
    ];

    /* ===== STATE ===== */
    let chatOpen = false;
    let firstOpen = true;

    /* ===== TOGGLE ===== */
    window.epToggleChat = function() {
        chatOpen = !chatOpen;
        const win = document.getElementById('ep-chat-window');
        const icon = document.getElementById('ep-chat-icon');
        const badge = document.getElementById('ep-chat-badge');

        if (chatOpen) {
            win.classList.add('open');
            icon.className = 'fas fa-times';
            badge.style.display = 'none';
            if (firstOpen) {
                firstOpen = false;
                setTimeout(function() { epBotSay("👋 Hi! I'm the <strong>EventPlaza Assistant</strong>.\n\nI can help you find vendors, book services, understand pricing, and more!\n\nWhat can I help you with today? 😊"); }, 400);
            }
            setTimeout(function(){ document.getElementById('ep-chat-input').focus(); }, 350);
        } else {
            win.classList.remove('open');
            icon.className = 'fas fa-comments';
        }
    };

    /* ===== SEND ===== */
    window.epSend = function() {
        const input = document.getElementById('ep-chat-input');
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        epUserSay(text);
        setTimeout(function() { epRespond(text); }, 600);
    };

    window.epAsk = function(text) {
        epUserSay(text);
        setTimeout(function(){ epRespond(text); }, 600);
    };

    /* ===== ADD USER MESSAGE ===== */
    function epUserSay(text) {
        addMsg(text, 'user');
    }

    /* ===== BOT RESPONSE ===== */
    function epRespond(text) {
        // Show typing indicator
        const typing = addTyping();
        const delay = 900 + Math.random() * 600;

        setTimeout(function() {
            typing.remove();
            const response = findAnswer(text.toLowerCase());
            epBotSay(response);
        }, delay);
    }

    /* ===== FIND ANSWER ===== */
    function findAnswer(text) {
        for (let i = 0; i < KB.length; i++) {
            for (let j = 0; j < KB[i].k.length; j++) {
                if (text.includes(KB[i].k[j])) {
                    return KB[i].a;
                }
            }
        }
        // Fallback
        return `Hmm, I'm not sure about that one! 🤔\n\nTry asking about:\n• Booking a vendor\n• Available categories\n• Pricing\n• How to register\n\nOr contact us at <a href="mailto:info@eventplaza.com" style="color:#D4AF37;">info@eventplaza.com</a> for live support! 📧`;
    }

    /* ===== ADD BOT MESSAGE ===== */
    function epBotSay(html) {
        addMsg(html, 'bot');
    }

    /* ===== DOM HELPERS ===== */
    function addMsg(content, type) {
        const msgs = document.getElementById('ep-chat-msgs');
        const div = document.createElement('div');
        div.className = 'ep-msg ' + type;
        const now = new Date();
        const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
        // Replace newlines with <br>
        const html = (type === 'bot') ? content.replace(/\n/g,'<br>') : escHtml(content).replace(/\n/g,'<br>');
        div.innerHTML = html + '<span class="ep-msg-time">' + time + '</span>';
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    function addTyping() {
        const msgs = document.getElementById('ep-chat-msgs');
        const div = document.createElement('div');
        div.className = 'ep-typing';
        div.innerHTML = '<span></span><span></span><span></span>';
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Show badge after 3 seconds if not opened yet
    setTimeout(function() {
        if (!chatOpen) {
            document.getElementById('ep-chat-badge').style.display = 'flex';
        }
    }, 3000);

})();
</script>

</body>
</html>
