<?php 
/**
 * Category Archive Template - Dynamic Grid (2 Columns, 3 Rows)
 * Location: /hello-child/category.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
?>

<style>
/* === Category Grid Styling === */
.nk-category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin: 40px auto;
  max-width: 1100px;
  padding: 0 15px;
}

.nk-category-card {
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.nk-category-card:hover {
  transform: translateY(-5px);
}

.nk-category-card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.nk-category-card h3 {
  font-size: 1.2rem;
  padding: 15px;
  margin: 0;
  color: #222;
}

.nk-category-card a {
  color: inherit;
  text-decoration: none;
}

.nk-category-card p {
  font-size: 0.95rem;
  padding: 0 15px 15px;
  color: #555;
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

.nk-no-posts {
  text-align: center;
  margin: 50px 0;
  color: #777;
}
</style>

<main id="primary" class="site-main">
  <header class="page-header">
    <h1 class="page-title" style="text-align:center;margin-top:40px;">
      <?php single_cat_title(); ?>
    </h1>
    <?php if ( category_description() ) : ?>
      <div class="archive-description" style="text-align:center;margin-bottom:30px;">
        <?php echo category_description(); ?>
      </div>
    <?php endif; ?>
  </header>

  <div class="nk-category-grid" id="nk-category-grid">
    <?php
    // Get the current page number safely
    $paged = max( 1, get_query_var( 'paged', 1 ) );

    $args = array(
      'post_type' => 'post',
      'posts_per_page' => 6, // 2 columns x 3 rows
      'paged' => $paged,
      'cat' => get_queried_object_id(),
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) :
      while ( $query->have_posts() ) : $query->the_post(); ?>
        <article class="nk-category-card">
          <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) {
              the_post_thumbnail( 'medium_large' );
            } else {
              echo '<img src="' . esc_url( get_stylesheet_directory_uri() . '/placeholder.jpg' ) . '" alt="No Image">';
            } ?>
            <h3><?php the_title(); ?></h3>
          </a>
          <p><?php echo wp_trim_words( get_the_excerpt(), 18, '...' ); ?></p>
        </article>
      <?php endwhile;
    else :
      echo '<p class="nk-no-posts">No posts found in this category.</p>';
    endif;
    wp_reset_postdata();
    ?>
  </div>

  <?php
  // === Pagination: show “View More” if more pages exist ===
  if ( $query->max_num_pages > 1 && $paged < $query->max_num_pages ) :
    $next_page = $paged + 1;
    $next_link = get_pagenum_link( $next_page );
  ?>
    <a class="nk-view-more" href="<?php echo esc_url( $next_link ); ?>">View More</a>
  <?php endif; ?>
</main>

<?php
get_footer();
?>
