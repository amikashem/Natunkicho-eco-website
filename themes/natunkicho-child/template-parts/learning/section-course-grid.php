<?php
/**
 * Template Part: Affiliate & Popular Courses
 * Grid of top courses seamlessly blending Internal and Affiliate courses.
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<section id="nk-courses" class="nk-learning-section bg-light-gray">
    <div class="nk-learning-container">
        
        <div class="nk-section-header with-filters">
            <h2 class="nk-section-title">Trending Courses</h2>
            
            <div class="nk-course-filters">
                <button class="nk-filter-btn active" data-filter="all">All</button>
                <button class="nk-filter-btn" data-filter="hotel-management">Hotel Management</button>
                <button class="nk-filter-btn" data-filter="culinary">Culinary</button>
                <button class="nk-filter-btn" data-filter="free">Free Courses</button>
            </div>
        </div>
        
        <div class="nk-course-grid">
            <?php
            // Pulling BOTH Internal (courses) and External (nk_external_course)
            $tutor_courses = new WP_Query( array(
                'post_type'      => array( 'courses', 'nk_external_course' ), 
                'posts_per_page' => 8,
                'post_status'    => 'publish'
            ) );

            if ( $tutor_courses->have_posts() ) :
                while ( $tutor_courses->have_posts() ) : $tutor_courses->the_post(); 
                    
                    $course_id = get_the_ID();
                    $post_type = get_post_type();
                    
                    // --- SMART DISPLAY LOGIC ---
                    if ( $post_type === 'nk_external_course' ) {
                        // It is an API Partner Course
                        $providers = wp_get_post_terms( $course_id, 'nk_course_provider' );
                        $institute = !empty($providers) ? $providers[0]->name : 'Partner Course';
                        $badge     = '<span class="nk-badge nk-badge-affiliate" style="background:#0056b3; color:#fff; padding:5px 10px; border-radius:4px; font-size:12px; position:absolute; top:15px; left:15px; font-weight:bold;">Partner: ' . esc_html($institute) . '</span>';
                    } else {
                        // It is a Native Tutor LMS Course
                        $institute = 'Natunkicho Academy';
                        $badge     = ''; 
                    }
                    ?>
                    
                    <div class="nk-course-card" style="position:relative;">
                        <div class="nk-course-thumbnail" style="background: url('<?php echo esc_url( get_the_post_thumbnail_url( $course_id, 'medium_large' ) ); ?>') center/cover; height:200px; position:relative;">
                            <?php echo $badge; ?>
                        </div>
                        
                        <div class="nk-course-content">
                            <span class="nk-course-institute" style="color:#888; font-size:12px; font-weight:600; text-transform:uppercase;"><?php echo esc_html( $institute ); ?></span>
                            <h3 class="nk-course-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <div class="nk-course-meta">
                                <span class="rating"><i class="dashicons dashicons-star-filled"></i> New</span>
                            </div>
                            
                            <div class="nk-course-footer">
                                <div class="nk-course-pricing"></div>
                                <!-- ALWAYS links to the Single Page first for SEO -->
                                <a href="<?php the_permalink(); ?>" class="nk-btn nk-btn-primary-sm">
                                    View Course
                                </a>
                            </div>
                        </div>
                    </div>

                <?php 
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No courses found. Add some courses in Tutor LMS or via the API Importer!</p>';
            endif;
            ?>
        </div>
        <div class="nk-section-footer center">
            <button class="nk-btn nk-btn-outline" id="load-more-courses">Load More Courses</button>
        </div>
        
    </div>
</section>