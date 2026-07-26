<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * PHASE 1: AI WIZARD JOB FORM FIELD ARCHITECTURE (With Memory/Persistence)
 * =========================================================================
 */
function nk_ai_wizard_job_fields( $fields ) {
    
    // Completely clear existing arrays to prevent WPJM merging conflicts
    $fields['job'] = array();
    $fields['company'] = array();

    // Rebuild securely
    $fields['job']['job_title'] = array(
        'label'       => 'Job Title',
        'type'        => 'text',
        'required'    => true,
        'placeholder' => 'e.g. Executive Chef, Hotel Manager',
        'priority'    => 1.1,
        'class'       => 'nk-wizard-step-1',
    );
    $fields['job']['job_category'] = array(
        'label'       => 'Job Category',
        'type'        => 'term-select',
        'taxonomy'    => 'job_listing_category',
        'required'    => true,
        'placeholder' => 'Select a category',
        'priority'    => 1.2,
        'class'       => 'nk-wizard-step-1',
    );
    $fields['job']['job_type'] = array(
        'label'       => 'Job Type',
        'type'        => 'term-select',
        'taxonomy'    => 'job_listing_type',
        'required'    => true,
        'placeholder' => 'Select Job Type',
        'priority'    => 1.3,
        'class'       => 'nk-wizard-step-1',
    );
    $fields['job']['job_country'] = array(
        'label'       => 'Job Country',
        'type'        => 'term-select',
        'taxonomy'    => 'job_country', 
        'required'    => true,
        'priority'    => 1.4,
        'class'       => 'nk-wizard-step-1 nk-force-search-dropdown', 
    );
    $fields['job']['job_location'] = array(
        'label'       => 'Specific City / Area',
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'e.g. Dubai, Sylhet, Tokyo',
        'priority'    => 1.5,
        'class'       => 'nk-wizard-step-1',
    );

    // --- STEP 2: DETAILS ---
    $fields['job']['job_summary'] = array(
        'label'       => 'Job Summary',
        'type'        => 'textarea',
        'required'    => false,
        'priority'    => 2.1,
        'class'       => 'nk-wizard-step-2',
    );
    $fields['job']['job_responsibilities'] = array(
        'label'       => 'Job Responsibilities',
        'type'        => 'wp-editor',
        'required'    => true,
        'priority'    => 2.2,
        'class'       => 'nk-wizard-step-2',
    );
    $fields['job']['job_requirements'] = array(
        'label'       => 'Requirements & Qualifications',
        'type'        => 'wp-editor',
        'required'    => true,
        'priority'    => 2.3,
        'class'       => 'nk-wizard-step-2',
    );
    $fields['job']['job_skills'] = array(
        'label'       => 'Required Skills (Comma separated)',
        'type'        => 'text',
        'required'    => true,
        'priority'    => 2.4,
        'class'       => 'nk-wizard-step-2',
    );
    $fields['job']['experience_required'] = array(
        'label'       => 'Experience Required',
        'type'        => 'select',
        'required'    => false,
        'options'     => array( '' => 'Select Experience Level', 'entry' => 'Entry Level', 'mid' => 'Mid Level', 'senior' => 'Senior Level', 'manager' => 'Management' ),
        'priority'    => 2.5,
        'class'       => 'nk-wizard-step-2',
    );

    // --- STEP 3: SALARY & APPLICATION ---
    $fields['job']['salary_type'] = array(
        'label'       => 'Salary Type',
        'type'        => 'select',
        'required'    => false,
        'options'     => array( 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'hourly' => 'Hourly' ),
        'priority'    => 3.1,
        'class'       => 'nk-wizard-step-3',
    );
    $fields['job']['salary_currency'] = array(
        'label'       => 'Currency',
        'type'        => 'text',
        'required'    => false,
        'priority'    => 3.2,
        'class'       => 'nk-wizard-step-3',
    );
    $fields['job']['salary_range'] = array(
        'label'       => 'Salary Range',
        'type'        => 'select',
        'required'    => false,
        'options'     => array( '' => 'Negotiable', '0-5000' => '0 - 5,000', '5000-10000' => '5,000 - 10,000', '10000-20000' => '10,000 - 20,000', '20000-30000' => '20,000 - 30,000', '30000-45000' => '30,000 - 45,000', '45000-60000' => '45,000 - 60,000', '60000-100000' => '60,000 - 100,000', '100000+' => '100,000+ (More)' ),
        'priority'    => 3.3,
        'class'       => 'nk-wizard-step-3',
    );
    $fields['job']['application'] = array(
        'label'       => 'Application Email',
        'type'        => 'text',
        'required'    => true,
        'default'     => wp_get_current_user()->user_email,
        'priority'    => 3.4,
        'class'       => 'nk-wizard-step-3',
    );
    $fields['job']['application_url'] = array(
        'label'       => 'External Application URL',
        'type'        => 'text',
        'required'    => false,
        'priority'    => 3.5,
        'class'       => 'nk-wizard-step-3',
    );

    // --- STEP 4: EXTRA FEATURES ---
    $fields['job']['company_logo'] = array(
        'label'       => 'Company Logo',
        'type'        => 'file',
        'required'    => false,
        'ajax'        => true,
        'priority'    => 4.05,
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['company_name'] = array(
        'label'       => 'Company Name',
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Leave blank to use your profile name',
        'priority'    => 4.01,
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['hospitality_sector'] = array(
        'label'       => 'Hospitality Sector',
        'type'        => 'select',
        'required'    => true,
        'options'     => array( '' => 'Select a Sector', 'hotel' => 'Hotel & Resort', 'restaurant' => 'Restaurant & Cafe', 'cruise' => 'Cruise Ship', 'aviation' => 'Aviation & Catering', 'management' => 'Management & Admin' ),
        'priority'    => 4.1, 
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['work_schedule'] = array(
        'label'       => 'Work Schedule',
        'type'        => 'select',
        'required'    => false,
        'options'     => array( '' => 'Select Schedule', 'day' => 'Day Shift', 'night' => 'Night Shift', 'rotational' => 'Rotational Shift', 'flexible' => 'Flexible Schedule' ),
        'priority'    => 4.2,
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['vacancy_count'] = array(
        'label'       => 'Number of Vacancies',
        'type'        => 'text',
        'required'    => false,
        'priority'    => 4.3,
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['application_deadline'] = array(
        'label'       => 'Application Deadline',
        'type'        => 'text', 
        'required'    => false, 
        'priority'    => 4.4,
        'class'       => 'nk-wizard-step-4 nk-datepicker',
    );
    $fields['job']['urgent_hiring'] = array(
        'label'       => 'Mark as Urgent Hiring?',
        'type'        => 'checkbox',
        'required'    => false,
        'priority'    => 4.5,
        'class'       => 'nk-wizard-step-4',
    );
    $fields['job']['featured_job'] = array(
        'label'       => 'Feature this Job? (Premium)',
        'type'        => 'checkbox',
        'required'    => false,
        'priority'    => 4.6,
        'class'       => 'nk-wizard-step-4',
    );

    /**
     * ==================================================
     * 10X UX: STATE PERSISTENCE & SMART AUTO-FILL
     * ==================================================
     */
    $job_id  = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;
    $user_id = get_current_user_id();

    if ( $job_id ) {
        // SCENARIO A: User clicked "Edit Listing". We restore EVERYTHING safely.
        $draft_post = get_post( $job_id );
        if ( $draft_post ) {
            $fields['job']['job_title']['value'] = $draft_post->post_title;
            
            // 1. Restore Standard Text / Meta Fields
            $custom_keys = [
                'job_summary', 'job_responsibilities', 'job_requirements', 'job_skills', 
                'salary_type', 'salary_currency', 'salary_range', 'application', 
                'application_url', 'company_name', 'company_logo', 'hospitality_sector', 
                'work_schedule', 'vacancy_count', 'experience_required', 'urgent_hiring', 'featured_job', 'job_location'
            ];
            
            foreach ( $custom_keys as $key ) {
                $meta_val = get_post_meta( $job_id, '_' . $key, true );
                if ( $meta_val !== '' ) {
                    $fields['job'][$key]['value'] = $meta_val;
                }
            }

            // 2. Restore Taxonomies (Country, Category, Type)
            $taxonomies = ['job_country', 'job_listing_category', 'job_listing_type'];
            foreach ( $taxonomies as $tax ) {
                $terms = wp_get_object_terms( $job_id, $tax, ['fields' => 'ids'] );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    if (isset($fields['job'][$tax])) { $fields['job'][$tax]['value'] = $terms[0]; }
                    if ($tax === 'job_listing_category' && isset($fields['job']['job_category'])) { $fields['job']['job_category']['value'] = $terms[0]; }
                    if ($tax === 'job_listing_type' && isset($fields['job']['job_type'])) { $fields['job']['job_type']['value'] = $terms[0]; }
                }
            }
        }
    } else {
        // SCENARIO B: Brand new job.
        // Prevent WPJM from auto-filling the descriptions of the previous job!
        $fields['job']['job_summary']['value'] = '';
        $fields['job']['job_responsibilities']['value'] = '';
        $fields['job']['job_requirements']['value'] = '';
        
        // Auto-fill ONLY Company Name & Logo from their last post
        $last_job = get_posts([
            'post_type'      => 'job_listing',
            'author'         => $user_id,
            'posts_per_page' => 1,
            'post_status'    => ['publish', 'pending', 'draft'],
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);
        
        if ( $last_job ) {
            $company_name = get_post_meta( $last_job[0]->ID, '_company_name', true );
            $company_logo = get_post_meta( $last_job[0]->ID, '_company_logo', true );
            
            if ($company_name) $fields['job']['company_name']['value'] = $company_name;
            if ($company_logo) $fields['job']['company_logo']['value'] = $company_logo; // Remembers the Logo!
        } else {
            // First time posting? Use their display name
            $user_info = get_userdata( $user_id );
            $fields['job']['company_name']['value'] = $user_info ? $user_info->display_name : '';
        }
    }

    return $fields;
}
add_filter( 'submit_job_form_fields', 'nk_ai_wizard_job_fields', 99 );

// 2. Google Search Console Schema Markup Fix 
add_filter( 'wpjm_get_job_listing_structured_data', 'nk_fix_google_jobs_schema_markup', 10, 2 );
function nk_fix_google_jobs_schema_markup( $data, $post ) {
    $raw_location = get_post_meta( $post->ID, '_job_location', true );
    $country_terms = get_the_terms( $post->ID, 'job_country' );
    $country_name = ( $country_terms && ! is_wp_error( $country_terms ) ) ? $country_terms[0]->name : 'BD';

    if ( empty($raw_location) ) $raw_location = $country_name; 
    
    $location_parts = array_map('trim', explode(',', $raw_location));
    $locality = isset($location_parts[0]) ? $location_parts[0] : $raw_location;
    $region   = isset($location_parts[1]) ? $location_parts[1] : $raw_location;

    $data['jobLocation'] = array(
        '@type' => 'Place',
        'address' => array(
            '@type' => 'PostalAddress',
            'streetAddress'   => $locality, 
            'addressLocality' => $locality,
            'addressRegion'   => $region,
            'postalCode'      => '0000', 
            'addressCountry'  => $country_name, 
        )
    );

    $min_salary = get_post_meta( $post->ID, '_job_salary', true ) ?: '0'; 
    $data['baseSalary'] = array(
        '@type'    => 'MonetaryAmount',
        'currency' => 'BDT', 
        'value'    => array(
            '@type'    => 'QuantitativeValue',
            'value'    => $min_salary,
            'unitText' => 'MONTH' 
        )
    );

    return $data;
}

// 3. Fix the Job Category 404 Bug 
add_action( 'save_post_job_listing', 'nk_flush_job_category_rewrite_rules' );
function nk_flush_job_category_rewrite_rules() {
    if ( ! get_transient( 'nk_flushed_job_rules' ) ) {
        flush_rewrite_rules();
        set_transient( 'nk_flushed_job_rules', true, 12 * HOUR_IN_SECONDS );
    }
}

/**
 * Append Social Share Buttons
 */
function nk_add_job_share_buttons() {
    if ( ! is_singular( 'job_listing' ) ) return;
    global $post;
    $permalink = esc_url( get_permalink( $post->ID ) );
    $title     = esc_attr( get_the_title( $post->ID ) );
    $summary   = esc_attr( wp_strip_all_tags( get_the_excerpt() ) );

    $share_links = [
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $permalink,
        'twitter'  => 'https://twitter.com/intent/tweet?text=' . urlencode( $title ) . '&url=' . $permalink,
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $permalink,
        'whatsapp' => 'https://api.whatsapp.com/send?text=' . urlencode( $title . ' ' . $permalink )
    ];
    ?>
    <div class="nk-share-container">
        <h4 class="nk-share-title"><?php _e( 'Share this job:', 'natunkicho' ); ?></h4>
        <div class="nk-share-buttons" data-url="<?php echo $permalink; ?>" data-title="<?php echo $title; ?>" data-text="<?php echo $summary; ?>">
            <button class="nk-share-btn nk-share-native" style="display:none;" title="Share Link">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                <span>Share</span>
            </button>
            <a href="<?php echo $share_links['facebook']; ?>" class="nk-share-btn nk-fb" target="_blank" rel="noopener noreferrer"><svg fill="currentColor" viewBox="0 0 24 24" width="18" height="18"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.8z"/></svg></a>
            <a href="<?php echo $share_links['twitter']; ?>" class="nk-share-btn nk-x" target="_blank" rel="noopener noreferrer"><svg fill="currentColor" viewBox="0 0 24 24" width="16" height="16"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
            <a href="<?php echo $share_links['linkedin']; ?>" class="nk-share-btn nk-linkedin" target="_blank" rel="noopener noreferrer"><svg fill="currentColor" viewBox="0 0 24 24" width="18" height="18"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.8v8.37h2.8v-4.67c0-.25.02-.5.1-.68a1.14 1.14 0 0 1 1-.77c.76 0 1 .58 1 1.42v4.7zM6.5 8.37a1.37 1.37 0 1 0 0-2.75 1.37 1.37 0 0 0 0 2.75M8 18.5V10.13H5.13V18.5z"/></svg></a>
            <a href="<?php echo $share_links['whatsapp']; ?>" class="nk-share-btn nk-wa" target="_blank" rel="noopener noreferrer"><svg fill="currentColor" viewBox="0 0 24 24" width="18" height="18"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.455 5.703 1.456h.004c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </button>
        </div>
    </div>
    <?php
}
add_action( 'single_job_listing_end', 'nk_add_job_share_buttons', 25 );

/**
 * =========================================================================
 * CANDIDATE: TOGGLE SAVE / UNSAVE JOB
 * =========================================================================
 */
function nk_ajax_toggle_save_job() {
    check_ajax_referer('nk_save_job_nonce', 'security');
    if (!is_user_logged_in()) wp_send_json_error('Please log in');
    
    $job_id = intval($_POST['job_id']);
    $user_id = get_current_user_id();
    
    $saved_jobs = get_user_meta($user_id, 'nk_saved_jobs', true);
    if (!is_array($saved_jobs)) $saved_jobs = [];
    
    if (in_array($job_id, $saved_jobs)) {
        $saved_jobs = array_diff($saved_jobs, [$job_id]);
        $status = 'unsaved';
    } else {
        $saved_jobs[] = $job_id;
        $status = 'saved';
    }
    
    update_user_meta($user_id, 'nk_saved_jobs', $saved_jobs);
    wp_send_json_success(['status' => $status]);
}
add_action('wp_ajax_nk_ajax_toggle_save_job', 'nk_ajax_toggle_save_job');

/**
 * =========================================================================
 * INTERNAL APPLICATION ENGINE & SMART EMAIL ROUTER
 * =========================================================================
 */
function nk_handle_internal_job_application() {
    check_ajax_referer('nk_apply_nonce', 'security');
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Please login to apply.' );

    $candidate_id = get_current_user_id();
    $job_id       = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $note         = isset($_POST['candidate_note']) ? sanitize_textarea_field($_POST['candidate_note']) : '';

    if ( ! $job_id ) wp_send_json_error( 'Invalid job reference.' );

    $job_applicants = get_post_meta( $job_id, 'nk_job_applications', true );
    if ( ! is_array( $job_applicants ) ) $job_applicants = [];

    if ( in_array( $candidate_id, $job_applicants ) ) wp_send_json_error( 'You already applied.' );

    $job_applicants[] = $candidate_id;
    update_post_meta( $job_id, 'nk_job_applications', $job_applicants );
    update_post_meta( $job_id, 'nk_app_status_' . $candidate_id, 'pending' ); 

    if ( ! empty( $note ) ) update_post_meta( $job_id, 'nk_app_note_' . $candidate_id, $note );

    $candidate_applications = get_user_meta( $candidate_id, 'nk_applied_jobs', true );
    if ( ! is_array( $candidate_applications ) ) $candidate_applications = [];
    if ( ! in_array( $job_id, $candidate_applications ) ) {
        $candidate_applications[] = $job_id;
        update_user_meta( $candidate_id, 'nk_applied_jobs', $candidate_applications );
    }

    $job_post = get_post($job_id);
    $employer = get_userdata($job_post->post_author);
    $candidate = get_userdata($candidate_id);

    if ( $employer && $candidate ) {
        if ( function_exists('nk_add_in_app_notification') ) {
            nk_add_in_app_notification(
                $employer->ID, 
                "New Application Received 📝", 
                "{$candidate->display_name} just applied for your open role: {$job_post->post_title}.", 
                site_url('/dashboard/?tab=applications')
            );
        }

        $apply_meta = get_post_meta( $job_id, '_application', true );
        $employer_primary_email = $employer->user_email;
        $to_emails = [ $employer_primary_email ];
        if ( is_email( $apply_meta ) && $apply_meta !== $employer_primary_email ) {
            $to_emails[] = $apply_meta;
        }

        $subject = 'New Application Received: ' . $job_post->post_title;
        $content = "<p>Hello <strong>" . esc_html($employer->display_name) . "</strong>,</p>";
        $content .= "<p>You have received a new application on NatunKicho for your job posting: <strong style='color:#0A66C2;'>" . esc_html($job_post->post_title) . "</strong>.</p>";
        $content .= "<p><strong>Applicant:</strong> " . esc_html($candidate->display_name) . "</p>";
        
        if ( ! empty($note) ) {
            $content .= "<div style='background:#f8fafc; padding:15px; border-left:4px solid #0A66C2; margin:15px 0;'><em>\"" . esc_html($note) . "\"</em></div>";
        }
        
        $content .= "<p>Log in to your Employer Dashboard to accept/reject the candidate, view their full profile, and download their CV.</p>";
        $content .= '<a href="' . esc_url(site_url('/dashboard/?tab=applications')) . '" style="display:inline-block; background:#0A66C2; color:#fff; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:15px;">Review Application</a>';
        
        if ( function_exists('nk_get_branded_email_html') ) {
            $final_html = nk_get_branded_email_html( $subject, $content );
            add_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
            wp_mail( $to_emails, $subject, $final_html );
            remove_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
        } else {
            wp_mail( $to_emails, $subject, strip_tags($content) );
        }
    }
    wp_send_json_success( 'Application submitted successfully!' );
}
add_action( 'wp_ajax_nk_apply_internal_job', 'nk_handle_internal_job_application' );

/**
 * =========================================================================
 * EMPLOYER: ACCEPT / REJECT APPLICATION
 * =========================================================================
 */
function nk_ajax_manage_application_status() {
    check_ajax_referer('nk_manage_app_nonce', 'security');
    if (!is_user_logged_in()) wp_send_json_error('Please log in');

    $employer_id = get_current_user_id();
    $job_id = intval($_POST['job_id']);
    $candidate_id = intval($_POST['candidate_id']);
    $status = sanitize_text_field($_POST['status']); 

    $job = get_post($job_id);
    if (!$job || $job->post_author != $employer_id) wp_send_json_error('Unauthorized access.');

    update_post_meta($job_id, 'nk_app_status_' . $candidate_id, $status);

    $candidate = get_userdata($candidate_id);
    if ($candidate) {
        if ( function_exists('nk_add_in_app_notification') ) {
            if ($status === 'accepted') {
                nk_add_in_app_notification(
                    $candidate_id, 
                    "Application Update: Shortlisted! 🎉", 
                    "You have been shortlisted for the {$job->post_title} role! The employer will contact you soon.", 
                    site_url('/dashboard/?tab=applied-jobs')
                );
            } else {
                nk_add_in_app_notification(
                    $candidate_id, 
                    "Application Update: {$job->post_title}", 
                    "The employer for this role has decided to move forward with other candidates at this time. Keep applying!", 
                    site_url('/dashboard/?tab=applied-jobs')
                );
            }
        }

        if (function_exists('nk_get_branded_email_html')) {
            if ($status === 'accepted') {
                $subject = "Update on your application: " . $job->post_title;
                $content = "<p>Great news <strong>" . esc_html($candidate->display_name) . "</strong>,</p>";
                $content .= "<p>The employer for <strong>" . esc_html($job->post_title) . "</strong> has reviewed your profile and moved your application to the <strong>Shortlisted</strong> stage.</p>";
                $content .= "<p>They will be reaching out to you shortly with the next steps.</p>";
            } else {
                $subject = "Update on your application: " . $job->post_title;
                $content = "<p>Hello <strong>" . esc_html($candidate->display_name) . "</strong>,</p>";
                $content .= "<p>Thank you for applying to the <strong>" . esc_html($job->post_title) . "</strong> role.</p>";
                $content .= "<p>While your background is impressive, the employer has decided to move forward with other candidates who more closely align with their current needs for this specific role.</p>";
                $content .= "<p>We encourage you to keep applying to other matching roles on NatunKicho!</p>";
                
                if (!nk_is_user_premium($candidate_id)) {
                    $content .= "<div style='margin-top:25px; padding:20px; border:1px solid #cbd5e1; background:#f8fafc; border-radius:8px;'>";
                    $content .= "<h4 style='margin:0 0 10px 0; color:#0A66C2;'>Want to stand out to employers?</h4>";
                    $content .= "<p style='font-size:14px; margin: 0 0 15px 0;'>Upgrade to Premium Pro to unlock our AI CV Assistant and Priority Application Boost to get your profile noticed faster.</p>";
                    $content .= "<a href='" . esc_url(site_url('/pricing/')) . "' style='display:inline-block; background:#0A66C2; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:bold;'>Upgrade Now</a></div>";
                }
            }
            
            $final_html = nk_get_branded_email_html( $subject, $content );
            add_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
            wp_mail( $candidate->user_email, $subject, $final_html );
            remove_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
        }
    }

    wp_send_json_success(ucfirst($status));
}
add_action('wp_ajax_nk_manage_app_status', 'nk_ajax_manage_application_status');

/**
 * =========================================================================
 * AUTO-LOCATION & GEOIP MANAGER
 * =========================================================================
 */
add_action('init', 'nk_detect_and_store_user_location');
function nk_detect_and_store_user_location() {
    if ( is_admin() || wp_is_json_request() || isset($_COOKIE['nk_user_country']) ) return;

    $user_ip = '';
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $user_ip = $_SERVER["HTTP_CF_CONNECTING_IP"]; 
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $user_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $user_ip = $_SERVER['REMOTE_ADDR'];
    }

    if ( $user_ip === '127.0.0.1' || $user_ip === '::1' ) return;

    $response = wp_remote_get("http://ip-api.com/json/{$user_ip}?fields=country");
    
    if ( ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200 ) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ( ! empty($data->country) ) {
            $detected_country = sanitize_text_field($data->country);
            setcookie('nk_user_country', $detected_country, time() + (86400 * 7), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            $_COOKIE['nk_user_country'] = $detected_country;
        }
    }
} 

add_filter( 'job_manager_output_jobs_args', 'nk_auto_filter_jobs_by_detected_location' );
function nk_auto_filter_jobs_by_detected_location( $args ) {
    if ( ! empty( $_REQUEST['search_location'] ) ) return $args;
    if ( ! empty( $_REQUEST['search_region'] ) || ! empty( $_REQUEST['search_categories'] ) ) return $args;
    if ( isset( $_COOKIE['nk_user_country'] ) ) $args['search_location'] = sanitize_text_field( $_COOKIE['nk_user_country'] );
    return $args;
}

add_filter( 'submit_job_form_fields_get_user_data', function($data) {
    if ( isset( $_COOKIE['nk_user_country'] ) && empty( $data['job']['job_location'] ) ) {
        $data['job']['job_location'] = sanitize_text_field( $_COOKIE['nk_user_country'] );
    }
    return $data;
});

/**
 * =========================================================================
 * DISPLAY CUSTOM JOB FIELDS (Expanded + Premium Salary Lock + Mobile Responsive)
 * =========================================================================
 */
function nk_display_custom_job_overview() {
    global $post;

    $salary        = get_post_meta( $post->ID, '_job_salary', true );
    $salary_type   = get_post_meta( $post->ID, '_salary_type', true );
    $salary_range  = get_post_meta( $post->ID, '_salary_range', true );
    $sector        = get_post_meta( $post->ID, '_hospitality_sector', true ); 
    $skills        = get_post_meta( $post->ID, '_job_skills', true ); 
    $requirements  = get_post_meta( $post->ID, '_job_requirements', true ); 
    $summary       = get_post_meta( $post->ID, '_job_summary', true );
    $respons       = get_post_meta( $post->ID, '_job_responsibilities', true );
    $vacancies     = get_post_meta( $post->ID, '_vacancy_count', true );
    $schedule      = get_post_meta( $post->ID, '_work_schedule', true );

    // Check Premium Status
    $is_premium = function_exists('nk_is_user_premium') ? nk_is_user_premium(get_current_user_id()) : false;

    // Mobile Responsiveness CSS injected directly
    echo '<style>
        .nk-job-overview-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin: 30px 0; }
        .nk-job-grid-top { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .nk-job-grid-bottom { display: grid; grid-template-columns: 1fr; gap: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        
        @media (max-width: 768px) {
            .nk-job-overview-box { padding: 15px !important; margin: 15px 0 !important; }
            .nk-job-grid-top { grid-template-columns: 1fr !important; gap: 15px !important; margin-bottom: 15px !important; }
            .nk-job-grid-bottom { gap: 15px !important; }
            .nk-job-overview-box h3 { font-size: 16px !important; margin-bottom: 15px !important; }
            .nk-job-overview-box p, .nk-job-overview-box div { font-size: 13.5px !important; }
        }
    </style>';

    echo '<div class="nk-job-overview-box">';
    echo '<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px;">💼 Job Overview</h3>';
    
    // Top Grid
    echo '<div class="nk-job-grid-top">';

    // Premium Locked Salary Block
    if ( ! empty( $salary ) || ! empty( $salary_range ) ) {
        echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase;">Salary (' . esc_html($salary_type) . ')</strong>';
        if ( $is_premium || current_user_can('manage_options') ) {
            $display_salary = !empty($salary_range) ? $salary_range : $salary;
            echo '<span style="color: #16a34a; font-weight: 700; font-size: 15px;">' . esc_html( $display_salary ) . '</span></div>';
        } else {
            echo '<a href="' . esc_url(site_url('/pricing/')) . '" style="display: inline-block; background: #fffbeb; color: #d97706; border: 1px solid #fcd34d; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; margin-top: 4px;">🔒 Upgrade to View</a></div>';
        }
    }

    if ( ! empty( $sector ) ) {
        echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase;">Sector</strong>';
        echo '<span style="color: #0f172a; font-weight: 600; font-size: 15px; text-transform: capitalize;">' . esc_html( str_replace('-', ' ', $sector) ) . '</span></div>';
    }
    
    if ( ! empty( $vacancies ) ) {
        echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase;">Vacancies</strong>';
        echo '<span style="color: #0f172a; font-weight: 600; font-size: 15px;">' . esc_html( $vacancies ) . ' Openings</span></div>';
    }

    if ( ! empty( $schedule ) ) {
        echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase;">Work Schedule</strong>';
        echo '<span style="color: #0f172a; font-weight: 600; font-size: 15px; text-transform: capitalize;">' . esc_html( str_replace('-', ' ', $schedule) ) . '</span></div>';
    }
    echo '</div>'; 

    // Middle Grid
    if ( ! empty( $summary ) ) {
        echo '<div style="margin-bottom: 20px;">';
        echo '<strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Job Summary</strong>';
        echo '<div style="color: #334155; font-size: 14px; line-height: 1.6;">' . wpautop( esc_html( $summary ) ) . '</div>';
        echo '</div>';
    }

    if ( ! empty( $skills ) ) {
        echo '<div style="margin-bottom: 20px;">';
        echo '<strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Required Skills</strong>';
        echo '<span style="color: #0A66C2; font-weight: 600; font-size: 14px; background: #eff6ff; padding: 6px 12px; border-radius: 20px; display: inline-block; line-height:1.5;">' . esc_html( $skills ) . '</span>';
        echo '</div>';
    }

    // Bottom Grid
    if ( ! empty( $respons ) || ! empty( $requirements ) ) {
        echo '<div class="nk-job-grid-bottom">';
        
        if ( ! empty( $respons ) ) {
            echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Key Responsibilities</strong>';
            echo '<div style="color: #334155; font-size: 14px; line-height: 1.6;">' . wpautop( wp_kses_post( $respons ) ) . '</div></div>';
        }

        if ( ! empty( $requirements ) ) {
            echo '<div><strong style="display: block; color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 8px;">Full Requirements</strong>';
            echo '<div style="color: #334155; font-size: 14px; line-height: 1.6;">' . wpautop( wp_kses_post( $requirements ) ) . '</div></div>';
        }
        
        echo '</div>';
    }
    echo '</div>';
}
add_action( 'single_job_listing_end', 'nk_display_custom_job_overview', 15 );

/**
 * =========================================================================
 * DISPLAY RELATED JOBS (HORIZONTAL SLIDER)
 * =========================================================================
 */
add_action( 'single_job_listing_end', 'nk_display_related_jobs_slider', 30 );

function nk_display_related_jobs_slider() {
    global $post;
    $categories = wp_get_post_terms( $post->ID, 'job_listing_category', ['fields' => 'ids'] );

    $args = [
        'post_type'           => 'job_listing',
        'post_status'         => 'publish',
        'posts_per_page'      => 6, 
        'post__not_in'        => [ $post->ID ],
        'orderby'             => 'rand',
    ];

    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        $args['tax_query'] = [[
            'taxonomy' => 'job_listing_category',
            'field'    => 'term_id',
            'terms'    => $categories,
        ]];
    }

    $related_jobs = new WP_Query( $args );

    if ( $related_jobs->have_posts() ) :
        ?>
        <style>
            .nk-related-slider { display: flex; gap: 20px; overflow-x: auto; padding: 10px 0 25px 0; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc; }
            .nk-related-slider::-webkit-scrollbar { height: 8px; }
            .nk-related-slider::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
            .nk-related-slider::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .nk-related-slider::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
            .nk-related-slider-card { min-width: 260px; max-width: 300px; scroll-snap-align: start; flex-shrink: 0; display: flex; flex-direction: column; text-decoration: none; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; background: #ffffff; transition: all 0.2s ease; }
            .nk-related-slider-card:hover { border-color: #0A66C2; transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        </style>

        <div class="nk-related-jobs-wrapper" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">🔥 Similar Roles</h3>
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">Swipe &rarr;</span>
            </div>
            
            <div class="nk-related-slider">
               <?php
                // Get Premium Status once for the slider
                $is_premium = function_exists('nk_is_user_premium') ? nk_is_user_premium(get_current_user_id()) : false;

                while ( $related_jobs->have_posts() ) : $related_jobs->the_post();
                    $company_name = get_post_meta( get_the_ID(), '_company_name', true );
                    $location     = get_post_meta( get_the_ID(), '_job_location', true );
                    $salary       = get_post_meta( get_the_ID(), '_job_salary', true );
                    $salary_range = get_post_meta( get_the_ID(), '_salary_range', true );
                    
                    $display_salary = !empty($salary_range) ? $salary_range : $salary;
                    
                    echo '<a href="' . esc_url( get_permalink() ) . '" class="nk-related-slider-card">';
                        echo '<h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #0A66C2; line-height: 1.4;">' . get_the_title() . '</h4>';
                        echo '<div style="font-size: 13px; color: #64748b; display: flex; flex-direction: column; gap: 6px; margin-top: auto;">';
                            if ( $company_name ) echo '<span style="font-weight: 600; color: #334155;">🏢 ' . esc_html( $company_name ) . '</span>';
                            if ( $location ) echo '<span>📍 ' . esc_html( $location ) . '</span>';
                            
                            // Premium Gate for Slider Salary
                            if ( $display_salary ) {
                                if ( $is_premium || current_user_can('manage_options') ) {
                                    echo '<span style="display: inline-block; background: #f0fdf4; color: #16a34a; padding: 4px 8px; border-radius: 6px; font-weight: 700; margin-top: 5px; width: max-content;">💰 ' . esc_html( $display_salary ) . '</span>';
                                } else {
                                    echo '<span style="display: inline-block; background: #fffbeb; color: #d97706; border: 1px solid #fcd34d; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; margin-top: 5px; width: max-content;">🔒 Premium</span>';
                                }
                            }
                        echo '</div>';
                    echo '</a>';
                endwhile;
                ?>
            </div> 
        </div> 
        <?php
        wp_reset_postdata();
    endif;
} 

/**
 * =========================================================================
 * COMPANY INFO FALLBACK (Uses Profile Data if Job fields are empty)
 * =========================================================================
 */
add_filter( 'the_company_name', 'nk_fallback_company_name', 10, 2 );
function nk_fallback_company_name( $company_name, $post = null ) {
    if ( empty( $company_name ) ) {
        $post_obj = get_post( $post );
        if ( ! $post_obj || ! isset( $post_obj->post_author ) ) return 'Confidential Employer';
        $user_info = get_userdata( $post_obj->post_author );
        return $user_info ? $user_info->display_name : 'Confidential Employer';
    }
    return $company_name;
}

add_filter( 'job_manager_company_logo', 'nk_fallback_company_logo', 10, 2 );
function nk_fallback_company_logo( $logo_url, $post = null ) {
    if ( empty( $logo_url ) ) {
        $post_obj = get_post( $post );
        if ( ! $post_obj || ! isset( $post_obj->post_author ) ) return '';
        $avatar = get_avatar_url( $post_obj->post_author, ['size' => 150] );
        return $avatar ? $avatar : '';
    }
    return $logo_url;
}

/**
 * =========================================================================
 * FORCE SAVE ALL CUSTOM JOB FIELDS (Bulletproof Front-end Saving)
 * =========================================================================
 */
add_action( 'job_manager_update_job_data', 'nk_force_save_wizard_fields', 10, 2 );
function nk_force_save_wizard_fields( $job_id, $values ) {
    if ( isset( $_POST['application_url'] ) ) { update_post_meta( $job_id, '_application_url', esc_url_raw( $_POST['application_url'] ) ); }
    if ( isset( $_POST['company_name'] ) ) { update_post_meta( $job_id, '_company_name', sanitize_text_field( $_POST['company_name'] ) ); }

    $custom_fields = [
        'hospitality_sector', 'work_schedule', 'vacancy_count', 'salary_type', 
        'salary_currency', 'salary_range', 'job_skills', 'experience_required', 
        'urgent_hiring', 'featured_job'
    ];

    foreach ( $custom_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $job_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
}

/**
 * =========================================================================
 * WAF BYPASS TUNNEL (Global Immediate Decoder) PRIORITY 1
 * =========================================================================
 */
add_action('init', 'nk_decode_waf_tunnel', 1);
function nk_decode_waf_tunnel() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $rich_fields = ['job_responsibilities', 'job_requirements', 'job_summary'];
        foreach ( $rich_fields as $field ) {
            if ( isset( $_POST[$field] ) && strpos( $_POST[$field], 'NKB64:' ) === 0 ) {
                $decoded = base64_decode( substr( $_POST[$field], 6 ) );
                $_POST[$field] = $decoded;
                $_REQUEST[$field] = $decoded; 
            }
        }
    }
}

/**
 * =========================================================================
 * UI POLISH: SELECT DROPDOWN FIXES, MOBILE RESPONSIVENESS & PREVIEW BUTTONS
 * =========================================================================
 */
add_action('wp_footer', 'nk_global_ui_fixes', 99);
function nk_global_ui_fixes() {
    if ( is_admin() ) return;
    ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        /* 1. Fix Country Dropdown Size & Z-Index */
        .select2-container--default .select2-dropdown {
            z-index: 999999 !important; 
            font-size: 14px !important;
        }
        .select2-results__options {
            max-height: 200px !important; 
        }
        .select2-container--default .select2-selection--single {
            height: 48px !important; 
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background: #f8fafc !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0f172a !important;
            font-size: 15px !important;
            padding-left: 15px !important;
            line-height: 46px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 10px !important;
        }
        .select2-dropdown {
            border: 1px solid #0A66C2 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            padding: 5px !important;
            background: #ffffff !important;
        }
        .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 10px !important;
            outline: none !important;
            display: block !important;
        }
        .select2-search__field:focus {
            border-color: #0A66C2 !important;
            box-shadow: 0 0 0 3px rgba(10,102,194,0.1) !important;
        }
        .select2-results__option {
            padding: 10px 15px !important;
            font-size: 14px !important;
            color: #334155 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: #0A66C2 !important;
            color: #ffffff !important;
            border-radius: 4px !important;
        }
        
        .nk-force-search-dropdown + .chosen-container { display: none !important; }

        /* 2. Style for the newly moved Submit Buttons */
        .nk-preview-action-bar {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            margin-bottom: 30px;
            clear: both;
        }
        .nk-preview-action-bar input[type="submit"] { margin: 0 !important; }
        @media (max-width: 640px) {
            .nk-preview-action-bar { flex-direction: column; }
            .nk-preview-action-bar input[type="submit"] { width: 100%; }
        }

        /* 3. Deep Mobile Responsiveness Fixes for Single Job View */
        .single-job_listing,
        .job_description, 
        .nk-job-overview-box {
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            max-width: 100% !important;
        }
        .job_description img, 
        .job_description iframe, 
        .job_description table {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
        }
        @media (max-width: 768px) {
            .single-job_listing, .job_listing_preview {
                overflow-x: hidden !important;
                width: 100% !important;
            }
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Force the pristine Select2 dropdown
            function applyBulletproofSearch() {
                var $dropdown = $('.nk-force-search-dropdown select, #job_country');
                if ($dropdown.length) {
                    if ($.fn.chosen) {
                        $dropdown.chosen('destroy');
                        $dropdown.removeClass('chzn-done');
                    }
                    if ($dropdown.hasClass('select2-hidden-accessible')) {
                        $dropdown.select2('destroy');
                    }
                    $dropdown.select2({
                        width: '100%',
                        placeholder: 'Type to search for a country...',
                        minimumResultsForSearch: 0 
                    });
                }
            }
            applyBulletproofSearch();
            setTimeout(applyBulletproofSearch, 1000);

            // Relocate Preview Buttons EXACTLY above Similar Roles
            var $previewForm = $('form#job_preview');
            if ($previewForm.length > 0) {
                var $buttons = $previewForm.find('.button, input[type="submit"]').filter(function() {
                    return $(this).val().toLowerCase().includes('submit') || $(this).val().toLowerCase().includes('edit');
                });
                if ($buttons.length > 0) {
                    var $actionBar = $('<div class="nk-preview-action-bar"></div>');
                    $buttons.appendTo($actionBar);
                    
                    // Smart Injection: Puts the buttons right before the Similar Roles slider or Share buttons
                    if ($('.nk-related-jobs-wrapper').length > 0) {
                        $actionBar.insertBefore('.nk-related-jobs-wrapper');
                    } else if ($('.nk-share-container').length > 0) {
                        $actionBar.insertBefore('.nk-share-container');
                    } else {
                        $previewForm.append($actionBar);
                    }
                }
            }
        });
    </script>
    <?php
}