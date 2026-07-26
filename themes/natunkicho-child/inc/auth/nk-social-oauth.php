<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * SaaS CORE NATIVE SOCIAL OAUTH 2.0 ENGINE
 * Path: inc/auth/nk-social-oauth.php
 * Supports: Google, Facebook, LinkedIn (No Premium Plugins Needed)
 * =========================================================================
 */

// --- DEVELOPER CONFIGURATION (Replace with your live console credentials) ---
define( 'NK_GOOGLE_CLIENT_ID',     '' );
define( 'NK_GOOGLE_CLIENT_SECRET', '' );

define( 'NK_FACEBOOK_APP_ID',      '' );
define( 'NK_FACEBOOK_APP_SECRET',  '2' );

define( 'NK_LINKEDIN_CLIENT_ID',   '' );
define( '' );

// Core Redirect Callback Gateway
define( 'NK_SOCIAL_REDIRECT_URL',  'https://natunkicho.com/?nk_social_callback=1' );


/**
 * 1. Catch Social Redirects on WordPress Initialization Hook
 */
function nk_social_oauth_handler() {
    if ( ! isset( $_GET['nk_social_callback'] ) && ! isset( $_GET['nk_social_init'] ) ) {
        return;
    }

    // A. INITIALIZATION PATHWAY (User clicks a custom login button)
    if ( isset( $_GET['nk_social_init'] ) ) {
        $provider = sanitize_text_field( $_GET['nk_social_init'] );
        $state    = wp_create_nonce( 'nk_social_auth_state' );
        
        switch ( $provider ) {
            case 'google':
                $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                    'client_id'     => NK_GOOGLE_CLIENT_ID,
                    'redirect_uri'  => NK_SOCIAL_REDIRECT_URL,
                    'response_type' => 'code',
                    'scope'         => 'openid email profile',
                    'state'         => 'google_' . $state
                ]);
                break;

            case 'facebook':
                $auth_url = 'https://www.facebook.com/v14.0/dialog/oauth?' . http_build_query([
                    'client_id'    => NK_FACEBOOK_APP_ID,
                    'redirect_uri' => NK_SOCIAL_REDIRECT_URL,
                    'scope'        => 'email,public_profile',
                    'state'        => 'facebook_' . $state
                ]);
                break;

            case 'linkedin':
                $auth_url = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
                    'response_type' => 'code',
                    'client_id'     => NK_LINKEDIN_CLIENT_ID,
                    'redirect_uri'  => NK_SOCIAL_REDIRECT_URL,
                    'scope'         => 'openid profile email',
                    'state'         => 'linkedin_' . $state
                ]);
                break;
                
            default:
                wp_die( 'Invalid social login provider selected.' );
        }
        
        wp_redirect( $auth_url );
        exit;
    }

    // B. CALLBACK PATHWAY (Provider returns user with authentication token code)
    if ( isset( $_GET['nk_social_callback'] ) && isset( $_GET['code'] ) ) {
        $code       = sanitize_text_field( $_GET['code'] );
        $state_data = explode( '_', sanitize_text_field( $_GET['state'] ) );
        $provider   = $state_data[0];
        $nonce      = isset( $state_data[1] ) ? $state_data[1] : '';

        if ( ! wp_verify_nonce( $nonce, 'nk_social_auth_state' ) ) {
            wp_die( 'Security verification expired. Please try signing in again.' );
        }

        $user_email = '';
        $first_name = '';
        $last_name  = '';
        $avatar_url = '';

        // Exchange Token Code for User Profiles
        if ( $provider === 'google' ) {
            $token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
                'body' => [
                    'code'          => $code,
                    'client_id'     => NK_GOOGLE_CLIENT_ID,
                    'client_secret' => NK_GOOGLE_CLIENT_SECRET,
                    'redirect_uri'  => NK_SOCIAL_REDIRECT_URL,
                    'grant_type'    => 'authorization_code'
                ]
            ]);
            if ( is_wp_error( $token_response ) ) wp_die( 'Google authentication handshake failed.' );
            $token_data = json_decode( wp_remote_retrieve_body( $token_response ), true );
            
            if ( isset( $token_data['access_token'] ) ) {
                $user_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $token_data['access_token'] );
                $user_info = json_decode( wp_remote_retrieve_body( $user_response ), true );
                $user_email = isset( $user_info['email'] ) ? sanitize_email( $user_info['email'] ) : '';
                $first_name = isset( $user_info['given_name'] ) ? sanitize_text_field( $user_info['given_name'] ) : '';
                $last_name  = isset( $user_info['family_name'] ) ? sanitize_text_field( $user_info['family_name'] ) : '';
                $avatar_url = isset( $user_info['picture'] ) ? esc_url_raw( $user_info['picture'] ) : '';
            }

        } elseif ( $provider === 'facebook' ) {
            $token_response = wp_remote_get( 'https://graph.facebook.com/v14.0/oauth/access_token?' . http_build_query([
                'client_id'     => NK_FACEBOOK_APP_ID,
                'redirect_uri'  => NK_SOCIAL_REDIRECT_URL,
                'client_secret' => NK_FACEBOOK_APP_SECRET,
                'code'          => $code
            ]));
            $token_data = json_decode( wp_remote_retrieve_body( $token_response ), true );
            
            if ( isset( $token_data['access_token'] ) ) {
                $user_response = wp_remote_get( 'https://graph.facebook.com/me?fields=id,first_name,last_name,email,picture.type(large)&access_token=' . $token_data['access_token'] );
                $user_info = json_decode( wp_remote_retrieve_body( $user_response ), true );
                $user_email = isset( $user_info['email'] ) ? sanitize_email( $user_info['email'] ) : '';
                $first_name = isset( $user_info['first_name'] ) ? sanitize_text_field( $user_info['first_name'] ) : '';
                $last_name  = isset( $user_info['last_name'] ) ? sanitize_text_field( $user_info['last_name'] ) : '';
                $avatar_url = isset( $user_info['picture']['data']['url'] ) ? esc_url_raw( $user_info['picture']['data']['url'] ) : '';
            }

        } elseif ( $provider === 'linkedin' ) {
            $token_response = wp_remote_post( 'https://www.linkedin.com/oauth/v2/accessToken', [
                'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
                'body'    => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'redirect_uri'  => NK_SOCIAL_REDIRECT_URL,
                    'client_id'     => NK_LINKEDIN_CLIENT_ID,
                    'client_secret' => NK_LINKEDIN_CLIENT_SECRET,
                ]
            ]);
            $token_data = json_decode( wp_remote_retrieve_body( $token_response ), true );
            
            if ( isset( $token_data['access_token'] ) ) {
                $user_response = wp_remote_get( 'https://api.linkedin.com/v2/userinfo', [
                    'headers' => [ 'Authorization' => 'Bearer ' . $token_data['access_token'] ]
                ]);
                $user_info  = json_decode( wp_remote_retrieve_body( $user_response ), true );
                $user_email = isset( $user_info['email'] ) ? sanitize_email( $user_info['email'] ) : '';
                $first_name = isset( $user_info['given_name'] ) ? sanitize_text_field( $user_info['given_name'] ) : '';
                $last_name  = isset( $user_info['family_name'] ) ? sanitize_text_field( $user_info['family_name'] ) : '';
                $avatar_url = isset( $user_info['picture'] ) ? esc_url_raw( $user_info['picture'] ) : '';
            }
        }

        // C. SITE PROCESSING GATEWAY (Sign in or Register user dynamically)
        if ( ! empty( $user_email ) ) {
            $user = get_user_by( 'email', $user_email );

            if ( ! $user ) {
                // User doesn't exist, build account programmatically as Candidate (job_seeker)
                $username   = explode( '@', $user_email )[0] . rand( 10, 99 );
                $password   = wp_generate_password( 16, true );
                $user_id    = wp_create_user( $username, $password, $user_email );
                
                if ( ! is_wp_error( $user_id ) ) {
                    wp_update_user([
                        'ID'           => $user_id,
                        'first_name'   => $first_name,
                        'last_name'    => $last_name,
                        'display_name' => $first_name . ' ' . $last_name,
                        'role'         => 'job_seeker' // Forces role mapping instantly
                    ]);
                    
                    // Hook custom avatar url to meta
                    if ( ! empty( $avatar_url ) ) {
                        update_user_meta( $user_id, 'nk_photo_url', $avatar_url );
                    }
                    $user = get_user_by( 'id', $user_id );
                } else {
                    wp_die( 'Account provisioning failed. Contact platform tech support.' );
                }
            }

            // Secure Login Execution
            wp_clear_auth_cookie();
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            do_action( 'wp_login', $user->user_login, $user );

            // Forward securely into our active Router Dashboard
            wp_redirect( home_url( '/dashboard/' ) );
            exit;
        }
        
        wp_die( 'Authentication failed to extract email coordinates from profile access token.' );
    }
}
add_action( 'init', 'nk_social_oauth_handler' );


