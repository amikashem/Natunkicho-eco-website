<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-apply-box" id="nkrp-apply-section">
    <div class="nkrp-apply-header">
        <h3><?php esc_html_e('Submit Your Application', 'nk-recruitment'); ?></h3>
    </div>

    <?php if (isset($_GET['apply_error'])): ?>
        <div class="nkrp-alert nkrp-alert-error" style="margin-bottom: 20px;">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e('There was an issue submitting your application. Please try again.', 'nk-recruitment'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="nkrp-apply-form">
        <?php wp_nonce_field('nkrp_apply_action', 'nkrp_apply_job_nonce'); ?>
        <input type="hidden" name="job_id" value="<?= esc_attr((string)$job_id) ?>">

        <div class="nkrp-form-group">
            <label for="resume_id"><?php esc_html_e('Select Resume *', 'nk-recruitment'); ?></label>
            <?php if (empty($candidate_resumes)): ?>
                <div class="nkrp-empty-resume-alert">
                    <span class="dashicons dashicons-media-document"></span>
                    <p>You haven't built a resume yet!</p>
                    <a href="<?= esc_url(home_url('/build-resume/')) ?>" class="nkrp-btn-secondary nkrp-btn-sm">Create Resume</a>
                </div>
            <?php else: ?>
                <select id="resume_id" name="resume_id" required class="nkrp-resume-select">
                    <option value=""><?php esc_html_e('-- Choose a Resume --', 'nk-recruitment'); ?></option>
                    <?php foreach ($candidate_resumes as $resume): ?>
                        <option value="<?= esc_attr((string)$resume->id) ?>" <?= $resume->is_primary ? 'selected' : '' ?>>
                            <?= esc_html($resume->resume_title) ?> <?= $resume->is_primary ? '(Primary)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="nkrp-form-group" style="margin-top: 20px;">
            <div class="nkrp-label-with-ai">
                <label for="cover_letter"><?php esc_html_e('Cover Letter / Message', 'nk-recruitment'); ?></label>
                <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                    <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                </button>
            </div>
            <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Tell the employer why you are a perfect fit for this role..."></textarea>
        </div>

        <div class="nkrp-apply-actions">
            <button type="submit" name="nkrp_apply_job_submit" class="nkrp-btn-submit nkrp-btn-block" <?= empty($candidate_resumes) ? 'disabled' : '' ?>>
                <span class="dashicons dashicons-paperclip" style="margin-top: 2px;"></span> <?php esc_html_e('Send Application', 'nk-recruitment'); ?>
            </button>
            <p class="nkrp-apply-disclaimer">By applying, you agree to share your profile data with this employer.</p>
        </div>
    </form>
</div>

<style>
    /* Application Box Styling */
    .nkrp-apply-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin-top: 40px; border-top: 4px solid #2563eb; }
    .nkrp-apply-header { margin-bottom: 20px; }
    .nkrp-apply-header h3 { margin: 0; color: #0f172a; font-size: 20px; }
    
    .nkrp-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
    .nkrp-resume-select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; background: #f8fafc; cursor: pointer; box-sizing: border-box; 
        min-height: 48px; 
        line-height: 1.5; 
        appearance: auto;
        /* NEW: Ensures long resume titles wrap safely with an ellipsis */
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden; }
    .nkrp-resume-select:focus { outline: none; border-color: #2563eb; background: #ffffff; }
    
    .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; font-family: inherit; resize: vertical; box-sizing: border-box; }
    .nkrp-form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    
    /* Empty Resume Alert */
    .nkrp-empty-resume-alert { background: #fef2f2; border: 1px dashed #f87171; padding: 20px; border-radius: 8px; text-align: center; color: #991b1b; }
    .nkrp-empty-resume-alert .dashicons { font-size: 24px; width: 24px; height: 24px; margin-bottom: 5px; }
    .nkrp-empty-resume-alert p { margin: 0 0 10px 0; font-size: 14px; font-weight: 500; }
    .nkrp-btn-sm { padding: 6px 12px !important; font-size: 13px !important; display: inline-block; text-decoration: none;}

    /* AI Premium Label */
    .nkrp-label-with-ai { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; }
    .nkrp-label-with-ai label { margin-bottom: 0; }
    .nkrp-ai-trigger { display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: not-allowed; transition: all 0.2s; }
    .nkrp-ai-trigger .dashicons { font-size: 12px; width: 12px; height: 12px; color: #cbd5e1; margin-top: 2px;}

    .nkrp-apply-actions { margin-top: 25px; }
    .nkrp-btn-submit { display: inline-flex; justify-content: center; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-block { width: 100%; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    .nkrp-btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
    
    .nkrp-apply-disclaimer { text-align: center; font-size: 12px; color: #64748b; margin: 12px 0 0 0; }
</style>