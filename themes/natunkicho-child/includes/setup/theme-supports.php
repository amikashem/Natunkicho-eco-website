<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_theme_supports() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'nk_theme_supports' );
