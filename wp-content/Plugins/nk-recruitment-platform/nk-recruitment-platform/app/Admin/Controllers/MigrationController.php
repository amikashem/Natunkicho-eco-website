<?php

declare(strict_types=1);

namespace NKRecruitment\Admin\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

class MigrationController
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('admin_post_nkrp_run_migration', [$this, 'runMigration']);
    }

    public function registerAdminMenu(): void
    {
        add_submenu_page(
            'tools.php',
            'NKRP Legacy Migration',
            'NKRP Migration Tool',
            'manage_options',
            'nkrp-migration-tool',
            [$this, 'renderMigrationPage']
        );
    }

    public function renderMigrationPage(): void
    {
        $migrated = isset($_GET['migrated']) ? (int)$_GET['migrated'] : -1;
        ?>
        <div class="wrap" style="max-width: 800px; margin-top: 40px;">
            <h1 style="font-size: 28px; margin-bottom: 10px;">NK Recruitment Data Importer</h1>
            <p style="font-size: 16px; color: #475569; margin-bottom: 30px;">This tool safely extracts all jobs and companies from your old plugins (like WP Job Manager) and injects them directly into your new NKRP custom database tables.</p>
            
            <?php if ($migrated >= 0): ?>
                <div class="notice notice-success is-dismissible" style="padding: 15px; border-left-color: #22c55e; font-size: 16px;">
                    <p><strong>✅ Merge Complete!</strong> Successfully imported <strong><?php echo $migrated; ?></strong> legacy jobs into your active NKRP system.</p>
                </div>
            <?php endif; ?>

            <div style="background: #fff; padding: 30px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <h3>Ready to Merge Data?</h3>
                <p>Because these jobs are injected into the <code>wp_nkrp_jobs</code> table, they will instantly appear in your existing Admin Panel, Employer Dashboards, and existing Frontend designs automatically.</p>
                
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="nkrp_run_migration">
                    <?php wp_nonce_field('nkrp_migration_action', 'nkrp_migration_nonce'); ?>
                    <button type="submit" class="button button-primary button-hero" style="background: #2563eb; border-color: #1d4ed8; text-shadow: none;">Merge Legacy Jobs Now</button>
                </form>
            </div>
        </div>
        <?php
    }

    public function runMigration(): void
    {
        // Security Check
        if (!isset($_POST['nkrp_migration_nonce']) || !wp_verify_nonce($_POST['nkrp_migration_nonce'], 'nkrp_migration_action')) {
            wp_die('Security Check Failed');
        }
        if (!current_user_can('manage_options')) wp_die('Unauthorized');

        global $wpdb;
        $jobs_table = $wpdb->prefix . 'nkrp_jobs';
        $companies_table = $wpdb->prefix . 'nkrp_companies';

        // Fetch all legacy WPJM jobs from the old WP posts table
        $legacy_jobs = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'job_listing' AND post_status IN ('publish', 'expired', 'pending')");
        $count = 0;

        foreach ($legacy_jobs as $post) {
            $author_id = (int)$post->post_author;
            
            // 1. Upgrade User to Employer
            $user = new \WP_User($author_id);
            if ($user->exists() && !in_array('nkrp_employer', (array)$user->roles)) {
                $user->add_role('nkrp_employer');
                $user->remove_role('nkrp_candidate');
            }

            // 2. Extract Company Meta from WPJM
            $meta = get_post_meta($post->ID);
            $company_name = isset($meta['_company_name'][0]) && !empty($meta['_company_name'][0]) ? sanitize_text_field($meta['_company_name'][0]) : 'Legacy Company ' . $author_id;
            
            // 3. Prevent Duplicate Companies (Merge into existing if found)
            $company_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$companies_table} WHERE user_id = %d AND company_name = %s", $author_id, $company_name));
            
            if (!$company_id) {
                $wpdb->insert($companies_table, [
                    'user_id' => $author_id,
                    'company_name' => $company_name,
                    'company_slug' => sanitize_title($company_name) . '-' . wp_rand(1000, 9999),
                    'website' => esc_url_raw($meta['_company_website'][0] ?? ''),
                    'logo' => sanitize_text_field($meta['_company_logo'][0] ?? ''),
                    'status' => 'active',
                    'created_at' => current_time('mysql')
                ]);
                $company_id = $wpdb->insert_id;
            }

            // 4. Prevent Duplicate Jobs (Check if we already imported this)
            $job_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$jobs_table} WHERE user_id = %d AND job_title = %s AND created_at = %s", $author_id, $post->post_title, $post->post_date));
            
            if (!$job_id) {
                // Extract Job Type Taxonomies from WPJM
                $type_terms = wp_get_post_terms($post->ID, 'job_listing_type', ['fields' => 'names']);
                $job_type = !empty($type_terms) && !is_wp_error($type_terms) ? implode(', ', $type_terms) : 'Full-Time';

                $cat_terms = wp_get_post_terms($post->ID, 'job_listing_category', ['fields' => 'names']);
                $department = !empty($cat_terms) && !is_wp_error($cat_terms) ? implode(', ', $cat_terms) : '';

                // Insert into your new NKRP Jobs table!
                $wpdb->insert($jobs_table, [
                    'user_id' => $author_id,
                    'company_id' => $company_id,
                    'job_title' => sanitize_text_field($post->post_title),
                    'job_type' => sanitize_text_field($job_type),
                    'department' => sanitize_text_field($department),
                    'location' => sanitize_text_field($meta['_job_location'][0] ?? ''),
                    'city' => sanitize_text_field($meta['_job_location'][0] ?? ''),
                    'description' => wp_kses_post($post->post_content),
                    'external_apply_url' => esc_url_raw($meta['_application'][0] ?? ''),
                    'deadline' => sanitize_text_field($meta['_job_expires'][0] ?? ''),
                    'status' => $post->post_status === 'expired' ? 'inactive' : 'published',
                    'created_at' => $post->post_date
                ]);
                $count++;
            }
        }
        
        // Redirect back with success count
        wp_safe_redirect(admin_url('tools.php?page=nkrp-migration-tool&migrated=' . $count));
        exit;
    }
}