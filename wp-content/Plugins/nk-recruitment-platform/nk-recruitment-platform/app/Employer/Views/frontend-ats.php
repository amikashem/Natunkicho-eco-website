<?php if (!defined('ABSPATH')) exit; 
global $wpdb;
$user_id = get_current_user_id();

$is_premium = apply_filters('nkrp_is_user_premium', false, $user_id); 

$apps_table = $wpdb->prefix . 'nkrp_applications';
$jobs_table = $wpdb->prefix . 'nkrp_jobs';
$users_table = $wpdb->users;

$job_filter = isset($_GET['filter_job']) ? (int) $_GET['filter_job'] : 0;
$query_append = $job_filter > 0 ? $wpdb->prepare(" AND a.job_id = %d", $job_filter) : "";

$suppress = $wpdb->suppress_errors();
$applications = $wpdb->get_results($wpdb->prepare("
    SELECT a.id as app_id, a.status, a.created_at, j.job_title as job_title, u.display_name as candidate_name, u.ID as candidate_id 
    FROM {$apps_table} a 
    LEFT JOIN {$jobs_table} j ON a.job_id = j.id 
    LEFT JOIN {$users_table} u ON a.candidate_id = u.ID
    WHERE j.user_id = %d {$query_append}
    ORDER BY a.created_at DESC
", $user_id));
$wpdb->suppress_errors($suppress);

// Inject variables for AJAX Engine
$ajax_nonce = wp_create_nonce('nk_ats_ajax_nonce');
$ajax_url = admin_url('admin-ajax.php');
?>

<div class="nkrp-dashboard-header">
    <h2>Applicant Tracking System</h2>
    <?php if ($job_filter > 0): ?>
        <a href="<?= esc_url(add_query_arg('tab', 'ats', home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-secondary">Clear Job Filter</a>
    <?php endif; ?>
</div>

<?php if (empty($applications)): ?>
    <div class="nkrp-empty-state">
        <span class="dashicons dashicons-groups"></span>
        <p>You have no applicants yet.</p>
        <p style="font-size:13px; margin-top:5px;">Once candidates start applying to your jobs, they will appear here.</p>
    </div>
<?php else: ?>
    <div class="nkrp-table-responsive">
        <table class="nkrp-table nkrp-ats-table" id="nkAtsTable">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Applied For</th>
                    <th>AI Match <span class="dashicons dashicons-superhero" style="font-size:14px; color:#fbbf24;"></span></th>
                    <th>Current Status</th>
                    <th>Quick Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): 
                    $status_class = 'nkrp-status-pending';
                    if ($app->status === 'reviewed') $status_class = 'nkrp-status-reviewed';
                    if ($app->status === 'shortlisted') $status_class = 'nkrp-status-shortlisted';
                    if ($app->status === 'rejected') $status_class = 'nkrp-status-rejected';
                    
                    $access_token = wp_create_nonce('view_applicant_' . $app->app_id);
                    $profile_url = home_url("/candidate-profile/?id={$app->candidate_id}&app_id={$app->app_id}&access_token={$access_token}");
                ?>
                <tr data-appid="<?= esc_attr((string)$app->app_id) ?>">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; color:#64748b;">
                                <span class="dashicons dashicons-admin-users" style="font-size:18px;"></span>
                            </div>
                            <strong><?= esc_html($app->candidate_name ?: 'Unknown Candidate') ?></strong>
                        </div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:4px; margin-left:42px;">Applied: <?= date_i18n(get_option('date_format'), strtotime($app->created_at)) ?></div>
                    </td>
                    <td><?= esc_html($app->job_title) ?></td>
                    
                    <td>
                        <?php if ($is_premium): ?>
                            <span style="color:#16a34a; font-weight:700;">88% Match</span>
                        <?php else: ?>
                            <div class="nkrp-premium-lock" onclick="window.location.href='<?= esc_url(home_url('/membership/')) ?>';" title="Upgrade to Premium to unlock AI Match Scores">
                                <span class="dashicons dashicons-lock"></span> Hidden
                            </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="nkrp-badge nk-status-badge <?= $status_class ?>"><?= esc_html(ucfirst($app->status)) ?></span>
                    </td>

                    <td>
                        <div class="nkrp-quick-actions">
                            
                            <!-- 🔥 THE FIX: View CV now fires AJAX while opening in a new tab -->
                            <a href="<?= esc_url($profile_url) ?>" target="_blank" class="nkrp-action-btn nk-ats-ajax-btn nkrp-btn-view" data-status="reviewed" title="View Full Profile & CV">
                                <span class="dashicons dashicons-visibility"></span> View CV
                            </a>

                            <!-- Instant AJAX Action Buttons -->
                            <button type="button" class="nkrp-action-btn nk-ats-ajax-btn nkrp-btn-shortlist" data-status="shortlisted" title="Shortlist Candidate">
                                <span class="dashicons dashicons-yes"></span>
                            </button>

                            <button type="button" class="nkrp-action-btn nk-ats-ajax-btn nkrp-btn-reject" data-status="rejected" title="Reject Candidate">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>

                            <?php if ($is_premium): ?>
                                <a href="<?= esc_url(add_query_arg('tab', 'messages', home_url('/employer-dashboard/'))) ?>" class="nkrp-action-btn nkrp-btn-message" title="Message Candidate">
                                    <span class="dashicons dashicons-email-alt"></span>
                                </a>
                            <?php else: ?>
                                <button type="button" class="nkrp-action-btn nkrp-btn-message" title="Premium Feature: Message Candidate" onclick="window.location.href='<?= esc_url(home_url('/membership/')) ?>';">
                                    <span class="dashicons dashicons-lock"></span>
                                </button>
                            <?php endif; ?>
                            
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var ajaxUrl = "<?= esc_url($ajax_url) ?>";
        var ajaxNonce = "<?= esc_attr($ajax_nonce) ?>";

        $('.nk-ats-ajax-btn').on('click', function(e) {
            var btn = $(this);
            var isLink = btn.is('a');
            
            // If they clicked a reject button, confirm first
            var newStatus = btn.data('status');
            if (newStatus === 'rejected') {
                if (!confirm('Are you sure you want to silently reject this candidate?')) {
                    return;
                }
            }
            
            var row = btn.closest('tr');
            var appId = row.data('appid');
            
            // Add a visual spinner/opacity state
            btn.css('opacity', '0.5');

            // Fire Background Notification Engine
            $.post(ajaxUrl, {
                action: 'nk_update_ats_status',
                security: ajaxNonce,
                app_id: appId,
                new_status: newStatus
            }, function(response) {
                btn.css('opacity', '1');
                if (response.success && !response.data.ignored) {
                    var badge = row.find('.nk-status-badge');
                    badge.removeClass('nkrp-status-pending nkrp-status-reviewed nkrp-status-shortlisted nkrp-status-rejected');
                    badge.addClass('nkrp-status-' + response.data.new_status);
                    
                    var statusText = response.data.new_status.charAt(0).toUpperCase() + response.data.new_status.slice(1);
                    badge.text(statusText);
                }
            });
            
            // If it's the View CV button, allow the browser to open the link in a new tab
            if (!isLink) {
                e.preventDefault();
            }
        });
    });
    </script>
