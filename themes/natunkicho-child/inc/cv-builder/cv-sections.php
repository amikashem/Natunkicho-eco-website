<?php
/**
 * NatunKicho AI CV Studio - Database Save Handler
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Hook the AJAX action for logged-in users
add_action( 'wp_ajax_nk_save_cv_data', 'nk_ajax_save_cv_data' );

function nk_ajax_save_cv_data() {
    // 1. Security Check
    check_ajax_referer( 'nk_cv_builder_nonce', 'security' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( [ 'message' => 'Session expired. Please log in again.' ] );
    }

    global $wpdb;
    $table_profiles = $wpdb->prefix . 'nk_cv_profiles';
    $table_sections = $wpdb->prefix . 'nk_cv_sections';

    // 2. Check if the user already has a CV profile
    $profile = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table_profiles WHERE user_id = %d LIMIT 1", $user_id ) );
    
    if ( ! $profile ) {
        $wpdb->insert(
            $table_profiles,
            [
                'user_id'      => $user_id,
                'profile_name' => 'My Professional CV',
                'template_id'  => 'modern',
                'visibility'   => 'private'
            ]
        );
        $profile_id = $wpdb->insert_id;
    } else {
        $profile_id = $profile->id;
    }

    // 3. Gather Static Data (ADDED PHOTO SUPPORT)
    $personal_info = [
        'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
        'last_name'  => sanitize_text_field( $_POST['last_name'] ?? '' ),
        'email'      => sanitize_email( $_POST['email'] ?? '' ),
        'phone'      => sanitize_text_field( $_POST['phone'] ?? '' ),
        'photo'      => sanitize_textarea_field( $_POST['photo_data'] ?? '' ) // Saves image as Base64 string safely
    ];

    $summary_data = [
        'summary' => sanitize_textarea_field( $_POST['summary'] ?? '' )
    ];

    // 4. Gather Dynamic Array Data
    
    // Work Experience
    $experience_data = [];
    if ( isset($_POST['experience']) && is_array($_POST['experience']) ) {
        foreach ( $_POST['experience'] as $exp ) {
            $experience_data[] = [
                'job_title'  => sanitize_text_field( $exp['job_title'] ?? '' ),
                'company'    => sanitize_text_field( $exp['company'] ?? '' ),
                'start_date' => sanitize_text_field( $exp['start_date'] ?? '' ),
                'end_date'   => sanitize_text_field( $exp['end_date'] ?? '' ),
                'details'    => sanitize_textarea_field( $exp['details'] ?? '' ),
            ];
        }
    }

    // Education
    $education_data = [];
    if ( isset($_POST['education']) && is_array($_POST['education']) ) {
        foreach ( $_POST['education'] as $edu ) {
            $education_data[] = [
                'degree'      => sanitize_text_field( $edu['degree'] ?? '' ),
                'institution' => sanitize_text_field( $edu['institution'] ?? '' ),
                'year'        => sanitize_text_field( $edu['year'] ?? '' ),
            ];
        }
    }

    // Skills
    $skills_data = [];
    if ( isset($_POST['skills']) && is_array($_POST['skills']) ) {
        foreach ( $_POST['skills'] as $skill ) {
            $skills_data[] = ['skill_name' => sanitize_text_field( $skill['skill_name'] ?? '' )];
        }
    }

    // Certifications
    $certifications_data = [];
    if ( isset($_POST['certifications']) && is_array($_POST['certifications']) ) {
        foreach ( $_POST['certifications'] as $cert ) {
            $certifications_data[] = [
                'cert_name' => sanitize_text_field( $cert['cert_name'] ?? '' ),
                'year'      => sanitize_text_field( $cert['year'] ?? '' )
            ];
        }
    }

    // Languages
    $languages_data = [];
    if ( isset($_POST['languages']) && is_array($_POST['languages']) ) {
        foreach ( $_POST['languages'] as $lang ) {
            $languages_data[] = [
                'language'    => sanitize_text_field( $lang['language'] ?? '' ),
                'proficiency' => sanitize_text_field( $lang['proficiency'] ?? '' )
            ];
        }
    }

    // References
    $references_data = [];
    if ( isset($_POST['references']) && is_array($_POST['references']) ) {
        foreach ( $_POST['references'] as $ref ) {
            $references_data[] = [
                'name'    => sanitize_text_field( $ref['name'] ?? '' ),
                'company' => sanitize_text_field( $ref['company'] ?? '' ),
                'contact' => sanitize_text_field( $ref['contact'] ?? '' ),
            ];
        }
    }

    // Extra Activities
    $activities_data = [];
    if ( isset($_POST['activities']) && is_array($_POST['activities']) ) {
        foreach ( $_POST['activities'] as $act ) {
            $activities_data[] = [
                'activity_name' => sanitize_text_field( $act['activity_name'] ?? '' ),
                'details'       => sanitize_text_field( $act['details'] ?? '' ),
            ];
        }
    }

    // 5. Save to the Sections table as JSON
    nk_upsert_cv_section( $profile_id, 'personal_info', $personal_info );
    nk_upsert_cv_section( $profile_id, 'summary', $summary_data );
    nk_upsert_cv_section( $profile_id, 'experience', $experience_data );
    nk_upsert_cv_section( $profile_id, 'education', $education_data );
    nk_upsert_cv_section( $profile_id, 'skills', $skills_data );
    nk_upsert_cv_section( $profile_id, 'certifications', $certifications_data );
    nk_upsert_cv_section( $profile_id, 'languages', $languages_data );
    nk_upsert_cv_section( $profile_id, 'references', $references_data );
    nk_upsert_cv_section( $profile_id, 'activities', $activities_data );

    wp_send_json_success( [ 'message' => 'CV Saved Successfully!' ] );
}

// Helper function to dynamically insert or update sections
function nk_upsert_cv_section( $profile_id, $section_type, $data ) {
    global $wpdb;
    $table_sections = $wpdb->prefix . 'nk_cv_sections';
    
    $json_data = wp_json_encode( $data ); 

    $existing = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table_sections WHERE profile_id = %d AND section_type = %s", $profile_id, $section_type ) );

    if ( $existing ) {
        $wpdb->update( $table_sections, [ 'section_data' => $json_data ], [ 'id' => $existing->id ] );
    } else {
        $wpdb->insert( $table_sections, [ 'profile_id' => $profile_id, 'section_type' => $section_type, 'section_data' => $json_data ] );
    }
}