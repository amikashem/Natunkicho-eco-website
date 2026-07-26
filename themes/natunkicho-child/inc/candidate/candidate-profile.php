<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * NEXT-GEN CANDIDATE PROFILE SYSTEM (Unified Dashboard Hub)
 * Path: inc/candidate/candidate-profile.php
 * =========================================================================
 */

// 1. Smart Completion Calculation (100% Total)
function nk_get_profile_completion($user_id){
    global $wpdb;
    $score = 0; 
    
    // Core Identity (40%)
    if (get_user_meta($user_id, 'nk_photo_url', true)) $score += 15;
    if (get_user_meta($user_id, 'nk_linkedin', true)) $score += 10;
    if (get_user_meta($user_id, 'nk_portfolio', true)) $score += 5;
    if (get_user_meta($user_id, 'nk_github', true)) $score += 5;
    if (get_user_meta($user_id, 'nk_instagram', true)) $score += 5;
    
    // Documentation (60%)
    if (get_user_meta($user_id, 'nk_cv_file_url', true)) $score += 10;
    
    $cv_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nk_cv_profiles WHERE user_id = %d", $user_id));
    if ($cv_exists > 0) $score += 50; // High reward for using AI Studio
    
    return min($score, 100);
}

// 2. AJAX Handler to Save Basic Profile Info
function nk_ajax_update_candidate_basic_profile() {
    check_ajax_referer('nk_profile_nonce', 'security');
    if (!is_user_logged_in()) wp_send_json_error('Please login first.');

    $user_id = get_current_user_id();
    update_user_meta($user_id, 'nk_linkedin', esc_url_raw($_POST['nk_linkedin']));
    update_user_meta($user_id, 'nk_portfolio', esc_url_raw($_POST['nk_portfolio']));
    update_user_meta($user_id, 'nk_github', esc_url_raw($_POST['nk_github']));
    update_user_meta($user_id, 'nk_instagram', esc_url_raw($_POST['nk_instagram']));

    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    if (!empty($_FILES['nk_photo']['name'])) {
        $photo = wp_handle_upload($_FILES['nk_photo'], array('test_form' => false));
        if ($photo && !isset($photo['error'])) {
            update_user_meta($user_id, 'nk_photo_url', $photo['url']);
        }
    }

    if (!empty($_FILES['nk_cv_file']['name'])) {
        $cv = wp_handle_upload($_FILES['nk_cv_file'], array('test_form' => false));
        if ($cv && !isset($cv['error'])) {
            update_user_meta($user_id, 'nk_cv_file_url', $cv['url']);
        }
    }

    wp_send_json_success('Basic Profile updated successfully!');
}
add_action('wp_ajax_nk_update_candidate_basic_profile', 'nk_ajax_update_candidate_basic_profile');


// --- 🔴 ADDED: AJAX Handler for Switching Active CV ---
function nk_ajax_set_active_cv() {
    check_ajax_referer('nk_profile_nonce', 'security');
    
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error('Please login first.');

    // Enforce Premium Rule: Free users can only have 1 CV, so they don't need to switch.
    if ( ! nk_is_user_premium( $user_id ) ) {
        wp_send_json_error('Switching active profiles is a Premium Pro feature.');
    }

    $cv_id = isset($_POST['cv_id']) ? intval($_POST['cv_id']) : 0;
    if ( ! $cv_id ) wp_send_json_error('Invalid CV ID.');

    // Security Check: Ensure this CV actually belongs to this user!
    global $wpdb;
    $owns_cv = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nk_cv_profiles WHERE id = %d AND user_id = %d", $cv_id, $user_id));
    
    if ( ! $owns_cv ) {
        wp_send_json_error('You do not have permission to activate this CV.');
    }

    // Success! Update the user meta to point to this specific CV ID
    update_user_meta( $user_id, 'nk_active_cv_id', $cv_id );

    wp_send_json_success('Active CV successfully switched!');
}
add_action('wp_ajax_nk_set_active_cv', 'nk_ajax_set_active_cv');

