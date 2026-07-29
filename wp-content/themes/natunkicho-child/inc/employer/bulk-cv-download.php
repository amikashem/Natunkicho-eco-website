<?php
/**
 * =========================================
 * BULK CV DOWNLOAD (Premium Employer Feature)
 * Path: inc/employer/bulk-cv-download.php
 * Max 10 CVs at a time, delivered as a ZIP
 * =========================================
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler: Generate a ZIP file containing up to 10 candidate CVs.
 * Only available to premium employers and administrators.
 */
add_action( 'wp_ajax_nk_bulk_cv_download', 'nk_bulk_cv_download_handler' );

function nk_bulk_cv_download_handler() {
    // 1. Security check
    check_ajax_referer( 'nk_bulk_cv_nonce', 'security' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Please log in to access this feature.' ] );
    }

    $employer_id   = get_current_user_id();
    $current_user  = wp_get_current_user();

    // 2. Premium access check (mirrors talent-database.php logic)
    $employer_access_id = 2949;
    $is_premium = false;

    if ( function_exists( 'wc_customer_bought_product' ) ) {
        if ( wc_customer_bought_product( $current_user->user_email, $employer_id, $employer_access_id ) ) {
            $is_premium = true;
        }
    }
    if ( ! $is_premium && function_exists( 'nk_is_user_premium' ) ) {
        $is_premium = nk_is_user_premium( $employer_id );
    }
    if ( in_array( 'administrator', (array) $current_user->roles, true ) ) {
        $is_premium = true;
    }

    if ( ! $is_premium ) {
        wp_send_json_error( [ 'message' => 'This feature is only available to Premium Employers.' ] );
    }

    // 3. Validate candidate IDs
    $candidate_ids = isset( $_POST['candidate_ids'] ) ? array_map( 'intval', (array) $_POST['candidate_ids'] ) : [];
    $candidate_ids = array_filter( $candidate_ids, function( $id ) { return $id > 0; } );
    $candidate_ids = array_unique( $candidate_ids );

    if ( empty( $candidate_ids ) ) {
        wp_send_json_error( [ 'message' => 'No candidates selected.' ] );
    }

    // 4. Enforce maximum of 10
    if ( count( $candidate_ids ) > 10 ) {
        wp_send_json_error( [ 'message' => 'You can download a maximum of 10 CVs at a time.' ] );
    }

    // 5. Verify all selected users are public candidates
    foreach ( $candidate_ids as $cand_id ) {
        $user = get_userdata( $cand_id );
        if ( ! $user ) {
            wp_send_json_error( [ 'message' => 'Invalid candidate selected (ID: ' . $cand_id . ').' ] );
        }
        $roles = (array) $user->roles;
        if ( ! array_intersect( $roles, [ 'job_seeker', 'premium_job_seeker' ] ) ) {
            wp_send_json_error( [ 'message' => 'Selected user is not a candidate.' ] );
        }
        // Check public visibility
        $cv_public = get_user_meta( $cand_id, 'nk_pref_cv_public', true );
        if ( $cv_public === '0' ) {
            wp_send_json_error( [ 'message' => 'Candidate #' . $cand_id . ' has not made their CV public.' ] );
        }
    }

    // 6. Generate individual CV HTML files and package into ZIP
    global $wpdb;

    $upload_dir = wp_upload_dir();
    $tmp_dir    = trailingslashit( $upload_dir['basedir'] ) . 'nk-bulk-cv-tmp/';

    // Create temp directory if it doesn't exist
    if ( ! file_exists( $tmp_dir ) ) {
        wp_mkdir_p( $tmp_dir );
    }

    // Unique filename for this download
    $zip_filename = 'bulk-cv-' . $employer_id . '-' . time() . '.zip';
    $zip_path     = $tmp_dir . $zip_filename;

    // Check ZipArchive availability
    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_send_json_error( [ 'message' => 'Server does not support ZIP generation. Please contact support.' ] );
    }

    $zip = new ZipArchive();
    if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
        wp_send_json_error( [ 'message' => 'Unable to create ZIP file. Please try again.' ] );
    }

    foreach ( $candidate_ids as $cand_id ) {
        $cv_html = nk_generate_cv_html_for_download( $cand_id );
        $user    = get_userdata( $cand_id );
        $name    = sanitize_file_name( $user->display_name ?: 'candidate-' . $cand_id );
        $zip->addFromString( $name . '-cv.html', $cv_html );
    }

    $zip->close();

    // 7. Generate a temporary download URL
    $zip_url = trailingslashit( $upload_dir['baseurl'] ) . 'nk-bulk-cv-tmp/' . $zip_filename;

    // 8. Schedule cleanup of old temp files (older than 1 hour)
    nk_cleanup_old_bulk_cv_files( $tmp_dir );

    // 9. Log the download for analytics
    nk_log_bulk_cv_download( $employer_id, $candidate_ids );

    wp_send_json_success( [
        'message'      => 'ZIP file ready!',
        'download_url' => $zip_url,
        'count'        => count( $candidate_ids )
    ] );
}

