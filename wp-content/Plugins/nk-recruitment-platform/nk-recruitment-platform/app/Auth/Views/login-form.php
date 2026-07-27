<?php if (!defined('ABSPATH')) exit; 

// Handle Error Messages caught from the AuthController
$error = isset($_GET['login_error']) ? sanitize_text_field($_GET['login_error']) : '';$error_message = '';
if ($error === '1') {$error_message = __('Invalid email or password. Please try again.', 'nk-recruitment');
} elseif ($error === 'social_failed') {$error_message = __('Social login failed, was canceled, or email access was denied. Please try again.', 'nk-recruitment');
}
?>

<div class="nkrp-auth-container">
    <div class="nkrp-auth-box">
        
        <div class="nkrp-auth-header">
            <h2><?php esc_html_e('Welcome Back', 'nk-recruitment'); ?></h2>
            <p><?php esc_html_e('Log in to manage your account.', 'nk-recruitment'); ?></p>
        </div>

        <?php if ($error_message): ?>
            <div class="nkrp-alert nkrp-alert-error">
                <span class="dashicons dashicons-warning"></span>
                <?= esc_html($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="nkrp-social-login">
            <a href="<?= esc_url(site_url('?nkrp_social_auth=google')) ?>" class="nkrp-social-btn nkrp-google" style="text-decoration: none;">
                <span class="dashicons dashicons-google"></span> Log in with Google
            </a>
            <a href="<?= esc_url(site_url('?nkrp_social_auth=linkedin')) ?>" class="nkrp-social-btn nkrp-linkedin" style="text-decoration: none;">
                <span class="dashicons dashicons-linkedin"></span> Log in with LinkedIn
            </a>
            <a href="<?= esc_url(site_url('?nkrp_social_auth=facebook')) ?>" class="nkrp-social-btn nkrp-facebook" style="text-decoration: none;">
                <span class="dashicons dashicons-facebook-alt"></span> Log in with Facebook
            </a> 
        </div>

        <div class="nkrp-divider">
            <span><?php esc_html_e('or log in with email', 'nk-recruitment'); ?></span>
        </div>

        <form method="POST" action="" class="nkrp-auth-form">
            <?php wp_nonce_field('nkrp_login_action', 'nkrp_login_nonce'); ?>
            <input type="hidden" name="nkrp_action" value="standard_login">
            
            <div class="nkrp-form-group">
                <label for="nkrp_log"><?php esc_html_e('Email Address', 'nk-recruitment'); ?></label>
                <input type="email" id="nkrp_log" name="log" required placeholder="you@example.com">
            </div>

            <div class="nkrp-form-group">
                <div class="nkrp-password-header">
                    <label for="nkrp_pwd"><?php esc_html_e('Password', 'nk-recruitment'); ?></label>
                    <a href="<?= esc_url(wp_lostpassword_url()) ?>" class="nkrp-forgot-link"><?php esc_html_e('Forgot password?', 'nk-recruitment'); ?></a>
                </div>
                <input type="password" id="nkrp_pwd" name="pwd" required placeholder="Enter your password">
            </div>

            <div class="nkrp-form-group nkrp-remember-me">
                <label>
                    <input type="checkbox" name="rememberme" value="forever">
                    <?php esc_html_e('Remember me', 'nk-recruitment'); ?>
                </label>
            </div>

            <button type="submit" class="nkrp-btn-submit">
                <?php esc_html_e('Log In', 'nk-recruitment'); ?>
            </button>
        </form>

        <div class="nkrp-auth-footer">
            <p><?php esc_html_e('Don\'t have an account?', 'nk-recruitment'); ?> <a href="<?= esc_url(home_url('/register/')) ?>"><?php esc_html_e('Sign up here', 'nk-recruitment'); ?></a></p>
        </div>

    </div>
</div>

<style>
    /* SaaS Auth Container Styling */
    .nkrp-auth-container { display: flex; justify-content: center; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; min-height: 80vh; align-items: center; }
    .nkrp-auth-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
    .nkrp-auth-header { text-align: center; margin-bottom: 30px; }
    .nkrp-auth-header h2 { font-size: 24px; color: #0f172a; margin: 0 0 8px 0; font-weight: 700; }
    .nkrp-auth-header p { color: #64748b; margin: 0; font-size: 15px; }
    .nkrp-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; }
    .nkrp-alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .nkrp-social-login { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
    .nkrp-social-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; box-sizing: border-box;}
    .nkrp-social-btn:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
    
    /* Facebook specific hover colors */
    .nkrp-facebook:hover { color: #1877F2 !important; border-color: #1877F2 !important; background: #f0f7ff !important; }
    .nkrp-google:hover { color: #db4437 !important; border-color: #db4437 !important; background: #fdf2f2 !important; }
    .nkrp-linkedin:hover { color: #0a66c2 !important; border-color: #0a66c2 !important; background: #f0f6ff !important; }

    .nkrp-social-btn .dashicons { font-size: 20px; width: 20px; height: 20px; }
    .nkrp-divider { text-align: center; position: relative; margin: 30px 0; z-index: 1; }
    .nkrp-divider::before { content: ""; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e2e8f0; z-index: -1; }
    .nkrp-divider span { background: #ffffff; padding: 0 16px; color: #94a3b8; font-size: 14px; font-weight: 500; }
    .nkrp-form-group { margin-bottom: 24px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; color: #334155; font-weight: 600; font-size: 14px; }
    .nkrp-form-group input[type="email"], .nkrp-form-group input[type="password"], .nkrp-form-group input[type="text"] { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; transition: border-color 0.2s; box-sizing: border-box; }
    .nkrp-form-group input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .nkrp-password-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .nkrp-password-header label { margin-bottom: 0; }
    .nkrp-forgot-link { font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 500; }
    .nkrp-forgot-link:hover { text-decoration: underline; }
    .nkrp-remember-me label { display: flex; align-items: center; gap: 8px; font-weight: normal; font-size: 14px; cursor: pointer; color: #475569; }
    .nkrp-remember-me input { margin: 0; cursor: pointer; }
    .nkrp-btn-submit { width: 100%; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    .nkrp-auth-footer { text-align: center; margin-top: 24px; font-size: 14px; color: #64748b; }
    .nkrp-auth-footer a { color: #2563eb; font-weight: 600; text-decoration: none; }
    .nkrp-auth-footer a:hover { text-decoration: underline; }
</style>