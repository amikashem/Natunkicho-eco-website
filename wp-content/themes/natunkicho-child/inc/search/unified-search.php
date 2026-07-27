<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/api/api-sync-manager.php';
require_once get_stylesheet_directory() . '/inc/jobs/jobs-template.php';

function nk_unified_search_shortcode() {
    $keywords = isset($_GET['keywords']) ? sanitize_text_field($_GET['keywords']) : '';
    $location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';

    // 1. Fetch ALL jobs (Internal + External) via the Sync Manager
    $all_jobs = nk_get_unified_jobs($keywords, $location);
    
    // 2. Filter to show ONLY external jobs (exclude 'Internal' source)
    $external_jobs = array_filter($all_jobs, function($job) {
        return isset($job['source']) && $job['source'] !== 'Internal';
    });
    
    // 3. Re-index array to maintain proper order (optional but recommended)
    $external_jobs = array_values($external_jobs);
    
    ob_start();
    ?>
    <div class="nk-search-results">
        <div class="nk-force-grid">
            <?php if (!empty($external_jobs)) : 
                foreach ($external_jobs as $job) : 
                    nk_render_job_card($job);
                endforeach;
            else : ?>
                <p>No external jobs found.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_unified_search', 'nk_unified_search_shortcode');