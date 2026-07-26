<?php
if (!defined('ABSPATH')) exit;

// Force Custom Login Redirect
add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
    $custom_login_url = site_url( '/login/' ); 
    if ( ! empty( $redirect ) ) {
        $custom_login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom_login_url );
    }
    return $custom_login_url;
}, 10, 3 );

add_filter( 'job_manager_job_applications_login_url', function($url) {
    return site_url('/login/');
}); 

// Redirect default WooCommerce shop to custom Dynamic Services page
add_action( 'template_redirect', 'nk_redirect_default_shop_page' );
function nk_redirect_default_shop_page() {
    if ( function_exists('is_shop') && is_shop() ) {
        wp_safe_redirect( home_url( '/services/' ) );
        exit;
    }
}