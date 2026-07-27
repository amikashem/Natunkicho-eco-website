/**
 * Natunkicho Learning Ecosystem - Core Interactivity
 * Handles sticky navigation, smooth scrolling, carousels, and AJAX filtering.
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Ensure we are on the learning marketplace page
    const ecosystemWrapper = document.getElementById('nk-learning-ecosystem');
    if (!ecosystemWrapper) return;

    /* ==========================================================================
       1. Smooth Scrolling & ScrollSpy (Sticky Nav Active States)
       ========================================================================== */
    const navLinks = document.querySelectorAll('.nk-learning-menu a[href^="#"]');
    const sections = document.querySelectorAll('.nk-learning-section');

    // Smooth scroll to section
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                // Adjust for the height of the sticky nav (approx 60px)
                const offsetTop = targetSection.getBoundingClientRect().top + window.scrollY - 70;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Update active nav link based on scroll position using Intersection Observer
    const observerOptions = {
        root: null,
        rootMargin: '-80px 0px -60% 0px', // Adjust trigger area
        threshold: 0
    };

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                // Remove active class from all
                navLinks.forEach(link => link.classList.remove('active'));
                // Add active class to the current section's link
                const activeLink = document.querySelector(`.nk-learning-menu a[href="#${id}"]`);
                if (activeLink) activeLink.classList.add('active');
            }
        });
    }, observerOptions);

    sections.forEach(section => {
        if (section.getAttribute('id')) {
            sectionObserver.observe(section);
        }
    });

    /* ==========================================================================
       2. Draggable Carousel for Institutes (No external plugins needed)
       ========================================================================== */
    const carousel = document.querySelector('.nk-institute-carousel');
    if (carousel) {
        let isDown = false;
        let startX;
        let scrollLeft;

        // Apply basic CSS via JS for the draggable layout
        carousel.style.display = 'flex';
        carousel.style.overflowX = 'auto';
        carousel.style.gap = '20px';
        carousel.style.paddingBottom = '20px';
        carousel.style.cursor = 'grab';
        carousel.style.scrollbarWidth = 'none'; // Firefox
        
        // Hide scrollbar for Chrome/Safari
        const style = document.createElement('style');
        style.innerHTML = `.nk-institute-carousel::-webkit-scrollbar { display: none; }`;
        document.head.appendChild(style);

        carousel.addEventListener('mousedown', (e) => {
            isDown = true;
            carousel.style.cursor = 'grabbing';
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });
        carousel.addEventListener('mouseleave', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });
        carousel.addEventListener('mouseup', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });
        carousel.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2; // Scroll-fast multiplier
            carousel.scrollLeft = scrollLeft - walk;
        });
    }

    /* ==========================================================================
       3. AJAX Course Filtering (Preparation)
       ========================================================================== */
    const filterButtons = document.querySelectorAll('.nk-filter-btn');
    const courseGrid = document.querySelector('.nk-course-grid');

    if (filterButtons.length > 0 && courseGrid) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // 1. Update active button state
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                // 2. Visual feedback (fade out grid)
                courseGrid.style.opacity = '0.5';

                // 3. AJAX Call Structure (Hooked into the WP localized object)
                if (typeof nk_learning_ajax !== 'undefined') {
                    
                    /* * NOTE: This is the structure for the real database connection in Phase 4.
                     * We will uncomment this when we write the backend PHP logic for 'nk_filter_courses'.
                     */
                    
                    
                    fetch(nk_learning_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'nk_filter_courses',
                            security: nk_learning_ajax.nonce,
                            category: filterValue
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        courseGrid.innerHTML = data;
                        courseGrid.style.opacity = '1';
                    });
                

                 
                } else {
                    courseGrid.style.opacity = '1';
                }
            });
        });
    }
});