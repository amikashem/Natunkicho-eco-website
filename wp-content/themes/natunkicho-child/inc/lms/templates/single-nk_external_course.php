<?php
/**
 * Single Template for External Affiliate Courses
 */
get_header(); 
?>

<?php while ( have_posts() ) : the_post(); 
    // Get the custom affiliate data
    $ext_url   = get_post_meta( get_the_ID(), '_nk_ext_url', true );
    $ext_price = get_post_meta( get_the_ID(), '_nk_ext_price', true );
    $ext_btn   = get_post_meta( get_the_ID(), '_nk_ext_btn_text', true ) ?: 'Enroll on Partner Site';
    
    // Get Provider Name
    $providers = wp_get_post_terms( get_the_ID(), 'nk_course_provider' );
    $provider_name = !empty($providers) ? $providers[0]->name : 'Partner';
?>

<div style="background: #1a1a1a; padding: 60px 0; color: #fff;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: center;">
        
        <!-- Left: Course Details -->
        <div>
            <span style="display: inline-block; background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; font-size: 13px; margin-bottom: 15px;">Verified Partner Course</span>
            <h1 style="font-size: 2.5rem; margin-bottom: 20px;"><?php the_title(); ?></h1>
            <p style="font-size: 1.1rem; color: #ccc; margin-bottom: 30px;">Offered by <strong><?php echo esc_html( $provider_name ); ?></strong></p>
            
            <div style="display: flex; gap: 20px;">
                <div style="background: rgba(255,255,255,0.05); padding: 15px 25px; border-radius: 8px;">
                    <span style="display:block; font-size: 12px; color: #aaa;">Price</span>
                    <strong style="font-size: 1.2rem;"><?php echo esc_html( $ext_price ); ?></strong>
                </div>
            </div>
        </div>

        <!-- Right: The Call to Action -->
        <div style="background: #fff; padding: 30px; border-radius: 12px; color: #222; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h3 style="margin-bottom: 10px;">Ready to start?</h3>
            <p style="color: #666; font-size: 14px; margin-bottom: 25px;">You will be redirected to our trusted partner to complete your enrollment.</p>
            
            <a href="<?php echo esc_url( $ext_url ); ?>" target="_blank" rel="noopener noreferrer" style="display: block; width: 100%; padding: 15px; background: #0056b3; color: #fff; text-decoration: none; font-weight: bold; font-size: 16px; border-radius: 6px; transition: 0.2s;">
                <?php echo esc_html( $ext_btn ); ?>
            </a>
            
            <p style="font-size: 12px; color: #999; margin-top: 15px;">Earns Natunkicho a small commission at no cost to you.</p>
        </div>

    </div>
</div>

<div style="background: #f9f9f9; padding: 60px 0; min-height: 40vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 0 20px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); line-height: 1.8; color: #444; font-size: 1.1rem;">
        <h2 style="margin-bottom: 20px; color: #222; border-bottom: 2px solid #eee; padding-bottom: 15px;">About this Course</h2>
        <?php the_content(); ?>
    </div>
</div>

<!-- NEW: Related Courses Section -->
<div style="background: #ffffff; padding: 60px 0; border-top: 1px solid #eeeeee;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <h2 style="font-size: 2rem; margin-bottom: 30px; text-align: center;">More Courses from <?php echo esc_html( $provider_name ); ?></h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <?php
            // Query 3 related courses from the same provider, excluding the current one
            $related_courses = new WP_Query( array(
                'post_type'      => 'nk_external_course',
                'posts_per_page' => 3,
                'post__not_in'   => array( get_the_ID() ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'nk_course_provider',
                        'field'    => 'slug',
                        'terms'    => !empty($providers) ? $providers[0]->slug : ''
                    )
                )
            ) );

            if ( $related_courses->have_posts() ) :
                while ( $related_courses->have_posts() ) : $related_courses->the_post(); 
                ?>
                <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; transition: 0.3s;">
                    <div style="height: 160px; background: url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>') center/cover;"></div>
                    <div style="padding: 20px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 1.1rem;"><a href="<?php the_permalink(); ?>" style="color: #222; text-decoration: none;"><?php the_title(); ?></a></h4>
                        <a href="<?php the_permalink(); ?>" style="color: #0056b3; font-weight: 600; text-decoration: none; font-size: 14px;">View Course &rarr;</a>
                    </div>
                </div>
                <?php 
                endwhile;
                wp_reset_postdata();
            else:
                echo '<p style="color:#888;">No other courses from this provider yet.</p>';
            endif;
            ?>
        </div>
    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>