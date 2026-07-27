<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PHASE 4: SALARY DASHBOARD INTEGRATIONS (Employer & Candidate)
 * =========================================================================
 */

// 1. Candidate Dashboard Widget: "Market Value Estimator"
// Hooking into the Candidate Dashboard (Assuming there is an action hook or we use a shortcode)
add_shortcode('nk_candidate_salary_estimator', 'nk_candidate_salary_estimator_widget');
function nk_candidate_salary_estimator_widget() {
    if (!is_user_logged_in()) return '';

    $user_id = get_current_user_id();
    global $wpdb;

    // Try to get Candidate profile data (Assuming WP Resume Manager)
    $args = array(
        'post_type' => 'resume',
        'author' => $user_id,
        'posts_per_page' => 1
    );
    $resumes = get_posts($args);

    $position = 'Sous Chef'; // Fallback
    $country = 'United Arab Emirates'; // Fallback
    $current_salary = 0;

    if (!empty($resumes)) {
        $resume_id = $resumes[0]->ID;
        $position = get_post_meta($resume_id, '_candidate_title', true) ?: $position;
        $loc = get_post_meta($resume_id, '_candidate_location', true);
        if ($loc) {
            // FIX: Split into a variable first to prevent pass-by-reference notice
            $loc_parts = explode(',', $loc);
            $country = trim(end($loc_parts));
        }
        $current_salary = floatval(get_post_meta($resume_id, '_candidate_expected_salary', true));
    }

    // Query our Salary Aggregates Table
    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';
    $stats = $wpdb->get_row($wpdb->prepare("
        SELECT avg_salary FROM $table_agg 
        WHERE position LIKE %s AND country LIKE %s
    ", '%' . $wpdb->esc_like($position) . '%', '%' . $wpdb->esc_like($country) . '%'));

    $avg_market = $stats ? floatval($stats->avg_salary) : 0;
    
    // UI Logic
    ob_start();
    ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; justify-content: space-between;">
            <span>📈 Your Market Value Estimator</span>
            <a href="/salaries/" style="font-size: 13px; color: #0A66C2; text-decoration: none;">View Full Report</a>
        </h3>
        
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Based on your profile as a <strong><?php echo esc_html($position); ?></strong> in <strong><?php echo esc_html($country); ?></strong>.</p>
        
        <?php if ($avg_market > 0): ?>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; background: #f8fafc; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700;">Market Average</div>
                    <div style="font-size: 24px; font-weight: 900; color: #0A66C2;"><?php echo number_format($avg_market); ?> USD</div>
                </div>
                <div style="flex: 1; background: <?php echo ($current_salary >= $avg_market) ? '#ecfdf5' : '#fef2f2'; ?>; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700;">Your Expected Salary</div>
                    <div style="font-size: 24px; font-weight: 900; color: <?php echo ($current_salary >= $avg_market) ? '#10b981' : '#ef4444'; ?>;">
                        <?php echo $current_salary > 0 ? number_format($current_salary) . ' USD' : 'Not Set'; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($current_salary > 0 && $current_salary < $avg_market): ?>
                <div style="margin-top: 15px; padding: 12px; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; color: #b45309; font-size: 13px; font-weight: 600;">
                    💡 You are currently targeting below the market average! <a href="/courses/" style="color: #d97706; text-decoration: underline;">Upgrade your skills</a> to negotiate higher.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="padding: 15px; background: #f8fafc; border-radius: 8px; color: #64748b; font-size: 14px; text-align: center;">
                We are currently collecting data for your exact position and location. Update your profile to help the community!
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// 2. Employer Dashboard Widget: "Salary Benchmark Alerts"
add_shortcode('nk_employer_salary_benchmark', 'nk_employer_salary_benchmark_widget');
function nk_employer_salary_benchmark_widget() {
    if (!is_user_logged_in()) return '';

    $user_id = get_current_user_id();
    global $wpdb;

    // Get active jobs posted by this employer
    $args = array(
        'post_type' => 'job_listing',
        'author' => $user_id,
        'post_status' => 'publish',
        'posts_per_page' => 3
    );
    $jobs = get_posts($args);

    if (empty($jobs)) return ''; // Don't show if they have no jobs

    ob_start();
    ?>
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #0f172a; display: flex; align-items: center; justify-content: space-between;">
            <span>⚖️ Active Job Salary Benchmarks</span>
            <span style="background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Premium Feature Preview</span>
        </h3>
        
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">See how your current job offerings compare to the active market average.</p>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php
            $table_agg = $wpdb->prefix . 'nk_salary_aggregates';
            
            foreach ($jobs as $job) {
                $position = $job->post_title;
                $loc = get_post_meta($job->ID, '_job_location', true);
                
                // FIX: Split into a variable first to prevent pass-by-reference notice
                $country = '';
                if ($loc) {
                    $loc_parts = explode(',', $loc);
                    $country = trim(end($loc_parts));
                }
                
                $sal_min = floatval(get_post_meta($job->ID, '_job_salary_min', true));
                $sal_max = floatval(get_post_meta($job->ID, '_job_salary_max', true));
                $offered = ($sal_min + $sal_max) / 2;

                $stats = null;
                if ($country) {
                    $stats = $wpdb->get_row($wpdb->prepare("
                        SELECT avg_salary FROM $table_agg 
                        WHERE position LIKE %s AND country LIKE %s
                    ", '%' . $wpdb->esc_like($position) . '%', '%' . $wpdb->esc_like($country) . '%'));
                }
                
                $market = $stats ? floatval($stats->avg_salary) : 0;
                
                echo '<div style="padding: 15px; border: 1px solid #f1f5f9; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">';
                echo '<div>';
                echo '<strong style="color: #0f172a; font-size: 15px;">' . esc_html($position) . '</strong>';
                echo '<div style="color: #64748b; font-size: 13px;">Offered: ' . number_format($offered) . ' USD</div>';
                echo '</div>';
                
                if ($market > 0) {
                    if ($offered >= $market) {
                        echo '<span style="color: #10b981; font-weight: bold; font-size: 13px; background: #ecfdf5; padding: 4px 10px; border-radius: 4px;">Above Market Average ✓</span>';
                    } else {
                        echo '<span style="color: #ef4444; font-weight: bold; font-size: 13px; background: #fef2f2; padding: 4px 10px; border-radius: 4px;">Below Market Average ⚠️</span>';
                    }
                } else {
                    echo '<span style="color: #94a3b8; font-size: 13px;">Analyzing Market...</span>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}  

/**
 * TEMPORARY SEED DATA: Inject dummy data so we can see the UI working
 */
add_action('admin_init', 'nk_seed_dummy_salary_data');
function nk_seed_dummy_salary_data() {
    global $wpdb;
    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';
    $table_col = $wpdb->prefix . 'nk_cost_of_living';
    
    // 1. Seed Salary Data
    if ($wpdb->get_var("SELECT COUNT(*) FROM $table_agg") == 0) {
        $wpdb->insert($table_agg, [
            'position' => 'Sous Chef', 'country' => 'United Arab Emirates',
            'avg_salary' => 5500.00, 'min_salary' => 3500.00, 'max_salary' => 8500.00,
            'sample_size' => 142, 'currency' => 'AED'
        ]);

        $wpdb->insert($table_agg, [
            'position' => 'Chef de Partie', 'country' => 'Maldives',
            'avg_salary' => 1200.00, 'min_salary' => 800.00, 'max_salary' => 1800.00,
            'sample_size' => 84, 'currency' => 'USD'
        ]);
    }

    // 2. Seed Cost of Living Data
    if ($wpdb->get_var("SELECT COUNT(*) FROM $table_col") == 0) {
        // UAE (Dubai)
        $wpdb->insert($table_col, [
            'country' => 'United Arab Emirates', 'city' => 'Dubai',
            'rent_est' => 2500.00, 'food_est' => 800.00, 'transport_est' => 300.00, 'currency' => 'AED'
        ]);

        // Maldives
        $wpdb->insert($table_col, [
            'country' => 'Maldives', 'city' => 'Resorts',
            'rent_est' => 0.00, 'food_est' => 150.00, 'transport_est' => 50.00, 'currency' => 'USD'
        ]);
    }
}