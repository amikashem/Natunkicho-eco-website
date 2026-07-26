<?php
if (!defined('ABSPATH')) exit;

function nk_ecosystem_teaser_shortcode() {
    ob_start();
    ?>
    <div class="nk-ecosystem-container">
        <div class="nk-ecosystem-grid">
            
            <div class="nk-ecosystem-card candidate-card">
                <div class="nk-eco-content">
                    <h3>For Hospitality Pros</h3>
                    <h2>Build an AI-Optimized Resume in Minutes.</h2>
                    <p>Stand out to top hotels and restaurants. Let our smart AI craft the perfect hospitality CV for you.</p>
                    <a href="<?php echo esc_url(home_url('/ai-cv-builder/')); ?>" class="nk-btn-white candidate-btn">Create AI Resume</a>
                </div>
            </div>

            <div class="nk-ecosystem-card employer-card">
                <div class="nk-eco-content">
                    <h3>For Employers</h3>
                    <h2>Hire Top Hospitality Talent Instantly.</h2>
                    <p>Post your open roles today and connect with thousands of qualified professionals ready to work.</p>
                    <a href="<?php echo esc_url(home_url('/post-job/')); ?>" class="nk-btn-white employer-btn">Post a Job</a>
                </div>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_ecosystem_teaser', 'nk_ecosystem_teaser_shortcode');