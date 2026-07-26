<?php
/**
 * Custom WooCommerce Archive Template - Grid (2 Columns x 3 Rows)
 * Location: /hello-child/woocommerce/archive-product.php
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<style>
/* === Product Category Grid Styling === */
.nk-product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin: 40px auto;
  max-width: 1100px;
  padding: 0 15px;
}

.nk-product-card {
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
  text-align: center;
}

.nk-product-card:hover {
  transform: translateY(-5px);
}

.nk-product-card img {
  width: 100%;
  height: 250px;
  object-fit: cover;
}

.nk-product-card h3 {
  font-size: 1.1rem;
  margin: 10px 0;
  color: #222;
}

.nk-product-card .price {
  font-size: 1rem;
  color: #f04e31;
  margin-bottom: 12px;
}

.nk-product-card a {
  text-decoration: none;
  color: inherit;
}

.nk-add-to-cart {
  background: #f04e31;
  color: #fff;
  padding: 8px 14px;
  border-radius: 6px;
  text-decoration: none;
  display: inline-block;
  margin-bottom: 15px;
  transition: background 0.3s ease;
}

.nk-add-to-cart:hover {
  background: #d83e22;
}

.nk-view-more {
  display: block;
  width: 200px;
  margin: 40px auto;
  background: #f04e31;
  color: #fff;
  text-align: center;
  padding: 12px 0;
  border-radius: 6px;
  text-decoration: none;
  transition: background 0.3s ease;
}

.nk-view-more:hover {
  background: #d83e22;
}
</style>

<main id="primary" class="site-main">

  <header class="woocommerce-products-header">
    <h1 class="page-title" style="text-align:center;margin-top:40px;">
      <?php woocommerce_page_title(); ?>
    </h1>
    <?php
    if ( apply_filters( 'woocommerce_show_page_description', true ) ) {
      do_action( 'woocommerce_archive_description' );
    }
    ?>
  </header>

  <div class="nk-product-grid">
    <?php
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

    $args = array(
      'post_type' => 'product',
      'posts_per_page' => 6, // 2 columns × 3 rows
      'paged' => $paged,
      'post_status' => 'publish',
      'tax_query' => array(
        array(
          'taxonomy' => 'product_cat',
          'field'    => 'term_id',
          'terms'    => get_queried_object_id(),
        ),
      ),
    );

    $loop = new WP_Query( $args );

    if ( $loop->have_posts() ) :
      while ( $loop->have_posts() ) : $loop->the_post();
        global $product;
        ?>
        <div class="nk-product-card">
          <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) {
              the_post_thumbnail( 'medium_large' );
            } else {
              echo '<img src="' . get_stylesheet_directory_uri() . '/placeholder.jpg" alt="No Image">';
            } ?>
            <h3><?php the_title(); ?></h3>
          </a>
          <?php if ( $product ) : ?>
            <div class="price"><?php echo $product->get_price_html(); ?></div>
            <a class="nk-add-to-cart" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>">
              <?php echo esc_html( $product->add_to_cart_text() ); ?>
            </a>
          <?php endif; ?>
        </div>
        <?php
      endwhile;
    else :
      echo '<p style="text-align:center;">No products found in this category.</p>';
    endif;

    wp_reset_postdata();
    ?>
  </div>

  <?php
  // “View More” pagination
  if ( $loop->max_num_pages > 1 && $paged < $loop->max_num_pages ) :
    $next_page = $paged + 1;
    $next_link = get_pagenum_link( $next_page );
  ?>
    <a class="nk-view-more" href="<?php echo esc_url( $next_link ); ?>">View More</a>
  <?php endif; ?>
</main>

<?php
get_footer( 'shop' );
?>
