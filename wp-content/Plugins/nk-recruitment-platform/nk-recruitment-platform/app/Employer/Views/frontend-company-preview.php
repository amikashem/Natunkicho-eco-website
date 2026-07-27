<?php if (!defined('ABSPATH')) exit; 
// Scope: $company
global $wpdb;
$user_id = get_current_user_id();

// Query for Active Vacancies for this Employer
// Suppressing errors so it doesn't break if the jobs table is empty/pending during dev
$jobs_table = $wpdb->prefix . 'nkrp_jobs';
$suppress = $wpdb->suppress_errors();

$vacancies = $wpdb->get_results($wpdb->prepare("
    SELECT id, title, location, job_type, created_at 
    FROM {$jobs_table} 
    WHERE employer_id = %d AND status = 'active'
    ORDER BY created_at DESC
", $user_id));

$wpdb->suppress_errors($suppress);
?>

<div class="nkrp-dashboard-header">
    <h2>Company Profile Preview</h2>
    <a href="<?= esc_url(add_query_arg('tab', 'company', home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-secondary"><span class="dashicons dashicons-edit"></span> Edit Profile</a>
</div>

<!-- Company Header Card -->
<div class="nkrp-preview-card" style="margin-bottom: 30px;">
    <div class="nkrp-preview-header">
        <div class="nkrp-preview-avatar">
            <?php if (!empty($company->logo_id)): ?>
                <img src="<?= esc_url(wp_get_attachment_image_url($company->logo_id, 'medium')) ?>" style="width:100%; height:100%; object-fit:cover; background:#fff;">
            <?php else: ?>
                <span class="dashicons dashicons-building" style="font-size:40px; color:#94a3b8; margin-top:10px;"></span>
            <?php endif; ?>
        </div>
        <div class="nkrp-preview-title">
            <h3><?= esc_html($company->name ?: 'Company Name') ?></h3>
            <div class="nkrp-preview-meta">
                <?php if (!empty($company->industry)): ?>
                    <span><span class="dashicons dashicons-category"></span> <?= esc_html($company->industry) ?></span>
                <?php endif; ?>
                <?php if (!empty($company->website)): ?>
                    <span><span class="dashicons dashicons-admin-links"></span> <a href="<?= esc_url($company->website) ?>" target="_blank" style="color:inherit; text-decoration:none;"><?= esc_html(str_replace(['http://', 'https://'], '', $company->website)) ?></a></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="nkrp-preview-body">
        <div class="nkrp-preview-section">
            <h4>About the Company</h4>
            <?php if (!empty($company->description)): ?>
                <p><?= nl2br(esc_html($company->description)) ?></p>
            <?php else: ?>
                <p style="color:#94a3b8; font-style:italic;">No description provided yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Vacancies Section -->
<div class="nkrp-vacancies-section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="margin:0; font-size:20px; color:#0f172a;">Open Vacancies (<?= is_array($vacancies) ? count($vacancies) : 0 ?>)</h3>
        <a href="<?= esc_url(home_url('/post-job/')) ?>" class="nkrp-btn-primary" style="font-size:13px; padding:6px 12px;"><span class="dashicons dashicons-plus-alt2" style="font-size:14px; margin-top:3px;"></span> Post Job</a>
    </div>

    <?php if (empty($vacancies)): ?>
        <div class="nkrp-empty-state" style="background:#fff;">
            <span class="dashicons dashicons-portfolio"></span>
            <p>You have no active job postings.</p>
            <p style="font-size:13px; margin-top:5px;">Post a job to see it listed on your company profile.</p>
        </div>
    <?php else: ?>
        <div class="nkrp-vacancy-list">
            <?php foreach ($vacancies as $job): ?>
                <div class="nkrp-vacancy-card">
                    <div>
                        <h4 style="margin:0 0 5px 0; font-size:16px; color:#0f172a;">
                            <a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" style="color:inherit; text-decoration:none;"><?= esc_html($job->title) ?></a>
                        </h4>
                        <div style="display:flex; gap:15px; font-size:13px; color:#64748b;">
                            <span><span class="dashicons dashicons-location" style="font-size:14px; margin-top:2px;"></span> <?= esc_html($job->location ?: 'Remote') ?></span>
                            <span><span class="dashicons dashicons-clock" style="font-size:14px; margin-top:2px;"></span> <?= esc_html(ucfirst($job->job_type ?: 'Full-time')) ?></span>
                        </div>
                    </div>
                    <div>
                        <a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" class="nkrp-btn-secondary" style="font-size:13px; padding:6px 12px; text-decoration:none;">View Job</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Profile Header */
    .nkrp-preview-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
    .nkrp-preview-header { padding: 30px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .nkrp-preview-avatar { width: 100px; height: 100px; border-radius: 12px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; border: 1px solid #cbd5e1; }
    .nkrp-preview-title { flex: 1; min-width: 250px; }
    .nkrp-preview-title h3 { margin: 0 0 8px 0; font-size: 24px; color: #0f172a; }
    .nkrp-preview-meta { display: flex; gap: 15px; font-size: 13px; color: #64748b; flex-wrap: wrap; }
    .nkrp-preview-meta span { display: flex; align-items: center; gap: 5px; }
    .nkrp-preview-meta a:hover { text-decoration: underline !important; color: #2563eb !important; }
    
    .nkrp-preview-body { padding: 30px; }
    .nkrp-preview-section h4 { margin: 0 0 15px 0; font-size: 16px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: inline-block; }
    .nkrp-preview-section p { margin: 0; font-size: 15px; line-height: 1.6; color: #334155; }
    
    /* Vacancy List */
    .nkrp-vacancy-list { display: flex; flex-direction: column; gap: 15px; }
    .nkrp-vacancy-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; transition: border-color 0.2s, box-shadow 0.2s; }
    .nkrp-vacancy-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nkrp-vacancy-card h4 a:hover { color: #2563eb !important; }
</style>