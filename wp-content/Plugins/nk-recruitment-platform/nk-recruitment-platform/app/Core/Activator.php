<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

use NKRecruitment\Database\Installer;

if (!defined('ABSPATH')) {
    exit;
}

class Activator
{
    /**
     * Fired during plugin activation.
     */
    public static function activate(): void
    {
        // 1. Create/Update Database Tables
        Installer::install();

        // 2. Track Plugin Version (From your original code)
        if (defined('NKRP_VERSION')) {
            update_option('nkrp_version', NKRP_VERSION);
        }

        // 3. Register Platform Roles
        self::registerRoles();

        // 4. Sync Existing WordPress Users into the Platform
        self::syncExistingUsers();

        // 5. Migrate WP Job Manager Data (One-Time Execution)
        self::migrateWPJobManagerData();

        // 6. Flush Rewrite Rules
        flush_rewrite_rules();
    }

    private static function registerRoles(): void
    {
        if (!get_role('nkrp_candidate')) {
            add_role('nkrp_candidate', 'Candidate', ['read' => true, 'edit_posts' => false, 'delete_posts' => false]);
        }
        if (!get_role('nkrp_employer')) {
            add_role('nkrp_employer', 'Employer', ['read' => true, 'edit_posts' => false, 'delete_posts' => false]);
        }
    }

    private static function syncExistingUsers(): void
    {
        // Ensure regular users become candidates
        $unsynced_users = get_users([
            'role__not_in' => ['administrator', 'nkrp_employer', 'nkrp_candidate']
        ]);

        foreach ($unsynced_users as $user) {
            $user->add_role('nkrp_candidate');
            
            if (class_exists('\NKRecruitment\Membership\Services\PermissionService')) {
                $permissionService = new \NKRecruitment\Membership\Services\PermissionService();
                $permissionService->getUserSubscription($user->ID, 'candidate');
            }
        }
    }

    /**
     * 10x MIGRATION ENGINE: Converts WP Job Manager posts to NKRP custom tables.
     */
    private static function migrateWPJobManagerData(): void
    {
        // Safety Check: Only run this once.
        if (get_option('nkrp_wpjm_migrated') === '1') {
            return;
        }

        global $wpdb;

        // 1. Check if there are any WPJM jobs to migrate
        $wpjm_jobs = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'job_listing' AND post_status IN ('publish', 'expired', 'pending')");
        
        if (empty($wpjm_jobs)) {
            // No jobs found, mark as migrated to skip future checks
            add_option('nkrp_wpjm_migrated', '1');
            return;
        }

        $companies_table = $wpdb->prefix . 'nkrp_companies';
        $jobs_table      = $wpdb->prefix . 'nkrp_jobs';

        // Cache for companies so we don't create duplicates for the same user
        $company_cache = [];

        foreach ($wpjm_jobs as $post) {
            $author_id = (int) $post->post_author;

            // Upgrade Author to Employer Role
            $user = new \WP_User($author_id);
            if ($user->exists() && !in_array('nkrp_employer', (array)$user->roles)) {
                $user->add_role('nkrp_employer');
                $user->remove_role('nkrp_candidate'); // Make sure they aren't both
                
                if (class_exists('\NKRecruitment\Membership\Services\PermissionService')) {
                    $permissionService = new \NKRecruitment\Membership\Services\PermissionService();
                    $permissionService->getUserSubscription($author_id, 'employer');
                }
            }

            // Extract WPJM Meta Data
            $meta = get_post_meta($post->ID);
            $company_name = isset($meta['_company_name'][0]) ? sanitize_text_field($meta['_company_name'][0]) : 'Legacy Company ' . $author_id;
            $company_web  = isset($meta['_company_website'][0]) ? esc_url_raw($meta['_company_website'][0]) : '';
            $company_logo = isset($meta['_company_logo'][0]) ? sanitize_text_field($meta['_company_logo'][0]) : '';
            
            $job_location = isset($meta['_job_location'][0]) ? sanitize_text_field($meta['_job_location'][0]) : '';
            $apply_url    = isset($meta['_application'][0]) ? sanitize_text_field($meta['_application'][0]) : '';
            $expiry       = isset($meta['_job_expires'][0]) ? sanitize_text_field($meta['_job_expires'][0]) : null;

            // Generate a cache key to group jobs by company for this author
            $cache_key = $author_id . '_' . sanitize_title($company_name);

            // Create the Company if it hasn't been created yet during this loop
            if (!isset($company_cache[$cache_key])) {
                $slug = sanitize_title($company_name) ?: 'company-' . time();
                
                $wpdb->insert($companies_table, [
                    'user_id'      => $author_id,
                    'company_name' => $company_name,
                    'company_slug' => $slug,
                    'website'      => $company_web,
                    'logo'         => $company_logo,
                    'status'       => 'active',
                    'created_at'   => $post->post_date
                ]);
                
                $company_cache[$cache_key] = $wpdb->insert_id;
            }

            $company_id = $company_cache[$cache_key];

            // Extract Job Type Taxonomy from WPJM
            $terms = wp_get_post_terms($post->ID, 'job_listing_type');
            $job_type = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : 'Full-Time';

            // Insert the Job into the new highly-optimized NKRP jobs table
            $wpdb->insert($jobs_table, [
                'user_id'            => $author_id,
                'company_id'         => $company_id,
                'job_title'          => $post->post_title,
                'job_type'           => $job_type,
                'employment_type'    => $job_type,
                'location'           => $job_location,
                'city'               => $job_location,
                'description'        => wp_kses_post($post->post_content),
                'external_apply_url' => filter_var($apply_url, FILTER_VALIDATE_URL) ? $apply_url : '',
                'deadline'           => $expiry,
                'status'             => $post->post_status === 'publish' ? 'published' : 'inactive',
                'created_at'         => $post->post_date
            ]);
        }

        // Flag the database so this heavy operation never runs again
        add_option('nkrp_wpjm_migrated', '1');
    }
}