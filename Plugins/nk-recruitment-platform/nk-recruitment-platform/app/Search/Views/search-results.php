<?php if (!defined('ABSPATH')) exit; 
use NKRecruitment\Membership\Services\PermissionService;
?>

<?php
$items_to_loop = [];
if (isset($initial_results['data']) && is_iterable($initial_results['data'])) {
    $items_to_loop = $initial_results['data'];
} elseif (is_iterable($initial_results)) {
    $items_to_loop = $initial_results;
}

$viewer_id = get_current_user_id();
$permissionService = new PermissionService();

$is_premium_employer = false;
if ($search_type === 'candidates' && $viewer_id > 0) {
    $is_premium_employer = $permissionService->canViewCandidateContact($viewer_id, 0);
}
?>

<div id="nkrp-results-container" class="nkrp-css-grid">

    <?php if (!empty($items_to_loop)): ?>
        <?php foreach ($items_to_loop as $item_raw): 
            $item = is_array($item_raw) ? (object) $item_raw : $item_raw;
            $companySlug = $item->company_slug ?? '';
            if (empty($companySlug) && !empty($item->company_name)) {
                $companySlug = sanitize_title($item->company_name);
            }
        ?>
            
            <?php if ($search_type === 'companies'): ?>
                <div class="nkrp-grid-card">
                    <div class="nkrp-card-header-center">
                        <?php if (!empty($item->logo)): ?>
                            <img src="<?= esc_url($item->logo) ?>" alt="<?= esc_attr($item->company_name ?? '') ?>" class="nkrp-logo-round">
                        <?php else: ?>
                            <div class="nkrp-logo-round-placeholder"><span class="dashicons dashicons-building"></span></div>
                        <?php endif; ?>
                        <h3 class="nkrp-title-lg"><?= esc_html($item->company_name ?? 'Unknown Company') ?></h3>
                        <?php if (!empty($item->industry)): ?>
                            <span class="nkrp-badge-subtle"><?= esc_html($item->industry) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="nkrp-card-body-center">
                        <?php if (!empty($item->city) || !empty($item->country)): ?>
                            <span class="nkrp-text-muted"><span class="dashicons dashicons-location"></span> <?= esc_html(trim(($item->city ?? '') . ', ' . ($item->country ?? ''), ', ')) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="nkrp-card-footer">
                        <a href="<?= esc_url(home_url('/company-profile/?slug=' . $companySlug)) ?>" class="nkrp-btn-block">View Profile</a>
                    </div>
                </div>

            <?php elseif ($search_type === 'candidates'): ?>
                <?php 
                    $candidate_user_id = isset($item->user_id) ? (int)$item->user_id : (isset($item->ID) ? (int)$item->ID : 0);
                    $can_view_this_candidate = $is_premium_employer || $permissionService->canViewCandidateContact($viewer_id, $candidate_user_id);
                    $skills_raw = is_string($item->skills_data ?? '') ? json_decode($item->skills_data, true) : ($item->skills_data ?? []);
                    $skills = is_array($skills_raw) ? $skills_raw : [];
                    $candidate_name = $item->display_name ?? 'Confidential Candidate';
                ?>
                <div class="nkrp-grid-card">
                    <div class="nkrp-card-header-center">
                        <div class="nkrp-logo-round-placeholder">
                            <span class="dashicons dashicons-businessman"></span>
                            <?php if (!$can_view_this_candidate): ?>
                                <span class="dashicons dashicons-lock nkrp-lock-badge"></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="nkrp-title-lg <?= !$can_view_this_candidate ? 'nkrp-blurred' : '' ?>">
                            <?= $can_view_this_candidate ? esc_html($candidate_name) : 'Hidden Name' ?>
                        </h3>
                        <span class="nkrp-text-highlight"><?= esc_html($item->resume_title ?? $item->professional_title ?? 'Professional') ?></span>
                    </div>
                    
                    <div class="nkrp-card-body-center">
                        <div class="nkrp-meta-flex">
                            <?php if (!empty($item->location)): ?>
                                <span><span class="dashicons dashicons-location"></span> <?= esc_html($item->location) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item->experience_years)): ?>
                                <span><span class="dashicons dashicons-portfolio"></span> <?= esc_html($item->experience_years) ?> Yrs</span>
                            <?php endif; ?>
                        </div>
                        <div class="nkrp-skill-tags">
                            <?php if (!empty($skills)): ?>
                                <?php foreach(array_slice($skills, 0, 3) as $skill): ?>
                                    <span class="nkrp-tag"><?= esc_html($skill) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="nkrp-text-muted">Skills not listed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nkrp-card-footer">
                        <?php if ($can_view_this_candidate): ?>
                            <a href="<?= esc_url(home_url('/candidate-profile/?resume_id=' . ($item->id ?? 0))) ?>" class="nkrp-btn-block">View Full CV</a>
                        <?php else: ?>
                            <button onclick="window.location.href='<?= esc_url(home_url('/membership/')) ?>';" class="nkrp-btn-block nkrp-btn-warning">
                                <span class="dashicons dashicons-lock"></span> Unlock Profile
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="nkrp-grid-card nkrp-job-card">
                    <div class="nkrp-card-top-flex">
                        <?php 
                            $company_logo = !empty($item->company_logo) ? esc_url($item->company_logo) : '';
                            if (is_numeric($item->company_logo ?? '')) $company_logo = wp_get_attachment_image_url($item->company_logo, 'thumbnail');
                        ?>
                        <?php if ($company_logo): ?>
                            <img src="<?= $company_logo ?>" class="nkrp-job-logo">
                        <?php else: ?>
                            <div class="nkrp-job-logo-placeholder"><span class="dashicons dashicons-building"></span></div>
                        <?php endif; ?>

                        <div class="nkrp-job-badges">
                            <?php if (isset($item->featured) && $item->featured == 1): ?>
                                <span class="nkrp-badge-gold"><span class="dashicons dashicons-star-filled"></span> Featured</span>
                            <?php endif; ?>
                            <?php if (!empty($item->job_type) || !empty($item->employment_type)): ?>
                                <span class="nkrp-badge-blue"><?= esc_html($item->job_type ?? $item->employment_type) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nkrp-card-content-left">
                        <h3 class="nkrp-title-md"><a href="<?= esc_url(home_url('/job-details/?id=' . ($item->job_id ?? $item->id ?? 0))) ?>"><?= esc_html($item->job_title ?? $item->title ?? 'Untitled Job') ?></a></h3>
                        
                        <?php if (!empty($item->company_name)): ?>
                            <a href="<?= esc_url(home_url('/company-profile/?slug=' . esc_attr($companySlug))) ?>" class="nkrp-company-link">
                                <?= esc_html($item->company_name) ?>
                            </a>
                        <?php endif; ?>

                        <div class="nkrp-job-meta-list">
                            <span title="Location"><span class="dashicons dashicons-location"></span> <?= esc_html($item->city ?? $item->location ?? 'Remote') ?></span>
                            
                            <?php if (!empty($item->salary_min)): ?>
                                <span title="Salary"><span class="dashicons dashicons-money-alt"></span> <?= esc_html($item->currency ?? 'USD') ?> <?= number_format((float)$item->salary_min) ?>+</span>
                            <?php endif; ?>

                            <?php if (!empty($item->deadline)): ?>
                                <span title="Deadline" style="color: #b91c1c;"><span class="dashicons dashicons-calendar-alt"></span> <?= date_i18n('M j', strtotime($item->deadline)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="nkrp-card-footer">
                        <a href="<?= esc_url(home_url('/job-details/?id=' . ($item->job_id ?? $item->id ?? 0))) ?>" class="nkrp-btn-block">View Job Details</a>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <div class="nkrp-no-results" style="grid-column: 1 / -1;">
            <div class="nkrp-empty-state" style="background: #fff; border: 1px dashed #cbd5e1; padding: 50px; text-align: center; border-radius: 12px;">
                <span class="dashicons dashicons-search" style="font-size: 40px; width: 40px; height: 40px; color: #94a3b8; margin-bottom: 15px;"></span>
                <p style="font-size: 16px; color: #0f172a; font-weight: 600; margin: 0 0 5px 0;"><?php esc_html_e('No results found.', 'nk-recruitment'); ?></p>
                <p style="font-size: 14px; color: #64748b; margin: 0;">Try adjusting your search criteria.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Phase 6: Core CSS Grid System */
    .nkrp-css-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin-top: 20px;}
    @media(max-width: 992px) { .nkrp-css-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 600px) { .nkrp-css-grid { grid-template-columns: 1fr; } }

    /* Card Foundation */
    .nkrp-grid-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; transition: all 0.2s ease; }
    .nkrp-grid-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-color: #cbd5e1; }

    /* Centered Layouts (Company & Candidate) */
    .nkrp-card-header-center { padding: 25px 20px 10px; text-align: center; }
    .nkrp-logo-round, .nkrp-logo-round-placeholder { width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 15px; border: 1px solid #e2e8f0; object-fit: cover; }
    .nkrp-logo-round-placeholder { background: #f8fafc; display: flex; align-items: center; justify-content: center; color: #94a3b8; position: relative; }
    .nkrp-logo-round-placeholder .dashicons { font-size: 32px; width: 32px; height: 32px; }
    .nkrp-lock-badge { position: absolute; bottom: 0; right: 0; background: #fbbf24; color: #78350f; border-radius: 50%; padding: 4px; font-size: 14px; width: 14px; height: 14px; border: 2px solid #fff; }
    
    .nkrp-title-lg { font-size: 18px; color: #0f172a; margin: 0 0 5px 0; font-weight: 700; line-height: 1.3; }
    .nkrp-badge-subtle { display: inline-block; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 5px;}
    .nkrp-text-highlight { color: #2563eb; font-size: 14px; font-weight: 600; }
    .nkrp-text-muted { color: #64748b; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 4px; }
    
    .nkrp-card-body-center { padding: 10px 20px 25px; text-align: center; flex-grow: 1; }
    .nkrp-meta-flex { display: flex; justify-content: center; gap: 15px; font-size: 13px; color: #64748b; margin-bottom: 15px; }
    .nkrp-meta-flex span { display: flex; align-items: center; gap: 4px; }
    .nkrp-skill-tags { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
    .nkrp-tag { background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 6px; font-size: 11px; }

    /* Left-Aligned Layouts (Jobs) */
    .nkrp-card-top-flex { padding: 20px 20px 10px; display: flex; justify-content: space-between; align-items: flex-start; }
    .nkrp-job-logo, .nkrp-job-logo-placeholder { width: 48px; height: 48px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: cover; }
    .nkrp-job-logo-placeholder { background: #f8fafc; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
    .nkrp-job-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .nkrp-badge-gold { background: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 2px;}
    .nkrp-badge-gold .dashicons { font-size: 12px; width: 12px; height: 12px; margin-top: 1px;}
    .nkrp-badge-blue { background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }

    .nkrp-card-content-left { padding: 0 20px 20px; flex-grow: 1; text-align: left; }
    .nkrp-title-md { font-size: 17px; margin: 0 0 5px 0; font-weight: 700; line-height: 1.3; }
    .nkrp-title-md a { color: #0f172a; text-decoration: none; }
    .nkrp-title-md a:hover { color: #2563eb; }
    .nkrp-company-link { color: #3b82f6; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; margin-bottom: 12px; }
    .nkrp-company-link:hover { text-decoration: underline; }
    
    .nkrp-job-meta-list { display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #475569; }
    .nkrp-job-meta-list span { display: flex; align-items: center; gap: 6px; }
    .nkrp-job-meta-list .dashicons { color: #94a3b8; font-size: 14px; width: 14px; height: 14px; }

    /* Footer & Buttons */
    .nkrp-card-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; background: #fafafa; }
    .nkrp-btn-block { display: block; width: 100%; padding: 10px; text-align: center; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 14px; box-sizing: border-box; background: #2563eb; color: #fff; transition: background 0.2s;}
    .nkrp-btn-block:hover { background: #1d4ed8; color: #fff; }
    .nkrp-btn-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;}
    .nkrp-btn-warning:hover { background: #fde68a; }

    .nkrp-blurred { color: transparent !important; text-shadow: 0 0 8px rgba(15,23,42,0.6); user-select: none; }
</style>