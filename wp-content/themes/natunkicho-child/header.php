<?php
/**
 * Title: NatunKicho Complete Dynamic Header & Navigation System
 * Description: Single-file enterprise architecture for NatunKicho Hospitality Ecosystem.
 * Version: 5.2.0 (10x Context-Aware SaaS Upgrade - Strict Role Isolation)
 * Author: Developer Blueprint Engine
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ==========================================
// 1. BACKEND CORE, SETTINGS & AJAX ENGINES
// ==========================================

class NatunKicho_Header_Engine {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_settings_menu' ) );
        add_action( 'admin_init', array( $this, 'initialize_theme_settings' ) );
        add_action( 'wp_ajax_nk_get_notifications', array( $this, 'ajax_fetch_notifications' ) );
        add_action( 'wp_ajax_nopriv_nk_get_notifications', array( $this, 'ajax_fetch_notifications_guest' ) );
    }

    public function register_admin_settings_menu() {
        add_menu_page(
            'NatunKicho Header Settings', 'NatunKicho Settings', 'manage_options', 
            'nk-header-settings', array( $this, 'render_settings_page' ), 'dashicons-welcome-widgets-menus', 60
        );
    }

    public function initialize_theme_settings() {
        register_setting( 'nk_header_group', 'nk_settings' );
    }

    public function render_settings_page() {
        $settings = get_option( 'nk_settings' );
        ?>
        <div class="wrap">
            <h1>NatunKicho Ecosystem Header Configuration Panel</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'nk_header_group' ); ?>
                <h2>Layer 1: Utility Bar (Top Bar Contact & Identity)</h2>
                <table class="form-table">
                    <tr><th>WhatsApp URL/Number</th><td><input type="text" name="nk_settings[whatsapp]" value="<?php echo esc_attr($settings['whatsapp'] ?? ''); ?>" placeholder="https://wa.me/qr/C4QCXMWLOLYIC1" style="width:400px;"></td></tr>
                    <tr><th>Email Address</th><td><input type="email" name="nk_settings[email]" value="<?php echo esc_attr($settings['email'] ?? ''); ?>" placeholder="info@natunkicho.com" style="width:400px;"></td></tr>
                    <tr><th>Help Center Link</th><td><input type="url" name="nk_settings[help_url]" value="<?php echo esc_url($settings['help_url'] ?? ''); ?>" placeholder="https://natunkicho.com/contact/" style="width:400px;"></td></tr>
                    <tr><th>Announcement Banner Text</th><td><input type="text" name="nk_settings[announcement]" value="<?php echo esc_attr($settings['announcement'] ?? ''); ?>" style="width:400px;"></td></tr>
                    <tr><th>Announcement Target URL</th><td><input type="url" name="nk_settings[announcement_url]" value="<?php echo esc_url($settings['announcement_url'] ?? ''); ?>" style="width:400px;"></td></tr>
                </table>
                <h2>Social Media Vectors</h2>
                <table class="form-table">
                    <tr><th>Facebook URL</th><td><input type="url" name="nk_settings[fb]" value="<?php echo esc_url($settings['fb'] ?? ''); ?>" placeholder="https://www.facebook.com/hospitality.global.hub/"></td></tr>
                    <tr><th>LinkedIn URL</th><td><input type="url" name="nk_settings[linkedin]" value="<?php echo esc_url($settings['linkedin'] ?? ''); ?>" placeholder="https://www.linkedin.com/company/food-business-success-lab/" style="width:400px;"></td></tr>
                    <tr><th>YouTube URL</th><td><input type="url" name="nk_settings[youtube]" value="<?php echo esc_url($settings['youtube'] ?? ''); ?>" placeholder="#"></td></tr>
                    <tr><th>Instagram URL</th><td><input type="url" name="nk_settings[instagram]" value="<?php echo esc_url($settings['instagram'] ?? ''); ?>" placeholder="#"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

   public function ajax_fetch_notifications() {
        check_ajax_referer( 'nk_header_nonce', 'security' );
        $user_id = get_current_user_id();
        $role    = nk_get_unified_user_role($user_id);

        // 🔥 FIX: Bypass all caching to ensure real-time bubble alerts
        $notifications = $this->generate_smart_role_notifications($role, $user_id);

        wp_send_json_success( array(
            'count' => count( $notifications ),
            'html'  => $this->compile_notification_html( $notifications )
        ));
    }

    public function ajax_fetch_notifications_guest() {
        wp_send_json_success( array( 'count' => 0, 'html' => '<li class="nk-notif-item">Please sign in to read notifications.</li>' ) );
    }

  private function generate_smart_role_notifications($role, $user_id) {
        $alerts = [];
        if ($role === 'employer') {
            $alerts[] = ['text' => '📥 ATS Update: Check your dashboard for recent applicant activity.', 'time' => 'System'];
        } elseif ($role === 'candidate') {
            $alerts[] = ['text' => '📄 Application Update: Employers are actively reviewing CVs.', 'time' => 'System'];
        }
        return $alerts;
    }

    private function compile_notification_html( $items ) {
        $out = '';
        if (empty($items)) return '<li class="nk-notif-item">No new activity.</li>';
        foreach ( $items as $item ) {
            $out .= '<li class="nk-notif-item"><strong style="color:#1e293b;">' . esc_html($item['text']) . '</strong><span class="nk-time">' . esc_html($item['time']) . '</span></li>';
        }
        $out .= '<li class="nk-notif-item" style="text-align:center; padding-top: 15px;"><a href="/dashboard/?tab=notifications" style="color:#0A66C2; font-weight:bold; text-decoration:none;">View All Activity &rarr;</a></li>';
        return $out;
    }
}
$nk_header_engine = new NatunKicho_Header_Engine();

// ==========================================
// 2. 10X UNIFIED ROLE RESOLVER
// ==========================================
if ( ! function_exists( 'nk_get_unified_user_role' ) ) {
    function nk_get_unified_user_role( $user_id = 0 ): string {
        if ( ! is_user_logged_in() ) return 'guest';

        $user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
        if ( ! $user ) return 'guest';

        $roles = (array) $user->roles;

        if ( in_array( 'administrator', $roles, true ) ) return 'admin';

        $workspace = get_user_meta( $user->ID, '_nkrp_active_workspace', true );
        if ( $workspace === 'employer' || $workspace === 'candidate' ) return $workspace;

        if ( in_array( 'nkrp_employer', $roles, true ) || in_array( 'employer', $roles, true ) ) return 'employer';
        if ( in_array( 'nkrp_candidate', $roles, true ) || in_array( 'candidate', $roles, true ) ) return 'candidate';

        return 'candidate'; 
    }
}

// Fetch System User Parameters
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$user_role    = nk_get_unified_user_role( $current_user->ID );
$nk_opt       = get_option( 'nk_settings' );

// Strict Role Isolation Flags
$is_guest     = ($user_role === 'guest');
$is_candidate = ($user_role === 'candidate');
$is_employer  = ($user_role === 'employer');
$is_admin     = ($user_role === 'admin');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='impact-site-verification' value='c60069c5-5f01-4021-a83c-2d134e53792e'>
    <?php wp_head(); ?>
    <style>
        /* CRITICAL RENDER PATH STYLE INJECTIONS - FULLY ISOLATED */
        :root {
            --nk-primary: #0066cc; --nk-dark: #111827; --nk-light: #f3f4f6;
            --nk-accent: #10b981; --nk-border: #e5e7eb; --nk-text-muted: #6b7280;
        }
        
        .nk-master-header { width:100%; position:relative; z-index:99999; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .nk-master-header.is-sticky { position:fixed; top:0; left:0; animation: nkSlideDown 0.3s ease; }
        @keyframes nkSlideDown { from { transform: translateY(-30px); } to { transform: translateY(0); } }
        
        /* Layer 1: Top Utility Bar */
        .nk-topbar { background: var(--nk-dark); color:#fff; font-size:12px; display:flex; justify-content:space-between; align-items:center; padding:8px 4%; border-bottom:1px solid #2d3748; }
        .nk-topbar a { color:#cbd5e1; text-decoration:none; margin-right:12px; transition:color 0.2s; }
        .nk-topbar a:hover { color:#fff; }
        .nk-announcement { flex-grow:1; text-align:center; color:#fef08a; font-weight:600; }

        /* Layer 2: Main Branding and Direct Nav Elements */
        .nk-main-header { display:flex; justify-content:space-between; align-items:center; padding:16px 4%; border-bottom:1px solid var(--nk-border); transition: padding 0.3s ease; }
        .nk-master-header.is-sticky .nk-main-header { padding:8px 4%; }
        .nk-logo a { font-size:24px; font-weight:800; color:var(--nk-primary); text-decoration:none; letter-spacing:-0.5px; display:flex; align-items:center; }
        
        /* Mega Nav Container Base */
        .nk-nav-menu { display:flex; list-style:none; margin:0; padding:0; }
        .nk-nav-item { position:static; padding: 0 14px; }
        .nk-nav-link { font-weight:600; color:#374151; text-decoration:none; font-size:14px; padding:12px 0; display:block; cursor:pointer; }
        .nk-nav-link:hover, .nk-nav-item:hover .nk-nav-link { color: var(--nk-primary); }

        /* Structural Mega Dropdown Grid Framework */
        .nk-mega-dropdown { position:absolute; top:100%; left:4%; right:4%; background:#fff; border:1px solid var(--nk-border); border-top:3px solid var(--nk-primary); border-radius:0 0 8px 8px; box-shadow:0 10px 25px rgba(0,0,0,0.08); display:none; grid-template-columns: repeat(4, 1fr); gap:24px; padding:28px; z-index:10000; }
        .nk-nav-item:hover .nk-mega-dropdown { display:grid; }
        
        /* The Invisible Hover Bridge */
        .nk-mega-dropdown::before, .nk-user-dropdown::before, .nk-notif-dropdown::before {
            content: ''; position: absolute; top: -30px; left: 0; width: 100%; height: 30px; background: transparent; 
        }

        .nk-mega-col h4 { font-size:13px; text-transform:uppercase; color:var(--nk-text-muted); margin:0 0 12px 0; letter-spacing:0.5px; border-bottom:1px solid var(--nk-border); padding-bottom:4px;}
        .nk-mega-col ul { list-style:none; padding:0; margin:0; }
        .nk-mega-col ul li a { display:block; padding:6px 0; color:#4b5563; text-decoration:none; font-size:14px; transition:0.2s; }
        .nk-mega-col ul li a:hover { color:var(--nk-primary); transform: translateX(3px); }
        .nk-widget-card { background:var(--nk-light); padding:12px; border-radius:6px; margin-top:8px; border:1px solid var(--nk-border); }

        /* UI Controls Right Grid */
        .nk-actions-cluster { display:flex; align-items:center; gap:16px; }
        .nk-icon-btn { background:none; border:none; position:relative; cursor:pointer; font-size:20px; color:#4b5563; padding:6px; transition: transform 0.2s; }
        .nk-icon-btn:hover { transform: scale(1.1); }
        .nk-badge { position:absolute; top:-2px; right:-2px; background:red; color:#fff; font-size:9px; border-radius:50%; width:16px; height:16px; display:flex; align-items:center; justify-content:center; font-weight:bold; border: 2px solid #fff; }
        .nk-cta-btn { background:var(--nk-primary); color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px; box-shadow:0 2px 4px rgba(0,96,204,0.2); transition: 0.2s; white-space: nowrap; }
        .nk-cta-btn:hover { filter: brightness(1.1); }
        .nk-hide-mobile { display: inline; }

        /* Search Panel overlay rules */
        .nk-search-panel { display:none; position:absolute; top:100%; left:0; width:100%; background:#fff; border-bottom:2px solid var(--nk-primary); padding:16px 4%; box-shadow:0 4px 6px rgba(0,0,0,0.05); box-sizing:border-box; z-index: 9999; }
        .nk-search-grid { display:flex; gap:12px; width:100%; max-width:900px; margin:0 auto; }
        .nk-search-grid input { flex-grow:1; padding:10px; border:1px solid var(--nk-border); border-radius:4px; }
        .nk-search-grid select { padding:10px; border:1px solid var(--nk-border); border-radius:4px; background:#fff; }

        /* User Contextual Flyout Dropdowns */
        .nk-user-menu { position:relative; }
        .nk-user-dropdown { display:none; position:absolute; right:0; top:100%; width:220px; background:#fff; border:1px solid var(--nk-border); box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:6px; padding:8px 0; list-style:none; z-index: 10001; }
        .nk-user-menu:hover .nk-user-dropdown { display:block; }
        .nk-user-dropdown a { display:block; padding:10px 16px; color:#374151; text-decoration:none; font-size:14px; }
        .nk-user-dropdown a:hover { background:var(--nk-light); color:var(--nk-primary); }
        
        .nk-notif-dropdown { display:none; position:absolute; right:60px; top:100%; width:320px; background:#fff; border:1px solid var(--nk-border); box-shadow:0 4px 20px rgba(0,0,0,0.15); border-radius:8px; padding:12px; max-height:400px; overflow-y:auto; z-index: 10001; }
        .nk-notif-item { padding:10px; border-bottom:1px solid var(--nk-light); font-size:13px; list-style-type:none; line-height: 1.4; }
        .nk-notif-item .nk-time { display:block; color:var(--nk-text-muted); font-size:11px; margin-top:4px; font-weight:bold; }

        /* ======================================================================
           DUAL MENU LOGIC: Safe Desktop Display vs. Flat Mobile Links
           ====================================================================== */
        .nk-mobile-only { display: none !important; }
        .nk-mobile-menu { display: none; } 
        .nk-hamburger, .nk-floating-mobile-cta { display:none; }
        
        @media(max-width:1024px) {
            .nk-topbar { display:none; }
            .nk-hamburger { display:block; font-size:24px; background:none; border:none; cursor:pointer; padding: 0; color: #111827; order: 1; }
            .nk-hide-mobile { display:none !important; }
            
            .nk-desktop-menu { display: none !important; }
            .nk-mobile-only { display: block !important; }
            .nk-desktop-only { display: none !important; }

            .nk-main-header { padding: 12px 4%; display: flex; align-items: center; justify-content: space-between; flex-wrap: nowrap; }
            .nk-logo { order: 2; margin: 0 auto; text-align: center; flex-grow: 1; display: flex; justify-content: center; }
            .nk-actions-cluster { order: 3; gap: 10px; }
            
            .nk-icon-btn { font-size: 20px; padding: 4px; }
            .nk-user-dropdown { right: -10px; top: 120%; width: 220px; }
            .nk-notif-dropdown { right: -60px; top: 120%; width: 280px; }
            
            .nk-mobile-menu { 
                display: none; width: 100%; flex-direction: column; position: absolute; 
                top: 100%; left: 0; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
                padding: 0; z-index: 10000; border-top: 1px solid var(--nk-border); max-height: 80vh; overflow-y: auto;
            }
            .nk-mobile-menu.is-mobile-active { display: flex; } 
            
            .nk-mobile-menu .nk-nav-item { width: 100%; padding: 0; box-sizing: border-box; }
            .nk-mobile-menu .nk-nav-link { border-bottom: 1px solid #f1f5f9; padding: 15px 5%; font-size: 16px; font-weight: 600; color: #111827; display: block; }
            
            .nk-search-grid { flex-direction: column; }
            .nk-search-grid select, .nk-search-grid input { width: 100%; box-sizing: border-box; }
            
            .nk-floating-mobile-cta { 
                display:block; position:fixed; bottom:20px; right:20px; background:var(--nk-accent); 
                color:#fff; padding:14px 24px; border-radius:50px; text-decoration:none; 
                font-weight:700; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:999999; 
            }
            .nk-actions-cluster .nk-cta-btn { display: none; }
        }
    </style>
</head>
<body <?php body_class(); ?>>

<header id="nk-masthead" class="nk-master-header">
    
    <div class="nk-topbar">
        <div class="nk-topbar-left">
            <?php 
            $wa_url = !empty($nk_opt['whatsapp']) ? $nk_opt['whatsapp'] : 'https://wa.me/qr/C4QCXMWLOLYIC1';
            $email  = !empty($nk_opt['email']) ? $nk_opt['email'] : 'info@natunkicho.com';
            $help   = !empty($nk_opt['help_url']) ? $nk_opt['help_url'] : 'https://natunkicho.com/contact/';
            ?>
            <a href="<?php echo esc_url($wa_url); ?>" target="_blank">💬 WhatsApp</a>
            <a href="mailto:<?php echo sanitize_email($email); ?>">✉️ <?php echo esc_html($email); ?></a>
            <a href="<?php echo esc_url($help); ?>">❓ Help Center</a>
        </div>
        <div class="nk-announcement">
            <?php if(!empty($nk_opt['announcement'])): ?>
                <a href="<?php echo esc_url($nk_opt['announcement_url'] ?? '#'); ?>"><?php echo esc_html($nk_opt['announcement']); ?></a>
            <?php endif; ?>
        </div>
        <div class="nk-topbar-right">
            <?php 
            $socials = [
                'fb' => ['label' => 'Facebook', 'default' => '#'],
                'linkedin' => ['label' => 'LinkedIn', 'default' => 'https://www.linkedin.com/company/food-business-success-lab/'],
                'youtube' => ['label' => 'YouTube', 'default' => '#'],
                'instagram' => ['label' => 'Instagram', 'default' => '#']
            ];
            foreach($socials as $k => $data): 
                $url = !empty($nk_opt[$k]) ? $nk_opt[$k] : $data['default'];
            ?>
                <a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($data['label']); ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="nk-main-header">
        <button class="nk-hamburger" id="nk-mobile-toggle" aria-label="Toggle Menu">☰</button>
        
        <div class="nk-logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php 
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo_image     = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                $fallback_logo  = get_theme_mod('natunkicho_logo_dark', '');

                if ( ! empty( $fallback_logo ) ) {
                    echo '<img src="' . esc_url( $fallback_logo ) . '" alt="NatunKicho" style="max-height:45px; width:auto; display:block;">';
                } elseif ( has_custom_logo() ) {
                    echo '<img src="' . esc_url( $logo_image[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" style="max-height:45px; width:auto; display:block;">';
                } else {
                    echo 'NatunKicho';
                }
                ?>
            </a>
        </div>

        <nav class="nk-navigation" role="navigation">
            <ul class="nk-nav-menu nk-desktop-menu">
                <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                
                <li class="nk-nav-item">
                    <span class="nk-nav-link nk-desktop-only">Jobs ▾</span>
                    <a class="nk-nav-link nk-mobile-only" href="/find-jobs/">Search Jobs</a>
                    
                    <div class="nk-mega-dropdown nk-desktop-only">
                        <?php if ( $is_employer || $is_admin ) : ?>
                            <div class="nk-mega-col">
                                <h4>Manage Listings</h4>
                                <ul>
                                    <li><a href="/manage-jobs/">Active Vacancies</a></li>
                                    <li><a href="/post-job/">Post a New Job</a></li>
                                    <li><a href="/dashboard/?tab=manage-jobs&filter=expired">Drafts & Expired</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Applications Pipeline</h4>
                                <ul>
                                    <li><a href="/dashboard/?tab=applications">Review All Applications</a></li>
                                    <li><a href="/dashboard/?tab=applications&filter=shortlisted">Shortlisted Candidates</a></li>
                                    <li><a href="/dashboard/?tab=messages">Message Applicants</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Proactive Sourcing</h4>
                                <ul>
                                    <li><a href="/talent-database/">Search Candidate Database</a></li>
                                    <li><a href="/dashboard/?tab=saved-candidates">Saved Profiles (Shortlist)</a></li>
                                    <li><a href="/dashboard/?tab=ai-matches">AI Match Recommendations</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Hiring Performance</h4>
                                <div class="nk-widget-card" style="border-left: 3px solid #10b981;">
                                    <h5 style="margin:0;">Listing Analytics</h5>
                                    <p style="font-size:11px; color:var(--nk-text-muted); margin:4px 0 0 0;">Track views, clicks, and conversion rates for your active roles.</p>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="nk-mega-col">
                                <h4>Search & Match</h4>
                                <ul>
                                    <li><a href="/find-jobs/">Search Jobs</a></li>
                                    <li><a href="/jobs/">Latest Jobs</a></li>
                                    <li><a href="/?post_type=job_listing&s=&search_location=">International Jobs</a></li>
                                    <li><a href="/jobs/featured">Featured Jobs</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Global Hubs</h4>
                                <ul>
                                    <li><a href="/jobs/">Global</a></li>
                                    <li><a href="/country/asia/">Asia</a></li>
                                    <li><a href="/country/americas/">Americas</a></li>
                                    <li><a href="/country/europe/">Europe</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Specializations</h4>
                                <ul>
                                    <li><a href="/find-jobs/?keywords=Hotel+&location=">Hotel Jobs</a></li>
                                    <li><a href="/find-jobs/?keywords=restaurant+&location=">Restaurant Jobs</a></li>
                                    <li><a href="/find-jobs/?keywords=cruise+line&location=">Cruise Line Jobs</a></li>
                                    <li><a href="/find-jobs/?keywords=chef&location=">Chef Roles</a></li>
                                </ul>
                            </div>
                            <div class="nk-mega-col">
                                <h4>Resources</h4>
                                <div class="nk-widget-card" style="border-left: 3px solid #0A66C2;">
                                    <h5 style="margin:0;">Jobseeker Insights</h5>
                                    <p style="font-size:11px; color:var(--nk-text-muted); margin:4px 0 0 0;">Create automated job alerts matching your experience metrics instantly.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>

                <li class="nk-nav-item">
                    <span class="nk-nav-link nk-desktop-only">Learning Center ▾</span>
                    <a class="nk-nav-link nk-mobile-only" href="/category/operations/">Kitchen Management</a>
                    
                    <div class="nk-mega-dropdown nk-desktop-only">
                        <div class="nk-mega-col">
                            <h4>Kitchen & Operations</h4>
                            <ul>
                                <li><a href="/category/recipes/">Culinary Recipes</a></li>
                                <li><a href="/category/food-business-tips/food-cost/">Food Costing Equations</a></li>
                                <li><a href="/category/operations/">Kitchen Management</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Service Standards</h4>
                            <ul>
                                <li><a href="/?post_type=post&s=SOP&category_name=">Hospitality SOPs</a></li>
                                <li><a href="/?post_type=post&s=Service&category_name=">Service Metrics</a></li>
                                <li><a href="/category/food-safety/">Food Safety & HACCP</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>E-Learning Media</h4>
                            <ul>
                                <li><a href="/learning/downloads">Downloadable Manuals</a></li>
                                <li><a href="/learning/workshops">Upcoming Virtual Panels</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Featured Guide</h4>
                            <div class="nk-widget-card">
                                <strong style="font-size:13px;">High-Margin Menu Engineering</strong>
                                <p style="font-size:11px; margin:4px 0 0 0;">Learn tactical asset tracking for professional kitchens.</p>
                            </div>
                        </div>
                    </div>
                </li>

                <?php if ( $is_candidate || $is_admin ) : ?>
                <li class="nk-nav-item">
                    <span class="nk-nav-link nk-desktop-only">Career Tools ▾</span>
                    <a class="nk-nav-link nk-mobile-only" href="/dashboard/?tab=cv-studio">ATS CV Builder</a>
                    
                    <div class="nk-mega-dropdown nk-desktop-only" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="nk-mega-col">
                            <h4>Automated CV Utilities</h4>
                            <ul>
                                <li><a href="/dashboard/?tab=cv-studio">ATS Resume Builder</a></li>
                                <li><a href="/dashboard/?tab=cv-studio">AI Resume Creator</a></li>
                                <li><a href="/dashboard/?tab=cv-studio">Instant CV Audit Tool</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Execution Ecosystem</h4>
                            <ul>
                                <li><a href="/hello/">Career Architecture Map</a></li>
                                <li><a href="/dashboard/">Real-time Profile Matching</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Premium Features</h4>
                            <div class="nk-widget-card" style="border-left: 3px solid gold;">
                                <strong style="font-size:13px;">Unlock Full Optimization Suite</strong>
                                <p style="font-size:11px; margin:4px 0 0 0;">Get deep algorithmic matching reviews for luxury properties.</p>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ( $is_employer || $is_admin ) : ?>
                <li class="nk-nav-item">
                    <span class="nk-nav-link nk-desktop-only">Employers ▾</span>
                    <a class="nk-nav-link nk-mobile-only" href="/post-job/">Post Vacancy</a>
                    
                    <div class="nk-mega-dropdown nk-desktop-only">
                        <div class="nk-mega-col">
                            <h4>Talent Sourcing</h4>
                            <ul>
                                <li><a href="/post-job/">Post a Vacancy</a></li>
                                <li><a href="/talent-database/">Search Candidate Pool</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Solutions</h4>
                            <ul>
                                <li><a href="/pricing/">Recruitment Packages</a></li>
                                <li><a href="/dashboard/">Enterprise ATS Core</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Guides</h4>
                            <ul>
                                <li><a href="/category/operations/hiring/">Retention Masterclass</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <div class="nk-widget-card" style="border-left: 3px solid #10b981;">
                                <h4 style="margin:0 0 4px 0;">Verified Employer Badge</h4>
                                <p style="font-size:11px; margin:0;">Boost organic conversion ratios by 40%.</p>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <li class="nk-nav-item">
                    <a class="nk-nav-link nk-desktop-only" href="https://natunkicho.com/acquisition/">Academy</a>
                    <a class="nk-nav-link nk-mobile-only" href="/hello/">Universities</a>
                    
                    <div class="nk-mega-dropdown nk-desktop-only" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="nk-mega-col">
                            <h4>Academic & Training</h4>
                            <ul>
                                <li><a href="/hello/">Universities</a></li>
                                <li><a href="/hello/">Training</a></li>
                                <li><a href="/hello/">Shortcourse</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Skills & Resources</h4>
                            <ul>
                                <li><a href="/hello/">Certifications</a></li>
                                <li><a href="/hello/">Free Learning Resource</a></li>
                                <li><a href="/hello/">Tutorials</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/salaries">Salary Insights</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/post-page/">Blogs</a></li>

            </ul>

            <!-- MOBILE MENU -->
            <ul class="nk-nav-menu nk-mobile-menu">
                <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                <?php if ( $is_employer ) : ?>
                    <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/employer-dashboard/')); ?>">Employer Workspace</a></li>
                    <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/post-job/')); ?>">Post a Job</a></li>
                <?php else : ?>
                    <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/find-jobs/')); ?>">Find Jobs</a></li>
                <?php endif; ?>
                
                <?php if ( $is_candidate ) : ?>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/tools/ats-builder">ATS CV Builder</a></li>
                <?php endif; ?>

                <li class="nk-nav-item"><a class="nk-nav-link" href="/category/operations/">Kitchen Management</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/hello/">Universities</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/salaries">Salary Insights</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/post-page/">Blogs</a></li>
            </ul>
        </nav>

        <div class="nk-actions-cluster">
            <button class="nk-icon-btn" onclick="nkToggleSearch()" aria-label="Toggle Search Grid">🔍</button>

            <div class="nk-user-menu">
                <button class="nk-icon-btn" id="nk-notif-bell">
                    🔔 <span class="nk-badge" id="nk-notif-count">0</span>
                </button>
                <div class="nk-notif-dropdown" id="nk-notif-box">
                    <ul id="nk-notif-list" style="padding:0; margin:0;"><li class="nk-notif-item">Syncing updates...</li></ul>
                </div>
            </div>

            <?php 
                $saved_url = '/saved-jobs/';
                $saved_title = 'Saved Jobs';
                if ($is_employer || $is_admin) {
                    $saved_url = '/dashboard/?tab=saved-candidates';
                    $saved_title = 'Saved Candidates & CVs';
                }
            ?>
            <a href="<?php echo esc_url($saved_url); ?>" class="nk-icon-btn" title="<?php echo esc_attr($saved_title); ?>">❤️</a>

            <div class="nk-user-menu">
                <button class="nk-icon-btn">👤 <span class="nk-hide-mobile" style="font-size:15px; font-weight:600; margin-left:4px;">Account</span></button>
                <div class="nk-user-dropdown">
                    <?php if ( ! $is_logged_in ) : ?>
                        <a href="<?php echo wp_login_url(); ?>">Log In</a>
                        <a href="https://natunkicho.com/register/">Register Account</a>
                    <?php else : ?>
                        <div style="padding:10px 16px; font-size:11px; text-transform:uppercase; color:var(--nk-text-muted); font-weight:bold; border-bottom:1px solid var(--nk-border);">Role: <?php echo esc_html($user_role); ?></div>
                        
                        <?php if ( $is_candidate ) : ?>
                            <a href="/candidate-dashboard/">Candidate Dashboard</a>
                            <a href="/candidate-dashboard/?tab=profile">My Profile</a>
                            <a href="/candidate-dashboard/">My Generated CVs</a>
                            <a href="/candidate-dashboard/?tab=applied-jobs">Applications Stack</a>
                        <?php elseif ( $is_employer ) : ?>
                            <a href="/employer-dashboard/">Employer Panel</a>
                            <a href="/company-profile/">Company Profile</a>
                            <a href="/post-job/">Post New Role</a>
                            <a href="/dashboard/?tab=saved-candidates">Saved Candidates</a>
                        <?php elseif ( $is_admin ) : ?>
                            <a href="<?php echo admin_url(); ?>">WordPress Admin Core</a>
                            <a href="<?php echo admin_url('admin.php?page=nk-header-settings'); ?>">Ecosystem Settings</a>
                        <?php endif; ?>
                        
                        <a href="<?php echo wp_logout_url( home_url() ); ?>" style="color:red; border-top:1px solid var(--nk-border);">Disconnect Session</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php 
                $cta_txt = 'Post a Job'; 
                $cta_url = '/post-job/'; 
                $cta_color = '#10b981'; // Green for employers/guests

                if($is_candidate) { 
                    $cta_txt = 'Find Jobs'; 
                    $cta_url = '/find-jobs/'; 
                    $cta_color = '#0A66C2'; // Blue for candidates
                } elseif($is_admin) { 
                    $cta_txt = 'Admin Panel'; 
                    $cta_url = admin_url(); 
                    $cta_color = '#334155'; // Slate for admins
                }
            ?>
            <a href="<?php echo esc_url($cta_url); ?>" class="nk-cta-btn" style="background: <?php echo $cta_color; ?>;"><?php echo esc_html($cta_txt); ?></a>
        </div>
    </div>

    <div class="nk-search-panel" id="nkSearchPanel">
        <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
            <div class="nk-search-grid">
                <select name="nk_context" id="nkContextSelector">
                    <option value="job">Search Marketplace Jobs</option>
                    <option value="article">Search Academic Articles</option>
                    <option value="recipe">Search Specialized Recipes</option>
                    <option value="training">Search Enterprise Training</option>
                    <?php if($is_employer || $is_admin): ?><option value="candidate">Search Talent Candidates</option><?php endif; ?>
                </select>
                <input type="text" name="s" placeholder="Execute contextual search query parameters across the ecosystem..." required>
                <button type="submit" class="nk-cta-btn" style="border:none; cursor:pointer;">Execute Query</button>
            </div>
        </form>
    </div>
</header>

<a href="<?php echo esc_url($cta_url); ?>" class="nk-floating-mobile-cta" style="background: <?php echo $cta_color; ?>;"><?php echo esc_html($cta_txt); ?></a>

<script>
    // 1. Interactive Display Elements Logic Engine
    function nkToggleSearch() {
        var panel = document.getElementById('nkSearchPanel');
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    }

    document.querySelector('#nkSearchPanel form').addEventListener('submit', function(e) {
        var context = document.getElementById('nkContextSelector').value;
        if(context !== 'job') {
            var input = document.createElement('input');
            input.type = 'hidden'; input.name = 'post_type'; input.value = context;
            this.appendChild(input);
        }
    });

    // 2. High-Performance Sticky Header Scroll Threshold Logic Engine
    window.addEventListener('scroll', function() {
        var header = document.getElementById('nk-masthead');
        if (window.scrollY > 120) {
            header.classList.add('is-sticky');
        } else {
            header.classList.remove('is-sticky');
        }
    });

    // 3. Secure Async Dynamic AJAX Notification Stack Engine
    document.getElementById('nk-notif-bell').addEventListener('click', function(e) {
        e.stopPropagation();
        var box = document.getElementById('nk-notif-box');
        box.style.display = (box.style.display === 'block') ? 'none' : 'block';
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=nk_get_notifications&security=<?php echo wp_create_nonce("nk_header_nonce"); ?>&t=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('nk-notif-count').innerText = data.data.count;
                    document.getElementById('nk-notif-list').innerHTML = data.data.html;
                }
            }).catch(err => console.error("Notification engine sync anomaly:", err));
    });

    document.addEventListener('click', function(e) {
        var notifBox = document.getElementById('nk-notif-box');
        var notifBell = document.getElementById('nk-notif-bell');
        if (notifBox && notifBox.style.display === 'block' && !notifBox.contains(e.target) && !notifBell.contains(e.target)) {
            notifBox.style.display = 'none';
        }
    });
    
    // 4. Mobile Navigation Toggle Engine (Targets the Clean Mobile Menu directly)
    document.getElementById('nk-mobile-toggle').addEventListener('click', function(e) {
        e.stopPropagation();
        var navMenu = document.querySelector('.nk-mobile-menu');
        navMenu.classList.toggle('is-mobile-active');
        
        if(navMenu.classList.contains('is-mobile-active')) {
            this.innerHTML = '✕';
        } else {
            this.innerHTML = '☰';
        }
    });

    document.addEventListener('click', function(e) {
        var navMenu = document.querySelector('.nk-mobile-menu');
        var hamburger = document.getElementById('nk-mobile-toggle');
        if (navMenu && navMenu.classList.contains('is-mobile-active') && !navMenu.contains(e.target) && !hamburger.contains(e.target)) {
            navMenu.classList.remove('is-mobile-active');
            hamburger.innerHTML = '☰';
        }
    });
</script>