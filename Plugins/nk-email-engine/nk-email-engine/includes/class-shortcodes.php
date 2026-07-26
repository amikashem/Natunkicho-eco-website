<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Shortcodes {

    public static function init() {
        add_shortcode( 'nk_subscribe_form', array( __CLASS__, 'render_subscribe_form' ) );
        add_shortcode( 'nk_email_preferences', array( __CLASS__, 'render_preference_center' ) ); // 10X Added Shortcode
        add_action( 'template_redirect', array( __CLASS__, 'handle_links_and_submission' ) );
    }

    public static function handle_links_and_submission() {
        if ( isset( $_GET['nk_verify'] ) ) {
            NK_Subscriber_Manager::verify( sanitize_text_field( wp_unslash( $_GET['nk_verify'] ) ) );
        }

        if ( isset( $_GET['nk_unsubscribe'] ) ) {
            NK_Subscriber_Manager::unsubscribe( sanitize_text_field( wp_unslash( $_GET['nk_unsubscribe'] ) ) );
        }

        // Handle Public Subscribe Form
        if ( isset( $_POST['nk_subscribe_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nk_subscribe_nonce'] ) ), 'nk_subscribe_action' ) ) {
            $email      = isset( $_POST['nk_email'] ) ? sanitize_email( wp_unslash( $_POST['nk_email'] ) ) : '';
            $name       = isset( $_POST['nk_name'] ) ? sanitize_text_field( wp_unslash( $_POST['nk_name'] ) ) : '';
            $interest   = isset( $_POST['nk_interest'] ) ? sanitize_text_field( wp_unslash( $_POST['nk_interest'] ) ) : '';
            $double_opt = isset( $_POST['nk_double_opt_in'] ) ? (bool) $_POST['nk_double_opt_in'] : true;

            $result = NK_Subscriber_Manager::subscribe( $email, $name, $interest, 'website_form', $double_opt );
            setcookie( 'nk_subscribe_result', rawurlencode( $result['message'] ), time() + 30, COOKIEPATH ?: '/' );
            wp_safe_redirect( remove_query_arg( array( 'nk_verify', 'nk_unsubscribe' ), wp_get_referer() ?: home_url( '/' ) ) );
            exit;
        }

        // 10X UPGRADE: Handle Dashboard User Preferences Update
        if ( isset( $_POST['nk_prefs_nonce'] ) && wp_verify_nonce( $_POST['nk_prefs_nonce'], 'nk_prefs_action' ) && is_user_logged_in() ) {
            global $wpdb;
            $user = wp_get_current_user();
            $status = sanitize_text_field($_POST['email_status']);
            $table_name = NK_Database::table( 'subscribers' );
            
            $wpdb->update( $table_name, array( 'status' => $status, 'updated_at' => current_time('mysql') ), array( 'email' => $user->user_email ) );
            
            setcookie( 'nk_subscribe_result', rawurlencode( 'Your email preferences have been successfully updated.' ), time() + 30, COOKIEPATH ?: '/' );
            wp_safe_redirect( wp_get_referer() );
            exit;
        }
    }

    // 10X UPGRADE: Dashboard User Interface
    public static function render_preference_center() {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to manage your email preferences.</p>';
        }

        global $wpdb;
        $user = wp_get_current_user();
        $table_name = NK_Database::table( 'subscribers' );
        
        $contact = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE email = %s", $user->user_email ) );
        $is_active = ($contact && $contact->status === 'active') ? true : false;

        $message = '';
        if ( isset( $_COOKIE['nk_subscribe_result'] ) ) {
            $message = sanitize_text_field( rawurldecode( wp_unslash( $_COOKIE['nk_subscribe_result'] ) ) );
            setcookie( 'nk_subscribe_result', '', time() - 3600, COOKIEPATH ?: '/' );
        }

        ob_start();
        ?>
        <div class="nk-email-preferences-box" style="background:#fff; padding:25px; border-radius:12px; border:1px solid #e2e8f0; max-width:600px;">
            <h3 style="margin-top:0; color:#0f172a; font-size: 20px;">Communication Preferences</h3>
            <p style="color:#64748b; font-size:14px;">Control how NatunKicho contacts you regarding jobs, talent, and hospitality news.</p>
            
            <?php if ( $message ) : ?>
                <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:6px; margin-bottom:15px; font-weight:bold;"><?php echo esc_html( $message ); ?></div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field( 'nk_prefs_action', 'nk_prefs_nonce' ); ?>
                <div style="margin: 20px 0; padding: 15px; background: <?php echo $is_active ? '#f0fdf4' : '#fef2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo $is_active ? '#bbf7d0' : '#fecaca'; ?>;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:bold; cursor:pointer; margin-bottom: 10px; color: #166534;">
                        <input type="radio" name="email_status" value="active" <?php checked($is_active, true); ?>>
                        ✅ Keep me subscribed to Smart Alerts & Updates
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; font-weight:bold; cursor:pointer; color:#ef4444;">
                        <input type="radio" name="email_status" value="unsubscribed" <?php checked($is_active, false); ?>>
                        🔕 Unsubscribe me from all marketing and job alerts
                    </label>
                </div>
                <button type="submit" style="background:#0A66C2; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">Save Preferences</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_subscribe_form( $atts ) {
        // ... (Keep your existing render_subscribe_form code exactly the same here) ...
        $atts = shortcode_atts( array( 'double_opt_in' => 'yes' ), $atts, 'nk_subscribe_form' );
        $message = '';
        if ( isset( $_COOKIE['nk_subscribe_result'] ) ) {
            $message = sanitize_text_field( rawurldecode( wp_unslash( $_COOKIE['nk_subscribe_result'] ) ) );
            setcookie( 'nk_subscribe_result', '', time() - 3600, COOKIEPATH ?: '/' );
        }
        ob_start();
        ?>
        <div class="nk-subscribe-form-wrapper">
            <?php if ( $message ) : ?>
                <p class="nk-subscribe-message"><?php echo esc_html( $message ); ?></p>
            <?php endif; ?>
            <form method="post" class="nk-subscribe-form">
                <?php wp_nonce_field( 'nk_subscribe_action', 'nk_subscribe_nonce' ); ?>
                <input type="hidden" name="nk_double_opt_in" value="<?php echo 'yes' === $atts['double_opt_in'] ? '1' : '0'; ?>">
                <p><label for="nk_name">Name</label><input type="text" id="nk_name" name="nk_name" required></p>
                <p><label for="nk_email">Email</label><input type="email" id="nk_email" name="nk_email" required></p>
                <p><label for="nk_interest">Interest Category</label>
                    <select id="nk_interest" name="nk_interest">
                        <option value="jobs">Jobs</option>
                        <option value="blogs">Hospitality Blogs</option>
                        <option value="recipes">Recipes</option>
                        <option value="training">Training Content</option>
                        <option value="all">Everything</option>
                    </select>
                </p>
                <p><button type="submit">Subscribe</button></p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}