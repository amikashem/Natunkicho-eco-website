<?php if (!defined('ABSPATH')) exit; 
// Scope: $candidate
?>

<div class="nkrp-dashboard-header">
    <h2>Profile Preview</h2>
    <a href="<?= esc_url(add_query_arg('tab', 'profile', home_url('/candidate-dashboard/'))) ?>" class="nkrp-btn-secondary"><span class="dashicons dashicons-edit"></span> Edit Profile</a>
</div>

<div class="nkrp-preview-card">
    <div class="nkrp-preview-header">
        <div class="nkrp-preview-avatar">
            <?php if (!empty($candidate->profile_photo_id)): ?>
                <img src="<?= esc_url(wp_get_attachment_image_url($candidate->profile_photo_id, 'medium')) ?>">
            <?php else: ?>
                <span class="dashicons dashicons-admin-users"></span>
            <?php endif; ?>
        </div>
        <div class="nkrp-preview-title">
            <h3><?= esc_html(!empty($candidate->first_name) ? $candidate->first_name . ' ' . $candidate->last_name : 'Candidate Name') ?></h3>
            <p class="nkrp-job-title"><?= esc_html($candidate->professional_title ?: 'Professional Title') ?></p>
            <div class="nkrp-preview-meta">
                <span><span class="dashicons dashicons-clock"></span> <?= esc_html($candidate->experience_years ?: '0') ?> Years Exp</span>
                <?php if (!empty($candidate->phone)): ?><span><span class="dashicons dashicons-smartphone"></span> <?= esc_html($candidate->phone) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="nkrp-preview-actions">
            <a href="<?= esc_url(add_query_arg('nkrp_action', 'export_cv', home_url('/candidate-dashboard/'))) ?>" target="_blank" class="nkrp-btn-primary" style="margin-right:10px;">
                <span class="dashicons dashicons-media-text"></span> Export CV
            </a>
            <?php if (!empty($candidate->cv_id)): ?>
                <a href="<?= esc_url(wp_get_attachment_url($candidate->cv_id)) ?>" target="_blank" class="nkrp-btn-secondary">
                    <span class="dashicons dashicons-external"></span> View Attachment
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="nkrp-preview-body">
        <?php if (!empty($candidate->bio)): ?>
            <div class="nkrp-preview-section">
                <h4>Professional Summary</h4>
                <p><?= nl2br(esc_html($candidate->bio)) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($candidate->skills)): ?>
            <div class="nkrp-preview-section">
                <h4>Top Skills</h4>
                <div class="nkrp-skills-list">
                    <?php foreach (explode(',', $candidate->skills) as $skill): ?>
                        <span class="nkrp-skill-tag"><?= esc_html(trim($skill)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($candidate->experience_data)): ?>
            <div class="nkrp-preview-section">
                <h4>Experience</h4>
                <?php foreach($candidate->experience_data as $exp): ?>
                    <div class="nkrp-timeline-item">
                        <div class="nkrp-timeline-header">
                            <strong><?= esc_html($exp['title']) ?></strong>
                            <span class="nkrp-timeline-date"><?= esc_html($exp['date']) ?></span>
                        </div>
                        <span class="nkrp-timeline-company"><?= esc_html($exp['company']) ?></span>
                        <p style="margin-top:5px; font-size:14px;"><?= nl2br(esc_html($exp['desc'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($candidate->education_data)): ?>
            <div class="nkrp-preview-section">
                <h4>Education</h4>
                <?php foreach($candidate->education_data as $edu): ?>
                    <div class="nkrp-timeline-item">
                        <div class="nkrp-timeline-header">
                            <strong><?= esc_html($edu['degree']) ?></strong>
                            <span class="nkrp-timeline-date"><?= esc_html($edu['year']) ?></span>
                        </div>
                        <span class="nkrp-timeline-company"><?= esc_html($edu['institution']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .nkrp-preview-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
    .nkrp-preview-header { padding: 30px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .nkrp-preview-avatar { width: 100px; height: 100px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
    .nkrp-preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .nkrp-preview-title { flex: 1; min-width: 250px; }
    .nkrp-preview-title h3 { margin: 0 0 5px 0; font-size: 24px; color: #0f172a; }
    .nkrp-job-title { margin: 0 0 10px 0; font-size: 16px; color: #3b82f6; font-weight: 500; }
    .nkrp-preview-meta { display: flex; gap: 15px; font-size: 13px; color: #64748b; flex-wrap: wrap; }
    
    .nkrp-preview-body { padding: 30px; }
    .nkrp-preview-section { margin-bottom: 30px; }
    .nkrp-preview-section h4 { margin: 0 0 15px 0; font-size: 16px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: inline-block; }
    .nkrp-skills-list { display: flex; flex-wrap: wrap; gap: 10px; }
    .nkrp-skill-tag { background: #eff6ff; color: #1d4ed8; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    
    .nkrp-timeline-item { border-left: 2px solid #e2e8f0; padding-left: 15px; margin-bottom: 20px; position: relative; }
    .nkrp-timeline-item::before { content: ''; position: absolute; left: -6px; top: 5px; width: 10px; height: 10px; background: #cbd5e1; border-radius: 50%; }
    .nkrp-timeline-header { display: flex; justify-content: space-between; margin-bottom: 2px; }
    .nkrp-timeline-date { font-size: 12px; color: #64748b; font-weight: 600; }
    .nkrp-timeline-company { font-size: 14px; color: #0f172a; font-weight: 500; }
</style>