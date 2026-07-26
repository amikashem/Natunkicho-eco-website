<?php
/**
 * NK Product Slider
 * Shortcode: [nk_product_slider]
 * Works with WooCommerce products
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register slider CSS & JS
 */
function nk_product_slider_assets() {
	$base = get_stylesheet_directory_uri() . '/template-parts/product-slider/';
	wp_register_style( 'nk-product-slider', $base . 'nk-product-slider.css', array(), '1.1' );
	wp_register_script( 'nk-product-slider', $base . 'nk-product-slider.js', array(), '1.1', true );
}
add_action( 'wp_enqueue_scripts', 'nk_product_slider_assets' );

/**
 * Product slider shortcode
 */
function nk_product_slider_shortcode( $atts ) {

	if ( ! class_exists( 'WooCommerce' ) ) {
		return '<p class="nk-slider-error">⚠️ WooCommerce not active.</p>';
	}

	$atts = shortcode_atts( array(
		'limit' => 12,
		'orderby' => 'rand', // Changed to random for random products
		'order' => 'DESC',
		'category' => '', // Added category parameter
		'status' => 'publish', // Ensure published products
	), $atts, 'nk_product_slider' );

	// Debug: Check if WooCommerce is functioning
	$debug_info = '';
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$debug_info .= '<!-- WooCommerce Active: ' . ( class_exists( 'WooCommerce' ) ? 'Yes' : 'No' ) . ' -->';
	}

	$args = array(
		'post_type' => 'product',
		'posts_per_page' => intval( $atts['limit'] ),
		'orderby' => sanitize_text_field( $atts['orderby'] ),
		'order' => sanitize_text_field( $atts['order'] ),
		'post_status' => sanitize_text_field( $atts['status'] ),
	);

	// Add category filter if provided
	if ( ! empty( $atts['category'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			),
		);
	}

	// Debug: Show query args
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$debug_info .= '<!-- Query Args: ' . print_r( $args, true ) . ' -->';
	}

	$loop = new WP_Query( $args );

	// Debug: Show found posts
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$debug_info .= '<!-- Found Posts: ' . $loop->found_posts . ' -->';
		$debug_info .= '<!-- SQL Query: ' . $loop->request . ' -->';
	}

	if ( ! $loop->have_posts() ) {
		$debug_message = '';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Check if products exist at all
			$all_products = get_posts( array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'numberposts' => 1
			) );
			$debug_message = '<br><small>Debug: Total published products: ' . count( $all_products ) . '</small>';
		}
		return '<p class="nk-slider-empty">No products found.' . $debug_message . '</p>' . $debug_info;
	}

	wp_enqueue_style( 'nk-product-slider' );
	wp_enqueue_script( 'nk-product-slider' );

	ob_start(); 
	echo $debug_info;
	?>
	<div class="nk-product-slider-wrapper">
		<h3 class="nk-slider-title">Featured Products</h3>

		<div class="nk-product-slider">
			<?php while ( $loop->have_posts() ) : $loop->the_post(); 
				global $product;
				// Ensure $product is properly set
				if ( ! is_a( $product, 'WC_Product' ) ) {
					$product = wc_get_product( get_the_ID() );
				}
			?>
				<div class="nk-product-card">
					<a href="<?php the_permalink(); ?>" class="nk-product-thumb">
						<?php 
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) );
						} else {
							echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="' . esc_attr( get_the_title() ) . '" loading="lazy">';
						}
						?>
					</a>
					<h4 class="nk-product-title"><?php the_title(); ?></h4>
					<span class="nk-product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					<?php if ( $product->is_on_sale() ) : ?>
						<span class="nk-sale-badge">Sale!</span>
					<?php endif; ?>
				</div>
			<?php endwhile; ?>
		</div>

		<!-- Navigation Arrows -->
		<button class="nk-slider-arrow nk-prev" aria-label="Previous products">&#10094;</button>
		<button class="nk-slider-arrow nk-next" aria-label="Next products">&#10095;</button>
	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'nk_product_slider', 'nk_product_slider_shortcode' );