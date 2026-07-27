<?php
/**
 * Template Part: Dynamic 3-Column Post Section
 * Usage: [post_grid_section]
 */

?>

<section class="rc-post-section">
  <div class="rc-post-grid">
    <!-- Popular Posts -->
    <div class="rc-post-column" id="popularPosts">
      <h2>Popular Posts</h2>
      <?php
      $popular_args = array(
        'posts_per_page' => 2,
        'orderby' => 'comment_count',
        'order' => 'DESC'
      );
      $popular_query = new WP_Query($popular_args);
      if ($popular_query->have_posts()):
        while ($popular_query->have_posts()): $popular_query->the_post(); ?>
          <div class="rc-post-item">
            <a href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } ?>
              <h3><?php the_title(); ?></h3>
            </a>
            <div class="rc-share">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank">Facebook</a> |
              <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>" target="_blank">Twitter</a>
            </div>
          </div>
        <?php endwhile;
        wp_reset_postdata();
      else:
        echo '<p>No popular posts found.</p>';
      endif;
      ?>
    </div>

    <!-- Recent Posts -->
    <div class="rc-post-column" id="recentPosts">
      <h2>Recent Posts</h2>
      <?php
      $recent_args = array(
        'posts_per_page' => 2,
        'orderby' => 'date',
        'order' => 'DESC'
      );
      $recent_query = new WP_Query($recent_args);
      if ($recent_query->have_posts()):
        while ($recent_query->have_posts()): $recent_query->the_post(); ?>
          <div class="rc-post-item">
            <a href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } ?>
              <h3><?php the_title(); ?></h3>
            </a>
          </div>
        <?php endwhile;
        wp_reset_postdata();
      else:
        echo '<p>No recent posts found.</p>';
      endif;
      ?>
    </div>

    <!-- Hospitality Skills / Training -->
    <div class="rc-post-column" id="hospitalityPosts">
      <h2>Hospitality Skills & Training</h2>
      <?php
      $hospitality_args = array(
        'posts_per_page' => 2,
        'orderby' => 'rand',
        'category_name' => 'hospitality-skills,hospitality-training'
      );
      $hospitality_query = new WP_Query($hospitality_args);
      if ($hospitality_query->have_posts()):
        while ($hospitality_query->have_posts()): $hospitality_query->the_post(); ?>
          <div class="rc-post-item">
            <a href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } ?>
              <h3><?php the_title(); ?></h3>
            </a>
          </div>
        <?php endwhile;
        wp_reset_postdata();
      else:
        echo '<p>No posts found in this category.</p>';
      endif;
      ?>
    </div>
  </div>
</section>

<style>
.rc-post-section {
  padding: 40px 0;
  background: transparent;
  text-align: center;
}
.rc-post-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
  align-items: stretch;
  max-width: 1200px;
  margin: 0 auto;
}
.rc-post-column {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.08);
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: opacity 0.6s ease;
}
.rc-post-column h2 {
  font-size: 22px;
  margin-bottom: 20px;
  color: #111;
}
.rc-post-item {
  text-align: left;
  margin-bottom: 25px;
  transition: all 0.3s ease;
}
.rc-post-item img {
  width: 100%;
  border-radius: 8px;
  margin-bottom: 10px;
}
.rc-post-item h3 {
  font-size: 18px;
  color: #333;
  margin: 0;
  line-height: 1.4;
}
.rc-post-item:hover h3 {
  color: #0073e6;
}
.rc-share {
  font-size: 14px;
  margin-top: 8px;
}
.rc-share a {
  color: #0073e6;
  text-decoration: none;
}
.rc-share a:hover {
  text-decoration: underline;
}
@media (max-width: 991px) {
  .rc-post-grid {
    grid-template-columns: 1fr;
  }
}
</style>
