document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Footer Accordion Logic
    const footerTitles = document.querySelectorAll('.nk-footer-col-title');
    
    footerTitles.forEach(function(title) {
        title.addEventListener('click', function() {
            // Only trigger accordion behavior on mobile (screen width < 768px)
            if (window.innerWidth <= 768) {
                const wrapper = this.nextElementSibling;
                const icon = this.querySelector('.nk-mobile-toggle');
                
                // Toggle Display
                if (wrapper.style.display === 'block') {
                    wrapper.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    wrapper.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        });
    });

    // Handle Window Resize to reset mobile states if screen grows
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.querySelectorAll('.nk-footer-menu-wrapper').forEach(function(wrapper) {
                wrapper.style.display = 'block';
            });
            document.querySelectorAll('.nk-mobile-toggle').forEach(function(icon) {
                icon.style.transform = 'rotate(0deg)';
            });
        } else {
            document.querySelectorAll('.nk-footer-menu-wrapper').forEach(function(wrapper) {
                wrapper.style.display = 'none';
            });
        }
    });

});

// ==========================================
    // Newsletter AJAX Submission Logic
    // ==========================================
    const newsletterForm = document.getElementById('nk-newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop page from reloading
            
           const btn = document.getElementById('nk-newsletter-btn');
            const msgBox = document.getElementById('nk-newsletter-message');
            
            // 1. Grab both Name and Email
            const name = document.getElementById('nk-newsletter-name').value;
            const email = document.getElementById('nk-newsletter-email').value;
            const security = document.getElementById('nk_security').value;
            
            // Set loading state
            btn.innerText = 'Sending...';
            btn.disabled = true;
            msgBox.style.display = 'none';

            // Prepare Data for WordPress AJAX
            const formData = new URLSearchParams();
            formData.append('action', 'nk_footer_subscribe');
            formData.append('name', name);   // 2. Append Name
            formData.append('email', email); // Append Email
            formData.append('security', security);

            // Send Silent Request to Server
            fetch(nkFooterAjax.ajax_url, {
                method: 'POST',
                body: formData,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(response => response.json())
            .then(res => {
                // Reset Button
                btn.innerText = 'Subscribe';
                btn.disabled = false;
                msgBox.style.display = 'block';
                
                if (res.success) {
                    msgBox.style.color = '#10b981'; // Success Green
                    msgBox.innerText = res.data.message;
                    newsletterForm.reset(); // Clear input
                } else {
                    msgBox.style.color = '#ef4444'; // Error Red
                    msgBox.innerText = res.data.message;
                }
            })
            .catch(error => {
                btn.innerText = 'Subscribe';
                btn.disabled = false;
                msgBox.style.display = 'block';
                msgBox.style.color = '#ef4444';
                msgBox.innerText = 'System error. Please try again.';
            });
        });
    }