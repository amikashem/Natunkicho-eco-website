<?php
/**
 * Single Template for Private Tutors
 */
get_header(); 
?>

<div style="background: #f4f7f6; padding: 60px 0; min-height: 80vh;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 350px; gap: 40px; align-items: start;">
        
        <?php while ( have_posts() ) : the_post(); ?>
            
            <div style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <div style="display: flex; gap: 30px; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 30px;">
                    <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 4px solid #f4f4f4;">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: #eef5fa; display: flex; align-items: center; justify-content: center; font-size: 50px; color: #0056b3;">
                                <i class="dashicons dashicons-businessman"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 style="margin: 0 0 10px 0; font-size: 2.2rem;"><?php the_title(); ?></h1>
                        <span style="display: inline-block; background: #eef5fa; color: #0056b3; padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 14px;">Professional Mentor</span>
                    </div>
                </div>
                
                <div style="line-height: 1.8; color: #444; font-size: 1.1rem;">
                    <h3 style="margin-bottom: 15px;">About the Mentor</h3>
                    <?php the_content(); ?>
                </div>
            </div>

            <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); position: sticky; top: 100px;">
                <h3 style="margin: 0 0 20px 0; text-align: center;">Book a Session</h3>
                <p style="color: #666; font-size: 14px; text-align: center; margin-bottom: 25px;">Schedule a 1-on-1 private hospitality consultation.</p>
                
                <a href="mailto:?subject=Booking Inquiry for <?php the_title_attribute(); ?>" style="display: block; width: 100%; text-align: center; background: #0056b3; color: #fff; padding: 15px 0; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px;">
                    Request Booking
                </a>
                
                <ul style="list-style: none; padding: 0; margin: 25px 0 0 0; color: #555; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px;">
                    <li style="margin-bottom: 10px;"><i class="dashicons dashicons-video-alt3" style="color: #0056b3; margin-right: 8px;"></i> Online Video Call</li>
                    <li style="margin-bottom: 10px;"><i class="dashicons dashicons-clock" style="color: #0056b3; margin-right: 8px;"></i> Flexible Hours</li>
                </ul>
            </div>

        <?php endwhile; ?>
        
    </div>
</div>

<?php get_footer(); ?>