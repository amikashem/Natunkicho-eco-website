<?php
/**
 * Title: NatunKicho Complete Dynamic Header & Navigation System
 * Description: Single-file enterprise architecture for NatunKicho Hospitality Ecosystem.
 * Version: 1.0.0
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
                    <tr><th>Facebook URL</th><td><input type="url" name="nk_settings[fb]" value="<?php echo esc_url($settings['fb'] ?? ''); ?>" placeholder="#"></td></tr>
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
        $user = wp_get_current_user();
        $role = in_array('administrator', $user->roles) ? 'admin' : (in_array('employer', $user->roles) ? 'employer' : 'candidate');

        // High-Performance Transient Caching Engine Strategy
        $cache_key = 'nk_notif_cache_' . $user_id;
        $notifications = get_transient( $cache_key );

        if ( false === $notifications ) {
            $notifications = $this->generate_role_notifications($role);
            set_transient( $cache_key, $notifications, 120 ); // Cached for 2 mins to prevent DB throttling
        }

        wp_send_json_success( array(
            'count' => count( $notifications ),
            'html'  => $this->compile_notification_html( $notifications )
        ));
    }

    public function ajax_fetch_notifications_guest() {
        wp_send_json_success( array( 'count' => 0, 'html' => '<li class="nk-notif-item">Please sign in to read notifications.</li>' ) );
    }

    private function generate_role_notifications($role) {
        if ($role === 'candidate') {
            return [
                ['text' => 'Application Viewed: Five-Star Resort Maldives', 'time' => '10m ago'],
                ['text' => 'CV Audit Complete: AI Optimizer Suggestions ready', 'time' => '2h ago'],
                ['text' => 'Interview Invite: Executive Pastry Chef Role', 'time' => '1d ago']
            ];
        } elseif ($role === 'employer') {
            return [
                ['text' => 'New Match: 5 Candidates match your Chef De Partie post', 'time' => '5m ago'],
                ['text' => 'Job Expiry Warning: UAE Restaurant Manager Post', 'time' => '1d ago']
            ];
        } else {
            return [
                ['text' => 'System Alert: 14 New profiles waiting validation', 'time' => '1m ago'],
                ['text' => 'Database Sync Successful', 'time' => '4h ago']
            ];
        }
    }

    private function compile_notification_html( $items ) {
        $out = '';
        foreach ( $items as $item ) {
            $out .= '<li class="nk-notif-item"><strong>' . esc_html($item['text']) . '</strong><span class="nk-time">' . esc_html($item['time']) . '</span></li>';
        }
        return $out;
    }
}
$nk_header_engine = new NatunKicho_Header_Engine();

// Fetch System User Parameters
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$user_role    = 'guest';

if ( $is_logged_in ) {
    if ( in_array( 'administrator', $current_user->roles ) ) { $user_role = 'admin'; }
    elseif ( in_array( 'employer', $current_user->roles ) ) { $user_role = 'employer'; }
    elseif ( in_array( 'premium', $current_user->roles ) ) { $user_role = 'premium'; }
    else { $user_role = 'candidate'; }
}

$nk_opt = get_option( 'nk_settings' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        /* CRITICAL RENDER PATH STYLE INJECTIONS */
        :root {
            --nk-primary: #0066cc; --nk-dark: #111827; --nk-light: #f3f4f6;
            --nk-accent: #10b981; --nk-border: #e5e7eb; --nk-text-muted: #6b7280;
        }
        body { margin:0; font-family: system-ui, -apple-system, sans-serif; }
        .nk-master-header { width:100%; position:relative; z-index:99999; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .nk-master-header.is-sticky { position:fixed; top:0; left:0; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { transform: translateY(-30px); } to { transform: translateY(0); } }
        
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
        
        /* 🔴 CRITICAL FIX: The Invisible Hover Bridge 
           This catches the mouse cursor if it moves over the gap between the header and the dropdown! */
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
        .nk-icon-btn { background:none; border:none; position:relative; cursor:pointer; font-size:20px; color:#4b5563; padding:6px; }
        .nk-badge { position:absolute; top:-2px; right:-2px; background:red; color:#fff; font-size:9px; border-radius:50%; width:15px; height:15px; display:flex; align-items:center; justify-content:center; font-weight:bold; }
        .nk-cta-btn { background:var(--nk-primary); color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px; box-shadow:0 2px 4px rgba(0,96,204,0.2); transition: 0.2s; }
        .nk-cta-btn:hover { background:#0052a3; }

        /* Search Panel overlay rules */
        .nk-search-panel { display:none; position:absolute; top:100%; left:0; width:100%; background:#fff; border-bottom:2px solid var(--nk-primary); padding:16px 4%; box-shadow:0 4px 6px rgba(0,0,0,0.05); box-sizing:border-box; }
        .nk-search-grid { display:flex; gap:12px; width:100%; max-width:900px; margin:0 auto; }
        .nk-search-grid input { flex-grow:1; padding:10px; border:1px solid var(--nk-border); border-radius:4px; }
        .nk-search-grid select { padding:10px; border:1px solid var(--nk-border); border-radius:4px; background:#fff; }

        /* User Contextual Flyout Dropdowns */
        .nk-user-menu { position:relative; }
        .nk-user-dropdown { display:none; position:absolute; right:0; top:100%; width:220px; background:#fff; border:1px solid var(--nk-border); box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:6px; padding:8px 0; list-style:none; }
        .nk-user-menu:hover .nk-user-dropdown { display:block; }
        .nk-user-dropdown a { display:block; padding:10px 16px; color:#374151; text-decoration:none; font-size:14px; }
        .nk-user-dropdown a:hover { background:var(--nk-light); color:var(--nk-primary); }
        .nk-notif-dropdown { display:none; position:absolute; right:60px; top:100%; width:300px; background:#fff; border:1px solid var(--nk-border); box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:6px; padding:12px; max-height:400px; overflow-y:auto; }
        .nk-notif-item { padding:8px; border-bottom:1px solid var(--nk-light); font-size:13px; list-style-type:none; }
        .nk-notif-item .nk-time { display:block; color:var(--nk-text-muted); font-size:11px; }

        /* Mobile Adjustments & Structural Collapse */
        .nk-hamburger, .nk-floating-mobile-cta { display:none; }
        @media(max-width:1024px) {
            .nk-topbar, .nk-nav-menu { display:none; }
            .nk-hamburger { display:block; font-size:24px; background:none; border:none; cursor:pointer; }
            .nk-floating-mobile-cta { display:block; position:fixed; bottom:20px; right:20px; background:var(--nk-accent); color:#fff; padding:14px 24px; border-radius:50px; text-decoration:none; font-weight:700; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:999999; }
            .nk-main-header { padding:12px; }
        }
    </style>
</head>
<body <?php body_class(); ?>>

<header id="nk-masthead" class="nk-master-header">
    
    <!-- LAYER 1: UTILITY BAR -->
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

    <!-- LAYER 2: MAIN BRANDING & ECOSYSTEM MEGA NAVIGATION -->
    <div class="nk-main-header">
        <button class="nk-hamburger" aria-label="Toggle Menu" onclick="alert('Mobile Nav Framework Operational Engine triggered.')">☰</button>
        
        <div class="nk-logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php 
                // 🔴 LOGO ENGINE RESTORED: Pulls from WP Customizer
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
            <ul class="nk-nav-menu">
                <li class="nk-nav-item"><a class="nk-nav-link" href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                
                <!-- JOBS MEGA MENU -->
                <li class="nk-nav-item">
                    <span class="nk-nav-link">Jobs ▾</span>
                    <div class="nk-mega-dropdown">
                        <div class="nk-mega-col">
                            <h4>Search & Match</h4>
                            <ul>
                                <li><a href="/jobs/search">Search Jobs</a></li>
                                <li><a href="/jobs/latest">Latest Jobs</a></li>
                                <li><a href="/jobs/international">International Jobs</a></li>
                                <li><a href="/jobs/featured">Featured Jobs</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Global Hubs</h4>
                            <ul>
                                <li><a href="/country/maldives">Maldives</a></li>
                                <li><a href="/country/uae">UAE</a></li>
                                <li><a href="/country/qatar">Qatar</a></li>
                                <li><a href="/country/saudi">Saudi Arabia</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Specializations</h4>
                            <ul>
                                <li><a href="/category/hotel">Hotel Jobs</a></li>
                                <li><a href="/category/restaurant">Restaurant Jobs</a></li>
                                <li><a href="/category/cruise">Cruise Line Jobs</a></li>
                                <li><a href="/category/chef">Chef Roles</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Resources</h4>
                            <div class="nk-widget-card">
                                <h5>Jobseeker Insights</h5>
                                <p style="font-size:12px; color:var(--nk-text-muted); margin:4px 0 0 0;">Create automated job alerts matching your experience metrics instantly.</p>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- LEARNING CENTER MEGA MENU -->
                <li class="nk-nav-item">
                    <span class="nk-nav-link">Learning Center ▾</span>
                    <div class="nk-mega-dropdown">
                        <div class="nk-mega-col">
                            <h4>Kitchen & Operations</h4>
                            <ul>
                                <li><a href="/learning/recipes">Culinary Recipes</a></li>
                                <li><a href="/learning/food-costing">Food Costing Equations</a></li>
                                <li><a href="/learning/kitchen-mgmt">Kitchen Management</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Service Standards</h4>
                            <ul>
                                <li><a href="/learning/sop">Hospitality SOPs</a></li>
                                <li><a href="/learning/service-standards">Service Metrics</a></li>
                                <li><a href="/learning/food-safety">Food Safety & HACCP</a></li>
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
                                <strong>High-Margin Menu Engineering</strong>
                                <p style="font-size:11px; margin:4px 0 0 0;">Learn tactical asset tracking for professional kitchens.</p>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- CAREER TOOLS MEGA MENU -->
                <li class="nk-nav-item">
                    <span class="nk-nav-link">Career Tools ▾</span>
                    <div class="nk-mega-dropdown" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="nk-mega-col">
                            <h4>Automated CV Utilities</h4>
                            <ul>
                                <li><a href="/tools/ats-builder">ATS Resume Builder</a></li>
                                <li><a href="/tools/ai-creator">AI Resume Creator</a></li>
                                <li><a href="/tools/cv-audit">Instant CV Audit Tool</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Execution Ecosystem</h4>
                            <ul>
                                <li><a href="/tools/roadmap">Career Architecture Map</a></li>
                                <li><a href="/tools/match-score">Real-time Profile Matching</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Premium Features</h4>
                            <div class="nk-widget-card" style="border-left: 3px solid gold;">
                                <strong>Unlock Full Optimization Suite</strong>
                                <p style="font-size:12px; margin:4px 0 0 0;">Get deep algorithmic matching reviews for luxury properties.</p>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- EMPLOYERS MEGA MENU -->
                <li class="nk-nav-item">
                    <span class="nk-nav-link">Employers ▾</span>
                    <div class="nk-mega-dropdown">
                        <div class="nk-mega-col">
                            <h4>Talent Sourcing</h4>
                            <ul>
                                <li><a href="/employers/post-job">Post a Vacancy</a></li>
                                <li><a href="/employers/talent-database">Search Candidate Pool</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Solutions</h4>
                            <ul>
                                <li><a href="/employers/packages">Recruitment Packages</a></li>
                                <li><a href="/employers/ats-integration">Enterprise ATS Core</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <h4>Guides</h4>
                            <ul>
                                <li><a href="/employers/hiring-guides">Retention Masterclass</a></li>
                            </ul>
                        </div>
                        <div class="nk-mega-col">
                            <div class="nk-widget-card"><h4>Verified Employer Badge</h4><p style="font-size:12px;">Boost organic conversion ratios by 40%.</p></div>
                        </div>
                    </div>
                </li>

                <li class="nk-nav-item"><a class="nk-nav-link" href="/training">Training</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/consultancy">Consultancy</a></li>
                <li class="nk-nav-item"><a class="nk-nav-link" href="/pricing">Pricing</a></li>
            </ul>
        </nav>

        <!-- LAYER 3: DYNAMIC REAL-TIME USER ACTIONS AREA -->
        <div class="nk-actions-cluster">
            <!-- Search Icon Toggle -->
            <button class="nk-icon-btn" onclick="nkToggleSearch()" aria-label="Toggle Search Grid">🔍</button>

            <!-- Notifications Bell Engine -->
            <div class="nk-user-menu">
                <button class="nk-icon-btn" id="nk-notif-bell">
                    🔔 <span class="nk-badge" id="nk-notif-count">0</span>
                </button>
                <div class="nk-notif-dropdown" id="nk-notif-box">
                    <ul id="nk-notif-list" style="padding:0; margin:0;"><li class="nk-notif-item">Syncing updates...</li></ul>
                </div>
            </div>

            <!-- Saved Items Vector -->
            <a href="/saved-items" class="nk-icon-btn" title="Saved Items">❤️</a>

            <!-- Dynamic User Accounts Engine Mapping Matrix -->
            <div class="nk-user-menu">
                <button class="nk-icon-btn" style="font-size:15px; font-weight:600;">👤 Account</button>
                <div class="nk-user-dropdown">
                    <?php if ( ! $is_logged_in ) : ?>
                        <a href="<?php echo wp_login_url(); ?>">Log In</a>
                        <a href="<?php echo wp_registration_url(); ?>">Register Account</a>
                    <?php else : ?>
                        <div style="padding:10px 16px; font-size:11px; text-transform:uppercase; color:var(--nk-text-muted); font-weight:bold; border-bottom:1px solid var(--nk-border);">Role: <?php echo esc_html($user_role); ?></div>
                        
                        <?php if ( $user_role === 'candidate' || $user_role === 'premium' ) : ?>
                            <a href="/dashboard">Candidate Dashboard</a>
                            <a href="/dashboard/profile">My Profile</a>
                            <a href="/dashboard/cvs">My Generated CVs</a>
                            <a href="/dashboard/applications">Applications Stack</a>
                        <?php elseif ( $user_role === 'employer' ) : ?>
                            <a href="/employer/dashboard">Employer Panel</a>
                            <a href="/employer/profile">Company Profile</a>
                            <a href="/employer/post">Post New Role</a>
                            <a href="/employer/candidates">Talent Sourcing Pool</a>
                        <?php elseif ( $user_role === 'admin' ) : ?>
                            <a href="<?php echo admin_url(); ?>">WordPress Admin Core</a>
                            <a href="<?php echo admin_url('admin.php?page=nk-header-settings'); ?>">Ecosystem Settings</a>
                        <?php endif; ?>
                        
                        <a href="<?php echo wp_logout_url( home_url() ); ?>" style="color:red; border-top:1px solid var(--nk-border);">Disconnect Session</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contextual CTA System Logic Matrix -->
            <?php 
                $cta_txt = 'Join Now'; $cta_url = wp_registration_url();
                if($user_role === 'candidate') { $cta_txt = 'Build My CV'; $cta_url = '/tools/ats-builder'; }
                elseif($user_role === 'premium') { $cta_txt = 'Premium Dashboard'; $cta_url = '/premium/hub'; }
                elseif($user_role === 'employer') { $cta_txt = 'Post a Job'; $cta_url = '/employer/post'; }
                elseif($user_role === 'admin') { $cta_txt = 'Admin Panel'; $cta_url = admin_url(); }
            ?>
            <a href="<?php echo esc_url($cta_url); ?>" class="nk-cta-btn"><?php echo esc_html($cta_txt); ?></a>
        </div>
    </div>

    <!-- EXPANDABLE REAL-TIME ROUTING ROUTER FRAMEWORK PANEL -->
    <div class="nk-search-panel" id="nkSearchPanel">
        <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
            <div class="nk-search-grid">
                <select name="nk_context" id="nkContextSelector">
                    <option value="job">Search Marketplace Jobs</option>
                    <option value="article">Search Academic Articles</option>
                    <option value="recipe">Search Specialized Recipes</option>
                    <option value="training">Search Enterprise Training</option>
                    <?php if($user_role === 'employer' || $user_role === 'admin'): ?><option value="candidate">Search Talent Candidates</option><?php endif; ?>
                </select>
                <input type="text" name="s" placeholder="Execute contextual search query parameters across the ecosystem..." required>
                <button type="submit" class="nk-cta-btn" style="border:none; cursor:pointer;">Execute Query</button>
            </div>
        </form>
    </div>
</header>

<!-- MOBILE FLOATING CONTACT ACTION LAYER -->
<a href="<?php echo esc_url($cta_url); ?>" class="nk-floating-mobile-cta"><?php echo esc_html($cta_txt); ?></a>

<script>
    // 1. Interactive Display Elements Logic Engine
    function nkToggleSearch() {
        var panel = document.getElementById('nkSearchPanel');
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    }

    // Dynamic Context-Based Search Router Interceptor
    document.querySelector('#nkSearchPanel form').addEventListener('submit', function(e) {
        var context = document.getElementById('nkContextSelector').value;
        if(context !== 'job') {
            // Append target structural post types context parameters cleanly on flight
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
        
        // Asynchronous non-blocking payload pull fetch
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=nk_get_notifications&security=<?php echo wp_create_nonce("nk_header_nonce"); ?>')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('nk-notif-count').innerText = data.data.count;
                    document.getElementById('nk-notif-list').innerHTML = data.data.html;
                }
            }).catch(err => console.error("Notification engine sync anomaly:", err));
    });

    document.addEventListener('click', function() {
        document.getElementById('nk-notif-box').style.display = 'none';
    });
</script>