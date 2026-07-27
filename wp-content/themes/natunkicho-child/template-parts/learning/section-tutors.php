<?php
/**
 * Template Part: Private Tutors
 * Profiles of independent mentors, chefs, and language trainers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<section id="nk-tutors" class="nk-learning-section bg-light-gray">
    <div class="nk-learning-container">
        
        <div class="nk-section-header">
            <h2 class="nk-section-title">Learn 1-on-1 with Experts</h2>
            <a href="#all-tutors" class="nk-view-all-link">Browse All Tutors &rarr;</a>
        </div>
        
        <div class="nk-tutor-grid">
            
            <?php
            // Fetch Tutors from the Database
            $tutors = new WP_Query( array(
                'post_type'      => 'nk_tutor',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
            ) );

            if ( $tutors->have_posts() ) :
                while ( $tutors->have_posts() ) : $tutors->the_post(); 
                    
                    // Fetch Custom Fields
                    $role      = get_post_meta( get_the_ID(), 'nk_tutor_role', true ) ?: 'Hospitality Mentor';
                    $location  = get_post_meta( get_the_ID(), 'nk_tutor_location', true ) ?: 'Global';
                    $languages = get_post_meta( get_the_ID(), 'nk_tutor_languages', true ) ?: 'English';
                    $price     = get_post_meta( get_the_ID(), 'nk_tutor_price', true ) ?: 'Contact for pricing';
                    ?>
                    
                    <div class="nk-tutor-card">
                        <div class="nk-tutor-header">
                            <div class="nk-tutor-avatar" style="background: url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ); ?>') center/cover;">
                                <?php if ( ! has_post_thumbnail() ) : ?>
                                    <i class="dashicons dashicons-admin-users"></i>
                                <?php endif; ?>
                            </div>
                            <div class="nk-tutor-basic-info">
                                <h4 class="nk-tutor-name"><?php the_title(); ?></h4>
                                <span class="nk-tutor-role"><?php echo esc_html( $role ); ?></span>
                            </div>
                        </div>
                        <div class="nk-tutor-details">
                            <div class="nk-tutor-meta">
                                <span><i class="dashicons dashicons-location"></i> <?php echo esc_html( $location ); ?></span>
                                <span><i class="dashicons dashicons-translation"></i> <?php echo esc_html( $languages ); ?></span>
                            </div>
                            <p class="nk-tutor-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 12, '...' ); ?></p>
                        </div>
                        <div class="nk-tutor-footer">
                            <span class="nk-tutor-price"><?php echo esc_html( $price ); ?></span>
                            <a href="<?php the_permalink(); ?>" class="nk-btn nk-btn-primary-sm">Book Session</a>
                        </div>
                    </div>

                <?php 
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>Mentors will be listed here soon.</p>';
            endif;
            ?>
            
        </div>
        
    </div>
</section>