<?php endif; ?>

<style>
    .nkrp-table-responsive { width: 100%; overflow-x: auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
    .nkrp-table { width: 100%; border-collapse: collapse; text-align: left; }
    .nkrp-table th { background: #f8fafc; padding: 15px 20px; font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .nkrp-table td { padding: 15px 20px; font-size: 14px; color: #0f172a; border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.3s; }
    .nkrp-table tbody tr:hover { background: #f8fafc; }
    
    .nkrp-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; transition: all 0.3s ease; }
    .nkrp-status-pending { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
    .nkrp-status-reviewed { background-color: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .nkrp-status-shortlisted { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .nkrp-status-rejected { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }

    .nkrp-quick-actions { display: flex; gap: 5px; align-items: center; }
    .nkrp-action-btn { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; padding: 6px 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; transition: all 0.2s; text-decoration: none; }
    .nkrp-action-btn:hover { background: #e2e8f0; }
    .nkrp-action-btn .dashicons { font-size: 14px; width: 14px; height: 14px; margin-top:2px; }
    
    .nkrp-btn-view { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .nkrp-btn-view:hover { background: #dbeafe; }
    .nkrp-btn-shortlist { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; padding: 6px; }
    .nkrp-btn-shortlist:hover { background: #dcfce7; }
    .nkrp-btn-reject { background: #fef2f2; border-color: #fecaca; color: #b91c1c; padding: 6px; }
    .nkrp-btn-reject:hover { background: #fee2e2; }
    .nkrp-btn-message { background: #fffbeb; border-color: #fde68a; color: #b45309; padding: 6px; }
    .nkrp-btn-message:hover { background: #fef3c7; }

    .nkrp-premium-lock { display: inline-flex; align-items: center; gap: 5px; background: #f1f5f9; color: #94a3b8; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px dashed #cbd5e1; transition: all 0.2s; }
    .nkrp-premium-lock:hover { background: #fef3c7; color: #b45309; border-color: #fbbf24; }
</style>