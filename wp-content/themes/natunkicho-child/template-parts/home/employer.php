<?php
/**
 * Template Part: Employer & Vendor Logged-In View
 */
$current_user = wp_get_current_user();
$company_name = get_user_meta( $current_user->ID, '_company_name', true );
$display_name = $company_name ? $company_name : $current_user->display_name;

// 1. Dynamic Active Jobs Count
$active_jobs_query = new WP_Query([
    'post_type'      => 'job_listing',
    'post_status'    => 'publish',
    'author'         => $current_user->ID,
    'fields'         => 'ids'
]);
$active_jobs = $active_jobs_query->found_posts;

// 2. Dynamic Applicants Count (Fetching applications assigned to this employer's jobs)
// Using a simpler fallback query for UI speed, assuming applications are tied to employer
$total_applicants = 0;
if ($active_jobs > 0) {
    $apps_query = new WP_Query([
        'post_type'      => 'job_application',
        'post_status'    => 'publish',
        'post_parent__in'=> $active_jobs_query->posts, // Matches apps to the employer's jobs
        'fields'         => 'ids'
    ]);
    $total_applicants = $apps_query->found_posts;
}
?>

<style>
    .nk-emp-dashboard { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 60px 20px; min-height: 80vh; }
    .nk-dash-container { max-width: 1200px; margin: 0 auto; width: 100%; }
    
    /* Employer Header */
    .nk-emp-header { background: #0A66C2; border-radius: 16px; padding: 40px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 10px 30px rgba(10,102,194,0.15); margin-bottom: 40px; }
    .nk-emp-header h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 800; color: #fff; line-height: 1.2; }
    .nk-emp-header p { margin: 0; color: #e0f2fe; font-size: 16px; }
    .nk-emp-btn-primary { background: #fff; color: #0A66C2; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; transition: 0.2s; border: none; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .nk-emp-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); color: #08529e; }

    /* Metrics Grid */
    .nk-metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-metric-card { background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 5px solid #0A66C2; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    .nk-metric-card h4 { margin: 0 0 10px 0; color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: 700; }
    .nk-metric-card .number { font-size: 40px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1; }
    
    /* Section Titles */
    .nk-section-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
    
    /* Tools Grid */
    .nk-emp-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-emp-tool { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; display: flex; align-items: flex-start; gap: 20px; text-decoration: none; transition: 0.3s; }
    .nk-emp-tool:hover { border-color: #10b981; box-shadow: 0 10px 30px rgba(16,185,129,0.08); transform: translateY(-3px); }
    .nk-emp-tool-icon { font-size: 32px; background: #f8fafc; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nk-emp-tool h3 { margin: 0 0 8px 0; color: #0f172a; font-size: 20px; font-weight: 800; }
    .nk-emp-tool p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }

    /* Wholesale Alert Banner */
    .nk-wholesale-banner { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px; flex-wrap: wrap; gap: 15px; }
    .nk-wholesale-banner h3 { margin: 0 0 5px 0; color: #b45309; font-size: 18px; font-weight: 800; }
    .nk-wholesale-banner p { margin: 0; color: #d97706; font-size: 14px; }
    .nk-btn-warning { background: #f59e0b; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; }
</style>

<div class="nk-emp-dashboard">
    <div class="nk-dash-container">
        
        <div class="nk-emp-header">
            <div>
                <h1>Employer Command Center 🏢</h1>
                <p>Welcome, <?php echo esc_html($display_name); ?>. Manage your hiring, training, and procurement here.</p>
            </div>
            <div>
                <a href="<?php echo esc_url(site_url('/post-job/')); ?>" class="nk-emp-btn-primary">+ Post a New Job</a>
            </div>
        </div>

        <div class="nk-metrics-grid">
            <div class="nk-metric-card" style="border-left-color: #0A66C2;">
                <h4>Active Job Postings</h4>
                <div class="number"><?php echo intval($active_jobs); ?></div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #10b981;">
                <h4>Total Applicants</h4>
                <div class="number"><?php echo intval($total_applicants); ?></div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #8b5cf6;">
                <h4>Wholesale Orders</h4>
                <div class="number">0</div>
            </div>
        </div>

        <div class="nk-wholesale-banner">
            <div>
                <h3>📦 Procure Hospitality Supplies at Wholesale Rates</h3>
                <p>Discover B2B pricing on kitchen equipment, uniforms, and premium ingredients.</p>
            </div>
            <a href="<?php echo esc_url(site_url('/hello/')); ?>" class="nk-btn-warning">Explore Wholesale Store</a>
        </div>
        <?php echo do_shortcode('[nk_employer_salary_benchmark]'); ?>

        <h2 class="nk-section-title">Business & Hiring Tools</h2>
        <div class="nk-emp-actions">
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=employer-applications')); ?>" class="nk-emp-tool">
                <div class="nk-emp-tool-icon" style="color: #0A66C2; background: #eff6ff;">👥</div>
                <div>
                    <h3>Review Applications</h3>
                    <p>Review incoming CVs, shortlist candidates, and manage your hiring pipeline.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=talent-database')); ?>" class="nk-emp-tool">
                <div class="nk-emp-tool-icon" style="color: #10b981; background: #ecfdf5;">🔍</div>
                <div>
                    <h3>Search Talent Database</h3>
                    <p>Proactively search our database of verified hospitality professionals globally.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/courses/')); ?>" class="nk-emp-tool">
                <div class="nk-emp-tool-icon" style="color: #ef4444; background: #fef2f2;">🎓</div>
                <div>
                    <h3>Staff Training Courses</h3>
                    <p>Buy premium courses and SOP packages in bulk to train your hotel/restaurant staff.</p>
                </div>
            </a>

            <a href="<?php echo esc_url(site_url('/dashboard/?tab=company-profile')); ?>" class="nk-emp-tool">
                <div class="nk-emp-tool-icon" style="color: #f59e0b; background: #fffbeb;">🏢</div>
                <div>
                    <h3>Company Profile</h3>
                    <p>Update your employer branding, upload photos, and showcase your company culture.</p>
                </div>
            </a>
        </div>

    </div>
</div>