<?php
/**
 * Premium Hero Slider Shortcode
 */
if (!defined('ABSPATH')) exit;

// Register the shortcode
function nk_hero_slider_shortcode($atts) {
    // Enqueue the necessary assets
    nk_enqueue_hero_slider_assets();
    
    // Shortcode attributes
    $atts = shortcode_atts(array(
        'posts_count' => '5',
        'products_count' => '5',
        'interval' => '5000'
    ), $atts, 'nk_hero_slider');
    
    // Get popular posts (by comment count)
    $popular_posts = get_posts(array(
        'post_type' => 'post',
        'numberposts' => $atts['posts_count'],
        'meta_key' => '_thumbnail_id',
        'orderby' => 'comment_count',
        'order' => 'DESC'
    ));
    
    // Get latest products
    $latest_products = get_posts(array(
        'post_type' => 'product',
        'numberposts' => $atts['products_count'],
        'meta_key' => '_thumbnail_id',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    // Mix posts and products
    $all_items = array_merge($popular_posts, $latest_products);
    shuffle($all_items); // Randomize the order
    
    // Text options
    $product_texts = array(
        "Get Hospitality Items with Discount",
        "Premium Quality, Unbeatable Prices",
        "Limited Time Offers - Shop Now",
        "Elevate Your Business with Our Products",
        "Exclusive Deals on Professional Gear"
    );
    
    $post_texts = array(
        "Earn More Skills Today",
        "Learn & Grow Your Expertise",
        "Professional Development Made Easy",
        "Master New Techniques",
        "Boost Your Career with Expert Insights"
    );
    
    ob_start();
    
    if (!empty($all_items)) {
    ?>
    
<div class="nk-hero-slider-wrapper">
    <div class="nk-hero-slider" data-interval="<?php echo esc_attr($atts['interval']); ?>">
        <div class="nk-slider-container">
            <div class="nk-slider-track">
                <?php foreach ($all_items as $item): 
                    $is_product = ($item->post_type === 'product');
                    $link = get_permalink($item->ID);
                    $image_id = get_post_thumbnail_id($item->ID);
                    
                    // If no featured image, try to get first image from content
                    if (!$image_id) {
                        $image_id = nk_get_first_image_id($item->post_content);
                    }
                    
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : get_stylesheet_directory_uri() . '/assets/images/placeholder.jpg';
                    
                    // Select random text
                    $random_text = $is_product ? 
                        $product_texts[array_rand($product_texts)] : 
                        $post_texts[array_rand($post_texts)];
                    
                    $button_text = $is_product ? 'Buy Now' : 'Learn More';
                ?>
                <div class="nk-slide <?php echo $is_product ? 'product-slide' : 'post-slide'; ?>">
                    <div class="nk-slide-image">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($random_text); ?>" />
                        <div class="nk-image-overlay"></div>
                    </div>
                    
                    <div class="nk-slide-content">
                        <div class="nk-content-wrapper">
                            <p class="nk-slide-text"><?php echo esc_html($random_text); ?></p>
                            <a href="<?php echo esc_url($link); ?>" class="nk-slide-button">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation -->
            <button class="nk-slider-prev">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="nk-slider-next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <!-- Dots -->
            <div class="nk-slider-dots"></div>
        </div>
    </div>
</div>

    <?php
    } else {
        echo '<p>No content available for the slider.</p>';
    }
    
    return ob_get_clean();
}

// Helper function to get first image from content
function nk_get_first_image_id($content) {
    preg_match('/<img.*?src="([^"]+)".*?>/i', $content, $matches);
    if (!empty($matches[1])) {
        $image_url = $matches[1];
        $image_id = attachment_url_to_postid($image_url);
        return $image_id ?: false;
    }
    return false;
}

// Register the shortcode
add_shortcode('nk_hero_slider', 'nk_hero_slider_shortcode');