// 3. The Main Profile UI Shortcode
function nk_profile_edit_form_shortcode() {
    if(!is_user_logged_in()){
        return '<div class="nk-dash-card" style="text-align:center; padding: 40px;"><h3>Please login to view your profile.</h3></div>';
    }

    $user_id = get_current_user_id();
    $is_premium = nk_is_user_premium($user_id);
    $percentage = nk_get_profile_completion($user_id);
    
    $linkedin = get_user_meta($user_id, 'nk_linkedin', true);
    $portfolio = get_user_meta($user_id, 'nk_portfolio', true);
    $github = get_user_meta($user_id, 'nk_github', true);
    $instagram = get_user_meta($user_id, 'nk_instagram', true);
    $photo_url = get_user_meta($user_id, 'nk_photo_url', true);
    $cv_file_url = get_user_meta($user_id, 'nk_cv_file_url', true);

    global $wpdb;
    $saved_cvs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nk_cv_profiles WHERE user_id = %d ORDER BY updated_at DESC", $user_id));

    ob_start();
    ?>
    <style>
        .nk-runtime-flex-layout { display: flex !important; gap: 30px !important; max-width: 1400px !important; margin: 30px auto !important; align-items: flex-start !important; width: 100% !important; box-sizing: border-box !important; }
        .nk-runtime-sidebar-column { width: 280px !important; flex-shrink: 0 !important; }
        .nk-runtime-workspace-column { flex: 1 !important; min-width: 0 !important; }
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
                $is_emp       = in_array( 'employer', $roles ) || in_array('premium_employer', $roles) || in_array( 'administrator', $roles );
                $is_cand      = in_array( 'job_seeker', $roles ) || in_array('premium_job_seeker', $roles);
                $is_adm       = in_array( 'administrator', $roles );
                echo nk_get_dashboard_sidebar( $sidebar_user, $is_emp, $is_cand, $is_adm );
            }
            ?>
        </div>
        
        <div class="nk-runtime-workspace-column">
            <div class="nk-candidate-profile-wrapper">
                
                <div class="nk-dash-card" style="margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="margin: 0; color: #111;">Profile Strength: <?php echo esc_html($percentage); ?>%</h3>
                        <a href="<?php echo esc_url(site_url('/cv/')); ?>" target="_blank" class="nk-btn-primary" style="background: #0A66C2 !important; color: #fff !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: bold; border: none; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 4px 10px rgba(10, 102, 194, 0.2);">
                            👁️ View My Public Profile
                        </a>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 20px; height: 12px; width: 100%; overflow: hidden;">
                        <div style="background: #10b981; height: 100%; width: <?php echo esc_attr($percentage); ?>%; transition: width 0.5s ease;"></div>
                    </div>
                </div>

                <?php if (empty($saved_cvs) && empty($cv_file_url)): ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 24px;">⚠️</span>
                        <div>
                            <h4 style="margin: 0 0 5px 0; color: #991b1b; font-size: 15px;">Your Profile is Missing a Resume</h4>
                            <p style="margin: 0; color: #b91c1c; font-size: 13px;">Employers cannot fully evaluate your profile. Please upload a Native CV below or build one instantly using the AI Studio.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="nk-premium-locked-box" style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: #ffffff; margin-bottom: 30px; min-height: 190px;">
                    <?php if (!$is_premium): ?>
                        <div class="nk-premium-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(4px); z-index: 10; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 15px;">
                            <span class="nk-premium-badge" style="background: #f59e0b; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; display: inline-block;">Premium Pro</span>
                            <h3 style="margin: 5px 0; color: #1e293b; font-size: 17px;">Unlock Smart Career Notifications</h3>
                            <p style="color: #64748b; font-size: 13px; margin-bottom: 12px; max-width: 400px; line-height: 1.4;">Get personalized real-time alerts for top hospitality jobs, certifications, and exclusive recipes tailored to your profile.</p>
                            <a href="/pricing/" class="nk-btn-upgrade" style="background: #0A66C2 !important; color: #fff !important; text-decoration: none !important; padding: 10px 20px !important; border-radius: 8px !important; font-weight: bold !important; display: inline-block !important; border: none !important; cursor: pointer !important; z-index: 20; position: relative;">Upgrade to Premium Pro</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="<?php echo !$is_premium ? 'nk-premium-blurred-content' : 'nk-dash-card'; ?>" style="<?php echo $is_premium ? 'margin-bottom: 0;' : 'filter: blur(6px); pointer-events: none; user-select: none; opacity: 0.4; padding: 20px; min-height: 190px; box-sizing: border-box;'; ?>">
                        <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 15px;">🔔 Smart Career Ecosystem Settings</h3>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; font-weight:600; cursor:pointer;"><input type="checkbox" checked <?php echo !$is_premium ? 'disabled' : ''; ?> style="width:16px; height:16px;"> Real-time Matching Job Alerts</label>
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; font-weight:600; cursor:pointer;"><input type="checkbox" checked <?php echo !$is_premium ? 'disabled' : ''; ?> style="width:16px; height:16px;"> Recommended Hospitality Courses</label>
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; font-weight:600; cursor:pointer;"><input type="checkbox" checked <?php echo !$is_premium ? 'disabled' : ''; ?> style="width:16px; height:16px;"> New Culinary Recipes & Guides</label>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <div class="nk-dash-card">
                        <h2 style="margin-top:0; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; font-size: 18px;">Basic Information</h2>
                        
                        <form id="nk-profile-form" class="nk-professional-form" enctype="multipart/form-data">
                            <?php wp_nonce_field('nk_profile_nonce', 'nk_security'); ?>
                            
                            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center; margin-bottom: 20px;">
                                <label style="display:block; font-weight:600; margin-bottom:10px;">Profile Photo</label>
                                <?php if($photo_url): ?>
                                    <img src="<?php echo esc_url($photo_url); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 2px solid #0A66C2;">
                                <?php endif; ?>
                                <input type="file" name="nk_photo" accept="image/*" style="width:100%; font-size: 13px;">
                            </div>
                            
                            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center; margin-bottom: 20px;">
                                <label style="display:block; font-weight:600; margin-bottom:10px;">Native Document Upload (Optional)</label>
                                <p style="font-size: 12px; color: #64748b; margin-top: 0;">If you prefer not to use the AI Builder, upload your existing PDF/Doc here.</p>
                                <?php if($cv_file_url): ?>
                                    <a href="<?php echo esc_url($cv_file_url); ?>" target="_blank" style="display:inline-block; margin-bottom: 15px; color: #0A66C2; font-weight: bold;">📄 View Current Uploaded CV</a>
                                <?php endif; ?>
                                <input type="file" name="nk_cv_file" accept=".pdf,.doc,.docx" style="width:100%; font-size: 13px;">
                            </div>

                            <h3 style="font-size: 15px; margin-bottom: 15px;">🌐 Professional Links (Optional)</h3>
                            
                            <div class="nk-form-group" style="margin-bottom: 15px;">
                                <label style="display:block; font-weight:600; font-size: 13px; margin-bottom:5px;">LinkedIn Profile</label>
                                <input type="url" name="nk_linkedin" value="<?php echo esc_url($linkedin); ?>" placeholder="https://linkedin.com/in/username" style="width:100%; height:40px; border:1px solid #ddd; border-radius:6px; padding:0 15px; font-size:13px;">
                            </div>

                            <div class="nk-form-group" style="margin-bottom: 15px;">
                                <label style="display:block; font-weight:600; font-size: 13px; margin-bottom:5px;">Personal Portfolio / Website</label>
                                <input type="url" name="nk_portfolio" value="<?php echo esc_url($portfolio); ?>" placeholder="https://mywebsite.com" style="width:100%; height:40px; border:1px solid #ddd; border-radius:6px; padding:0 15px; font-size:13px;">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                                <div>
                                    <label style="display:block; font-weight:600; font-size: 13px; margin-bottom:5px;">GitHub URL</label>
                                    <input type="url" name="nk_github" value="<?php echo esc_url($github); ?>" placeholder="https://github.com/..." style="width:100%; height:40px; border:1px solid #ddd; border-radius:6px; padding:0 15px; font-size:13px;">
                                </div>
                                <div>
                                    <label style="display:block; font-weight:600; font-size: 13px; margin-bottom:5px;">Instagram URL</label>
                                    <input type="url" name="nk_instagram" value="<?php echo esc_url($instagram); ?>" placeholder="https://instagram.com/..." style="width:100%; height:40px; border:1px solid #ddd; border-radius:6px; padding:0 15px; font-size:13px;">
                                </div>
                            </div>

                            <button type="submit" id="nk-profile-submit-btn" class="nk-btn-primary" style="width: 100%; height: 45px; font-size: 15px; border-radius: 8px; border: none; background: #0f172a; color: #fff; cursor: pointer; font-weight: bold;">
                                Save Basic Info
                            </button>
                        </form>
                    </div>

                    <div class="nk-dash-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">
                        <h2 style="margin:0; font-size: 18px;">My Digital CVs</h2>
                        <?php if ($is_premium): ?>
                      <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold;">
                                 <?php echo count($saved_cvs); ?> / 5 Used
                             </span>
                          <?php endif; ?>
                        </div>
                        
                        <?php if (empty($saved_cvs)): ?>
                            <div style="text-align: center; padding: 30px 20px; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                <div style="font-size: 40px; margin-bottom: 10px;">📄</div>
                                <h3 style="margin: 0 0 10px 0; font-size: 16px;">No CV Created Yet</h3>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Build a modern, ATS-friendly resume using our AI-powered studio.</p>
                                
                                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" style="background: #10b981; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">✨ Open AI CV Studio</a>
                            </div>
                        <?php else: ?>
                        
                            <?php 
                            $active_cv_id = get_user_meta($user_id, 'nk_active_cv_id', true);
                            
                            // If they have CVs but no active one is set yet, default to their oldest/first one
                            if ( empty($active_cv_id) && !empty($saved_cvs) ) {
                                $active_cv_id = end($saved_cvs)->id;
                                update_user_meta($user_id, 'nk_active_cv_id', $active_cv_id);
                            }

                            foreach($saved_cvs as $index => $saved_cv): 
                                $is_active = ($saved_cv->id == $active_cv_id);
                            ?>
                                <div style="background: #ffffff; border: 1px solid <?php echo $is_active ? '#10b981' : '#e2e8f0'; ?>; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; transition: 0.2s;">
                                    <div>
                                        <?php 
                                        $cv_title = "Digital CV #" . ($index + 1);
                                        $exp_data = $wpdb->get_var($wpdb->prepare("SELECT section_data FROM {$wpdb->prefix}nk_cv_sections WHERE profile_id = %d AND section_type = 'experience'", $saved_cv->id));
                                        if($exp_data) {
                                            $exp_arr = json_decode($exp_data, true);
                                            if(!empty($exp_arr[0]['job_title'])) $cv_title = esc_html($exp_arr[0]['job_title']) . " CV";
                                        }
                                        ?>
                                        <h4 style="margin: 0 0 5px 0; font-size: 15px; color: #0f172a;"><?php echo $cv_title; ?></h4>
                                        
                                        <?php if ($is_active): ?>
                                            <span style="font-size: 11px; color: #16a34a; background: #dcfce7; padding: 2px 8px; border-radius: 4px; font-weight: bold;">✅ Active Public CV</span>
                                        <?php else: ?>
                                            <button type="button" class="nk-make-active-btn" data-cv-id="<?php echo esc_attr($saved_cv->id); ?>" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; cursor: pointer; transition: 0.2s;">Make Active</button>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="<?php echo esc_url(site_url('/cv/?cv_id=' . $saved_cv->id)); ?>" target="_blank" style="background: #f1f5f9; color: #475569; text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;">👁️ View</a>
                                        
                                        <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio&edit=' . $saved_cv->id)); ?>" style="background: #0A66C2; color: #fff; text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;">✏️ Edit</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (!$is_premium): ?>
                                <div style="margin-top: 25px; padding: 20px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; text-align: center;">
                                    <span style="font-size: 24px; display: block; margin-bottom: 10px;">🎯</span>
                                    <h4 style="margin: 0 0 8px 0; color: #92400e; font-size: 15px;">Want to target different jobs?</h4>
                                    <p style="margin: 0 0 15px 0; font-size: 12px; color: #b45309;">Premium users can create up to 5 different tailored CVs and switch between them instantly.</p>
                                    <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="background: #f59e0b; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block;">🔒 Unlock Targeted CVs</a>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio&cv_action=new')); ?>" style="display: block; text-align: center; width: 100%; background: #f8fafc; border: 1px dashed #cbd5e1; color: #0f172a; padding: 12px; border-radius: 8px; font-weight: bold; text-decoration: none; cursor: pointer; margin-top: 10px; box-sizing: border-box; transition: background 0.2s;">
                                    + Create New Targeted CV
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 🔴 ADDED: Interactive CV Switcher Logic
        document.querySelectorAll('.nk-make-active-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cvId = this.getAttribute('data-cv-id');
                const originalText = this.innerText;
                
                this.innerText = 'Switching...';
                this.disabled = true;

                let formData = new FormData();
                formData.append('action', 'nk_set_active_cv');
                formData.append('cv_id', cvId);
                formData.append('security', document.getElementById('nk_security').value);
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if(typeof nk_show_toast === 'function') nk_show_toast(data.data, 'success');
                        setTimeout(() => { window.location.reload(); }, 800); // Reload to show new active badge
                    } else {
                        alert(data.data); // Likely the Premium Paywall alert!
                        this.innerText = originalText;
                        this.disabled = false;
                    }
                });
            });
        });
        const form = document.getElementById('nk-profile-form');
        const submitBtn = document.getElementById('nk-profile-submit-btn');
        if(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const originalText = submitBtn.innerText;
                submitBtn.innerText = 'Saving...';
                submitBtn.disabled = true;
                let formData = new FormData(form);
                formData.append('action', 'nk_update_candidate_basic_profile');
                formData.append('security', document.getElementById('nk_security').value);
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if(typeof nk_show_toast === 'function') nk_show_toast(data.data, 'success');
                        setTimeout(() => { window.location.reload(); }, 1500);
                    } else {
                        alert(data.data);
                    }
                }).finally(() => { submitBtn.innerText = originalText; submitBtn.disabled = false; });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
