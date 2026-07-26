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
        <div class="nkrp-container" style="max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 15px; box-sizing: border-box;">

<?php
// =====================================================================
// 3. YOUR EXACT JOB DETAILS LOGIC & HTML
// =====================================================================
global $nkrp_current_job;
if (empty($nkrp_current_job)) {
    echo '<div class="nkrp-notice" style="padding:40px; text-align:center; background:#fff; border-radius:12px;">Error: Job data could not be loaded.</div>';
    echo '</div></main></div>'; // Close wrappers
    get_footer(); // Load footer before exiting
    return;
}

$job = $nkrp_current_job;
$job_id = (int) ($job->id ?? 0); 
$company_id = (int) ($job->company_id ?? 0); 

global $wpdb;
$company_info = null;
if ($company_id > 0) {
    $company_table = $wpdb->prefix . 'nkrp_companies';
    $company_info = $wpdb->get_row($wpdb->prepare("SELECT company_name, company_slug, logo FROM {$company_table} WHERE id = %d AND status = 'active'", $company_id));
}

$title = esc_html($job->title ?? $job->job_title ?? 'Untitled Job');
$location = esc_html($job->location ?? $job->city ?? 'Remote');
$type = esc_html($job->job_type ?? $job->employment_type ?? 'Full-Time');
$department = esc_html($job->department ?? '');
$experience = esc_html($job->experience_level ?? $job->experience ?? 'Not Specified');
$vacancies = (int) ($job->vacancies ?? 1);
$deadline = !empty($job->deadline) ? date_i18n(get_option('date_format'), strtotime($job->deadline)) : 'Open Until Filled';

$can_view_salary = false;
$is_candidate = false;
$is_employer = false;
$is_job_saved = false;

if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $roles = (array) $current_user->roles;
    
    $saved_jobs = get_user_meta($current_user->ID, '_nkrp_saved_jobs', true);
    if (is_array($saved_jobs) && in_array($job_id, $saved_jobs)) {
        $is_job_saved = true;
    }

    if (in_array('employer', $roles) || in_array('nk_employer', $roles) || in_array('administrator', $roles)) {
        $is_employer = true;
        $can_view_salary = true;
    } else {
        $is_candidate = true;
        if (get_user_meta($current_user->ID, 'nk_premium_plan_name', true) || get_user_meta($current_user->ID, 'nk_premium_expiry', true)) {
            $can_view_salary = true;
        }
    }
}

$salary = 'Depends on Experience';
if (!empty($job->salary_min)) {
    $currency = esc_html($job->currency ?? 'USD');
    $max = !empty($job->salary_max) ? ' - ' . number_format((float)$job->salary_max) : '+';
    $salary_type = !empty($job->salary_type) ? ' / ' . esc_html($job->salary_type) : '';
    $actual_salary = $currency . ' ' . number_format((float)$job->salary_min) . $max . $salary_type;

    if ($can_view_salary) {
        $salary = $actual_salary;
    } else {
        $salary = '<span class="dashicons dashicons-lock" style="font-size:14px; color:#b45309; margin-top:2px;"></span> <a href="' . esc_url(home_url('/membership/')) . '" style="color:#b45309; text-decoration:underline; font-weight:600; font-size:14px;">Premium Only</a>';
    }
}

$description = wp_kses_post($job->description ?? '');
$requirements = wp_kses_post($job->requirements ?? '');
$responsibilities = wp_kses_post($job->responsibilities ?? '');
$benefits = wp_kses_post($job->benefits ?? '');

$beautiful_url = home_url('/job/' . (!empty($job->job_slug) ? $job->job_slug : $job_id) . '/');
$current_url = urlencode(esc_url($beautiful_url));
$share_title = urlencode($title . ' at NK Recruitment');
?>

