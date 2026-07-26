<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * BRIDGE: Custom Login/Registration API & Platform Security
 * Path: inc/auth/auth-functions.php
 * =========================================================================
 */

function nk_custom_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('employer', $user->roles) || in_array('premium_employer', $user->roles) || in_array('job_seeker', $user->roles) || in_array('premium_job_seeker', $user->roles)) {
            return home_url('/dashboard/');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'nk_custom_login_redirect', 10, 3);
add_filter('um_login_redirect_url', 'nk_custom_login_redirect', 10, 3); 

add_action('admin_init', 'nk_block_wp_admin_access');
function nk_block_wp_admin_access() {
    if (is_admin() && !wp_doing_ajax() && !current_user_can('administrator')) {
        wp_redirect(home_url('/dashboard/'));
        exit;
    }
}

add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
    $custom_login_url = site_url( '/login/' ); 
    if ( ! empty( $redirect ) ) { $custom_login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom_login_url ); }
    return $custom_login_url;
}, 10, 3 );
add_filter( 'job_manager_job_applications_login_url', function($url) { return site_url('/login/'); });
add_filter( 'submit_job_form_login_url', function($url) { return site_url('/login/?redirect_to=' . urlencode(site_url('/post-job/')) . '&type=employer'); });

// Turnstile Helper
function nk_verify_turnstile($response_token) {
    if (!defined('NK_TURNSTILE_SECRET') || NK_TURNSTILE_SECRET === '') return true; // Pass if no key
    $response = wp_remote_post("https://challenges.cloudflare.com/turnstile/v0/siteverify", [
        'body' => [ 'secret' => NK_TURNSTILE_SECRET, 'response' => $response_token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ]
    ]);
    if (is_wp_error($response)) return false;
    $body = json_decode(wp_remote_retrieve_body($response));
    return $body->success;
}

// 4. Custom AJAX Registration Handler
function nk_custom_register_ajax() {
    check_ajax_referer('nk_auth_nonce', 'security');

    // ðŸ”´ 1. Cloudflare Turnstile Bot Check
    $turnstile_res = isset($_POST['cf-turnstile-response']) ? sanitize_text_field($_POST['cf-turnstile-response']) : '';
    if (!nk_verify_turnstile($turnstile_res)) {
        wp_send_json_error('Security check failed. Please verify you are human.');
    }

    $username = sanitize_user($_POST['username']);
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $role     = sanitize_text_field($_POST['role']); 

    if (empty($username) || empty($email) || empty($password)) { wp_send_json_error('Please fill in all required fields.'); }
    if (email_exists($email)) { wp_send_json_error('This email is already registered. Please login or reset your password.'); }
    if (username_exists($username)) { wp_send_json_error('This username is already taken. Please try another one.'); }
    if (strlen($password) < 8) { wp_send_json_error('For your security, your password must be at least 8 characters long.'); }

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) { wp_send_json_error($user_id->get_error_message()); }

    $user = new WP_User($user_id);
    $user->set_role($role);
    update_user_meta($user_id, 'nk_user_tier', 'free');
    
    // ðŸ”´ 2. Require Email Verification setup
    $verify_token = wp_generate_password(20, false);
    update_user_meta($user_id, 'nk_verify_token', $verify_token);
    update_user_meta($user_id, 'nk_account_status', 'unverified');

    if ($role === 'job_seeker') {
        if (isset($_POST['candidate_profession'])) update_user_meta($user_id, 'nk_profession', sanitize_text_field($_POST['candidate_profession']));
        if (isset($_POST['candidate_department'])) update_user_meta($user_id, 'nk_department', sanitize_text_field($_POST['candidate_department']));
        if (isset($_POST['nk_alert_frequency'])) update_user_meta($user_id, 'nk_alert_frequency', sanitize_text_field($_POST['nk_alert_frequency']));
    } elseif ($role === 'employer') {
        if (isset($_POST['company_name'])) update_user_meta($user_id, 'nk_company_name', sanitize_text_field($_POST['company_name']));
        if (isset($_POST['company_industry'])) update_user_meta($user_id, 'nk_company_industry', sanitize_text_field($_POST['company_industry']));
    }

    // ðŸ”´ 3. Send Verification Email
    $verify_link = add_query_arg(['verify_token' => $verify_token, 'uid' => $user_id], site_url('/login/'));
    $subject = 'Action Required: Verify Your NatunKicho Account';
    $content = "<p>Hello <strong>" . esc_html($username) . "</strong>,</p>";
    $content .= "<p>Welcome to NatunKicho! To activate your account and access the dashboard, please verify your email address by clicking the button below:</p>";
    $content .= '<a href="' . esc_url($verify_link) . '" style="display:inline-block; background:#0A66C2; color:#fff; padding:12px 24px; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:15px; margin-bottom:15px;">Verify My Email</a>';
    $content .= "<p>If you did not create this account, you can safely ignore this email.</p>";

    if (function_exists('nk_get_branded_email_html')) {
        $final_html = nk_get_branded_email_html($subject, $content);
        add_filter('wp_mail_content_type', 'nk_set_html_mail_content_type');
        wp_mail($email, $subject, $final_html);
        remove_filter('wp_mail_content_type', 'nk_set_html_mail_content_type');
    } else {
        wp_mail($email, $subject, strip_tags($content) . "\n\nLink: " . $verify_link);
    }

    // Redirect to login page with a check_email flag (NO AUTO LOGIN)
    wp_send_json_success([ 
        'message' => 'Account created! Please check your email to verify.', 
        'redirect' => site_url('/login/?check_email=1') 
    ]);
}
add_action('wp_ajax_nopriv_nk_custom_register', 'nk_custom_register_ajax');
add_action('wp_ajax_nk_custom_register', 'nk_custom_register_ajax');  

