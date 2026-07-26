<?php

declare(strict_types=1);

namespace NKRecruitment\Core;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class UrlRouter
{
    public function register(): void
    {
        add_action('init', [$this, 'addRewriteRules'], 9999);
        add_filter('query_vars', [$this, 'addQueryVars']);
        
        // 🔥 REGISTRATION: Forces WordPress to load our custom plugin template file
        add_filter('template_include', [$this, 'loadVirtualTemplate'], 99);
        
        add_action('template_redirect', [$this, 'enforceSeoUrls']);
        add_action('template_redirect', [$this, 'routeUniversalDashboard']);
    }
    
    /**
     * The Universal Workspace Router
     */
    
    public function routeUniversalDashboard(): void
    {
        if (is_page('dashboard') && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $workspace = get_user_meta($user_id, '_nkrp_active_workspace', true);
            
            // If they don't have a workspace set, guess based on their primary role
            if (empty($workspace)) {
                $user = wp_get_current_user();
                if (in_array('nkrp_employer', (array) $user->roles)) {
                    $workspace = 'employer';
                } else {
                    $workspace = 'candidate';
                }
            }

            // Route them instantly!
            if ($workspace === 'employer') {
                wp_safe_redirect(home_url('/employer-dashboard/'));
                exit;
            } else {
                wp_safe_redirect(home_url('/candidate-dashboard/'));
                exit;
            }
        }
    }

    public function addRewriteRules(): void
    {
        add_rewrite_rule('^job/([^/]+)/?$', 'index.php?job_slug=$matches[1]', 'top');
        add_rewrite_rule('^company/([^/]+)/?$', 'index.php?company_slug=$matches[1]', 'top');
    }

    public function addQueryVars(array $vars): array
    {
        $vars[] = 'job_slug';
        $vars[] = 'company_slug';
        return $vars;
    }

    /**
     * Registers and returns our plugin's template file when a job slug is present.
     */
    public function loadVirtualTemplate($template)
    {
        $job_slug = get_query_var('job_slug');
        
        if (!empty($job_slug)) {
            global $wpdb;
            $table = DatabaseManager::table('jobs');
            
            try {
                $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE job_slug = %s LIMIT 1", $job_slug));
                
                if ($exists) {
                    $custom_template = NKRP_PLUGIN_PATH . 'templates/public/single-job-page.php';
                    if (file_exists($custom_template)) {
                        return $custom_template; // Successfully registered and loaded!
                    }
                }
            } catch (\Throwable $e) {
                error_log('NKRP Template Include Error: ' . $e->getMessage());
            }
        }

        return $template;
    }

    public function enforceSeoUrls(): void
    {
        if (is_page('job-details') && isset($_GET['id']) && empty(get_query_var('job_slug'))) {
            global $wpdb;
            $job_id = (int) $_GET['id'];
            $table = DatabaseManager::table('jobs');
            $job = $wpdb->get_row($wpdb->prepare("SELECT job_slug, job_title FROM {$table} WHERE id = %d", $job_id));
            
            if ($job) {
                $slug = $job->job_slug;
                if (empty($slug) && !empty($job->job_title)) {
                    $slug = sanitize_title($job->job_title) . '-' . $job_id;
                    $wpdb->update($table, ['job_slug' => $slug], ['id' => $job_id]); 
                }
                if (!empty($slug)) {
                    wp_redirect(home_url('/job/' . $slug . '/'), 301);
                    exit;
                }
            }
        }
    }

    public function dynamicTitle(string $title): string
    {
        global $wpdb;
        $job_slug = get_query_var('job_slug');
        if (!empty($job_slug)) {
            $table = DatabaseManager::table('jobs');
            $job_title = $wpdb->get_var($wpdb->prepare("SELECT job_title FROM {$table} WHERE job_slug = %s", $job_slug));
            if ($job_title) return $job_title . ' - ' . get_bloginfo('name');
        }
        return $title;
    }

    public function dynamicTitleParts(array $title): array
    {
        global $wpdb;
        $job_slug = get_query_var('job_slug');
        if (!empty($job_slug)) {
            $table = DatabaseManager::table('jobs');
            $job_title = $wpdb->get_var($wpdb->prepare("SELECT job_title FROM {$table} WHERE job_slug = %s", $job_slug));
            if ($job_title) $title['title'] = $job_title;
        }
        return $title;
    }

    public function injectDynamicSEO(): void
    {
        global $wpdb;
        $job_slug = get_query_var('job_slug');
        if (!empty($job_slug)) {
            $table = DatabaseManager::table('jobs');
            $compTable = DatabaseManager::table('companies');
            $job = $wpdb->get_row($wpdb->prepare("SELECT j.job_title, j.description, c.company_name, c.logo FROM {$table} j LEFT JOIN {$compTable} c ON j.company_id = c.id WHERE j.job_slug = %s", $job_slug));

            if ($job) {
                $desc = wp_trim_words(strip_tags(stripslashes((string)$job->description)), 25, '...');
                $logo_url = is_numeric($job->logo) ? wp_get_attachment_image_url((int)$job->logo, 'large') : $job->logo;
                if (empty($logo_url)) $logo_url = get_site_icon_url();
                
                echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
                echo '<meta property="og:title" content="' . esc_attr($job->job_title . ' at ' . $job->company_name) . '" />' . "\n";
                echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
                echo '<meta property="og:type" content="website" />' . "\n";
                echo '<meta property="og:image" content="' . esc_url((string)$logo_url) . '" />' . "\n";
            }
        }
    }
}