<?php
if (!defined('ABSPATH')) exit;

/**
 * RENDER A UNIFIED JOB CARD
 */
function nk_render_job_card($job) {
    $is_internal = ($job['source'] === 'Internal');
    
    // Normalize data with safety defaults
    $title       = !empty($job['title'])       ? $job['title']       : 'Hospitality Job';
    $company     = !empty($job['company'])     ? $job['company']     : 'Company';
    $location    = !empty($job['location'])    ? $job['location']    : 'Location';
    $url         = !empty($job['url'])         ? $job['url']         : '#';
    $source      = !empty($job['source'])      ? $job['source']      : 'Internal';
    
    // Final description sanitization
    // We strip tags to remove HTML noise and trim to 18 words
    $raw_desc    = !empty($job['description']) ? $job['description'] : 'Click "View Job" to see full responsibilities and requirements.';
    $description = wp_trim_words(strip_tags($raw_desc), 18);
    ?>
    <div class="nk-global-job-card <?php echo esc_attr(strtolower($source)); ?>">
        <span class="nk-job-source"><?php echo esc_html($source); ?></span>
        
        <h3>
            <a href="<?php echo esc_url($url); ?>" target="_blank">
                <?php echo esc_html($title); ?>
            </a>
        </h3>
        
        <p class="company"><strong><?php echo esc_html($company); ?></strong></p>
        <p class="location"><?php echo esc_html($location); ?></p>
        
        <p class="description" style="color: #555; font-size: 14px; margin: 10px 0; height: 60px; overflow: hidden;">
            <?php echo esc_html($description); ?>
        </p>
        
        <div class="nk-job-actions" style="margin-top: auto;">
            <a class="nk-btn-primary" href="<?php echo esc_url($url); ?>" target="_blank">
                <?php echo $is_internal ? 'Apply Now' : 'View Job'; ?>
            </a>
            
            <?php if ($is_internal && !empty($job['id'])) : ?>
                <?php echo do_shortcode('[nk_save_job id="' . esc_attr($job['id']) . '"]'); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}