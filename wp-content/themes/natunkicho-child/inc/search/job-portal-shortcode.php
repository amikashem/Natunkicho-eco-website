<?php
if (!defined('ABSPATH')) exit;

function nk_job_portal_shortcode() {
    // Get current search parameters so the inputs "remember" what you searched for
    $keywords = isset($_GET['keywords']) ? sanitize_text_field($_GET['keywords']) : '';
    $location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';

    ob_start();
    ?>
    <div class="nk-job-portal-wrapper">
        <!--
        <div class="nk-premium-search-bar">
            <form action="" method="GET" class="nk-search-form">
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">🔍</span>
                    <input type="text" name="keywords" placeholder="Job title, keyword, or company..." value="<?php echo esc_attr($keywords); ?>">
                </div>
                
                <div class="nk-search-divider"></div>
                
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">📍</span>
                    <input type="text" name="location" placeholder="City, state, or region..." value="<?php echo esc_attr($location); ?>">
                </div>
                
                <button type="submit" class="nk-search-submit-btn">Find Jobs</button>
            </form>
        </div> --->

        <div class="nk-portal-results">
            <?php echo do_shortcode('[nk_unified_search]'); ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
// Ensure it's only added once
if (!shortcode_exists('nk_job_portal')) {
    add_shortcode('nk_job_portal', 'nk_job_portal_shortcode');
}