/**
 * 2. Premium Layout Shortcode: [nk_custom_social_login]
 */
function nk_custom_social_login_shortcode() {
    ob_start();
    ?>
    <div class="nk-social-login-wrapper">
        <a href="<?php echo esc_url( home_url( '/?nk_social_init=google' ) ); ?>" class="nk-social-btn nk-google-btn">
            <span class="nk-social-icon">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
            </span>
            <span class="nk-social-text">Continue with Google</span>
        </a>

        <a href="<?php echo esc_url( home_url( '/?nk_social_init=linkedin' ) ); ?>" class="nk-social-btn nk-linkedin-btn">
            <span class="nk-social-icon">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn">
            </span>
            <span class="nk-social-text">Continue with LinkedIn</span>
        </a>

        <?php /*
        <a href="<?php echo esc_url( home_url( '/?nk_social_init=facebook' ) ); ?>" class="nk-social-btn nk-facebook-btn">
            <span class="nk-social-icon">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b9/2023_Facebook_icon.svg" alt="Facebook">
            </span>
            <span class="nk-social-text">Continue with Facebook</span>
        </a> */ ?> 
    </div>

    <style>
    .nk-social-login-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
        margin-bottom: 20px;
    }
    .nk-social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 52px;
        width: 100%;
        border-radius: 12px;
        text-decoration: none !important;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid transparent;
        position: relative;
    }
    .nk-social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .nk-social-icon {
        position: absolute;
        left: 20px;
        display: flex;
        align-items: center;
    }
    .nk-social-icon img {
        width: 22px;
        height: 22px;
        object-fit: contain;
    }
    .nk-social-text {
        color: inherit;
    }
    /* Provider Variations */
    .nk-google-btn {
        background: #ffffff !important;
        color: #3c4043 !important;
        border: 1px solid #dadce0 !important;
    }
    .nk-google-btn:hover { background: #f8f9fa !important; }
    
    .nk-linkedin-btn {
        background: #0077b5 !important;
        color: #ffffff !important;
    }
    .nk-linkedin-btn:hover { background: #04669b !important; }

    .nk-facebook-btn {
        background: #1877f2 !important;
        color: #ffffff !important;
    }
    .nk-facebook-btn:hover { background: #0d65d9 !important; }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_custom_social_login', 'nk_custom_social_login_shortcode' );