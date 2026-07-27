<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * PHASE 1: SALARY DATABASE ENGINE & AUTOMATED DATA COLLECTION
 * =========================================================================
 */

// 1. Create Custom High-Performance MySQL Tables
add_action('admin_init', 'nk_salary_create_custom_tables');
function nk_salary_create_custom_tables() {
    // Only run this once, or if we update the version number
    if (get_option('nk_salary_db_version') === '1.0.0') {
        return; 
    }

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Table 1: Raw Anonymous Data (Harvested from Jobs & Candidates)
    $table_raw = $wpdb->prefix . 'nk_salary_raw_data';
    $sql_raw = "CREATE TABLE $table_raw (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        source_type varchar(50) NOT NULL, /* 'job' or 'candidate' */
        source_id bigint(20) NOT NULL, /* Post ID or User ID */
        position varchar(150) NOT NULL,
        department varchar(100) DEFAULT '' NOT NULL,
        country varchar(100) NOT NULL,
        currency varchar(10) DEFAULT 'USD' NOT NULL,
        base_salary decimal(10,2) NOT NULL,
        benefits text DEFAULT '' NOT NULL, /* Stored as JSON */
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY position_country (position, country)
    ) $charset_collate;";
    dbDelta($sql_raw);

    // Table 2: Pre-calculated Aggregates (For Lightning-Fast Frontend Pages)
    $table_agg = $wpdb->prefix . 'nk_salary_aggregates';
    $sql_agg = "CREATE TABLE $table_agg (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        position varchar(150) NOT NULL,
        country varchar(100) NOT NULL,
        avg_salary decimal(10,2) NOT NULL,
        min_salary decimal(10,2) NOT NULL,
        max_salary decimal(10,2) NOT NULL,
        sample_size int(11) DEFAULT 0 NOT NULL,
        currency varchar(10) DEFAULT 'USD' NOT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY pos_country (position, country)
    ) $charset_collate;";
    dbDelta($sql_agg);

    // Table 3: Cost of Living Estimates (For the "Can I Afford It?" Tool)
    $table_col = $wpdb->prefix . 'nk_cost_of_living';
    $sql_col = "CREATE TABLE $table_col (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        country varchar(100) NOT NULL,
        city varchar(100) DEFAULT '' NOT NULL,
        rent_est decimal(10,2) NOT NULL,
        food_est decimal(10,2) NOT NULL,
        transport_est decimal(10,2) NOT NULL,
        currency varchar(10) DEFAULT 'USD' NOT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY country_city (country, city)
    ) $charset_collate;";
    dbDelta($sql_col);

    update_option('nk_salary_db_version', '1.0.0');
}

// 2. Automated Data Collection Hook: HARVEST FROM EMPLOYER JOB POSTS
// Triggers automatically when WP Job Manager saves a job
add_action('job_manager_update_job_data', 'nk_harvest_salary_from_jobs', 10, 2);
function nk_harvest_salary_from_jobs($job_id, $values) {
    global $wpdb;

    // Check if job is published to ensure data accuracy
    if (get_post_status($job_id) !== 'publish') return;

    $position = get_the_title($job_id);
    // Assuming location is stored in WP Job Manager's default or custom taxonomy/meta
    $location = get_post_meta($job_id, '_job_location', true); 
    
    // We expect employers to input salary in custom fields like _job_salary_min / max
    $salary_min = floatval(get_post_meta($job_id, '_job_salary_min', true));
    $salary_max = floatval(get_post_meta($job_id, '_job_salary_max', true));
    
    // Calculate a base expected salary for this entry
    $base_salary = ($salary_min > 0 && $salary_max > 0) ? (($salary_min + $salary_max) / 2) : ($salary_min ?: $salary_max);

    if ($base_salary > 0 && !empty($position) && !empty($location)) {
        
        // Clean up location to try and extract country (Can be expanded with Google Maps API later)
        $country = trim(end(explode(',', $location))); 

        $table_raw = $wpdb->prefix . 'nk_salary_raw_data';
        
        // Use REPLACE to avoid duplicate entries for the same job ID if they update it
        $wpdb->replace(
            $table_raw,
            array(
                'source_type' => 'job',
                'source_id'   => $job_id,
                'position'    => sanitize_text_field($position),
                'country'     => sanitize_text_field($country),
                'base_salary' => $base_salary,
                // Additional benefits mapping can be added here
            ),
            array('%s', '%d', '%s', '%s', '%f')
        );
    }
}

// 3. Automated Data Collection Hook: HARVEST FROM CANDIDATE RESUMES
// Triggers when candidate updates WP Resume Manager profile
add_action('resume_manager_update_resume_data', 'nk_harvest_salary_from_candidates', 10, 2);
function nk_harvest_salary_from_candidates($resume_id, $values) {
    global $wpdb;

    $position = get_post_meta($resume_id, '_candidate_title', true);
    $location = get_post_meta($resume_id, '_candidate_location', true);
    
    // Custom field where candidates state expected salary
    $expected_salary = floatval(get_post_meta($resume_id, '_candidate_expected_salary', true));

    if ($expected_salary > 0 && !empty($position) && !empty($location)) {
        
        $country = trim(end(explode(',', $location))); 
        $table_raw = $wpdb->prefix . 'nk_salary_raw_data';
        
        $wpdb->replace(
            $table_raw,
            array(
                'source_type' => 'candidate',
                'source_id'   => $resume_id,
                'position'    => sanitize_text_field($position),
                'country'     => sanitize_text_field($country),
                'base_salary' => $expected_salary,
            ),
            array('%s', '%d', '%s', '%s', '%f')
        );
    }
}