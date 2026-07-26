<?php

declare(strict_types=1);

namespace NKRecruitment\Jobs\Controllers;

use NKRecruitment\Jobs\Models\Job;
use NKRecruitment\Jobs\Services\JobService;

if (!defined('ABSPATH')) {
    exit;
}

class JobController
{
    private JobService $service;

    public function __construct()
    {
        $this->service = new JobService();
        
        add_action('init', [$this, 'handleFrontendSubmit']);
        add_action('init', [$this, 'handleMagicLinkVerification']);
        
        // 🔥 THE 10X FIX: Magic filter to temporarily unlock candidate profiles for active employers
        add_filter('nkrp_is_user_premium', [$this, 'grantApplicantAccess'], 10, 2);
    }

    /**
     * If an employer clicks "View CV" from the ATS, this validates the cryptographic
     * token. If they own the job, it bypasses the Premium Wall and unlocks the profile!
     */
    public function grantApplicantAccess($is_premium, $user_id) 
    {
        if ($is_premium) return true; 

        if (isset($_GET['app_id'], $_GET['access_token'])) {
            $app_id = (int)$_GET['app_id'];
            
            if (wp_verify_nonce($_GET['access_token'], 'view_applicant_' . $app_id)) {
                global $wpdb;
                $owner = $wpdb->get_var($wpdb->prepare("
                    SELECT j.user_id 
                    FROM {$wpdb->prefix}nkrp_applications a
                    JOIN {$wpdb->prefix}nkrp_jobs j ON a.job_id = j.id
                    WHERE a.id = %d
                ", $app_id));
                
                if ((int)$owner === (int)$user_id) {
                    return true; // Access Granted! The CV is now unlocked.
                }
            }
        }
        return $is_premium;
    }

    public function handleFrontendSubmit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['nkrp_action']) || $_POST['nkrp_action'] !== 'edit_job_submit') {
            return;
        }

        if (!isset($_POST['nkrp_edit_job_nonce']) || !wp_verify_nonce($_POST['nkrp_edit_job_nonce'], 'nkrp_edit_job_action')) {
            wp_die(__('Security check failed. Please refresh the page and try again.', 'nk-recruitment'));
        }

        $is_guest = !is_user_logged_in();
        $user_id  = get_current_user_id();

        if ($is_guest) {
            $guest_email = sanitize_email($_POST['guest_email'] ?? '');
            $guest_name  = sanitize_text_field($_POST['guest_name'] ?? 'Employer');

            if (empty($guest_email)) {
                wp_die(__('Email address is required to post a job as a guest.', 'nk-recruitment'));
            }

            $user_id = email_exists($guest_email);
            
            if (!$user_id) {
                $random_password = wp_generate_password(16, false);
                $user_id = wp_create_user($guest_email, $random_password, $guest_email);
                
                if (is_wp_error($user_id)) {
                    wp_die($user_id->get_error_message());
                }

                $user = new \WP_User($user_id);
                $user->set_role('employer');
                
                $name_parts = explode(' ', $guest_name);
                update_user_meta($user_id, 'first_name', $name_parts[0]);
                if (isset($name_parts[1])) {
                    update_user_meta($user_id, 'last_name', $name_parts[1]);
                }
            }
        }

        $posted_company = sanitize_text_field($_POST['company_id'] ?? '');
        if ($is_guest && empty($posted_company)) {
            $posted_company = sanitize_text_field($_POST['guest_company'] ?? '');
        }

        $final_company_id = 0;

