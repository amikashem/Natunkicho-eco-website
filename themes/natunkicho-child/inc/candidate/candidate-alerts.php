<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * GLOBAL CANDIDATE SUBSCRIPTION & ALERT ENGINE MODULE
 * Path: inc/candidate/candidate-alerts.php
 * Shortcode: [nk_candidate_alerts]
 * =========================================================================
 */

// 1. Process Asynchronous Alert Scheduling Actions securely via AJAX
function nk_ajax_save_user_job_subscription() {
    check_ajax_referer('nk_alerts_secure_nonce', 'security');
    if (!is_user_logged_in()) {
        wp_send_json_error('Your logging session has expired.');
    }

    $user_id = get_current_user_id();
    $tier = get_user_meta($user_id, 'nk_user_tier', true);
    $current_alerts = get_user_meta($user_id, 'nk_global_user_alerts', true);
    if (!is_array($current_alerts)) {
        $current_alerts = [];
    }

    // Enforce Monetization Guardrails (Free Tier users are metered to exactly 1 alert row)
    if (empty($tier) || $tier === 'free') {
        if (count($current_alerts) >= 1) {
            wp_send_json_error('Standard Free profiles are limited to 1 active alert tracking profile. Please upgrade to Premium Pro for unlimited international routing lookups.');
        }
    }

    $alert_id = uniqid('sub_');
    $current_alerts[$alert_id] = [
        'keyword'        => sanitize_text_field($_POST['alert_keyword']),
        'category'       => sanitize_text_field($_POST['alert_category']),
        'country'        => sanitize_text_field($_POST['alert_country']),
        'frequency'      => sanitize_text_field($_POST['alert_frequency']),
        'include_blogs'  => isset($_POST['include_blogs']) && $_POST['include_blogs'] === '1' ? 1 : 0,
        'include_serv'   => isset($_POST['include_serv']) && $_POST['include_serv'] === '1' ? 1 : 0,
        'created_date'   => current_time('mysql')
    ];

    update_user_meta($user_id, 'nk_global_user_alerts', $current_alerts);
    wp_send_json_success('Your career notification alert channel has been successfully updated.');
}
add_action('wp_ajax_nk_save_user_job_subscription', 'nk_ajax_save_user_job_subscription');

// 2. Unsubscribe / Remove Alert Track Handler Matrix
function nk_ajax_delete_user_job_subscription() {
    check_ajax_referer('nk_alerts_secure_nonce', 'security');
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized process execution block.');
    }

    $user_id = get_current_user_id();
    $alert_id = sanitize_key($_POST['alert_id']);
    $current_alerts = get_user_meta($user_id, 'nk_global_user_alerts', true);

    if (is_array($current_alerts) && isset($current_alerts[$alert_id])) {
        unset($current_alerts[$alert_id]);
        update_user_meta($user_id, 'nk_global_user_alerts', $current_alerts);
        wp_send_json_success('Successfully unsubscribed from channel.');
    }
    wp_send_json_error('Target configuration item not found.');
}
add_action('wp_ajax_nk_delete_user_job_subscription', 'nk_ajax_delete_user_job_subscription');