function nk_smart_auth_menu_shortcode() {
    ob_start();
    if (is_user_logged_in()) {
        ?>
        <div class="nk-smart-auth-menu">
            <div class="nk-dropdown-wrapper">
                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="nk-account-toggle">
                    My Account 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <div class="nk-auth-dropdown">
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>">Dashboard</a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="nk-logout-text">Sign Out</a>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="nk-smart-auth-menu">
            <a href="<?php echo esc_url(home_url('/login/')); ?>" class="nk-header-login-btn">Sign In</a>
            <a href="<?php echo esc_url(home_url('/post-job/')); ?>" class="nk-btn-primary" onclick="localStorage.setItem('nk_intended_role', 'employer');">Post a Job</a>
        </div>
        <?php
    }
    return ob_get_clean();
}
if (!shortcode_exists('nk_smart_auth_menu')) add_shortcode('nk_smart_auth_menu', 'nk_smart_auth_menu_shortcode');

// 5. Custom AJAX Login Handler
function nk_custom_login_ajax() {
    check_ajax_referer('nk_auth_nonce', 'security');
    
    // Turnstile Check on Login
    $turnstile_res = isset($_POST['cf-turnstile-response']) ? sanitize_text_field($_POST['cf-turnstile-response']) : '';
    if (!nk_verify_turnstile($turnstile_res)) {
        wp_send_json_error('Security check failed. Please verify you are human.');
    }

    $username = sanitize_user($_POST['username']);
    $user = get_user_by('login', $username);
    if (!$user && is_email($username)) { $user = get_user_by('email', $username); }

    if ($user) {
        // ðŸ”´ Prevent Login if Unverified
        $status = get_user_meta($user->ID, 'nk_account_status', true);
        if ($status === 'unverified') {
            wp_send_json_error('Your email has not been verified. Please check your inbox for the activation link.');
        }
    }

    $creds = array( 'user_login' => $username, 'user_password' => $_POST['password'], 'remember' => isset($_POST['remember']) ? true : false );
    $signon = wp_signon($creds, is_ssl() ? true : false);

    if (is_wp_error($signon)) wp_send_json_error('Invalid username or password. Please try again.');

    $redirect_url = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/dashboard/');
    wp_send_json_success([ 'message' => 'Login successful! Redirecting...', 'redirect' => $redirect_url ]);
}
add_action('wp_ajax_nopriv_nk_custom_login', 'nk_custom_login_ajax');
add_action('wp_ajax_nk_custom_login', 'nk_custom_login_ajax');

