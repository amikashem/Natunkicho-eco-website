<?php if (!defined('ABSPATH')) exit; 
// Scope variables from Shortcode: $user, $employer_companies, $active_jobs_count, $total_applications, $is_verified, $current_tab, $edit_company, $edit_job

$base_url = home_url('/employer-dashboard/');
$is_premium = apply_filters('nkrp_is_user_premium', false, $user->ID); // Check premium status
?>

<div class="nkrp-dashboard-wrapper">
    <div class="nkrp-dashboard-sidebar">
        <div class="nkrp-user-profile">
            <div class="nkrp-avatar">
                <span class="dashicons dashicons-businessman"></span>
            </div>
            <h4><?= esc_html($user->display_name) ?></h4>
            <span class="nkrp-role-badge">Employer Workspace</span>
        </div>

        <ul class="nkrp-dashboard-menu">
            <li class="<?= $current_tab === 'overview' ? 'active' : '' ?>">
                <a href="<?= esc_url($base_url) ?>"><span class="dashicons dashicons-dashboard"></span> Dashboard</a>
            </li>
            <li class="<?= in_array($current_tab, ['companies', 'add-company', 'edit-company']) ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'companies', $base_url)) ?>"><span class="dashicons dashicons-building"></span> My Companies</a>
            </li>
            <li class="<?= in_array($current_tab, ['jobs', 'add-job', 'edit-job']) ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'jobs', $base_url)) ?>"><span class="dashicons dashicons-portfolio"></span> Manage Jobs</a>
            </li>
            
            <li class="<?= $current_tab === 'talent-search' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'talent-search', $base_url)) ?>"><span class="dashicons dashicons-search"></span> Talent Search</a>
            </li>
            
            <li class="<?= $current_tab === 'ats' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'ats', $base_url)) ?>"><span class="dashicons dashicons-groups"></span> Applications (ATS)</a>
            </li>
            <li class="<?= $current_tab === 'messages' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'messages', $base_url)) ?>"><span class="dashicons dashicons-email"></span> Messages</a>
            </li>
            <li class="<?= $current_tab === 'settings' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'settings', $base_url)) ?>"><span class="dashicons dashicons-admin-settings"></span> Settings</a>
            </li>
            
            <li style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                <form method="POST" action="" style="margin:0;">
                    <?php wp_nonce_field('switch_role_action', 'nkrp_switch_role_nonce'); ?>
                    <input type="hidden" name="nkrp_action" value="switch_to_candidate">
                    <button type="submit" class="nkrp-btn-switch-role" style="width: 100%; text-align: left; background: none; border: none; color: #64748b; padding: 12px 16px; font-size: 15px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.2s;">
                        <span class="dashicons dashicons-update"></span> Switch to Candidate
                    </button>
                </form>
            </li>
            <li>
                <a href="<?= esc_url(wp_logout_url(home_url('/login/'))) ?>" class="nkrp-logout"><span class="dashicons dashicons-external"></span> Logout</a>
            </li>
        </ul>
    </div>

    <div class="nkrp-dashboard-main">
        <?php 
        $scope_vars = get_defined_vars();

        switch ($current_tab):
            
            // --- ACTUAL FILE ROUTING ---
            case 'companies':
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/company-list.php';
                if (function_exists('nkrp_safe_render_view')) nkrp_safe_render_view($file, $scope_vars); elseif(file_exists($file)) include $file;
                break;

            case 'add-company':
            case 'edit-company':
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-company-edit.php';
                if (function_exists('nkrp_safe_render_view')) nkrp_safe_render_view($file, $scope_vars); elseif(file_exists($file)) include $file;
                break;

            case 'jobs':
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-manage-jobs.php';
                if (function_exists('nkrp_safe_render_view')) nkrp_safe_render_view($file, $scope_vars); elseif(file_exists($file)) include $file;
                break;

            case 'add-job':
            case 'edit-job':
                $file = NKRP_PLUGIN_PATH . 'app/Jobs/Views/frontend-post-job.php';
                if (function_exists('nkrp_safe_render_view')) nkrp_safe_render_view($file, $scope_vars); elseif(file_exists($file)) include $file;
                break;

            case 'ats':
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-ats.php';
                if (!file_exists($file)) { $file = NKRP_PLUGIN_PATH . 'app/ATS/Views/employer-ats-dashboard.php'; }
                if (function_exists('nkrp_safe_render_view')) nkrp_safe_render_view($file, $scope_vars); elseif(file_exists($file)) include $file;
                break;

            case 'messages':
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-messages.php';
                if (file_exists($file)) {
                    include $file; 
                } else {
                    echo "<div class='nkrp-alert nkrp-alert-warning'>Error: frontend-messages.php file is missing. Please create it first!</div>";
                }
                break;

            // ====================================================================
            // TALENT SEARCH UI
            // ====================================================================
            case 'talent-search':
                global $wpdb;
                $search_query = isset($_GET['sq']) ? sanitize_text_field($_GET['sq']) : '';
                $index_table = $wpdb->prefix . 'nkrp_candidate_index';
                
                if (!empty($search_query)) {
                    $terms = array_filter(array_map('trim', explode(' ', $search_query)));
                    $where_clauses = [];
                    $sql_args = [];
                    foreach ($terms as $term) {
                        $like = '%' . $wpdb->esc_like($term) . '%';
                        $where_clauses[] = "(display_name LIKE %s OR professional_title LIKE %s OR skills LIKE %s OR location LIKE %s OR bio LIKE %s)";
                        array_push($sql_args, $like, $like, $like, $like, $like);
                    }
                    if (!empty($where_clauses)) {
                        $where_sql = implode(' AND ', $where_clauses);
                        $candidates = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$index_table} WHERE {$where_sql} ORDER BY updated_at DESC LIMIT 50", ...$sql_args));
                    } else {
                        $candidates = [];
                    }
                } else {
                    $candidates = $wpdb->get_results("SELECT * FROM {$index_table} ORDER BY updated_at DESC LIMIT 12");
                }
                ?>
                <div class="nkrp-dashboard-header">
                    <h2>Talent Database</h2>
                    <p style="color: #64748b; margin: 5px 0 0 0;">Search thousands of hospitality professionals instantly.</p>
                </div>

                <form method="GET" action="" style="display: flex; gap: 10px; margin-bottom: 30px; background: #fff; padding: 10px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <input type="hidden" name="tab" value="talent-search">
                    <span class="dashicons dashicons-search" style="color: #94a3b8; padding: 12px 10px; font-size: 20px;"></span>
                    <input type="text" name="sq" value="<?= esc_attr($search_query) ?>" placeholder="Search by job title, skill, or keyword" style="flex: 1; border: none; outline: none; font-size: 16px; background: transparent;">
                    <button type="submit" class="nkrp-btn-primary" style="padding: 12px 24px;">Search</button>
                </form>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php if (empty($candidates)): ?>
                        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                            <span class="dashicons dashicons-search" style="font-size: 40px; width: 40px; height: 40px; color: #cbd5e1; margin-bottom: 15px;"></span>
                            <h3 style="margin: 0 0 10px 0; color: #475569;">No talent found</h3>
                        </div>
                    <?php else: ?>
                        <?php foreach ($candidates as $cand): ?>
                            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                    <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: bold; font-size: 18px;">
                                        <?= esc_html(strtoupper(substr($cand->display_name, 0, 1))) ?>
                                    </div>
                                    <div>
                                        <h3 style="margin: 0 0 4px 0; font-size: 16px; color: #0f172a;"><?= esc_html($cand->display_name) ?></h3>
                                        <div style="font-size: 13px; color: #2563eb; font-weight: 500;"><?= esc_html($cand->professional_title ?: 'Professional') ?></div>
                                    </div>
                                </div>
                                <div style="font-size: 13px; color: #64748b; margin-bottom: 15px; display: flex; align-items: center; gap: 5px;">
                                    <span class="dashicons dashicons-location" style="font-size: 14px;"></span> <?= esc_html($cand->location ?: 'Location Flexible') ?>
                                </div>
                                <?php if (!empty($cand->skills)): ?>
                                    <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 6px;">
                                        <?php $skills = explode(',', $cand->skills); foreach (array_slice($skills, 0, 3) as $skill): ?>
                                            <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; font-size: 11px; padding: 4px 8px; border-radius: 4px;"><?= esc_html(trim($skill)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div style="display: flex; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                                    <a href="<?= esc_url(home_url('/candidate-profile/?id=' . $cand->user_id)) ?>" target="_blank" class="nkrp-btn-secondary" style="flex: 1; text-align: center; font-size: 13px; padding: 8px;">View CV</a>
                                    <?php if ($is_premium): ?>
                                        <a href="<?= esc_url(add_query_arg(['tab' => 'messages', 'new_msg' => $cand->user_id], $base_url)) ?>" class="nkrp-btn-primary" style="flex: 1; text-align: center; font-size: 13px; padding: 8px;">Message</a>
                                    <?php else: ?>
                                        <a href="<?= esc_url(home_url('/membership/')) ?>" class="nkrp-btn-primary" style="flex: 1; text-align: center; font-size: 13px; padding: 8px; background: #fef08a; color: #854d0e; border: 1px solid #fde047;">
                                            <span class="dashicons dashicons-lock" style="font-size: 14px;"></span> Message
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php
                break;

            // ====================================================================
            // SETTINGS UI
            // ====================================================================
            case 'settings':
                $pref_app = get_user_meta($user->ID, '_nkrp_notify_apps', true) ?: 'instant';
                $pref_msg = get_user_meta($user->ID, '_nkrp_notify_msgs', true) ?: 'instant';
                ?>
                <div class="nkrp-dashboard-header"><h2>Account Settings</h2></div>
                <div class="nkrp-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <form method="POST" action="" style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
                            <h3 style="margin-top: 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-size: 18px;">Profile Information</h3>
                            <?php wp_nonce_field('update_account_action', 'nkrp_account_nonce'); ?>
                            <input type="hidden" name="nkrp_action" value="update_account">
                            <div class="nkrp-form-group" style="margin-bottom: 15px;">
                                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600; color:#475569;">Email Address</label>
                                <input type="email" name="email" value="<?= esc_attr($user->user_email) ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;" required>
                            </div>
                            <button type="submit" class="nkrp-btn-primary">Save Profile</button>
                        </form>
                    </div>
                    <div>
                        <form method="POST" action="" style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; height: 100%;">
                            <h3 style="margin-top: 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-size: 18px;">Notification Preferences</h3>
                            <?php wp_nonce_field('update_prefs_action', 'nkrp_prefs_nonce'); ?>
                            <input type="hidden" name="nkrp_action" value="update_prefs">
                            <div style="margin-bottom: 25px;">
                                <strong style="display:block; color: #0f172a; margin-bottom: 5px;">New Candidate Applications</strong>
                                <select name="pref_app" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <option value="instant" <?= $pref_app === 'instant' ? 'selected' : '' ?>>Instantly</option>
                                    <option value="daily" <?= $pref_app === 'daily' ? 'selected' : '' ?>>Daily Digest</option>
                                </select>
                            </div>
                            <button type="submit" class="nkrp-btn-primary" style="width: 100%; justify-content: center;">Save Preferences</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            // ====================================================================
            // OVERVIEW
            // ====================================================================
            case 'overview':
            default:
        ?>
            <div class="nkrp-dashboard-header">
                <h2>Employer Overview</h2> <a href="<?= esc_url(add_query_arg('tab', 'add-job', $base_url)) ?>" class="nkrp-btn-primary"><span class="dashicons dashicons-plus-alt2"></span> Post a New Job</a>
            </div>
            
            <div class="nkrp-stats-grid">
                <div class="nkrp-stat-card">
                    <div class="nkrp-stat-icon"><span class="dashicons dashicons-building"></span></div>
                    <div class="nkrp-stat-details">
                        <h3><?= esc_html((string)(is_array($employer_companies) ? count($employer_companies) : 0)) ?></h3>
                        <p>Companies</p>
                    </div>
                </div>
                <div class="nkrp-stat-card">
                    <div class="nkrp-stat-icon" style="background:#dcfce7; color:#166534;"><span class="dashicons dashicons-portfolio"></span></div>
                    <div class="nkrp-stat-details">
                        <h3><?= esc_html((string)$active_jobs_count) ?></h3>
                        <p>Active Jobs</p>
                    </div>
                </div>
            </div>
        <?php 
            break; 
        endswitch; 
        ?>
    </div>
