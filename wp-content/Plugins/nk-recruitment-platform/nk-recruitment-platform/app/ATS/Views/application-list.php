<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Applications Pipeline (ATS)', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-application-create" class="page-title-action"><?php esc_html_e('Manual Entry', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <div class="nkrp-stats-grid">
        <div class="nkrp-stat-card"><div class="stat-icon" style="background:#e0e7ff; color:#1e40af;"><span class="dashicons dashicons-portfolio"></span></div>
            <div class="stat-details"><span class="stat-title">Total</span><span class="stat-number"><?= esc_html((string)$counts['all']) ?></span></div>
        </div>
        <div class="nkrp-stat-card"><div class="stat-icon" style="background:#fef3c7; color:#b45309;"><span class="dashicons dashicons-star-empty"></span></div>
            <div class="stat-details"><span class="stat-title">New</span><span class="stat-number"><?= esc_html((string)$counts['new']) ?></span></div>
        </div>
        <div class="nkrp-stat-card"><div class="stat-icon" style="background:#e0e7ff; color:#4338ca;"><span class="dashicons dashicons-groups"></span></div>
            <div class="stat-details"><span class="stat-title">Interviewing</span><span class="stat-number"><?= esc_html((string)$counts['interview']) ?></span></div>
        </div>
        <div class="nkrp-stat-card"><div class="stat-icon" style="background:#dcfce7; color:#166534;"><span class="dashicons dashicons-saved"></span></div>
            <div class="stat-details"><span class="stat-title">Hired</span><span class="stat-number"><?= esc_html((string)$counts['hired']) ?></span></div>
        </div>
    </div>

    <ul class="subsubsub">
        <li class="all"><a href="?page=nkrp-applications" class="<?= empty($status) ? 'current' : '' ?>">All <span class="count">(<?= esc_html((string)$counts['all']) ?>)</span></a> |</li>
        <li class="new"><a href="?page=nkrp-applications&status=new" class="<?= $status === 'new' ? 'current' : '' ?>">New <span class="count">(<?= esc_html((string)$counts['new']) ?>)</span></a> |</li>
        <li class="screening"><a href="?page=nkrp-applications&status=screening" class="<?= $status === 'screening' ? 'current' : '' ?>">Screening <span class="count">(<?= esc_html((string)$this->service->countApplications(['status' => 'screening'])) ?>)</span></a> |</li>
        <li class="interview"><a href="?page=nkrp-applications&status=interview" class="<?= $status === 'interview' ? 'current' : '' ?>">Interview <span class="count">(<?= esc_html((string)$counts['interview']) ?>)</span></a> |</li>
        <li class="offered"><a href="?page=nkrp-applications&status=offered" class="<?= $status === 'offered' ? 'current' : '' ?>">Offered <span class="count">(<?= esc_html((string)$this->service->countApplications(['status' => 'offered'])) ?>)</span></a> |</li>
        <li class="hired"><a href="?page=nkrp-applications&status=hired" class="<?= $status === 'hired' ? 'current' : '' ?>">Hired <span class="count">(<?= esc_html((string)$counts['hired']) ?>)</span></a> |</li>
        <li class="rejected"><a href="?page=nkrp-applications&status=rejected" class="<?= $status === 'rejected' ? 'current' : '' ?>">Rejected <span class="count">(<?= esc_html((string)$counts['rejected']) ?>)</span></a></li>
    </ul>

    <form method="post">
        <?php wp_nonce_field('bulk-applications', 'bulk_applications_nonce'); ?>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1">Bulk actions</option>
                    <option value="new">Mark New</option>
                    <option value="screening">Mark Screening</option>
                    <option value="interview">Mark Interviewing</option>
                    <option value="offered">Mark Offered</option>
                    <option value="hired">Mark Hired</option>
                    <option value="rejected">Mark Rejected</option>
                    <option value="trash">Delete</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?= esc_html((string)$total_items) ?> items</span>
                    <span class="pagination-links">
                        <?= paginate_links(['base' => add_query_arg('paged', '%#%'), 'format' => '', 'total' => $total_pages, 'current' => $paged]); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                    <th scope="col" class="manage-column" style="width: 60px;">ID</th>
                    <th scope="col" class="manage-column column-primary">Candidate Name</th>
                    <th scope="col" class="manage-column">Applied Job</th>
                    <th scope="col" class="manage-column">Company</th>
                    <th scope="col" class="manage-column">Resume</th>
                    <th scope="col" class="manage-column">Date</th>
                    <th scope="col" class="manage-column">Rating</th>
                    <th scope="col" class="manage-column">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($applications)): foreach ($applications as $app): ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" name="application_ids[]" value="<?= esc_attr((string)$app->id) ?>"></th>
                        <td><strong>#<?= esc_html((string)$app->id) ?></strong></td>
                        <td class="title column-primary">
                            <strong><a href="?page=nkrp-application-edit&id=<?= esc_attr((string)$app->id) ?>"><?= esc_html($app->candidate_name ?: 'Unknown') ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="?page=nkrp-application-edit&id=<?= esc_attr((string)$app->id) ?>">Process Application</a> | </span>
                                <span class="trash"><a href="?page=nkrp-application-delete&id=<?= esc_attr((string)$app->id) ?>" onclick="return confirm('Delete?');" style="color:#b32d2e;">Delete</a></span>
                            </div>
                            <button type="button" class="toggle-row"><span class="screen-reader-text">Show details</span></button>
                        </td>
                        <td><strong><?= esc_html($app->job_title ?: 'Unknown Job') ?></strong></td>
                        <td><?= esc_html($app->company_name ?: 'Unknown Company') ?></td>
                        <td>
                            <?php if ($app->resume_id): ?>
                                <a href="?page=nkrp-resume-edit&id=<?= esc_attr((string)$app->resume_id) ?>" target="_blank" class="button button-small"><span class="dashicons dashicons-media-document" style="margin-top: 2px; font-size: 16px;"></span> View CV</a>
                            <?php else: ?>
                                <span style="color:#999;">No File</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc_html(wp_date(get_option('date_format'), strtotime($app->created_at))) ?></td>
                        <td><?= str_repeat('⭐', (int)$app->employer_rating) ?: '<span style="color:#ccc;">Unrated</span>' ?></td>
                        <td>
                            <?php
                                $colors = ['new'=>'#fef3c7;color:#b45309', 'screening'=>'#e0e7ff;color:#4338ca', 'interview'=>'#fce7f3;color:#be185d', 'offered'=>'#dcfce7;color:#166534', 'hired'=>'#22c55e;color:#fff', 'rejected'=>'#fee2e2;color:#b91c1c'];
                                $style = $colors[$app->status] ?? '#f1f5f9;color:#475569';
                            ?>
                            <span style="padding:4px 10px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase; background:<?= $style ?>;"><?= esc_html($app->status) ?></span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="9"><?php esc_html_e('No applications found in pipeline.', 'nk-recruitment'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>
<style>
    .nkrp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
    .nkrp-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .nkrp-stat-card .stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 24px;}
    .nkrp-stat-card .stat-title { font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
    .nkrp-stat-card .stat-number { font-size: 24px; font-weight: 700; color: #0f172a; display: block;}
</style>