<?php if (!defined('ABSPATH')) exit; 
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);

$pref_employer = get_user_meta($user_id, '_nkrp_pref_employer_notifications', true) ?: 'yes';
$pref_premium  = get_user_meta($user_id, '_nkrp_pref_premium_alerts', true) ?: 'yes';
$pref_news     = get_user_meta($user_id, '_nkrp_pref_newsletter', true) ?: 'yes';
$pref_promo    = get_user_meta($user_id, '_nkrp_pref_promo_emails', true) ?: 'yes';

$phone = get_user_meta($user_id, '_nkrp_phone', true) ?: '';
?>

<div class="nkrp-dashboard-header">
    <h2>Employer Settings</h2>
</div>

<?php if (isset($_GET['settings_updated']) && $_GET['settings_updated'] == '1'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Settings updated successfully.</div>
<?php endif; ?>

<div class="nkrp-settings-grid">
    <div class="nkrp-settings-card" style="grid-column: 1 / -1;">
        <h3><span class="dashicons dashicons-admin-users"></span> Account Details</h3>
        <form method="POST" action="">
            <input type="hidden" name="nkrp_action" value="update_account">
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
            <button type="submit" class="nkrp-btn-primary">Save Account Details</button>
        </form>
    </div>

    <div class="nkrp-settings-card" style="grid-column: 1 / -1;">
        <h3><span class="dashicons dashicons-email-alt"></span> Email & Notification Preferences</h3>
        <form method="POST" action="">
            <input type="hidden" name="nkrp_action" value="update_prefs">
            <?php wp_nonce_field('update_prefs_action', 'nkrp_prefs_nonce'); ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-bottom: 25px;">
                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_employer" value="yes" <?= checked($pref_employer, 'yes', false) ?>>
                        <strong>Employer Notifications</strong>
                        <span class="nkrp-toggle-desc">Receive emails when candidates apply to your jobs.</span>
                    </label>
                </div>
                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_premium" value="yes" <?= checked($pref_premium, 'yes', false) ?>>
                        <strong>Premium Alerts</strong>
                        <span class="nkrp-toggle-desc">Get notified about subscription renewals and features.</span>
                    </label>
                </div>
                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_news" value="yes" <?= checked($pref_news, 'yes', false) ?>>
                        <strong>Platform Newsletter</strong>
                        <span class="nkrp-toggle-desc">Receive monthly insights and hiring trends.</span>
                    </label>
                </div>
                <div class="nkrp-toggle-group">
                    <label class="nkrp-toggle-label">
                        <input type="checkbox" name="pref_promo" value="yes" <?= checked($pref_promo, 'yes', false) ?>>
                        <strong>Promotional Emails</strong>
                        <span class="nkrp-toggle-desc">Receive special offers and discounts.</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="nkrp-btn-primary">Save Preferences</button>
        </form>
    </div>

    <div class="nkrp-settings-card">
        <h3><span class="dashicons dashicons-lock"></span> Change Password</h3>
        <form method="POST" action="">
            <input type="hidden" name="nkrp_action" value="update_password">
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
            <button type="submit" class="nkrp-btn-primary">Update Password</button>
        </form>
    </div>

    <div class="nkrp-settings-card" style="background:#eff6ff; border-color:#bfdbfe;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
            <div>
                <h3 style="margin-top:0; color:#1e40af;"><span class="dashicons dashicons-id"></span> Switch to Candidate Account</h3>
                <p style="margin:0; font-size:13px; color:#1e40af;">Looking for a job yourself? Switch to a Candidate profile.</p>
            </div>
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to switch to a Candidate account?');">
                <input type="hidden" name="nkrp_action" value="switch_to_candidate">
                <?php wp_nonce_field('switch_role_action', 'nkrp_switch_role_nonce'); ?>
                <button type="submit" class="nkrp-btn-secondary" style="border-color:#1e40af; color:#1e40af;">Become a Candidate</button>
            </form>
        </div>
    </div>
</div>

<style>
    .nkrp-settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-settings-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; }
    .nkrp-settings-card h3 { display: flex; align-items: center; gap: 10px; margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #0f172a; }
    .nkrp-form-group { margin-bottom: 20px; }
    .nkrp-form-group label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="email"], .nkrp-form-group input[type="password"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    
    .nkrp-toggle-group { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .nkrp-toggle-label { display: block; cursor: pointer; }
    .nkrp-toggle-label input { float: left; margin-right: 10px; margin-top: 4px; }
    .nkrp-toggle-label strong { display: block; font-size: 15px; color: #0f172a; margin-bottom: 4px; }
    .nkrp-toggle-desc { display: block; font-size: 13px; color: #64748b; margin-left: 24px; }

    @media(max-width: 768px) {
        .nkrp-settings-grid { grid-template-columns: 1fr; }
    }
</style>