<?php
/**
 * Natun Kicho Child Theme – functions.php
 * Optimized for Hello Elementor parent theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access
}

/**
 * Safe include helper
 * -----------------------------------
 */
function nk_require( $relative_path ) {
	$file = get_stylesheet_directory() . $relative_path;
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

function nk_display_custom_sidebar() {
    if ( file_exists( get_stylesheet_directory() . '/template-parts/single/sidebar-nksp.php' ) ) {
        include get_stylesheet_directory() . '/template-parts/single/sidebar-nksp.php';
    }
}

// Load Mailchimp Handler
nk_require('/includes/mailchimp-handler.php');

// Localize AJAX URL for JavaScript
function nk_localize_ajax_url() {
    wp_localize_script('nksp-single-js', 'nksp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'nk_localize_ajax_url');

function hello_child_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}
add_action('wp_enqueue_scripts', 'hello_child_enqueue_styles');

// Remove duplicate header actions if any
function hello_child_remove_sidebar() {
    if (is_page()) {
        add_filter('is_active_sidebar', '__return_false');
    }
}
add_action('wp', 'hello_child_remove_sidebar');

/**
 * Load core setup files
 * -----------------------------------
 */
nk_require( '/includes/helpers/meta-viewport.php' );
nk_require( '/includes/setup/enqueue-scripts.php' );
nk_require( '/includes/setup/theme-supports.php' );
nk_require( '/includes/setup/sidebar-register.php' );
nk_require( '/includes/setup/elementor-settings.php' );

/**
 * Load shortcodes & widgets
 * -----------------------------------
 */
nk_require( '/includes/shortcodes/post-grid-shortcode.php' );
nk_require( '/includes/widgets/custom-widgets.php' );

/**
 * Load template-part PHP components
 * -----------------------------------
 */
nk_require( '/template-parts/category-bar/nk-category-bar.php' );
nk_require( '/template-parts/product-slider/nk-product-slider.php' );

// ✅ Do NOT include .css or .js files via PHP require.
// Those are automatically loaded by nk-product-slider.php when the shortcode is used.
require_once get_stylesheet_directory() . '/includes/shortcodes/nk-dynamic-menu-section.php';

/**--------Translate Module---------------------------------*/
// Load translate functions
nk_require( '/includes/translate/translate-functions.php' );

/**
 * Output Translate Button
 */
function nk_output_translate_button() {
    // Check if file exists and include it
    $translate_file = get_stylesheet_directory() . '/includes/translate/translate-button.php';
    if (file_exists($translate_file)) {
        include $translate_file;
    }
}
add_action('wp_footer', 'nk_output_translate_button', 10);
/**---End translate-----------------------------------------*/

/**
 * WooCommerce Affiliate Links Script
 */
function nk_woocommerce_affiliate_script() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    ?>
    <script>
    /**
     * Force affiliate (ShopEngine/WooCommerce) forms to open in new tab
     */
    document.addEventListener('DOMContentLoaded', function() {
      function forceAffiliateFormsNewTab() {
        const forms = document.querySelectorAll('form.cart[action]');
        forms.forEach(form => {
          const action = form.getAttribute('action');
          if (!action) return;

          if (/amzn\.to|amazon\.|flipkart\.|aliexpress\.|aff/i.test(action) && !action.includes(window.location.host)) {
            form.setAttribute('target', '_blank');
          }
        });

        const links = document.querySelectorAll('a[href]');
        links.forEach(link => {
          const href = link.getAttribute('href');
          if (!href) return;
          if (/amzn\.to|amazon\.|flipkart\.|aliexpress\.|aff/i.test(href) && !href.includes(window.location.host)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener nofollow');
          }
        });
      }

      forceAffiliateFormsNewTab();
      setTimeout(forceAffiliateFormsNewTab, 1500);
      document.addEventListener('ajaxComplete', forceAffiliateFormsNewTab);
    });
    </script>
    <?php
}
add_action('wp_footer', 'nk_woocommerce_affiliate_script', 30);