<?php
if (!defined('ABSPATH')) exit;

function nk_auth_login_shortcode() {
    // If they are already logged in, don't show the form
    if (is_user_logged_in()) {
        return '<div style="text-align:center; padding: 50px;"><h3>You are already logged in.</h3><a href="'.esc_url(home_url('/dashboard/')).'" class="nk-btn-primary">Go to Dashboard</a></div>';
    }
    
    ob_start();
    ?>
    <div class="nk-auth-wrapper">
        
        <div class="nk-auth-visual" style="background-image: url('https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');">
            <div class="nk-auth-visual-overlay"></div>
            <div class="nk-auth-visual-content">
                <h2>Welcome Back</h2>
                <p>Sign in to access your dashboard, manage applications, and connect with top hospitality talent.</p>
            </div>
        </div>

        <div class="nk-auth-panel">
            <div class="nk-auth-form-container">
                
                <h3 class="nk-auth-title">Sign In</h3>

                <div class="nk-social-login-wrapper">
                    <?php echo do_shortcode('[nk_custom_social_login]'); ?>
                </div>

                <div class="nk-auth-divider"><span>or continue with email</span></div>

                <form id="nk-custom-login-form" class="nk-professional-form">
                    <?php wp_nonce_field('nk_auth_nonce', 'nk_security'); ?>
                    
                    <input type="hidden" name="redirect_to" value="<?php echo isset($_GET['redirect_to']) ? esc_attr($_GET['redirect_to']) : ''; ?>">

                    <div class="nk-form-group">

                    <div class="nk-form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" required placeholder="john@example.com">
                    </div>

                    <div class="nk-form-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="nk-form-group" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <label style="margin: 0; font-weight: normal; font-size: 14px; cursor: pointer; display: flex; align-items: center;">
                            <input type="checkbox" name="remember" style="width: auto; height: auto; margin-right: 8px;"> Remember me
                        </label>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" style="font-size: 14px; color: #0A66C2; text-decoration: none; font-weight: 600;">Forgot Password?</a>
                    </div>

                    <button type="submit" class="nk-btn-primary" style="width: 100%;" id="nk-login-submit-btn">
                        Sign In
                    </button>
                </form>

                <p class="nk-auth-footer-link">Don't have an account? <a href="<?php echo esc_url(home_url('/register/')); ?>">Create Account</a></p>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nk-custom-login-form');
        const submitBtn = document.getElementById('nk-login-submit-btn');

        if(form){
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                submitBtn.textContent = 'Signing in...';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';

                let formData = new FormData(form);
                formData.append('action', 'nk_custom_login');
                formData.append('security', document.getElementById('nk_security').value);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success! Show toast and redirect
                        nk_show_toast(data.data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.data.redirect;
                        }, 1000);
                    } else {
                        // Error! Show the helpful error message
                        nk_show_toast(data.data, 'error');
                        submitBtn.textContent = 'Sign In';
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                    }
                })
                .catch(error => {
                    nk_show_toast('A server error occurred. Please try again.', 'error');
                    submitBtn.textContent = 'Sign In';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_auth_login', 'nk_auth_login_shortcode');