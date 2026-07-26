<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

// =====================================================================
// 1. 10X FIX: TRICK ASTRA/ELEMENTOR INTO LOADING THE HEADER
// =====================================================================
global $post, $wp_query;
$real_page_id = get_option('page_on_front'); // Borrow the Home Page ID
if (!$real_page_id) {
    $pages = get_pages(['number' => 1]);
    $real_page_id = $pages ? $pages[0]->ID : 0; 
}

if ($real_page_id) {
    $real_post = get_post($real_page_id);
    $post = $real_post;
    $wp_query->queried_object = $real_post;
    $wp_query->queried_object_id = $real_page_id;
    $wp_query->post = $real_post;
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
}

get_header(); // Render the main site header
?>

<!-- 2. ASTRA THEME WRAPPERS -->
<div id="primary" class="content-area" style="background-color: #f8fafc; padding: 40px 0;">
    <main id="main" class="site-main" role="main">
        <div class="nkrp-container" style="max-width: 1400px; margin: 0 auto; width: 100%; padding: 0 15px; box-sizing: border-box;">

<?php
// =====================================================================
// 3. YOUR ORIGINAL COMPANY PROFILE LOGIC
// =====================================================================
global $nkrp_current_company;
global $nkrp_company_jobs;

if (empty($nkrp_current_company)) {
    echo '<div class="nkrp-notice" style="padding:40px; text-align:center; background:#fff; border-radius:12px;">Error: Company data could not be loaded.</div>';
    echo '</div></main></div>';
    get_footer();
    return;
}

$company = $nkrp_current_company;

// 10X FIX: Directly query the database to guarantee jobs show up
global $wpdb;
$jobs_table = $wpdb->prefix . 'nkrp_jobs';
$jobs = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$jobs_table} 
    WHERE company_id = %d AND status IN ('publish', 'published', 'active') 
    ORDER BY created_at DESC
", $company->id));

// --- FOLLOW COMPANY LOGIC ---
$current_user_id = get_current_user_id();
$is_following = false;
$following_list = [];
$action_status = ''; // Tracks the message to show

if ($current_user_id) {
    $following_list = get_user_meta($current_user_id, 'nkrp_following_companies', true);
    if (!is_array($following_list)) {
        $following_list = [];
    }
    
    // Process Follow/Unfollow Form Submission INLINE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nkrp_follow_nonce']) && wp_verify_nonce($_POST['nkrp_follow_nonce'], 'nkrp_company_action')) {
        $action_comp_id = (int) $_POST['company_id'];
        
        if (isset($_POST['nkrp_follow_btn'])) {
            if (!in_array($action_comp_id, $following_list)) {
                $following_list[] = $action_comp_id;
            }
            $action_status = 'followed';
        } elseif (isset($_POST['nkrp_unfollow_btn'])) {
            $following_list = array_diff($following_list, [$action_comp_id]);
            $action_status = 'unfollowed';
        }
        
        update_user_meta($current_user_id, 'nkrp_following_companies', array_values($following_list));
        
        // Silently clear the POST data
        echo "<script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>";
    }
    
    $is_following = in_array($company->id, $following_list);
}
// ---------------------------------

$name = esc_html($company->company_name ?? 'Unnamed Company');
$industry = esc_html($company->industry ?? 'Hospitality');
$size = esc_html($company->company_size ?? 'Not specified');
$founded = esc_html($company->founded_year ?? '');
$location = esc_html(trim(($company->city ?? '') . ', ' . ($company->country ?? ''), ', '));
$website = esc_url($company->website ?? '');

// Ensure logo resolves correctly via WP Media ID or URL
$logo = '';
if (!empty($company->logo)) {
    $logo = is_numeric($company->logo) ? wp_get_attachment_image_url($company->logo, 'medium') : esc_url($company->logo);
}

$description = wp_kses_post($company->description ?? '');
?>