</div>

<style>
    /* Premium SaaS Dashboard Styles */
    .nkrp-dashboard-wrapper { display: grid; grid-template-columns: 260px 1fr; gap: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; min-height: 800px; margin: 30px auto; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
    .nkrp-dashboard-sidebar { background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 20px; }
    .nkrp-user-profile { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
    .nkrp-avatar { width: 64px; height: 64px; background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; color: #3730a3; overflow: hidden; border: 2px solid #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nkrp-avatar .dashicons { font-size: 32px; width: 32px; height: 32px; }
    .nkrp-user-profile h4 { margin: 0 0 5px 0; font-size: 16px; color: #0f172a; font-weight: 700;}
    .nkrp-role-badge { font-size: 11px; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-weight: 700; border: 1px solid #e2e8f0; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .nkrp-dashboard-menu { list-style: none; padding: 0; margin: 0; }
    .nkrp-dashboard-menu li { margin-bottom: 5px; }
    .nkrp-dashboard-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #64748b; text-decoration: none; font-weight: 500; font-size: 15px; border-radius: 8px; transition: all 0.2s; }
    .nkrp-dashboard-menu a .dashicons { font-size: 18px; width: 18px; height: 18px; }
    .nkrp-dashboard-menu a:hover { background: #f8fafc; color: #2563eb; transform: translateX(4px); }
    .nkrp-dashboard-menu li.active a { background: #eff6ff; color: #2563eb; font-weight: 600; }
    
    .nkrp-dashboard-menu a.nkrp-logout { color: #dc2626; margin-top: 20px; }
    .nkrp-dashboard-menu a.nkrp-logout:hover { background: #fef2f2; color: #b91c1c; transform: none; }
    .nkrp-btn-switch-role:hover { background: #f8fafc !important; color: #0f172a !important; transform: translateX(4px); }

    .nkrp-dashboard-main { padding: 40px; }
    .nkrp-dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
    .nkrp-dashboard-header h2 { margin: 0; font-size: 26px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px;}
    
    .nkrp-btn-primary { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s, box-shadow 0.2s; border: none; cursor: pointer;}
    .nkrp-btn-primary:hover { background: #1d4ed8; color: #fff; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; justify-content: center; align-items: center; }
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    
    .nkrp-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    .nkrp-stat-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: transform 0.2s; }
    .nkrp-stat-card:hover { transform: translateY(-3px); }
    .nkrp-stat-icon { width: 56px; height: 56px; border-radius: 16px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nkrp-stat-icon .dashicons { font-size: 28px; width: 28px; height: 28px; }
    .nkrp-stat-details h3 { margin: 0 0 4px 0; font-size: 28px; color: #0f172a; font-weight: 800; line-height: 1; }
    .nkrp-stat-details p { margin: 0; font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
    
    /* 🔥 MOBILE RESPONSIVE FIXES */
    @media(max-width: 992px) {
        .nkrp-dashboard-wrapper { grid-template-columns: 1fr; }
        .nkrp-dashboard-sidebar { border-right: none; border-bottom: 1px solid #e2e8f0; }
        .nkrp-dashboard-main { padding: 20px; overflow-x: auto; -webkit-overflow-scrolling: touch; } /* Prevents horizontal break */
        .nkrp-dashboard-main table { min-width: 700px; } /* Ensures table content doesn't crush together */
        .nkrp-stats-grid { grid-template-columns: 1fr; }
        .nkrp-form-grid { grid-template-columns: 1fr !important; gap: 15px; }
    }
</style>