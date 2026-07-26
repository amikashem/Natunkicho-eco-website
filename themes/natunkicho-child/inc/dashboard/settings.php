<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_render_settings_page( $active_view ) {
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    
    $is_premium = function_exists('nk_is_user_premium') ? nk_is_user_premium( $user_id ) : false;

    $pref_jobs    = get_user_meta($user_id, 'nk_pref_email_jobs', true) !== '0';
    $pref_courses = get_user_meta($user_id, 'nk_pref_email_courses', true) !== '0';
    $alert_freq   = get_user_meta($user_id, 'nk_pref_alert_freq', true) ?: 'daily';
    $is_public    = get_user_meta($user_id, 'nk_pref_cv_public', true) !== '0';

    $plan_name = get_user_meta($user_id, 'nk_premium_plan_name', true) ?: 'Premium Pro';
    $expiry    = get_user_meta($user_id, 'nk_premium_expiry', true);
    
    $expiry_text = '';
    if ( $is_premium && $expiry ) {
        if ( $expiry === 'lifetime' ) { $expiry_text = 'Never (Lifetime Access)'; } 
        else { $expiry_text = date('F j, Y', (int)$expiry); }
    }

    // 🔴 10X INTEGRATION: Pull dynamic master status from custom plugin database
    global $wpdb;
    $master_status = 'active'; // Default fallback
    if ( class_exists('NK_Database') ) {
        $table_subscribers = NK_Database::table('subscribers');
        $db_status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$table_subscribers} WHERE email = %s", $user->user_email ) );
        if ( $db_status ) {
            $master_status = $db_status;
        }
    }

    ob_start();
    ?>
    <div class="nk-settings-wrapper" style="max-width: 900px; display: flex; flex-direction: column; gap: 25px;">

        <?php if ( isset($_GET['upgrade']) && $_GET['upgrade'] === 'success' ) : ?>
            <div style="background: #dcfce7; border: 1px solid #22c55e; color: #166534; padding: 20px; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🎉</span> Payment Successful! Your account has been upgraded to Premium Pro.
            </div>
        <?php endif; ?>

        <div class="nk-dash-card" style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;">🔄 Workspace Context</h3>
                <p style="margin: 0; color: #64748b; font-size: 14px;">You are currently viewing the dashboard as a <strong><?php echo esc_html( ucfirst( $active_view ) ); ?></strong>.</p>
            </div>
            <div>
                <?php if ( $active_view === 'employer' ) : ?>
                    <button class="nk-workspace-switch-btn" data-target="candidate" style="background: #f8fafc; color: #0A66C2; border: 1px solid #0A66C2; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Switch to Candidate Panel</button>
                <?php else : ?>
                    <button class="nk-workspace-switch-btn" data-target="employer" style="background: #0A66C2; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Switch to Recruiter Panel</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="nk-dash-card" style="background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;">💳 Subscription Details</h3>
                <?php if ( $is_premium ) : ?>
                    <div style="margin-top: 10px; font-size: 14px; color: #334155; line-height: 1.6;">
                        <strong>Active Plan:</strong> <span style="background: #fef08a; color: #854d0e; padding: 3px 10px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; margin-left: 5px;"><?php echo esc_html($plan_name); ?> 🌟</span><br>
                        <strong>Expires On:</strong> <span style="color: #0A66C2; font-weight: 600;"><?php echo esc_html($expiry_text); ?></span>
                    </div>
                    <div style="margin-top: 12px;"><a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="font-size: 13px; color: #64748b; text-decoration: underline; font-weight: 600;">Upgrade or Extend Package</a></div>
                <?php else : ?>
                    <p style="margin: 0; font-size: 14px; color: #64748b;">Current Active Tier: <span style="background: #e2e8f0; color: #475569; padding: 3px 10px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase;">Free Basic</span></p>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #ef4444; font-weight: 600;">Your premium access has expired or is inactive.</p>
                <?php endif; ?>
            </div>
            <?php if ( !$is_premium ) : ?>
                <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="background: #10b981; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; text-decoration: none; box-shadow: 0 4px 10px rgba(16,185,129,0.3);">Upgrade to Premium</a>
            <?php endif; ?>
        </div>

        <form id="nk-settings-form" style="display: flex; flex-direction: column; gap: 25px;">
            <div class="nk-dash-card" style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); overflow: hidden;">
                <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;">📬 Smart Notifications</h3>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;">Control what NatunKicho sends to your inbox (<?php echo esc_html($user->user_email); ?>).</p>
                
                <div style="margin-bottom: 25px; padding: 15px; background: <?php echo ($master_status === 'active') ? '#f0fdf4' : '#fef2f2'; ?>; border-radius: 8px; border: 1px solid <?php echo ($master_status === 'active') ? '#bbf7d0' : '#fecaca'; ?>;">
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; cursor:pointer; margin-bottom: 10px; color: #166534; font-size:14px;">
                        <input type="radio" name="nk_master_subscription_status" value="active" <?php checked($master_status, 'active'); ?> style="width:16px; height:16px; accent-color:#10b981;">
                        ✅ Enable System Alerts, Job Alerts & Newsletters
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; font-weight:700; cursor:pointer; color:#b91c1c; font-size:14px;">
                        <input type="radio" name="nk_master_subscription_status" value="unsubscribed" <?php checked($master_status, 'unsubscribed'); ?> style="width:16px; height:16px; accent-color:#ef4444;">
                        🔕 Unsubscribe from all non-essential platform correspondence
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 15px; color: #334155; font-weight: 500;">
                        <input type="checkbox" name="nk_pref_email_jobs" value="1" <?php checked($pref_jobs, true); ?> style="width: 18px; height: 18px; accent-color: #0A66C2; cursor: pointer;"> Receive Automated Job Matches & Alerts
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 15px; color: #334155; font-weight: 500;">
                        <input type="checkbox" name="nk_pref_email_courses" value="1" <?php checked($pref_courses, true); ?> style="width: 18px; height: 18px; accent-color: #0A66C2; cursor: pointer;"> Receive Platform News, Courses, & Skill Recommendations
                    </label>
                </div>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <label style="display: block; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Email Alert Frequency</label>
                    <select name="nk_pref_alert_freq" style="width: 100%; max-width: 300px; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; outline: none; cursor: pointer;">
                        <option value="weekly" <?php selected($alert_freq, 'weekly'); ?>>Weekly Digest</option>
                        <option value="daily" <?php selected($alert_freq, 'daily'); ?>>Daily Digest (Recommended)</option>
                        <?php if ( $is_premium ) : ?>
                            <option value="realtime" <?php selected($alert_freq, 'realtime'); ?>>⚡ Real-time (Instant Alerts)</option>
                        <?php else : ?>
                            <option value="realtime" disabled>⚡ Real-time Alerts (Premium Only)</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <?php if ( $active_view === 'candidate' ) : ?>
            <div class="nk-dash-card" style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;">🔒 Privacy & Visibility</h3>
                <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;">Control who can find your profile in our Global Talent Database.</p>
                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; font-size: 15px; color: #334155; font-weight: 500;">
                    <input type="checkbox" name="nk_pref_cv_public" value="1" <?php checked($is_public, true); ?> style="width: 20px; height: 20px; accent-color: #10b981; margin-top: 2px; cursor: pointer;">
                    <div>
                        <span style="display: block; font-weight: bold; color: #0f172a;">Make My CV Public to Employers</span>
                        <span style="font-size: 13px; color: #64748b; font-weight: normal;">If unchecked, employers cannot find you in searches.</span>
                    </div>
                </label>
            </div>
            <?php endif; ?>

            <div>
                <input type="hidden" name="action" value="nk_save_user_settings">
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('nk_settings_nonce'); ?>">
                <button type="submit" id="nk-settings-save-btn" style="background: #0A66C2; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: all 0.2s;">Save Preferences</button>
            </div>
        </form>

        <div class="nk-dash-card" style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;">🔐 Account Security</h3>
            <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;">Update your password securely without leaving the dashboard.</p>
            
            <form id="nk-security-form" style="max-width: 400px; display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:5px; color:#334155;">Current Password</label>
                    <input type="password" name="current_pass" required style="width:100%; padding:12px; border-radius:8px; border:1px solid #cbd5e1; outline:none; font-size: 14px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:5px; color:#334155;">New Password</label>
                    <input type="password" name="new_pass" required style="width:100%; padding:12px; border-radius:8px; border:1px solid #cbd5e1; outline:none; font-size: 14px;">
                </div>
                <input type="hidden" name="action" value="nk_change_account_password">
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('nk_security_nonce'); ?>">
                <button type="submit" id="nk-security-btn" style="background: #1e293b; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; align-self: flex-start; transition: 0.2s;">Update Password</button>
                <div id="nk-security-msg" style="font-size: 14px; font-weight: bold; margin-top: 5px;"></div>
            </form>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. WORKSPACE SWITCHER AJAX
        const switchBtns = document.querySelectorAll('.nk-workspace-switch-btn');
        switchBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.innerText = 'Switching View...';
                this.disabled = true;

                let fd = new FormData();
                fd.append('action', 'nk_switch_user_workspace');
                fd.append('target_context', this.getAttribute('data-target'));
                fd.append('security', '<?php echo wp_create_nonce("nk_workspace_nonce"); ?>');

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { window.location.href = data.data.redirect; } 
                    else { alert(data.data || 'Failed to switch workspace.'); window.location.reload(); }
                });
            });
        });

        // 2. SETTINGS SAVE AJAX
        const settingsForm = document.getElementById('nk-settings-form');
        const saveBtn = document.getElementById('nk-settings-save-btn');
        if(settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const originalText = saveBtn.innerText;
                saveBtn.innerText = 'Saving...'; saveBtn.disabled = true;
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        saveBtn.innerText = 'Saved Successfully ✓'; saveBtn.style.background = '#10b981';
                        setTimeout(() => { saveBtn.innerText = originalText; saveBtn.style.background = '#0A66C2'; saveBtn.disabled = false; }, 2500);
                    } else { alert(data.data || 'Failed to save settings.'); saveBtn.innerText = originalText; saveBtn.disabled = false; }
                });
            });
        });

        // 3. SECURITY SAVE AJAX
        const securityForm = document.getElementById('nk-security-form');
        const securityBtn = document.getElementById('nk-security-btn');
        const securityMsg = document.getElementById('nk-security-msg');
        if(securityForm) {
            securityForm.addEventListener('submit', function(e) {
                e.preventDefault();
                securityBtn.innerText = 'Updating...'; securityBtn.disabled = true; securityMsg.innerText = '';
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) { securityMsg.style.color = '#10b981'; securityMsg.innerText = 'Password updated successfully! ✓'; securityForm.reset(); } 
                    else { securityMsg.style.color = '#ef4444'; securityMsg.innerText = data.data || 'Failed to update password.'; }
                    securityBtn.innerText = 'Update Password'; securityBtn.disabled = false;
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}