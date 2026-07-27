<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * NATUNKICHO CORE SYSTEM LAUNCHER (Clean Architecture)
 * =========================================================================
 * 
 * Active Modules Only - Cleaned & Optimized
 * Last Updated: 2026-07-27
 */

// --- 1. ENQUEUE SCRIPTS & STYLES ---
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('nk-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], time(), true);
    wp_enqueue_script('nk-save-jobs', get_stylesheet_directory_uri() . '/assets/js/save-jobs.js', [], time(), true);
    wp_localize_script('nk-save-jobs', 'nk_ajax', [ 'ajax_url' => admin_url('admin-ajax.php') ]);
}); 

// --- 2. LOAD CORE SETUP & HELPERS ---
require_once get_stylesheet_directory() . '/includes/helpers/meta-viewport.php';
require_once get_stylesheet_directory() . '/includes/setup/enqueue-scripts.php';
require_once get_stylesheet_directory() . '/includes/setup/theme-supports.php';
require_once get_stylesheet_directory() . '/includes/setup/sidebar-register.php';
require_once get_stylesheet_directory() . '/inc/core/custom-redirects.php';

// --- 3. LOAD SAAS & OPTIMIZATION ENGINES ---
require_once get_stylesheet_directory() . '/inc/woocommerce/saas-engine.php';
require_once get_stylesheet_directory() . '/inc/core/theme-optimizations.php';
require_once get_stylesheet_directory() . '/inc/core/init.php';
require_once get_stylesheet_directory() . '/inc/cv-builder/cv-sections.php';

// --- 4. LOAD SEARCH & API SYSTEM ---
require_once get_stylesheet_directory() . '/inc/api/api-sync-manager.php';
require_once get_stylesheet_directory() . '/inc/search/unified-search.php';
require_once get_stylesheet_directory() . '/inc/search/job-portal-shortcode.php';
require_once get_stylesheet_directory() . '/inc/search/hero-search-shortcode.php';
require_once get_stylesheet_directory() . '/inc/search/category-links-shortcode.php';

// --- 5. LOAD LEARNING SYSTEM ---
require_once get_stylesheet_directory() . '/inc/learning/learning-alerts.php';

// --- 6. LOAD SALARY INTELLIGENCE CENTER ---
require_once get_stylesheet_directory() . '/inc/salaries/salary-database.php';
require_once get_stylesheet_directory() . '/inc/salaries/salary-router.php';
require_once get_stylesheet_directory() . '/inc/salaries/salary-shortcodes.php';
require_once get_stylesheet_directory() . '/inc/salaries/salary-dashboard.php';
require_once get_stylesheet_directory() . '/inc/salaries/salary-ai-insights.php';

// --- 7. LOAD LMS & FOOTER ---
require_once get_stylesheet_directory() . '/inc/lms/lms-init.php';
require_once get_stylesheet_directory() . '/inc/footer/footer-functions.php';

// --- 8. LOAD SHORTCODES & COMPONENTS ---
require_once get_stylesheet_directory() . '/includes/shortcodes/post-grid-shortcode.php';
require_once get_stylesheet_directory() . '/includes/shortcodes/nk-dynamic-menu-section.php';
require_once get_stylesheet_directory() . '/includes/shortcodes/nk-dual-search-shortcode.php'; 
require_once get_stylesheet_directory() . '/includes/widgets/custom-widgets.php';
require_once get_stylesheet_directory() . '/includes/widgets/floating-chat.php'; 
require_once get_stylesheet_directory() . '/template-parts/category-bar/nk-category-bar.php';
require_once get_stylesheet_directory() . '/template-parts/product-slider/nk-product-slider.php';

/**
 * =========================================================================
 * UTILITY FUNCTION: Safe Theme Rendering
 * =========================================================================
 * 
 * Safely renders a child theme view or template part.
 * Prevents typos or coding mistakes from breaking the front-end layout.
 *
 * @param string $relative_file_path Path to the file relative to the child theme root
 * @param array  $variables          Variables you want to pass down to that file
 */
function nk_theme_safe_render(string $relative_file_path, array $variables = []): void {
    $file_path = get_stylesheet_directory() . '/' . ltrim($relative_file_path, '/');

    if (!file_exists($file_path)) {
        if (current_user_can('manage_options')) {
            echo '<div style="background:#fff5f5; color:#c53030; padding:12px; border:1px solid #feb2b2; margin:10px 0; font-family:sans-serif; font-size:13px;">';
            echo '<strong>Theme Admin Error:</strong> File not found at:<br><code>' . esc_html($relative_file_path) . '</code>';
            echo '</div>';
        }
        return;
    }

    if (!empty($variables)) {
        extract($variables);
    }

    try {
        include $file_path;
    } 
    catch (\Throwable $e) {
        error_log('NK Child Theme Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

        if (current_user_can('manage_options')) {
            echo '<div style="background:#fff5f5; color:#c53030; padding:15px; border-left:4px solid #e53e3e; margin:15px 0; font-family:monospace; font-size:13px; border-radius:4px;">';
            echo '<strong style="color:#9b2c2c; display:block; margin-bottom:5px;">⚠️ Theme Component Isolated (Admin Only)</strong>';
            echo '<strong>Error:</strong> ' . esc_html($e->getMessage()) . '<br>';
            echo '<strong>File:</strong> ' . esc_html($e->getFile()) . ' on line ' . esc_html($e->getLine());
            echo '</div>';
        } else {
            echo '<!-- Section temporarily unavailable due to system optimization -->';
        }
    }
}
