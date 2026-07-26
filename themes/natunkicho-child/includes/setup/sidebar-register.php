<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_register_sidebars() {
	register_sidebar( array(
		'name'          => __( 'Main Sidebar', 'natun-kicho' ),
		'id'            => 'main-sidebar',
		'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'natun-kicho' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'nk_register_sidebars' );
