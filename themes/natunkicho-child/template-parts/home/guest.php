<?php
/**
 * Template Name: SaaS Homepage (NatunKicho)
 * Description: The dynamic 40/30/20/10 structure for the NatunKicho platform.
 */
get_header(); 
?>

<style>
    .nk-home-wrapper { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #334155; }
    .nk-home-section { padding: 80px 20px; box-sizing: border-box; }
    .nk-container { max-width: 1200px; margin: 0 auto; width: 100%; box-sizing: border-box; }
    .nk-section-title { font-size: 32px; font-weight: 800; color: #0f172a; margin: 0 0 15px 0; text-align: center; }
    .nk-section-subtitle { font-size: 16px; color: #64748b; text-align: center; margin: 0 0 50px 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; }
    
    /* Hero Section */
    .nk-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 100px 20px 120px; text-align: center; color: #fff; position: relative; overflow: hidden; }
    .nk-hero-badge { display: inline-block; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 20px; letter-spacing: 1px; text-transform: uppercase; }
    .nk-hero h1 { font-size: 48px; font-weight: 900; margin: 0 0 20px 0; line-height: 1.2; }
    .nk-hero p { font-size: 18px; color: #cbd5e1; max-width: 700px; margin: 0 auto 40px; line-height: 1.6; }
    
    /* Dual Search Bar */
    .nk-search-container { background: #fff; border-radius: 12px; padding: 10px; max-width: 800px; margin: 0 auto 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    .nk-search-tabs { display: flex; gap: 10px; margin-bottom: 15px; padding: 0 10px; border-bottom: 1px solid #f1f5f9; }
    .nk-search-tab { background: none; border: none; padding: 10px 20px; font-size: 15px; font-weight: 700; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; }
    .nk-search-tab.active { color: #0A66C2; border-bottom-color: #0A66C2; }
    .nk-search-form { display: flex; gap: 10px; padding: 0 10px 10px; align-items: stretch; }
    .nk-search-input-group { flex: 1; position: relative; }
    .nk-search-input-group input, .nk-search-input-group select { width: 100%; height: 100%; padding: 15px 20px 15px 45px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; color: #0f172a; outline: none; transition: 0.2s; background: #f8fafc; box-sizing: border-box; }
    .nk-search-input-group input:focus, .nk-search-input-group select:focus { border-color: #0A66C2; background: #fff; }
    .nk-search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8; }
    .nk-search-btn { background: #0A66C2; color: #fff; border: none; padding: 15px 35px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(10, 102, 194, 0.2); line-height: 1; height: 100%; box-sizing: border-box; }
    .nk-search-btn:hover { background: #08529e; transform: translateY(-1px); }
    
    .nk-hero-ctas { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
    .nk-btn-primary { background: #10b981; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; transition: 0.2s; display: inline-block; }
    .nk-btn-outline { background: transparent; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; border: 1px solid rgba(255,255,255,0.3); transition: 0.2s; display: inline-block; }
    .nk-btn-outline:hover { background: rgba(255,255,255,0.1); border-color: #fff; }

    /* Stats Grid */
    .nk-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; background: #fff; border-radius: 12px; padding: 30px; margin-top: -60px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; z-index: 10; }
    .nk-stat-item { text-align: center; border-right: 1px solid #e2e8f0; }
    .nk-stat-item:last-child { border-right: none; }
    .nk-stat-number { font-size: 32px; font-weight: 900; color: #0A66C2; margin-bottom: 5px; }
    .nk-stat-label { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

    /* Grids & Cards */
    .nk-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
    .nk-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    
    .nk-path-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px 30px; transition: 0.3s; position: relative; overflow: hidden; display: flex; flex-direction: column; }
    .nk-path-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border-color: #cbd5e1; }
    .nk-path-icon { font-size: 40px; margin-bottom: 20px; }
    .nk-path-card h3 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 20px 0; }
    .nk-path-list { list-style: none; padding: 0; margin: 0 0 30px 0; flex: 1; }
    .nk-path-list li { margin-bottom: 12px; font-size: 15px; color: #475569; display: flex; align-items: start; gap: 10px; }
    .nk-path-list li::before { content: '✓'; color: #10b981; font-weight: bold; }
    .nk-path-btn { background: #f8fafc; color: #0A66C2; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: bold; text-align: center; border: 1px solid #bfdbfe; transition: 0.2s; width: 100%; display: block; box-sizing: border-box; }
    .nk-path-card:hover .nk-path-btn { background: #0A66C2; color: #fff; }

    /* Tools Grid */
    .nk-tool-card { display: flex; align-items: flex-start; gap: 15px; padding: 20px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; transition: 0.2s; text-decoration: none; }
    .nk-tool-card:hover { border-color: #0A66C2; box-shadow: 0 10px 20px rgba(10,102,194,0.05); transform: translateY(-2px); }
    .nk-tool-icon { width: 50px; height: 50px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #0A66C2; flex-shrink: 0; }
    .nk-tool-card h4 { margin: 0 0 5px 0; font-size: 16px; font-weight: 800; color: #0f172a; transition: color 0.2s; }
    .nk-tool-card:hover h4 { color: #0A66C2; }
    .nk-tool-card p { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

    /* Category Pills */
    .nk-cat-pill { background: #fff; border: 1px solid #e2e8f0; padding: 15px 25px; border-radius: 50px; font-weight: 700; color: #334155; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .nk-cat-pill:hover { border-color: #10b981; color: #10b981; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16,185,129,0.1); }

    /* Article Cards */
    .nk-article-card { background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; text-decoration: none; display: block; transition: 0.3s; }
    .nk-article-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
    .nk-article-img { height: 200px; background: #e2e8f0; position: relative; background-size: cover; background-position: center; }
    .nk-article-badge { position: absolute; top: 15px; left: 15px; background: #0A66C2; color: #fff; font-size: 11px; font-weight: bold; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; }
    .nk-article-content { padding: 20px; }
    .nk-article-content h4 { margin: 0 0 10px 0; font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.4; }
    .nk-article-content p { margin: 0 0 15px 0; font-size: 14px; color: #64748b; line-height: 1.5; }
    .nk-article-meta { font-size: 12px; color: #94a3b8; font-weight: 600; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }

    /* Final CTA */
    .nk-final-cta { background: #0f172a; padding: 100px 20px; text-align: center; color: #fff; }
    .nk-final-cta h2 { font-size: 36px; font-weight: 900; margin: 0 0 20px 0; }
    .nk-final-cta p { font-size: 18px; color: #94a3b8; max-width: 600px; margin: 0 auto 40px; }
    .nk-final-buttons { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }

    @media (max-width: 991px) {
        .nk-grid-3, .nk-grid-4 { grid-template-columns: repeat(2, 1fr); }
        .nk-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .nk-stat-item:nth-child(2) { border-right: none; }
        .nk-stat-item { padding: 10px 0; }
    }
    @media (max-width: 768px) {
        .nk-grid-3, .nk-grid-4 { grid-template-columns: 1fr; }
        .nk-stats-grid { grid-template-columns: 1fr; margin-top: 20px; }
        .nk-stat-item { border-right: none; border-bottom: 1px solid #e2e8f0; padding: 20px 0; }
        .nk-stat-item:last-child { border-bottom: none; }
        .nk-search-form { flex-direction: column; }
        .nk-hero h1 { font-size: 32px; }
    }
</style>

<div class="nk-home-wrapper">

    <section class="nk-hero">
        <div class="nk-container">
            <span class="nk-hero-badge">The Complete Hospitality Platform</span>
            <h1>Learn. Build Your Career. Get Hired.</h1>
            <p>Hospitality training, professional CV creation, AI career tools, and thousands of top-tier hospitality jobs — all in one unified ecosystem.</p>
            
            <div class="nk-search-container">
                <div class="nk-search-tabs">
                    <button class="nk-search-tab active" onclick="nkSwitchSearch('jobs')">💼 Search Jobs</button>
                    <button class="nk-search-tab" onclick="nkSwitchSearch('learning')">📚 Search Learning & Resources</button>
                </div>
                
                <form id="nk-form-jobs" action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-search-form">
                    <input type="hidden" name="post_type" value="job_listing"> <div class="nk-search-input-group">
                        <span class="nk-search-icon"></span>
                        <input type="text" name="s" placeholder="Job Title (e.g. Head Chef)" > 
                    </div>
                    <div class="nk-search-input-group">
                        <span class="nk-search-icon"></span>
                        <input type="text" name="search_location" placeholder="Location (e.g. Maldives)">
                    </div>
                    <button type="submit" class="nk-search-btn">Find Jobs</button>
                </form>

                <form id="nk-form-learning" action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="nk-search-form" style="display:none;">
                    <input type="hidden" name="post_type" value="post"> <div class="nk-search-input-group">
                        <span class="nk-search-icon"></span>
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
                    <button type="submit" class="nk-search-btn">Search Articles</button>
                </form>
            </div>

            <div class="nk-hero-ctas">
                <a href="<?php echo esc_url(site_url('/jobs/')); ?>" class="nk-btn-primary">View All Jobs</a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-btn-outline">Build ATS-Friendly CV</a>
                <a href="#learning" class="nk-btn-outline">Explore Learning Center</a>
            </div>
        </div>
    </section>

    <div class="nk-container">
        <div class="nk-stats-grid">
            <div class="nk-stat-item">
                <div class="nk-stat-number">50,000+</div>
                <div class="nk-stat-label">Verified Candidates</div>
            </div>
            <div class="nk-stat-item">
                <div class="nk-stat-number">10,000+</div>
                <div class="nk-stat-label">Jobs Posted</div>
            </div>
            <div class="nk-stat-item">
                <div class="nk-stat-number">2,000+</div>
                <div class="nk-stat-label">Active Employers</div>
            </div>
            <div class="nk-stat-item">
                <div class="nk-stat-number">1,000+</div>
                <div class="nk-stat-label">Learning Resources</div>
            </div>
        </div>
    </div>

    <section class="nk-home-section" style="background: #f8fafc; margin-top: -50px; padding-top: 130px;">
        <div class="nk-container">
            <h2 class="nk-section-title">Choose Your Path</h2>
            <p class="nk-section-subtitle">Whether you are hiring staff, looking for a job, or upgrading your hospitality skills, your journey starts here.</p>
            
            <div class="nk-grid-3">
                <div class="nk-path-card">
                    <div class="nk-path-icon">👨‍🍳</div>
                    <h3>Job Seeker</h3>
                    <ul class="nk-path-list">
                        <li>Search Global Hospitality Jobs</li>
                        <li>Create a Professional Profile</li>
                        <li>Use the ATS-Friendly CV Builder</li>
                        <li>AI Resume & Summary Assistant</li>
                        <li>Track Active Applications</li>
                        <li>Get Real-Time Job Alerts</li>
                    </ul>
                    <a href="<?php echo esc_url(site_url('/register/')); ?>" class="nk-path-btn">Start Your Career Journey</a>
                </div>

                <div class="nk-path-card" style="border-top: 4px solid #10b981;">
                    <div class="nk-path-icon">🏢</div>
                    <h3>Employer</h3>
                    <ul class="nk-path-list">
                        <li>Post Jobs to Global Audience</li>
                        <li>Search the Talent Database</li>
                        <li>AI-Powered Candidate Matching</li>
                        <li>Download Premium ATS CVs</li>
                        <li>Message Candidates Directly</li>
                        <li>Unlimited Hiring Options</li>
                    </ul>
                    <a href="<?php echo esc_url(site_url('/post-job/')); ?>" class="nk-path-btn" style="background: #10b981; color: #fff; border-color: #059669;">Hire Top Talent</a>
                </div>

                <div class="nk-path-card">
                    <div class="nk-path-icon">📚</div>
                    <h3>Hospitality Learning</h3>
                    <ul class="nk-path-list">
                        <li>Master Food Costing & Yields</li>
                        <li>Access Standardized Recipes</li>
                        <li>SOP & Service Standards</li>
                        <li>Kitchen Management Guides</li>
                        <li>Hotel Operations Training</li>
                        <li>Consultancy Resources</li>
                    </ul>
                    <a href="#learning" class="nk-path-btn">Start Learning</a>
                </div>
            </div>
        </div>
    </section>

    <section class="nk-home-section">
        <div class="nk-container">
            <h2 class="nk-section-title">Career Success Toolkit</h2>
            <p class="nk-section-subtitle">Get hired faster using our suite of AI-powered and ATS-optimized career tools.</p>
            
            <div class="nk-grid-3">
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">📄</div>
                    <div>
                        <h4>ATS Resume Builder</h4>
                        <p>Create resumes that pass through Applicant Tracking Systems perfectly.</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">✨</div>
                    <div>
                        <h4>AI CV Generator</h4>
                        <p>Let our AI write your professional summary and job responsibilities.</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">🔍</div>
                    <div>
                        <h4>Full CV Audit</h4>
                        <p>Our AI Recruiter analyzes and rewrites your resume for maximum impact.</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=applied-jobs')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">📊</div>
                    <div>
                        <h4>Application Tracker</h4>
                        <p>Manage and track the status of every job you apply for in one place.</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(site_url('/blog/')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">🗣️</div>
                    <div>
                        <h4>Interview Prep</h4>
                        <p>Access specialized guides for passing hospitality interviews.</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=candidate-alerts')); ?>" class="nk-tool-card">
                    <div class="nk-tool-icon">🔔</div>
                    <div>
                        <h4>Smart Job Alerts</h4>
                        <p>Get notified instantly when top employers post jobs matching your skills.</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="nk-home-section" style="background: #f8fafc;">
        <div class="nk-container">
            <h2 class="nk-section-title">Popular Hospitality Categories</h2>
            <p class="nk-section-subtitle">Explore open roles and learning resources in the most in-demand sectors of the industry.</p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                <?php
                // Fetch the Top 8 Blog Categories dynamically from WordPress
                $blog_categories = get_categories([
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'number'     => 8,
                    'hide_empty' => true
                ]);

                // Array of cool hospitality emojis to assign to the dynamic categories
                $emojis = ['🍽️', '🏨', '🍷', '☕', '👨‍🍳', '📊', '🧾', '👔', '📚', '💡'];

                if ( !empty($blog_categories) ) {
                    $emoji_index = 0;
                    foreach ( $blog_categories as $cat ) {
                        // Get the emoji, and loop back to the start if we have more categories than emojis
                        $emoji = $emojis[ $emoji_index % count($emojis) ];
                        $cat_link = get_category_link( $cat->term_id );
                        
                        echo '<a href="' . esc_url($cat_link) . '" class="nk-cat-pill">' . $emoji . ' ' . esc_html($cat->name) . '</a>';
                        $emoji_index++;
                    }
                } else {
                    echo '<p style="color:#64748b;">No categories published yet.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <section class="nk-home-section">
        <div class="nk-container">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h2 class="nk-section-title" style="text-align: left; margin-bottom: 5px;">Featured Jobs</h2>
                    <p style="color: #64748b; margin: 0;">Top opportunities across the globe.</p>
                </div>
                <a href="<?php echo esc_url(site_url('/jobs/')); ?>" class="nk-btn-primary" style="background: #0A66C2;">View All Jobs →</a>
            </div>
            
            <div class="nk-grid-3">
                <?php
                $job_args = array(
                    'post_type'      => 'job_listing',
                    'post_status'    => 'publish',
                    'posts_per_page' => 6,
                    'orderby'        => 'date',
                    'order'          => 'DESC'
                );
                $jobs_query = new WP_Query( $job_args );

                if ( $jobs_query->have_posts() ) :
                    while ( $jobs_query->have_posts() ) : $jobs_query->the_post();
                        $job_title = get_the_title();
                        $job_url   = get_permalink();
                        $company   = get_post_meta( get_the_ID(), '_company_name', true ) ?: 'Confidential Employer';
                        $location  = get_post_meta( get_the_ID(), '_job_location', true ) ?: 'Remote / Flexible';
                        
                        echo '<a href="' . esc_url( $job_url ) . '" style="display: flex; flex-direction: column; justify-content: space-between; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-decoration: none; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.02); min-height: 120px;" onmouseover="this.style.transform=\'translateY(-4px)\'; this.style.boxShadow=\'0 12px 20px rgba(0,0,0,0.06)\'; this.style.borderColor=\'#bfdbfe\';" onmouseout="this.style.transform=\'none\'; this.style.boxShadow=\'0 4px 6px rgba(0,0,0,0.02)\'; this.style.borderColor=\'#e2e8f0\';">';
                        
                        echo '<div>';
                        echo '<h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 16px; font-weight: 800; line-height: 1.3;">' . esc_html( $job_title ) . '</h4>';
                        echo '<p style="margin: 0 0 12px 0; font-size: 13px; color: #0A66C2; font-weight: 700;">🏢 ' . esc_html( $company ) . '</p>';
                        echo '</div>';
                        
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 12px; margin-top: auto;">';
                        echo '<p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 600;">📍 ' . esc_html( $location ) . '</p>';
                        echo '<span style="font-size: 18px; color: #cbd5e1;">→</span>';
                        echo '</div>';
                        
                        echo '</a>';
                    endwhile;
                    wp_reset_postdata();
                else:
                    echo '<p style="color:#64748b; font-style:italic;">No jobs posted yet.</p>';
                endif;
                ?>
            </div>
        </div>
    </section>

    <section id="learning" class="nk-home-section" style="background: #f8fafc;">
        <div class="nk-container">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h2 class="nk-section-title" style="text-align: left; margin-bottom: 5px;">Hospitality Knowledge Center</h2>
                    <p style="color: #64748b; margin: 0;">Master your craft with our latest articles, recipes, and management guides.</p>
                </div>
                <a href="<?php echo esc_url(site_url('/blog/')); ?>" class="nk-btn-outline" style="color: #0A66C2; border-color: #cbd5e1;">Read More Articles</a>
            </div>

            <div class="nk-grid-3">
                <?php
                $blog_args = array( 'post_type' => 'post', 'posts_per_page' => 3 );
                $blog_query = new WP_Query( $blog_args );
                
                if ( $blog_query->have_posts() ) :
                    while ( $blog_query->have_posts() ) : $blog_query->the_post(); 
                        $category = get_the_category();
                        $cat_name = !empty($category) ? esc_html($category[0]->name) : 'Article';
                        $img_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium_large') : 'https://via.placeholder.com/600x400?text=Learning';
                ?>
                    <a href="<?php the_permalink(); ?>" class="nk-article-card">
                        <div class="nk-article-img" style="background-image: url('<?php echo esc_url($img_url); ?>');">
                            <span class="nk-article-badge"><?php echo $cat_name; ?></span>
                        </div>
                        <div class="nk-article-content">
                            <h4><?php the_title(); ?></h4>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 15, '...' ); ?></p>
                            <div class="nk-article-meta">
                                <span>🗓️ <?php echo get_the_date(); ?></span>
                                <span>Read Article →</span>
                            </div>
                        </div>
                    </a>
                <?php 
                    endwhile; 
                    wp_reset_postdata();
                else: 
                    echo '<p style="color:#64748b; font-style:italic;">No learning articles published yet.</p>';
                endif; 
                ?>
            </div>
        </div>
    </section>

    <section class="nk-home-section">
        <div class="nk-container">
            <h2 class="nk-section-title">Premium Consultancy & Training</h2>
            <p class="nk-section-subtitle">Scale your hospitality business with our expert consultancy and operational audits.</p>
            
            <div class="nk-grid-3">
                <div class="nk-tool-card" style="flex-direction: column; align-items: center; text-align: center; cursor: default;">
                    <div class="nk-tool-icon" style="background: #fef3c7; color: #d97706;">📉</div>
                    <h4>Food Costing Consultancy</h4>
                    <p style="margin-bottom: 15px;">Optimize your menus to reduce waste and maximize profit margins.</p>
                </div>
                <div class="nk-tool-card" style="flex-direction: column; align-items: center; text-align: center; border-color: #0A66C2; box-shadow: 0 10px 20px rgba(10,102,194,0.05); cursor: default;">
                    <div class="nk-tool-icon" style="background: #eff6ff; color: #0A66C2;">🏪</div>
                    <h4>Restaurant Setup</h4>
                    <p style="margin-bottom: 15px;">End-to-end operational planning for new hospitality ventures.</p>
                </div>
                <div class="nk-tool-card" style="flex-direction: column; align-items: center; text-align: center; cursor: default;">
                    <div class="nk-tool-icon" style="background: #f0fdf4; color: #16a34a;">👥</div>
                    <h4>Staff Training Programs</h4>
                    <p style="margin-bottom: 15px;">Elevate your service standards with intensive team training.</p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="<?php echo esc_url(site_url('/contact/')); ?>" class="nk-btn-primary" style="background: #0f172a; padding: 16px 32px; font-size: 16px;">Request a Consultation</a>
            </div>
        </div>
    </section>

    <section class="nk-home-section" style="background: #f8fafc;">
        <div class="nk-container">
            <h2 class="nk-section-title">Success Stories</h2>
            <p class="nk-section-subtitle">See how NatunKicho is transforming careers and businesses globally.</p>
            
            <div class="nk-grid-3">
                <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <div style="color: #f59e0b; font-size: 20px; margin-bottom: 15px;">★★★★★</div>
                    <p style="font-style: italic; color: #334155; margin-bottom: 20px;">"I created my profile using the AI CV Builder and applied for a role in the Maldives. The ATS-friendly layout got me an interview within 3 days. I start next month!"</p>
                    <div style="font-weight: 800; color: #0f172a;">- Candidate Story</div>
                </div>
                <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <div style="color: #f59e0b; font-size: 20px; margin-bottom: 15px;">★★★★★</div>
                    <p style="font-style: italic; color: #334155; margin-bottom: 20px;">"We needed specialized F&B staff urgently. Using the Premium Talent Database, we found and hired 20 verified staff members within 10 days."</p>
                    <div style="font-weight: 800; color: #0f172a;">- Employer Story</div>
                </div>
                <div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    <div style="color: #f59e0b; font-size: 20px; margin-bottom: 15px;">★★★★★</div>
                    <p style="font-style: italic; color: #334155; margin-bottom: 20px;">"The consultancy resources and articles on Food Costing helped our restaurant reduce overall food waste by 12% in just two months."</p>
                    <div style="font-weight: 800; color: #0f172a;">- Training Story</div>
                </div>
            </div>
        </div>
    </section>

    <section class="nk-final-cta">
        <div class="nk-container">
            <h2>Ready to Grow Your Hospitality Career?</h2>
            <p>Whether you're looking for a job, hiring talent, or improving your hospitality skills, everything starts right here.</p>
            <div class="nk-final-buttons">
                <a href="<?php echo esc_url(site_url('/jobs/')); ?>" class="nk-btn-primary">Find Jobs</a>
                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" class="nk-btn-outline">Build CV</a>
                <a href="<?php echo esc_url(site_url('/post-job/')); ?>" class="nk-btn-outline">Hire Staff</a>
                <a href="#learning" class="nk-btn-outline">Explore Learning</a>
            </div>
        </div>
    </section>

</div>

<script>
    function nkSwitchSearch(type) {
        // Reset tabs
        document.querySelectorAll('.nk-search-tab').forEach(t => t.classList.remove('active'));
        
        if(type === 'jobs') {
            document.querySelectorAll('.nk-search-tab')[0].classList.add('active');
            document.getElementById('nk-form-jobs').style.display = 'flex';
            document.getElementById('nk-form-learning').style.display = 'none';
        } else {
            document.querySelectorAll('.nk-search-tab')[1].classList.add('active');
            document.getElementById('nk-form-jobs').style.display = 'none';
            document.getElementById('nk-form-learning').style.display = 'flex';
        }
    }
</script>

<?php get_footer(); ?>