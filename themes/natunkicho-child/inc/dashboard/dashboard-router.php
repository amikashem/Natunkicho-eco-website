<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * SaaS UNIFIED DASHBOARD ROUTER
 * =========================================================================
 */
function nk_unified_dashboard_router_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="nk-dash-card" style="text-align:center; padding: 40px;"><h3>Welcome to Natunkicho. Please login to enter your portal workspace.</h3></div>';
    }

    $user_id = get_current_user_id();
    $user_obj = wp_get_current_user();

    $active_view = function_exists('nk_get_active_workspace') ? nk_get_active_workspace($user_id) : 'candidate';
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
    
    // --- CHECK PREMIUM STATUS ---
    $is_premium = false;
    if ( function_exists( 'wc_customer_bought_product' ) && wc_customer_bought_product( $user_obj->user_email, $user_id, 2949 ) ) {
        $is_premium = true;
    } elseif ( function_exists('nk_is_user_premium') && nk_is_user_premium($user_id) ) {
        $is_premium = true;
    } elseif ( in_array('administrator', (array)$user_obj->roles) ) {
        $is_premium = true;
    }

    $sidebar_file = get_stylesheet_directory() . '/inc/dashboard/sidebar.php';
    $widgets_file = get_stylesheet_directory() . '/inc/dashboard/widgets.php';
    $settings_file = get_stylesheet_directory() . '/inc/dashboard/settings.php'; 
    
    if ( file_exists( $sidebar_file ) ) include_once $sidebar_file;
    if ( file_exists( $widgets_file ) ) include_once $widgets_file;
    if ( file_exists( $settings_file ) ) include_once $settings_file; 

    ob_start();
    ?>
    <style>
        .nk-runtime-flex-layout {
            display: flex !important; gap: 30px !important; max-width: 1400px !important; margin: 30px auto !important; align-items: flex-start !important; width: 100% !important; box-sizing: border-box !important;
        }
        .nk-runtime-sidebar-column { width: 280px !important; flex-shrink: 0 !important; }
        .nk-runtime-workspace-column { flex: 1 !important; min-width: 0 !important; }
        .nk-studio-active-workspace { max-width: 100% !important; padding: 0 20px !important; }
        
        @media (max-width: 991px) {
            .nk-runtime-flex-layout { flex-direction: column !important; gap: 20px !important; }
            .nk-runtime-sidebar-column, .nk-runtime-workspace-column { width: 100% !important; }
        }
    </style>

    <div class="nk-runtime-flex-layout <?php echo ($current_tab === 'cv-studio') ? 'nk-studio-active-workspace' : ''; ?>">
        
        <?php if ( $current_tab !== 'cv-studio' ) : ?>
            <div class="nk-runtime-sidebar-column">
                <?php 
                if ( function_exists('nk_get_dashboard_sidebar') ) {
                    echo nk_get_dashboard_sidebar( $user_obj, ($active_view === 'employer'), ($active_view === 'candidate'), false );
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="nk-runtime-workspace-column">
            <div class="nk-dashboard-content">
                
                <?php if ( $current_tab === 'settings' ) : ?>
                    <h2 style="margin-top: 0; font-size: 26px; font-weight: 800; color: #1e293b; margin-bottom: 25px;">⚙️ Settings & Preferences</h2>
                    <?php if ( function_exists('nk_render_settings_page') ) { echo nk_render_settings_page( $active_view ); } ?>

                <?php elseif ( $current_tab === 'notifications' ) : ?>
                    <h2 style="margin-top: 0; font-size: 26px; font-weight: 800; color: #1e293b; margin-bottom: 25px;">🔔 Notification Center</h2>
                    <?php if ( function_exists('nk_render_notifications_page') ) { echo nk_render_notifications_page(); } ?>

                <?php elseif ( $current_tab === 'messages' ) : ?>
                    <h2 style="margin-top: 0; font-size: 26px; font-weight: 800; color: #1e293b; margin-bottom: 25px;">💬 Direct Messages</h2>
                    <?php if ( function_exists('nk_render_messages_page') ) { echo nk_render_messages_page($active_view); } ?>
                
                <?php elseif ( $current_tab === 'cv-studio' ) : ?>
                    <?php 
                    $cv_builder_file = get_stylesheet_directory() . '/inc/cv-builder/cv-builder.php';
                    if ( file_exists( $cv_builder_file ) ) { require_once $cv_builder_file; } 
                    ?>

                <?php else : ?>
                    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; padding: 40px; color: #fff; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);">
                        <div>
                            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #fff;">
                                Welcome back, <?php echo esc_html( $user_obj->display_name ); ?> 👋
                            </h2>
                            <p style="margin: 0; font-size: 15px; opacity: 0.9; max-width: 500px; line-height: 1.5;">
                                <?php if ($active_view === 'employer'): ?>
                                    Your hiring dashboard is ready. Find top talent and manage your hiring pipeline today.
                                <?php else: ?>
                                    Your hospitality career dashboard is ready. Take action today to land your next big role.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <?php if($active_view === 'employer'): ?>
                                <a href="<?php echo esc_url(site_url('/post-job/')); ?>" style="background: #10b981; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.2s;">➕ Post a Job</a>
                                <a href="<?php echo esc_url(site_url('/talent-database/')); ?>" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.2s;">🔍 Browse Talent</a>
                            <?php else: ?>
                                <a href="?tab=cv-studio" style="background: #10b981; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.2s;">✨ AI CV Studio</a>
                                <a href="<?php echo esc_url(site_url('/jobs/')); ?>" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.2s;">🔍 Search Jobs</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nk-dashboard-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 30px;">
                        <?php 
                        if ( $active_view === 'employer' ) {
                            if ( function_exists('nk_get_dashboard_widget_card') ) {
                                echo nk_get_dashboard_widget_card( 'Active Vacancies', '0', 'Publish and maintain your current hiring pipelines.' );
                                echo nk_get_dashboard_widget_card( 'Applications Received', '0', 'Review and filter incoming talent resumes.' );
                            }
                        } else {
                            if ( function_exists('nk_get_dashboard_widget_card') ) {
                                echo nk_get_dashboard_widget_card( 'Jobs Applied', '0', 'Monitor updates on submitted applications.' );
                                echo nk_get_dashboard_widget_card( 'Saved Bookmarks', '0', 'Positions flagged for final proofing evaluations.' );
                            }
                        }
                        ?>
                    </div>

                    <?php if ( $active_view === 'employer' ) : ?>
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
                            <div class="nk-dash-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px;">
                                <h3 style="margin-top:0; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; font-size: 18px; color: #0f172a;">🚀 Employer Quick Actions</h3>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    <li style="padding: 15px 0; border-bottom: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 24px;">🏢</span>
                                        <div><h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;">Update Company Profile</h4><p style="margin: 0; font-size: 13px; color: #64748b;">Attract top talent by showcasing your brand, culture, and benefits.</p></div>
                                    </li>
                                    <li style="padding: 15px 0; border-bottom: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 24px;">📥</span>
                                        <div><h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;">Review Applications</h4><p style="margin: 0; font-size: 13px; color: #64748b;">Check your inbox for new candidates applying to your vacancies.</p></div>
                                    </li>
                                    <li style="padding: 15px 0; display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 24px;">💬</span>
                                        <div><h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;">Message Candidates</h4><p style="margin: 0; font-size: 13px; color: #64748b;">Reach out directly to hospitality professionals to schedule interviews.</p></div>
                                    </li>
                                </ul>
                            </div>
                            
                            <?php if ( ! $is_premium ) : ?>
                                <div class="nk-dash-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; text-align: center;">
                                    <div style="font-size: 40px; margin-bottom: 15px;">👑</div>
                                    <h4 style="margin: 0 0 10px 0; color: #0f172a; font-size: 16px;">Premium Hiring Tools</h4>
                                    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5;">Hire the best hospitality talent 10x faster with Premium Pro. Unlock unlimited CV downloads and direct messaging.</p>
                                    <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="display: inline-block; background: #0A66C2; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 13px; width: 100%; box-sizing: border-box;">Unlock Premium</a>
                                </div>
                            <?php else: ?>
                                <div class="nk-dash-card" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 25px; text-align: center;">
                                    <div style="font-size: 40px; margin-bottom: 15px;">✅</div>
                                    <h4 style="margin: 0 0 10px 0; color: #166534; font-size: 16px;">Premium Active</h4>
                                    <p style="font-size: 13px; color: #15803d; margin-bottom: 0; line-height: 1.5;">You have unlimited access to the Talent Database and priority Job Boosting.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
                            <div class="nk-dash-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px;">
                                <h3 style="margin-top:0; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; font-size: 18px; color: #0f172a;">🚀 Recommended Next Steps</h3>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    <li style="padding: 15px 0; border-bottom: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 24px;">📄</span>
                                        <div><h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;">Polish Your Digital CV</h4><p style="margin: 0; font-size: 13px; color: #64748b;">Use our AI Studio to score higher on ATS tracking systems.</p></div>
                                    </li>
                                    <li style="padding: 15px 0; border-bottom: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 15px;">
                                        <span style="font-size: 24px;">🔔</span>
                                        <div><h4 style="margin: 0 0 5px 0; font-size: 15px; color: #1e293b;">Set Up Job Alerts</h4><p style="margin: 0; font-size: 13px; color: #64748b;">Never miss a top hospitality opportunity matching your skills.</p></div>
                                    </li>
                                </ul>
                            </div>
                            <div><?php if ( function_exists( 'nk_profile_strength_shortcode' ) ) echo nk_profile_strength_shortcode(); ?></div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
remove_shortcode( 'nk_dashboard' );
add_shortcode( 'nk_dashboard', 'nk_unified_dashboard_router_shortcode' );