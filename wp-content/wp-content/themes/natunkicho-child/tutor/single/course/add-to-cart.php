<?php
/**
 * Override: Single Course Enroll Button
 * Hijacks the button if an affiliate link is present.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$course_id     = get_the_ID();
$external_link = get_post_meta( $course_id, '_nk_external_link', true );
$button_text   = get_post_meta( $course_id, '_nk_button_text', true ) ?: 'Go to Partner Site';

if ( ! empty( $external_link ) ) {
    // 1. THIS IS AN AFFILIATE COURSE - Display the custom redirect button
    ?>
    <div class="tutor-course-add-to-cart-box" style="margin-bottom: 20px;">
        <a href="<?php echo esc_url( $external_link ); ?>" target="_blank" rel="noopener noreferrer" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block" style="width: 100%; text-align: center;">
            <?php echo esc_html( $button_text ); ?>
        </a>
        <p style="text-align: center; font-size: 13px; margin-top: 10px; color: #666;">
            <i class="tutor-icon-external-link"></i> You will be redirected to our partner platform.
        </p>
    </div>
    <?php
} else {
    // 2. THIS IS AN INTERNAL COURSE - Load the default Tutor LMS logic
    include tutor()->path . 'templates/single/course/add-to-cart.php';
}
?>