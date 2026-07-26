<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PREMIUM SaaS MULTI-ROLE REGISTRATION & ONBOARDING ALERTS
 * Path: inc/auth/register.php
 * Shortcode: [nk_custom_register]
 * =========================================================================
 */

function nk_custom_registration_shortcode() {
    if (is_user_logged_in()) {
        return '<div class="nk-dash-card"><p>You are already registered. <a href="' . esc_url(home_url('/dashboard/')) . '">Go to your Dashboard</a>.</p></div>';
    }

    $default_role = 'job_seeker'; 
    if (isset($_GET['type']) && $_GET['type'] === 'employer') { $default_role = 'employer'; } 
    elseif (isset($_GET['redirect_to']) && (strpos(urldecode($_GET['redirect_to']), 'post-job') !== false)) { $default_role = 'employer'; } 

    // Cloudflare Turnstile API
    wp_enqueue_script('cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true);

    ob_start();
    ?>
    <div class="nk-dash-card" style="max-width: 550px; margin: 40px auto; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <h2 style="margin-top:0; font-weight:800; color:#1e293b; text-align:center;">Join the Ecosystem</h2>
        <p style="color:#64748b; font-size:14px; text-align:center; margin-bottom:25px;">Create your account to connect with premium hospitality opportunities.</p>

        <div style="display:flex; background:#f1f5f9; border-radius:8px; padding:4px; margin-bottom:25px;">
            <button type="button" id="tab-seeker" onclick="nkToggleRegRole('job_seeker')" style="flex:1; height:40px; border:none; border-radius:6px; font-weight:700; font-size:14px; cursor:pointer; background:#fff; color:#0A66C2; box-shadow:0 2px 4px rgba(0,0,0,0.05); transition:all 0.2s;">💼 Candidate</button>
            <button type="button" id="tab-employer" onclick="nkToggleRegRole('employer')" style="flex:1; height:40px; border:none; border-radius:6px; font-weight:700; font-size:14px; cursor:pointer; background:transparent; color:#64748b; transition:all 0.2s;">🏢 Employer</button>
        </div>

        <form id="nk-register-form" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="action" value="nk_custom_register">
            <input type="hidden" name="security" value="<?php echo wp_create_nonce('nk_auth_nonce'); ?>">
            <input type="hidden" name="role" id="nk_account_type" value="job_seeker">
            <?php if (isset($_GET['redirect_to'])): ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($_GET['redirect_to']); ?>">
            <?php endif; ?>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Username</label>
                <input type="text" name="username" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;" required>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Email Address</label>
                <input type="email" name="email" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;" required>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Password</label>
                <input type="password" name="password" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;" required>
            </div>

            <div id="wrapper-seeker" style="display:flex; flex-direction:column; gap:16px;">
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Professional Title</label>
                        <input type="text" name="candidate_profession" placeholder="e.g. Executive Chef" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Department</label>
                        <select name="candidate_department" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;">
                            <option value="">Select Category...</option>
                            <option value="culinary">Culinary & Kitchen</option>
                            <option value="fnb">Food & Beverage Service</option>
                            <option value="front_office">Front Office & Guest Services</option>
                            <option value="housekeeping">Housekeeping & Maintenance</option>
                            <option value="management">Management & Administration</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="wrapper-employer" style="display:none; flex-direction:column; gap:16px;">
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Company Name</label>
                    <input type="text" name="company_name" placeholder="e.g. The Ritz-Carlton" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Hospitality Sector</label>
                    <select name="company_industry" style="width:100%; height:46px; border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; box-sizing:border-box;">
                        <option value="hotels">Hotels & Resorts</option>
                        <option value="restaurants">Restaurants & Dining</option>
                        <option value="catering">Aviation & Catering</option>
                        <option value="cruise">Cruise Ships</option>
                    </select>
                </div>
            </div>

            <?php if (defined('NK_TURNSTILE_SITE_KEY') && NK_TURNSTILE_SITE_KEY !== '') : ?>
                <div class="cf-turnstile" data-sitekey="<?php echo esc_attr(NK_TURNSTILE_SITE_KEY); ?>" style="margin-top: 10px;"></div>
            <?php endif; ?>

            <button type="submit" id="nk-submit-btn" class="nk-btn-primary" style="width:100%; height:48px; border:none; border-radius:8px; font-weight:700; font-size:15px; cursor:pointer; margin-top:10px; background:#0A66C2; color:#fff;">Create Account</button>
            <div id="nk-reg-msg" style="font-weight: bold; text-align: center; margin-top: 10px; font-size: 14px;"></div>
        </form>

        <script>
        function nkToggleRegRole(roleTarget) {
            const inputField = document.getElementById('nk_account_type');
            const tabSeeker = document.getElementById('tab-seeker');
            const tabEmployer = document.getElementById('tab-employer');
            const wrapSeeker = document.getElementById('wrapper-seeker');
            const wrapEmployer = document.getElementById('wrapper-employer');

            if (!inputField || !tabSeeker || !tabEmployer) return;
            inputField.value = roleTarget;

            if(roleTarget === 'job_seeker') {
                tabSeeker.style.background = '#fff'; tabSeeker.style.color = '#0A66C2'; tabSeeker.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
                tabEmployer.style.background = 'transparent'; tabEmployer.style.color = '#64748b'; tabEmployer.style.boxShadow = 'none';
                wrapSeeker.style.display = 'flex'; wrapEmployer.style.display = 'none';
            } else {
                tabEmployer.style.background = '#fff'; tabEmployer.style.color = '#0A66C2'; tabEmployer.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
                tabSeeker.style.background = 'transparent'; tabSeeker.style.color = '#64748b'; tabSeeker.style.boxShadow = 'none';
                wrapEmployer.style.display = 'flex'; wrapSeeker.style.display = 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            let finalRole = '<?php echo esc_js($default_role); ?>';
            const urlString = window.location.href.toLowerCase();
            if (urlString.includes('type=employer') || urlString.includes('post-job') || urlString.includes('manage-jobs')) { finalRole = 'employer'; }
            if (localStorage.getItem('nk_intended_role') === 'employer') { finalRole = 'employer'; localStorage.removeItem('nk_intended_role'); }
            nkToggleRegRole(finalRole);

            // AJAX FORM SUBMISSION
            const form = document.getElementById('nk-register-form');
            const btn = document.getElementById('nk-submit-btn');
            const msg = document.getElementById('nk-reg-msg');

            if(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    btn.innerText = 'Creating Account...'; btn.disabled = true; msg.innerText = '';
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: new FormData(this) })
                    .then(res => res.json()).then(data => {
                        if (data.success) {
                            msg.style.color = '#10b981';
                            msg.innerText = data.data.message;
                            if(data.data.redirect) { setTimeout(() => { window.location.href = data.data.redirect; }, 2000); }
                        } else {
                            msg.style.color = '#ef4444';
                            msg.innerText = data.data;
                            btn.innerText = 'Create Account'; btn.disabled = false;
                        }
                    });
                });
            }
        });
        </script>

        <p style="text-align:center; font-size:14px; color:#64748b; margin-top:25px; margin-bottom:0;">
            Already have an account? <a href="<?php echo esc_url(home_url('/login/')); ?>" style="color:#0A66C2; font-weight:700; text-decoration:none;">Sign in here</a>
        </p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_custom_register', 'nk_custom_registration_shortcode');