<div class="nkrp-company-profile-container">
    
    <!-- SUCCESS MESSAGES -->
    <?php if (!empty($action_status)): ?>
        <?php if ($action_status === 'followed'): ?>
            <div class="nkrp-alert" style="background:#eff6ff; color:#1e40af; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bfdbfe;">
                <span class="dashicons dashicons-yes"></span> You are now following <?= $name ?>. You will be notified of their new jobs!
            </div>
        <?php elseif ($action_status === 'unfollowed'): ?>
            <div class="nkrp-alert" style="background:#f8fafc; color:#475569; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #cbd5e1;">
                <span class="dashicons dashicons-info"></span> You have unfollowed <?= $name ?>.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="nkrp-company-header-box">
        <div class="nkrp-company-header-inner">
            
            <div class="nkrp-company-logo-wrapper">
                <?php if (!empty($logo)): ?>
                    <img src="<?= $logo ?>" alt="<?= $name ?> Logo">
                <?php else: ?>
                    <span class="dashicons dashicons-building"></span>
                <?php endif; ?>
            </div>

            <div class="nkrp-company-title-area">
                <?php if (isset($company->verified) && $company->verified == 1): ?>
                    <span class="nkrp-badge nkrp-badge-verified"><span class="dashicons dashicons-yes-alt"></span> Verified Employer</span>
                <?php endif; ?>
                
                <h1 class="nkrp-company-name"><?= $name ?></h1>
                
                <div class="nkrp-company-meta-row">
                    <?php if (!empty($location)): ?>
                        <span><span class="dashicons dashicons-location"></span> <?= $location ?></span>
                    <?php endif; ?>
                    
                    <?php if (!empty($industry)): ?>
                        <span><span class="dashicons dashicons-portfolio"></span> <?= $industry ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>

    <div class="nkrp-company-grid">
        
        <div class="nkrp-company-main">
            
            <div class="nkrp-section">
                <h2>About <?= $name ?></h2>
                <?php if (!empty($description)): ?>
                    <div class="nkrp-rich-text"><?= $description ?></div>
                <?php else: ?>
                    <p><em>No description provided yet.</em></p>
                <?php endif; ?>
            </div>

            <div class="nkrp-section nkrp-mt-40">
                <h2 style="display:flex; justify-content:space-between; align-items:center;">
                    Open Positions
                    <span class="nkrp-badge" style="background:#eff6ff; color:#2563eb; font-size:14px;"><?= count($jobs) ?> Active</span>
                </h2>
                
                <?php if (empty($jobs)): ?>
                    <div class="nkrp-empty-jobs">
                        <span class="dashicons dashicons-portfolio" style="font-size:32px; width:32px; height:32px; color:#cbd5e1; margin-bottom:10px;"></span>
                        <p style="margin:0; font-weight:600; color:#0f172a;">No open positions at the moment.</p>
                        <p style="margin:5px 0 0 0; font-size:14px;">Check back later or save this company to track future updates.</p>
                    </div>
                <?php else: ?>
                    <div class="nkrp-jobs-grid-auto">
                        <?php foreach ($jobs as $job): ?>
                            <div class="nkrp-grid-card nkrp-job-card">
                                <div class="nkrp-card-top-flex">
                                    <?php if (!empty($logo)): ?>
                                        <img src="<?= $logo ?>" class="nkrp-job-logo">
                                    <?php else: ?>
                                        <div class="nkrp-job-logo-placeholder"><span class="dashicons dashicons-building"></span></div>
                                    <?php endif; ?>

                                    <div class="nkrp-job-badges">
                                        <?php if (isset($job->featured) && $job->featured == 1): ?>
                                            <span class="nkrp-badge-gold"><span class="dashicons dashicons-star-filled"></span> Featured</span>
                                        <?php endif; ?>
                                        <?php if (!empty($job->job_type) || !empty($job->employment_type)): ?>
                                            <span class="nkrp-badge-blue"><?= esc_html($job->job_type ?? $job->employment_type) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="nkrp-card-content-left">
                                    <h3 class="nkrp-title-md"><a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>"><?= esc_html($job->job_title ?? $job->title ?? 'Untitled Job') ?></a></h3>
                                    
                                    <div class="nkrp-job-meta-list">
                                        <span title="Location"><span class="dashicons dashicons-location"></span> <?= esc_html($job->city ?? $job->location ?? 'Remote') ?></span>
                                        
                                        <?php if (!empty($job->salary_min)): ?>
                                            <span title="Salary"><span class="dashicons dashicons-money-alt"></span> <?= esc_html($job->currency ?? 'USD') ?> <?= number_format((float)$job->salary_min) ?>+</span>
                                        <?php endif; ?>

                                        <?php if (!empty($job->deadline)): ?>
                                            <span title="Deadline" style="color: #b91c1c;"><span class="dashicons dashicons-calendar-alt"></span> <?= date_i18n('M j', strtotime($job->deadline)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                 <div class="nkrp-card-footer nkrp-dual-buttons">
                                <a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" class="nkrp-btn-outline">
                                    Details
                                </a>
                            
                                <a href="<?= esc_url(home_url('/job-details/?id=' . $job->id)) ?>" class="nkrp-btn-solid">
                                    Apply Now
                                </a>
                            </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="nkrp-company-sidebar">
            <div class="nkrp-sidebar-card">
                <h3>Company Overview</h3>
                
                <ul class="nkrp-overview-list">
                    <li>
                        <strong>Industry</strong>
                        <span><?= $industry ?></span>
                    </li>
                    <li>
                        <strong>Company Size</strong>
                        <span><?= $size ?></span>
                    </li>
                    <?php if (!empty($founded)): ?>
                    <li>
                        <strong>Founded Year</strong>
                        <span><?= $founded ?></span>
                    </li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($website)): ?>
                    <a href="<?= $website ?>" target="_blank" rel="noopener noreferrer" class="nkrp-btn-website" style="margin-bottom: 15px;">
                        Visit Website <span class="dashicons dashicons-external" style="font-size:16px; margin-left:5px; vertical-align:middle;"></span>
                    </a>
                <?php endif; ?>

                <!-- FOLLOW BUTTON UI -->
                <?php if ($current_user_id): ?>
                    <form method="POST" action="" style="margin: 0;">
                        <?php wp_nonce_field('nkrp_company_action', 'nkrp_follow_nonce'); ?>
                        <input type="hidden" name="company_id" value="<?= esc_attr((string)$company->id) ?>">
                        
                        <?php if ($is_following): ?>
                            <button type="submit" name="nkrp_unfollow_btn" class="nkrp-btn-follow nkrp-btn-following">
                                <span class="dashicons dashicons-yes"></span> Following
                            </button>
                        <?php else: ?>
                            <button type="submit" name="nkrp_follow_btn" class="nkrp-btn-follow">
                                <span class="dashicons dashicons-plus"></span> Follow <?= $name ?>
                            </button>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <a href="<?= esc_url(home_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI']))) ?>" class="nkrp-btn-follow nkrp-btn-loggedout">
                        <span class="dashicons dashicons-plus"></span> Log in to Follow
                    </a>
                <?php endif; ?>

            </div>
        </div>
        
    </div>

    <!-- RELATED COMPANIES SECTION -->
    <?php
    global $wpdb;
    $related_companies = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}nkrp_companies 
        WHERE industry = %s AND id != %d AND status = 'active' 
        ORDER BY created_at DESC LIMIT 3
    ", $industry, $company->id));
    
    if (!empty($related_companies)):
    ?>
        <div class="nkrp-section nkrp-mt-40">
            <h3 style="font-size: 24px; color: #0f172a; margin-bottom: 20px; margin-top: 40px;">Similar Companies in <?= $industry ?></h3>
            <div class="nkrp-jobs-grid-auto">
                <?php foreach ($related_companies as $rel_comp): 
                    $rel_slug = esc_attr($rel_comp->company_slug ?? sanitize_title($rel_comp->company_name));
                    $rel_logo = is_numeric($rel_comp->logo) ? wp_get_attachment_image_url($rel_comp->logo, 'thumbnail') : esc_url($rel_comp->logo);
                ?>
                    <div class="nkrp-grid-card">
                        <div class="nkrp-card-header-center" style="padding: 25px 20px 15px; text-align: center;">
                            <?php if (!empty($rel_logo)): ?>
                                <img src="<?= $rel_logo ?>" alt="<?= esc_attr($rel_comp->company_name) ?>" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; margin: 0 auto 15px; display:block; background:#fff;">
                            <?php else: ?>
                                <div style="width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 15px; display:flex; align-items:center; justify-content:center; background:#f8fafc; border:1px solid #e2e8f0; color:#94a3b8;">
                                    <span class="dashicons dashicons-building" style="font-size:32px; width:32px; height:32px;"></span>
                                </div>
                            <?php endif; ?>
                            <h4 style="font-size:18px; margin:0 0 5px 0; color:#0f172a;"><?= esc_html($rel_comp->company_name) ?></h4>
                            <span style="font-size:13px; color:#64748b; display:block;"><span class="dashicons dashicons-location"></span> <?= esc_html(trim(($rel_comp->city ?? '') . ', ' . ($rel_comp->country ?? ''), ', ')) ?></span>
                        </div>
                        <div class="nkrp-card-footer" style="padding: 15px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                            <a href="<?= esc_url(home_url('/company-profile/?slug=' . $rel_slug)) ?>" class="nkrp-btn-website" style="background:#2563eb; color:#fff; border:none; padding:10px; font-size:14px;">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .nkrp-company-profile-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1400px; width: 100%; margin: 0 auto; color: #334155; }
    
    .nkrp-company-header-box { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 40px; border: 1px solid #e2e8f0; }
    .nkrp-company-header-inner { display: flex; align-items: center; gap: 30px; }
    .nkrp-company-logo-wrapper { width: 130px; height: 130px; border-radius: 16px; border: 1px solid #e2e8f0; padding: 10px; display: flex; align-items: center; justify-content: center; background: #f8fafc; flex-shrink: 0; }
    .nkrp-company-logo-wrapper img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
    .nkrp-company-logo-wrapper .dashicons { font-size: 54px; width: 54px; height: 54px; color: #94a3b8; }
    .nkrp-company-name { margin: 10px 0; font-size: 40px; color: #0f172a; font-weight: 700; line-height: 1.2; }
    .nkrp-company-meta-row { display: flex; flex-wrap: wrap; gap: 20px; font-size: 16px; color: #64748b; margin-top: 10px; }
    .nkrp-company-meta-row span { display: flex; align-items: center; gap: 6px; }
    
    .nkrp-badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .nkrp-badge-verified { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    
    .nkrp-company-grid { display: grid; grid-template-columns: 3fr 1fr; gap: 40px; }
    
    .nkrp-company-main { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
    .nkrp-section { margin-bottom: 35px; }
    .nkrp-section:last-child { margin-bottom: 0; }
    .nkrp-section h2 { font-size: 22px; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
    .nkrp-mt-40 { margin-top: 40px; }
    .nkrp-rich-text { line-height: 1.7; font-size: 16px; color: #475569; }
    .nkrp-rich-text p { margin-bottom: 15px; }
    
    .nkrp-company-sidebar { position: sticky; top: 20px; align-self: start; }
    .nkrp-sidebar-card { background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .nkrp-sidebar-card h3 { margin: 0 0 20px 0; font-size: 18px; color: #0f172a; }
    .nkrp-overview-list { list-style: none; padding: 0; margin: 0 0 25px 0; }
    .nkrp-overview-list li { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
    .nkrp-overview-list li:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .nkrp-overview-list strong { display: block; color: #64748b; font-size: 13px; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .nkrp-overview-list span { color: #0f172a; font-size: 15px; font-weight: 500; }
    
    .nkrp-btn-website { display: block; width: 100%; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 14px 0; font-size: 15px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none; box-sizing: border-box; }
    .nkrp-btn-website:hover { background: #f1f5f9; border-color: #94a3b8; }
    
    /* FOLLOW BUTTON CSS */
    .nkrp-btn-follow { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #2563eb; color: #fff; border: none; padding: 14px 0; font-size: 15px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s; box-sizing: border-box; text-decoration: none; }
    .nkrp-btn-follow:hover { background: #1d4ed8; }
    .nkrp-btn-following { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
    .nkrp-btn-following:hover { background: #e2e8f0; color: #b91c1c; border-color: #fca5a5; }
    .nkrp-btn-following:hover span::before { content: "\f335"; } /* Changes to X icon on hover */
    .nkrp-btn-loggedout { background: #fff; border: 1px dashed #cbd5e1; color: #64748b; }
    .nkrp-btn-loggedout:hover { background: #f8fafc; color: #2563eb; border-color: #93c5fd; }
    
    .nkrp-empty-jobs { padding: 40px; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; }

    .nkrp-jobs-grid-auto { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
    
    .nkrp-grid-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; transition: all 0.2s ease; }
    .nkrp-grid-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-color: #cbd5e1; }

    .nkrp-card-top-flex { padding: 20px 20px 10px; display: flex; justify-content: space-between; align-items: flex-start; }
    .nkrp-job-logo, .nkrp-job-logo-placeholder { width: 48px; height: 48px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: cover; }
    .nkrp-job-logo-placeholder { background: #f8fafc; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
    .nkrp-job-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .nkrp-badge-gold { background: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 2px;}
    .nkrp-badge-gold .dashicons { font-size: 12px; width: 12px; height: 12px; margin-top: 1px;}
    .nkrp-badge-blue { background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }

    .nkrp-card-content-left { padding: 0 20px 20px; flex-grow: 1; text-align: left; }
    .nkrp-title-md { font-size: 17px; margin: 0 0 12px 0; font-weight: 700; line-height: 1.3; }
    .nkrp-title-md a { color: #0f172a; text-decoration: none; }
    .nkrp-title-md a:hover { color: #2563eb; }
    
    .nkrp-job-meta-list { display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #475569; }
    .nkrp-job-meta-list span { display: flex; align-items: center; gap: 6px; }
    .nkrp-job-meta-list .dashicons { color: #94a3b8; font-size: 14px; width: 14px; height: 14px; }

    .nkrp-card-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; background: #fafafa; }
    .nkrp-dual-buttons { display: flex; gap: 10px; }
    .nkrp-btn-outline { flex: 1; text-align: center; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s;}
    .nkrp-btn-outline:hover { background: #f1f5f9; }
    .nkrp-btn-solid { flex: 2; text-align: center; padding: 8px; border-radius: 6px; background: #2563eb; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 4px; transition: 0.2s;}
    .nkrp-btn-solid:hover { background: #1d4ed8; color: #fff;}

    @media(max-width: 992px) {
        .nkrp-company-grid { grid-template-columns: 1fr; }
        .nkrp-company-sidebar { position: static; margin-top: 30px; }
    }
    
    @media(max-width: 768px) {
        .nkrp-company-header-inner { flex-direction: column; text-align: center; gap: 15px; }
        .nkrp-company-meta-row { justify-content: center; }
        .nkrp-company-header-box, .nkrp-company-main { padding: 25px; }
        .nkrp-company-name { font-size: 28px; }
    }
</style>

<?php
// =====================================================================
// 4. CLOSE WRAPPERS AND LOAD FOOTER
// =====================================================================
?>
        </div>
    </main>
</div>
<?php get_footer(); ?>