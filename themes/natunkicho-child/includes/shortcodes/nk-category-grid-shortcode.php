<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [nk_category_grid]
 */
function nk_category_grid_shortcode( $atts ) {
    // Enqueue the assets when shortcode is used
    if (function_exists('nk_enqueue_category_grid_assets')) {
        nk_enqueue_category_grid_assets();
    }
    
    $atts = shortcode_atts( array(
        'number' => 9,
    ), $atts, 'nk_category_grid' );

    // Category icons mapping
    $category_icons = array(
        'Kitchen' => '🍳',
        'Restaurant & Bar' => '🍽️',
        'Operations' => '⚙️',
        'Food Business idea' => '💡',
        'Compliance' => '📋',
        'Recipes' => '📖',
        'Hospitality Skills' => '🎓',
        'Halal Tourism' => '🕌',
        'Luxury Tourism' => '⭐'
    );

    $categories = array(
        'Kitchen',
        'Restaurant & Bar',
        'Operations',
        'Food Business idea',
        'Compliance',
        'Recipes',
        'Hospitality Skills',
        'Halal Tourism',
        'Luxury Tourism'
    );

    ob_start();
    ?>
    
    <div class="nk-category-grid-container">
        <div class="nk-category-grid-wrapper">
            <?php foreach ( $categories as $category_name ) :
                $category = get_categories( array(
                    'hide_empty' => false,
                    'name' => $category_name
                ) );
                
                if ( empty( $category ) ) continue;
                $category = $category[0];
                
                $cat_link = get_category_link( $category->term_id );
                $icon = isset($category_icons[$category_name]) ? $category_icons[$category_name] : '📁';
            ?>
                <div class="nk-category-card">
                    <div class="nk-cat-icon"><?php echo $icon; ?></div>
                    
                    <div class="nk-cat-content">
                        <h3><a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $category_name ); ?></a></h3>

                        <div class="nk-cat-posts">
                            <?php
                            $posts = get_posts( array(
                                'category' => $category->term_id,
                                'posts_per_page' => 2,
                                'post_status' => 'publish'
                            ));
                            
                            foreach ( $posts as $post ) :
                                // Use specific image size for consistency
                                $thumb = get_the_post_thumbnail_url( $post->ID, 'medium' );
                                $thumb = $thumb ? $thumb : get_template_directory_uri() . '/assets/img/default-thumb.jpg';
                            ?>
                                <a href="<?php echo get_permalink( $post->ID ); ?>" class="nk-subpost">
                                    <div class="nk-image-container">
                                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" class="nk-post-image">
                                    </div>
                                    <div class="nk-post-title"><?php echo esc_html( $post->post_title ); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="nk-button-container">
                            <a href="<?php echo esc_url( $cat_link ); ?>" class="nk-more-btn">Explore More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_category_grid', 'nk_category_grid_shortcode' );