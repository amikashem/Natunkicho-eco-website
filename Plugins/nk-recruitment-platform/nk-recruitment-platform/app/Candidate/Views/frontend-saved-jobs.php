<?php if (!defined('ABSPATH')) exit; 
global $wpdb;
$user_id = get_current_user_id();

// Fetch saved job IDs from user meta securely
$saved_job_ids = get_user_meta($user_id, '_nkrp_saved_jobs', true);
$saved_jobs = [];

if (!empty($saved_job_ids) && is_array($saved_job_ids)) {
    $jobs_table = $wpdb->prefix . 'nkrp_jobs';
    
    // Sanitize to integers to prevent any SQL injection 
    $safe_ids = array_map('intval', $saved_job_ids);
    $ids_string = implode(',', $safe_ids);
    
    $suppress = $wpdb->suppress_errors();
    $saved_jobs = $wpdb->get_results("
        SELECT id as job_id, job_title, city as location, employment_type as job_type, status 
        FROM {$jobs_table} 
        WHERE id IN ({$ids_string})
        ORDER BY created_at DESC
    ");
    $wpdb->suppress_errors($suppress);
}
?>

<div class="nkrp-dashboard-header">
    <h2>Saved Jobs</h2>
    <p style="margin:0; color:#64748b; font-size:14px;">You have unlimited saved jobs access.</p>
</div>

<?php if (isset($_GET['job_removed']) && $_GET['job_removed'] == '1'): ?>
    <div class="nkrp-alert nkrp-alert-success" style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
        <span class="dashicons dashicons-yes-alt"></span> Job removed from your saved list.
    </div>
<?php endif; ?>

<?php if (empty($saved_jobs)): ?>
    <div class="nkrp-empty-state" style="background:#fff; border:1px dashed #cbd5e1; padding:40px; text-align:center; border-radius:12px;">
        <span class="dashicons dashicons-star-empty" style="font-size:40px; width:40px; height:40px; color:#cbd5e1; margin-bottom:15px;"></span>
        <p style="color:#0f172a; font-weight:600; font-size: 16px;">You haven't saved any jobs yet.</p>
        <p style="font-size:13px; margin-top:5px; color:#64748b;">Bookmark jobs you are interested in to apply later.</p>
        <a href="<?= esc_url(home_url('/jobs/')) ?>" class="nkrp-btn-secondary" style="margin-top:15px; display:inline-block; text-decoration:none;">Browse Jobs</a>
    </div>
<?php else: ?>
    <div class="nkrp-saved-grid">
        <?php foreach ($saved_jobs as $job): ?>
            <div class="nkrp-saved-card">
                <div class="nkrp-saved-card-header">
                    <div>
                        <h3 style="margin:0 0 5px 0; font-size:16px; color:#0f172a;"><?= esc_html($job->job_title ?: 'Unknown Role') ?></h3>
                        <p style="margin:0; font-size:13px; color:#3b82f6; font-weight:500;"><?= esc_html($job->job_type ?: 'Full-Time') ?></p>
                    </div>
                    
                    <form method="POST" action="" onsubmit="return confirm('Remove from saved jobs?');" style="margin:0;">
                        <?php wp_nonce_field('unsave_job_action', 'nkrp_unsave_job_nonce'); ?>
                        <input type="hidden" name="job_id" value="<?= esc_attr((string)$job->job_id) ?>">
                        <button type="submit" name="nkrp_unsave_job" class="nkrp-btn-icon" title="Remove Job">
                            <span class="dashicons dashicons-trash" style="color:#94a3b8;"></span>
                        </button>
                    </form>
                </div>
                
                <div class="nkrp-saved-card-meta">
                    <span style="font-size:13px; color:#64748b; display:flex; align-items:center; gap:5px;">
                        <span class="dashicons dashicons-location"></span> <?= esc_html($job->location ?: 'Remote') ?>
                    </span>
                </div>
                
                <div style="margin-top: 15px;">
                    <a href="<?= esc_url(home_url('/job-details/?id=' . $job->job_id)) ?>" class="nkrp-btn-primary" style="width:100%; justify-content:center; padding:8px 0; font-size:13px; display:flex; align-items:center; text-decoration:none; box-sizing:border-box;">View & Apply</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
    .nkrp-saved-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .nkrp-saved-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; transition: transform 0.2s, box-shadow 0.2s; }
    .nkrp-saved-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    .nkrp-saved-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
    .nkrp-saved-card-meta { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; }
    .nkrp-btn-icon { background: none; border: none; cursor: pointer; padding: 5px; border-radius: 4px; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center; }
    .nkrp-btn-icon:hover { background: #fef2f2; }
    .nkrp-btn-icon:hover .dashicons { color: #dc2626 !important; }
</style>