/**
 * Generate a styled HTML CV for a candidate.
 */
function nk_generate_cv_html_for_download( $user_id ) {
    global $wpdb;
    $table_profiles = $wpdb->prefix . 'nk_cv_profiles';
    $table_sections = $wpdb->prefix . 'nk_cv_sections';

    $user = get_userdata( $user_id );

    // Try to get CV from the CV builder database first
    $profile = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM $table_profiles WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1",
        $user_id
    ) );

    $p_data   = [];
    $s_data   = [];
    $e_data   = [];
    $edu_data = [];
    $skl_data = [];

    if ( $profile ) {
        $sections = $wpdb->get_results( $wpdb->prepare(
            "SELECT section_type, section_data FROM $table_sections WHERE profile_id = %d",
            $profile->id
        ) );
        foreach ( $sections as $sec ) {
            $decoded = json_decode( $sec->section_data, true ) ?: [];
            switch ( $sec->section_type ) {
                case 'personal_info': $p_data = $decoded; break;
                case 'summary':       $s_data = $decoded; break;
                case 'experience':    $e_data = $decoded; break;
                case 'education':     $edu_data = $decoded; break;
                case 'skills':        $skl_data = $decoded; break;
            }
        }
    }

    // Fallback to user meta if no CV builder profile
    $name     = ! empty( $p_data['first_name'] ) ? $p_data['first_name'] . ' ' . ($p_data['last_name'] ?? '') : $user->display_name;
    $email    = ! empty( $p_data['email'] ) ? $p_data['email'] : $user->user_email;
    $phone    = ! empty( $p_data['phone'] ) ? $p_data['phone'] : get_user_meta( $user_id, 'nk_phone', true );
    $title    = get_user_meta( $user_id, 'nk_job_title', true ) ?: 'Professional';
    $location = get_user_meta( $user_id, 'nk_location', true ) ?: '';
    $summary  = ! empty( $s_data['summary'] ) ? $s_data['summary'] : get_user_meta( $user_id, 'nk_bio', true );
    $skills   = ! empty( $skl_data ) ? $skl_data : explode( ',', get_user_meta( $user_id, 'nk_cv_skills', true ) ?: get_user_meta( $user_id, 'nk_skills', true ) );

    // Build HTML
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - <?php echo esc_html( $name ); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; line-height: 1.6; padding: 40px; max-width: 800px; margin: 0 auto; }
        .cv-header { border-bottom: 3px solid #0A66C2; padding-bottom: 20px; margin-bottom: 30px; }
        .cv-header h1 { font-size: 28px; color: #0f172a; margin-bottom: 5px; }
        .cv-header .title { font-size: 16px; color: #0A66C2; font-weight: 600; margin-bottom: 10px; }
        .cv-header .contact { font-size: 13px; color: #64748b; }
        .cv-header .contact span { margin-right: 15px; }
        .cv-section { margin-bottom: 25px; }
        .cv-section h2 { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; color: #0A66C2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; }
        .cv-section p { font-size: 14px; color: #475569; }
        .experience-item, .education-item { margin-bottom: 15px; padding-left: 15px; border-left: 2px solid #e2e8f0; }
        .experience-item h3, .education-item h3 { font-size: 15px; color: #0f172a; }
        .experience-item .meta, .education-item .meta { font-size: 12px; color: #64748b; margin-bottom: 5px; }
        .skills-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .skills-list span { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 20px; font-size: 12px; color: #475569; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="cv-header">
        <h1><?php echo esc_html( $name ); ?></h1>
        <div class="title"><?php echo esc_html( $title ); ?></div>
        <div class="contact">
            <?php if ( $email ) : ?><span>📧 <?php echo esc_html( $email ); ?></span><?php endif; ?>
            <?php if ( $phone ) : ?><span>📞 <?php echo esc_html( $phone ); ?></span><?php endif; ?>
            <?php if ( $location ) : ?><span>📍 <?php echo esc_html( $location ); ?></span><?php endif; ?>
        </div>
    </div>

    <?php if ( $summary ) : ?>
    <div class="cv-section">
        <h2>Professional Summary</h2>
        <p><?php echo esc_html( $summary ); ?></p>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $e_data ) ) : ?>
    <div class="cv-section">
        <h2>Work Experience</h2>
        <?php foreach ( $e_data as $exp ) : ?>
        <div class="experience-item">
            <h3><?php echo esc_html( $exp['job_title'] ?? $exp['title'] ?? 'Position' ); ?></h3>
            <div class="meta">
                <?php echo esc_html( $exp['company'] ?? '' ); ?>
                <?php if ( ! empty( $exp['start_date'] ) ) : ?>
                    | <?php echo esc_html( $exp['start_date'] ); ?> - <?php echo esc_html( $exp['end_date'] ?? 'Present' ); ?>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $exp['description'] ) ) : ?>
                <p><?php echo esc_html( $exp['description'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $edu_data ) ) : ?>
    <div class="cv-section">
        <h2>Education</h2>
        <?php foreach ( $edu_data as $edu ) : ?>
        <div class="education-item">
            <h3><?php echo esc_html( $edu['degree'] ?? $edu['title'] ?? 'Degree' ); ?></h3>
            <div class="meta">
                <?php echo esc_html( $edu['institution'] ?? $edu['school'] ?? '' ); ?>
                <?php if ( ! empty( $edu['year'] ) || ! empty( $edu['start_date'] ) ) : ?>
                    | <?php echo esc_html( $edu['year'] ?? $edu['start_date'] ?? '' ); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $skills ) ) : ?>
    <div class="cv-section">
        <h2>Skills</h2>
        <div class="skills-list">
            <?php
            foreach ( $skills as $skill ) {
                $skill_name = is_array( $skill ) ? ( $skill['name'] ?? $skill['skill'] ?? '' ) : trim( $skill );
                if ( $skill_name ) {
                    echo '<span>' . esc_html( $skill_name ) . '</span>';
                }
            }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Generated from NatunKicho Talent Network | <?php echo esc_html( gmdate( 'F j, Y' ) ); ?></p>
    </div>
</body>
</html>
    <?php
    return ob_get_clean();
}

/**
 * Clean up ZIP files older than 1 hour.
 */
function nk_cleanup_old_bulk_cv_files( $dir ) {
    if ( ! is_dir( $dir ) ) return;

    $files = glob( $dir . '*.zip' );
    $now   = time();

    foreach ( $files as $file ) {
        if ( is_file( $file ) && ( $now - filemtime( $file ) ) > 3600 ) {
            wp_delete_file( $file );
        }
    }
}

/**
 * Log bulk CV download for analytics and rate limiting.
 */
function nk_log_bulk_cv_download( $employer_id, $candidate_ids ) {
    $log_key   = 'nk_bulk_cv_log';
    $log_entry = [
        'employer_id'   => $employer_id,
        'candidates'    => $candidate_ids,
        'downloaded_at' => current_time( 'mysql' ),
    ];

    $existing_log = get_option( $log_key, [] );
    $existing_log[] = $log_entry;

    // Keep only last 500 entries
    if ( count( $existing_log ) > 500 ) {
        $existing_log = array_slice( $existing_log, -500 );
    }

    update_option( $log_key, $existing_log, false );
}
