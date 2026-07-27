<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nk_enqueue_calculator_assets() {

    if ( is_admin() ) {
        return;
    }
 // CSS
    wp_enqueue_style(
        'nk-calculator-style',
        get_stylesheet_directory_uri() . '/assets/css/nk-calculator.css',
        array(),
        '1.3'
    );

    // Core JS
    wp_enqueue_script(
        'nk-calculator-core',
        get_stylesheet_directory_uri() . '/assets/js/nk-calculator-core.js',
        array('jquery'),
        '1.3',
        true
    );

    // Food Cost JS
    wp_enqueue_script(
        'nk-fc-food-cost',
        get_stylesheet_directory_uri() . '/assets/js/nk-fc-food-cost.js',
        array('nk-calculator-core'),
        '1.0',
        true
    );
}

add_action( 'wp_enqueue_scripts', 'nk_enqueue_calculator_assets' );
