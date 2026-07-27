<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * ADVANCED APPLICANT TRACKING SYSTEM (ATS)
 * Path: inc/employer/employer-applications.php
 * Shortcode: [nk_employer_applications]
 * =========================================
 */
function nk_employer_applications_shortcode() {
    if ( ! is_user_logged_in() ) return '<p>Please login.</p>';

    $employer_id = get_current_user_id();
    $is_premium  = function_exists('nk_is_user_premium') ? nk_is_user_premium($employer_id) : false;

    // Fetch all jobs posted by this employer
    $jobs = get_posts([
        'post_type'      => 'job_listing',
        'author'         => $employer_id,
        'posts_per_page' => -1,
        'post_status'    => ['publish', 'expired']
    ]);

    ob_start();
    ?>
    <div class="nk-ats-wrapper" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="margin-bottom: 25px;">
            <h2 style="margin: 0 0 5px 0; color: #0f172a; font-size: 24px; font-weight: 800;">Applicant Tracker</h2>
            <p style="margin: 0; color: #64748b; font-size: 14px;">Review CVs, shortlist candidates, and send direct messages.</p>
        </div>

        <?php if ( empty($jobs) ) : ?>
            <div style="background: #f8fafc; padding: 40px; text-align: center; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <p style="color: #64748b; margin: 0;">You have not posted any jobs yet.</p>
            </div>
        <?php else : ?>
            
            <?php foreach ( $jobs as $job ) : 
                $applicants = get_post_meta( $job->ID, 'nk_job_applications', true );
                if ( ! is_array( $applicants ) || empty($applicants) ) continue;
            ?>
                <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                    <div style="background: #f8fafc; padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 16px; color: #0f172a;"><?php echo esc_html( $job->post_title ); ?></h3>
                        <span style="background: #eef2ff; color: #0A66C2; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;"><?php echo count($applicants); ?> Applicants</span>
                    </div>

                    <div style="padding: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <?php foreach ( array_reverse($applicants) as $candidate_id ) : 
                                $candidate = get_userdata( $candidate_id );
                                if ( ! $candidate ) continue;

                                $status = get_post_meta( $job->ID, 'nk_app_status_' . $candidate_id, true ) ?: 'pending';
                                $cv_url = get_user_meta( $candidate_id, 'nk_cv_file_url', true );
                                
                                $status_color = '#64748b'; $status_bg = '#f1f5f9';
                                if ($status === 'accepted') { $status_color = '#16a34a'; $status_bg = '#dcfce7'; }
                                elseif ($status === 'rejected') { $status_color = '#dc2626'; $status_bg = '#fee2e2'; }
                            ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; flex-wrap: wrap; gap: 15px;">
                                    
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div style="width: 45px; height: 45px; background: #cbd5e1; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: #fff; font-weight: bold; font-size: 18px;">
                                            <?php echo strtoupper(substr($candidate->display_name, 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong style="display: block; font-size: 15px; color: #0f172a;"><?php echo esc_html($candidate->display_name); ?></strong>
                                            <span style="font-size: 12px; color: <?php echo $status_color; ?>; background: <?php echo $status_bg; ?>; padding: 2px 8px; border-radius: 12px; font-weight: bold; text-transform: capitalize; margin-top: 4px; display: inline-block;">
                                                <?php echo $status === 'accepted' ? 'Shortlisted' : $status; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <?php if($cv_url): ?>
                                            <a href="<?php echo esc_url($cv_url); ?>" target="_blank" style="padding: 8px 12px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none;">📄 CV</a>
                                        <?php endif; ?>

                                        <?php if ($is_premium): ?>
                                            <a href="<?php echo esc_url(site_url('/dashboard/?tab=messages&chat=' . $candidate_id)); ?>" style="padding: 8px 12px; background: #eef2ff; color: #0A66C2; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none;">💬 Message</a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="padding: 8px 12px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none;" title="Premium Feature">🔒 Message</a>
                                        <?php endif; ?>

                                        <?php if ($status === 'pending') : ?>
                                            <button class="nk-ats-btn accept" data-job="<?php echo $job->ID; ?>" data-cand="<?php echo $candidate_id; ?>" style="padding: 8px 12px; background: #10b981; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer;">Shortlist</button>
                                            <button class="nk-ats-btn reject" data-job="<?php echo $job->ID; ?>" data-cand="<?php echo $candidate_id; ?>" style="padding: 8px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer;">Reject</button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nk-ats-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const isAccept = this.classList.contains('accept');
                const actionText = isAccept ? 'Shortlist' : 'Reject';
                const statusStr = isAccept ? 'accepted' : 'rejected';
                
                if (confirm(`Are you sure you want to ${actionText} this candidate?`)) {
                    this.innerText = '...';
                    this.disabled = true;

                    let fd = new FormData();
                    fd.append('action', 'nk_manage_app_status');
                    fd.append('job_id', this.getAttribute('data-job'));
                    fd.append('candidate_id', this.getAttribute('data-cand'));
                    fd.append('status', statusStr);
                    fd.append('security', '<?php echo wp_create_nonce("nk_manage_app_nonce"); ?>');

                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            if (isAccept) {
                                if(confirm('Candidate Shortlisted successfully! Would you like to send them a direct message now to schedule an interview?')) {
                                    window.location.href = '?tab=messages&chat=' + this.getAttribute('data-cand');
                                    return;
                                }
                            }
                            window.location.reload();
                        } else {
                            alert(data.data || 'Failed to update status.');
                            this.innerText = actionText;
                            this.disabled = false;
                        }
                    });
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_employer_applications', 'nk_employer_applications_shortcode' );