<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PHASE 5: GLOBAL NOTIFICATION & SMART ALERT ENGINE
 * Path: inc/core/notifications.php
 * Handles: UI Toasts, Email Branding, and Automated Job Matching Alerts
 * =========================================================================
 */

/**
 * 1. FRONTEND UI TOAST NOTIFICATIONS (Existing)
 */
function nk_enqueue_notification_system() {
    ?>
    <div id="nk-toast" class="nk-toast" style="display:none;"></div>
    <style>
    .nk-toast { 
        position: fixed; bottom: 20px; right: 20px; padding: 15px 25px; 
        border-radius: 8px; color: #fff; font-weight: 600; z-index: 9999; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: opacity 0.3s;
    }
    .nk-toast.success { background: #10b981; }
    .nk-toast.error { background: #ef4444; }
    </style>
    <script>
    function nk_show_toast(message, type = 'success') {
        const toast = document.getElementById('nk-toast');
        toast.textContent = message;
        toast.className = 'nk-toast ' + type;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }
    </script>
    <?php
}
add_action('wp_footer', 'nk_enqueue_notification_system');

/**
 * 2. PROFESSIONAL EMAIL BRANDING
 */
function nk_custom_wp_mail_from_name( $original_email_from ) {
    return 'NatunKicho'; 
}
add_filter( 'wp_mail_from_name', 'nk_custom_wp_mail_from_name' );

function nk_custom_wp_mail_from( $original_email_address ) {
    $domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'natunkicho.com';
    $domain = preg_replace('/^www\./', '', $domain);
    return 'noreply@' . $domain;
}
add_filter( 'wp_mail_from', 'nk_custom_wp_mail_from' );

function nk_set_html_mail_content_type() {
    return 'text/html';
}

/**
 * 3. PREMIUM HTML EMAIL TEMPLATE WRAPPER
 */
function nk_get_branded_email_html( $title, $message_body ) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo esc_html($title); ?></title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc;">
        <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            
            <div style="background-color: #0A66C2; padding: 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px;">NatunKicho</h1>
                <p style="color: #e0f2fe; margin: 5px 0 0 0; font-size: 14px;">Premium Hospitality Ecosystem</p>
            </div>
            
            <div style="padding: 40px 30px; color: #334155; line-height: 1.6; font-size: 16px;">
                <h2 style="color: #0f172a; margin-top: 0; font-size: 20px;"><?php echo esc_html($title); ?></h2>
                <?php echo $message_body; ?>
            </div>
            
            <div style="background-color: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
                <p style="margin: 0;">You are receiving this because of your account preferences on NatunKicho.</p>
                <p style="margin: 5px 0 0 0;">&copy; <?php echo date('Y'); ?> NatunKicho. All rights reserved.</p>
            </div>
            
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * 4. AUTOMATED CRON JOB SCHEDULING (PHASE 5 ENGINE)
 */
if ( ! wp_next_scheduled( 'nk_daily_smart_alerts' ) ) {
    wp_schedule_event( time(), 'daily', 'nk_daily_smart_alerts' );
}

/**
 * 5. SMART ALERT MATCHING ENGINE
 */
function nk_process_daily_smart_alerts() {
    $users = get_users([ 'meta_key' => 'nk_global_user_alerts', 'meta_compare' => 'EXISTS' ]);

    foreach ( $users as $user ) {
        $alerts = get_user_meta( $user->ID, 'nk_global_user_alerts', true );
        $is_premium = nk_is_user_premium( $user->ID ); 
        
        if ( ! is_array( $alerts ) ) continue;

        foreach ( $alerts as $alert_id => $criteria ) {
            if ( isset($criteria['frequency']) && $criteria['frequency'] === 'realtime' && !$is_premium ) {
                $criteria['frequency'] = 'daily'; 
            }

            $args = [
                'post_type'      => 'job_listing',
                'post_status'    => 'publish',
                'date_query'     => [ [ 'after' => '1 day ago' ] ],
                'posts_per_page' => 5
            ];

            if ( !empty($criteria['keyword']) && strtolower($criteria['keyword']) !== 'hospitality' ) {
                $args['s'] = $criteria['keyword'];
            }

            $matching_jobs = get_posts( $args );

            if ( ! empty( $matching_jobs ) ) {
                $title = "Your Daily Hospitality Job Matches";
                $content = "<p>Hello <strong>" . esc_html( $user->display_name ) . "</strong>,</p>";
                $content .= "<p>We found " . count($matching_jobs) . " new hospitality roles matching your profile preferences:</p>";
                $content .= '<ul style="list-style: none; padding: 0;">';
                foreach ( $matching_jobs as $job ) {
                    $company = get_post_meta( $job->ID, '_company_name', true );
                    $location = get_post_meta( $job->ID, '_job_location', true );
                    $content .= '<li style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 10px;">';
                    $content .= '<strong style="display:block; color: #0A66C2; font-size: 16px;">' . esc_html( $job->post_title ) . '</strong>';
                    $content .= '<span style="color: #475569; font-size: 14px;">' . esc_html( $company ) . ' &bull; ' . esc_html( $location ) . '</span><br>';
                    $content .= '<a href="' . get_permalink( $job->ID ) . '" style="display: inline-block; margin-top: 10px; color: #10b981; font-weight: bold; text-decoration: none;">View Job &rarr;</a></li>';
                }
                $content .= '</ul>';

                if ( ! $is_premium ) {
                    $content .= '<div style="margin-top: 25px; padding: 20px; background: #fffbeb; border: 1px dashed #fcd34d; border-radius: 8px; text-align: center;">';
                    $content .= '<p style="margin: 0 0 10px 0; color: #b45309; font-weight: bold;">Want real-time instant alerts?</p>';
                    $content .= '<p style="margin: 0 0 15px 0; font-size: 14px; color: #d97706;">Premium members receive instant notifications the second a job is posted.</p>';
                    $content .= '<a href="' . site_url('/pricing/') . '" style="display: inline-block; background: #b45309; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold;">Upgrade to Premium Pro</a></div>';
                }

                $final_html = nk_get_branded_email_html( $title, $content );
                add_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
                wp_mail( $user->user_email, $title, $final_html );
                remove_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
            }
        }
    }
}
add_action( 'nk_daily_smart_alerts', 'nk_process_daily_smart_alerts' ); 

/**
 * =========================================================================
 * SAVE USER SETTINGS (AJAX)
 * =========================================================================
 */
function nk_ajax_save_user_settings() {
    check_ajax_referer('nk_settings_nonce', 'security');
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Session expired. Please log in.' );

    $user_id = get_current_user_id();
    $pref_jobs    = isset($_POST['nk_pref_email_jobs']) ? '1' : '0';
    $pref_courses = isset($_POST['nk_pref_email_courses']) ? '1' : '0';
    $pref_public  = isset($_POST['nk_pref_cv_public']) ? '1' : '0';
    $alert_freq   = isset($_POST['nk_pref_alert_freq']) ? sanitize_text_field($_POST['nk_pref_alert_freq']) : 'daily';

    if ( $alert_freq === 'realtime' && ! nk_is_user_premium($user_id) ) {
        $alert_freq = 'daily'; 
    }

    update_user_meta( $user_id, 'nk_pref_email_jobs', $pref_jobs );
    update_user_meta( $user_id, 'nk_pref_email_courses', $pref_courses );
    update_user_meta( $user_id, 'nk_pref_cv_public', $pref_public );
    update_user_meta( $user_id, 'nk_pref_alert_freq', $alert_freq );

    wp_send_json_success( 'Preferences updated successfully.' );
}
add_action('wp_ajax_nk_save_user_settings', 'nk_ajax_save_user_settings');


/**
 * =========================================================================
 * PHASE 8.1: IN-APP NOTIFICATIONS ENGINE (New Code)
 * =========================================================================
 */

// Function to safely add a new notification to a user's account
function nk_add_in_app_notification( $user_id, $title, $message, $link = '' ) {
    $notifications = get_user_meta($user_id, 'nk_user_notifications', true);
    if (!is_array($notifications)) $notifications = [];

    $new_notif = [
        'id'      => uniqid('nk_notif_'),
        'title'   => sanitize_text_field($title),
        'message' => sanitize_textarea_field($message),
        'link'    => esc_url($link),
        'status'  => 'unread',
        'time'    => time()
    ];

    array_unshift($notifications, $new_notif);
    // Keep database light by only storing the 50 most recent notifications per user
    $notifications = array_slice($notifications, 0, 50); 
    
    update_user_meta($user_id, 'nk_user_notifications', $notifications);
}

// Function to get the unread counter for the Red Dot
function nk_get_unread_notification_count( $user_id ) {
    $notifications = get_user_meta($user_id, 'nk_user_notifications', true);
    if (!is_array($notifications)) return 0;
    
    $count = 0;
    foreach ($notifications as $n) {
        if (isset($n['status']) && $n['status'] === 'unread') $count++;
    }
    return $count;
}

// AJAX function to clear the red dot (Mark All As Read)
function nk_ajax_mark_notifications_read() {
    check_ajax_referer('nk_notif_nonce', 'security');
    if (!is_user_logged_in()) wp_send_json_error();

    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nk_user_notifications', true);

    if (is_array($notifications)) {
        foreach ($notifications as &$n) {
            $n['status'] = 'read';
        }
        update_user_meta($user_id, 'nk_user_notifications', $notifications);
    }
    wp_send_json_success();
}
add_action('wp_ajax_nk_mark_notifications_read', 'nk_ajax_mark_notifications_read');

// The UI render function for the "?tab=notifications" page
function nk_render_notifications_page() {
    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nk_user_notifications', true);
    
    // Auto-bootstrap a welcome notification if their inbox is completely empty
    if ( empty($notifications) ) {
        nk_add_in_app_notification($user_id, 'Welcome to NatunKicho!', 'Your notification center is set up. Updates on your jobs and applications will appear here.', site_url('/dashboard/'));
        $notifications = get_user_meta($user_id, 'nk_user_notifications', true);
    }

    ob_start();
    ?>
    <div class="nk-notifications-wrapper" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <p style="margin: 0; color: #64748b;">Recent alerts and platform updates.</p>
            <button id="nk-mark-read-btn" style="background: transparent; color: #0A66C2; border: 1px solid #0A66C2; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; transition: all 0.2s;">✓ Mark All as Read</button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($notifications as $n) : 
                $is_unread = ($n['status'] === 'unread');
                $bg_color = $is_unread ? '#eff6ff' : '#ffffff';
                $border_color = $is_unread ? '#bfdbfe' : '#e2e8f0';
            ?>
                <div style="background: <?php echo $bg_color; ?>; border: 1px solid <?php echo $border_color; ?>; padding: 20px; border-radius: 12px; display: flex; flex-direction: column; gap: 8px; position: relative;">
                    <?php if ($is_unread) : ?>
                        <span style="position: absolute; top: 22px; right: 20px; width: 10px; height: 10px; background: #ef4444; border-radius: 50%;"></span>
                    <?php endif; ?>
                    
                    <h4 style="margin: 0; font-size: 16px; color: #0f172a; padding-right: 20px;"><?php echo esc_html($n['title']); ?></h4>
                    <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;"><?php echo esc_html($n['message']); ?></p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                        <span style="font-size: 12px; color: #94a3b8;"><?php echo human_time_diff($n['time'], current_time('timestamp')) . ' ago'; ?></span>
                        <?php if (!empty($n['link'])) : ?>
                            <a href="<?php echo esc_url($n['link']); ?>" style="font-size: 13px; color: #0A66C2; font-weight: bold; text-decoration: none;">View Details &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const markReadBtn = document.getElementById('nk-mark-read-btn');
        if(markReadBtn) {
            markReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                markReadBtn.innerText = 'Updating...';
                
                let formData = new FormData();
                formData.append('action', 'nk_mark_notifications_read');
                formData.append('security', '<?php echo wp_create_nonce("nk_notif_nonce"); ?>');

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        window.location.reload(); // Refresh to clear the red dots
                    }
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}