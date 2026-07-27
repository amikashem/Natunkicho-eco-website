<?php

declare(strict_types=1);

namespace NKRecruitment\Employer\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class EmployerDashboardShortcode
{
    public function register(): void
    {
        add_shortcode('nk_employer_dashboard', [$this, 'render']);
        add_action('wp', [$this, 'handleActions']); 
        
        // 🔥 NEW: Register the secure AJAX endpoint for Instant ATS Sync
        add_action('wp_ajax_nk_update_ats_status', [$this, 'ajaxUpdateAtsStatus']);
        
        add_action('wp_enqueue_scripts', function() {
            global $post;
            if (is_page() && is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'nk_employer_dashboard')) {
                wp_enqueue_media();
                wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
                wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
            }
        });
    }

    /**
     * 🔥 THE DUAL-NOTIFICATION & ATS AJAX ENGINE
     */
    public function ajaxUpdateAtsStatus(): void
    {
        check_ajax_referer('nk_ats_ajax_nonce', 'security');

        if (!is_user_logged_in() || !current_user_can('nkrp_employer') && !current_user_can('employer') && !current_user_can('administrator')) {
            wp_send_json_error('Unauthorized');
        }

        $app_id = (int)$_POST['app_id'];
        $new_status = sanitize_text_field($_POST['new_status']);

        global $wpdb;
        $app_table = $wpdb->prefix . 'nkrp_applications';
        $job_table = $wpdb->prefix . 'nkrp_jobs';

        // 1. Intelligent Status Prevention (Don't downgrade a shortlisted candidate to just 'reviewed' if they click View CV again)
        $current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM {$app_table} WHERE id = %d", $app_id));
        if ($new_status === 'reviewed' && in_array($current_status, ['shortlisted', 'rejected'])) {
            wp_send_json_success(['new_status' => $current_status, 'ignored' => true]);
        }

        // 2. Update Database Instantly
        $wpdb->update($app_table, ['status' => $new_status], ['id' => $app_id]);

        // 3. Fetch Data for Notifications
        $data = $wpdb->get_row($wpdb->prepare("
            SELECT a.candidate_id, j.job_title, u.user_email, u.display_name 
            FROM {$app_table} a 
            JOIN {$job_table} j ON a.job_id = j.id 
            JOIN {$wpdb->users} u ON a.candidate_id = u.ID 
            WHERE a.id = %d
        ", $app_id));

        if ($data) {
            // Determine Contextual Wording
            $status_labels = [
                'reviewed'    => 'viewed your application for',
                'shortlisted' => 'shortlisted your application for',
                'rejected'    => 'updated the status of your application for' // Soft rejection wording
            ];
            $action_text = $status_labels[$new_status] ?? 'updated your application for';

            // --- A. IN-APP BUBBLE NOTIFICATION ---
            $alert = [
                'text' => "🏢 An employer {$action_text} {$data->job_title}.",
                'time' => current_time('mysql')
            ];
            
            $existing_alerts = get_user_meta($data->candidate_id, 'nk_user_alerts', true);
            if (!is_array($existing_alerts)) $existing_alerts = [];
            array_push($existing_alerts, $alert);
            
            if (count($existing_alerts) > 20) array_shift($existing_alerts); // Cap at 20 alerts
            update_user_meta($data->candidate_id, 'nk_user_alerts', $existing_alerts);

            // --- B. AMAZON SES / BREVO EMAIL PUSH ---
            $subject = "Application Update: {$data->job_title}";
            $body  = "<h3>Hello {$data->display_name},</h3>";
            $body .= "<p>An employer has <strong>{$action_text}</strong> the role of <strong>{$data->job_title}</strong>.</p>";
            $body .= "<p>Log in to your Candidate Dashboard to view your application stack.</p>";
            $body .= "<p><a href='" . esc_url(home_url('/candidate-dashboard/?tab=applied-jobs')) . "'>View Dashboard</a></p>";
            $body .= "<br><p>Best regards,<br>The NatunKicho Team</p>";

            if (class_exists('NK_Email_Queue')) {
                \NK_Email_Queue::enqueue($data->user_email, $data->display_name, $subject, $body, ['priority' => 'high']);
            } else {
                add_filter('wp_mail_content_type', function() { return 'text/html'; });
                wp_mail($data->user_email, $subject, $body);
                remove_filter('wp_mail_content_type', function() { return 'text/html'; });
            }
        }

        wp_send_json_success(['new_status' => $new_status]);
    }

    public function handleActions(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return; 
        
        if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            wp_die('<div style="padding:30px; text-align:center; margin:50px auto;"><h2>Upload Failed</h2><p>File is too large for your server.</p><a href="javascript:history.back()">Go Back</a></div>');
        }

        if (!is_user_logged_in()) return;
        $user_id = get_current_user_id();
        $action = sanitize_text_field($_POST['nkrp_action'] ?? '');

        if ($action === 'edit_job_submit') {
            $controller = new \NKRecruitment\Jobs\Controllers\JobController();
            $controller->handleFrontendSubmit();
            exit; 
        }

        if ($action === 'save_company') {
            if (!wp_verify_nonce($_POST['nkrp_company_nonce'], 'save_company_action')) wp_die('Security check failed.');

            global $wpdb;
            $company_table = $wpdb->prefix . 'nkrp_companies';
            $company_id = isset($_POST['company_id']) ? (int)$_POST['company_id'] : 0;

            if ($company_id > 0) {
                $existing_owner = (int) $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$company_table} WHERE id = %d", $company_id));
                if ($existing_owner !== $user_id) wp_die('Unauthorized to edit this company.');
            }

            $company_name = sanitize_text_field($_POST['company_name'] ?? '');
            if (empty($company_name)) wp_die('Company Name is required.');

            $company_data = [
                'user_id'       => $user_id,
                'company_name'  => $company_name,
                'company_email' => sanitize_email($_POST['company_email'] ?? ''),
                'phone'         => sanitize_text_field($_POST['phone'] ?? ''),
                'industry'      => sanitize_text_field($_POST['industry'] ?? ''),
                'company_size'  => sanitize_text_field($_POST['company_size'] ?? ''),
                'country'       => sanitize_text_field($_POST['country'] ?? ''),
                'city'          => sanitize_text_field($_POST['city'] ?? ''),
                'address'       => sanitize_text_field($_POST['address'] ?? ''),
                'description'   => wp_kses_post($_POST['company_description'] ?? ''), 
                'updated_at'    => current_time('mysql')
            ];

            $website = sanitize_text_field(trim($_POST['website'] ?? ''));
            if (!empty($website) && !preg_match("~^(?:f|ht)tps?://~i", $website)) $website = "https://" . $website;
            $company_data['website'] = esc_url_raw($website);

            if (!empty($_POST['founded_year'])) {
                $company_data['founded_year'] = (int)$_POST['founded_year'];
            }

            if ($company_id === 0) {
                $slug = sanitize_title($company_data['company_name']);
                if (empty($slug)) $slug = 'company-' . time();
                $base_slug = $slug;
                $counter = 1;
                while ($wpdb->get_var($wpdb->prepare("SELECT id FROM {$company_table} WHERE company_slug = %s", $slug))) {
                    $slug = $base_slug . '-' . $counter;
                    $counter++;
                }
                $company_data['company_slug'] = $slug;
                $company_data['status'] = 'active';
                $company_data['created_at'] = current_time('mysql');
            }

            if (!empty($_FILES['company_logo']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                
                $logo_id = media_handle_upload('company_logo', 0);
                if (is_wp_error($logo_id)) {
                    wp_die('<h2>Upload Error</h2><p>' . esc_html($logo_id->get_error_message()) . '</p>');
                } else {
                    $company_data['logo'] = (string)$logo_id;
                }
            }

            if ($company_id > 0) {
                $result = $wpdb->update($company_table, $company_data, ['id' => $company_id]);
                if ($result === false) wp_die('<h1>Update Failed</h1><code>' . esc_html($wpdb->last_error) . '</code>');
                $redirect_url = add_query_arg(['tab' => 'companies', 'msg' => 'updated'], home_url('/employer-dashboard/'));
            } else {
                $result = $wpdb->insert($company_table, $company_data);
                if ($result === false) wp_die('<h1>Creation Failed</h1><code>' . esc_html($wpdb->last_error) . '</code>');
                $redirect_url = add_query_arg(['tab' => 'companies', 'msg' => 'created'], home_url('/employer-dashboard/'));
            }

            wp_safe_redirect(esc_url_raw($redirect_url));
            exit;
        }

        if ($action === 'switch_to_candidate') {
            if (!wp_verify_nonce($_POST['nkrp_switch_role_nonce'], 'switch_role_action')) wp_die('Security check failed.');
            
            $u = new \WP_User($user_id);
            $u->add_role('nkrp_candidate');
            update_user_meta($user_id, '_nkrp_active_workspace', 'candidate');
            
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user_id, true, is_ssl());
            
            wp_safe_redirect(home_url('/dashboard/'));
            exit;
        }

        if ($action === 'update_account') {
            if (!wp_verify_nonce($_POST['nkrp_account_nonce'], 'update_account_action')) wp_die('Security check failed.');
            $new_email = sanitize_email($_POST['email']);
            $new_username = sanitize_user($_POST['username']);
            $new_phone = sanitize_text_field($_POST['phone']);
            global $wpdb;
            
            if (email_exists($new_email) && email_exists($new_email) != $user_id) { wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Email already in use.')], home_url('/employer-dashboard/'))); exit; }
            if (username_exists($new_username) && username_exists($new_username) != $user_id) { wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Username already taken.')], home_url('/employer-dashboard/'))); exit; }

            $wpdb->update($wpdb->users, ['user_email' => $new_email, 'user_login' => $new_username], ['ID' => $user_id]);
            update_user_meta($user_id, '_nkrp_phone', $new_phone);
            
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user_id, true, is_ssl());
            
            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1', 't' => time()], home_url('/employer-dashboard/')));
            exit;
        }

        if ($action === 'update_password') {
            if (!wp_verify_nonce($_POST['nkrp_password_nonce'], 'update_password_action')) wp_die('Security check failed.');
            $current_pass = $_POST['current_password'];
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];
            $user = wp_get_current_user();
            if (!wp_check_password($current_pass, $user->user_pass, $user_id)) { wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Incorrect current password.')], home_url('/employer-dashboard/'))); exit; }
            if ($new_pass !== $confirm_pass) { wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('New passwords do not match.')], home_url('/employer-dashboard/'))); exit; }
            wp_set_password($new_pass, $user_id);
            wp_set_auth_cookie($user_id, false, is_ssl());
            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1'], home_url('/employer-dashboard/')));
            exit;
        }

        if ($action === 'update_prefs') {
            if (!wp_verify_nonce($_POST['nkrp_prefs_nonce'], 'update_prefs_action')) wp_die('Security check failed.');
            update_user_meta($user_id, '_nkrp_pref_employer_notifications', isset($_POST['pref_employer']) ? 'yes' : 'no');
            update_user_meta($user_id, '_nkrp_pref_premium_alerts', isset($_POST['pref_premium']) ? 'yes' : 'no');
            update_user_meta($user_id, '_nkrp_pref_newsletter', isset($_POST['pref_news']) ? 'yes' : 'no');
            update_user_meta($user_id, '_nkrp_pref_promo_emails', isset($_POST['pref_promo']) ? 'yes' : 'no');
            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1'], home_url('/employer-dashboard/')));
            exit;
        }
    }

    public function render(): string
    {
        if (!is_user_logged_in()) {
            return '<div class="nkrp-alert" style="padding:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">Please <a href="' . esc_url(home_url('/login/')) . '">log in</a> to access the Employer Dashboard.</div>';
        }

        $user = wp_get_current_user();
        if (!in_array('nkrp_employer', (array) $user->roles) && !in_array('employer', (array) $user->roles)) {
            return '<div class="nkrp-alert" style="padding:20px; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:8px;"><strong>Access Denied:</strong> You must be registered as an Employer to view this dashboard.</div>';
        }

        global $wpdb;

        $company_table = $wpdb->prefix . 'nkrp_companies';
        $suppress = $wpdb->suppress_errors();
        $employer_companies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$company_table} WHERE user_id = %d ORDER BY id DESC", $user->ID));
        
        $active_jobs_count = count_user_posts($user->ID, 'nk_job', true); 
        $applications_table = $wpdb->prefix . 'nkrp_applications';
        $jobs_table = $wpdb->prefix . 'nkrp_jobs'; 
        
        $total_applications = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(a.id) FROM {$applications_table} a 
            LEFT JOIN {$jobs_table} j ON a.job_id = j.id 
            WHERE j.user_id = %d
        ", $user->ID));
        $wpdb->suppress_errors($suppress);

        $is_verified = get_user_meta($user->ID, '_nkrp_email_verified', true) === '1';

        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
        
        $edit_company = new \stdClass();
        if ($current_tab === 'edit-company' && isset($_GET['id'])) {
            $fetched = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$company_table} WHERE id = %d", (int)$_GET['id']));
            if ($fetched && (int)$fetched->user_id === $user->ID) {
                $edit_company = $fetched;
            } else {
                return '<div class="nkrp-alert nkrp-alert-error">Unauthorized or Company not found.</div>';
            }
        }

        $edit_job = new \stdClass();
        $raw_countries = ''; $countries_array = [];
        $raw_departments = ''; $departments_array = [];
        
        if ($current_tab === 'edit-job' && isset($_GET['id'])) {
            $fetchedJob = $wpdb->get_row($wpdb->prepare("SELECT *, job_title AS title FROM {$jobs_table} WHERE id = %d", (int)$_GET['id']));
            
            $job_owner = isset($fetchedJob->user_id) ? (int)$fetchedJob->user_id : 0;

            if ($fetchedJob && $job_owner === $user->ID) {
                $edit_job = $fetchedJob;
                
                $raw_countries = get_option('nkrp_global_countries', "United States\nUnited Kingdom\nCanada\nAustralia");
                $countries_array = array_filter(array_map('trim', explode("\n", $raw_countries)));
                $raw_departments = get_option('nkrp_global_departments', "Management\nFood & Beverage\nCulinary");
                $departments_array = array_filter(array_map('trim', explode("\n", $raw_departments)));
            } else {
                return '<div class="nkrp-alert nkrp-alert-error">Unauthorized or Job not found.</div>';
            }
        }

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-dashboard.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<p>Dashboard template is missing.</p>';
        }
        return ob_get_clean();
    }
}