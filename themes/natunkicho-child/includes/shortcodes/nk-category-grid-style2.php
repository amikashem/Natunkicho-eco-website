<?php
/**
 * Category Grid Style 2 Shortcode - With specific styling
 */
if (!defined('ABSPATH')) exit;

// Enqueue assets when shortcode is used
function nk_category_grid_style2_shortcode_enqueue() {
    nk_enqueue_category_grid_style2_assets();
}

// Register the shortcode
function nk_category_grid_style2_shortcode($atts) {
    // Enqueue the necessary assets
    nk_category_grid_style2_shortcode_enqueue();
    
    // Shortcode attributes
    $atts = shortcode_atts(array(
        'title' => 'Explore Post Categories',
        'columns' => '4',
        'initial_visible' => '4'
    ), $atts, 'nk_category_grid_style2');
    
    ob_start();
    ?>
    
<section class="category-section">
  <h2 class="category-title"><?php echo esc_html($atts['title']); ?></h2>

  <div class="category-grid" id="categoryGridStyle2">
    <?php
    $categories = get_categories(array(
      'orderby' => 'name',
      'order'   => 'ASC'
    ));

    if (!empty($categories)) {
      foreach ($categories as $category) {
        $category_link = get_category_link($category->term_id);
        echo '<div class="category-item"><a href="' . esc_url($category_link) . '">' . esc_html($category->name) . '</a></div>';
      }
    } else {
      echo '<p>No categories found.</p>';
    }
    ?>
  </div>

  <button id="showMoreBtnStyle2" class="show-more-btn">Show More Categories</button>
</section>

    <?php
    return ob_get_clean();
}

// Register the shortcode with a NEW name
add_shortcode('nk_category_grid_style2', 'nk_category_grid_style2_shortcode');
add_shortcode('category_grid_style2', 'nk_category_grid_style2_shortcode');