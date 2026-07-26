<?php
if (!defined('ABSPATH')) exit;

function nk_auth_split_screen_shortcode() {
    ob_start();
    ?>
    <div class="nk-auth-wrapper">
        
        <div class="nk-auth-visual" style="background-image: url('https://images.unsplash.com/photo-1551882547-ff40c0d1398c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');">
            <div class="nk-auth-visual-overlay"></div>
            <div class="nk-auth-visual-content">
                <h2>Join the Future of Hospitality</h2>
                <p>Connect with top hotels, discover elite culinary talent, and elevate your career in minutes.</p>
            </div>
        </div>

        <div class="nk-auth-panel">
            <div class="nk-auth-form-container">
                
                <h3 class="nk-auth-title">Create your account</h3>
                
                <div class="nk-role-toggle">
                    <button class="nk-toggle-btn active" data-target="job_seeker">I am a Candidate</button>
                    <button class="nk-toggle-btn" data-target="employer">I am an Employer</button>
                </div>

                <div class="nk-social-login-wrapper">
                    <?php echo do_shortcode('[nk_custom_social_login]'); ?>
                </div>

                <div class="nk-auth-divider"><span>or continue with email</span></div>

               <form id="nk-custom-register-form" class="nk-professional-form">
                    <?php wp_nonce_field('nk_auth_nonce', 'nk_security'); ?>
                    
                    <input type="hidden" id="nk_reg_role" name="role" value="job_seeker">
                    
                    <input type="hidden" name="redirect_to" value="<?php echo isset($_GET['redirect_to']) ? esc_attr($_GET['redirect_to']) : ''; ?>">

                    <div class="nk-form-group">
                    
                    <input type="hidden" id="nk_reg_role" name="role" value="job_seeker">

                    <div class="nk-form-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="e.g. chefjohn99">
                    </div>

                    <div class="nk-form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>

                    <div class="nk-form-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="••••••••">
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            * Must be at least 8 characters long.
                        </small>
                    </div>

                    <button type="submit" class="nk-btn-primary" style="width: 100%; margin-top: 15px;" id="nk-reg-submit-btn">
                        Create Account
                    </button>
                </form>

                <p class="nk-auth-footer-link">Already have an account? <a href="<?php echo esc_url(home_url('/login/')); ?>">Sign In</a></p>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.nk-toggle-btn');
        const roleInput = document.getElementById('nk_reg_role');
        const form = document.getElementById('nk-custom-register-form');
        const submitBtn = document.getElementById('nk-reg-submit-btn');

        // Handle Role Toggle
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                buttons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Update the hidden role input!
                roleInput.value = this.getAttribute('data-target');
            });
        });

        // Handle Form Submission via AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state 
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';

            let formData = new FormData(form);
            formData.append('action', 'nk_custom_register');
            formData.append('security', document.getElementById('nk_security').value);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success! Show toast and redirect [cite: 761, 762]
                    nk_show_toast(data.data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.data.redirect;
                    }, 1000);
                } else {
                    // Error! Show the EXACT helpful error message we wrote in PHP
                    nk_show_toast(data.data, 'error');
                    submitBtn.textContent = 'Create Account';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
            })
            .catch(error => {
                nk_show_toast('A server error occurred. Please try again.', 'error');
                submitBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_auth_split', 'nk_auth_split_screen_shortcode');