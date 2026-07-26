<?php
/**
 * nk-footer-slider.php
 * Shortcode: [natunkicho_latest_slider]
 *
 * Outputs a footer/latest-posts auto-scrolling slider.
 * Enqueues CSS & JS only when shortcode is rendered.
 *
 * Place this file in: hello-child/includes/shortcodes/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nk_footer_slider_enqueue_assets() {
    // Register (register first to avoid duplicate registration)
    wp_register_style(
        'nk-footer-slider',
        get_stylesheet_directory_uri() . '/assets/css/nk-footer-slider.css',
        array(),
        '1.0.0'
    );

    wp_register_script(
        'nk-footer-slider',
        get_stylesheet_directory_uri() . '/assets/js/nk-footer-slider.js',
        array(),
        '1.0.0',
        true
    );
}

/**
 * Render shortcode and enqueue assets.
 *
 * Attributes:
 * - category (slug) optional
 * - limit (int) how many posts to fetch (default 6)
 */
function nk_footer_slider_shortcode( $atts = array() ) {
    // Register assets (if not already)
    nk_footer_slider_enqueue_assets();

    // Shortcode attributes
    $atts = shortcode_atts( array(
        'category' => '',
        'limit'    => 6,
    ), $atts, 'natunkicho_latest_slider' );

    // enqueue only when rendering
    wp_enqueue_style( 'nk-footer-slider' );
    wp_enqueue_script( 'nk-footer-slider' );

    // Pass attributes to JS in case you want to use them later (optional)
    wp_localize_script( 'nk-footer-slider', 'nkFooterSliderVars', array(
        'category' => sanitize_text_field( $atts['category'] ),
        'limit'    => intval( $atts['limit'] ),
        'siteUrl'  => esc_url_raw( home_url() ),
    ) );

    // Output HTML (identical structure)
    ob_start();
    ?>
    <div class="latest-post-slider-wrapper">
      <ul class="latest-post-slider slider-posts" aria-hidden="false"></ul>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'natunkicho_latest_slider', 'nk_footer_slider_shortcode' );
