<?php if (!defined('ABSPATH')) exit; 
global $wpdb;
$user_id = get_current_user_id();

$apps_table = $wpdb->prefix . 'nkrp_applications';
$jobs_table = $wpdb->prefix . 'nkrp_jobs';
$companies_table = $wpdb->prefix . 'nkrp_companies';

$suppress = $wpdb->suppress_errors();
$applied_jobs = $wpdb->get_results($wpdb->prepare("
    SELECT a.id, a.status as app_status, a.created_at as applied_date, 
           j.id as job_id, j.job_title as title, j.city as location, 
           c.company_name
    FROM {$apps_table} a
    INNER JOIN {$jobs_table} j ON a.job_id = j.id
    LEFT JOIN {$companies_table} c ON j.company_id = c.id
    WHERE a.candidate_id = %d
    ORDER BY a.created_at DESC
", $user_id));
$wpdb->suppress_errors($suppress);
?>

<div class="nkrp-dashboard-header">
    <h2>Applied Jobs</h2>
    <p style="margin:0; color:#64748b; font-size:14px;">Track the status of your recent applications.</p>
</div>

<?php if (empty($applied_jobs)): ?>
    <div class="nkrp-empty-state" style="background:#fff; border:1px dashed #cbd5e1; padding:40px; text-align:center; border-radius:12px;">
        <span class="dashicons dashicons-clipboard" style="font-size:32px; width:32px; height:32px; color:#94a3b8; margin-bottom:10px;"></span>
        <p style="color:#0f172a; font-weight:600; font-size: 16px;">You haven't applied to any jobs yet.</p>
        <p style="font-size:13px; margin-top:5px; color:#64748b;">Find your dream job and submit your AI-powered CV.</p>
        <a href="<?= esc_url(home_url('/jobs/')) ?>" class="nkrp-btn-primary" style="margin-top:15px; display:inline-block; text-decoration:none;">Browse Jobs</a>
    </div>
<?php else: ?>
    <div class="nkrp-table-responsive">
        <table class="nkrp-table">
            <thead>
                <tr>
                    <th>Job Title & Company</th>
                    <th>Date Applied</th>
                    <th>Application Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applied_jobs as $app): 
                    
                    // Dynamic Status Badges mapped to Employer ATS options
                    $status_class = 'nkrp-status-pending';
                    $status_label = 'Pending Review';
                    
                    if ($app->app_status === 'reviewed') {
                        $status_class = 'nkrp-status-reviewed';
                        $status_label = 'Reviewed by Employer';
                    } elseif ($app->app_status === 'shortlisted') {
                        $status_class = 'nkrp-status-shortlisted';
                        $status_label = 'Shortlisted!';
                    } elseif ($app->app_status === 'interview') {
                        $status_class = 'nkrp-status-interview';
                        $status_label = 'Interviewing';
                    } elseif ($app->app_status === 'hired') {
                        $status_class = 'nkrp-status-hired';
                        $status_label = 'Hired!';
                    } elseif ($app->app_status === 'rejected') {
                        $status_class = 'nkrp-status-rejected';
                        $status_label = 'Not Selected';
                    }
                ?>
                <tr>
                    <td>
                        <strong><a href="<?= esc_url(home_url('/job-details/?id=' . $app->job_id)) ?>" target="_blank" style="color:#0f172a; text-decoration:none; font-size:15px;"><?= esc_html($app->title) ?></a></strong>
                        <div style="font-size:12px; color:#3b82f6; margin-top:4px; font-weight:600;"><?= esc_html($app->company_name ?: 'Confidential Company') ?> &bull; <span style="color:#64748b; font-weight:400;"><?= esc_html($app->location) ?></span></div>
                    </td>
                    <td style="color:#64748b; font-size:13px;">
                        <?= date_i18n(get_option('date_format'), strtotime($app->applied_date)) ?>
                    </td>
                    <td>
                        <span class="nkrp-badge <?= $status_class ?>"><?= esc_html($status_label) ?></span>
                    </td>
                    <td>
                        <a href="<?= esc_url(home_url('/job-details/?id=' . $app->job_id)) ?>" class="nkrp-btn-secondary" style="font-size:12px; padding:6px 12px; text-decoration:none;">View Job</a>
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
    
    .nkrp-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; border: 1px solid transparent; }
    .nkrp-status-pending { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
    .nkrp-status-reviewed { background-color: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .nkrp-status-shortlisted { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .nkrp-status-interview { background-color: #f3e8ff; color: #9d174d; border-color: #fbcfe8; }
    .nkrp-status-hired { background-color: #dcfce7; color: #166534; border-color: #22c55e; }
    .nkrp-status-rejected { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
    
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center;}
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
</style>