<?php
/**
 * =========================================================================
 * SaaS JOB SUBMISSION CORE ENGINE
 * Path: inc/jobs/premium-job-submit.php
 * Shortcode: [nk_premium_post_job]
 * =========================================================================
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_premium_post_job_shortcode() {
    if (!is_user_logged_in()) {
        return '<div style="text-align:center; padding: 40px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; max-width:600px; margin:40px auto;"><h3>Please login as an Employer to post a job.</h3></div>';
    }

    $user_id = get_current_user_id();
    $user_obj = wp_get_current_user();
    
    $is_premium = false;
    if ( function_exists( 'wc_customer_bought_product' ) && wc_customer_bought_product( $user_obj->user_email, $user_id, 2949 ) ) {
        $is_premium = true;
    } elseif ( function_exists('nk_is_user_premium') && nk_is_user_premium($user_id) ) {
        $is_premium = true;
    } elseif ( in_array('administrator', (array)$user_obj->roles) ) {
        $is_premium = true;
    }

    ob_start();
    ?>
    
    <div class="nk-job-post-container-safe" style="box-sizing: border-box; max-width: 1300px; margin: 0 auto; padding: 0 20px;">
        
        <div style="width: 100%; display: block; margin-bottom: 25px; clear: both;">
            <a href="<?php echo esc_url(site_url('/dashboard/')); ?>" style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #1e293b; text-decoration: none; padding: 10px 18px; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e2e8f0';">
                <span style="font-size: 16px; line-height: 1;">&larr;</span> Back to Dashboard
            </a>
        </div>

        <div class="nk-job-post-wrapper">
            
            <div class="nk-form-column">
                <div style="margin-bottom: 30px;">
                    <h2 style="font-size: 28px; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; line-height: 1.2;">Post a Job</h2>
                    <p style="color: #64748b; margin: 0; font-size: 15px;">Fill out the details below to find your next exceptional hire.</p>
                </div>
                
                <div class="nk-wpjm-hijack">
                    <input type="hidden" id="nk_job_submit_nonce" value="<?php echo wp_create_nonce('nk_job_submit_nonce'); ?>">
                    <?php echo do_shortcode('[submit_job_form]'); ?>
                </div>
            </div>

            <div class="nk-preview-column">
                <div class="nk-sticky-preview-wrapper">
                    <h3 style="font-size: 15px; font-weight: 700; color: #475569; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.05em;">Live Preview</h3>
                    
                    <div class="nk-live-job-card" style="display: flex; flex-direction: column;">
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center;">
                            <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">🏢</div>
                            <div style="overflow: hidden; width: 100%;">
                                <h4 id="live-job-title" style="margin: 0; font-size: 18px; color: #0f172a; font-weight: 800; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Job Title</h4>
                                <p id="live-company-name" style="margin: 3px 0 0 0; font-size: 14px; color: #0A66C2; font-weight: 700; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Company Name</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 8px; font-size: 12px; color: #64748b; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                            <span style="background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">📍 <span id="live-job-location">Location</span></span>
                            <span style="background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">💼 <span id="live-job-type">Full Time</span></span>
                        </div>
                        
                        <div id="live-job-dynamic-body" style="flex: 1; overflow-y: auto; max-height: 400px; font-size: 13px; color: #334155; margin-bottom: 20px;">
                            <div id="live-job-placeholder">
                                <div style="height: 8px; background: #f1f5f9; border-radius: 4px; width: 100%; margin-bottom: 12px;"></div>
                                <div style="height: 8px; background: #f1f5f9; border-radius: 4px; width: 90%; margin-bottom: 12px;"></div>
                                <div style="height: 8px; background: #f1f5f9; border-radius: 4px; width: 65%; margin-bottom: 25px;"></div>
                            </div>
                        </div>
                        
                        <button type="button" style="width: 100%; background: #0A66C2; color: #fff; border: none; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: not-allowed; opacity: 0.6; display: block; text-align: center;">Apply Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="nk-ai-job-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.75); z-index:999999; justify-content:center; align-items:center; padding: 20px; box-sizing: border-box;">
        <div style="background:#fff; padding:30px; border-radius:14px; width:100%; max-width:500px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); box-sizing: border-box; position: relative;">
            <h3 style="margin:0 0 10px 0; font-size:1.35rem; font-weight:800; display:flex; align-items:center; gap:8px; color: #0f172a;">✨ AI Job Copywriter</h3>
            <p style="color:#64748b; font-size:14px; line-height: 1.5; margin: 0 0 20px 0;">Our system will instantly organize your job summary, experience levels, and criteria definitions based on your provided job title.</p>
            
            <div id="nk-ai-job-loading" style="display:none; text-align: center; padding: 25px 10px;">
                <div style="font-size: 32px; margin-bottom: 12px; animation: nkpulse 1.4s infinite ease-in-out;">🤖</div>
                <h4 style="color: #0A66C2; margin:0; font-size: 15px; font-weight: 700;">Drafting optimized job criteria...</h4>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 15px;">
                <button type="button" id="nk-close-ai-job" style="background:#f1f5f9; color:#475569; border:none; padding:10px 18px; border-radius:6px; cursor:pointer; font-weight:700; font-size: 14px;">Cancel</button>
                <button type="button" id="nk-run-ai-job" style="background:#10b981; color:#fff; border:none; padding:10px 22px; border-radius:6px; cursor:pointer; font-weight:700; font-size: 14px; box-shadow: 0 2px 4px rgba(16,185,129,0.2);">✨ Write Content</button>
            </div>
        </div>
    </div>

    <style>
        .nk-job-post-container-safe *, .nk-job-post-container-safe *::before, .nk-job-post-container-safe *::after { box-sizing: border-box !important; }
        .nk-job-post-wrapper { display: flex; gap: 30px; align-items: start; width: 100%; margin-bottom: 50px; }
        .nk-form-column { flex: 1; min-width: 0; }
        
        .nk-preview-column { width: 360px; flex-shrink: 0; position: -webkit-sticky; position: sticky; top: 40px; align-self: flex-start; }
        .nk-sticky-preview-wrapper { width: 100%; }
        
        .nk-wpjm-hijack form { background: #fff !important; padding: 35px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.01); }
        .nk-wpjm-hijack fieldset { margin-bottom: 25px; border: none !important; padding: 0 !important; position: relative !important; width: 100% !important; float: none !important; }
        .nk-wpjm-hijack label { display: block !important; font-size: 14px !important; font-weight: 700 !important; color: #0f172a !important; margin-bottom: 8px !important; float: none !important; text-align: left !important; }
        .nk-wpjm-hijack small.description { color: #64748b !important; font-size: 12.5px !important; display: block !important; margin: 4px 0 8px 0 !important; line-height: 1.4; }
        
        .nk-wpjm-hijack input[type="text"], .nk-wpjm-hijack input[type="email"], .nk-wpjm-hijack input[type="url"] { width: 100% !important; padding: 12px 15px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; font-size: 15px !important; color: #0f172a !important; background: #f8fafc !important; transition: all 0.2s; box-sizing: border-box !important; }
        .nk-wpjm-hijack textarea { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; color: #0f172a; background: #f8fafc; box-sizing: border-box; }
        .nk-wpjm-hijack input:focus, .nk-wpjm-hijack select:focus, .nk-wpjm-hijack textarea:focus { border-color: #0A66C2 !important; outline: none !important; background: #fff !important; box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1) !important; }

        .nk-wpjm-hijack select { height: 48px !important; padding: 0 15px !important; color: #0f172a !important; -webkit-appearance: auto !important; appearance: auto !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; background: #f8fafc !important; width: 100% !important; box-sizing: border-box !important; }
        
        .nk-wpjm-hijack .select2-container .select2-selection--single { height: 48px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; background: #f8fafc !important; display: flex; align-items: center; }
        .nk-wpjm-hijack .select2-container--default .select2-selection--single .select2-selection__rendered { color: #0f172a !important; font-size: 15px !important; padding-left: 15px !important; line-height: 46px !important; }
        .nk-wpjm-hijack .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; }
        .select2-container--default .select2-dropdown { border: 1px solid #cbd5e1 !important; border-radius: 0 0 8px 8px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
        .select2-container--default .select2-search--dropdown { padding: 12px !important; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .select2-container--default .select2-search--dropdown .select2-search__field { border: 1px solid #0A66C2 !important; border-radius: 6px !important; padding: 10px 12px !important; outline: none !important; box-shadow: 0 0 0 3px rgba(10,102,194,0.1) !important; }

        .nk-wpjm-hijack .chosen-container { width: 100% !important; }
        .nk-wpjm-hijack .chosen-container-single .chosen-single,
        .nk-wpjm-hijack .chosen-container-multi .chosen-choices { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; height: 48px !important; line-height: 46px !important; padding: 0 15px !important; color: #0f172a !important; font-size: 15px !important; box-shadow: none !important; background-image: none !important; box-sizing: border-box !important; }
        .nk-wpjm-hijack .chosen-container-single .chosen-single span { color: #0f172a !important; line-height: 46px !important; margin-top: 0 !important; display: block !important; }
        .nk-wpjm-hijack .chosen-container-single .chosen-single div b { margin-top: 12px !important; }
        .nk-wpjm-hijack .chosen-container-active .chosen-single { border-color: #0A66C2 !important; box-shadow: 0 0 0 3px rgba(10,102,194,0.1) !important; }
        .nk-wpjm-hijack .chosen-results li { color: #0f172a !important; line-height: 1.5 !important; font-size: 14px !important; padding: 8px 10px !important; }
        .nk-wpjm-hijack .chosen-container .chosen-drop { border: 1px solid #cbd5e1 !important; border-top: none !important; border-radius: 0 0 8px 8px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
        .nk-wpjm-hijack .chosen-container .chosen-search { padding: 12px !important; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: block !important; }
        .nk-wpjm-hijack .chosen-container .chosen-search input[type="text"] { width: 100% !important; padding: 10px 12px !important; border: 1px solid #0A66C2 !important; border-radius: 6px !important; font-family: inherit !important; background: #fff !important; background-image: none !important; box-shadow: 0 0 0 3px rgba(10,102,194,0.1) !important; display: block !important; }
        
        .nk-wpjm-hijack .button, .nk-wpjm-hijack input[type="submit"] { background: #0A66C2 !important; color: #fff !important; padding: 15px 24px !important; border: none !important; border-radius: 8px !important; font-size: 16px !important; font-weight: 700 !important; cursor: pointer !important; width: 100% !important; margin-top: 10px !important; transition: background 0.2s !important; display: block !important; box-shadow: 0 4px 6px -1px rgba(10,102,194,0.15); }
        .nk-wpjm-hijack .button:hover, .nk-wpjm-hijack input[type="submit"]:hover { background: #08529e !important; }
        .wp-editor-container { border: 1px solid #cbd5e1 !important; border-radius: 8px !important; overflow: hidden; }
        
        .nk-live-job-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.03); max-height: 85vh; }
        #live-job-dynamic-body::-webkit-scrollbar { width: 6px; }
        #live-job-dynamic-body::-webkit-scrollbar-track { background: transparent; }
        #live-job-dynamic-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        #live-job-dynamic-body h1, #live-job-dynamic-body h2, #live-job-dynamic-body h3 { font-size: 14px !important; color: #0f172a; margin: 10px 0 5px 0; font-weight: bold; }
        #live-job-dynamic-body ul { padding-left: 20px; margin-top: 5px; }
        
        @keyframes nkpulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
        
        @media (max-width: 991px) { .nk-job-post-wrapper { flex-direction: column; gap: 20px; } .nk-preview-column { display: none !important; } .nk-wpjm-hijack form { padding: 20px; } }
    </style>

    <script>
    jQuery(document).ready(function($) {
        
        setTimeout(function() {
            if ($.fn.chosen) {
                $('#job_country').chosen('destroy').chosen({ search_contains: true, width: '100%', disable_search: false, disable_search_threshold: 0 });
            } else if ($.fn.select2) {
                $('#job_country').select2('destroy').select2({ width: '100%', minimumResultsForSearch: 0 });
            }
        }, 500);

        $('#job_title').on('input', function() { $('#live-job-title').text($(this).val() || 'Job Title'); });
        
        function syncLiveLocation() {
            let city = $('#job_location').val();
            let country = $('#job_country option:selected').text();
            let finalLocation = '';
            
            if (country && country.indexOf('Select') === -1 && country.indexOf('Choose') === -1) {
                finalLocation = country;
            }
            if (city) {
                finalLocation = city + (finalLocation ? ', ' + finalLocation : '');
            }
            $('#live-job-location').text(finalLocation || 'Location');
        }

        $('#job_location').on('input', syncLiveLocation);
        $('#job_country').on('change', syncLiveLocation);

        $('#company_name').on('input', function() { $('#live-company-name').text($(this).val() || 'Company Name'); });
        $('#job_type').on('change', function() { $('#live-job-type').text($(this).find("option:selected").text() || 'Full Time'); });

        // 10x FIX: Hardcoded the exact IDs so WPJM CSS classes cannot hide them!
        function syncRichTextToPreview() {
            let fieldsToSync = [
                { id: 'job_summary', label: 'Job Summary' },
                { id: 'job_responsibilities', label: 'Job Responsibilities' },
                { id: 'job_requirements', label: 'Requirements' },
                { id: 'job_skills', label: 'Skills' }
            ];

            let fullContent = '';

            fieldsToSync.forEach(function(f) {
                let val = '';
                
                // Try to get from TinyMCE first
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get(f.id)) {
                    val = tinyMCE.get(f.id).getContent();
                } 
                // Fallback to plain textarea/input
                else if ($('#' + f.id).length) {
                    val = $('#' + f.id).val();
                    // If it's a textarea, replace line breaks with <br>
                    if(val && $('#' + f.id).is('textarea')) {
                        val = '<p>' + val.replace(/\n/g, '<br>') + '</p>';
                    }
                }

                if (val && val.trim() !== '') {
                    fullContent += '<h5 style="margin: 15px 0 5px 0; color:#0A66C2; font-size:12px; text-transform:uppercase; letter-spacing:1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">' + f.label + '</h5>';
                    fullContent += '<div style="font-size:13px; color:#334155; line-height:1.6;">' + val + '</div>';
                }
            });

            if(fullContent !== '') {
                $('#live-job-placeholder').hide();
                let currentPreview = $('#live-job-dynamic-body').find('.dynamic-content').html() || '';
                
                // Only rewrite DOM if content changed (stops flickering)
                if (currentPreview !== fullContent) {
                    $('#live-job-dynamic-body').find('.dynamic-content').remove();
                    $('#live-job-dynamic-body').append('<div class="dynamic-content">' + fullContent + '</div>');
                }
            } else {
                $('#live-job-dynamic-body').find('.dynamic-content').remove();
                $('#live-job-placeholder').show();
            }
        }
        
        setInterval(syncRichTextToPreview, 1000);

        let isPremium = <?php echo $is_premium ? 'true' : 'false'; ?>;
        
        // Modal Action Handlers
        let activeAIField = ''; 

      $(document).on('click', '.nk-open-ai-job-btn', function(e) {
            e.preventDefault();
            
            // Show Beautiful Locked Modal for General Users
            if (!isPremium) {
                let lockedHTML = `
                    <div style="text-align:center; padding: 20px 10px;">
                        <span style="font-size: 50px; display:block; margin-bottom:15px;">🔒</span>
                        <h3 style="color:#0f172a; font-weight:800; font-size: 22px; margin-bottom:12px;">AI Features are Premium Only</h3>
                        <p style="color:#64748b; font-size:15px; margin-bottom:25px; line-height: 1.5;">Upgrade to our Premium Employer plan to unlock the AI Job Copywriter. Instantly generate highly optimized job descriptions that attract top talent.</p>
                        <a href="/pricing/" style="display:inline-block; background:#f59e0b; color:#fff; padding:14px 28px; border-radius:8px; font-weight:700; text-decoration:none; font-size: 16px; box-shadow: 0 4px 12px rgba(245,158,11,0.3);">Upgrade to Premium ✨</a>
                        <button type="button" id="nk-close-ai-locked" style="display:block; margin:20px auto 0 auto; background:transparent; border:none; color:#64748b; cursor:pointer; font-weight:600; font-size: 14px;">Maybe Later</button>
                    </div>
                `;
                $('#nk-ai-job-modal').html('<div style="background:#fff; padding:30px; border-radius:14px; width:100%; max-width:450px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative;">' + lockedHTML + '</div>');
                $('#nk-ai-job-modal').css('display', 'flex').hide().fadeIn(200);
                
                $(document).on('click', '#nk-close-ai-locked', function() {
                    $('#nk-ai-job-modal').fadeOut(150);
                });
                return;
            }

            if(!$('#job_title').val()) {
                alert("Please type a Title into the Job Title field first so the AI model understands what profile to generate.");
                return;
            }
            if(!$('#job_title').val()) {
                alert("Please type a Title into the Job Title field first so the AI model understands what profile to generate.");
                return;
            }
            activeAIField = $(this).attr('data-target-field'); 
            $('#nk-ai-job-modal').css('display', 'flex').hide().fadeIn(200);
        });

        $('#nk-close-ai-job').click(function() {
            $('#nk-ai-job-modal').fadeOut(150);
        });

        $('#nk-run-ai-job').click(function() {
            let $btn = $(this);
            let jobTitle = $('#job_title').val();
            
            $btn.text('⏳ Formatting...').prop('disabled', true).css('opacity', '0.6');
            $('#nk-ai-job-loading').slideDown(200);

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'nk_generate_ai_job_desc',
                security: $('#nk_job_submit_nonce').val(),
                job_title: jobTitle
            }, function(response) {
                if(response.success) {
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get(activeAIField)) {
                        tinyMCE.get(activeAIField).setContent(response.data);
                    } else {
                        $('#' + activeAIField).val(response.data);
                    }
                    $('#nk-ai-job-modal').fadeOut(150);
                    syncRichTextToPreview(); 
                } else {
                    alert(response.data);
                }
                $btn.text('✨ Write Content').prop('disabled', false).css('opacity', '1');
                $('#nk-ai-job-loading').slideUp(150);
            });
        });

        // Upgrades Widget
        const submitFieldset = $('.nk-wpjm-hijack .step-submit, .nk-wpjm-hijack .form-submit').first();
        const boostHTML = isPremium 
            ? `<div style="background: #f0fdf4; border: 1px solid #10b981; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; width:100%; box-sizing:border-box;">
                   <div>
                       <h4 style="margin: 0 0 4px 0; color: #166534; font-size: 15px; font-weight:700;">🚀 Premium Visibility Active</h4>
                       <p style="margin: 0; font-size: 13px; color: #15803d; line-height:1.4;">This posting receives highlighted styling and premium pinning automatically.</p>
                   </div>
                   <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #166534; cursor: pointer; font-size: 14px; margin:0!important;">
                       <input type="checkbox" name="nk_boost_job" value="1" checked style="width: 18px; height: 18px; accent-color: #10b981;"> Active
                   </label>
               </div>`
            : `<div style="background: #fffbeb; border: 1px solid #f59e0b; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; width:100%; box-sizing:border-box;">
                   <div>
                       <h4 style="margin: 0 0 4px 0; color: #92400e; font-size: 15px; font-weight:700;">🚀 Feature This Position</h4>
                       <p style="margin: 0; font-size: 13px; color: #b45309; line-height:1.4;">Pin your opening to the top of search listings to accelerate visibility.</p>
                   </div>
                   <a href="/pricing/" target="_blank" style="background: #f59e0b; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 700; transition: background 0.2s;">Upgrade Plan</a>
               </div>`;
        
        if(submitFieldset.length > 0) {
            submitFieldset.before(boostHTML);
        } else {
            $('.nk-wpjm-hijack input[type="submit"]').before(boostHTML);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('nk_premium_post_job', 'nk_premium_post_job_shortcode');