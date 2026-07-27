<?php
/**
 * Shortcode: Post Grid Section
 * File: /includes/shortcodes/post-grid-shortcode.php
 * Usage: [post_grid_section]
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Prevent direct access

function rc_post_grid_section_shortcode() {
    ob_start();
    get_template_part('template-parts/post-sections/post-grid-section');
    return ob_get_clean();
}
add_shortcode('post_grid_section', 'rc_post_grid_section_shortcode');