// 3. Render Frontend Controls Component Layout Interface Card
function nk_candidate_alerts_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="nk-dash-card"><p>Please log into your candidate profile to manage matching preferences.</p></div>';
    }

    $user_id = get_current_user_id();
    $tier = get_user_meta($user_id, 'nk_user_tier', true);
    $alerts = get_user_meta($user_id, 'nk_global_user_alerts', true);
    if (!is_array($alerts)) {
        $alerts = [];
    }

    ob_start();
    ?>
    <div class="nk-dash-card nk-alerts-preference-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="margin:0; font-weight:800; color:#1e293b;">🎯 Countrywise &amp; Categorywise Notification Hub</h3>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:14px;">Set up cross-ecosystem alerts for new vacancies, professional blogs, training content, and platform services.</p>
            </div>
            <span style="padding:6px 14px; border-radius:50px; font-size:11px; font-weight:700; text-transform:uppercase; background:<?php echo ($tier === 'premium') ? '#fef3c7; color:#d97706;' : '#f1f5f9; color:#475569;'; ?>">
                Tier Level: <?php echo esc_html($tier ? $tier : 'Standard Free Profile'); ?>
            </span>
        </div>

        <!-- NEW ALERT SETUP PREFERENCE CONTAINER -->
        <form id="nk-subscription-alerts-form" style="background:#f8fafc; border:1px solid #e2e8f0; padding:20px; border-radius:12px; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:15px; margin-bottom:25px;">
            <input type="hidden" id="nk_alerts_matrix_nonce" value="<?php echo wp_create_nonce('nk_alerts_secure_nonce'); ?>">
            
            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Target Job Keyword</label>
                <input type="text" id="alert_keyword" placeholder="e.g. Pastry Chef, Barista, Hotel Manager" style="width:100%; height:44px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box;" required>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Hospitality Specialization Sector</label>
                <select id="alert_category" style="width:100%; height:44px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box;">
                    <option value="">All Industry Sectors</option>
                    <option value="hotel-resort">Hotel & Resort</option>
                    <option value="aviation-catering">Aviation & Catering</option>
                    <option value="restaurant-catering">Restaurant & Catering</option>
                    <option value="cruise-ship-management">Cruise Ship & Management</option>
                </select>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Target Location Country</label>
                <input type="text" id="alert_country" placeholder="e.g. United Kingdom, United Arab Emirates" style="width:100%; height:44px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box;" required>
            </div>

            <div>
                <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#334155;">Digest Email Frequency</label>
                <select id="alert_frequency" style="width:100%; height:44px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box;">
                    <option value="daily">Daily Dynamic Digest</option>
                    <option value="weekly">Weekly Dynamic Digest</option>
                </select>
            </div>

            <!-- CROSS CONTENT EXTENSION FIELD SYSTEM MAP -->
            <div style="grid-column:1/-1; display:flex; flex-wrap:wrap; gap:25px; background:#ffffff; border:1px solid #e2e8f0; padding:12px 15px; border-radius:8px;">
                <label style="font-weight:600; font-size:13px; color:#334155; display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" id="alert_include_blogs" checked value="1"> Include New Hospitality Blogs, Recipes &amp; Training content matches
                </label>
                <label style="font-weight:600; font-size:13px; color:#334155; display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" id="alert_include_services" checked value="1"> Include Digital Career Services Alerts (AI CV improvements, rollout tools)
                </label>
            </div>

            <div style="grid-column:1/-1; text-align:right;">
                <button type="submit" class="nk-btn-primary" style="height:44px; padding:0 25px; border:none; border-radius:8px; cursor:pointer; font-weight:700;">Save Subscription Settings</button>
            </div>
        </form>

        <!-- RENDERING ACTIVE METRIC LAYERS ROWS LIST -->
        <h4 style="font-weight:700; color:#1e293b; margin:25px 0 15px; border-top:1px solid #f1f5f9; padding-top:20px;">Your Active Tracking Channels</h4>
        <div id="nk-live-alerts-list" style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($alerts)) : ?>
                <p id="nk-no-alerts-placeholder" style="color:#94a3b8; font-size:14px; margin:0;">You have no active matching subscriptions running at this time.</p>
            <?php else : ?>
                <?php foreach ($alerts as $id => $data) : ?>
                    <div class="nk-alert-tracking-row" id="sub_row_<?php echo esc_attr($id); ?>" style="display:flex; justify-content:space-between; align-items:center; padding:15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                        <div>
                            <span style="font-weight:700; color:#0A66C2; font-size:15px;">🔍 "<?php echo esc_html($data['keyword']); ?>"</span>
                            <div style="margin-top:4px; font-size:13px; color:#475569; display:flex; gap:15px; flex-wrap:wrap;">
                                <span>🗺️ Country: <strong><?php echo esc_html($data['country']); ?></strong></span>
                                <?php if (!empty($data['category'])): ?><span>📁 Sector: <strong><?php echo esc_html($data['category']); ?></strong></span><?php endif; ?>
                                <span>⏱️ Interval: <strong><?php echo esc_html($data['frequency']); ?></strong></span>
                            </div>
                            <div style="margin-top:6px; display:flex; gap:8px;">
                                <?php if (!empty($data['include_blogs'])): ?><span style="font-size:11px; font-weight:600; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px;">📚 Content Feeds Syncing</span><?php endif; ?>
                                <?php if (!empty($data['include_serv'])): ?><span style="font-size:11px; font-weight:600; background:#ecfdf5; color:#047857; padding:2px 8px; border-radius:4px;">⚙️ Career Services Engaged</span><?php endif; ?>
                            </div>
                        </div>
                        <button type="button" class="nk-unsubscribe-alert-trigger" data-id="<?php echo esc_attr($id); ?>" style="background:none; border:none; color:#ef4444; font-weight:700; cursor:pointer; font-size:13px; padding:10px;">✕ Cancel</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Front-End AJAX Orchestration Thread Interceptor Link -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nk-subscription-alerts-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerText = 'Syncing Matrix Channels...';

                let payload = new FormData();
                payload.append('action', 'nk_save_user_job_subscription');
                payload.append('alert_keyword', document.getElementById('alert_keyword').value);
                payload.append('alert_category', document.getElementById('alert_category').value);
                payload.append('alert_country', document.getElementById('alert_country').value);
                payload.append('alert_frequency', document.getElementById('alert_frequency').value);
                payload.append('include_blogs', document.getElementById('alert_include_blogs').checked ? '1' : '0');
                payload.append('include_serv', document.getElementById('alert_include_services').checked ? '1' : '0');
                payload.append('security', document.getElementById('nk_alerts_matrix_nonce').value);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: payload })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        window.location.reload();
                    } else {
                        alert(resData.data);
                        btn.disabled = false;
                        btn.innerText = 'Save Subscription Settings';
                    }
                }).catch(() => {
                    alert('Ecosystem connection failure.');
                    btn.disabled = false;
                });
            });
        }

        document.querySelectorAll('.nk-unsubscribe-alert-trigger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to permanently disable this alert pipeline tracking track?')) return;
                
                const targetId = this.getAttribute('data-id');
                let payload = new FormData();
                payload.append('action', 'nk_delete_user_job_subscription');
                payload.append('alert_id', targetId);
                payload.append('security', document.getElementById('nk_alerts_matrix_nonce').value);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: payload })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        document.getElementById('sub_row_' + targetId).remove();
                        if (document.querySelectorAll('.nk-alert-tracking-row').length === 0) {
                            document.getElementById('nk-live-alerts-list').innerHTML = '<p id="nk-no-alerts-placeholder" style="color:#94a3b8; font-size:14px; margin:0;">You have no active matching subscriptions running at this time.</p>';
                        }
                    } else {
                        alert(resData.data);
                    }
                });
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_candidate_alerts', 'nk_candidate_alerts_dashboard_shortcode');