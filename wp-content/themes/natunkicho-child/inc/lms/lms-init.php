<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'NK_LMS_DIR', get_stylesheet_directory() . '/inc/lms/' );

// 1. LMS Cleanup 
require_once NK_LMS_DIR . 'lms-cleanup.php';

// 2. Affiliate Manager (The new fast-edit screen)
require_once NK_LMS_DIR . 'lms-affiliate-manager.php';

// 3. Marketplace Data (Institutes & Tutors)
require_once NK_LMS_DIR . 'lms-marketplace-data.php';

// 4. SliceWP Integration (Unified Dashboards)
require_once NK_LMS_DIR . 'lms-slicewp-sync.php';

// 5. External Affiliate Courses Engine (Option B Architecture)
require_once NK_LMS_DIR . 'lms-external-courses.php';

// 6. Enqueue CSS and JS for the Learning Marketplace
add_action( 'wp_enqueue_scripts', 'nk_lms_enqueue_assets', 99 );
function nk_lms_enqueue_assets() {
    
    // Only load these files if we are on the Learning Marketplace page OR a Tutor LMS course page
    if ( is_page_template( 'template-learning-marketplace.php' ) || ( function_exists( 'is_tutor_page' ) && is_tutor_page() ) || is_singular('nk_institute') || is_singular('nk_tutor') ) {
        
        // Connect the CSS
        wp_enqueue_style( 'nk-learning-style', get_stylesheet_directory_uri() . '/assets/css/learning-marketplace.css', array(), time() );
        
        // Connect the JS
        wp_enqueue_script( 'nk-learning-script', get_stylesheet_directory_uri() . '/assets/js/learning-marketplace.js', array('jquery'), time(), true );
        
        // Pass the AJAX URL to the JS file (prevents console errors if your JS file still has the filter script)
        wp_localize_script( 'nk-learning-script', 'nk_learning_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'nk_learning_nonce' )
        ) );
    }
}

// 6. Custom Authentication Pages (Premium UI Wrappers)
add_shortcode( 'nk_academy_login', 'nk_render_academy_login' );
function nk_render_academy_login() {
    
    // Smart feature: If already logged in, redirect straight to the dashboard
    if ( is_user_logged_in() && function_exists('tutor_utils') ) {
        echo '<script>window.location.href="'.esc_url( tutor_utils()->tutor_dashboard_url() ).'";</script>';
        return '';
    }

    ob_start(); ?>
    <div class="nk-auth-page-wrapper">
        <div class="nk-auth-card">
            <h2>Welcome Back 🎓</h2>
            <p>Sign in to access your courses and dashboard.</p>
            <?php 
            // Tutor LMS uses the dashboard shortcode to render the login form for guests
            echo do_shortcode('[tutor_dashboard]'); 
            ?>
        </div>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'nk_instructor_registration', 'nk_render_instructor_reg' );
function nk_render_instructor_reg() {
    ob_start(); ?>
    <div class="nk-auth-page-wrapper">
        <div class="nk-auth-card">
            <h2>Become a Mentor 👨‍🏫</h2>
            <p>Join our elite team of hospitality experts and start teaching.</p>
            <?php echo do_shortcode('[tutor_instructor_registration_form]'); ?>
        </div>
    </div>
    <?php return ob_get_clean();
}

