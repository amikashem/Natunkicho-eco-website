<?php if (!defined('ABSPATH')) exit; 
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);

// Load current preferences
$pref_jobs     = get_user_meta($user_id, '_nkrp_pref_job_alerts', true) ?: 'yes';
$pref_premium  = get_user_meta($user_id, '_nkrp_pref_premium_alerts', true) ?: 'yes';
$pref_news     = get_user_meta($user_id, '_nkrp_pref_newsletter', true) ?: 'yes';
$pref_learning = get_user_meta($user_id, '_nkrp_pref_learning', true) ?: 'yes';
$pref_promo    = get_user_meta($user_id, '_nkrp_pref_promo_emails', true) ?: 'yes';

// NEW: Load Privacy and Frequency Data
$profile_privacy = get_user_meta($user_id, '_nkrp_profile_privacy', true) ?: 'public';
$notify_freq_jobs = get_user_meta($user_id, '_nkrp_notify_freq_jobs', true) ?: 'instantly';
$notify_freq_messages = get_user_meta($user_id, '_nkrp_notify_freq_messages', true) ?: 'instantly';

$phone = get_user_meta($user_id, '_nkrp_phone', true) ?: '';

// Check if user is premium for Real-Time access
$is_premium = apply_filters('nkrp_is_user_premium', false, $user_id);
$pref_realtime = get_user_meta($user_id, '_nkrp_pref_realtime_alerts', true) ?: 'no';
?>

<div class="nkrp-dashboard-header">
    <h2>Account Settings</h2>
</div>

<?php if (isset($_GET['settings_updated']) && $_GET['settings_updated'] == '1'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Settings updated successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['settings_error'])): ?>
    <div class="nkrp-alert nkrp-alert-error" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;">
        <span class="dashicons dashicons-warning"></span> <?= esc_html(urldecode($_GET['settings_error'])) ?>
    </div>
<?php endif; ?>

<div class="nkrp-settings-grid">

    <!-- ACCOUNT DETAILS -->
    <div class="nkrp-settings-card" style="grid-column: 1 / -1;">
        <h3><span class="dashicons dashicons-admin-users"></span> Account Details</h3>
        <form method="POST" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>">
            <?php wp_nonce_field('update_account_action', 'nkrp_account_nonce'); ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px;">
                <div class="nkrp-form-group">
                    <label>Username (Login ID)</label>
                    <input type="text" name="username" value="<?= esc_attr($user_info->user_login) ?>" required>
                </div>
                <div class="nkrp-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= esc_attr($user_info->user_email) ?>" required>
                </div>
                <div class="nkrp-form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= esc_attr($phone) ?>" placeholder="+1 234 567 8900">
                </div>
            </div>
            <button type="submit" name="nkrp_update_account" class="nkrp-btn-primary">Save Account Details</button>
        </form>
    </div>

    <!-- PRIVACY & NOTIFICATIONS (UPGRADED) -->
    <div class="nkrp-settings-card" style="grid-column: 1 / -1;">
        <h3><span class="dashicons dashicons-email-alt"></span> Privacy & Notifications</h3>
        <form method="POST" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>">
            <?php wp_nonce_field('update_prefs_action', 'nkrp_prefs_nonce'); ?>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px solid #e2e8f0;">
                <div class="nkrp-form-group" style="margin-bottom: 0;">
                    <label>CV & Profile Visibility</label>
                    <select name="profile_privacy" class="nkrp-select-field">
                        <option value="public" <?= selected($profile_privacy, 'public', false) ?>>Public (Employers can find me)</option>
                        <option value="private" <?= selected($profile_privacy, 'private', false) ?>>Private (Only visible when I apply)</option>
                    </select>
                </div>
                <div class="nkrp-form-group" style="margin-bottom: 0;">
                    <label>Job Alert Frequency</label>
                    <select name="notify_freq_jobs" class="nkrp-select-field">
                        <option value="instantly" <?= selected($notify_freq_jobs, 'instantly', false) ?>>Instantly</option>
                        <option value="daily" <?= selected($notify_freq_jobs, 'daily', false) ?>>Daily Digest</option>
                        <option value="weekly" <?= selected($notify_freq_jobs, 'weekly', false) ?>>Weekly Digest</option>
                        <option value="never" <?= selected($notify_freq_jobs, 'never', false) ?>>Never</option>
                    </select>
                </div>
                <div class="nkrp-form-group" style="margin-bottom: 0;">
                    <label>Direct Message Alerts</label>
                    <select name="notify_freq_messages" class="nkrp-select-field">
                        <option value="instantly" <?= selected($notify_freq_messages, 'instantly', false) ?>>Instantly</option>
                        <option value="daily" <?= selected($notify_freq_messages, 'daily', false) ?>>Daily Digest</option>
                        <option value="never" <?= selected($notify_freq_messages, 'never', false) ?>>Never</option>
                    </select>
                </div>
            </div>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-bottom: 25px;">
                
                <div class="nkrp-toggle-group <?= !$is_premium ? 'nkrp-locked-feature' : '' ?>">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_realtime" value="yes" <?= checked($pref_realtime, 'yes', false) ?> <?= !$is_premium ? 'disabled' : '' ?>>
                        <strong>Real-Time SMS Alerts <span class="nkrp-badge-gold">Premium</span></strong>
                        <span class="nkrp-toggle-desc">Get instantly texted the second a matching job is posted.</span>
                    </label>
                </div>

                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_learning" value="yes" <?= checked($pref_learning, 'yes', false) ?>>
                        <strong>Learning Notifications</strong>
                        <span class="nkrp-toggle-desc">Updates on new courses, interview tips, and CV building guides.</span>
                    </label>
                </div>

                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_news" value="yes" <?= checked($pref_news, 'yes', false) ?>>
                        <strong>Platform Newsletter</strong>
                        <span class="nkrp-toggle-desc">Receive monthly platform updates and community news.</span>
                    </label>
                </div>

                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_promo" value="yes" <?= checked($pref_promo, 'yes', false) ?>>
                        <strong>Promotional Emails</strong>
                        <span class="nkrp-toggle-desc">Special offers and sponsored content from top employers.</span>
                    </label>
                </div>
            </div>

            <button type="submit" name="nkrp_update_prefs" class="nkrp-btn-primary">Save Privacy & Preferences</button>
        </form>
    </div>

    <!-- SECURITY & SWITCH ROLE -->
    <div class="nkrp-settings-card">
        <h3><span class="dashicons dashicons-lock"></span> Change Password</h3>
        <form method="POST" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>">
            <?php wp_nonce_field('update_password_action', 'nkrp_password_nonce'); ?>
            <div class="nkrp-form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="nkrp-form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="nkrp-form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" name="nkrp_update_password" class="nkrp-btn-primary">Update Password</button>
        </form>
    </div>

    <div class="nkrp-settings-card" style="background:#f0fdf4; border-color:#bbf7d0;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
            <div>
                <h3 style="margin-top:0; color:#166534;"><span class="dashicons dashicons-building"></span> Switch to Employer Account</h3>
                <p style="margin:0; font-size:13px; color:#166534;">Looking to hire talent? Switch your account to an Employer profile.</p>
            </div>
            <form method="POST" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>" onsubmit="return confirm('Are you sure you want to switch to an Employer account?');">
                <?php wp_nonce_field('switch_role_action', 'nkrp_switch_role_nonce'); ?>
                <button type="submit" name="nkrp_switch_role" class="nkrp-btn-secondary" style="border-color:#166534; color:#166534;">Become an Employer</button>
            </form>
        </div>
    </div>
