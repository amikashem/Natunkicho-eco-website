<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Candidate Resumes', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-resume-create" class="page-title-action"><?php esc_html_e('Add New Resume', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <div class="nkrp-stats-grid">
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #1e40af;">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Total Resumes', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_all) ?></span>
            </div>
        </div>
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #dcfce7; color: #166534;">
                <span class="dashicons dashicons-visibility"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Active / Visible', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_active) ?></span>
            </div>
        </div>
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #fef9c3; color: #854d0e;"> 
                <span class="dashicons dashicons-hidden"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Hidden (Private)', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_hidden) ?></span>
            </div>
        </div>
        <div class="nkrp-stat-card">
            <div class="stat-icon" style="background: #f3f4f6; color: #475569;">
                <span class="dashicons dashicons-edit-page"></span>
            </div>
            <div class="stat-details">
                <span class="stat-title"><?php esc_html_e('Drafts / Incomplete', 'nk-recruitment'); ?></span>
                <span class="stat-number"><?= esc_html($count_draft) ?></span>
            </div>
        </div>
    </div>

    <ul class="subsubsub">
        <li class="all"><a href="?page=nkrp-resumes" class="<?= empty($status) ? 'current' : '' ?>">All <span class="count">(<?= esc_html($count_all) ?>)</span></a> |</li>
        <li class="active"><a href="?page=nkrp-resumes&status=active" class="<?= $status === 'active' ? 'current' : '' ?>">Active <span class="count">(<?= esc_html($count_active) ?>)</span></a> |</li>
        <li class="hidden"><a href="?page=nkrp-resumes&status=hidden" class="<?= $status === 'hidden' ? 'current' : '' ?>">Hidden <span class="count">(<?= esc_html($count_hidden) ?>)</span></a> |</li>
        <li class="draft"><a href="?page=nkrp-resumes&status=draft" class="<?= $status === 'draft' ? 'current' : '' ?>">Draft <span class="count">(<?= esc_html($count_draft) ?>)</span></a></li>
    </ul>

    <form method="get">
        <input type="hidden" name="page" value="nkrp-resumes">
        <?php if ($status): ?><input type="hidden" name="status" value="<?= esc_attr($status) ?>"><?php endif; ?>
        <p class="search-box">
            <input type="search" name="s" value="<?= esc_attr($search) ?>" placeholder="<?php esc_attr_e('Search title or skills...', 'nk-recruitment'); ?>">
            <input type="submit" class="button" value="<?php esc_attr_e('Search', 'nk-recruitment'); ?>">
        </p>
    </form>

    <form id="resumes-filter" method="post">
        <?php wp_nonce_field('bulk-resumes', 'bulk_resumes_nonce'); ?>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1"><?php esc_html_e('Bulk actions', 'nk-recruitment'); ?></option>
                    <option value="active"><?php esc_html_e('Mark Active', 'nk-recruitment'); ?></option>
                    <option value="hidden"><?php esc_html_e('Mark Hidden', 'nk-recruitment'); ?></option>
                    <option value="draft"><?php esc_html_e('Mark Draft', 'nk-recruitment'); ?></option>
                    <option value="trash"><?php esc_html_e('Move to Trash', 'nk-recruitment'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'nk-recruitment'); ?>">
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?= esc_html($total_items) ?> items</span>
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
                    <th scope="col" class="manage-column column-primary"><?php esc_html_e('Resume Title', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Candidate ID', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Type', 'nk-recruitment'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Status', 'nk-recruitment'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resumes)): ?>
                    <?php foreach ($resumes as $res): ?>
                        <tr>
                            <th scope="row" class="check-column"><input type="checkbox" name="resume_ids[]" value="<?= esc_attr($res->id) ?>"></th>
                            <td class="title column-primary">
                                <strong><a href="?page=nkrp-resume-edit&id=<?= esc_attr($res->id) ?>"><?= esc_html($res->resume_title) ?></a></strong>
                                <?= $res->is_primary ? '<span class="dashicons dashicons-star-filled" style="color:#eab308; font-size:16px; margin-left:5px;" title="Primary Resume"></span>' : '' ?>
                                <div class="row-actions">
                                    <span class="edit"><a href="?page=nkrp-resume-edit&id=<?= esc_attr($res->id) ?>"><?php esc_html_e('Edit', 'nk-recruitment'); ?></a> | </span>
                                    <span class="trash"><a href="?page=nkrp-resume-delete&id=<?= esc_attr($res->id) ?>" onclick="return confirm('Delete this resume?');"><?php esc_html_e('Delete', 'nk-recruitment'); ?></a></span>
                                </div>
                                <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                            </td>
                            <td>#<?= esc_html((string)$res->candidate_id) ?></td>
                            <td><?= esc_html(ucwords(str_replace('_', ' ', $res->file_type))) ?></td>
                            <td>
                                <?php
                                    $badge_class = 'badge-draft';
                                    if ($res->status === 'active') $badge_class = 'badge-publish';
                                    if ($res->status === 'hidden') $badge_class = 'badge-closed';
                                ?>
                                <span class="nkrp-badge <?= esc_attr($badge_class) ?>"><?= esc_html(ucfirst($res->status)) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><?php esc_html_e('No resumes found.', 'nk-recruitment'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<style>
    /* Stats & Badge CSS */
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
</style>