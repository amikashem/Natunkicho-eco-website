<?php
/**
 * NatunKicho AI CV Studio - Main Builder UI
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $current_tab ) || $current_tab !== 'cv-studio' ) { return; }

$user_id = get_current_user_id();
global $wpdb;

$is_premium = function_exists('nk_is_user_premium') && nk_is_user_premium($user_id);

$cv_action = isset($_GET['cv_action']) ? sanitize_text_field($_GET['cv_action']) : '';
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

$profile = null;

if ( $cv_action !== 'new' ) {
    if ( $edit_id > 0 ) {
        $profile = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}nk_cv_profiles WHERE id = %d AND user_id = %d", $edit_id, $user_id ) );
    } else {
        $active_cv = get_user_meta($user_id, 'nk_active_cv_id', true);
        if ($active_cv) {
            $profile = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}nk_cv_profiles WHERE id = %d AND user_id = %d", $active_cv, $user_id ) );
        }
        if (!$profile) {
            $profile = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}nk_cv_profiles WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1", $user_id ) );
        }
    }
}

$current_profile_id = $profile ? $profile->id : 0;

$p_data = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'photo' => ''];
$s_data = ['summary' => ''];
$e_data = []; $edu_data = []; $skl_data = []; $cert_data = []; $lang_data = []; $ref_data = []; $act_data = [];

if ( $profile ) {
    $sections = $wpdb->get_results( $wpdb->prepare( "SELECT section_type, section_data FROM {$wpdb->prefix}nk_cv_sections WHERE profile_id = %d", $profile->id ) );
    foreach ( $sections as $sec ) {
        if ( $sec->section_type === 'personal_info' ) $p_data = json_decode( $sec->section_data, true );
        if ( $sec->section_type === 'summary' ) $s_data = json_decode( $sec->section_data, true );
        if ( $sec->section_type === 'experience' ) $e_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'education' ) $edu_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'skills' ) $skl_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'certifications' ) $cert_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'languages' ) $lang_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'references' ) $ref_data = json_decode( $sec->section_data, true ) ?: [];
        if ( $sec->section_type === 'activities' ) $act_data = json_decode( $sec->section_data, true ) ?: [];
    }
}
?>

<div class="nk-cv-studio-wrapper" style="display: grid; grid-template-columns: 60% 40%; gap: 30px; padding-top: 10px; width: 100%; max-width: 100%; box-sizing: border-box; align-items: start;">
    
    <div class="nk-cv-builder-panel" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
         <a href="?tab=profile" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px; font-size: 13px; font-weight: bold; color: #0f172a; text-decoration: none; padding: 8px 16px; background: #f8fafc; border-radius: 6px; border: 1px solid #cbd5e1; transition: background 0.2s;">
            <span>←</span> Exit Studio & Return to Dashboard
        </a>
        
        <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px;">
                CV Builder 
                <?php if($is_premium): ?>
                    <span style="background: #fffbeb; border: 1px solid #fde68a; color: #d97706; font-size: 12px; padding: 4px 8px; border-radius: 6px;">👑 Premium Pro</span>
                <?php else: ?>
                    <span style="font-size: 14px; color: #64748b; font-weight: normal;">(Free)</span>
                <?php endif; ?>
            </h2>
            <?php if($cv_action === 'new'): ?>
                <div style="margin-bottom: 5px;"><span style="background: #dcfce7; color: #166534; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: bold;">✨ Drafting New CV</span></div>
            <?php endif; ?>
            <p style="color: #64748b; font-size: 13px; margin: 0;">Fill in your details below. Your progress is saved securely.</p>
            
            <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="color: #fff;">
                <h4 style="margin: 0 0 5px 0; font-size: 15px; display: flex; align-items: center; gap: 8px;">✨ AI Full CV Audit & Rewrite</h4>
                <p style="margin: 0; font-size: 12px; color: #94a3b8;">Instantly analyze, reorganize, and professionally rewrite your entire CV.</p>
            </div>
            <button type="button" class="<?php echo $is_premium ? 'nk-run-audit-btn' : 'nk-locked-audit-btn'; ?>" style="background: <?php echo $is_premium ? '#10b981' : '#f59e0b'; ?>; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px; transition: 0.2s;">
                <?php echo $is_premium ? 'Run Full Audit' : '🔒 Upgrade to Unlock'; ?>
            </button>
        </div>
        </div>

        <?php 
        $template_file = get_stylesheet_directory() . '/inc/cv-builder/cv-premium-templates.php';
        if ( file_exists( $template_file ) ) require_once $template_file; 
        ?>
        
        <form id="nk-cv-form">
            <input type="hidden" id="nk_cv_nonce" value="<?php echo wp_create_nonce('nk_cv_builder_nonce'); ?>">
            <input type="hidden" id="nk_cv_profile_id" value="<?php echo esc_attr($current_profile_id); ?>">

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">1. Personal Details</h3>
                <div style="margin-bottom: 15px; padding: 15px; border: 1px dashed #cbd5e1; border-radius: 8px; text-align: center; background: #f8fafc;">
                    <label style="display: block; font-size: 13px; font-weight: bold; color: #475569; margin-bottom: 10px;">Upload Profile Photo (Optional)</label>
                    <input type="file" id="nk-photo-upload" accept="image/*" style="font-size: 12px; width: 100%;">
                    <input type="hidden" name="photo_data" id="nk-photo-data" value="<?php echo esc_attr($p_data['photo'] ?? ''); ?>">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <input type="text" name="first_name" value="<?php echo esc_attr($p_data['first_name']); ?>" placeholder="First Name" class="nk-cv-input">
                    <input type="text" name="last_name" value="<?php echo esc_attr($p_data['last_name']); ?>" placeholder="Last Name" class="nk-cv-input">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <input type="email" name="email" value="<?php echo esc_attr($p_data['email']); ?>" placeholder="Email Address" class="nk-cv-input">
                    <input type="text" name="phone" value="<?php echo esc_attr($p_data['phone']); ?>" placeholder="Phone Number" class="nk-cv-input">
                </div>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                    <h3 style="font-size: 1.1rem; color: #334155; margin: 0;">2. Professional Summary</h3>
                    <button type="button" id="nk-trigger-ai-summary" style="background: #eef4ff; color: #0A66C2; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; transition: 0.2s;">✨ Generate with AI</button>
                </div>
                <textarea name="summary" id="nk-summary-textarea" rows="4" placeholder="Highlight your experience..." class="nk-cv-input" style="resize: vertical;"><?php echo esc_textarea($s_data['summary']); ?></textarea>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">3. Work Experience</h3>
                <div id="nk-experience-container">
                    <?php foreach ( $e_data as $exp ) : ?>
                        <div class="nk-exp-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <input type="text" class="exp-title nk-cv-input" value="<?php echo esc_attr($exp['job_title']); ?>" placeholder="Job Title" style="margin-bottom:10px;">
                            <input type="text" class="exp-company nk-cv-input" value="<?php echo esc_attr($exp['company']); ?>" placeholder="Company" style="margin-bottom:10px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <input type="text" class="exp-start nk-cv-input" value="<?php echo esc_attr($exp['start_date']); ?>" placeholder="Start Date">
                                <input type="text" class="exp-end nk-cv-input" value="<?php echo esc_attr($exp['end_date']); ?>" placeholder="End Date">
                            </div>
                            <textarea class="exp-details nk-cv-input" rows="3" placeholder="Responsibilities..."><?php echo esc_textarea($exp['details']); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-exp-btn" class="nk-add-btn">+ Add Work Experience</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">4. Education</h3>
                <div id="nk-education-container">
                    <?php foreach ( $edu_data as $edu ) : ?>
                        <div class="nk-edu-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <input type="text" class="edu-degree nk-cv-input" value="<?php echo esc_attr($edu['degree']); ?>" placeholder="Degree (e.g., BSc Hospitality)" style="margin-bottom:10px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="text" class="edu-inst nk-cv-input" value="<?php echo esc_attr($edu['institution']); ?>" placeholder="Institution">
                                <input type="text" class="edu-year nk-cv-input" value="<?php echo esc_attr($edu['year']); ?>" placeholder="Year">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-edu-btn" class="nk-add-btn">+ Add Education</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="font-size: 1.1rem; color: #334155; margin: 0;">5. Skills</h3>
                    <button type="button" id="nk-trigger-ai-skills" style="background: none; color: #0A66C2; border: none; font-size: 11px; font-weight: bold; cursor: pointer;">✨ AI Suggest</button>
                </div>
                <div id="nk-skills-container">
                    <?php foreach ( $skl_data as $skl ) : ?>
                        <div class="nk-skl-block" style="display:flex; gap:10px; margin-bottom:10px;">
                            <input type="text" class="skl-name nk-cv-input" value="<?php echo esc_attr($skl['skill_name']); ?>" placeholder="Skill (e.g., Team Leadership)">
                            <button type="button" class="nk-remove-btn" style="position:static; padding:10px;">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-skl-btn" class="nk-add-btn">+ Add Skill</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">6. Languages</h3>
                <div id="nk-language-container">
                    <?php foreach ( $lang_data as $lang ) : ?>
                        <div class="nk-lang-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="text" class="lang-name nk-cv-input" value="<?php echo esc_attr($lang['language']); ?>" placeholder="Language (e.g. English)">
                                <input type="text" class="lang-prof nk-cv-input" value="<?php echo esc_attr($lang['proficiency']); ?>" placeholder="Fluency (e.g. Native)">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-lang-btn" class="nk-add-btn">+ Add Language</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">7. Certifications</h3>
                <div id="nk-cert-container">
                    <?php foreach ( $cert_data as $cert ) : ?>
                        <div class="nk-cert-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                                <input type="text" class="cert-name nk-cv-input" value="<?php echo esc_attr($cert['cert_name']); ?>" placeholder="Certification (e.g. HACCP)">
                                <input type="text" class="cert-year nk-cv-input" value="<?php echo esc_attr($cert['year']); ?>" placeholder="Year">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-cert-btn" class="nk-add-btn">+ Add Certification</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">8. Extra Activities</h3>
                <div id="nk-act-container">
                    <?php foreach ( $act_data as $act ) : ?>
                        <div class="nk-act-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <input type="text" class="act-name nk-cv-input" value="<?php echo esc_attr($act['activity_name']); ?>" placeholder="Activity / Role" style="margin-bottom:10px;">
                            <input type="text" class="act-details nk-cv-input" value="<?php echo esc_attr($act['details']); ?>" placeholder="Short description">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-act-btn" class="nk-add-btn">+ Add Activity</button>
            </div>

            <div class="nk-cv-section" style="margin-bottom: 35px;">
                <h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 15px;">9. References (Optional)</h3>
                <div id="nk-ref-container">
                    <?php foreach ( $ref_data as $ref ) : ?>
                        <div class="nk-ref-block nk-repeat-block">
                            <button type="button" class="nk-remove-btn">Remove</button>
                            <input type="text" class="ref-name nk-cv-input" value="<?php echo esc_attr($ref['name']); ?>" placeholder="Reference Name" style="margin-bottom:10px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="text" class="ref-comp nk-cv-input" value="<?php echo esc_attr($ref['company']); ?>" placeholder="Company & Title">
                                <input type="text" class="ref-cont nk-cv-input" value="<?php echo esc_attr($ref['contact']); ?>" placeholder="Email / Phone">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="nk-add-ref-btn" class="nk-add-btn">+ Add Reference</button>
            </div>

            <div style="border-top: 2px solid #f1f5f9; padding-top: 20px;">
                <button type="button" id="nk-save-cv" style="width: 100%; background: #0A66C2; color: #fff; border: none; padding: 16px; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer;">
                    💾 Save CV Data
                </button>
                <p id="nk-save-status" style="text-align: center; font-size: 13px; font-weight: bold; margin-top: 10px; display: none;"></p>
            </div>
        </form>
        <div id="nk-ai-audit-modal" style="display:none; position:fixed; bottom: 30px; right: 30px; width: 450px; max-height: 80vh; background: #ffffff; border-radius: 12px; border: 2px solid #0A66C2; box-shadow: 0 20px 40px rgba(0,0,0,0.3); z-index: 999999; flex-direction: column; overflow: hidden;">
    
    <div style="background: #0A66C2; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 8px;">✨ AI Executive Recruiter</h3>
        <button type="button" class="nk-ai-close-btn" style="background: rgba(255,255,255,0.2); border: none; color: #fff; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 4px; padding: 4px 10px;">Close ✕</button>
    </div>

    <div style="padding: 20px; overflow-y: auto; flex: 1;">
        <div id="nk-ai-audit-loading" style="text-align: center; padding: 30px 0;">
            <div style="font-size: 40px; margin-bottom: 15px; animation: pulse 1.5s infinite;">🤖</div>
            <h4 style="color: #0A66C2; margin:0;">Analyzing your career profile...</h4>
            <p style="font-size: 12px; color: #64748b;">This takes about 10 seconds.</p>
        </div>

        <div id="nk-ai-audit-results" style="display:none; font-size: 13px; line-height: 1.6; color: #334155; user-select: text;">
            </div>
    </div>
</div>
<style>@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
</style>
    </div>

    <div class="nk-cv-preview-panel" style="background: #334155; border-radius: 12px; border: 1px solid #1e293b; display: flex; flex-direction: column; position: sticky; top: 20px; height: calc(100vh - 60px); overflow: hidden; box-sizing: border-box; z-index: 1;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; background: #1e293b; border-bottom: 1px solid #0f172a; z-index: 10; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: #ffffff;">Live A4 Preview</h2>
                <div style="background: #334155; padding: 4px 8px; border-radius: 6px; display: flex; gap: 5px;">
                    <button type="button" id="nk-zoom-out" style="background: #475569; color: white; border: none; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-weight: bold;">-</button>
                    <button type="button" id="nk-zoom-in" style="background: #475569; color: white; border: none; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-weight: bold;">+</button>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" id="nk-download-docx-btn" class="<?php echo $is_premium ? 'unlocked' : 'locked'; ?>" style="background: #f59e0b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; transition: 0.2s;">
                    <?php echo $is_premium ? '📄 Download DOCX' : '🔒 Download DOCX'; ?>
                </button>
                <button type="button" id="nk-download-pdf-btn" style="background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; transition: 0.2s;">⬇️ Download PDF</button>
            </div>
        </div>
        
        <div id="nk-reader-scroll" style="flex: 1; overflow: auto; padding: 20px 0; display: flex; justify-content: center; align-items: flex-start; background: #475569; width: 100%; box-sizing: border-box;">
            <div id="nk-canvas-sizer" style="width: 100%; display: flex; justify-content: center; transform-origin: top center;">
                
                <div id="nk-cv-canvas" class="nk-cv-canvas template-modern" style="width: 794px; min-width: 794px; min-height: 1123px; background-color: #ffffff; background-image: linear-gradient(to bottom, transparent 1121px, #94a3b8 1121px, #94a3b8 1123px); background-size: 100% 1123px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); box-sizing: border-box; transform-origin: top center; transition: transform 0.2s ease; display: table;">
                    
                    <div style="display: table-cell; width: 33%; vertical-align: top; background: #f8fafc; padding: 40px 25px; border-right: 1px solid #e2e8f0; box-sizing: border-box;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <img id="prev-photo" src="<?php echo !empty($p_data['photo']) ? esc_attr($p_data['photo']) : 'https://via.placeholder.com/150'; ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); <?php echo empty($p_data['photo']) ? 'display:none;' : ''; ?>">
                        </div>
                        <h1 style="margin: 0 0 15px 0; font-size: 26px; color: #0f172a; text-transform: uppercase; font-weight: 800; line-height: 1.2; text-align: center; white-space: nowrap; overflow: hidden; letter-spacing: 1px;">
                             <span id="prev-fname"><?php echo esc_html($p_data['first_name'] ?: 'YOUR'); ?></span> 
                         <span id="prev-lname"><?php echo esc_html($p_data['last_name'] ?: 'NAME'); ?></span>
</h1>
                        <div style="margin-bottom: 30px; font-size: 12px; color: #475569; text-align: center; line-height: 1.8; word-wrap: break-word;">
                            <div id="prev-email"><?php echo $p_data['email'] ? esc_html($p_data['email']) : 'email@example.com'; ?></div>
                            <div id="prev-phone"><?php echo $p_data['phone'] ? esc_html($p_data['phone']) : '+123456789'; ?></div>
                        </div>
                        <h3 class="prev-heading-side">Skills</h3>
                        <div id="prev-skills-list" style="margin-bottom: 30px;"></div>
                        <h3 class="prev-heading-side">Languages</h3>
                        <div id="prev-langs-list" style="margin-bottom: 30px;"></div>
                        <h3 class="prev-heading-side">Certifications</h3>
                        <div id="prev-certs-list" style="margin-bottom: 30px;"></div>
                    </div>

                    <div style="display: table-cell; width: 67%; vertical-align: top; background: #ffffff; padding: 40px 35px; box-sizing: border-box;">
                        <h3 class="prev-heading-main">Professional Summary</h3>
                        <p id="prev-summary" style="font-size: 13px; line-height: 1.6; color: #334155; margin-bottom: 30px; white-space: pre-wrap; word-wrap: break-word;"><?php echo $s_data['summary'] ? esc_html($s_data['summary']) : 'Your professional summary will appear here...'; ?></p>

                        <h3 class="prev-heading-main">Work Experience</h3>
                        <div id="prev-experience-list" style="margin-bottom: 30px; word-wrap: break-word;"></div>
                        <h3 class="prev-heading-main">Education</h3>
                        <div id="prev-education-list" style="margin-bottom: 30px; word-wrap: break-word;"></div>
                        <h3 class="prev-heading-main">Extra Activities</h3>
                        <div id="prev-activities-list" style="margin-bottom: 30px; word-wrap: break-word;"></div>
                        <h3 class="prev-heading-main">References</h3>
                        <div id="prev-refs-list" style="margin-bottom: 30px; word-wrap: break-word;"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="nk-ai-summary-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:30px; border-radius:12px; width:100%; max-width:600px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 10px 0; font-size:1.4rem; display:flex; align-items:center; gap:8px;">✨ Magic AI Writer</h3>
        
        <div id="nk-ai-summary-step-1">
            <p style="color:#64748b; font-size:13px; margin-bottom:20px;">Provide a target job title. We will analyze your work experience automatically and generate a powerful summary.</p>
            <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:8px;">Target Job Title</label>
            <input type="text" id="nk-ai-target-role" placeholder="e.g. Hotel Manager, Head Chef" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; margin-bottom:20px; box-sizing:border-box;">
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="nk-ai-close-btn" style="background:#f1f5f9; color:#475569; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">Cancel</button>
                <button type="button" id="nk-ai-run-summary" style="background:#10b981; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;">✨ Generate Summary</button>
            </div>
        </div>

        <div id="nk-ai-summary-step-2" style="display:none;">
            <p style="color:#64748b; font-size:13px; margin-bottom:15px;">Review your generated summary below. You can manually edit it here, regenerate a new one, or accept it.</p>
            <textarea id="nk-ai-summary-preview" rows="6" style="width:100%; padding:15px; border:2px solid #0A66C2; border-radius:8px; margin-bottom:20px; box-sizing:border-box; font-size:14px; line-height:1.6;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="nk-ai-close-btn" style="background:#f1f5f9; color:#475569; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">Cancel</button>
                <button type="button" id="nk-ai-regenerate-summary" style="background:#e2e8f0; color:#0f172a; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">🔄 Regenerate</button>
                <button type="button" id="nk-ai-accept-summary" style="background:#0A66C2; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;">✅ Accept & Apply</button>
            </div>
        </div>
    </div>
</div>

<div id="nk-ai-skills-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:30px; border-radius:12px; width:100%; max-width:500px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 10px 0; font-size:1.4rem; display:flex; align-items:center; gap:8px;">🎯 AI Skill Discovery</h3>
        <p style="color:#64748b; font-size:13px; margin-bottom:20px;">Tell us what job you are applying for, and AI will instantly generate the top 10 most in-demand skills for that role.</p>
        <label style="display:block; font-size:13px; font-weight:bold; margin-bottom:8px;">Target Job Title</label>
        <input type="text" id="nk-ai-skills-role" placeholder="e.g. Concierge, F&B Director" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; margin-bottom:20px; box-sizing:border-box;">
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="nk-ai-close-btn" style="background:#f1f5f9; color:#475569; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">Cancel</button>
            <button type="button" id="nk-ai-run-skills" style="background:#0A66C2; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;">✨ Suggest Skills</button>
        </div>
    </div>
</div>

<style>
.nk-cv-studio-wrapper { display: grid; grid-template-columns: 60% 40%; gap: 30px; padding-top: 10px; width: 100%; max-width: 100%; box-sizing: border-box; align-items: start; }
.nk-cv-input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
.nk-cv-input:focus { outline: none; border-color: #0A66C2; box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1); }
.nk-repeat-block { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 15px; position: relative; }
.nk-remove-btn { position: absolute; top: 10px; right: 10px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; font-weight: bold; }
.nk-add-btn { width: 100%; background: #ffffff; color: #0f172a; border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
.nk-add-btn:hover { background: #f1f5f9; }
.prev-heading-main { font-size: 15px; text-transform: uppercase; color: #0f172a; margin: 0 0 15px 0; font-weight: 800; letter-spacing: 1px; border-bottom: 2px solid #0f172a; padding-bottom: 5px; }
.prev-heading-side { font-size: 13px; text-transform: uppercase; color: #0f172a; margin: 0 0 12px 0; font-weight: 800; letter-spacing: 1px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
#nk-reader-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
#nk-reader-scroll::-webkit-scrollbar-track { background: #1e293b; }
#nk-reader-scroll::-webkit-scrollbar-thumb { background: #64748b; border-radius: 4px; }

@media (max-width: 1100px) {
    .nk-cv-studio-wrapper { grid-template-columns: 1fr !important; }
    .nk-cv-preview-panel { position: static !important; height: 800px !important; }
}
@media (max-width: 768px) {
    .nk-cv-responsive-grid { grid-template-columns: 1fr !important; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
jQuery(document).ready(function($) {
    
    // --- 1. MANUAL ZOOM LOGIC ---
    let currentZoom = 0.65;
    function applyZoom() {
        $('#nk-cv-canvas').css('transform', `scale(${currentZoom})`);
        $('#nk-canvas-sizer').css('height', (1123 * currentZoom) + 'px');
    }
    applyZoom(); 

    $('#nk-zoom-in').click(function() { if(currentZoom < 1.5) { currentZoom += 0.1; applyZoom(); } });
    $('#nk-zoom-out').click(function() { if(currentZoom > 0.3) { currentZoom -= 0.1; applyZoom(); } });

    // --- 2. Photo Upload ---
    $('#nk-photo-upload').on('change', function(e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#nk-photo-data').val(event.target.result);
                $('#prev-photo').attr('src', event.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // --- 3. Live Syncing ---
    $('input[name="first_name"]').on('input', function() { $('#prev-fname').text($(this).val() || 'YOUR'); });
    $('input[name="last_name"]').on('input', function() { $('#prev-lname').text($(this).val() || 'NAME'); });
    $('input[name="email"]').on('input', function() { $('#prev-email').text($(this).val() || 'email@example.com'); });
    $('input[name="phone"]').on('input', function() { $('#prev-phone').text($(this).val() || '+123456789'); });
    $('textarea[name="summary"]').on('input', function() { $('#prev-summary').text($(this).val() || 'Your professional summary will appear here...'); });

    // --- 4. HTML Generator ---
    function updatePreview() {
        function escapeHtml(text) { return $('<div>').text(text).html(); }

        let expHtml = '';
        $('.nk-exp-block').each(function() {
            let title = escapeHtml($(this).find('.exp-title').val() || 'Job Title');
            let comp = escapeHtml($(this).find('.exp-company').val() || 'Company');
            let start = escapeHtml($(this).find('.exp-start').val() || 'Start');
            let end = escapeHtml($(this).find('.exp-end').val() || 'End');
            let desc = escapeHtml($(this).find('.exp-details').val());
            expHtml += `
            <div style="margin-bottom: 20px; position: relative; padding-left: 15px; border-left: 2px solid #cbd5e1;" class="nk-exp-block-border">
                <div class="nk-exp-dot" style="position: absolute; left: -5px; top: 6px; width: 8px; height: 8px; border-radius: 50%; background: #0A66C2;"></div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                    <strong style="font-size: 14.5px; color: #0f172a;">${title}</strong>
                    <span style="font-size: 11px; color: #0f172a; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">${start} - ${end}</span>
                </div>
                <div style="font-size: 13px; color: #0A66C2; font-weight: 600; margin-bottom: 8px;">${comp}</div>
                <p style="font-size: 12.5px; margin: 0; line-height: 1.6; color: #334155; white-space: pre-wrap;">${desc}</p>
            </div>`;
        });
        $('#prev-experience-list').html(expHtml);

        let eduHtml = '';
        $('.nk-edu-block').each(function() {
            let deg = escapeHtml($(this).find('.edu-degree').val() || 'Degree');
            let inst = escapeHtml($(this).find('.edu-inst').val() || 'Institution');
            let yr = escapeHtml($(this).find('.edu-year').val() || 'Year');
            eduHtml += `
            <div style="margin-bottom: 15px; position: relative; padding-left: 15px; border-left: 2px solid #cbd5e1;" class="nk-exp-block-border">
                <div class="nk-exp-dot" style="position: absolute; left: -5px; top: 6px; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                <strong style="font-size: 14px; color: #0f172a; display: block; margin-bottom: 3px;">${deg}</strong>
                <span style="font-size: 12.5px; color: #475569;">${inst} &bull; ${yr}</span>
            </div>`;
        });
        $('#prev-education-list').html(eduHtml);

        let sklHtml = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
        $('.nk-skl-block').each(function() { 
            let skill = escapeHtml($(this).find('.skl-name').val());
            if(skill) sklHtml += `<span style="background: #e2e8f0; color: #0f172a; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${skill}</span>`; 
        });
        $('#prev-skills-list').html(sklHtml + '</div>');

        let certHtml = '<ul style="padding-left:15px; margin:0; font-size:12px; color:#475569; line-height:1.6;">';
        $('.nk-cert-block').each(function() { 
            let cName = escapeHtml($(this).find('.cert-name').val()); let cYr = escapeHtml($(this).find('.cert-year').val());
            if(cName) certHtml += `<li style="margin-bottom:4px;"><strong style="color:#0f172a;">${cName}</strong><br>${cYr}</li>`; 
        });
        $('#prev-certs-list').html(certHtml + '</ul>');

        let langHtml = '<ul style="padding-left:15px; margin:0; font-size:12px; color:#475569; line-height:1.6;">';
        $('.nk-lang-block').each(function() { 
            let lName = escapeHtml($(this).find('.lang-name').val()); let lProf = escapeHtml($(this).find('.lang-prof').val());
            if(lName) langHtml += `<li style="margin-bottom:4px;"><strong style="color:#0f172a;">${lName}</strong><br>${lProf}</li>`; 
        });
        $('#prev-langs-list').html(langHtml + '</ul>');

        let actHtml = '';
        $('.nk-act-block').each(function() {
            let aName = escapeHtml($(this).find('.act-name').val()); let aDesc = escapeHtml($(this).find('.act-details').val());
            if(aName) actHtml += `<div style="margin-bottom:12px;"><strong style="font-size:13.5px; color:#0f172a;">${aName}</strong><p style="font-size:12.5px; color:#475569; margin:2px 0 0 0; white-space: pre-wrap;">${aDesc}</p></div>`;
        });
        $('#prev-activities-list').html(actHtml);

        let refHtml = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">';
        $('.nk-ref-block').each(function() {
            let rName = escapeHtml($(this).find('.ref-name').val()); let rComp = escapeHtml($(this).find('.ref-comp').val()); let rCont = escapeHtml($(this).find('.ref-cont').val());
            if(rName) refHtml += `<div><strong style="font-size:13.5px; color:#0f172a; display:block;">${rName}</strong><span style="font-size:12px; color:#64748b;">${rComp}<br>${rCont}</span></div>`;
        });
        $('#prev-refs-list').html(refHtml + '</div>');
        
        applyZoom(); 
    }
    updatePreview(); 
    $(document).on('input', '.nk-cv-input', function() { updatePreview(); });

    // --- 5. AJAX Save ---
    $('#nk-save-cv').on('click', function(e) {
        e.preventDefault();
        let $btn = $(this); let $status = $('#nk-save-status');
        $btn.text('💾 Saving...').css('opacity', '0.7'); $status.hide();

        let formData = {
            action: 'nk_save_multi_cv_data', 
            security: $('#nk_cv_nonce').val(),
            cv_profile_id: $('#nk_cv_profile_id').val(),
            photo_data: $('#nk-photo-data').val(),
            first_name: $('input[name="first_name"]').val(), last_name: $('input[name="last_name"]').val(),
            email: $('input[name="email"]').val(), phone: $('input[name="phone"]').val(), summary: $('textarea[name="summary"]').val(),
            
            experience: $('.nk-exp-block').map(function(){ return { job_title: $(this).find('.exp-title').val(), company: $(this).find('.exp-company').val(), start_date: $(this).find('.exp-start').val(), end_date: $(this).find('.exp-end').val(), details: $(this).find('.exp-details').val() }; }).get(),
            education: $('.nk-edu-block').map(function(){ return { degree: $(this).find('.edu-degree').val(), institution: $(this).find('.edu-inst').val(), year: $(this).find('.edu-year').val() }; }).get(),
            skills: $('.nk-skl-block').map(function(){ return { skill_name: $(this).find('.skl-name').val() }; }).get(),
            languages: $('.nk-lang-block').map(function(){ return { language: $(this).find('.lang-name').val(), proficiency: $(this).find('.lang-prof').val() }; }).get(),
            certifications: $('.nk-cert-block').map(function(){ return { cert_name: $(this).find('.cert-name').val(), year: $(this).find('.cert-year').val() }; }).get(),
            activities: $('.nk-act-block').map(function(){ return { activity_name: $(this).find('.act-name').val(), details: $(this).find('.act-details').val() }; }).get(),
            references: $('.nk-ref-block').map(function(){ return { name: $(this).find('.ref-name').val(), company: $(this).find('.ref-comp').val(), contact: $(this).find('.ref-cont').val() }; }).get()
        };

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function(response) {
            $btn.text('💾 Save CV Data').css('opacity', '1');
            $status.text(response.data.message).css('color', response.success ? '#16a34a' : '#dc2626').fadeIn();
            
            if (response.success && response.data.profile_id) {
                $('#nk_cv_profile_id').val(response.data.profile_id);
                window.history.replaceState(null, null, '?tab=cv-studio&edit=' + response.data.profile_id);
            }
            setTimeout(() => { $status.fadeOut(); }, 3000);
        });
    });

    // --- 6. AI INTEGRATION JAVASCRIPT ---
    $('.nk-ai-close-btn').click(function() {
        $('#nk-ai-summary-modal').fadeOut();
        $('#nk-ai-skills-modal').fadeOut();
        $('#nk-ai-audit-modal').fadeOut();
        $('#nk-ai-summary-step-2').hide();
        $('#nk-ai-summary-step-1').show();
        $('#nk-ai-target-role').val('');
        $('#nk-ai-summary-preview').val('');
    });

    $('#nk-trigger-ai-summary').click(function() {
        $('#nk-ai-summary-step-2').hide();
        $('#nk-ai-summary-step-1').show();
        $('#nk-ai-summary-modal').css('display', 'flex').hide().fadeIn();
    });

    function fetchAiSummary(buttonElement) {
        let role = $('#nk-ai-target-role').val();
        if(!role) { alert('Please enter a target job title.'); return; }

        let $btn = $(buttonElement);
        let originalText = $btn.text();
        $btn.text('⏳ Generating...').prop('disabled', true).css('opacity', '0.7');

        let expContext = '';
        $('.nk-exp-block').each(function() {
            let t = $(this).find('.exp-title').val();
            let c = $(this).find('.exp-company').val();
            if(t && c) expContext += t + ' at ' + c + '. ';
        });

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'nk_generate_ai_summary', security: $('#nk_cv_nonce').val(), target_role: role, experience: expContext
        }, function(response) {
            if(response.success) {
                $('#nk-ai-summary-step-1').hide();
                $('#nk-ai-summary-step-2').fadeIn();
                $('#nk-ai-summary-preview').val(response.data);
            } else {
                alert(response.data);
            }
            $btn.text(originalText).prop('disabled', false).css('opacity', '1');
        });
    }

    $('#nk-ai-run-summary').click(function() { fetchAiSummary(this); });
    $('#nk-ai-regenerate-summary').click(function() { fetchAiSummary(this); });

    $('#nk-ai-accept-summary').click(function() {
        let finalSummary = $('#nk-ai-summary-preview').val();
        $('#nk-summary-textarea').val(finalSummary).trigger('input');
        $('#nk-ai-summary-modal').fadeOut();
        setTimeout(() => { $('#nk-ai-summary-step-2').hide(); $('#nk-ai-summary-step-1').show(); $('#nk-ai-summary-preview').val(''); }, 500);
    });

    $('#nk-trigger-ai-skills').click(function() { $('#nk-ai-skills-modal').css('display', 'flex').hide().fadeIn(); });

    $('#nk-ai-run-skills').click(function() {
        let role = $('#nk-ai-skills-role').val();
        if(!role) { alert('Please enter a target job title.'); return; }

        let $btn = $(this);
        $btn.text('⏳ Scanning...').prop('disabled', true).css('opacity', '0.7');

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'nk_generate_ai_skills', security: $('#nk_cv_nonce').val(), target_role: role
        }, function(response) {
            if(response.success) {
                let skills = response.data; 
                skills.forEach(function(skill) {
                    if(skill) {
                        $('#nk-skills-container').append(`
                            <div class="nk-skl-block" style="display:flex; gap:10px; margin-bottom:10px;">
                                <input type="text" class="skl-name nk-cv-input" value="${skill}" placeholder="Skill">
                                <button type="button" class="nk-remove-btn" style="position:static; padding:10px;">X</button>
                            </div>
                        `);
                    }
                });
                updatePreview(); 
                $('#nk-ai-skills-modal').fadeOut();
            } else { alert(response.data); }
            $btn.text('✨ Suggest Skills').prop('disabled', false).css('opacity', '1');
        });
    });

   // ==========================================
    // PREMIUM FEATURE: AI FULL CV AUDIT
    // ==========================================
    $('.nk-locked-audit-btn').click(function() {
        alert("🔒 Premium Feature: The AI Full CV Audit is reserved for Premium Pro members. Upgrade now to have an AI Executive Recruiter rewrite your resume!");
        window.location.href = '/pricing/';
    });

    $('.nk-run-audit-btn').click(function() {
        $('#nk-ai-audit-modal').css('display', 'flex').hide().fadeIn();
        $('#nk-ai-audit-loading').show();
        $('#nk-ai-audit-results').hide();

        // Gather all CV data to send to AI
        let summary = $('textarea[name="summary"]').val();
        let expContext = '';
        $('.nk-exp-block').each(function() {
            expContext += "Job: " + $(this).find('.exp-title').val() + " at " + $(this).find('.exp-company').val() + " | Responsibilities: " + $(this).find('.exp-details').val() + " \n";
        });
        let skillContext = '';
        $('.nk-skl-block').each(function() { skillContext += $(this).find('.skl-name').val() + ", "; });

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'nk_generate_ai_cv_audit',
            security: $('#nk_cv_nonce').val(),
            summary: summary,
            experience: expContext,
            skills: skillContext
        }, function(response) {
            $('#nk-ai-audit-loading').hide();
            if(response.success) {
                $('#nk-ai-audit-results').html(response.data).fadeIn();
            } else {
                $('#nk-ai-audit-results').html('<p style="color:red;">Error: ' + response.data + '</p>').fadeIn();
            }
        });
    });

    // ==========================================
    // 8. THE PDF EXPORT ENGINE 
    // ==========================================
    $('#nk-download-pdf-btn').click(async function() {
        let $btn = $(this);
        let originalText = $btn.text();
        $btn.text('⏳ Generating PDF...').prop('disabled', true).css('opacity', '0.7');

        let originalCanvas = document.getElementById('nk-cv-canvas');
        let clone = originalCanvas.cloneNode(true);
        clone.id = 'nk-print-clone';

        clone.querySelectorAll('.prev-heading-main, .prev-heading-side, h1').forEach(h => h.style.letterSpacing = 'normal');

        $(clone).css({
            'position': 'fixed', 'top': '0', 'left': '0', 'z-index': '999999', 
            'width': '794px', 'height': '1123px', 'background-color': '#ffffff',
            'background-image': 'none', 'transform': 'none', 'box-shadow': 'none',
            'margin': '0', 'padding': '0', 'overflow': 'visible'
        });

        let isClassic = originalCanvas.classList.contains('template-crisp');
        if (!isClassic) {
            $(clone).children().eq(0).css({ 'display': 'block', 'float': 'left', 'width': '262px', 'height': '1123px', 'box-sizing': 'border-box' });
            $(clone).children().eq(1).css({ 'display': 'block', 'float': 'left', 'width': '532px', 'height': '1123px', 'box-sizing': 'border-box' });
        }

        document.body.appendChild(clone);

        try {
            const canvas = await html2canvas(clone, { scale: 2, useCORS: true, backgroundColor: '#ffffff', width: 794, height: 1123 });
            const imgData = canvas.toDataURL('image/jpeg', 1.0);
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);
            
            let fName = $('#prev-fname').text() || 'Candidate';
            let lName = $('#prev-lname').text() || 'CV';
            pdf.save(fName.trim() + '_' + lName.trim() + '_CV.pdf');
        } catch (err) {
            alert("An error occurred. Please try again.");
        } finally {
            document.body.removeChild(clone);
            $btn.text(originalText).prop('disabled', false).css('opacity', '1');
        }
    });

    // ==========================================
    // 9. THE ATS-CLEAN DOCX EXPORT ENGINE
    // ==========================================
    $('.locked').click(function(e) {
        if (this.id === 'nk-download-docx-btn') {
            alert("🔒 Premium Feature: Downloading your CV in fully editable Word format is available for Premium members only.");
            window.location.href = '/pricing/';
        }
    });

    $('.unlocked').click(function(e) {
        if (this.id === 'nk-download-docx-btn') {
            // DOCX requires clean, basic HTML for ATS systems. We strip complex CSS layouts.
            let content = document.getElementById('nk-cv-canvas').innerHTML;
            let header = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>ATS Friendly CV</title><style>body { font-family: Arial, sans-serif; color: #000; } h1, h3 { color: #000; } div { margin-bottom: 10px; }</style></head><body>";
            let footer = "</body></html>";
            
            // Clean up the HTML to be Word-friendly
            let cleanHTML = content.replace(/float: left;/g, '')
                                   .replace(/width: 33%;/g, 'width: 100%;')
                                   .replace(/width: 67%;/g, 'width: 100%;')
                                   .replace(/display: table-cell;/g, 'display: block;');
                                   
            let sourceHTML = header + cleanHTML + footer;
            
            let source = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(sourceHTML);
            let fileDownload = document.createElement("a");
            document.body.appendChild(fileDownload);
            fileDownload.href = source;
            fileDownload.download = 'ATS_Friendly_CV.doc';
            fileDownload.click();
            document.body.removeChild(fileDownload);
        }
    });

});
</script>