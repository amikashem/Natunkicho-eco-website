<?php
/**
 * Template Part: Candidate & Student Logged-In View
 */
$current_user = wp_get_current_user();
$first_name = $current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name;

$saved_jobs = get_user_meta( $current_user->ID, '_job_bookmarks', true );
$saved_jobs_count = !empty($saved_jobs) && is_array($saved_jobs) ? count($saved_jobs) : 0;

$applied_query = new WP_Query([
    'post_type'      => 'job_application',
    'post_status'    => 'publish',
    'meta_query'     => [['key' => '_candidate_email', 'value' => $current_user->user_email, 'compare' => '=']],
    'fields'         => 'ids'
]);
$applied_jobs_count = $applied_query->found_posts;

$strength = 40; 
if ( get_user_meta( $current_user->ID, '_candidate_title', true ) ) $strength += 20;
if ( get_user_meta( $current_user->ID, '_candidate_location', true ) ) $strength += 20;
if ( get_user_meta( $current_user->ID, '_candidate_cv', true ) ) $strength += 20;
?>

<style>
    .nk-user-dashboard { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 60px 20px; min-height: 80vh; }
    .nk-dash-container { max-width: 1200px; margin: 0 auto; width: 100%; }
    
    .nk-welcome-banner { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 40px; color: #fff; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .nk-welcome-banner h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 800; color: #fff; }
    .nk-welcome-banner p { margin: 0; font-size: 16px; color: #cbd5e1; }

    /* DASHBOARD SEARCH BAR */
    .nk-dash-search { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 40px; border: 1px solid #e2e8f0; }
    .nk-search-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
    .nk-dash-search-tab { background: none; border: none; padding: 10px 20px; font-size: 15px; font-weight: 700; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; margin-bottom: -11px; }
    .nk-dash-search-tab.active { color: #0A66C2; border-bottom-color: #0A66C2; }
    .nk-dash-search-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
    .nk-search-input-group { flex: 1; min-width: 250px; position: relative; }
    .nk-search-input-group input, .nk-search-input-group select { width: 100%; height: 50px; padding: 0 20px 0 45px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; color: #0f172a; outline: none; background: #f8fafc; box-sizing: border-box; }
    .nk-search-input-group input:focus, .nk-search-input-group select:focus { border-color: #0A66C2; background: #fff; }
    .nk-search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8; }
    .nk-search-btn { background: #0A66C2; color: #fff; border: none; padding: 0 35px; height: 50px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; white-space: nowrap; }
    .nk-search-btn:hover { background: #08529e; }

    .nk-quick-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-stat-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
    .nk-stat-icon { font-size: 32px; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nk-stat-info h3 { margin: 0; font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1; }
    .nk-stat-info span { font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 5px; display: block; }

    .nk-section-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
    .nk-action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-action-card { background: #fff; border-radius: 12px; padding: 30px; text-align: center; border: 1px solid #e2e8f0; text-decoration: none; transition: 0.3s; display: block; }
    .nk-action-card:hover { border-color: #0A66C2; box-shadow: 0 10px 30px rgba(10,102,194,0.08); transform: translateY(-5px); }
    .nk-action-card .icon { font-size: 40px; margin-bottom: 15px; }
    .nk-action-card h4 { color: #0f172a; margin: 0 0 10px 0; font-size: 18px; font-weight: 800; }
    .nk-action-card p { color: #64748b; font-size: 14px; margin: 0; line-height: 1.5; }
</style>

<div class="nk-user-dashboard">
    <div class="nk-dash-container">
        
        <div class="nk-welcome-banner">
            <h1>Welcome back, <?php echo esc_html($first_name); ?>! 👋</h1>
            <p>Track your job applications, upgrade your skills, and access premium hospitality courses.</p>
        </div>

        <div class="nk-dash-search">
            <div class="nk-search-tabs">
                <button class="nk-dash-search-tab active" onclick="nkDashSwitchSearch('jobs', this)">💼 Search Jobs</button>
                <button class="nk-dash-search-tab" onclick="nkDashSwitchSearch('learning', this)">📚 Search Learning & Resources</button>
            </div>
            
            <form id="nk-dash-form-jobs" action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-dash-search-form">
                <input type="hidden" name="post_type" value="job_listing"> 
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">🔍</span>
                    <input type="text" name="s" placeholder="Job Title (e.g. Head Chef)" > 
                </div>
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">📍</span>
                    <input type="text" name="search_location" placeholder="Location (e.g. Maldives)">
                </div>
                <button type="submit" class="nk-search-btn">Find Jobs</button>
            </form>

            <form id="nk-dash-form-learning" action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-dash-search-form" style="display:none;">
                <input type="hidden" name="post_type" value="post"> 
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">🔍</span>
                    <input type="text" name="s" placeholder="Search Food Costing, Recipes, SOPs..."> 
                </div>
                <div class="nk-search-input-group">
                    <span class="nk-search-icon">📁</span>
                    <select name="category_name">
                        <option value="">All Categories</option>
                        <?php
                        $search_cats = get_categories(['hide_empty' => true]);
                        foreach($search_cats as $scat) {
                            echo '<option value="' . esc_attr($scat->slug) . '">' . esc_html($scat->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="nk-search-btn" style="background: #8b5cf6;">Search Articles</button>
            </form>
        </div>

        <div class="nk-quick-stats">
            <div class="nk-stat-card">
                <div class="nk-stat-icon" style="color: #0A66C2; background: #eff6ff;">📄</div>
                <div class="nk-stat-info">
                    <h3><?php echo intval($applied_jobs_count); ?></h3>
                    <span>Active Applications</span>
                </div>
            </div>
            <div class="nk-stat-card">
                <div class="nk-stat-icon" style="color: #10b981; background: #ecfdf5;">🔖</div>
                <div class="nk-stat-info">
                    <h3><?php echo intval($saved_jobs_count); ?></h3>
                    <span>Saved Jobs</span>
                </div>
            </div>
            <div class="nk-stat-card">
                <div class="nk-stat-icon" style="color: #f59e0b; background: #fffbeb;">⭐</div>
                <div class="nk-stat-info">
                    <h3><?php echo intval($strength); ?>%</h3>
                    <span>Profile Strength</span>
                </div>
            </div>
        </div>
        
        <?php echo do_shortcode('[nk_candidate_salary_estimator]'); ?>
        
        <h2 class="nk-section-title">Your Career Toolkit</h2>
        <div class="nk-action-grid">
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-action-card">
                <div class="icon">✨</div>
                <h4>AI CV Builder</h4>
                <p>Update your ATS-friendly resume to match top hospitality standards.</p>
            </a>
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=applied-jobs')); ?>" class="nk-action-card">
                <div class="icon">📊</div>
                <h4>Track Applications</h4>
                <p>View the status of jobs you've applied for and employer messages.</p>
            </a>
            <a href="<?php echo esc_url(site_url('/courses/')); ?>" class="nk-action-card">
                <div class="icon">🎓</div>
                <h4>Premium Courses</h4>
                <p>Enroll in professional hospitality certifications and video tutorials.</p>
            </a>
        </div>
    </div>
</div>

<script>
    function nkDashSwitchSearch(type, btn) {
        let tabs = btn.parentElement.querySelectorAll('.nk-dash-search-tab');
        tabs.forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        
        if(type === 'jobs') {
            document.getElementById('nk-dash-form-jobs').style.display = 'flex';
            document.getElementById('nk-dash-form-learning').style.display = 'none';
        } else {
            document.getElementById('nk-dash-form-jobs').style.display = 'none';
            document.getElementById('nk-dash-form-learning').style.display = 'flex';
        }
    }
</script>