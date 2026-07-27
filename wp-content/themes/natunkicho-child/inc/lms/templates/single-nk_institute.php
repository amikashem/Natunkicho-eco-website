<?php
/**
 * Single Template for Institutes / Partners
 */
get_header(); 
?>

<?php while ( have_posts() ) : the_post(); ?>

<div style="background: #222; padding: 80px 0 40px 0; text-align: center; color: #fff;">
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        <div style="width: 120px; height: 120px; margin: 0 auto 20px auto; border-radius: 12px; background: #fff; overflow: hidden; padding: 10px;">
            <?php if ( has_post_thumbnail() ) : ?>
                <img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>" style="width: 100%; height: 100%; object-fit: contain;">
            <?php endif; ?>
        </div>
        <h1 style="margin: 0 0 10px 0; font-size: 2.5rem;"><?php the_title(); ?></h1>
        <p style="font-size: 1.2rem; color: #ccc;">Verified Hospitality Education Partner</p>
    </div>
</div>

<div style="background: #f9f9f9; padding: 60px 0; min-height: 50vh;">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 20px;">
        <div style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); line-height: 1.8; color: #444; font-size: 1.1rem;">
            <h2 style="margin-bottom: 20px; color: #222; border-bottom: 2px solid #eee; padding-bottom: 15px;">Overview</h2>
            <?php the_content(); ?>
        </div>
    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>