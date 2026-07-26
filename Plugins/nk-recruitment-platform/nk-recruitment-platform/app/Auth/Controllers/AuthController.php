<?php

declare(strict_types=1);

namespace NKRecruitment\Auth\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

class AuthController
{
    // =========================================================================
    // 🛑 ATTENTION: ADD YOUR API KEYS HERE TO ENABLE SOCIAL LOGIN!
    // =========================================================================
    
    // Google Keys (From Google Cloud Console)
    private $google_client_id = 'XXXXXXXXXXXXXX';
    private $google_client_secret = 'XXXXXXXXXXXX';
    
    // LinkedIn Keys (From LinkedIn Developer Portal)
    private $linkedin_client_id = 'XXXXXXXX';
    private $linkedin_client_secret = 'XXXXXXXXXXXXXXX';

    // Facebook Keys (From Meta for Developers)
    private $facebook_client_id = 'XXXXX';
    private $facebook_client_secret = 'XXXXXXXXXXXXX';

    public function register(): void
    {
        add_action('init', [$this, 'setupRoles']);

        add_shortcode('nk_login', [$this, 'renderLoginForm']);
        add_shortcode('nk_register', [$this, 'renderRegisterForm']);
        
        add_action('init', [$this, 'handleStandardLogin']);
        add_action('admin_post_nkrp_auth_action', [$this, 'handleStandardRegister']);
        add_action('admin_post_nopriv_nkrp_auth_action', [$this, 'handleStandardRegister']);
        
        add_action('init', [$this, 'handleSocialLoginRedirects']);
        add_action('init', [$this, 'handleEmailVerification']);
    }

    public function setupRoles(): void
    {
        if (!get_role('nkrp_employer')) add_role('nkrp_employer', __('Employer', 'nk-recruitment'), ['read' => true]);
        if (!get_role('nkrp_candidate')) add_role('nkrp_candidate', __('Candidate', 'nk-recruitment'), ['read' => true]);
    }

    public function renderLoginForm(): string
    {
        if (is_user_logged_in()) return $this->redirectLoggedInUser();
        ob_start();
        $file = NKRP_PLUGIN_PATH . 'app/Auth/Views/login-form.php';
        if (file_exists($file)) require $file;
        return ob_get_clean();
    }

    public function renderRegisterForm(): string
    {
        if (is_user_logged_in()) return $this->redirectLoggedInUser();
        ob_start();
        $file = NKRP_PLUGIN_PATH . 'app/Auth/Views/register-form.php';
        if (file_exists($file)) require $file;
        return ob_get_clean();
    }

    private function redirectLoggedInUser(string $query_args = ''): string
    {
        $user = wp_get_current_user();
        if (in_array('nkrp_employer', (array) $user->roles)) {
            $dash_url = home_url('/employer-dashboard/' . $query_args);
        } elseif (in_array('nkrp_candidate', (array) $user->roles)) {
            $dash_url = home_url('/candidate-dashboard/' . $query_args);
        } else {
            $dash_url = home_url('/' . $query_args);
        }
        return '<script>window.location.href="' . esc_url($dash_url) . '";</script>';
    }

    // =========================================================================
    // 1. STANDARD LOGIN & REGISTER
    // =========================================================================

    public function handleStandardLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_action']) && $_POST['nkrp_action'] === 'standard_login') {
            if (!wp_verify_nonce($_POST['nkrp_login_nonce'], 'nkrp_login_action')) wp_die('Security Check Failed.');

            $creds = [
                'user_login'    => sanitize_user($_POST['log']),
                'user_password' => $_POST['pwd'],
                'remember'      => isset($_POST['rememberme'])
            ];

            $user = wp_signon($creds, is_ssl());

            if (is_wp_error($user)) {
                wp_safe_redirect(add_query_arg('login_error', '1', wp_get_referer()));
                exit;
            }

