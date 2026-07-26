<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nk_get_dashboard_sidebar' ) ) {
    function nk_get_dashboard_sidebar( $user, $is_employer, $is_candidate, $is_admin ) {
        ob_start();
        $user_id = get_current_user_id();
        $active_view = function_exists('nk_get_active_workspace') ? nk_get_active_workspace($user_id) : 'candidate';
        
        $unread_count = function_exists('nk_get_unread_notification_count') ? nk_get_unread_notification_count($user_id) : 0;
        $dot_html = $unread_count > 0 ? '<span style="background:#ef4444; color:#fff; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:bold; margin-left:auto;">' . $unread_count . ' New</span>' : '';

        $unread_chats = function_exists('nk_get_unread_message_count') ? nk_get_unread_message_count($user_id) : 0;
        $chat_dot_html = $unread_chats > 0 ? '<span style="background:#0A66C2; color:#fff; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:bold; margin-left:auto;">' . $unread_chats . ' New</span>' : '';
        ?>
        <aside class="nk-dashboard-sidebar" style="background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box;">
            
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                <div style="font-size: 38px; margin-bottom: 8px;">👤</div>
                <h3 style="margin: 0; font-size: 16px; color: #1e293b; font-weight: 700;"><?php echo esc_html( $user->display_name ); ?></h3>
                <span style="font-size: 11px; color: #0A66C2; background: #eef4ff; padding: 4px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase; margin-top: 8px; display: inline-block;">
                    <?php echo ( $active_view === 'employer' ) ? 'Employer Panel' : 'Hospitality Talent'; ?>
                </span>
            </div>
            
            <div class="nk-dashboard-menu">
                <style>
                    .nk-sidebar-link { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 8px; color: #475569; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s; }
                    .nk-sidebar-link:hover { background: #f8fafc; color: #0A66C2; }
                    .nk-sidebar-header { margin: 20px 0 8px 10px; font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 800; letter-spacing: 0.5px; }
                </style>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                
                <li class="nk-sidebar-header">Main Workspace</li>
                <li><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="nk-sidebar-link">🏠 Dashboard Overview</a></li>
                
                <?php if ( $active_view === 'employer' ) : ?>
                    <li class="nk-sidebar-header">Recruitment Hub</li>
                    <li><a href="<?php echo esc_url( home_url( '/post-job/' ) ); ?>" class="nk-sidebar-link">💼 Post a Job</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/manage-jobs/' ) ); ?>" class="nk-sidebar-link">📋 Manage Jobs</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/applications/' ) ); ?>" class="nk-sidebar-link">📥 Applicant Tracker</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/talent-database/' ) ); ?>" class="nk-sidebar-link">🔍 Global Talent Search</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/company-profile/' ) ); ?>" class="nk-sidebar-link">🏢 Company Profile</a></li>
                <?php else : ?>
                    <li class="nk-sidebar-header">Career Hub</li>
                    <li><a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="nk-sidebar-link">👤 My Profile / CV</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/applied-jobs/' ) ); ?>" class="nk-sidebar-link">✓ Applied Jobs</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/saved-jobs/' ) ); ?>" class="nk-sidebar-link">♡ Saved Bookmarks</a></li>
                    
                    <li>
                        <a href="<?php echo esc_url( home_url( '/dashboard/?tab=cv-studio' ) ); ?>" class="nk-sidebar-link">
                            🤖 AI CV Studio <span style="background:#ef4444; color:#fff; font-size:10px; padding:2px 6px; border-radius:4px; margin-left:auto;">NEW</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nk-sidebar-header">Communications</li>
                <li><a href="<?php echo esc_url( home_url( '/dashboard/?tab=messages' ) ); ?>" class="nk-sidebar-link">💬 Inbox <?php echo $chat_dot_html; ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/dashboard/?tab=notifications' ) ); ?>" class="nk-sidebar-link">🔔 Notifications <?php echo $dot_html; ?></a></li>

                <li class="nk-sidebar-header">Account</li>
                <li><a href="<?php echo esc_url( home_url( '/dashboard/?tab=settings' ) ); ?>" class="nk-sidebar-link">⚙️ Settings & Billing</a></li>
                <li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="nk-sidebar-link" style="color: #ef4444;">🚪 Secure Logout</a></li>
                
                </ul>
            </div>
        </aside>
        
        <script>
        if (typeof nk_switch_init === 'undefined') {
            var nk_switch_init = true;
            document.body.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('nk-workspace-switch-btn')) {
                    e.preventDefault();
                    e.target.innerText = 'Switching...';
                    let fd = new FormData();
                    fd.append('action', 'nk_switch_user_workspace');
                    fd.append('target_context', e.target.getAttribute('data-target'));
                    fd.append('security', '<?php echo wp_create_nonce("nk_workspace_nonce"); ?>');
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd })
                    .then(res => res.json()).then(data => { if(data.success) window.location.href = data.data.redirect; });
                }
            });
        }
        </script>
        <?php
        return ob_get_clean();
    }
}