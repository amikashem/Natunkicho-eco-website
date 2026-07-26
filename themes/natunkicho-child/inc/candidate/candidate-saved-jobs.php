<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * SAVED JOBS DASHBOARD PAGE
 * Shortcode: [nk_saved_jobs]
 */
function nk_saved_jobs_page_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>Please login to view saved jobs.</p>';
    }

    $user_id = get_current_user_id();
    $saved_jobs = get_user_meta( $user_id, 'nk_saved_jobs', true );

    ob_start();
    ?>
    <div class="nk-saved-jobs-dashboard nk-dash-card">
        <div class="nk-manage-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h2 style="margin: 0;">Saved Jobs</h2>
            <p style="margin: 5px 0 0 0; color: #666;">Track your favorite hospitality opportunities.</p>
        </div>

        <?php if ( empty( $saved_jobs ) || ! is_array( $saved_jobs ) ) : ?>
            <div class="nk-notice" style="background: #f8fafc; padding: 20px; text-align: center; border-radius: 8px;">
                <p style="margin: 0;">You haven't saved any jobs yet.</p>
                <a href="/jobs/" class="nk-post-job-btn" style="display: inline-block; margin-top: 15px;">Browse Jobs</a>
            </div>
        <?php else : 
            $args = [
                'post_type'      => 'job_listing',
                'post__in'       => $saved_jobs,
                'posts_per_page' => -1,
                'orderby'        => 'post__in'
            ];
            $jobs = get_posts( $args );
        ?>
            <div class="nk-global-jobs-grid nk-force-grid">
                <?php foreach ( $jobs as $job ) : ?>
                    <div class="nk-global-job-card" style="border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <span class="nk-job-source" style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Saved</span>
                        
                        <h3 style="margin: 10px 0 5px 0;">
                            <a href="<?php echo get_permalink( $job->ID ); ?>" style="text-decoration: none; color: #1e293b;">
                                <?php echo esc_html( $job->post_title ); ?>
                            </a>
                        </h3>
                        
                        <p class="location" style="color: #64748b; font-size: 14px; margin-bottom: 15px;">
                            <?php echo esc_html( get_post_meta( $job->ID, '_job_location', true ) ); ?>
                        </p>
                        
                        <div class="nk-job-actions" style="display: flex; gap: 10px; align-items: center;">
                            <a class="nk-btn-primary" href="<?php echo get_permalink( $job->ID ); ?>" style="background: #0056b3; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px;">View Job</a>
                            <?php echo do_shortcode('[nk_save_job id="' . $job->ID . '"]'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_saved_jobs', 'nk_saved_jobs_page_shortcode' );