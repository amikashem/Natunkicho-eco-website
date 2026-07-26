<?php
/**
 * Override: Single Course Enrollment Box (For Free/Non-WooCommerce Courses)
 * Hijacks the button if an affiliate link is present.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$course_id     = get_the_ID();
$external_link = get_post_meta( $course_id, '_nk_external_link', true );
$button_text   = get_post_meta( $course_id, '_nk_button_text', true ) ?: 'Go to Partner Site';

if ( ! empty( $external_link ) ) {
    // 1. THIS IS AN AFFILIATE COURSE - Force the redirect button
    ?>
    <div class="tutor-course-enrolment-box" style="margin-bottom: 20px; padding: 20px; border: 1px solid #e1e1e1; border-radius: 8px; text-align: center;">
        <h4 style="margin-bottom: 15px;">Enroll in this Course</h4>
        <a href="<?php echo esc_url( $external_link ); ?>" target="_blank" rel="noopener noreferrer" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block" style="width: 100%; display: block;">
            <?php echo esc_html( $button_text ); ?>
        </a>
        <p style="font-size: 13px; margin-top: 10px; color: #666;">
            <i class="tutor-icon-external-link"></i> You will be redirected to our partner platform.
        </p>
    </div>
    <?php
} else {
    // 2. THIS IS AN INTERNAL COURSE - Load the default Tutor LMS enrollment
    include tutor()->path . 'templates/single/course/enrolment-box.php';
}
?>