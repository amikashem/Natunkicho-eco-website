<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline">Process Application: <?= esc_html($application->candidate_name) ?></h1>
    <a href="?page=nkrp-applications" class="page-title-action">Back to Pipeline</a>
    <hr class="wp-header-end">
    
    <form method="post">
        <?php wp_nonce_field('nkrp_application'); ?>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span>Application Fact Sheet (Immutable)</span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr><th>Job Applied For:</th><td><strong><?= esc_html($application->job_title) ?></strong></td></tr>
                                <tr><th>Candidate Name:</th><td><strong><?= esc_html($application->candidate_name) ?></strong></td></tr>
                                <tr><th>Hiring Company:</th><td><strong><?= esc_html($application->company_name) ?></strong></td></tr>
                                <tr><th>Applied On:</th><td><?= esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->created_at))) ?></td></tr>
                                
                                <tr>
                                    <th>Attached Resume:</th>
                                    <td>
                                        <?php if ($application->resume_id): ?>
                                            <a href="?page=nkrp-resume-edit&id=<?= esc_attr((string)$application->resume_id) ?>" class="button button-secondary" target="_blank">
                                                <span class="dashicons dashicons-media-document" style="margin-top:4px;"></span> View Candidate Resume
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#777;"><em>No formal resume attached. Candidate used Quick Apply.</em></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if(!empty($application->cover_letter)): ?>
                    <div class="postbox">
                        <h2 class="hndle"><span>Cover Letter / Pitch</span></h2>
                        <div class="inside" style="background:#f8fafc; padding:20px; border-radius:6px; font-family:Georgia, serif; font-size:15px; line-height:1.6; border: 1px solid #e2e8f0;">
                            <?= nl2br(esc_html($application->cover_letter)) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox" style="border-top:3px solid #3b82f6;">
                        <h2 class="hndle"><span class="dashicons dashicons-update-alt" style="margin-top:2px;"></span> <span>ATS Pipeline Controls</span></h2>
                        <div class="inside">
                            <p><label><strong>Current Stage:</strong></label><br>
                                <select name="status" class="widefat" style="margin-top:5px; font-weight:bold;">
                                    <option value="new" <?= selected($application->status, 'new', false) ?>>🔴 New Application</option>
                                    <option value="screening" <?= selected($application->status, 'screening', false) ?>>🟠 Screening</option>
                                    <option value="interview" <?= selected($application->status, 'interview', false) ?>>🟡 Interviewing</option>
                                    <option value="offered" <?= selected($application->status, 'offered', false) ?>>🔵 Job Offered</option>
                                    <option value="hired" <?= selected($application->status, 'hired', false) ?>>🟢 Hired / Placed</option>
                                    <option value="rejected" <?= selected($application->status, 'rejected', false) ?>>⚫ Rejected</option>
                                </select>
                            </p>
                            <p><label><strong>Candidate Rating (0-5 Stars):</strong></label><br>
                                <input type="number" name="employer_rating" value="<?= esc_attr((string)$application->employer_rating) ?>" min="0" max="5" class="widefat">
                                <span class="description">For internal shortlisting only.</span>
                            </p>
                            <p><label><strong>Internal HR Notes:</strong></label><br>
                                <textarea name="employer_notes" class="widefat" rows="5" placeholder="e.g., Called candidate on Tuesday. Left voicemail..."><?= esc_textarea($application->employer_notes) ?></textarea>
                            </p>
                            <hr>
                            <input type="submit" class="button button-primary button-large widefat" value="Update ATS Record">
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </form>
</div>