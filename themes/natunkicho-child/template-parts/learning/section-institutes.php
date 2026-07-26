<?php
/**
 * Template Part: Dynamic Institutes (Partners & Universities)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<section id="nk-institutes" class="nk-learning-section bg-light-gray" style="padding: 60px 0; background: #f9f9f9;">
    <div class="nk-learning-container">
        
        <div class="nk-section-header">
            <h2 class="nk-section-title">Our Global Partners</h2>
            <p class="nk-section-subtitle">Learn from top-tier universities and hospitality brands.</p>
        </div>

        <div class="nk-institute-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php
            // Fetch Institutes from the Database
            $institutes = new WP_Query( array(
                'post_type'      => 'nk_institute',
                'posts_per_page' => 4, // Show 4 partners
                'post_status'    => 'publish',
            ) );

            if ( $institutes->have_posts() ) :
                while ( $institutes->have_posts() ) : $institutes->the_post(); 
                    ?>
                    
                    <div class="nk-institute-card" style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; text-align: center;">
                        <div class="nk-institute-logo" style="width: 80px; height: 80px; margin: 0 auto 20px auto; border-radius: 50%; overflow: hidden; border: 1px solid #ddd;">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else : ?>
                                <div style="width: 100%; height: 100%; background: #0056b3; color: #fff; line-height: 80px; font-size: 24px; font-weight: bold;">
                                    <?php echo substr( get_the_title(), 0, 1 ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="nk-inst-title" style="margin: 0 0 10px 0; font-size: 1.2rem;"><?php the_title(); ?></h3>
                        <p class="nk-inst-desc" style="color: #666; font-size: 0.95rem; margin-bottom: 20px;">
                            <?php echo wp_trim_words( get_the_excerpt(), 12, '...' ); ?>
                        </p>
                        
                        <a href="<?php the_permalink(); ?>" class="nk-btn nk-btn-outline-sm" style="display: inline-block; padding: 8px 20px; border: 1px solid #0056b3; color: #0056b3; border-radius: 4px; text-decoration: none;">View Profile</a>
                    </div>

                <?php 
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>Add partner institutes in the backend to see them here.</p>';
            endif;
            ?>
        </div>
        
    </div>
</section>