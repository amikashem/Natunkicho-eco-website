<?php
/**
 * Dynamic Category Bar Shortcode
 * Location: template-parts/category-bar/nk-category-bar.php
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// === Enqueue CSS and JS === //
add_action( 'wp_enqueue_scripts', function() {
    $theme_dir = get_stylesheet_directory_uri();
    wp_enqueue_style( 'nk-category-bar', $theme_dir . '/template-parts/category-bar/nk-category-bar.css', [], '1.0' );
    wp_enqueue_script( 'nk-category-bar', $theme_dir . '/template-parts/category-bar/nk-category-bar.js', ['jquery'], '1.0', true );
});

// === Shortcode Function === //
function nk_category_bar_shortcode() {
    $categories = get_categories([
        'orderby' => 'name',
        'order'   => 'ASC',
        'hide_empty' => true,
    ]);

    if ( empty( $categories ) ) {
        return '<p class="nk-no-categories">No categories found.</p>';
    }

    ob_start(); ?>

    <div class="nk-category-bar">
        <?php foreach ( $categories as $index => $category ) : ?>
            <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"
               class="nk-category-item <?php echo $index >= 4 ? 'nk-hidden' : ''; ?>">
                <?php echo esc_html( $category->name ); ?>
            </a>
        <?php endforeach; ?>

        <?php if ( count( $categories ) > 4 ) : ?>
            <button class="nk-show-more">Show More</button>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_category_bar', 'nk_category_bar_shortcode' );
