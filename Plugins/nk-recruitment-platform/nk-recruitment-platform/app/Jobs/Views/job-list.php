<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Hospitality Jobs', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-job-create" class="page-title-action"><?php esc_html_e('Add New Job', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <div class="nkrp-stats-grid">
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #1e40af;">
                <span class="dashicons dashicons-portfolio"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Total Jobs', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_all) ?></span>
            </div>
        </div>
        
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #dcfce7; color: #166534;">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Active / Published', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_publish) ?></span>
            </div>
        </div>

        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #fef9c3; color: #854d0e;"> 
                <span class="dashicons dashicons-edit-page"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Drafts', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_draft) ?></span>
            </div>
        </div>

        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Closed', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_closed) ?></span>
            </div>
        </div>
    </div>
    <ul class="subsubsub">
        <li class="all"><a href="?page=nkrp-jobs" class="<?= empty($status) ? 'current' : '' ?>">All <span class="count">(<?= esc_html($count_all) ?>)</span></a> |</li>
        <li class="publish"><a href="?page=nkrp-jobs&status=publish" class="<?= $status === 'publish' ? 'current' : '' ?>">Published <span class="count">(<?= esc_html($count_publish) ?>)</span></a> |</li>
        <li class="draft"><a href="?page=nkrp-jobs&status=draft" class="<?= $status === 'draft' ? 'current' : '' ?>">Drafts <span class="count">(<?= esc_html($count_draft) ?>)</span></a> |</li>
        <li class="closed"><a href="?page=nkrp-jobs&status=closed" class="<?= $status === 'closed' ? 'current' : '' ?>">Closed <span class="count">(<?= esc_html($count_closed) ?>)</span></a></li>
    </ul>

    <form method="get">
        <input type="hidden" name="page" value="nkrp-jobs">
        <?php if ($status): ?>
            <input type="hidden" name="status" value="<?= esc_attr($status) ?>">
        <?php endif; ?>
        
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php esc_html_e('Search Jobs:', 'nk-recruitment'); ?></label>
            <input type="search" id="post-search-input" name="s" value="<?= esc_attr($search) ?>">
            <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Jobs', 'nk-recruitment'); ?>">
        </p>
    </form>

    <form id="jobs-filter" method="post">
        <?php wp_nonce_field('bulk-jobs', 'bulk_jobs_nonce'); ?>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1"><?php esc_html_e('Bulk actions', 'nk-recruitment'); ?></option>
                    <option value="publish"><?php esc_html_e('Publish', 'nk-recruitment'); ?></option>
                    <option value="draft"><?php esc_html_e('Move to Draft', 'nk-recruitment'); ?></option>
                    <option value="trash"><?php esc_html_e('Move to Trash', 'nk-recruitment'); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e('Apply', 'nk-recruitment'); ?>">
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?= esc_html($total_jobs) ?> items</span>
                    <span class="pagination-links">
                        <?php
                            echo paginate_links([
                                'base'      => add_query_arg('paged', '%#%'),
                                'format'    => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total'     => $total_pages,
                                'current'   => $paged,
                            ]);
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><input type="checkbox"></td>
                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Job Title', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Department', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Location', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Vacancies', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Status', 'nk-recruitment'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="job_ids[]" value="<?= esc_attr($job->id) ?>">
                            </th>
                            <td class="title column-title has-row-actions column-primary page-title">
                                <strong>
                                    <a href="?page=nkrp-job-edit&id=<?= esc_attr($job->id) ?>" class="row-title">
                                        <?= esc_html($job->title) ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="?page=nkrp-job-edit&id=<?= esc_attr($job->id) ?>"><?php esc_html_e('Edit', 'nk-recruitment'); ?></a> | </span>
                                    <span class="trash"><a href="?page=nkrp-job-delete&id=<?= esc_attr($job->id) ?>" class="submitdelete" onclick="return confirm('Are you sure you want to delete this job?');"><?php esc_html_e('Trash', 'nk-recruitment'); ?></a> | </span>
                                    <!-- FIXED: Now explicitly points to /job-details/?id= -->
                                    <span class="view"><a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" target="_blank" rel="bookmark"><?php esc_html_e('View Front-end', 'nk-recruitment'); ?></a></span>
                                </div>
                                <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                            </td>
                            <td data-colname="Department"><?= esc_html($job->department ?: '-') ?></td>
                            <td data-colname="Location"><?= esc_html($job->location ?: $job->country) ?></td>
                            <td data-colname="Vacancies"><?= esc_html((string)$job->vacancies) ?></td>
                            <td data-colname="Status">
                                <?php
                                    $badge_class = 'badge-draft';
                                    if ($job->status === 'publish' || $job->status === 'published') $badge_class = 'badge-publish';
                                    if ($job->status === 'closed') $badge_class = 'badge-closed';
                                ?>
                                <span class="nkrp-badge <?= esc_attr($badge_class) ?>"><?= esc_html(ucfirst($job->status)) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e('No jobs found. Start by adding one!', 'nk-recruitment'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<style>
    .nkrp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
    .nkrp-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .nkrp-stat-card .stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; }
    .nkrp-stat-card .stat-icon .dashicons { font-size: 24px; width: 24px; height: 24px; }
    .nkrp-stat-card .stat-details { display: flex; flex-direction: column; }
    .nkrp-stat-card .stat-title { font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .nkrp-stat-card .stat-number { font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .nkrp-badge { padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; display: inline-block; }
    .badge-publish { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .badge-draft { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24;}
    .badge-closed { background: #fee2e2; color: #991b1b; border: 1px solid #f87171;}
    .nkrp-admin-wrap table.wp-list-table { box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 4px; overflow: hidden; }
</style>