            if (in_array('nkrp_employer', (array) $user->roles)) {
                wp_safe_redirect(home_url('/employer-dashboard/'));
            } else {
                wp_safe_redirect(home_url('/candidate-dashboard/'));
            }
            exit;
        }
    }

    public function handleStandardRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_action']) && $_POST['nkrp_action'] === 'standard_register') {
            if (!wp_verify_nonce($_POST['nkrp_register_nonce'], 'nkrp_register_action')) wp_die('Security Check Failed.');

            $email = sanitize_email($_POST['email']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $password = $_POST['password'];
            $role = sanitize_text_field($_POST['user_role']);

            if (empty($email) || empty($password) || empty($role)) {
                wp_safe_redirect(add_query_arg('reg_error', urlencode('Please fill in all required fields.'), wp_get_referer())); exit;
            }

            if (email_exists($email)) {
                wp_safe_redirect(add_query_arg('reg_error', urlencode('An account with this email already exists.'), wp_get_referer())); exit;
            }

            $username = explode('@', $email)[0];
            if (username_exists($username)) $username = $username . '_' . wp_rand(1000, 9999);

            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                wp_safe_redirect(add_query_arg('reg_error', urlencode($user_id->get_error_message()), wp_get_referer())); exit;
            }

            wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name]);

            $user = new \WP_User($user_id);
            $user->set_role($role === 'nkrp_employer' ? 'nkrp_employer' : 'nkrp_candidate');

            $verify_token = wp_generate_password(32, false);
            update_user_meta($user_id, '_nkrp_email_verified', '0');
            update_user_meta($user_id, '_nkrp_verification_token', $verify_token);
            
            do_action('nkrp_send_welcome_email', $user_id, $email, $verify_token);

            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user_id, true, is_ssl());

            if ($role === 'nkrp_employer') {
                wp_safe_redirect(home_url('/employer-dashboard/?welcome=1'));
            } else {
                wp_safe_redirect(home_url('/candidate-dashboard/?welcome=1'));
            }
            exit;
        }
    }

    // =========================================================================
    // 2. SOCIAL OAUTH FLOW ENGINE (Google, LinkedIn, Facebook)
    // =========================================================================

    public function handleSocialLoginRedirects(): void
    {
        // Part A: Send user to Social Provider to authorize
        if (isset($_GET['nkrp_social_auth'])) {
            $provider = sanitize_text_field($_GET['nkrp_social_auth']);
            
            if ($provider === 'google') {
                if ($this->google_client_id === 'YOUR_GOOGLE_CLIENT_ID_HERE') wp_die('Google API keys missing in AuthController.php');
                $redirect_uri = urlencode(site_url('?nkrp_social_callback=google'));
                $url = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$this->google_client_id}&redirect_uri={$redirect_uri}&response_type=code&scope=email profile";
                wp_redirect($url); exit;
            }

            if ($provider === 'linkedin') {
                if ($this->linkedin_client_id === 'YOUR_LINKEDIN_CLIENT_ID_HERE') wp_die('LinkedIn API keys missing in AuthController.php');
                $redirect_uri = urlencode(site_url('?nkrp_social_callback=linkedin'));
                $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$this->linkedin_client_id}&redirect_uri={$redirect_uri}&scope=openid profile email";
                wp_redirect($url); exit;
            }

            if ($provider === 'facebook') {
                if ($this->facebook_client_id === 'YOUR_FACEBOOK_APP_ID_HERE') wp_die('Facebook API keys missing in AuthController.php');
                $redirect_uri = urlencode(site_url('?nkrp_social_callback=facebook'));
                $url = "https://www.facebook.com/v18.0/dialog/oauth?client_id={$this->facebook_client_id}&redirect_uri={$redirect_uri}&scope=email,public_profile";
                wp_redirect($url); exit;
            }
        }

        // Part B: Catch the callback, process token, log them in
        if (isset($_GET['nkrp_social_callback'])) {
            $provider = sanitize_text_field($_GET['nkrp_social_callback']);
            
            // If the user cancelled the social login (e.g. denied permissions)
            if (isset($_GET['error'])) $this->socialFail();
            
            $code = sanitize_text_field($_GET['code'] ?? '');
            if (empty($code)) $this->socialFail();

            $redirect_uri = site_url('?nkrp_social_callback=' . $provider);
            $user_data = [];

            if ($provider === 'google') {
                $token_response = wp_remote_post('https://oauth2.googleapis.com/token', [
                    'body' => [
                        'code' => $code, 'client_id' => $this->google_client_id, 'client_secret' => $this->google_client_secret,
                        'redirect_uri' => $redirect_uri, 'grant_type' => 'authorization_code'
                    ]
                ]);
                if (is_wp_error($token_response)) $this->socialFail();
                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                if (empty($token_data['access_token'])) $this->socialFail();

                $profile_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
                    'headers' => ['Authorization' => 'Bearer ' . $token_data['access_token']]
                ]);
                if (is_wp_error($profile_response)) $this->socialFail();
                $profile = json_decode(wp_remote_retrieve_body($profile_response), true);

                if (empty($profile['email'])) $this->socialFail();
                $user_data = ['email' => $profile['email'], 'first_name' => $profile['given_name'] ?? '', 'last_name' => $profile['family_name'] ?? ''];
            }

            if ($provider === 'linkedin') {
                $token_response = wp_remote_post('https://www.linkedin.com/oauth/v2/accessToken', [
                    'body' => [
                        'code' => $code, 'client_id' => $this->linkedin_client_id, 'client_secret' => $this->linkedin_client_secret,
                        'redirect_uri' => $redirect_uri, 'grant_type' => 'authorization_code'
                    ]
                ]);
                if (is_wp_error($token_response)) $this->socialFail();
                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                if (empty($token_data['access_token'])) $this->socialFail();

                $profile_response = wp_remote_get('https://api.linkedin.com/v2/userinfo', [
                    'headers' => ['Authorization' => 'Bearer ' . $token_data['access_token']]
                ]);
                if (is_wp_error($profile_response)) $this->socialFail();
                $profile = json_decode(wp_remote_retrieve_body($profile_response), true);

                if (empty($profile['email'])) $this->socialFail();
                $user_data = ['email' => $profile['email'], 'first_name' => $profile['given_name'] ?? '', 'last_name' => $profile['family_name'] ?? ''];
            }

            if ($provider === 'facebook') {
                // Fetch Token
                $token_url = "https://graph.facebook.com/v18.0/oauth/access_token?client_id={$this->facebook_client_id}&redirect_uri=" . urlencode($redirect_uri) . "&client_secret={$this->facebook_client_secret}&code={$code}";
                $token_response = wp_remote_get($token_url);
                if (is_wp_error($token_response)) $this->socialFail();
                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                if (empty($token_data['access_token'])) $this->socialFail();

                // Fetch Profile details (Email is explicitly requested)
                $profile_url = "https://graph.facebook.com/me?fields=id,first_name,last_name,email&access_token=" . $token_data['access_token'];
                $profile_response = wp_remote_get($profile_url);
                if (is_wp_error($profile_response)) $this->socialFail();
                $profile = json_decode(wp_remote_retrieve_body($profile_response), true);

                // If user didn't grant email permissions, we can't create an account
                if (empty($profile['email'])) $this->socialFail();
                
                $user_data = ['email' => $profile['email'], 'first_name' => $profile['first_name'] ?? '', 'last_name' => $profile['last_name'] ?? ''];
            }

            // Part C: Login or Create the user
            if (!empty($user_data)) {
                $user = get_user_by('email', $user_data['email']);
                
                if (!$user) {
                    $random_password = wp_generate_password(12, false);
                    $username = explode('@', $user_data['email'])[0];
                    if (username_exists($username)) $username = $username . '_' . wp_rand(100, 999);
                    
                    $user_id = wp_create_user($username, $random_password, $user_data['email']);
                    wp_update_user(['ID' => $user_id, 'first_name' => $user_data['first_name'], 'last_name' => $user_data['last_name']]);
                    $user = new \WP_User($user_id);
                    $user->set_role('nkrp_candidate'); // Default to candidate for social logins
                }

                clean_user_cache($user->ID);
                wp_clear_auth_cookie();
                wp_set_auth_cookie($user->ID, true, is_ssl());

                if (in_array('nkrp_employer', (array) $user->roles)) {
                    wp_safe_redirect(home_url('/employer-dashboard/'));
                } else {
                    wp_safe_redirect(home_url('/candidate-dashboard/'));
                }
                exit;
            }
        }
    }

    private function socialFail() {
        wp_safe_redirect(add_query_arg('login_error', 'social_failed', home_url('/login/')));
        exit;
    }

    // =========================================================================
    // 3. EMAIL VERIFICATION LISTENER
    // =========================================================================
    
    public function handleEmailVerification(): void
    {
        if (isset($_GET['nkrp_verify']) && isset($_GET['uid'])) {
            $token = sanitize_text_field($_GET['nkrp_verify']);
            $user_id = intval($_GET['uid']);

            $saved_token = get_user_meta($user_id, '_nkrp_verification_token', true);

            if ($saved_token && hash_equals($saved_token, $token)) {
                update_user_meta($user_id, '_nkrp_email_verified', '1');
                delete_user_meta($user_id, '_nkrp_verification_token');

                if (is_user_logged_in()) {
                    echo $this->redirectLoggedInUser('?verified=1');
                    exit;
                } else {
                    wp_safe_redirect(home_url('/login/?verified=1'));
                    exit;
                }
            } else {
                wp_die(
                    __('Invalid or expired verification link. Please request a new one from your dashboard.', 'nk-recruitment'), 
                    __('Verification Failed', 'nk-recruitment'), 
                    ['response' => 400]
                );
            }
        }
    }
}