remove_shortcode('nk_candidate_profile_edit');
add_shortcode('nk_candidate_profile_edit', 'nk_profile_edit_form_shortcode');

// 4. Standalone Profile Strength Widget
function nk_profile_strength_shortcode() {
    if ( ! is_user_logged_in() ) return ''; 
    $user_id = get_current_user_id();
    $percentage = nk_get_profile_completion( $user_id ); 
    ob_start();
    ?>
    <div class="nk-dash-card nk-profile-strength-widget" style="text-align: center; padding: 25px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <h4 style="margin: 0 0 12px 0; color: #111111; font-size: 16px; font-weight: 700;">Profile Completion Strength</h4>
        <div style="background: #e5e7eb; border-radius: 20px; height: 12px; width: 100%; overflow: hidden; margin-bottom: 12px;">
            <div style="background: #10b981; height: 100%; width: <?php echo esc_attr( $percentage ); ?>%; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); min-width: 5%;"></div>
        </div>
        <p style="color: #10b981; font-size: 15px; margin: 0; font-weight: 700; letter-spacing: 0.2px;">
            <?php echo esc_html( $percentage ); ?>% Completed
        </p>
    </div>
    <?php
    return ob_get_clean();
}
remove_shortcode( 'nk_profile_strength' );
add_shortcode( 'nk_profile_strength', 'nk_profile_strength_shortcode' );