</div>

<style>

    
    /* 🔥 FIX 4: Ensures dropdown options aren't cut off */
    .nkrp-select-field { 
        width: 100%; 
        padding: 12px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        font-size: 14px; 
        box-sizing: border-box; 
        background: #fff; 
        font-family: inherit;
        overflow: visible !important;
        min-height: 48px; /* Ensures consistent height */
    }
    
    /* 🔥 FIX 5: Last form group gets extra bottom margin for dropdown visibility */
    .nkrp-form-group:last-child {
        margin-bottom: 40px;
    }
    
    .nkrp-settings-card h3 { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        margin-top: 0; 
        margin-bottom: 20px; 
        font-size: 18px; 
        color: #0f172a; 
        flex-shrink: 0; /* Prevents title from shrinking */
    }
    
    .nkrp-form-group label { 
        display: block; 
        font-size: 14px; 
        font-weight: 600; 
        color: #334155; 
        margin-bottom: 8px; 
    }
    
    .nkrp-select-field { 
        width: 100%; 
        padding: 12px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        font-size: 14px; 
        box-sizing: border-box; 
        background: #fff; 
        font-family: inherit; 
    }
    
    .nkrp-select-field:focus { 
        outline: none; 
        border-color: #2563eb; 
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* 🔥 FIX 6: Mobile responsive with extra space */
    @media(max-width: 768px) {
        .nkrp-settings-grid { 
            grid-template-columns: 1fr; 
            margin-bottom: 120px; 
        }
        
        .nkrp-form-group:last-child {
            margin-bottom: 60px; /* Extra space for dropdown on mobile */
        }
    }
    
    /* 🔥 FIX 4: Add extra spacing ONLY under the Privacy card so dropdowns have room to fall over the bottom cards */
    .nkrp-settings-card:nth-child(2) {
        margin-bottom: 80px;
    }
    
    
    .nkrp-form-group { margin-bottom: 20px; position: relative; }
  
    
    .nkrp-toggle-group { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .nkrp-toggle-label { display: block; cursor: pointer; }
    .nkrp-toggle-label input { float: left; margin-right: 10px; margin-top: 4px; }
    .nkrp-toggle-label strong { display: block; font-size: 15px; color: #0f172a; margin-bottom: 4px; }
    .nkrp-toggle-desc { display: block; font-size: 13px; color: #64748b; margin-left: 24px; }
    
    .nkrp-locked-feature { opacity: 0.6; filter: grayscale(1); cursor: not-allowed; }
    .nkrp-badge-gold { background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; vertical-align: top; margin-left: 4px;}

    @media(max-width: 768px) {
        .nkrp-settings-grid { grid-template-columns: 1fr; }
        .nkrp-settings-card:nth-child(2) { margin-bottom: 20px; }
    }
</style>
</style>