<div class="nkrp-job-details-container">
    
    <?php if (isset($_GET['application_success']) && $_GET['application_success'] == '1'): ?>
        <div class="nkrp-alert nkrp-alert-success" style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
            <span class="dashicons dashicons-yes-alt"></span> Success! Your application has been sent to the employer.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['already_applied']) && $_GET['already_applied'] == '1'): ?>
        <div class="nkrp-alert nkrp-alert-warning" style="background:#fef3c7; color:#92400e; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #fde68a;">
            <span class="dashicons dashicons-warning"></span> You have already applied for this position!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['job_saved']) && $_GET['job_saved'] == '1'): ?>
        <div class="nkrp-alert nkrp-alert-success" style="background:#eff6ff; color:#1e40af; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bfdbfe;">
            <svg width="20" height="20" fill="#2563eb" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> Job saved! You can view it in your candidate dashboard.
        </div>
    <?php endif; ?>

    <div class="nkrp-job-header">
        <div class="nkrp-job-header-content">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <span class="nkrp-badge nkrp-badge-type"><?= $type ?></span>
                    <?php if (isset($job->featured) && $job->featured == 1): ?>
                        <span class="nkrp-badge nkrp-badge-featured">Featured</span>
                    <?php endif; ?>
                    <h1 class="nkrp-job-title"><?= $title ?></h1>
                    
                    <?php if ($company_info): ?>
                        <p style="margin:0 0 15px 0; font-size:16px; font-weight:500;">
                            <a href="<?= esc_url(home_url('/company/' . esc_attr($company_info->company_slug) . '/')) ?>" style="color: #2563eb; text-decoration: none;">
                                <span class="dashicons dashicons-building" style="font-size:16px; width:16px; height:16px; vertical-align:middle;"></span> 
                                <?= esc_html($company_info->company_name) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="" style="margin:0;">
                    <?php wp_nonce_field('nkrp_job_actions', 'nkrp_job_action_nonce'); ?>
                    <input type="hidden" name="job_id" value="<?= esc_attr((string)$job_id) ?>">
                    <button type="submit" name="nkrp_save_job" class="nkrp-btn-save <?= $is_job_saved ? 'saved' : '' ?>" title="Save Job">
                        <?php if ($is_job_saved): ?>
                            <svg width="20" height="20" fill="#2563eb" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <?php else: ?>
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <?php endif; ?>
                    </button>
                </form>
            </div>
            
            <div class="nkrp-job-meta-row">
                <span><span class="dashicons dashicons-location"></span> <?= $location ?></span>
                <span><span class="dashicons dashicons-money-alt"></span> <?= $salary ?></span>
                <span><span class="dashicons dashicons-calendar-alt"></span> Deadline: <?= $deadline ?></span>
            </div>
        </div>
    </div>

    <div class="nkrp-job-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
        
       <div class="nkrp-job-main" style="min-width: 0; word-wrap: break-word;">
            
            <?php if (!empty($description)): ?>
                <div class="nkrp-section">
                    <h2>Job Description</h2>
                    <div class="nkrp-rich-text"><?= $description ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($responsibilities)): ?>
                <div class="nkrp-section">
                    <h2>Key Responsibilities</h2>
                    <div class="nkrp-rich-text"><?= $responsibilities ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($requirements)): ?>
                <div class="nkrp-section">
                    <h2>Requirements & Qualifications</h2>
                    <div class="nkrp-rich-text"><?= $requirements ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($benefits)): ?>
                <div class="nkrp-section">
                    <h2>Benefits & Perks</h2>
                    <div class="nkrp-rich-text"><?= $benefits ?></div>
                </div>
            <?php endif; ?>

            <div class="nkrp-share-section" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; display:flex; align-items:center; gap:15px;">
                <strong style="color:#0f172a; font-size:15px;">Share this role:</strong>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $current_url ?>&title=<?= $share_title ?>" target="_blank" class="nkrp-share-btn" style="background:#0a66c2;" title="Share on LinkedIn">
                    <svg width="18" height="18" fill="#ffffff" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= $current_url ?>&text=<?= $share_title ?>" target="_blank" class="nkrp-share-btn" style="background:#000000;" title="Share on X (Twitter)">
                    <svg width="16" height="16" fill="#ffffff" viewBox="0 0 24 24"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>
                </a>
                <a href="https://api.whatsapp.com/send?text=<?= $share_title ?>%20<?= $current_url ?>" target="_blank" class="nkrp-share-btn" style="background:#25d366;" title="Share on WhatsApp">
                    <svg width="20" height="20" fill="#ffffff" viewBox="0 0 24 24"><path d="M12.031 0c-6.627 0-12.031 5.404-12.031 12.031 0 2.115.545 4.143 1.588 5.962l-1.588 6.007 6.136-1.562c1.782.94 3.766 1.436 5.895 1.436 6.627 0 12.031-5.404 12.031-12.031s-5.404-12.031-12.031-12.031zm6.27 17.391c-.244.693-1.42 1.341-1.955 1.396-.51.052-1.157.106-3.238-.755-2.464-.997-4.041-3.526-4.16-3.684-.117-.158-1.002-1.332-1.002-2.542s.631-1.802.859-2.039c.228-.236.495-.296.66-.296.165 0 .331.002.474.009.155.008.365-.062.571.455.216.541.696 1.695.757 1.814.062.119.103.257.021.434-.082.177-.124.286-.247.433-.124.147-.258.324-.37.432-.124.119-.253.251-.114.489.14.238.625 1.026 1.331 1.731.909.907 1.691 1.189 1.93 1.328.238.139.379.119.519-.041.14-.158.604-.707.769-.949.165-.243.331-.202.55-.119.219.083 1.385.653 1.623.771.238.119.397.177.455.277.058.099.058.571-.186 1.264z"/></svg>
                </a>
            </div>

        </div>

        <div class="nkrp-job-sidebar-container" style="min-width: 0;">
            <div class="nkrp-job-sidebar">
                
                <?php if ($company_info): ?>
                    <div class="nkrp-sidebar-card" style="margin-bottom: 20px;">
                        <h3 style="margin-top:0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px;">Hiring Company</h3>
                        <div style="display:flex; align-items:center; gap: 15px;">
                            <?php if (!empty($company_info->logo)): ?>
                                <img src="<?= esc_url($company_info->logo) ?>" alt="Logo" style="width: 50px; height: 50px; border-radius: 8px; object-fit: contain; border: 1px solid #e2e8f0; padding: 2px; background: #fff;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                                    <span class="dashicons dashicons-building"></span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong style="display:block; color:#0f172a; font-size:15px; margin-bottom:2px;"><?= esc_html($company_info->company_name) ?></strong>
                                <a href="<?= esc_url(home_url('/company/' . esc_attr($company_info->company_slug) . '/')) ?>" style="font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 600;">View Profile &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="nkrp-sidebar-card">
                    <h3>Job Overview</h3>
                    
                    <ul class="nkrp-overview-list">
                        <li>
                            <strong>Experience Level</strong>
                            <span><?= $experience ?></span>
                        </li>
                        <li>
                            <strong>Vacancies</strong>
                            <span><?= $vacancies ?></span>
                        </li>
                        <li>
                            <strong>Date Posted</strong>
                            <span><?= date_i18n(get_option('date_format'), strtotime($job->created_at ?? time())) ?></span>
                        </li>
                    </ul>

                    <?php 
                    $external_url = isset($job->external_apply_url) ? esc_url($job->external_apply_url) : '';
                    ?>
                    
                    <div style="margin-top: 20px; display:flex; flex-direction:column; gap:12px;">
                        
                        <?php if ($job_id > 0): ?>
                            <?php if ($is_candidate): ?>
                                <form method="POST" action="" id="apply-form" style="margin:0;">
                                    <?php wp_nonce_field('nkrp_job_actions', 'nkrp_job_action_nonce'); ?>
                                    <input type="hidden" name="job_id" value="<?= esc_attr((string)$job_id) ?>">
                                    <input type="hidden" name="company_id" value="<?= esc_attr((string)$company_id) ?>">
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:5px;">Cover Letter (Optional)</label>
                                        <textarea name="cover_letter" rows="3" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-family:inherit; font-size:14px; box-sizing:border-box;" placeholder="Introduce yourself..."></textarea>
                                    </div>

                                    <button type="submit" name="nkrp_apply_job" class="nkrp-btn-apply">
                                        <?= !empty($external_url) ? 'Apply via NatunKicho' : 'Apply Now with Profile CV' ?>
                                    </button>
                                </form>
                            <?php elseif ($is_employer): ?>
                                <div style="background: #fffbeb; border: 1px dashed #f59e0b; padding: 15px; border-radius: 8px; text-align: center;">
                                    <p style="font-size:13px; color:#b45309; margin: 0;">You are logged in as an <strong>Employer</strong>. To apply for jobs, please switch to a Candidate account.</p>
                                </div>
                            <?php else: ?>
                                <div style="background: #ffffff; border: 1px dashed #cbd5e1; padding: 15px; border-radius: 8px; text-align: center;">
                                    <p style="font-size:13px; color:#64748b; margin: 0 0 10px 0;">You must be logged in as a candidate to apply directly via NatunKicho.</p>
                                    <a href="<?= esc_url(home_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI']))) ?>" class="nkrp-btn-apply" style="display:block; text-decoration:none;">
                                        Log in to Apply Internally
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($external_url)): ?>
                            <div style="text-align:center; position:relative; z-index:1; margin-top:5px; margin-bottom:5px;">
                                <span style="background:#f8fafc; padding:0 10px; color:#94a3b8; font-size:12px; font-weight:700; letter-spacing:0.5px;">OR</span>
                                <hr style="border-top:1px solid #e2e8f0; border-bottom:none; border-left:none; border-right:none; margin-top:-10px; z-index:-1; position:relative;">
                            </div>

                            <a href="<?= $external_url ?>" target="_blank" rel="noopener noreferrer" class="nkrp-btn-apply nkrp-btn-external" style="display:block; text-decoration:none;">
                                Apply on Company Website <span class="dashicons dashicons-external" style="font-size:16px; margin-left:5px; vertical-align:middle;"></span>
                            </a>
                        <?php endif; ?>
                        
                    </div>
                </div> 
            </div>
        </div>
    </div>

    <?php
    global $nkrp_related_jobs; 
    
    if (empty($nkrp_related_jobs) && !empty($department)) {
        $nkrp_related_jobs = $wpdb->get_results($wpdb->prepare("
            SELECT id, job_slug, job_title, city, employment_type 
            FROM {$wpdb->prefix}nkrp_jobs 
            WHERE department = %s AND id != %d AND status IN ('publish', 'published', 'active') 
            ORDER BY created_at DESC LIMIT 3
        ", $department, $job_id));
    }
    
    if (!empty($nkrp_related_jobs)): 
    ?>
    <div class="nkrp-related-jobs" style="margin-top: 50px;">
        <h3 style="font-size: 24px; color: #0f172a; margin-bottom: 20px;">Similar Jobs in <?= $department ?></h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($nkrp_related_jobs as $rel_job): 
                $rel_link = home_url('/job/' . (!empty($rel_job->job_slug) ? $rel_job->job_slug : $rel_job->id) . '/');
            ?>
                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform 0.2s;">
                    <h4 style="margin: 0 0 10px 0; font-size: 18px;"><a href="<?= esc_url($rel_link) ?>" style="color: #0f172a; text-decoration: none;"><?= esc_html($rel_job->job_title ?? $rel_job->title) ?></a></h4>
                    <div style="display: flex; gap: 15px; font-size: 13px; color: #64748b; margin-bottom: 15px;">
                        <span><span class="dashicons dashicons-location"></span> <?= esc_html($rel_job->city ?? $rel_job->location ?? 'Remote') ?></span>
                        <span><span class="dashicons dashicons-clock"></span> <?= esc_html($rel_job->employment_type ?? $rel_job->job_type) ?></span>
                    </div>
                    <a href="<?= esc_url($rel_link) ?>" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 14px;">View Details &rarr;</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .nkrp-job-details-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #334155; }
    .nkrp-job-header { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #e2e8f0; }
    .nkrp-job-title { margin: 15px 0 5px 0; font-size: 32px; color: #0f172a; font-weight: 700; line-height: 1.2; }
    .nkrp-job-meta-row { display: flex; flex-wrap: wrap; gap: 20px; font-size: 15px; color: #64748b; margin-top: 15px; }
    .nkrp-job-meta-row span { display: flex; align-items: center; gap: 6px; }
    
    .nkrp-badge { padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block; }
    .nkrp-badge-type { background: #e0e7ff; color: #3730a3; }
    .nkrp-badge-featured { background: #fef3c7; color: #b45309; margin-left: 10px; }
    
    .nkrp-btn-save { background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .nkrp-btn-save:hover { background: #e2e8f0; color: #0f172a; border-color: #cbd5e1; }
    .nkrp-btn-save.saved { background: #eff6ff; border-color: #bfdbfe; }
    .nkrp-btn-save.saved:hover { background: #dbeafe; }

    .nkrp-share-btn { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff !important; text-decoration: none; transition: opacity 0.2s; }
    .nkrp-share-btn:hover { opacity: 0.8; }

    .nkrp-job-main { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
    .nkrp-section { margin-bottom: 35px; }
    .nkrp-section h2 { font-size: 20px; color: #0f172a; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
    .nkrp-rich-text { line-height: 1.7; font-size: 16px; color: #475569; }

    .nkrp-job-sidebar-container { height: 100%; position: relative; }
    .nkrp-job-sidebar { position: sticky; top: 100px; z-index: 10; height: max-content; }
    
    .nkrp-sidebar-card { background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .nkrp-overview-list { list-style: none; padding: 0; margin: 0 0 25px 0; }
    .nkrp-overview-list li { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
    .nkrp-overview-list strong { display: block; color: #64748b; font-size: 13px; font-weight: 600; margin-bottom: 4px; text-transform: uppercase;}
    .nkrp-overview-list span { color: #0f172a; font-size: 15px; font-weight: 500; }
    
    .nkrp-btn-apply { width: 100%; background: #2563eb; color: #fff; border: none; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: background 0.2s; text-align: center; box-sizing: border-box;}
    .nkrp-btn-apply:hover { background: #1d4ed8; color: #fff;}
    .nkrp-btn-external { background: #ffffff !important; color: #0f172a !important; border: 1px solid #cbd5e1; font-size: 15px; }
    .nkrp-btn-external:hover { background: #f8fafc !important; border-color: #94a3b8; }
    
    @media(max-width: 992px) {
        .nkrp-job-grid { grid-template-columns: 1fr !important; }
        .nkrp-job-sidebar { position: static; }
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