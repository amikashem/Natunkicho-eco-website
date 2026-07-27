<?php
/**
 * Template Part: Individual Course Card (Updated for Affiliate Links)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$course_id = get_the_ID();

// Get the Affiliate Data we just created
$external_link = get_post_meta( $course_id, '_nk_external_link', true );
$button_text   = get_post_meta( $course_id, '_nk_button_text', true ) ?: 'Go to Partner Site';

// Determine Button Logic
if ( ! empty( $external_link ) ) {
    $button_url = esc_url( $external_link );
    $button_target = 'target="_blank" rel="noopener noreferrer"';
} else {
    $button_url = get_the_permalink();
    $button_target = '';
    $button_text = 'Enroll Now';
}
?>

<div class="nk-course-card">
    <div class="nk-course-thumbnail" style="background: url('<?php echo esc_url( get_the_post_thumbnail_url( $course_id, 'medium_large' ) ); ?>') center/cover;">
        <?php if ( ! empty( $external_link ) ) : ?>
            <span class="nk-badge nk-badge-affiliate">Partner Course</span>
        <?php endif; ?>
    </div>
    
    <div class="nk-course-content">
        <h3 class="nk-course-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        
        <div class="nk-course-footer">
            <a href="<?php echo $button_url; ?>" <?php echo $button_target; ?> class="nk-btn nk-btn-primary-sm">
                <?php echo esc_html( $button_text ); ?>
            </a>
        </div>
    </div>
</div>