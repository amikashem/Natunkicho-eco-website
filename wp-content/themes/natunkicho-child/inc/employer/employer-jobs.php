<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * EMPLOYER MANAGE JOBS (Modular)
 * Shortcode: [nk_employer_jobs]
 * =========================================
 */
function nk_employer_jobs_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>Please login first.</p>';
    }

    $user_id = get_current_user_id();

    // =========================================================
    // 🚀 NEW: INTERCEPT 'EDIT' ACTION & RENDER EDIT FORM
    // =========================================================
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && ! empty( $_GET['job_id'] ) ) {
        $job_id = intval( $_GET['job_id'] );
        $job = get_post( $job_id );

        // Security Check: Make sure the logged-in user actually owns this job
        if ( $job && $job->post_author == $user_id && $job->post_type === 'job_listing' ) {
            ob_start();
            ?>
            <div class="nk-manage-jobs">
                <div class="nk-manage-header" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <h2 style="margin: 0;">Edit Job: <span style="color: #0A66C2;"><?php echo esc_html( $job->post_title ); ?></span></h2>
                    <a href="<?php echo esc_url( remove_query_arg( ['action', 'job_id'] ) ); ?>" class="nk-btn-primary" style="background: #64748b; font-size: 14px; padding: 8px 16px; border-radius: 6px; text-decoration: none; color: #fff;">
                        &larr; Back to Jobs
                    </a>
                </div>
                <div class="nk-dash-card" style="padding: 30px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <?php 
                    // This leverages WP Job Manager's native edit logic seamlessly!
                    echo do_shortcode( '[job_dashboard]' ); 
                    ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    // =========================================================
    // STANDARD DASHBOARD VIEW (List of Jobs)
    // =========================================================
    $args = [
        'post_type'      => 'job_listing',
        'post_status'    => ['publish', 'pending', 'expired'],
        'author'         => $user_id,
        'posts_per_page' => -1
    ];

    $jobs = get_posts( $args );

    ob_start();
    ?>
    <div class="nk-manage-jobs">
        <div class="nk-manage-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0;">My Posted Jobs</h2>
            <a href="/post-job/" class="nk-post-job-btn" style="background: #0A66C2; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;">
                + Post New Job
            </a>
        </div>

        <?php if ( ! empty( $jobs ) ) : ?>
            <div class="nk-manage-jobs-grid" style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ( $jobs as $job ) : ?>
                    <div class="nk-manage-job-card nk-dash-card" style="padding: 20px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                        <div>
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a;"><?php echo esc_html( $job->post_title ); ?></h3>
                            <?php 
                                // Status styling logic
                                $status = $job->post_status;
                                $status_color = '#64748b'; // Default gray
                                $status_bg = '#f1f5f9';
                                if ($status === 'publish') { $status_color = '#16a34a'; $status_bg = '#dcfce7'; }
                                elseif ($status === 'pending') { $status_color = '#d97706'; $status_bg = '#fef3c7'; }
                                elseif ($status === 'expired') { $status_color = '#dc2626'; $status_bg = '#fee2e2'; }
                            ?>
                            <p style="margin: 0; font-size: 13px; color: #64748b;">
                                Status: <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 3px 8px; border-radius: 4px; font-weight: bold; text-transform: capitalize;"><?php echo esc_html( $status ); ?></span>
                            </p>
                        </div>

                        <div class="nk-job-actions" style="display: flex; gap: 10px;">
                            <a href="<?php echo get_permalink( $job->ID ); ?>" target="_blank" class="nk-btn-view" style="padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                                View
                            </a>
                            
                            <?php 
                            // 🚀 FIX: Route natively on the same dashboard page to trigger the Edit Interceptor
                            $edit_link = add_query_arg( 
                                [ 'action' => 'edit', 'job_id' => $job->ID ], 
                                get_permalink() // Keeps the user on the dashboard page!
                            ); 
                            ?>
                            <a href="<?php echo esc_url( $edit_link ); ?>" class="nk-btn-edit" style="padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; background: #0A66C2; color: #fff; border: none;">
                                Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="nk-dash-card" style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 15px 0; color: #64748b; font-size: 15px;">You haven't posted any jobs yet.</p>
                <a href="/post-job/" class="nk-post-job-btn" style="background: #0A66C2; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;">Post Your First Job</a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode( 'nk_employer_jobs', 'nk_employer_jobs_shortcode' );