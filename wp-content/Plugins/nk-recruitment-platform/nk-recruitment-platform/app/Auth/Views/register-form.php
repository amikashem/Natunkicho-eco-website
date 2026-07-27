<?php if (!defined('ABSPATH')) exit; 

$current_intent = $intent ?? (isset($_GET['intent']) ? sanitize_text_field($_GET['intent']) : 'nkrp_candidate');

$error = isset($_GET['reg_error']) ? sanitize_text_field(urldecode($_GET['reg_error'])) : '';
$error_message = '';
if ($error === 'missing_fields') $error_message = __('Please fill in all required fields.', 'nk-recruitment');
if ($error === 'email_exists') $error_message = __('This email is already registered. Please log in.', 'nk-recruitment');
if ($error === 'creation_failed') $error_message = __('An error occurred while creating your account. Please try again.', 'nk-recruitment');
if (!empty($error) && empty($error_message)) $error_message = $error; 
?>

<div class="nkrp-auth-container">
    <div class="nkrp-auth-box">
        
        <div class="nkrp-auth-header">
            <h2><?php esc_html_e('Create an Account', 'nk-recruitment'); ?></h2>
            <p><?php esc_html_e('Join our professional network today.', 'nk-recruitment'); ?></p>
        </div>

        <?php if ($error_message): ?>
            <div class="nkrp-alert nkrp-alert-error">
                <span class="dashicons dashicons-warning"></span>
                <?= esc_html($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="nkrp-social-login">
            <a href="<?= esc_url(site_url('?nkrp_social_auth=google')) ?>" class="nkrp-social-btn nkrp-google" style="text-decoration: none;">
                <span class="dashicons dashicons-google"></span> Sign up with Google
            </a>
            <a href="<?= esc_url(site_url('?nkrp_social_auth=linkedin')) ?>" class="nkrp-social-btn nkrp-linkedin" style="text-decoration: none;">
                <span class="dashicons dashicons-linkedin"></span> Sign up with LinkedIn
            </a>
            <a href="<?= esc_url(site_url('?nkrp_social_auth=facebook')) ?>" class="nkrp-social-btn nkrp-facebook" style="text-decoration: none;">
                <span class="dashicons dashicons-facebook-alt"></span> Sign up with Facebook
            </a> 
        </div>

        <div class="nkrp-divider">
            <span><?php esc_html_e('or register with email', 'nk-recruitment'); ?></span>
        </div>

        <form method="POST" action="<?= esc_url(admin_url('admin-post.php')) ?>" class="nkrp-auth-form">
            <input type="hidden" name="action" value="nkrp_auth_action">
            <input type="hidden" name="nkrp_action" value="standard_register">
            <?php wp_nonce_field('nkrp_register_action', 'nkrp_register_nonce'); ?>
            
            <div class="nkrp-form-group">
                <label><?php esc_html_e('I want to:', 'nk-recruitment'); ?></label>
                <div class="nkrp-role-selector">
                    <label class="nkrp-role-card <?= ($current_intent === 'nkrp_candidate' || $current_intent === 'candidate') ? 'active' : '' ?>">
                        <input type="radio" name="user_role" value="nkrp_candidate" <?= ($current_intent === 'nkrp_candidate' || $current_intent === 'candidate') ? 'checked' : '' ?> required>
                        <span class="dashicons dashicons-id"></span>
                        <strong><?php esc_html_e('Find a Job', 'nk-recruitment'); ?></strong>
                        <span><?php esc_html_e('Create a candidate profile', 'nk-recruitment'); ?></span>
                    </label>
                    
                    <label class="nkrp-role-card <?= ($current_intent === 'nkrp_employer' || $current_intent === 'employer') ? 'active' : '' ?>">
                        <input type="radio" name="user_role" value="nkrp_employer" <?= ($current_intent === 'nkrp_employer' || $current_intent === 'employer') ? 'checked' : '' ?> required>
                        <span class="dashicons dashicons-building"></span>
                        <strong><?php esc_html_e('Hire Talent', 'nk-recruitment'); ?></strong>
                        <span><?php esc_html_e('Post jobs and find staff', 'nk-recruitment'); ?></span>
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="nkrp-form-group">
                    <label>First Name <span style="color:#dc2626">*</span></label>
                    <input type="text" name="first_name" required placeholder="John">
                </div>
                <div class="nkrp-form-group">
                    <label>Last Name <span style="color:#dc2626">*</span></label>
                    <input type="text" name="last_name" required placeholder="Doe">
                </div>
            </div>

            <div class="nkrp-form-group">
                <label for="nkrp_email"><?php esc_html_e('Email Address', 'nk-recruitment'); ?> <span style="color:#dc2626">*</span></label>
                <input type="email" id="nkrp_email" name="email" required placeholder="you@example.com">
            </div>

            <div class="nkrp-form-group">
                <label for="nkrp_password"><?php esc_html_e('Password', 'nk-recruitment'); ?> <span style="color:#dc2626">*</span></label>
                <input type="password" id="nkrp_password" name="password" required placeholder="Create a strong password" minlength="8">
            </div>

            <button type="submit" name="nkrp_register_submit" class="nkrp-btn-submit">
                <?php esc_html_e('Create Account', 'nk-recruitment'); ?>
            </button>
        </form>

        <div class="nkrp-auth-footer">
            <p><?php esc_html_e('Already have an account?', 'nk-recruitment'); ?> <a href="<?= esc_url(home_url('/login/')) ?>"><?php esc_html_e('Log in here', 'nk-recruitment'); ?></a></p>
        </div>

    </div>
</div>

<style>
    /* Exact same styling as login, plus role cards */
    .nkrp-auth-container { display: flex; justify-content: center; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; min-height: 80vh; align-items: center; }
    .nkrp-auth-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
    .nkrp-auth-header { text-align: center; margin-bottom: 30px; }
    .nkrp-auth-header h2 { font-size: 24px; color: #0f172a; margin: 0 0 8px 0; font-weight: 700; }
    .nkrp-auth-header p { color: #64748b; margin: 0; font-size: 15px; }
    .nkrp-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; }
    .nkrp-alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .nkrp-social-login { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
    .nkrp-social-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; box-sizing: border-box; }
    
    .nkrp-social-btn:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
    .nkrp-facebook:hover { color: #1877F2 !important; border-color: #1877F2 !important; background: #f0f7ff !important; }
    .nkrp-google:hover { color: #db4437 !important; border-color: #db4437 !important; background: #fdf2f2 !important; }
    .nkrp-linkedin:hover { color: #0a66c2 !important; border-color: #0a66c2 !important; background: #f0f6ff !important; }

    .nkrp-social-btn .dashicons { font-size: 20px; width: 20px; height: 20px; }
    .nkrp-divider { text-align: center; position: relative; margin: 30px 0; z-index: 1; }
    .nkrp-divider::before { content: ""; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e2e8f0; z-index: -1; }
    .nkrp-divider span { background: #ffffff; padding: 0 16px; color: #94a3b8; font-size: 14px; font-weight: 500; }
    .nkrp-form-group { margin-bottom: 24px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; color: #334155; font-weight: 600; font-size: 14px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="email"], .nkrp-form-group input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; transition: border-color 0.2s; box-sizing: border-box; }
    .nkrp-form-group input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .nkrp-role-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .nkrp-role-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 20px 10px; border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.2s; position: relative; background: #ffffff; }
    .nkrp-role-card input[type="radio"] { position: absolute; opacity: 0; }
    .nkrp-role-card .dashicons { font-size: 32px; width: 32px; height: 32px; color: #64748b; margin-bottom: 12px; transition: color 0.2s; }
    .nkrp-role-card strong { font-size: 16px; color: #0f172a; margin-bottom: 4px; display: block; }
    .nkrp-role-card span:last-child { font-size: 12px; color: #64748b; line-height: 1.3; }
    .nkrp-role-card:hover { border-color: #cbd5e1; background: #f8fafc; }
    .nkrp-role-card.active, .nkrp-role-card:has(input:checked) { border-color: #2563eb; background: #eff6ff; }
    .nkrp-role-card.active .dashicons, .nkrp-role-card:has(input:checked) .dashicons { color: #2563eb; }

    .nkrp-btn-submit { width: 100%; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    .nkrp-auth-footer { text-align: center; margin-top: 24px; font-size: 14px; color: #64748b; }
    .nkrp-auth-footer a { color: #2563eb; font-weight: 600; text-decoration: none; }
    .nkrp-auth-footer a:hover { text-decoration: underline; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.nkrp-role-card');
        const radios = document.querySelectorAll('.nkrp-role-card input[type="radio"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                cards.forEach(c => c.classList.remove('active'));
                if (this.checked) this.closest('.nkrp-role-card').classList.add('active');
            });
        });
    });
</script>