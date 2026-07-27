<?php
/**
 * Bulletproof External Link Meta Box
 * Bypasses the Tutor LMS React Builder and places the box at the bottom of the page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Force the Meta Box onto the 'courses' screen, below the React builder
add_action( 'add_meta_boxes', 'nk_force_external_link_metabox', 999 );
function nk_force_external_link_metabox() {
    add_meta_box(
        'nk_bulletproof_external_link',
        '🔗 External / Affiliate Course Link (Natunkicho Settings)', 
        'nk_render_bulletproof_metabox',
        'courses',
        'advanced', // 'advanced' context forces it below the main editor
        'high'
    );
}

// 2. Render the Box
function nk_render_bulletproof_metabox( $post ) {
    $external_link = get_post_meta( $post->ID, '_nk_external_link', true );
    $button_text   = get_post_meta( $post->ID, '_nk_button_text', true ) ?: 'Go to Partner Site';
    ?>
    <div style="background: #eef5fa; border-left: 4px solid #0056b3; padding: 15px; margin: 10px 0;">
        <h4 style="margin-top: 0;">Is this an external affiliate course?</h4>
        <p>If yes, paste the link below. If you leave it blank, the course will be sold internally.</p>
        
        <p>
            <label style="font-weight: bold;">Affiliate URL:</label><br>
            <input type="url" name="nk_external_link" value="<?php echo esc_attr( $external_link ); ?>" style="width: 100%; max-width: 600px; padding: 8px;" placeholder="https://www.coursera.org/...">
        </p>
        <p>
            <label style="font-weight: bold;">Button Text:</label><br>
            <input type="text" name="nk_button_text" value="<?php echo esc_attr( $button_text ); ?>" style="width: 100%; max-width: 300px; padding: 8px;">
        </p>
    </div>
    <?php
}

// 3. Save the Data
add_action( 'save_post_courses', 'nk_save_bulletproof_metabox' );
function nk_save_bulletproof_metabox( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( isset( $_POST['nk_external_link'] ) ) {
        update_post_meta( $post_id, '_nk_external_link', sanitize_url( $_POST['nk_external_link'] ) );
    }
    if ( isset( $_POST['nk_button_text'] ) ) {
        update_post_meta( $post_id, '_nk_button_text', sanitize_text_field( $_POST['nk_button_text'] ) );
    }
}