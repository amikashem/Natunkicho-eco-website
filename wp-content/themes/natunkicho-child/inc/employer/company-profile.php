<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * COMPANY PROFILE (Self-Contained Flex Grid)
 * Path: inc/employer/company-profile.php
 * Shortcode: [nk_company_profile]
 * =========================================================================
 */
function nk_company_profile_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<div class="nk-dash-card" style="text-align:center; padding: 40px;"><h3>Please login first to view your company profile.</h3></div>';
    }

    $user_id = get_current_user_id();
    $message = '';

    /**
     * 1. SAVE PROFILE HANDLING
     */
    if ( isset( $_POST['nk_company_submit'] ) && wp_verify_nonce( $_POST['nk_company_nonce'], 'save_company_profile' ) ) {
        update_user_meta( $user_id, 'nk_company_name', sanitize_text_field( $_POST['nk_company_name'] ) );
        
        $website_input = sanitize_text_field( $_POST['nk_company_website'] );
        if ( ! empty( $website_input ) && ! preg_match( "~^(?:f|ht)tps?://~i", $website_input ) ) {
            $website_input = "https://" . $website_input;
        }
        update_user_meta( $user_id, 'nk_company_website', esc_url_raw( $website_input ) );

        update_user_meta( $user_id, 'nk_company_location', sanitize_text_field( $_POST['nk_company_location'] ) );
        update_user_meta( $user_id, 'nk_company_about', sanitize_textarea_field( $_POST['nk_company_about'] ) );
        update_user_meta( $user_id, 'nk_company_size', sanitize_text_field( $_POST['nk_company_size'] ) );
        
        $message = '<div class="nk-success-notice" style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">Company profile successfully updated.</div>';
    }

    /**
     * 2. DATA EXTRACTION
     */
    $company_name     = get_user_meta( $user_id, 'nk_company_name', true );
    $company_website  = get_user_meta( $user_id, 'nk_company_website', true );
    $company_location = get_user_meta( $user_id, 'nk_company_location', true );
    $company_about    = get_user_meta( $user_id, 'nk_company_about', true );
    $company_size     = get_user_meta( $user_id, 'nk_company_size', true );

    ob_start();
    ?>
    <style>
        .nk-runtime-flex-layout {
            display: flex !important;
            gap: 30px !important;
            max-width: 1400px !important;
            margin: 30px auto !important;
            align-items: flex-start !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .nk-runtime-sidebar-column {
            width: 280px !important;
            flex-shrink: 0 !important;
        }
        .nk-runtime-workspace-column {
            flex: 1 !important;
            min-width: 0 !important;
        }
        @media (max-width: 991px) {
            .nk-runtime-flex-layout { flex-direction: column !important; gap: 20px !important; }
            .nk-runtime-sidebar-column, .nk-runtime-workspace-column { width: 100% !important; }
        }
    </style>

    <div class="nk-runtime-flex-layout">
        
        <div class="nk-runtime-sidebar-column">
            <?php 
            $sidebar_path = get_stylesheet_directory() . '/inc/dashboard/sidebar.php';
            if ( file_exists( $sidebar_path ) ) {
                include_once $sidebar_path;
                $sidebar_user = wp_get_current_user();
                $roles        = ! empty( $sidebar_user->roles ) ? $sidebar_user->roles : [];
                $is_emp       = in_array( 'employer', $roles ) || in_array( 'administrator', $roles );
                $is_cand      = in_array( 'job_seeker', $roles );
                $is_adm       = in_array( 'administrator', $roles );
                echo nk_get_dashboard_sidebar( $sidebar_user, $is_emp, $is_cand, $is_adm );
            }
            ?>
        </div>
        
        <div class="nk-runtime-workspace-column">
            <div class="nk-company-profile nk-dash-card">
                
                <div class="nk-manage-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <h2 style="margin: 0; color: #111111;">Company Profile</h2>
                    <p style="margin: 5px 0 0 0; color: #666666;">Update your employer branding to attract the best hospitality talent.</p>
                </div>

                <?php echo $message; ?>

                <form method="post" class="nk-company-form nk-professional-form">
                    <?php wp_nonce_field( 'save_company_profile', 'nk_company_nonce' ); ?>

                    <fieldset style="margin-bottom: 20px; border: none; padding: 0;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Company Name</label>
                        <input type="text" name="nk_company_name" value="<?php echo esc_attr( $company_name ); ?>" style="width:100%; height:50px; border:1px solid #ddd; border-radius:10px; padding:0 15px;" required>
                    </fieldset>

                    <fieldset style="margin-bottom: 20px; border: none; padding: 0;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Company Website <small style="color:#999999; font-weight:normal;">(optional)</small></label>
                        <input type="text" name="nk_company_website" value="<?php echo esc_attr( $company_website ); ?>" placeholder="www.yourcompany.com" style="width:100%; height:50px; border:1px solid #ddd; border-radius:10px; padding:0 15px;">
                        <?php if ( ! empty( $company_website ) ) : ?>
                            <div style="margin-top: 8px; font-size: 13px; color: #666666;">
                                Test Your Link: <a href="<?php echo esc_url( $company_website ); ?>" target="_blank" rel="noopener noreferrer" style="color: #0A66C2; text-decoration: underline; font-weight: 600;"><?php echo esc_html( $company_website ); ?> ↗</a>
                            </div>
                        <?php endif; ?>
                    </fieldset>

                    <fieldset style="margin-bottom: 20px; border: none; padding: 0;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Company Location</label>
                        <input type="text" name="nk_company_location" value="<?php echo esc_attr( $company_location ); ?>" placeholder="e.g. Dhaka, Bangladesh" style="width:100%; height:50px; border:1px solid #ddd; border-radius:10px; padding:0 15px;">
                    </fieldset>

                    <fieldset style="margin-bottom: 20px; border: none; padding: 0;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Company Size</label>
                        <input type="text" name="nk_company_size" value="<?php echo esc_attr( $company_size ); ?>" placeholder="e.g. 50-100 Employees" style="width:100%; height:50px; border:1px solid #ddd; border-radius:10px; padding:0 15px;">
                    </fieldset>

                    <fieldset style="margin-bottom: 25px; border: none; padding: 0;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">About Company</label>
                        <textarea name="nk_company_about" rows="6" style="width:100%; border:1px solid #ddd; border-radius:10px; padding:15px;"><?php echo esc_textarea( $company_about ); ?></textarea>
                        <span class="description" style="display: block; font-size: 13px; color: #666666; margin-top: 5px;">Describe your company culture and why hospitality professionals should work with you.</span>
                    </fieldset>

                    <div class="nk-form-submit-wrapper">
                        <button type="submit" name="nk_company_submit" class="nk-btn-primary" style="width: 100%; height: 55px; font-size: 16px; font-weight: bold; cursor: pointer;">
                            Save Profile Settings
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

remove_shortcode( 'nk_company_profile' );
add_shortcode( 'nk_company_profile', 'nk_company_profile_shortcode' );