        if (!empty($posted_company) && $posted_company !== '0') {
            if (is_numeric($posted_company)) {
                $final_company_id = (int) $posted_company;
            } else {
                global $wpdb;
                $company_table = $wpdb->prefix . 'nkrp_companies';
                
                $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$company_table} WHERE company_name = %s AND user_id = %d", $posted_company, $user_id));
                
                if ($existing_id) {
                    $final_company_id = (int) $existing_id;
                } else {
                    $slug = sanitize_title($posted_company);
                    if (empty($slug)) $slug = 'company-' . time();
                    
                    $fallback_email = $is_guest ? ($guest_email ?? '') : wp_get_current_user()->user_email;
                    
                    $wpdb->insert($company_table, [
                        'user_id'       => $user_id,
                        'company_name'  => $posted_company,
                        'company_slug'  => $slug,
                        'company_email' => $fallback_email,
                        'status'        => 'active',
                        'created_at'    => current_time('mysql')
                    ]);
                    $final_company_id = $wpdb->insert_id;
                }
            }
        }

        $salary_range = sanitize_text_field($_POST['salary_range'] ?? 'Negotiable');
        $salary_min = 0; 
        $salary_max = 0;
        
        if ($salary_range !== 'Negotiable') {
            $clean_range = str_replace(['+', ','], '', $salary_range);
            $parts = explode('-', $clean_range);
            $salary_min = floatval($parts[0]);
            $salary_max = isset($parts[1]) ? floatval($parts[1]) : 0;
        }

        $ext_url = sanitize_text_field(trim($_POST['external_apply_url'] ?? ''));
        if (!empty($ext_url) && !preg_match("~^(?:f|ht)tps?://~i", $ext_url)) {
            $ext_url = "https://" . $ext_url;
        }

        $job = new Job();
        $job->user_id            = $user_id; 
        $job->company_id         = $final_company_id; 
        $job->title              = sanitize_text_field($_POST['title'] ?? '');
        $job->slug               = sanitize_title($job->title);
        $job->job_type           = sanitize_text_field($_POST['job_type'] ?? '');
        $job->department         = sanitize_text_field($_POST['department'] ?? '');
        $job->location           = sanitize_text_field($_POST['location'] ?? '');
        $job->country            = sanitize_text_field($_POST['country'] ?? '');
        $job->salary_type        = sanitize_text_field($_POST['salary_type'] ?? 'Monthly'); 
        $job->salary_range       = $salary_range; 
        $job->salary_min         = $salary_min;
        $job->salary_max         = $salary_max;
        $job->vacancies          = (int) ($_POST['vacancies'] ?? 1);
        $job->deadline           = sanitize_text_field($_POST['deadline'] ?? '');
        $job->experience         = sanitize_text_field($_POST['experience'] ?? '');
        $job->education          = sanitize_text_field($_POST['education'] ?? '');
        
        $job->description        = wp_kses_post($_POST['description'] ?? '');
        $job->responsibilities   = wp_kses_post($_POST['responsibilities'] ?? '');
        $job->requirements       = wp_kses_post($_POST['requirements'] ?? '');
        
        $job->notification_email = sanitize_email($_POST['notification_email'] ?? '');
        $job->external_apply_url = esc_url_raw($ext_url);

        if ($is_guest) {
            $job->status = 'pending_verification';
            $job_id = $this->service->create($job);

            $token = wp_generate_password(32, false);
            update_user_meta($user_id, '_nkrp_magic_token', $token);
            update_user_meta($user_id, '_nkrp_pending_job', $job_id);

            $verify_url = home_url("/?nkrp_verify=1&token={$token}&email=" . urlencode($guest_email));
            $subject = "Verify and Publish your Job on NatunKicho";
            $message = "Hello,\n\nYou just submitted a job for '{$job->title}'.\n\nPlease click the secure link below to verify your email, publish the job instantly, and access your Employer Dashboard:\n\n{$verify_url}\n\nWelcome to NatunKicho!";
            wp_mail($guest_email, $subject, $message);

            wp_redirect(home_url('/post-a-job/?job_posted=success'));
            exit;
        } else {
            $existing_job_id = (int)($_POST['job_id'] ?? 0);
            $job->status = get_option('nkrp_job_moderation', 'publish'); 

            if ($existing_job_id > 0) {
                $job->id = $existing_job_id;
                
                global $wpdb;
                $owner_row = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}nkrp_jobs WHERE id = %d", $job->id));
                $owner_id = (int)($owner_row->user_id ?? 0);
                
                if ($owner_id !== $user_id && !current_user_can('manage_options')) {
                    wp_die('Unauthorized action.');
                }

                $this->service->update($job);
                wp_redirect(home_url('/employer-dashboard/?tab=jobs&job_msg=updated'));
            } else {
                $new_id = $this->service->create($job);
                
                global $wpdb;
                $slug = sanitize_title($job->title) . '-' . $new_id;
                $wpdb->update($wpdb->prefix . 'nkrp_jobs', ['job_slug' => $slug], ['id' => $new_id]);

                wp_redirect(home_url('/employer-dashboard/?tab=jobs&job_msg=created'));
            }
            exit;
        }
    }

    public function handleMagicLinkVerification(): void
    {
        if (isset($_GET['nkrp_verify'], $_GET['token'], $_GET['email'])) {
            $email = sanitize_email($_GET['email']);
            $token = sanitize_text_field($_GET['token']);
            
            $user = get_user_by('email', $email);
            
            if ($user && get_user_meta($user->ID, '_nkrp_magic_token', true) === $token) {
                delete_user_meta($user->ID, '_nkrp_magic_token');
                
                $pending_job_id = get_user_meta($user->ID, '_nkrp_pending_job', true);
                if ($pending_job_id) {
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix . 'nkrp_jobs',
                        ['status' => 'publish'], 
                        ['id' => $pending_job_id]
                    );
                    delete_user_meta($user->ID, '_nkrp_pending_job');
                }

                clean_user_cache($user->ID);
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true);

                wp_redirect(home_url('/employer-dashboard/?welcome=1&msg=job_published'));
                exit;
            } else {
                wp_die(__('Invalid or expired verification link.', 'nk-recruitment'));
            }
        }
    }

    private function getCompanies(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nkrp_companies';
        $suppress = $wpdb->suppress_errors();
        
        $companies = $wpdb->get_results("SELECT id, name AS company_name FROM {$table} ORDER BY name ASC");
        if ($wpdb->last_error) {
            $companies = $wpdb->get_results("SELECT id, company_name FROM {$table} ORDER BY company_name ASC");
        }
        $wpdb->suppress_errors($suppress);
        
        return $companies ?: [];
    }

    public function jobList(): void
    {
        if (isset($_GET['approve_job']) && current_user_can('manage_options')) {
            $approve_id = (int)$_GET['approve_job'];
            $this->service->bulkUpdateStatus([$approve_id], 'publish'); 
            wp_redirect(admin_url('admin.php?page=nkrp-jobs&msg=approved'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['job_ids'])) {
            check_admin_referer('bulk-jobs', 'bulk_jobs_nonce');
            
            $action = sanitize_text_field($_POST['action'] ?? '-1');
            $job_ids = array_map('intval', $_POST['job_ids']);

            if (in_array($action, ['publish', 'draft', 'closed', 'pending', 'pending_verification'])) {
                $this->service->bulkUpdateStatus($job_ids, $action);
                wp_redirect(admin_url('admin.php?page=nkrp-jobs&msg=updated'));
                exit;
            } elseif ($action === 'trash') {
                $this->service->bulkDelete($job_ids);
                wp_redirect(admin_url('admin.php?page=nkrp-jobs&msg=deleted'));
                exit;
            }
        }

        $search = sanitize_text_field($_GET['s'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $paged  = max(1, (int) ($_GET['paged'] ?? 1));
        
        $limit  = 15;
        $offset = ($paged - 1) * $limit;

        $args = ['search' => $search, 'status' => $status, 'limit' => $limit, 'offset' => $offset, 'orderby' => 'id DESC'];

        $jobs        = $this->service->getJobs($args);
        $total_jobs  = $this->service->countJobs($args);
        $total_pages = ceil($total_jobs / $limit);

        $count_all     = $this->service->countJobs(['search' => $search]);
        $count_publish = $this->service->countJobs(['search' => $search, 'status' => 'publish']);
        $count_draft   = $this->service->countJobs(['search' => $search, 'status' => 'draft']);
        $count_closed  = $this->service->countJobs(['search' => $search, 'status' => 'closed']);
        $count_pending = $this->service->countJobs(['search' => $search, 'status' => 'pending']);

        require NKRP_PLUGIN_PATH . 'app/Jobs/Views/job-list.php';
    }

    public function jobCreate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_job');

            $job = new Job();
            $job->company_id       = (int) ($_POST['company_id'] ?? 0);
            $job->title            = sanitize_text_field($_POST['title'] ?? '');
            $job->slug             = sanitize_title($job->title);
            $job->job_type         = sanitize_text_field($_POST['job_type'] ?? '');
            $job->department       = sanitize_text_field($_POST['department'] ?? '');
            $job->location         = sanitize_text_field($_POST['location'] ?? '');
            $job->country          = sanitize_text_field($_POST['country'] ?? '');
            $job->salary_min       = (float) ($_POST['salary_min'] ?? 0);
            $job->salary_max       = (float) ($_POST['salary_max'] ?? 0);
            $job->currency         = sanitize_text_field($_POST['currency'] ?? 'USD');
            $job->vacancies        = (int) ($_POST['vacancies'] ?? 1);
            $job->deadline         = sanitize_text_field($_POST['deadline'] ?? '');
            
            $job->description      = wp_kses_post($_POST['description'] ?? '');
            $job->responsibilities = wp_kses_post($_POST['responsibilities'] ?? '');
            $job->requirements     = wp_kses_post($_POST['requirements'] ?? '');
            $job->benefits         = wp_kses_post($_POST['benefits'] ?? '');
            
            $job->notification_email = sanitize_email($_POST['notification_email'] ?? '');
            $job->external_apply_url = esc_url_raw($_POST['external_apply_url'] ?? '');
            
            $job->status           = sanitize_text_field($_POST['status'] ?? 'draft');
            $job->featured         = isset($_POST['featured']) ? 1 : 0;

            $this->service->create($job);
            wp_redirect(admin_url('admin.php?page=nkrp-jobs'));
            exit;
        }

        $companies = $this->getCompanies(); 
        require NKRP_PLUGIN_PATH . 'app/Jobs/Views/job-create.php';
    }

    public function jobEdit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) wp_die(__('Invalid Job ID.', 'nk-recruitment'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_job');
            
            $job = new Job();
            $job->id               = $id;
            $job->company_id       = (int) ($_POST['company_id'] ?? 0);
            $job->title            = sanitize_text_field($_POST['title'] ?? '');
            $job->slug             = sanitize_title($job->title);
            $job->job_type         = sanitize_text_field($_POST['job_type'] ?? '');
            $job->department       = sanitize_text_field($_POST['department'] ?? ''); 
            $job->location         = sanitize_text_field($_POST['location'] ?? '');
            $job->country          = sanitize_text_field($_POST['country'] ?? '');
            $job->salary_min       = (float) ($_POST['salary_min'] ?? 0);
            $job->salary_max       = (float) ($_POST['salary_max'] ?? 0);
            $job->currency         = sanitize_text_field($_POST['currency'] ?? 'USD');
            $job->vacancies        = (int) ($_POST['vacancies'] ?? 1);
            $job->deadline         = sanitize_text_field($_POST['deadline'] ?? '');
            
            $job->description      = wp_kses_post($_POST['description'] ?? '');
            $job->responsibilities = wp_kses_post($_POST['responsibilities'] ?? '');
            $job->requirements     = wp_kses_post($_POST['requirements'] ?? '');
            $job->benefits         = wp_kses_post($_POST['benefits'] ?? '');

            $job->notification_email = sanitize_email($_POST['notification_email'] ?? '');
            $job->external_apply_url = esc_url_raw($_POST['external_apply_url'] ?? '');
            
            $job->status           = sanitize_text_field($_POST['status'] ?? 'draft');
            $job->featured         = isset($_POST['featured']) ? 1 : 0;

            $this->service->update($job);
            wp_redirect(admin_url('admin.php?page=nkrp-jobs&msg=updated'));
            exit;
        }

        $job = $this->service->find($id);
        if (!$job) wp_die(__('Job not found.', 'nk-recruitment'));

        $companies = $this->getCompanies(); 
        require NKRP_PLUGIN_PATH . 'app/Jobs/Views/job-edit.php';
    }

    public function jobDelete(): void
    {
        if (isset($_GET['id']) && current_user_can('manage_options')) {
            $id = (int) $_GET['id'];
            $this->service->delete($id);
            wp_redirect(admin_url('admin.php?page=nkrp-jobs&msg=deleted'));
            exit;
        }
    }

    public function jobSettings(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('nkrp_job_settings');

            $currency       = sanitize_text_field($_POST['default_currency'] ?? 'USD');
            $expiry         = (int) ($_POST['job_expiry_days'] ?? 30);
            $moderation     = sanitize_text_field($_POST['job_moderation'] ?? 'pending');
            $salary_privacy = sanitize_text_field($_POST['salary_privacy'] ?? 'public');

            $countries      = sanitize_textarea_field($_POST['global_countries'] ?? '');
            $departments    = sanitize_textarea_field($_POST['global_departments'] ?? '');

            update_option('nkrp_default_currency', $currency);
            update_option('nkrp_job_expiry_days', $expiry);
            update_option('nkrp_job_moderation', $moderation);
            update_option('nkrp_salary_privacy', $salary_privacy);
            
            update_option('nkrp_global_countries', $countries);
            update_option('nkrp_global_departments', $departments);

            wp_redirect(admin_url('admin.php?page=nkrp-settings&msg=updated'));
            exit;
        }

        $default_currency = get_option('nkrp_default_currency', 'USD');
        $job_expiry_days  = get_option('nkrp_job_expiry_days', 30);
        $job_moderation   = get_option('nkrp_job_moderation', 'pending');
        $salary_privacy   = get_option('nkrp_salary_privacy', 'public');
        
        $global_countries   = get_option('nkrp_global_countries', "United States\nUnited Kingdom\nCanada\nAustralia\nUnited Arab Emirates\nSaudi Arabia");
        $global_departments = get_option('nkrp_global_departments', "Management\nFood & Beverage\nHousekeeping\nFront Office\nCulinary\nHuman Resources");

        require NKRP_PLUGIN_PATH . 'app/Jobs/Views/job-settings.php';
    }
}