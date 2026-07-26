<?php if (!defined('ABSPATH')) exit; 
global $wpdb;
$user_id = get_current_user_id();
$jobs_table = $wpdb->prefix . 'nkrp_jobs';
$apps_table = $wpdb->prefix . 'nkrp_applications';

// ==========================================
// ACTION HANDLER: Process Delete, Pause, Close, Duplicate
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $action_job_id = (int)$_POST['job_id'];
    $redirect_url = '';

    // Handle Delete
    if (isset($_POST['nkrp_delete_job']) && wp_verify_nonce($_POST['nkrp_action_nonce_' . $action_job_id], 'job_action')) {
        $wpdb->delete($jobs_table, ['id' => $action_job_id, 'user_id' => $user_id]);
        $redirect_url = add_query_arg('job_msg', 'deleted');
    }
    
    // Handle Pause
    if (isset($_POST['nkrp_pause_job']) && wp_verify_nonce($_POST['nkrp_action_nonce_' . $action_job_id], 'job_action')) {
        $wpdb->update($jobs_table, ['status' => 'paused'], ['id' => $action_job_id, 'user_id' => $user_id]);
        $redirect_url = add_query_arg('job_msg', 'paused');
    }

    // Handle Close
    if (isset($_POST['nkrp_close_job']) && wp_verify_nonce($_POST['nkrp_action_nonce_' . $action_job_id], 'job_action')) {
        $wpdb->update($jobs_table, ['status' => 'closed'], ['id' => $action_job_id, 'user_id' => $user_id]);
        $redirect_url = add_query_arg('job_msg', 'closed');
    }

    // Handle Duplicate 
    if (isset($_POST['nkrp_duplicate_job']) && wp_verify_nonce($_POST['nkrp_action_nonce_' . $action_job_id], 'job_action')) {
        $original_job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$jobs_table} WHERE id = %d AND user_id = %d", $action_job_id, $user_id), ARRAY_A);
        
        if ($original_job) {
            unset($original_job['id']); 
            $original_job['job_title'] = $original_job['job_title'] . ' (Copy)';
            $original_job['status'] = 'draft'; 
            $original_job['created_at'] = current_time('mysql');
            
            $wpdb->insert($jobs_table, $original_job);
            $redirect_url = add_query_arg('job_msg', 'duplicated');
        }
    }

    if (!empty($redirect_url)) {
        echo "<script>window.location.href='" . esc_url_raw($redirect_url) . "';</script>";
        exit;
    }
}

// Fetch jobs for this employer
$suppress = $wpdb->suppress_errors();
$jobs = $wpdb->get_results($wpdb->prepare("
    SELECT j.id, j.job_title as title, j.status, j.created_at, 
           (SELECT COUNT(a.id) FROM {$apps_table} a WHERE a.job_id = j.id) as app_count
    FROM {$jobs_table} j
    WHERE j.user_id = %d
    ORDER BY j.created_at DESC
", $user_id));
$wpdb->suppress_errors($suppress);
?>

<div class="nkrp-dashboard-header">
    <h2>Manage Jobs</h2>
    <a href="<?= esc_url(home_url('/post-job/')) ?>" class="nkrp-btn-primary"><span class="dashicons dashicons-plus-alt2"></span> Post a New Job</a>
</div>

<?php if (isset($_GET['job_msg'])): ?>
    <div class="nkrp-alert nkrp-alert-success">
        <span class="dashicons dashicons-yes-alt"></span> 
        Job successfully <?= esc_html($_GET['job_msg']) ?>.
    </div>
<?php endif; ?>

<?php if (empty($jobs)): ?>
    <div class="nkrp-empty-state">
        <span class="dashicons dashicons-portfolio"></span>
        <p>You haven't posted any jobs yet.</p>
        <p style="font-size:13px; margin-top:5px;">Post a vacancy to start receiving applications from top talent.</p>
        <a href="<?= esc_url(home_url('/post-job/')) ?>" class="nkrp-btn-primary" style="margin-top:15px; display:inline-block; text-decoration:none;">Create Job Post</a>
    </div>
<?php else: ?>
    <div class="nkrp-table-responsive">
        <table class="nkrp-table">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): 
                    $status_class = in_array($job->status, ['published', 'active']) ? 'nkrp-status-active' : ($job->status === 'pending' ? 'nkrp-status-pending' : 'nkrp-status-closed');
                    if ($job->status === 'draft') $status_class = 'nkrp-status-draft';
                ?>
                <tr>
                    <td><strong><a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" target="_blank" style="color:#0f172a; text-decoration:none;"><?= esc_html($job->title) ?></a></strong></td>
                    <td><span class="nkrp-badge <?= $status_class ?>"><?= esc_html(ucfirst($job->status)) ?></span></td>
                    <td>
                        <a href="<?= esc_url(add_query_arg(['tab' => 'ats', 'filter_job' => $job->id], home_url('/employer-dashboard/'))) ?>" style="font-weight:700; color:#2563eb; text-decoration:none;">
                            <?= esc_html((string)$job->app_count) ?> Candidates
                        </a>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="<?= esc_url(add_query_arg(['tab' => 'edit-job', 'id' => $job->id], home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-icon" title="Edit Job"><span class="dashicons dashicons-edit"></span></a>
                            
                            <form method="POST" action="" style="margin:0; display:flex; gap:8px;">
                                <?php wp_nonce_field('job_action', 'nkrp_action_nonce_' . $job->id); ?>
                                <input type="hidden" name="job_id" value="<?= esc_attr((string)$job->id) ?>">
                                
                                <button type="submit" name="nkrp_duplicate_job" class="nkrp-btn-icon" title="Duplicate Job"><span class="dashicons dashicons-admin-page"></span></button>

                                <?php if ($job->status === 'published' || $job->status === 'active'): ?>
                                    <button type="submit" name="nkrp_pause_job" class="nkrp-btn-icon" title="Pause Job" onclick="return confirm('Pause this job? It will be hidden from search.');"><span class="dashicons dashicons-controls-pause"></span></button>
                                <?php endif; ?>

                                <?php if ($job->status !== 'closed' && $job->status !== 'draft'): ?>
                                    <button type="submit" name="nkrp_close_job" class="nkrp-btn-icon" title="Close Job" onclick="return confirm('Close this job? You will no longer receive applications.');"><span class="dashicons dashicons-no"></span></button>
                                <?php endif; ?>

                                <button type="submit" name="nkrp_delete_job" class="nkrp-btn-icon" style="color:#dc2626;" title="Delete Job" onclick="return confirm('Are you sure you want to permanently delete this job post?');"><span class="dashicons dashicons-trash"></span></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
    .nkrp-table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
    .nkrp-table { width: 100%; border-collapse: collapse; text-align: left; }
    .nkrp-table th { background: #f8fafc; padding: 15px 20px; font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .nkrp-table td { padding: 15px 20px; font-size: 14px; color: #0f172a; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .nkrp-table tbody tr:hover { background: #f8fafc; }
    .nkrp-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
    .nkrp-status-active { background: #dcfce7; color: #166534; }
    .nkrp-status-pending { background: #fef08a; color: #854d0e; }
    .nkrp-status-draft { background: #e2e8f0; color: #475569; }
    .nkrp-status-closed { background: #f1f5f9; color: #475569; }
    .nkrp-btn-icon { background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 6px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration: none; }
    .nkrp-btn-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
    .nkrp-btn-icon .dashicons { font-size: 16px; width: 16px; height: 16px; }
</style>