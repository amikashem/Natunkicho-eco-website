<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * CANDIDATE APPLIED JOBS DASHBOARD
 * Path: inc/candidate/candidate-applied-jobs.php
 * Shortcode: [nk_applied_jobs]
 * =========================================
 */
function nk_applied_jobs_page_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>Please login to view applied jobs.</p>';
    }

    $user_id = get_current_user_id();
    $applied_jobs = get_user_meta( $user_id, 'nk_applied_jobs', true );
    $is_premium = nk_is_user_premium( $user_id ); 

    ob_start();
    ?>
    <div class="nk-applied-jobs-dashboard nk-dash-card">
        <div class="nk-manage-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h2 style="margin: 0;">Applied Jobs</h2>
            <p style="margin: 5px 0 0 0; color: #64748b;">Track the status of your hospitality job applications.</p>
        </div>

        <?php if ( empty( $applied_jobs ) || ! is_array( $applied_jobs ) ) : ?>
            <div class="nk-notice" style="background: #f8fafc; padding: 20px; text-align: center; border-radius: 8px;">
                <p style="margin: 0;">You haven't applied to any jobs yet.</p>
                <a href="/jobs/" class="nk-post-job-btn" style="display: inline-block; margin-top: 15px; background: #0A66C2; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px;">Browse Jobs</a>
            </div>
        <?php else : 
            $args = [
                'post_type'      => 'job_listing',
                'post__in'       => $applied_jobs,
                'posts_per_page' => -1,
                'orderby'        => 'post__in'
            ];
            $jobs = get_posts( $args );
        ?>
            <div class="nk-global-jobs-grid nk-force-grid">
                <?php foreach ( $jobs as $job ) : 
                    // Fetch the ATS Status set by the employer
                    $status = get_post_meta( $job->ID, 'nk_app_status_' . $user_id, true );
                    if ( ! $status ) $status = 'pending';
                    
                    // Grab the Employer's ID for the messaging system
                    $employer_id = $job->post_author;
                ?>
                    <div class="nk-global-job-card" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin-bottom: 15px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                        
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                            
                            <?php if ( $status === 'accepted' ) : ?>
                                <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">🎉 Shortlisted</span>
                            <?php elseif ( $status === 'rejected' ) : ?>
                                <span style="background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">Not Selected</span>
                            <?php else : ?>
                                <span style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">⏳ Under Review</span>
                            <?php endif; ?>
                            
                            <?php if ( $is_premium ) : ?>
                                <span style="background: #fef08a; color: #854d0e; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">🚀 Priority Boost Active</span>
                            <?php else : ?>
                                <a href="/pricing/" title="Push your application to the top of the employer's list!" style="background: #f8fafc; color: #475569; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-decoration: none; border: 1px dashed #cbd5e1; transition: all 0.2s;">⚡ Boost Application (Premium)</a>
                            <?php endif; ?>
                        </div>
                        
                        <h3 style="margin: 15px 0 5px 0; font-size: 18px;">
                            <a href="<?php echo get_permalink( $job->ID ); ?>" style="text-decoration: none; color: #0f172a;">
                                <?php echo esc_html( $job->post_title ); ?>
                            </a>
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #64748b;">
                            <?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?>
                        </p>
                        
                        <div class="nk-job-actions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            <a class="nk-btn-primary" href="<?php echo get_permalink( $job->ID ); ?>" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">View Job Post</a>
                            
                            <?php if ( $is_premium ) : ?>
                                <a href="<?php echo esc_url(site_url('/dashboard/?tab=messages&chat=' . $employer_id)); ?>" style="background: #10b981; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">💬 Message Employer</a>
                                
                                <a href="<?php echo esc_url(site_url('/profile/')); ?>" style="background: #0A66C2; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">Update CV</a>
                            <?php else : ?>
                                <a href="/pricing/" style="color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: none; margin-left: auto;">🔒 Message Employer (Premium)</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_applied_jobs', 'nk_applied_jobs_page_shortcode' );