<?php
if (!defined('ABSPATH')) exit;

function nk_category_links_shortcode() {
    ob_start();
    ?>
    <div class="nk-category-links-container">
        <div class="nk-category-grid">
            
            <a href="<?php echo esc_url(home_url('/find-jobs/?keywords=Chef')); ?>" class="nk-category-card">
                <div class="nk-category-icon">👨‍🍳</div>
                <h3 class="nk-category-title">Chefs & Culinary</h3>
            </a>

            <a href="<?php echo esc_url(home_url('/find-jobs/?keywords=Manager')); ?>" class="nk-category-card">
                <div class="nk-category-icon">👔</div>
                <h3 class="nk-category-title">Management</h3>
            </a>

            <a href="<?php echo esc_url(home_url('/find-jobs/?keywords=Front+Desk')); ?>" class="nk-category-card">
                <div class="nk-category-icon">🛎️</div>
                <h3 class="nk-category-title">Front Desk</h3>
            </a>

            <a href="<?php echo esc_url(home_url('/find-jobs/?keywords=Housekeeping')); ?>" class="nk-category-card">
                <div class="nk-category-icon">🧹</div>
                <h3 class="nk-category-title">Housekeeping</h3>
            </a>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_category_links', 'nk_category_links_shortcode');