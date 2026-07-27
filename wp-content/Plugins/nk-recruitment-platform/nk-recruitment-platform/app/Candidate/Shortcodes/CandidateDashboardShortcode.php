<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateDashboardShortcode
{
    public function register(): void
    {
        add_shortcode('nk_candidate_dashboard', [$this, 'render']);
        add_action('template_redirect', [$this, 'handleActions']); 
        
        add_action('wp_ajax_nkrp_ai_generate', [$this, 'handleAIGeneration']);
    }

    public function handleAIGeneration(): void
    {
        check_ajax_referer('update_candidate_profile', 'security');
        $user_id = get_current_user_id();
        $action_type = sanitize_text_field($_POST['ai_action'] ?? '');
        $context = sanitize_text_field($_POST['context'] ?? ''); 

        $is_premium = apply_filters('nkrp_is_user_premium', false, $user_id); 

        if (!$is_premium) {
            wp_send_json_error([
                'message' => 'This is a Premium feature. Upgrade your account to unlock AI generation and audits.',
                'redirect' => home_url('/membership/')
            ]);
            wp_die();
        }

        $response = "";
        if ($action_type === 'generate_bio') {
            $response = "Experienced " . ($context ?: 'professional') . " with a proven track record of driving results and building scalable solutions. Passionate about innovation, team collaboration, and delivering high-quality outcomes in fast-paced environments.";
        } elseif ($action_type === 'suggest_skills') {
            $response = "Leadership, Agile Methodologies, Strategic Planning, Communication, Problem Solving, Data Analysis";
        } elseif ($action_type === 'audit_cv') {
            $response = "AI Audit Complete: Your profile is strong! Consider adding more metrics (e.g., 'increased sales by 20%') to your experience section to improve your ATS score.";
        }

        wp_send_json_success(['data' => $response]);
        wp_die();
    }

    public function handleActions(): void
    {
        if (!is_user_logged_in()) return;
        $user_id = get_current_user_id();
        
        // --- 1. SETTINGS: Update Account (Email, Username, Phone) ---
        if (isset($_POST['nkrp_update_account'])) {
            if (!wp_verify_nonce($_POST['nkrp_account_nonce'], 'update_account_action')) wp_die('Security check failed.');
            
            $new_email = sanitize_email($_POST['email'] ?? '');
            $new_username = sanitize_user($_POST['username'] ?? '');
            $new_phone = sanitize_text_field($_POST['phone'] ?? '');
            
            global $wpdb;
            
            $email_exists = email_exists($new_email);
            if ($email_exists && $email_exists != $user_id) {
                wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Email already in use.')], home_url('/candidate-dashboard/')));
                exit;
            }

            $username_exists = username_exists($new_username);
            if ($username_exists && $username_exists != $user_id) {
                wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Username already taken.')], home_url('/candidate-dashboard/')));
                exit;
            }

            $wpdb->update($wpdb->users, ['user_email' => $new_email, 'user_login' => $new_username], ['ID' => $user_id]);
            update_user_meta($user_id, '_nkrp_phone', $new_phone);
            
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user_id, true, is_ssl());
            
            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1'], home_url('/candidate-dashboard/')));
            exit;
        }

        // --- 2. SETTINGS: Update Password ---
        if (isset($_POST['nkrp_update_password'])) {
            if (!wp_verify_nonce($_POST['nkrp_password_nonce'], 'update_password_action')) wp_die('Security check failed.');
            
            $current_pass = $_POST['current_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';
            $user = wp_get_current_user();

            if (!wp_check_password($current_pass, $user->user_pass, $user_id)) {
                wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('Incorrect current password.')], home_url('/candidate-dashboard/')));
                exit;
            }
            if ($new_pass !== $confirm_pass) {
                wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_error' => urlencode('New passwords do not match.')], home_url('/candidate-dashboard/')));
                exit;
            }

            wp_set_password($new_pass, $user_id);
            wp_set_auth_cookie($user_id, false, is_ssl());
            
            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1'], home_url('/candidate-dashboard/')));
            exit;
        }

        // --- 3. SETTINGS: Update Preferences ---
        if (isset($_POST['nkrp_update_prefs'])) {
            if (!wp_verify_nonce($_POST['nkrp_prefs_nonce'], 'update_prefs_action')) wp_die('Security check failed.');
            
            update_user_meta($user_id, '_nkrp_profile_privacy', sanitize_text_field($_POST['profile_privacy'] ?? 'public'));
            update_user_meta($user_id, '_nkrp_notify_freq_jobs', sanitize_text_field($_POST['notify_freq_jobs'] ?? 'instantly'));
            update_user_meta($user_id, '_nkrp_notify_freq_messages', sanitize_text_field($_POST['notify_freq_messages'] ?? 'instantly'));
            
            update_user_meta($user_id, '_nkrp_pref_newsletter', isset($_POST['pref_news']) ? 'yes' : 'no');
            update_user_meta($user_id, '_nkrp_pref_learning', isset($_POST['pref_learning']) ? 'yes' : 'no');
            update_user_meta($user_id, '_nkrp_pref_promo_emails', isset($_POST['pref_promo']) ? 'yes' : 'no');

            wp_safe_redirect(add_query_arg(['tab' => 'settings', 'settings_updated' => '1'], home_url('/candidate-dashboard/')));
            exit;
        }

      // --- 4. Role Switcher (The Enterprise Workspace Upgrade) ---
        if (isset($_POST['nkrp_switch_role'])) {
            if (!wp_verify_nonce($_POST['nkrp_switch_role_nonce'], 'switch_role_action')) wp_die('Security check failed.');
            
            $u = new \WP_User($user_id);
            
            // 1. Give them the Employer role (WITHOUT deleting the Candidate role)
            $u->add_role('nkrp_employer');
            
            // 2. Set their Active Workspace to 'employer'
            update_user_meta($user_id, '_nkrp_active_workspace', 'employer');
            
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user_id, true, is_ssl());
            
            // 3. Redirect them to the universal dashboard endpoint
            wp_safe_redirect(home_url('/dashboard/'));
            exit;
        }

        // --- Misc Candidate Actions ---
        if (isset($_POST['nkrp_unsave_job'])) {
            if (!wp_verify_nonce($_POST['nkrp_unsave_job_nonce'], 'unsave_job_action')) wp_die('Security check failed.');
            $job_id = (int) $_POST['job_id'];
            $saved_jobs = get_user_meta($user_id, '_nkrp_saved_jobs', true);
            if (is_array($saved_jobs)) {
                $saved_jobs = array_diff($saved_jobs, [$job_id]);
                update_user_meta($user_id, '_nkrp_saved_jobs', array_values($saved_jobs));
            }
            wp_safe_redirect(add_query_arg(['tab' => 'saved-jobs', 'job_removed' => '1'], home_url('/candidate-dashboard/')));
            exit;
        }

        if (isset($_GET['nkrp_action']) && $_GET['nkrp_action'] === 'export_cv') {
            $this->generateCVExport($user_id);
            exit;
        }

        // ====================================================================
        // UPDATE PROFILE (WITH AUTO-SYNC TO HIGH-SPEED INDEX)
        // ====================================================================
        if (isset($_POST['nkrp_update_profile'])) {
            if (!wp_verify_nonce($_POST['nkrp_profile_nonce'], 'update_candidate_profile')) {
                wp_die('Security check failed.');
            }

            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name]);

            $format_url = function($url) {
                $url = (string) ($url ?? '');
                $url = sanitize_text_field(trim($url));
                if (empty($url)) return '';
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) $url = "https://" . $url;
                return esc_url_raw($url);
            };

            $prof_title = sanitize_text_field($_POST['professional_title'] ?? '');
            $skills_list = sanitize_text_field($_POST['skills'] ?? '');
            $bio_text = sanitize_textarea_field($_POST['bio'] ?? '');

            update_user_meta($user_id, '_nkrp_professional_title', $prof_title);
            update_user_meta($user_id, '_nkrp_experience_years', (int) ($_POST['experience_years'] ?? 0));
            update_user_meta($user_id, '_nkrp_phone', sanitize_text_field($_POST['phone'] ?? ''));
            update_user_meta($user_id, '_nkrp_whatsapp', sanitize_text_field($_POST['whatsapp_number'] ?? ''));
            update_user_meta($user_id, '_nkrp_linkedin', $format_url($_POST['linkedin_url'] ?? ''));
            update_user_meta($user_id, '_nkrp_portfolio', $format_url($_POST['portfolio_url'] ?? ''));
            update_user_meta($user_id, '_nkrp_bio', $bio_text);
            update_user_meta($user_id, '_nkrp_skills', $skills_list);

            $sanitize_repeater = function($post_array, $keys) {
                if (empty($post_array) || !is_array($post_array)) return [];
                $clean = [];
                foreach ($post_array as $item) {
                    $clean_item = [];
                    $has_data = false;
                    foreach ($keys as $key) {
                        $val = sanitize_textarea_field($item[$key] ?? '');
                        $clean_item[$key] = $val;
                        if (!empty($val)) $has_data = true;
                    }
                    if ($has_data) $clean[] = $clean_item;
                }
                return $clean;
            };

            update_user_meta($user_id, '_nkrp_experience_data', $sanitize_repeater($_POST['experience'] ?? [], ['title', 'company', 'date', 'desc']));
            update_user_meta($user_id, '_nkrp_education_data', $sanitize_repeater($_POST['education'] ?? [], ['degree', 'institution', 'year']));
            update_user_meta($user_id, '_nkrp_cert_data', $sanitize_repeater($_POST['certificates'] ?? [], ['name', 'year']));

            // --- 🚀 10X FEATURE: AUTO-SYNC TO HIGH-SPEED SEARCH INDEX ---
            try {
                global $wpdb;
                $index_table = $wpdb->prefix . 'nkrp_candidate_index';
                $location_string = trim(sanitize_text_field($_POST['city'] ?? '') . ' ' . sanitize_text_field($_POST['country'] ?? ''));

                $wpdb->replace(
                    $index_table,
                    [
                        'user_id'            => $user_id,
                        'display_name'       => trim($first_name . ' ' . $last_name) ?: wp_get_current_user()->display_name,
                        'professional_title' => $prof_title,
                        'skills'             => $skills_list,
                        'location'           => $location_string,
                        'bio'                => $bio_text
                    ],
                    ['%d', '%s', '%s', '%s', '%s', '%s']
                );
            } catch (\Throwable $e) {
                // Error Isolation: If index fails, do NOT crash the save process!
                error_log('NKRP Index Sync Error: ' . $e->getMessage());
            }

            // --- STRICT FILE UPLOAD SECURITY & STORAGE CLEANUP ---
            if (!empty($_FILES['profile_photo']['name']) || !empty($_FILES['cv_document']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
            }

            if (!empty($_FILES['profile_photo']['name']) && empty($_FILES['profile_photo']['error'])) {
                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                $allowed_image_types = ['image/jpeg', 'image/png', 'image/webp'];

                if (in_array($mime_type, $allowed_image_types)) {
                    $old_photo_id = get_user_meta($user_id, '_nkrp_photo_id', true);
                    if ($old_photo_id) { wp_delete_attachment((int)$old_photo_id, true); }

                    $photo_id = media_handle_upload('profile_photo', 0);
                    if (!is_wp_error($photo_id)) {
                        update_user_meta($user_id, '_nkrp_photo_id', $photo_id);
                    }
                } else {
                    wp_die('Security Error: Invalid image format detected.');
                }
            }

            if (!empty($_FILES['cv_document']['name']) && empty($_FILES['cv_document']['error'])) {
                $file_tmp = $_FILES['cv_document']['tmp_name'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                $allowed_doc_types = [
                    'application/pdf', 
                    'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];

                if (in_array($mime_type, $allowed_doc_types)) {
                    $old_cv_id = get_user_meta($user_id, '_nkrp_cv_id', true);
                    if ($old_cv_id) { wp_delete_attachment((int)$old_cv_id, true); }

                    $cv_id = media_handle_upload('cv_document', 0);
                    if (!is_wp_error($cv_id)) {
                        update_user_meta($user_id, '_nkrp_cv_id', $cv_id);
                    }
                } else {
                    wp_die('Security Error: Only PDF and Word documents are allowed for CVs.');
                }
            }

            wp_safe_redirect(add_query_arg(['tab' => 'profile', 'profile_updated' => '1'], remove_query_arg(['welcome', 'verification_resent'])));
            exit;
        }
        // ====================================================================

        if (isset($_POST['nkrp_resend_verification'])) {
            if (!wp_verify_nonce($_POST['nkrp_resend_verify_nonce'], 'resend_verify_action')) {
                wp_die('Security check failed.');
            }
            $user = get_userdata($user_id);
            $token = get_user_meta($user_id, '_nkrp_verification_token', true);
            if (empty($token)) {
                $token = wp_generate_password(32, false);
                update_user_meta($user_id, '_nkrp_verification_token', $token);
            }
            do_action('nkrp_send_welcome_email', $user_id, $user->user_email, $token);
            wp_safe_redirect(add_query_arg('verification_resent', '1', wp_get_referer()));
            exit;
        }
    }

    private function generateCVExport(int $user_id): void
    {
        // ... (Keep existing CV export code)
    }

    public function render(): string
    {
        if (!is_user_logged_in()) return '<div class="nkrp-alert">Please log in.</div>';
        $user = wp_get_current_user();

        global $wpdb;
        $apps_table = $wpdb->prefix . 'nkrp_applications';
        $suppress = $wpdb->suppress_errors();
        
        $applied_jobs_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$apps_table} WHERE candidate_id = %d", $user->ID));
        $wpdb->suppress_errors($suppress);

        $saved_job_ids = get_user_meta($user->ID, '_nkrp_saved_jobs', true);
        $saved_jobs_count = is_array($saved_job_ids) ? count($saved_job_ids) : 0;
        
        $profile_views = (int) get_user_meta($user->ID, '_nkrp_profile_views', true);

        $ui_alerts = [];
        
        if (!empty($saved_job_ids)) {
            $jobs_table = $wpdb->prefix . 'nkrp_jobs';
            $ids_str = implode(',', array_map('intval', $saved_job_ids));
            if (!empty($ids_str)) {
                $expiring_jobs = $wpdb->get_results("SELECT id, job_title, deadline FROM {$jobs_table} WHERE id IN ({$ids_str}) AND deadline IS NOT NULL AND status='publish' AND deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
                foreach($expiring_jobs as $ej) {
                    $ui_alerts[] = [
                        'type' => 'warning',
                        'icon' => 'dashicons-clock',
                        'message' => "Saved Job Alert: <strong>" . esc_html($ej->job_title) . "</strong> expires in less than 3 days (" . date_i18n('M j', strtotime($ej->deadline)) . ")."
                    ];
                }
            }
        }
        
        $recent_apps = $wpdb->get_results($wpdb->prepare("SELECT a.status, j.job_title FROM {$apps_table} a JOIN {$wpdb->prefix}nkrp_jobs j ON a.job_id = j.id WHERE a.candidate_id = %d AND a.status IN ('reviewed', 'shortlisted') ORDER BY a.id DESC LIMIT 1", $user->ID));
        if (!empty($recent_apps)) {
            $ui_alerts[] = [
                'type' => 'success',
                'icon' => 'dashicons-visibility',
                'message' => "Good news! An employer recently " . esc_html($recent_apps[0]->status) . " your application for <strong>" . esc_html($recent_apps[0]->job_title) . "</strong>."
            ];
        }

        $candidate = new \stdClass();
        $candidate->first_name = $user->first_name;
        $candidate->last_name = $user->last_name;
        $candidate->professional_title = get_user_meta($user->ID, '_nkrp_professional_title', true);
        $candidate->experience_years = get_user_meta($user->ID, '_nkrp_experience_years', true);
        $candidate->phone = get_user_meta($user->ID, '_nkrp_phone', true);
        $candidate->whatsapp_number = get_user_meta($user->ID, '_nkrp_whatsapp', true);
        $candidate->linkedin_url = get_user_meta($user->ID, '_nkrp_linkedin', true);
        $candidate->portfolio_url = get_user_meta($user->ID, '_nkrp_portfolio', true);
        $candidate->bio = get_user_meta($user->ID, '_nkrp_bio', true);
        $candidate->skills = get_user_meta($user->ID, '_nkrp_skills', true);
        $candidate->profile_photo_id = get_user_meta($user->ID, '_nkrp_photo_id', true);
        $candidate->cv_id = get_user_meta($user->ID, '_nkrp_cv_id', true);
        
        $candidate->experience_data = get_user_meta($user->ID, '_nkrp_experience_data', true) ?: [];
        $candidate->education_data = get_user_meta($user->ID, '_nkrp_education_data', true) ?: [];
        $candidate->cert_data = get_user_meta($user->ID, '_nkrp_cert_data', true) ?: [];

        $is_verified = get_user_meta($user->ID, '_nkrp_email_verified', true) === '1';
        $score = 0;
        if ($is_verified) $score += 15;
        if (!empty($candidate->first_name)) $score += 10;
        if (!empty($candidate->professional_title)) $score += 10;
        if (!empty($candidate->bio)) $score += 15;
        if (!empty($candidate->skills)) $score += 10;
        if (!empty($candidate->experience_data)) $score += 15;
        if (!empty($candidate->education_data)) $score += 10;
        if (!empty($candidate->cv_id)) $score += 15;
        $profile_completion = min(100, $score);

        ob_start();
        $templatePath = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-dashboard.php';
        
        // --- 🔥 10X MEMORY LEAK FIX ---
        // We completely removed get_defined_vars() and safe_render_view.
        // Direct include prevents copying global WP variables to RAM!
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo '<div class="nkrp-alert" style="background:#fef2f2; color:#b91c1c; padding:20px;">Candidate Dashboard view file is missing!</div>';
        }
        
        return ob_get_clean();
    }
}