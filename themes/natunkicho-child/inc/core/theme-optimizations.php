<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Facebook Meta Verification
add_action('wp_head', function() {
    echo '<meta name="facebook-domain-verification" content="mvga05tdhr7jy1l5ytd1eogh2wpr2t" />';
});

// 2. Completely Disable Astra Footer Builder
add_action('wp', function() {
    if (class_exists('Astra_Builder_Footer')) {
        remove_action('astra_footer', array(Astra_Builder_Footer::get_instance(), 'footer_markup'));
    }
});

// 3. Frontend Job Search Placeholder
add_filter('job_manager_get_listings_custom_filter', function($query_args){ return $query_args; }); 

// 4. Customize WP Job Manager Fields (Premium UI/UX)
add_filter('submit_job_form_fields', function($fields) {
    unset($fields['company']['company_twitter']);
    unset($fields['company']['company_video']);

    $fields['job']['job_skills'] = [
        'label'       => 'Required Skills',
        'type'        => 'text',
        'required'    => true,
        'placeholder' => 'e.g. Fine Dining, POS Systems, Team Leadership',
        'priority'    => 5,
    ];

    $fields['job']['job_requirements'] = [
        'label'       => 'Experience & Requirements',
        'type'        => 'wp-editor',
        'required'    => true,
        'placeholder' => 'List the qualifications, years of experience, and certifications needed...',
        'priority'    => 6,
    ];

    if(isset($fields['job']['job_title'])) $fields['job']['job_title']['placeholder'] = 'e.g. Executive Chef, Front Desk Manager';
    if(isset($fields['job']['job_location'])) $fields['job']['job_location']['placeholder'] = 'City, State, or Remote';

    return $fields;
}, 10, 1); 

// 5. Dynamic Saved Jobs Handler
add_action( 'wp_ajax_nk_toggle_save_job', function() {
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Session expired. Please sign in to save position tracking profiles.' );
    
    $user_id = get_current_user_id();
    $job_id  = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
    if ( ! $job_id ) wp_send_json_error( 'Invalid position ID targeted.' );

    $saved_jobs = get_user_meta( $user_id, 'nk_saved_jobs', true );
    if ( ! is_array( $saved_jobs ) ) $saved_jobs = [];

    if ( in_array( $job_id, $saved_jobs ) ) {
        $saved_jobs = array_diff( $saved_jobs, [ $job_id ] );
        $status = 'unsaved';
    } else {
        $saved_jobs[] = $job_id;
        $status = 'saved';
    }

    update_user_meta( $user_id, 'nk_saved_jobs', $saved_jobs );
    wp_send_json_success( [ 'status' => $status ] );
});

// 6. Secure Password Changer (Dashboard Settings)
add_action('wp_ajax_nk_change_account_password', function() {
    check_ajax_referer('nk_security_nonce', 'security');
    if ( ! is_user_logged_in() ) wp_send_json_error( 'Please log in.' );

    $user = wp_get_current_user();
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];

    if ( ! wp_check_password( $current_pass, $user->user_pass, $user->ID ) ) wp_send_json_error( 'Your current password is incorrect.' );

    wp_set_password( $new_pass, $user->ID );
    clean_user_cache( $user->ID );
    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID );
    wp_send_json_success( 'Password updated.' );
});

// 7. Global Password Visibility Toggle (SaaS UX Engine)
add_action( 'wp_footer', function() {
    ?>
    <style>
        .nk-password-container-wrapper { position: relative !important; display: block !important; width: 100% !important; }
        .nk-password-visibility-trigger { position: absolute !important; top: 50% !important; right: 15px !important; transform: translateY(-50%) !important; cursor: pointer !important; background: none !important; border: none !important; padding: 0 !important; margin: 0 !important; font-size: 18px !important; line-height: 1 !important; color: #64748b !important; user-select: none !important; z-index: 10 !important; transition: color 0.2s ease !important; }
        .nk-password-visibility-trigger:hover { color: #0A66C2 !important; }
        .nk-password-container-wrapper input[type="password"], .nk-password-container-wrapper input[type="text"] { padding-right: 45px !important; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function nk_initialize_password_fields() {
            document.querySelectorAll('input[type="password"]:not(.nk-processed-toggle)').forEach(function(field) {
                field.classList.add('nk-processed-toggle');
                const wrapper = document.createElement('div');
                wrapper.className = 'nk-password-container-wrapper';
                field.parentNode.insertBefore(wrapper, field);
                wrapper.appendChild(field);
                
                const toggleBtn = document.createElement('span');
                toggleBtn.className = 'nk-password-visibility-trigger';
                toggleBtn.innerHTML = '👁️';
                toggleBtn.setAttribute('title', 'Show Password');
                wrapper.appendChild(toggleBtn);
                
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault(); e.stopPropagation();
                    if (field.type === 'password') { field.type = 'text'; toggleBtn.innerHTML = '🙈'; toggleBtn.setAttribute('title', 'Hide Password'); } 
                    else { field.type = 'password'; toggleBtn.innerHTML = '👁️'; toggleBtn.setAttribute('title', 'Show Password'); }
                });
            });
        }
        nk_initialize_password_fields();
        const observer = new MutationObserver(function() { nk_initialize_password_fields(); });
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
    <?php
}, 999 );