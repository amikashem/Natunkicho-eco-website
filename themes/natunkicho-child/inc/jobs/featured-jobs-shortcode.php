<?php
if (!defined('ABSPATH')) exit;

function nk_featured_jobs_shortcode() {
    ob_start();
    
    // Fetch unified jobs (empty search terms to get the latest general jobs)
    // We use @ to suppress any strict API warnings during home page load
    $all_jobs = nk_get_unified_jobs('', '');
    
    // Limit to the top 6 jobs so the home page doesn't get too long
    $featured_jobs = array_slice($all_jobs, 0, 6);
    ?>
    <div class="nk-featured-jobs-container">
        
        <div class="nk-section-header">
            <h2>Featured Opportunities</h2>
            <p>Discover the latest roles from top hospitality brands around the world.</p>
        </div>

        <div class="nk-featured-jobs-grid">
            <?php
            if (!empty($featured_jobs)) {
                foreach ($featured_jobs as $job) {
                    // Reuse our beautiful standardized job card!
                    nk_render_job_card($job);
                }
            } else {
                echo '<p style="text-align:center; width:100%;">No jobs found at the moment.</p>';
            }
            ?>
        </div>

        <div class="nk-view-all-wrapper">
            <a href="<?php echo esc_url(home_url('/find-jobs/')); ?>" class="nk-btn-primary nk-btn-large">View All Jobs</a>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_featured_jobs', 'nk_featured_jobs_shortcode');