<?php
if (!defined('ABSPATH')) exit;
get_header(); 

// 1. Get the URL parameters
$position_slug = get_query_var('salary_position');
$country_slug  = get_query_var('salary_country');

$display_position = ucwords(str_replace('-', ' ', $position_slug));
$display_country  = ucwords(str_replace('-', ' ', $country_slug));

// 2. USE THE NEW HYBRID ENGINE (Real Data -> AI Fallback)
if (function_exists('nk_get_or_estimate_salary')) {
    $stats = nk_get_or_estimate_salary($display_position, $display_country);
} else {
    global $wpdb;
    $stats = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nk_salary_aggregates WHERE position LIKE %s AND country LIKE %s", '%' . $wpdb->esc_like($display_position) . '%', '%' . $wpdb->esc_like($display_country) . '%'));
}

$has_data = !empty($stats);
$is_estimated = isset($stats->is_estimated) ? $stats->is_estimated : ($stats && $stats->sample_size == 0);

// Format the numbers
$currency   = $has_data ? $stats->currency : 'USD';
$avg_salary = $has_data ? number_format($stats->avg_salary, 0) : '0';
$min_salary = $has_data ? number_format($stats->min_salary, 0) : '0';
$max_salary = $has_data ? number_format($stats->max_salary, 0) : '0';
?>

