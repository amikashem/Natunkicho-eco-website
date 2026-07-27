<?php
if (!defined('ABSPATH')) exit;

function nk_hero_search_bar_shortcode() {
    ob_start();
    ?>
    <div class="nk-hero-search-container">
        <form action="<?php echo esc_url(home_url('/find-jobs/')); ?>" method="GET" class="nk-hero-search-form">
            
            <div class="nk-search-input-group">
                <input type="text" name="keywords" placeholder="Job title, keyword, or company..." required>
            </div>
            
            <div class="nk-search-input-group divider">
                <input type="text" name="location" placeholder="City, state, or country...">
            </div>
            
            <button type="submit" class="nk-hero-search-btn">Find Jobs</button>
            
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_hero_search_bar', 'nk_hero_search_bar_shortcode');