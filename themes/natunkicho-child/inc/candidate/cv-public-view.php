<?php
if (!defined('ABSPATH')) exit;

/**
 * =========================================================================
 * DIGITAL CV: PREVIEW & PUBLIC VIEW (A4 STUDIO EDITION + RELATIONAL PAYWALL)
 * Path: inc/candidate/cv-public-view.php
 * Shortcode: [nk_candidate_cv_view]
 * =========================================================================
 */

function nk_candidate_cv_view_shortcode() {
    $current_user_id = get_current_user_id();
    $target_user_id = isset($_GET['u']) ? intval($_GET['u']) : $current_user_id;

    if (!$target_user_id) {
        return '<div class="nk-dash-card" style="text-align:center; padding: 40px;"><h3>Please log in or provide a valid candidate ID.</h3></div>';
    }

    $user_info = get_userdata($target_user_id);
    if (!$user_info) return '<p>Candidate not found.</p>';

    global $wpdb;
    $profile = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}nk_cv_profiles WHERE user_id = %d LIMIT 1", $target_user_id ) );

    $p_data = ['first_name' => $user_info->first_name, 'last_name' => $user_info->last_name, 'email' => $user_info->user_email, 'phone' => '', 'photo' => ''];
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
    } else {
        $s_data['summary'] = get_user_meta($target_user_id, 'nk_bio', true);
        $p_data['photo'] = get_user_meta($target_user_id, 'nk_photo_url', true);
        
        $old_skills = get_user_meta($target_user_id, 'nk_skills', true);
        if($old_skills) {
            foreach(explode(',', $old_skills) as $s) { $skl_data[] = ['skill_name' => trim($s)]; }
        }
        $old_exp = get_user_meta($target_user_id, 'nk_experience', true);
        if($old_exp) { $e_data[] = ['job_title' => 'Prior Experience', 'company' => '', 'start_date' => '', 'end_date' => '', 'details' => $old_exp]; }
        
        $old_edu = get_user_meta($target_user_id, 'nk_education', true);
        if($old_edu) { $edu_data[] = ['degree' => 'Education & Certifications', 'institution' => $old_edu, 'year' => '']; }
    }

    $cv_file_url = get_user_meta($target_user_id, 'nk_cv_file_url', true);
    $linkedin    = get_user_meta($target_user_id, 'nk_linkedin', true);
    $portfolio   = get_user_meta($target_user_id, 'nk_portfolio', true);
    $github      = get_user_meta($target_user_id, 'nk_github', true);
    $instagram   = get_user_meta($target_user_id, 'nk_instagram', true);

    $share_url = esc_url(site_url('/cv/?u=' . $target_user_id));
    $is_owner = ($current_user_id === $target_user_id);

    $has_full_access = false;
    if ($is_owner || current_user_can('manage_options') || (function_exists('nk_is_user_premium') && nk_is_user_premium($current_user_id))) {
        $has_full_access = true;
    } else if (is_user_logged_in() && in_array('employer', wp_get_current_user()->roles)) {
        $applied_jobs = get_user_meta($target_user_id, 'nk_applied_jobs', true);
        if (is_array($applied_jobs)) {
            foreach ($applied_jobs as $ajob_id) {
                $j = get_post($ajob_id);
                if ($j && $j->post_author == $current_user_id) {
                    $has_full_access = true; 
                    break;
                }
            }
        }
    }

    wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], null, true);
    wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], null, true);

    ob_start();
    ?>
    <div class="nk-cv-public-wrapper" style="max-width: 900px; margin: 0 auto;">
        
        <div class="nk-cv-actions no-print" style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 15px 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
            <div>
                <?php if ($is_owner): ?>
                    <span style="background: #dbeafe; color: #1e40af; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; margin-right: 15px;">👁️ Public Preview</span>
                    <a href="<?php echo esc_url(site_url('/dashboard/?tab=cv-studio')); ?>" style="font-size: 14px; color: #64748b; text-decoration: none; font-weight: 600;">Edit in Studio ✏️</a>
                <?php else: ?>
                    <span style="background: <?php echo $has_full_access ? '#dcfce7; color: #166534;' : '#fef3c7; color: #92400e;'; ?>; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;">
                        <?php echo $has_full_access ? '🔓 Unlocked Candidate Profile' : '🔒 Premium Gated Profile'; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px;">
                <?php if ($is_owner): ?>
                    <button onclick="nkCopyShareLink('<?php echo $share_url; ?>')" style="background: #fff; color: #0A66C2; border: 2px solid #0A66C2; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                        🔗 Copy Public Link
                    </button>
                <?php endif; ?>
                
                <button id="nk-download-cv-btn" style="background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; transition: 0.2s;" <?php echo !$has_full_access ? 'disabled style="opacity: 0.5; background: #94a3b8; cursor: not-allowed;" title="Upgrade to Premium to download"' : ''; ?>>
                    ⬇️ Download PDF <?php echo !$has_full_access ? '🔒' : ''; ?>
                </button>
            </div>
        </div>

        <?php if($cv_file_url && $has_full_access): ?>
            <div class="no-print" style="margin-bottom: 30px; padding: 15px 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h4 style="margin: 0 0 5px 0; color: #0f172a; font-size: 15px;">Original Attached Document</h4>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">The candidate also uploaded their own formatted resume.</p>
                </div>
                <a href="<?php echo esc_url($cv_file_url); ?>" target="_blank" style="background: #0f172a; color: #fff; text-decoration: none; padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: bold;">
                    📄 View Uploaded CV
                </a>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; width: 100%; overflow-x: auto; padding-bottom: 40px;">
            <div id="nk-cv-canvas" style="width: 794px; min-width: 794px; min-height: 1123px; background-color: #ffffff; background-image: linear-gradient(to bottom, transparent 1121px, #94a3b8 1121px, #94a3b8 1123px); background-size: 100% 1123px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); box-sizing: border-box; display: table; position: relative;">
                
                <div style="display: table-cell; width: 33%; vertical-align: top; background: #f8fafc; padding: 40px 25px; border-right: 1px solid #e2e8f0; box-sizing: border-box;">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <?php if(!empty($p_data['photo'])): ?>
                            <img src="<?php echo esc_attr($p_data['photo']); ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <?php else: ?>
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; color: #94a3b8; border: 4px solid #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">👤</div>
                        <?php endif; ?>
                    </div>
                    
                    <h1 style="margin: 0 0 15px 0; font-size: 22px; color: #0f172a; text-transform: uppercase; font-weight: 800; line-height: 1.2; text-align: center; word-wrap: break-word; word-break: break-word;">
                        <?php echo esc_html($p_data['first_name'] ?: 'CANDIDATE'); ?><br>
                        <?php echo esc_html($p_data['last_name'] ?: 'PROFILE'); ?>
                    </h1>
                    
                    <div style="margin-bottom: 30px; font-size: 12px; color: #475569; text-align: center; line-height: 1.8; word-wrap: break-word;">
                        <?php if ($has_full_access): ?>
                            <div><?php echo esc_html($p_data['email'] ?: 'No email provided'); ?></div>
                            <div><?php echo esc_html($p_data['phone'] ?: 'No phone provided'); ?></div>
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 15px; align-items: center;">
                                <?php if(!empty($linkedin)): ?>
                                    <a href="<?php echo esc_url($linkedin); ?>" target="_blank" style="color: #0A66C2; text-decoration: none; font-weight: 800; font-size: 11px; background: #e0f2fe; padding: 6px 12px; border-radius: 4px; transition: background 0.2s; width: 80%;">🔗 LinkedIn Profile</a>
                                <?php endif; ?>
                                <?php if(!empty($portfolio)): ?>
                                    <a href="<?php echo esc_url($portfolio); ?>" target="_blank" style="color: #0d9488; text-decoration: none; font-weight: 800; font-size: 11px; background: #ccfbf1; padding: 6px 12px; border-radius: 4px; transition: background 0.2s; width: 80%;">🌍 Portfolio / Website</a>
                                <?php endif; ?>
                                <?php if(!empty($github)): ?>
                                    <a href="<?php echo esc_url($github); ?>" target="_blank" style="color: #334155; text-decoration: none; font-weight: 800; font-size: 11px; background: #f1f5f9; padding: 6px 12px; border-radius: 4px; transition: background 0.2s; width: 80%;">💻 GitHub Profile</a>
                                <?php endif; ?>
                                <?php if(!empty($instagram)): ?>
                                    <a href="<?php echo esc_url($instagram); ?>" target="_blank" style="color: #be185d; text-decoration: none; font-weight: 800; font-size: 11px; background: #fce7f3; padding: 6px 12px; border-radius: 4px; transition: background 0.2s; width: 80%;">📸 Instagram</a>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <div style="filter: blur(4px); user-select: none;">hidden@email.com</div>
                            <div style="color: #ef4444; font-weight: bold; margin-top: 5px;">[Contact Info Locked]</div>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($skl_data)): ?>
                        <h3 class="prev-heading-side">Skills</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 30px;">
                            <?php foreach($skl_data as $skl): if(!empty($skl['skill_name'])): ?>
                                <span style="background: #e2e8f0; color: #0f172a; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;"><?php echo esc_html($skl['skill_name']); ?></span>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($lang_data)): ?>
                        <h3 class="prev-heading-side">Languages</h3>
                        <ul style="padding-left:15px; margin:0 0 30px 0; font-size:12px; color:#475569; line-height:1.6;">
                            <?php foreach($lang_data as $lang): if(!empty($lang['language'])): ?>
                                <li style="margin-bottom:4px;"><strong style="color:#0f172a;"><?php echo esc_html($lang['language']); ?></strong><br><?php echo esc_html($lang['proficiency']); ?></li>
                            <?php endif; endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if(!empty($cert_data)): ?>
                        <h3 class="prev-heading-side">Certifications</h3>
                        <ul style="padding-left:15px; margin:0 0 30px 0; font-size:12px; color:#475569; line-height:1.6;">
                            <?php foreach($cert_data as $cert): if(!empty($cert['cert_name'])): ?>
                                <li style="margin-bottom:4px;"><strong style="color:#0f172a;"><?php echo esc_html($cert['cert_name']); ?></strong><br><?php echo esc_html($cert['year']); ?></li>
                            <?php endif; endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div style="display: table-cell; width: 67%; vertical-align: top; background: #ffffff; padding: 40px 35px; box-sizing: border-box; position: relative;">
                    
                    <div style="<?php echo !$has_full_access ? 'filter: blur(5px); user-select: none; pointer-events: none; opacity: 0.4;' : ''; ?>">
                        
                        <?php if(!empty($s_data['summary'])): ?>
                            <h3 class="prev-heading-main">Professional Summary</h3>
                            <p style="font-size: 13px; line-height: 1.6; color: #334155; margin-bottom: 30px; white-space: pre-wrap; word-wrap: break-word;"><?php echo esc_html($s_data['summary']); ?></p>
                        <?php endif; ?>

                        <?php if(!empty($e_data)): ?>
                            <h3 class="prev-heading-main">Work Experience</h3>
                            <div style="margin-bottom: 30px; word-wrap: break-word;">
                                <?php foreach($e_data as $exp): if(!empty($exp['job_title'])): ?>
                                    <div style="margin-bottom: 20px; position: relative; padding-left: 15px; border-left: 2px solid #cbd5e1;">
                                        <div style="position: absolute; left: -5px; top: 6px; width: 8px; height: 8px; border-radius: 50%; background: #0A66C2;"></div>
                                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                            <strong style="font-size: 14.5px; color: #0f172a;"><?php echo esc_html($exp['job_title']); ?></strong>
                                            <span style="font-size: 11px; color: #0f172a; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html($exp['start_date']) . ' - ' . esc_html($exp['end_date']); ?></span>
                                        </div>
                                        <div style="font-size: 13px; color: #0A66C2; font-weight: 600; margin-bottom: 8px;"><?php echo esc_html($exp['company']); ?></div>
                                        <p style="font-size: 12.5px; margin: 0; line-height: 1.6; color: #334155; white-space: pre-wrap;"><?php echo esc_html($exp['details']); ?></p>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($edu_data)): ?>
                            <h3 class="prev-heading-main">Education</h3>
                            <div style="margin-bottom: 30px; word-wrap: break-word;">
                                <?php foreach($edu_data as $edu): if(!empty($edu['degree'])): ?>
                                    <div style="margin-bottom: 15px; position: relative; padding-left: 15px; border-left: 2px solid #cbd5e1;">
                                        <div style="position: absolute; left: -5px; top: 6px; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                                        <strong style="font-size: 14px; color: #0f172a; display: block; margin-bottom: 3px;"><?php echo esc_html($edu['degree']); ?></strong>
                                        <span style="font-size: 12.5px; color: #475569;"><?php echo esc_html($edu['institution']) . ' &bull; ' . esc_html($edu['year']); ?></span>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($act_data)): ?>
                            <h3 class="prev-heading-main">Extra Activities</h3>
                            <div style="margin-bottom: 30px; word-wrap: break-word;">
                                <?php foreach($act_data as $act): if(!empty($act['activity_name'])): ?>
                                    <div style="margin-bottom:12px;">
                                        <strong style="font-size:13.5px; color:#0f172a;"><?php echo esc_html($act['activity_name']); ?></strong>
                                        <p style="font-size:12.5px; color:#475569; margin:2px 0 0 0; white-space: pre-wrap;"><?php echo esc_html($act['details']); ?></p>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($ref_data)): ?>
                            <h3 class="prev-heading-main">References</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; word-wrap: break-word;">
                                <?php foreach($ref_data as $ref): if(!empty($ref['name'])): ?>
                                    <div>
                                        <strong style="font-size:13.5px; color:#0f172a; display:block;"><?php echo esc_html($ref['name']); ?></strong>
                                        <span style="font-size:12px; color:#64748b;"><?php echo esc_html($ref['company']); ?><br><?php echo esc_html($ref['contact']); ?></span>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <?php if (!$has_full_access): ?>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 85%; background: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.15); z-index: 10;">
                            <span style="font-size: 40px; display: block; margin-bottom: 15px;">🔒</span>
                            <h3 style="margin: 0 0 10px 0; font-weight: 800; color: #0f172a; font-size: 22px;">Unlock Full Talent History</h3>
                            <p style="margin: 0 0 25px 0; font-size: 15px; color: #64748b; line-height: 1.6;">This candidate's detailed experience map, education, and PDF download are protected. Upgrade to Premium Pro to instantly unlock this and all other profiles.</p>
                            
                            <a href="<?php echo esc_url(site_url('/pricing/')); ?>" style="display: inline-block; padding: 12px 30px; border-radius: 8px; font-weight: 700; color: #fff; background: linear-gradient(45deg, #ff4d6d, #ff9e00); text-decoration: none; font-size: 15px; box-shadow: 0 4px 15px rgba(255, 77, 109, 0.3);">
                                Upgrade to Unlock Profile
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <style>
    .prev-heading-main { font-size: 15px; text-transform: uppercase; color: #0f172a; margin: 0 0 15px 0; font-weight: 800; letter-spacing: 1px; border-bottom: 2px solid #0f172a; padding-bottom: 5px; }
    .prev-heading-side { font-size: 13px; text-transform: uppercase; color: #0f172a; margin: 0 0 12px 0; font-weight: 800; letter-spacing: 1px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
    </style>

    <script>
    function nkCopyShareLink(url) {
        navigator.clipboard.writeText(url).then(function() {
            alert("Public link copied to clipboard! Share it with employers.");
        });
    }

    jQuery(document).ready(function($) {
        $('#nk-download-cv-btn').click(async function() {
            let $btn = $(this);
            let originalText = $btn.text();
            $btn.text('⏳ Generating PDF...').prop('disabled', true).css('opacity', '0.7');

            let originalCanvas = document.getElementById('nk-cv-canvas');
            let clone = originalCanvas.cloneNode(true);
            clone.id = 'nk-print-clone';

            let headingsMain = clone.querySelectorAll('.prev-heading-main');
            let headingsSide = clone.querySelectorAll('.prev-heading-side');
            headingsMain.forEach(h => h.style.letterSpacing = 'normal');
            headingsSide.forEach(h => h.style.letterSpacing = 'normal');

            $(clone).css({
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'z-index': '-999999', 
                'width': '794px',
                'height': '1123px',
                'background-color': '#ffffff',
                'background-image': 'none',
                'transform': 'none',
                'box-shadow': 'none',
                'margin': '0',
                'padding': '0',
                'display': 'block', 
                'overflow': 'visible'
            });

            let $leftCol = $(clone).children().eq(0);
            let $rightCol = $(clone).children().eq(1);

            $leftCol.css({ 'display': 'block', 'float': 'left', 'width': '262px', 'height': '1123px', 'box-sizing': 'border-box' });
            $rightCol.css({ 'display': 'block', 'float': 'left', 'width': '532px', 'height': '1123px', 'box-sizing': 'border-box' });

            document.body.appendChild(clone);

            try {
                const canvas = await html2canvas(clone, {
                    scale: 2, 
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    width: 794,
                    height: 1123
                });

                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);

                let fileName = '<?php echo esc_js($p_data['first_name'] . '_' . $p_data['last_name']); ?>_CV.pdf';
                pdf.save(fileName.replace(/\s+/g, '_'));

            } catch (err) {
                console.error("PDF Export failed:", err);
                alert("An error occurred. Please try again.");
            } finally {
                document.body.removeChild(clone);
                $btn.text(originalText).prop('disabled', false).css('opacity', '1');
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_candidate_cv_view', 'nk_candidate_cv_view_shortcode');