add_filter( 'job_manager_default_registration_role', function( $role ) { return 'employer'; });
add_action( 'job_manager_user_registered', function( $user_id ) {
    $user = new WP_User($user_id);
    $user->set_role('employer');
    update_user_meta($user_id, 'nk_user_tier', 'free');
});

add_action('wp_footer', function() {
    if (!is_user_logged_in()) {
        ?>
        <script>
        document.addEventListener('click', function(e) {
            let target = e.target.closest('a');
            if (target && target.href && (target.href.includes('post-job') || target.href.includes('type=employer') || target.href.includes('manage-jobs'))) {
                localStorage.setItem('nk_intended_role', 'employer');
            }
        });
        </script>
        <?php
    }
});

/**
 * =========================================================================
 * 10X EMAIL VERIFICATION LISTENER & UI NOTIFIER
 * =========================================================================
 */
add_action('init', 'nk_process_email_verification');
function nk_process_email_verification() {
    // Only run if the exact parameters exist in the URL
    if (isset($_GET['verify_token']) && isset($_GET['uid'])) {
        
        $uid = intval($_GET['uid']);
        $token = sanitize_text_field($_GET['verify_token']);
        
        // Get the stored token from the user's database profile
        $saved_token = get_user_meta($uid, 'nk_verify_token', true);
        $current_status = get_user_meta($uid, 'nk_account_status', true);

        // If they are already verified, redirect them to login safely
        if ($current_status === 'verified') {
            wp_redirect(site_url('/login/?verified=already'));
            exit;
        }

        // Verify the token
        if (!empty($saved_token) && $saved_token === $token) {
            // 1. Mark account as verified!
            update_user_meta($uid, 'nk_account_status', 'verified');
            
            // 2. Destroy the token for maximum security (One-time use)
            delete_user_meta($uid, 'nk_verify_token');
            
            // 3. Redirect to login page with success flag
            wp_redirect(site_url('/login/?verified=true'));
            exit;
        } else {
            // Invalid or expired token
            wp_redirect(site_url('/login/?verified=false'));
            exit;
        }
    }
}

/**
 * INJECT BEAUTIFUL UI MESSAGES ON THE LOGIN PAGE
 */
add_action('wp_footer', 'nk_verification_ui_notices');
function nk_verification_ui_notices() {
    // Determine the message based on the URL parameter
    $message = '';
    $color = '';

    if (isset($_GET['check_email'])) {
        $message = 'Account created! Please check your email inbox to verify your account before logging in.';
        $color = '#f59e0b'; // Warning Orange
    } elseif (isset($_GET['verified'])) {
        if ($_GET['verified'] === 'true') {
            $message = '🎉 Email verified successfully! You can now log in to your dashboard.';
            $color = '#10b981'; // Success Green
        } elseif ($_GET['verified'] === 'already') {
            $message = 'Your email is already verified. Please log in.';
            $color = '#0A66C2'; // Brand Blue
        } elseif ($_GET['verified'] === 'false') {
            $message = 'Invalid or expired verification link. Please request a new one or contact support.';
            $color = '#ef4444'; // Error Red
        }
    }

    // If we have a message, inject it dynamically above the login form
    if (!empty($message)) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Create the notification banner HTML
            var noticeHtml = '<div style="background: <?php echo $color; ?>15; border: 1px solid <?php echo $color; ?>50; color: <?php echo $color; ?>; padding: 16px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-weight: 700; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">' + '<?php echo $message; ?>' + '</div>';
            
            // Find the login box to inject the message above
            var $loginContainer = $('.nk-dash-card').first();
            if ($loginContainer.length === 0) {
                // Fallback if class is different on login page
                $loginContainer = $('form').first().parent(); 
            }
            
            $loginContainer.prepend(noticeHtml);
            
            // 10X UX TRICK: Clean up the URL instantly so it looks professional (removes the ?verified=true part)
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        });
        </script>
        <?php
    }
}