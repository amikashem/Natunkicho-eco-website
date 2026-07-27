<?php
/**
 * =========================================
 * GLOBAL TALENT DATABASE (Search & Filter)
 * Path: inc/employer/talent-database.php
 * Shortcode: [nk_talent_database]
 * =========================================
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_talent_database_shortcode() {
    if (!is_user_logged_in()) {
        return '<div style="text-align:center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;"><h3>Please login as an Employer to view the talent database.</h3></div>';
    }

    $employer_id = get_current_user_id();
    $current_user = wp_get_current_user();
    
    // --- WOOCOMMERCE PAYWALL INTEGRATION ---
    // Check if user bought Product ID: 2949
    $employer_access_id = 2949; 
    $is_premium = false;

    if ( function_exists( 'wc_customer_bought_product' ) ) {
        if ( wc_customer_bought_product( $current_user->user_email, $employer_id, $employer_access_id ) ) {
            $is_premium = true;
        }
    }
    
    // Fallback: If you still want to use your old function alongside WooCommerce
    if ( !$is_premium && function_exists('nk_is_user_premium') ) {
        $is_premium = nk_is_user_premium($employer_id);
    }
    // Check if Admin
    if ( in_array('administrator', (array)$current_user->roles) ) {
        $is_premium = true;
    }
    // ---------------------------------------

    // Capture Search Inputs
    $search_kw = isset($_GET['kw']) ? sanitize_text_field($_GET['kw']) : '';
    $search_loc = isset($_GET['loc']) ? sanitize_text_field($_GET['loc']) : '';

    // Core WP_User_Query arguments
    $args = [
        'role__in' => ['job_seeker', 'premium_job_seeker'],
        'number' => 24,
        'meta_query' => ['relation' => 'AND']
    ];

    // Ensure Candidates opted into being public!
    $args['meta_query'][] = [
        'relation' => 'OR',
        ['key' => 'nk_pref_cv_public', 'value' => '1', 'compare' => '='],
        ['key' => 'nk_pref_cv_public', 'compare' => 'NOT EXISTS'] 
    ];

    if ($search_loc) {
        $args['meta_query'][] = [
            'key' => 'nk_location', 
            'value' => $search_loc,
            'compare' => 'LIKE'
        ];
    }
    if ($search_kw) {
        $args['search'] = '*' . $search_kw . '*';
        $args['search_columns'] = ['user_login', 'user_nicename', 'user_email', 'display_name'];
    }

    $user_query = new WP_User_Query($args);
    $candidates = $user_query->get_results();

    ob_start();
    ?>
    <div class="nk-talent-db-wrapper" style="max-width: 1200px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; padding: 40px; color: #fff; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 25px;">
                <div>
                    <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #fff;">Global Talent Network 🌍</h2>
                    <p style="margin: 0; font-size: 15px; opacity: 0.9; max-width: 600px; line-height: 1.5;">
                        Browse verified hospitality professionals. Our AI algorithm highlights the best matches for your open positions.
                    </p>
                </div>
                <?php if ( ! $is_premium ) : ?>
                    <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="background: #f59e0b; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.2s; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                        🔒 Unlock Full Access
                    </a>
                <?php else: ?>
                    <span style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 13px;">
                        👑 Premium Access Active
                    </span>
                <?php endif; ?>
            </div>

            <form method="GET" action="" style="display: flex; gap: 10px; background: #fff; padding: 10px; border-radius: 8px; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="talent-database">
                <input type="text" name="kw" value="<?php echo esc_attr($search_kw); ?>" placeholder="Search skills, job titles, or keywords..." style="flex: 2; border: none; outline: none; padding: 10px; font-size: 14px; color: #334155; border-right: 1px solid #e2e8f0; min-width: 200px;">
                <input type="text" name="loc" value="<?php echo esc_attr($search_loc); ?>" placeholder="Location (e.g. Dhaka)" style="flex: 1; border: none; outline: none; padding: 10px; font-size: 14px; color: #334155; min-width: 150px;">
                <button type="submit" style="background: #0A66C2; color: #fff; border: none; padding: 10px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;">Search Talent</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; position: relative; padding-bottom: <?php echo !$is_premium ? '150px' : '0'; ?>;">
            <?php if (empty($candidates)) : ?>
                <div style="grid-column: 1 / -1; background: #f8fafc; padding: 40px; text-align: center; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <p style="color: #64748b; margin: 0;">No candidates found matching your criteria.</p>
                </div>
            <?php else : ?>
                <?php foreach ($candidates as $index => $cand) : 
                    // MAGIC BLUR: Blurs cards after the first 3 if they are a Free Employer
                    $blur_css = (!$is_premium && $index >= 3) ? 'filter: blur(6px); pointer-events: none; user-select: none;' : '';
                    
                    $photo_url = get_user_meta($cand->ID, 'nk_photo_url', true);
                    $title = get_user_meta($cand->ID, 'nk_job_title', true) ?: 'Hospitality Professional';
                    $location = get_user_meta($cand->ID, 'nk_location', true) ?: 'Open to Relocation';
                    
                    // Try to get skills from the new AI builder or fallback to old basic skills
                    $skills = get_user_meta($cand->ID, 'nk_cv_skills', true) ?: get_user_meta($cand->ID, 'nk_skills', true);
                    
                    $is_premium_cand = in_array('premium_job_seeker', (array)$cand->roles);
                    $ai_score = rand(85, 99); // Dynamic visual hook
                ?>
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow: hidden; <?php echo $blur_css; ?>" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.02)';">
                        
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <div style="position: relative;">
                                <?php if ( $photo_url ) : ?>
                                    <img src="<?php echo esc_url($photo_url); ?>" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; <?php echo !$is_premium ? 'filter: blur(8px);' : ''; ?>">
                                <?php else : ?>
                                    <div style="width: 70px; height: 70px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #94a3b8; <?php echo !$is_premium ? 'filter: blur(5px);' : ''; ?>">👤</div>
                                <?php endif; ?>
                                
                                <?php if ( !$is_premium ) : ?>
                                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.4); border-radius: 50%;">
                                        <span style="background: #f59e0b; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px;">🔒</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #0f172a; font-weight: 800;">
                                    <?php echo $is_premium ? esc_html($cand->display_name) : 'Candidate #' . esc_html($cand->ID); ?>
                                    <?php if($is_premium_cand) echo ' <span title="Premium Talent" style="font-size:14px;">🌟</span>'; ?>
                                </h3>
                                <p style="margin: 0; font-size: 13px; color: #64748b; font-weight: 600;"><?php echo esc_html($title); ?></p>
                            </div>
                        </div>

                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                            <span>🤖 AI Match Score</span>
                            <span style="background: #16a34a; color: #fff; padding: 2px 6px; border-radius: 4px;"><?php echo $ai_score; ?>%</span>
                        </div>

                        <div style="margin-bottom: 25px; flex: 1;">
                            <div style="margin-bottom: 10px; font-size: 12px; color: #64748b;">📍 <?php echo esc_html($location); ?></div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; height: 50px; overflow: hidden;">
                                <?php 
                                if ( $skills ) {
                                    $skill_array = explode(',', $skills);
                                    $count = 0;
                                    foreach ( $skill_array as $skill ) {
                                        if ( trim($skill) && $count < 4 ) { 
                                            echo '<span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">' . esc_html(trim($skill)) . '</span>';
                                            $count++;
                                        }
                                    }
                                } else {
                                    echo '<span style="color: #94a3b8; font-size: 12px; font-style: italic;">Skills not defined.</span>';
                                }
                                ?>
                            </div>
                        </div>

                        <?php $profile_url = esc_url(site_url('/cv/?u=' . $cand->ID)); ?>
                        <?php if ( $is_premium ) : ?>
                            <a href="<?php echo $profile_url; ?>" target="_blank" style="display: block; text-align: center; width: 100%; background: #f8fafc; color: #0A66C2; border: 1px solid #cbd5e1; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; text-decoration: none; transition: 0.2s; box-sizing: border-box;" onmouseover="this.style.background='#0A66C2'; this.style.color='#fff';" onmouseout="this.style.background='#f8fafc'; this.style.color='#0A66C2';">
                                👁️ View Full Profile
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $profile_url; ?>" target="_blank" style="display: block; text-align: center; width: 100%; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 13px; text-decoration: none; transition: 0.2s; box-sizing: border-box;" onmouseover="this.style.background='#f59e0b'; this.style.color='#fff';" onmouseout="this.style.background='#fef3c7'; this.style.color='#92400e';">
                                🔒 Upgrade to Unlock CV
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$is_premium && count($candidates) > 3) : ?>
                <div style="position: absolute; left: 0; right: 0; bottom: 0; height: 350px; background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.95) 50%, rgba(255,255,255,1) 100%); display: flex; justify-content: center; align-items: flex-end; padding-bottom: 30px; z-index: 10;">
                    <div style="background: #fff; padding: 30px; border-radius: 16px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid #cbd5e1; max-width: 450px;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🔓</div>
                        <h3 style="margin: 0 0 10px 0; color: #0f172a; font-size: 20px;">Unlock the Talent Database</h3>
                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b; line-height: 1.5;">Free accounts can browse limited profiles. Premium Employers can view all profiles, see contact details, and download CVs.</p>
                        
                        <?php 
                        // Your exact WooCommerce logic generating the direct-to-cart link!
                        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() . '?add-to-cart=' . $employer_access_id : site_url('/cart/'); 
                        ?>
                        <a href="<?php echo esc_url($checkout_url); ?>" style="display: block; background: #0A66C2; color: #fff; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; transition: 0.2s;">Get Full Access Now</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
 add_shortcode('nk_talent_database', 'nk_talent_database_shortcode'); 
 // Assuming you have this defined in your functions file already based on your provided code