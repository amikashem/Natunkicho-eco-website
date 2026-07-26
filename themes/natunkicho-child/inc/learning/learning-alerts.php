<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * CROSS-ECOSYSTEM BACKGROUND MATCHING & DIGEST CRON ENGINE
 * Path: inc/learning/learning-alerts.php
 * Automatically aggregates and dispatches personalized emails to candidates.
 * =========================================================================
 */

// 1. Add Custom Interval Tracking Capabilities to WP-Cron
function nk_custom_cron_schedules($schedules) {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = [
            'interval' => 604800,
            'display'  => __('Once Weekly')
        ];
    }
    return $schedules;
}
add_filter('cron_schedules', 'nk_custom_cron_schedules');

// 2. Automated Master Core Daemon Execution Hook
function nk_execute_automated_ecosystem_alerts() {
    // Gather all active candidates safely
    $candidates = get_users(['role' => 'job_seeker']);
    if (empty($candidates)) return;

    // Fetch newly added cross-ecosystem content items (Blogs, Recipes, Courses) from the past 24 hours
    $recent_content = get_posts([
        'post_type'      => ['post', 'courses'],
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'date_query'     => [['after' => '24 hours ago']]
    ]);

    foreach ($candidates as $candidate) {
        $saved_alerts = get_user_meta($candidate->ID, 'nk_global_user_alerts', true);
        if (empty($saved_alerts) || !is_array($saved_alerts)) continue;

        $jobs_payload = '';
        $content_payload = '';
        $services_payload = '';

        foreach ($saved_alerts as $alert_id => $criteria) {
            // Re-verify tracking interval rules to respect daily/weekly profiles
            // For testing configurations, the system runs daily passes automatically
            
            // Build internal vacancy queries matching candidate preferences
            $job_args = [
                'post_type'      => 'job_listing',
                'posts_per_page' => 3,
                'post_status'    => 'publish',
                'date_query'     => [['after' => '24 hours ago']],
                's'              => $criteria['keyword']
            ];

            // Filter by category slug taxonomies if selected
            if (!empty($criteria['category'])) {
                $job_args['tax_query'] = [[
                    'taxonomy' => 'job_listing_category',
                    'field'    => 'slug',
                    'terms'    => $criteria['category']
                ]];
            }

            $matched_jobs = get_posts($job_args);
            
            if (!empty($matched_jobs)) {
                foreach ($matched_jobs as $job) {
                    // Check for location country match constraints safely
                    $job_country = get_post_meta($job->ID, '_job_location', true);
                    if (empty($criteria['country']) || strpos(strtolower($job_country), strtolower($criteria['country'])) !== false) {
                        $jobs_payload .= "💼 " . esc_html($job->post_title) . " | 📍 " . esc_html($job_country) . "\n   View Listing: " . esc_url(get_permalink($job->ID)) . "\n\n";
                    }
                }
            }

            // Parse Learning Content Elements (Blogs, Recipes, Training Paths) if opted-in
            if (!empty($recent_content) && !empty($criteria['include_blogs'])) {
                foreach ($recent_content as $item) {
                    $content_payload .= "📚 [" . strtoupper($item->post_type) . "] " . esc_html($item->post_title) . "\n   Read Article: " . esc_url(get_permalink($item->ID)) . "\n\n";
                }
            }

            // Inject Digital Service Alerts (AI Tool Upgrades, CV templates) if opted-in
            if (!empty($criteria['include_serv'])) {
                // Pull active platform notifications setup by admin
                $global_notices = get_option('nk_global_platform_service_notices', '');
                if (!empty($global_notices)) {
                    $services_payload .= "⚙️ Premium Service Update:\n" . esc_html($global_notices) . "\n\n";
                }
            }
        }

        // If data matches any criteria tracks, send out the consolidated email digest
        if (!empty($jobs_payload) || !empty($content_payload) || !empty($services_payload)) {
            $to = $candidate->user_email;
            $subject = '🔔 Your Personalized Natunkicho Hospitality Digest';
            
            $body = "Hello " . esc_html($candidate->display_name) . ",\n\n";
            $body .= "Here is your updated hospitality match report matching your subscription parameters over the last 24 hours:\n\n";
            
            if (!empty($jobs_payload)) {
                $body .= "=========================================\n";
                $body .= "🔥 NEW MATCHING VACANCIES LIVE ON PLATFORM\n";
                $body .= "=========================================\n" . $jobs_payload;
            }
            
            if (!empty($content_payload)) {
                $body .= "=========================================\n";
                $body .= "🎓 FRESH TRAINING INSIGHTS, BLOGS & RECIPES\n";
                $body .= "=========================================\n" . $content_payload;
            }
            
            if (!empty($services_payload)) {
                $body .= "=========================================\n";
                $body .= "🚀 DIGITAL PLATFORM & CAREER SERVICES\n";
                $body .= "=========================================\n" . $services_payload;
            }
            
            $body .= "Modify or toggle your notification channels any time by visiting your profile settings dashboard at: " . esc_url(home_url('/profile/')) . "\n\n";
            $body .= "Thank you for being part of our network,\nThe Natunkicho Hospitality Ecosystem Core";

            wp_mail($to, $subject, $body);
        }
    }
}
add_action('nk_process_automated_digests_action', 'nk_execute_automated_ecosystem_alerts');

// 3. Register and Secure Background Task Scheduling Rules
if (!wp_next_scheduled('nk_process_automated_digests_action')) {
    wp_schedule_event(time(), 'daily', 'nk_process_automated_digests_action');
}