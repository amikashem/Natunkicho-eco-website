<?php if (!defined('ABSPATH')) exit; 
// Scope variables injected from Shortcode: $user, $is_verified, $profile_completion, $candidate, $applied_jobs_count, $profile_views, $saved_jobs_count, $ui_alerts

$base_url = home_url('/candidate-dashboard/');
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
?>

<div class="nkrp-dashboard-wrapper">
    <div class="nkrp-dashboard-sidebar">
        <div class="nkrp-user-profile">
            <div class="nkrp-avatar">
                <?php if (!empty($candidate->profile_photo_id)): ?>
                    <img src="<?= esc_url(wp_get_attachment_image_url($candidate->profile_photo_id, 'thumbnail')) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid #fff;">
                <?php else: ?>
                    <span class="dashicons dashicons-admin-users"></span>
                <?php endif; ?>
            </div>
            <h4><?= esc_html(!empty($candidate->first_name) ? $candidate->first_name . ' ' . $candidate->last_name : $user->user_login) ?></h4>
            <span class="nkrp-role-badge">Candidate Workspace</span>
        </div>

        <ul class="nkrp-dashboard-menu">
            <li class="<?= $current_tab === 'overview' ? 'active' : '' ?>">
                <a href="<?= esc_url($base_url) ?>"><span class="dashicons dashicons-dashboard"></span> Dashboard</a>
            </li>
            <li class="<?= $current_tab === 'profile' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'profile', $base_url)) ?>"><span class="dashicons dashicons-id"></span> My Profile & CV</a>
            </li>
            <li class="<?= $current_tab === 'applied-jobs' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'applied-jobs', $base_url)) ?>"><span class="dashicons dashicons-portfolio"></span> Applied Jobs</a>
            </li>
            <li class="<?= $current_tab === 'saved-jobs' ? 'active' : '' ?>">
                <a href="<?= esc_url(add_query_arg('tab', 'saved-jobs', $base_url)) ?>"><span class="dashicons dashicons-star-filled"></span> Saved Jobs</a>
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
                    <input type="hidden" name="nkrp_switch_role" value="1">
                    <button type="submit" class="nkrp-btn-switch-role" style="width: 100%; text-align: left; background: none; border: none; color: #64748b; padding: 12px 16px; font-size: 15px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.2s;">
                        <span class="dashicons dashicons-update"></span> Switch to Employer
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
        // 🔥 10X FIX: Removed get_defined_vars() array duplication to fix the 1GB RAM memory leak!
        // We now use native PHP includes, which inherently pass all variables securely and use 0 extra memory.

        switch ($current_tab):
            
            case 'profile':
                $file = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-profile-edit.php';
                if (file_exists($file)) include $file;
                break;

            case 'preview': 
                $file = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-profile-preview.php';
                if (file_exists($file)) include $file;
                break;

            case 'applied-jobs': 
                $file = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-applied-jobs.php';
                if (file_exists($file)) include $file;
                break;

            case 'saved-jobs': 
                $file = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-saved-jobs.php';
                if (file_exists($file)) include $file;
                break;

            case 'messages': 
                // Reuse the Employer Message UI (Universal Chat File) safely
                $file = NKRP_PLUGIN_PATH . 'app/Employer/Views/frontend-messages.php';
                if (file_exists($file)) include $file;
                break;

            case 'settings':
                $file = NKRP_PLUGIN_PATH . 'app/Candidate/Views/frontend-settings.php';
                if (file_exists($file)) include $file;
                break;

            // ====================================================================
            // CANDIDATE OVERVIEW DASHBOARD
            // ====================================================================
            case 'overview':
            default:
        ?>
            <div class="nkrp-dashboard-header">
                <h2>Candidate Overview</h2>
                <a href="<?= esc_url(home_url('/jobs/')) ?>" class="nkrp-btn-primary"><span class="dashicons dashicons-search"></span> Find a Job</a>
            </div>

            <?php if (!empty($ui_alerts)): ?>
                <?php foreach($ui_alerts as $alert): ?>
                    <div class="nkrp-alert nkrp-alert-<?= esc_attr($alert['type']) ?>" style="display:flex; align-items:center; gap:10px; margin-bottom:15px; padding:15px; border-radius:8px; <?= $alert['type'] == 'warning' ? 'background:#fffbeb; color:#92400e; border:1px solid #fde68a;' : 'background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;' ?>">
                        <span class="dashicons <?= esc_attr($alert['icon']) ?>" style="font-size: 20px;"></span>
                        <span><?= wp_kses_post($alert['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($_GET['welcome']) && $_GET['welcome'] == '1'): ?>
                <div class="nkrp-alert nkrp-alert-success" style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
                    <span class="dashicons dashicons-yes-alt"></span> Welcome to your new Candidate Workspace! Complete your profile to get started.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['verification_resent']) && $_GET['verification_resent'] == '1'): ?>
                <div class="nkrp-alert nkrp-alert-success" style="background:#eff6ff; color:#1e40af; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bfdbfe;">
                    <span class="dashicons dashicons-email-alt"></span> A new verification link has been sent to your email address.
                </div>
            <?php endif; ?>

            <?php if (!$is_verified): ?>
                <div class="nkrp-alert nkrp-alert-warning" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; background:#fef3c7; color:#92400e; border:1px solid #fde68a; padding:15px; border-radius:8px; margin-bottom:20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="dashicons dashicons-warning" style="color: #b45309; font-size: 24px; width: 24px; height: 24px;"></span>
                        <div>
                            <strong style="display: block; color: #78350f;">Action Required: Verify Your Email</strong>
                            <span style="font-size: 13px; color: #92400e;">You can browse jobs, but you must verify your email before applying.</span>
                        </div>
                    </div>
                    <form method="POST" action="" style="margin: 0;">
                        <?php wp_nonce_field('resend_verify_action', 'nkrp_resend_verify_nonce'); ?>
                        <button type="submit" name="nkrp_resend_verification" class="nkrp-btn-secondary" style="padding: 6px 12px; font-size: 13px; background:#fff;">Resend Email</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="nkrp-completion-widget">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <strong style="color:#0f172a; font-size:15px;">Profile Completion</strong>
                    <strong style="color:#2563eb; font-size:15px;"><?= esc_html((string)$profile_completion) ?>%</strong>
                </div>
                <div class="nkrp-progress-track">
                    <div class="nkrp-progress-fill" style="width: <?= esc_attr((string)$profile_completion) ?>%;"></div>
                </div>
                <?php if ($profile_completion < 100): ?>
                    <p style="font-size: 13px; color: #64748b; margin: 12px 0 0 0; display:flex; align-items:center; gap:5px;">
                        <span class="dashicons dashicons-info" style="font-size:14px; width:14px; height:14px; color:#3b82f6;"></span>
                        <?php if (!$is_verified): ?>
                            Next step: <a href="#" style="color:#2563eb; text-decoration:none; font-weight:600;">Verify your email address (+20%)</a>
                        <?php else: ?>
                            Next step: <a href="<?= esc_url(add_query_arg('tab', 'profile', $base_url)) ?>" style="color:#2563eb; text-decoration:none; font-weight:600;">Update your Profile & Upload CV to reach 100%</a>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="nkrp-stats-grid">
                <div class="nkrp-stat-card">
                    <div class="nkrp-stat-icon"><span class="dashicons dashicons-portfolio"></span></div>
                    <div class="nkrp-stat-details">
                        <h3><?= esc_html((string)($applied_jobs_count ?? 0)) ?></h3>
                        <p>Applied Jobs</p>
                    </div>
                </div>
                <div class="nkrp-stat-card">
                    <div class="nkrp-stat-icon" style="background:#dcfce7; color:#166534;"><span class="dashicons dashicons-visibility"></span></div>
                    <div class="nkrp-stat-details">
                        <h3><?= esc_html((string)($profile_views ?? 0)) ?></h3>
                        <p>Profile Views</p>
                    </div>
                </div>
                <div class="nkrp-stat-card">
                    <div class="nkrp-stat-icon" style="background:#fef9c3; color:#a16207;"><span class="dashicons dashicons-star-filled"></span></div>
                    <div class="nkrp-stat-details">
                        <h3><?= esc_html((string)($saved_jobs_count ?? 0)) ?></h3>
                        <p>Saved Jobs</p>
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
    /* Premium SaaS Dashboard Styles - Synchronized with Employer Dashboard */
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
    .nkrp-dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .nkrp-dashboard-header h2 { margin: 0; font-size: 26px; color: #0f172a; font-weight: 800; letter-spacing: -0.5px;}
    
    .nkrp-btn-primary { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s, box-shadow 0.2s; border: none; cursor: pointer;}
    .nkrp-btn-primary:hover { background: #1d4ed8; color: #fff; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; justify-content: center; align-items: center; }
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    
    /* Progress Bar */
    .nkrp-completion-widget { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .nkrp-progress-track { width: 100%; height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; margin-top: 8px;}
    .nkrp-progress-fill { height: 100%; background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); border-radius: 5px; transition: width 0.8s ease-out; }

    /* Stats Grid */
    .nkrp-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    .nkrp-stat-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: transform 0.2s; }
    .nkrp-stat-card:hover { transform: translateY(-3px); }
    .nkrp-stat-icon { width: 56px; height: 56px; border-radius: 16px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nkrp-stat-icon .dashicons { font-size: 28px; width: 28px; height: 28px; }
    .nkrp-stat-details h3 { margin: 0 0 4px 0; font-size: 28px; color: #0f172a; font-weight: 800; line-height: 1; }
    .nkrp-stat-details p { margin: 0; font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
    
    @media(max-width: 992px) {
        .nkrp-dashboard-wrapper { grid-template-columns: 1fr; }
        .nkrp-dashboard-sidebar { border-right: none; border-bottom: 1px solid #e2e8f0; }
        .nkrp-dashboard-main { padding: 20px; }
        .nkrp-stats-grid { grid-template-columns: 1fr; }
    }
</style>