<style>
    .nk-salary-report { padding: 80px 20px; background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 80vh; }
    .nk-report-container { max-width: 1000px; margin: 0 auto; width: 100%; }
    
    /* Increased bottom padding to 120px to allow search bar overlap */
    .nk-report-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 60px 40px 120px 40px; border-radius: 16px; text-align: center; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
    .nk-report-header h1 { margin: 15px 0 10px 0; font-size: 40px; font-weight: 900; line-height: 1.2; }
    
    .nk-badge-real { background: #10b981; color: #fff; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .nk-badge-ai { background: #8b5cf6; color: #fff; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 0 15px rgba(139,92,246,0.5); }
    
    .nk-data-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-data-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; border: 1px solid #e2e8f0; }
    .nk-data-card h4 { margin:0 0 10px 0; color:#64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    .nk-data-card .value { font-size: 36px; font-weight: 900; color: #0f172a; margin-bottom: 5px; }
    .nk-data-card .currency { font-size: 16px; color: #94a3b8; font-weight: 600; }

    /* Related Jobs Sliding Grid */
    .nk-related-section { margin-top: 60px; }
    .nk-related-section h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
    .nk-job-slider-wrapper { display: flex; overflow-x: auto; gap: 20px; padding-bottom: 20px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .nk-job-slider-wrapper::-webkit-scrollbar { display: none; }
    .nk-job-slide-card { min-width: 320px; width: 320px; scroll-snap-align: start; flex-shrink: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; transition: 0.3s; display: flex; flex-direction: column; justify-content: space-between; position: relative; text-decoration: none; }
    .nk-job-slide-card:hover { border-color: #0A66C2; box-shadow: 0 15px 30px rgba(10,102,194,0.1); transform: translateY(-5px); }
    .nk-job-slide-card h4 { margin: 0 0 10px 0; color: #0f172a; font-size: 18px; font-weight: 800; line-height: 1.3; }
    .nk-job-slide-card .meta { color: #64748b; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; flex-direction: column; gap: 5px; }
    .nk-slide-btn { background: #f8fafc; color: #0A66C2; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: bold; text-align: center; border: 1px solid #bfdbfe; transition: 0.2s; }
    .nk-job-slide-card:hover .nk-slide-btn { background: #0A66C2; color: #fff; }
</style>

<div class="nk-salary-report">
    <div class="nk-report-container">
        
        <div class="nk-report-header">
            <?php if($is_estimated): ?>
                <span class="nk-badge-ai">✨ AI Market Estimation</span>
            <?php else: ?>
                <span class="nk-badge-real">Verified Market Data</span>
            <?php endif; ?>
            
            <h1><?php echo esc_html($display_position); ?> Salary in <?php echo esc_html($display_country); ?></h1>
            
            <p style="color: #cbd5e1; margin: 0; margin-top:15px;">
                <?php if($is_estimated): ?>
                    Calculated via our global AI engine. We are actively collecting real data for this exact market.
                <?php elseif($has_data): ?>
                    Based on <strong><?php echo intval($stats->sample_size); ?></strong> verified profiles and job postings in our database.
                <?php endif; ?>
            </p>
        </div>

        <div style="position: relative; z-index: 10;">
            <?php echo do_shortcode('[nk_salary_search]'); ?>
        </div>

        <div id="nk-ai-insight-box" style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #0A66C2; border-radius: 12px; padding: 25px; margin-bottom: 40px; display: none;">
            <div style="display: flex; gap: 15px; align-items: flex-start;">
                <div style="font-size: 24px;">🤖</div>
                <div>
                    <h4 style="margin: 0 0 5px 0; color: #1e3a8a; font-size: 16px;">AI Market Insight</h4>
                    <p id="nk-ai-insight-text" style="margin: 0; color: #3b82f6; font-size: 14px; line-height: 1.6;">Analyzing live market trends...</p>
                </div>
            </div>
        </div>

        <?php if(!$has_data): ?>
            <div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 40px;">
                <h3 style="margin: 0 0 10px 0; color: #b45309; font-size: 18px;">📊 Connection Timeout</h3>
                <p style="margin: 0; color: #d97706;">The AI Engine is currently busy. Please refresh the page in a few seconds to load the global data.</p>
            </div>
        <?php else: ?>
            <div class="nk-data-grid">
                <div class="nk-data-card" style="border-top: 4px solid #f59e0b;">
                    <h4>Lowest Estimated</h4>
                    <div class="value"><?php echo esc_html($min_salary); ?></div>
                    <div class="currency"><?php echo esc_html($currency); ?> / Month</div>
                </div>
                <div class="nk-data-card" style="border-top: 4px solid #0A66C2; transform: scale(1.05); box-shadow: 0 20px 40px rgba(10,102,194,0.1);">
                    <h4 style="color: #0A66C2;">Average Market Salary</h4>
                    <div class="value" style="color: #0A66C2;"><?php echo esc_html($avg_salary); ?></div>
                    <div class="currency"><?php echo esc_html($currency); ?> / Month</div>
                </div>
                <div class="nk-data-card" style="border-top: 4px solid #10b981;">
                    <h4>Highest Estimated</h4>
                    <div class="value"><?php echo esc_html($max_salary); ?></div>
                    <div class="currency"><?php echo esc_html($currency); ?> / Month</div>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        $calc_file = get_stylesheet_directory() . '/template-parts/salaries/calc-affordability.php';
        if(file_exists($calc_file)) include($calc_file); 
        ?>

        <?php
        // ROLE CHECKER: Find out if the person looking at this is an Employer
        $is_employer = false;
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $roles = (array) $current_user->roles;
            $active_view = get_user_meta( $current_user->ID, '_nk_active_view', true );
            $primary_role = !empty($active_view) ? $active_view : (!empty($roles) ? $roles[0] : '');
            
            if ( $primary_role === 'employer' || $primary_role === 'administrator' ) {
                $is_employer = true;
            }
        }
        ?>

        <?php if ( $is_employer ) : ?>
            <div class="nk-employer-action-section" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 50px 20px; text-align: center; margin-top: 60px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <div style="font-size: 40px; margin-bottom: 15px;">🎯</div>
                <h2 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 15px;">Ready to hire a <?php echo esc_html($display_position); ?>?</h2>
                <p style="color: #64748b; font-size: 16px; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                    Use this market data to offer a competitive salary. Post your job now to reach thousands of professionals in <?php echo esc_html($display_country); ?>, or actively search our verified talent database.
                </p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo esc_url(site_url('/post-job/')); ?>" style="background: #10b981; color: #fff; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px; transition: 0.2s; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">+ Post a New Job</a>
                    <a href="<?php echo esc_url(site_url('/talent-database/')); ?>" style="background: #f8fafc; color: #0A66C2; border: 2px solid #bfdbfe; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px; transition: 0.2s;">🔍 Search Talent Database</a>
                </div>
            </div>

        <?php else : ?>
            <div class="nk-related-section">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h2>Live <?php echo esc_html($display_position); ?> Jobs in <?php echo esc_html($display_country); ?></h2>
                    <div class="nk-slider-controls">
                        <span style="font-size: 14px; font-weight:bold; color: #94a3b8; background: #fff; padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0;">← Swipe →</span>
                    </div>
                </div>
                
                <div class="nk-job-slider-wrapper">
                    <?php
                    $related_jobs = new WP_Query([
                        'post_type'      => 'job_listing',
                        'post_status'    => 'publish',
                        'posts_per_page' => 6,
                        's'              => $display_position,
                        'meta_query'     => [
                            [ 'key' => '_job_location', 'value' => $display_country, 'compare' => 'LIKE' ]
                        ]
                    ]);

                    if ( $related_jobs->have_posts() ) :
                        while ( $related_jobs->have_posts() ) : $related_jobs->the_post();
                            $company = get_post_meta( get_the_ID(), '_company_name', true ) ?: 'Confidential Employer';
                            $location = get_post_meta( get_the_ID(), '_job_location', true ) ?: $display_country;
                            $salary_min = get_post_meta( get_the_ID(), '_job_salary_min', true );
                    ?>
                        <a href="<?php the_permalink(); ?>" class="nk-job-slide-card">
                            <div>
                                <h4><?php the_title(); ?></h4>
                                <div class="meta">
                                    <span>🏢 <?php echo esc_html($company); ?></span>
                                    <span>📍 <?php echo esc_html($location); ?></span>
                                    <?php if($salary_min) echo '<span style="color:#10b981;">💰 '.esc_html($salary_min).' /mo</span>'; ?>
                                </div>
                            </div>
                            <div class="nk-slide-btn">View Job & Apply</div>
                        </a>
                    <?php 
                        endwhile; wp_reset_postdata();
                    else: 
                    ?>
                        <div style="padding: 40px; text-align: center; color: #64748b; width: 100%; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                            No open positions found matching this exact report right now.<br><br>
                            <a href="/dashboard/?tab=candidate-alerts" style="background: #0A66C2; color:#fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 10px;">🔔 Alert me when a job opens</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Fetch AI Market Insight securely
document.addEventListener('DOMContentLoaded', function() {
    let formData = new FormData();
    formData.append('action', 'nk_get_salary_insight');
    formData.append('position', '<?php echo esc_js($display_position); ?>');
    formData.append('country', '<?php echo esc_js($display_country); ?>');

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success && data.data) {
            document.getElementById('nk-ai-insight-box').style.display = 'block';
            document.getElementById('nk-ai-insight-text').innerText = data.data;
        }
    }).catch(err => console.log('Insight loading...'));
});
</script>

<?php get_footer(); ?>