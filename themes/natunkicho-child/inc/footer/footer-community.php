<div class="nk-footer-community">
    <div class="nk-container nk-community-flex">
        
        <div class="nk-community-social">
            <h4>Follow NatunKicho</h4>
            <div class="nk-social-links">
                <a href="https://www.facebook.com/hospitality.global.hub" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><span class="dashicons dashicons-facebook-alt"></span></a>
                <a href="https://www.linkedin.com/company/food-business-success-lab/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><span class="dashicons dashicons-linkedin"></span></a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><span class="dashicons dashicons-youtube"></span></a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><span class="dashicons dashicons-instagram"></span></a>
            </div>
        </div>

        <div class="nk-community-newsletter">
            <stong>Join the Hospitality Network </br> Stay updated with hospitality jobs, career tips, and learning resources.</strong>
           
            <form id="nk-newsletter-form" class="nk-newsletter-form">
                <input type="text" id="nk-newsletter-name" name="name" placeholder="Enter your name" required />
                
                <input type="email" id="nk-newsletter-email" name="email" placeholder="Enter your email address" required />
                <?php wp_nonce_field('nk_newsletter_nonce', 'nk_security'); ?>
                <button type="submit" id="nk-newsletter-btn">Subscribe</button>
            </form>
            <div id="nk-newsletter-message" style="display:none; font-size:13px; margin-top:10px; font-weight:600;"></div>
        </div>

        <div class="nk-community-apps">
            <h4>Get the App</h4>
            <div class="nk-app-buttons">
                <div class="nk-app-btn placeholder-play">Google Play</div>
                <div class="nk-app-btn placeholder-apple">App Store</div>
            </div>
        </div>

    </div>
</div>