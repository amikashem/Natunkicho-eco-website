<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add only the correct viewport meta tag for mobile responsiveness
 */
add_action( 'wp_head', function() {
    // Remove duplicate viewports from parent or Elementor (safety)
    ob_start();
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    echo ob_get_clean();
}, 0);
