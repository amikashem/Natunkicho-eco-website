<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-dashboard-nav-bar" style="margin-top: 30px; margin-bottom: -15px;">
    <a href="<?= esc_url(home_url('/employer-dashboard/')) ?>" class="nkrp-btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
        <span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e('Back to Employer Dashboard', 'nk-recruitment'); ?>
    </a>
</div>

<div class="nkrp-ats-container">
    <div class="nkrp-ats-header">
        <h2><?php esc_html_e('Applicant Tracking System (ATS)', 'nk-recruitment'); ?></h2>
        <p><?php esc_html_e('Manage your recruitment pipeline and review incoming candidate applications.', 'nk-recruitment'); ?></p>
    </div>

    <?php if (isset($_GET['status_updated']) && $_GET['status_updated'] == '1'): ?>
        <div class="nkrp-alert nkrp-alert-success">
            <span class="dashicons dashicons-yes-alt"></span> Candidate pipeline status updated successfully.
        </div>
    <?php endif; ?>

    <div class="nkrp-kanban-board">
        
        <?php 
        $columns = [
            'new' => ['title' => 'New Applicants', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
            'screening' => ['title' => 'Screening', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
            'interview' => ['title' => 'Interviewing', 'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
            'hired' => ['title' => 'Hired / Offered', 'color' => '#10b981', 'bg' => '#dcfce7'],
            'rejected' => ['title' => 'Rejected', 'color' => '#ef4444', 'bg' => '#fef2f2']
        ];
        ?>

        <?php foreach ($columns as $status_key => $col): ?>
            <div class="nkrp-kanban-col">
                <div class="nkrp-kanban-col-header" style="border-top: 3px solid <?= $col['color'] ?>; background: <?= $col['bg'] ?>;">
                    <h4><?= esc_html($col['title']) ?></h4>
                    <span class="nkrp-badge-count" style="background: <?= $col['color'] ?>; color: #fff;">
                        <?= count($board[$status_key]) ?>
                    </span>
                </div>
                
                <div class="nkrp-kanban-cards">
                    <?php if (empty($board[$status_key])): ?>
                        <div class="nkrp-empty-card">No candidates in this stage.</div>
                    <?php else: ?>
                        <?php foreach ($board[$status_key] as $app): ?>
                            <div class="nkrp-ats-card">
                                <div class="nkrp-card-header">
                                    <strong><?= esc_html($app->candidate_name) ?></strong>
                                    <span class="nkrp-card-date"><?= date_i18n('M j', strtotime($app->created_at ?? time())) ?></span>
                                </div>
                                <div class="nkrp-card-job">
                                    <span class="dashicons dashicons-portfolio"></span> <?= esc_html($app->job_title) ?>
                                </div>
                                <div class="nkrp-card-resume">
                                    <span class="dashicons dashicons-media-document"></span> <?= esc_html($app->resume_title) ?>
                                </div>
                                
                                <div class="nkrp-card-actions">
                                    <button type="button" class="nkrp-btn-view" onclick="alert('Premium Feature: View full Candidate JSON Resume and Cover Letter coming soon!')">View CV</button>
                                    
                                    <form method="POST" action="" class="nkrp-status-form">
                                        <?php wp_nonce_field('nkrp_ats_action', 'nkrp_ats_nonce'); ?>
                                        <input type="hidden" name="application_id" value="<?= esc_attr((string)$app->app_id) ?>">
                                        <select name="new_status" onchange="this.form.submit()" class="nkrp-status-select">
                                            <option value="new" <?= $status_key === 'new' ? 'selected' : '' ?>>New</option>
                                            <option value="screening" <?= $status_key === 'screening' ? 'selected' : '' ?>>Screening</option>
                                            <option value="interview" <?= $status_key === 'interview' ? 'selected' : '' ?>>Interview</option>
                                            <option value="hired" <?= $status_key === 'hired' ? 'selected' : '' ?>>Hired</option>
                                            <option value="rejected" <?= $status_key === 'rejected' ? 'selected' : '' ?>>Reject</option>
                                        </select>
                                        <input type="hidden" name="nkrp_ats_update_status" value="1">
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<style>
    /* SaaS ATS Kanban Styling */
    .nkrp-ats-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 30px; }
    .nkrp-ats-header { margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; }
    .nkrp-ats-header h2 { margin: 0 0 5px 0; color: #0f172a; font-size: 22px; }
    .nkrp-ats-header p { margin: 0; color: #64748b; font-size: 14px; }
    
    .nkrp-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; }
    .nkrp-alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .nkrp-alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Kanban Board Layout */
    .nkrp-kanban-board { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; }
    .nkrp-kanban-col { flex: 0 0 280px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; flex-direction: column; max-height: 800px; }
    
    .nkrp-kanban-col-header { padding: 15px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .nkrp-kanban-col-header h4 { margin: 0; font-size: 14px; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; }
    .nkrp-badge-count { padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    
    .nkrp-kanban-cards { padding: 15px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 15px; }
    
    .nkrp-empty-card { background: transparent; border: 1px dashed #cbd5e1; color: #94a3b8; font-size: 13px; text-align: center; padding: 20px; border-radius: 6px; }
    
    /* Candidate Cards */
    .nkrp-ats-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: box-shadow 0.2s; }
    .nkrp-ats-card:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    
    .nkrp-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .nkrp-card-header strong { color: #0f172a; font-size: 15px; }
    .nkrp-card-date { font-size: 11px; color: #94a3b8; }
    
    .nkrp-card-job, .nkrp-card-resume { font-size: 12px; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nkrp-card-job .dashicons, .nkrp-card-resume .dashicons { font-size: 14px; width: 14px; height: 14px; color: #94a3b8; }
    
    <div class="nkrp-card-actions">
                                    <!-- SMART CV BUTTON -->
                                    <?php if (!empty($app->file_path)): ?>
                                        <a href="<?= esc_url($app->file_path) ?>" target="_blank" class="nkrp-btn-view" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-pdf"></span> View PDF
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="nkrp-btn-view" onclick="alert('This candidate used the Digital Builder. Premium digital profile viewing coming soon!')" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-media-document"></span> Digital CV
                                        </button>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="" class="nkrp-status-form">
    .nkrp-btn-view { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .nkrp-btn-view:hover { background: #2563eb; color: #ffffff; }
    
    .nkrp-status-form { margin: 0; flex: 1; }
    .nkrp-status-select { width: 100%; padding: 5px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #334155; cursor: pointer; }
    .nkrp-status-select:focus { outline: none; border-color: #2563eb; }

    /* New Secondary Button Style for Navigation */
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    
    /* New Secondary Button Style for Navigation */
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }

    /* NEW: Mobile Responsiveness for ATS Kanban Board */
    @media (max-width: 768px) {
        .nkrp-ats-container {
            padding: 15px;
        }
        .nkrp-kanban-board {
            flex-direction: column; /* Stack columns vertically */
            gap: 30px;
            overflow-x: visible; /* Remove horizontal scroll */
        }
        .nkrp-kanban-col {
            flex: 1 1 auto;
            max-height: none; /* Let columns expand to fit content */
        }
        .nkrp-kanban-cards {
            overflow-y: visible; /* Remove inner vertical scroll on mobile */
        }
    }
</style>
</style>