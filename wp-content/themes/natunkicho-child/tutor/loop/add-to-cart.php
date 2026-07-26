<?php
/**
 * Override: Course Grid Card Button
 * Hijacks the button on the marketplace grid if an affiliate link is present.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$course_id     = get_the_ID();
$external_link = get_post_meta( $course_id, '_nk_external_link', true );
$button_text   = get_post_meta( $course_id, '_nk_button_text', true ) ?: 'Go to Partner Site';

if ( ! empty( $external_link ) ) {
    // Affiliate Course Button
    ?>
    <div class="tutor-loop-cart-btn-wrap">
        <a href="<?php echo esc_url( $external_link ); ?>" target="_blank" rel="noopener noreferrer" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
            <?php echo esc_html( $button_text ); ?>
        </a>
    </div>
    <?php
} else {
    // Internal Course Button (Default)
    include tutor()->path . 'templates/loop/add-to-cart.php';
}
?>