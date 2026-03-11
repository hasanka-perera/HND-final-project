// Register ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// Mobile Menu Toggle
const navToggle = document.getElementById('navToggle');
const navLinks = document.querySelector('.nav-links');

if (navToggle) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');

        // Animate links staggering in
        if (navLinks.classList.contains('active')) {
            gsap.fromTo('.nav-links li',
                { opacity: 0, y: -20 },
                { opacity: 1, y: 0, duration: 0.3, stagger: 0.1 }
            );
        }
    });
}

// Initialize Animations on Page Load
window.addEventListener('load', () => {

    // Refresh ScrollTrigger to ensure correct positions
    ScrollTrigger.refresh();

    // Hero Animations
    const heroTl = gsap.timeline();
    heroTl.from('.hero h1', { opacity: 0, y: 50, duration: 1, ease: 'power3.out' })
        .from('.hero p', { opacity: 0, y: 30, duration: 0.8, ease: 'power2.out' }, '-=0.5')
        .from('.hero .btn', { opacity: 0, y: 30, duration: 0.5, ease: 'back.out(1.7)' }, '-=0.3');

    // Scroll Animations (.fade-up)
    const fadeElements = gsap.utils.toArray('.fade-up');
    fadeElements.forEach(element => {
        gsap.fromTo(element,
            {
                opacity: 0,
                y: 50
            },
            {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 85%',
                    toggleActions: 'play none none reverse'
                }
            }
        );
    });

    // Staggered Cards Animation if present
    if (document.querySelector('.card-grid')) {
        gsap.from('.card', {
            scrollTrigger: {
                trigger: '.card-grid',
                start: 'top 85%' // Trigger earlier
            },
            opacity: 0,
            y: 50,
            duration: 0.8,
            stagger: 0.2, // Stagger effect
            ease: 'power2.out'
        });
    }

});
