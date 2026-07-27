<?php
/**
 * Natunkicho External Course Engine (Option B Architecture)
 * Handles Affiliate courses (Coursera, Typsy, AHLEI) isolated from Tutor LMS.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Register the External Course Custom Post Type
add_action( 'init', 'nk_register_external_courses_cpt' );
function nk_register_external_courses_cpt() {
    $labels = array(
        'name'                  => 'Affiliate Courses',
        'singular_name'         => 'Affiliate Course',
        'menu_name'             => 'Affiliate Courses',
        'add_new'               => 'Add New Course',
        'add_new_item'          => 'Add New Affiliate Course',
        'edit_item'             => 'Edit Affiliate Course',
        'all_items'             => 'All Affiliate Courses',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'partner-course' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 26,
        'menu_icon'          => 'dashicons-networking', // Distinct icon
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'show_in_rest'       => true, // Enables Gutenberg editor
    );

    register_post_type( 'nk_external_course', $args );
}

// 2. Register the "Providers" Taxonomy (Coursera, Typsy, AHLEI)
add_action( 'init', 'nk_register_course_providers_tax' );
function nk_register_course_providers_tax() {
    register_taxonomy(
        'nk_course_provider',
        'nk_external_course',
        array(
            'label'        => 'Course Providers',
            'hierarchical' => true,
            'rewrite'      => array( 'slug' => 'provider' ),
            'show_admin_column' => true,
            'show_in_rest' => true,
        )
    );
}

// 3. THE BRIDGE: Connect Tutor LMS Categories to External Courses
add_action( 'init', 'nk_bridge_tutor_categories_to_external' );
function nk_bridge_tutor_categories_to_external() {
    // This allows Affiliate Courses to use the exact same categories as Internal Courses
    register_taxonomy_for_object_type( 'course-category', 'nk_external_course' );
}

// 4. Create the Meta Box for Affiliate Links & Pricing
add_action( 'add_meta_boxes', 'nk_add_external_course_meta' );
function nk_add_external_course_meta() {
    add_meta_box(
        'nk_external_course_data',
        'Affiliate Link & Pricing Data',
        'nk_external_course_meta_html',
        'nk_external_course',
        'normal',
        'high'
    );
}

function nk_external_course_meta_html( $post ) {
    wp_nonce_field( 'nk_save_external_course_data', 'nk_external_course_nonce' );
    
    $ext_url   = get_post_meta( $post->ID, '_nk_ext_url', true );
    $ext_price = get_post_meta( $post->ID, '_nk_ext_price', true );
    $ext_btn   = get_post_meta( $post->ID, '_nk_ext_btn_text', true ) ?: 'Enroll on Partner Site';
    ?>
    <div style="padding: 10px 0;">
        <p style="margin-bottom: 15px;">
            <label for="nk_ext_url" style="font-weight:bold; display:block; margin-bottom:5px;">Affiliate / Referral Link (URL):</label>
            <input type="url" id="nk_ext_url" name="nk_ext_url" value="<?php echo esc_attr( $ext_url ); ?>" style="width:100%; padding:8px;" placeholder="https://coursera.org/affiliate-link..." />
        </p>
        <p style="margin-bottom: 15px;">
            <label for="nk_ext_price" style="font-weight:bold; display:block; margin-bottom:5px;">Price Display (e.g., "$49/mo" or "Free"):</label>
            <input type="text" id="nk_ext_price" name="nk_ext_price" value="<?php echo esc_attr( $ext_price ); ?>" style="width:100%; padding:8px;" />
        </p>
        <p style="margin-bottom: 15px;">
            <label for="nk_ext_btn_text" style="font-weight:bold; display:block; margin-bottom:5px;">Custom Button Text:</label>
            <input type="text" id="nk_ext_btn_text" name="nk_ext_btn_text" value="<?php echo esc_attr( $ext_btn ); ?>" style="width:100%; padding:8px;" />
        </p>
    </div>
    <?php
}

// 5. Save the Meta Box Data Safely
add_action( 'save_post', 'nk_save_external_course_meta' );
function nk_save_external_course_meta( $post_id ) {
    if ( ! isset( $_POST['nk_external_course_nonce'] ) || ! wp_verify_nonce( $_POST['nk_external_course_nonce'], 'nk_save_external_course_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['nk_ext_url'] ) ) update_post_meta( $post_id, '_nk_ext_url', esc_url_raw( $_POST['nk_ext_url'] ) );
    if ( isset( $_POST['nk_ext_price'] ) ) update_post_meta( $post_id, '_nk_ext_price', sanitize_text_field( $_POST['nk_ext_price'] ) );
    if ( isset( $_POST['nk_ext_btn_text'] ) ) update_post_meta( $post_id, '_nk_ext_btn_text', sanitize_text_field( $_POST['nk_ext_btn_text'] ) );
}

// ==========================================================================
// 6. THE API CONTROL ROOM (Professional Mapped Settings & Importer)
// ==========================================================================

// A. Create the Submenus under Tutor LMS
add_action( 'admin_menu', 'nk_api_management_menus', 99 );
function nk_api_management_menus() {
    add_submenu_page( 'tutor', 'Import Affiliate Courses', 'API Importer', 'manage_options', 'nk-api-importer', 'nk_api_importer_html' );
    add_submenu_page( 'tutor', 'Affiliate API Settings', 'API Settings', 'manage_options', 'nk-api-settings', 'nk_api_settings_html' );
}

// B. The API Configuration Mapping (Defines what each provider requires)
function nk_get_api_provider_config() {
    return array(
        'coursera'    => array( 'label' => 'Coursera',    'fields' => array( 'token' => 'API Token' ) ),
        'udemy'       => array( 'label' => 'Udemy',       'fields' => array( 'client_id' => 'Client ID', 'client_secret' => 'Client Secret' ) ),
        'edx'         => array( 'label' => 'edX',         'fields' => array( 'access_token' => 'Access Token' ) ),
        'typsy'       => array( 'label' => 'Typsy',       'fields' => array( 'api_key' => 'API Key' ) ),
        'rouxbe'      => array( 'label' => 'Rouxbe',      'fields' => array( 'network_id' => 'Network ID', 'api_key' => 'API Key' ) ),
        'ahlei'       => array( 'label' => 'AHLEI',       'fields' => array( 'api_key' => 'API Key' ) ),
        'ehotelier'   => array( 'label' => 'eHotelier',   'fields' => array( 'access_key' => 'Access Key' ) ),
        'futurelearn' => array( 'label' => 'FutureLearn', 'fields' => array( 'api_key' => 'API Key' ) ),
        'alison'      => array( 'label' => 'Alison',      'fields' => array( 'publisher_key' => 'Publisher Key' ) )
    );
}

// C. The Professional Settings Panel UI
function nk_api_settings_html() {
    $config = nk_get_api_provider_config();

    if ( isset( $_POST['nk_save_apis'] ) && check_admin_referer( 'nk_save_api_action' ) ) {
        foreach ( $config as $provider_id => $provider_data ) {
            foreach ( $provider_data['fields'] as $field_id => $field_label ) {
                $input_name = 'nk_api_' . $provider_id . '_' . $field_id;
                if ( isset( $_POST[$input_name] ) ) {
                    update_option( $input_name, sanitize_text_field( wp_unslash( $_POST[$input_name] ) ) );
                }
            }
        }
        echo '<div class="notice notice-success is-dismissible" style="margin-top:20px;"><p>✅ <strong>Success!</strong> API Credentials saved securely.</p></div>';
    }
    ?>
    <div class="wrap" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 900px; margin-top: 20px;">
        <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">⚙️ Affiliate API Settings</h1>
        <p style="color: #666; margin-bottom: 30px;">Securely manage your API credentials. Only fill out the providers you have been approved for.</p>
        
        <form method="POST">
            <?php wp_nonce_field( 'nk_save_api_action' ); ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <?php foreach ( $config as $provider_id => $provider_data ) : ?>
                    <div style="background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #e2e4e7;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #0056b3;">
                            <?php echo esc_html( $provider_data['label'] ); ?>
                        </h3>
                        
                        <?php foreach ( $provider_data['fields'] as $field_id => $field_label ) : 
                            $option_key = 'nk_api_' . $provider_id . '_' . $field_id;
                            $saved_val  = get_option( $option_key );
                        ?>
                            <div style="margin-bottom: 12px;">
                                <label style="font-weight: 600; display: block; font-size: 13px; margin-bottom: 5px; color:#444;">
                                    <?php echo esc_html( $field_label ); ?>
                                </label>
                                <input type="text" name="<?php echo esc_attr( $option_key ); ?>" value="<?php echo esc_attr( $saved_val ); ?>" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: monospace;" placeholder="Enter <?php echo esc_attr( $field_label ); ?>...">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" name="nk_save_apis" class="button button-primary" style="margin-top: 30px; padding: 10px 30px; height: auto; font-size: 16px;">Save API Credentials</button>
        </form>
    </div>
    <?php
}

// D. The Importer Dashboard UI (Remains mostly the same visually)
function nk_api_importer_html() {
    $config = nk_get_api_provider_config();
    ?>
    <div class="wrap" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 800px; margin-top: 20px;">
        <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">🚀 Affiliate Course Importer</h1>
        <p style="color: #666; margin-bottom: 30px;">Select a provider and enter the Course ID to instantly fetch its data.</p>
        
        <div style="display: flex; gap: 20px; align-items: flex-end; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
            <div style="flex: 1;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Select Provider</label>
                <select id="nk-api-provider" style="width: 100%; padding: 8px;">
                    <?php foreach ( $config as $provider_id => $provider_data ) : ?>
                        <option value="<?php echo esc_attr($provider_id); ?>"><?php echo esc_html($provider_data['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 2;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Course ID</label>
                <input type="text" id="nk-api-course-id" placeholder="e.g., course-id-123" style="width: 100%; padding: 8px;">
            </div>
            
            <div>
                <button id="nk-api-fetch-btn" class="button button-primary" style="padding: 6px 20px; height: auto;">Fetch Course</button>
            </div>
        </div>
        <div id="nk-api-results" style="margin-top: 30px; padding: 15px; border-radius: 6px; display: none;"></div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#nk-api-fetch-btn').on('click', function(e) {
            e.preventDefault();
            var provider = $('#nk-api-provider').val();
            var courseId = $('#nk-api-course-id').val();
            var $btn = $(this);
            var $results = $('#nk-api-results');

            if (!courseId) { alert('Please enter a Course ID.'); return; }

            $btn.prop('disabled', true).text('Fetching...');
            $results.show().css({'background': '#fff', 'border': 'none', 'color': '#333'}).html('<p style="color: #0056b3;">⏳ Connecting to ' + provider.toUpperCase() + ' API...</p>');

            $.ajax({
                url: ajaxurl, type: 'POST',
                data: { action: 'nk_fetch_external_api_course', provider: provider, course_id: courseId, nonce: '<?php echo wp_create_nonce("nk_api_import_nonce"); ?>' },
                success: function(response) {
                    if(response.success) {
                        $results.css({'background': '#eef5fa', 'border': '1px solid #b8daff', 'color': '#004085'}).html('<h3>✅ Success!</h3><p><strong>' + response.data.title + '</strong> imported.</p><a href="' + response.data.edit_url + '" class="button" target="_blank">Edit Course</a>');
                    } else {
                        $results.css({'background': '#fdf2f2', 'border': '1px solid #f5c6cb', 'color': '#721c24'}).html('<h3>❌ Error</h3><p>' + response.data + '</p>');
                    }
                    $btn.prop('disabled', false).text('Fetch Course');
                }
            });
        });
    });
    </script>
    <?php
}

// E. The Background API Logic (AJAX Handler)
add_action( 'wp_ajax_nk_fetch_external_api_course', 'nk_process_api_fetch' );
function nk_process_api_fetch() {
    check_ajax_referer( 'nk_api_import_nonce', 'nonce' );

    $provider  = sanitize_text_field( $_POST['provider'] );
    $course_id = sanitize_text_field( $_POST['course_id'] );
    
    // Validate that ALL required fields for this provider exist
    $config = nk_get_api_provider_config();
    $missing_keys = false;
    
    foreach ( $config[$provider]['fields'] as $field_id => $field_label ) {
        $key_val = get_option( 'nk_api_' . $provider . '_' . $field_id );
        if ( empty( trim( $key_val ) ) ) {
            $missing_keys = true;
        }
    }

    if ( $missing_keys ) {
        wp_send_json_error( "You are missing API credentials for " . $config[$provider]['label'] . ". Please go to API Settings and fill in all fields." );
    }

    // ------------------------------------------------------------------
    // API ROUTING LOGIC (Demo data)
    // ------------------------------------------------------------------
    $fetched_data = array(
        'title'       => $config[$provider]['label'] . ' Hospitality Masterclass',
        'description' => 'This course was fetched via the verified ' . $config[$provider]['label'] . ' API. It covers essential hospitality skills.',
        'image_url'   => '',
        'price'       => '$49.99',
        'link'        => 'https://' . $provider . '.com/course/' . $course_id . '?ref=natunkicho'
    );

    // ------------------------------------------------------------------
    // POST CREATION ENGINE
    // ------------------------------------------------------------------
    $new_post = array(
        'post_title'   => sanitize_text_field( $fetched_data['title'] ),
        'post_content' => wp_kses_post( $fetched_data['description'] ),
        'post_status'  => 'publish',
        'post_type'    => 'nk_external_course',
    );

    $post_id = wp_insert_post( $new_post );
    if ( is_wp_error( $post_id ) ) { wp_send_json_error( 'Database error.' ); }

    wp_set_object_terms( $post_id, $provider, 'nk_course_provider' );
    update_post_meta( $post_id, '_nk_ext_url', $fetched_data['link'] );
    update_post_meta( $post_id, '_nk_ext_price', $fetched_data['price'] );
    update_post_meta( $post_id, '_nk_ext_btn_text', 'Enroll on ' . $config[$provider]['label'] );

    wp_send_json_success( array( 'title' => $fetched_data['title'], 'edit_url' => get_edit_post_link( $post_id, 'raw' ) ) );
}

// 7. Add External Courses Router (For the SEO Landing Pages)
add_filter( 'single_template', 'nk_route_external_course_template' );
function nk_route_external_course_template( $single_template ) {
    global $post;
    if ( $post->post_type === 'nk_external_course' ) {
        $custom_template = NK_LMS_DIR . 'templates/single-nk_external_course.php';
        if ( file_exists( $custom_template ) ) return $custom_template;
    }
    return $single_template;
}