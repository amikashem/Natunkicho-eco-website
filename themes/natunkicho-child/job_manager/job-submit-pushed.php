<?php
/**
 * Template Override: Job Submit Form (10x AI Wizard Version)
 * Path: natunkicho-child/job_manager/job-submit.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DONOTCACHEPAGE' ) ) {
    define( 'DONOTCACHEPAGE', true );
}

if ( ! is_user_logged_in() ) {
    return; 
}

global $job_manager;

$wizard_steps = [1 => [], 2 => [], 3 => [], 4 => []];
foreach ( $job_fields as $key => $field ) {
    $class = isset($field['class']) ? $field['class'] : '';
    if ( strpos( $class, 'nk-wizard-step-2' ) !== false ) {
        $wizard_steps[2][$key] = $field;
    } elseif ( strpos( $class, 'nk-wizard-step-3' ) !== false ) {
        $wizard_steps[3][$key] = $field;
    } elseif ( strpos( $class, 'nk-wizard-step-4' ) !== false ) {
        $wizard_steps[4][$key] = $field;
    } else {
        $wizard_steps[1][$key] = $field;
    }
}

$is_premium = ( function_exists('nk_is_user_premium') && nk_is_user_premium( get_current_user_id() ) ) ? 'true' : 'false';
?>

<style>
    .nk-wizard-container { width: 100%; box-sizing: border-box; overflow: visible !important; margin-top: 15px; padding-top: 10px; }
    
    .nk-progress-bar { display: flex; justify-content: space-between; align-items: flex-start; position: relative; width: 100%; margin: 10px 0 40px 0; overflow: visible !important; }
    .nk-progress-bar::before { content: ''; position: absolute; top: 22px; left: 10%; width: 80%; height: 3px; background: #e2e8f0; z-index: 1; }
    
    .nk-progress-step { position: relative; z-index: 2; width: 25%; text-align: center; overflow: visible !important; }
    
    .nk-progress-dot { 
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 44px !important; 
        height: 44px !important; 
        border-radius: 50% !important; 
        background: #fff !important; 
        border: 2px solid #e2e8f0 !important; 
        font-weight: 700 !important; 
        color: #94a3b8 !important; 
        font-size: 16px !important; 
        margin: 0 auto 10px auto !important; 
        box-sizing: border-box !important; 
        padding: 0 !important;
        box-shadow: 0 0 0 6px #fff !important;
        position: relative !important;
        top: 0 !important;
    }
    .nk-progress-label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; text-align: center; }
    
    .nk-progress-step.active .nk-progress-dot { border-color: #0A66C2 !important; background: #0A66C2 !important; color: #fff !important; box-shadow: 0 0 0 6px #fff, 0 0 0 8px rgba(10, 102, 194, 0.15) !important; }
    .nk-progress-step.active .nk-progress-label { color: #0A66C2; }
    .nk-progress-step.completed .nk-progress-dot { border-color: #16a34a !important; background: #16a34a !important; color: #fff !important; box-shadow: 0 0 0 6px #fff !important; }
    
    .nk-step-section { 
        position: absolute !important; 
        left: -9999px !important; 
        top: -9999px !important; 
        visibility: hidden !important; 
        display: block !important; 
        width: 100%; 
        opacity: 0; 
        transition: opacity 0.2s ease; 
    }
    .nk-step-section.active { 
        position: relative !important; 
        left: 0 !important; 
        top: 0 !important; 
        visibility: visible !important; 
        opacity: 1; 
    }
    
    .nk-wizard-footer { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 25px; border-top: 1px solid #e2e8f0; gap: 15px; }
    .nk-btn-nav { padding: 14px 28px; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.2s; text-align: center; }
    .nk-btn-prev { background: #fff; border: 1px solid #cbd5e1; color: #475569; }
    .nk-btn-prev:hover { background: #f1f5f9; }
    .nk-btn-next, .nk-btn-submit { background: #0A66C2; border: none; color: #fff; flex: 1; max-width: 280px; }
    .nk-btn-next:hover, .nk-btn-submit:hover { background: #084d94; transform: translateY(-1px); box-shadow: 0 4px 15px rgba(10, 102, 194, 0.2); }
    
    /* 10x FIX: Force Native Chosen Search Box to Display */
    .chosen-search { display: block !important; padding: 10px !important; }
    .chosen-container-single .chosen-search input[type="text"] { 
        display: block !important; width: 100% !important; padding: 8px !important; border: 1px solid #0A66C2 !important; box-sizing: border-box !important;
    }

    @media (max-width: 640px) {
        .nk-progress-label { display: none; }
        .nk-wizard-footer { flex-direction: column; }
        .nk-btn-next, .nk-btn-submit { max-width: 100%; }
    }
</style>

<div class="nk-wizard-container">
    <div class="nk-progress-bar">
        <div class="nk-progress-step active" id="dot-1"><div class="nk-progress-dot">1</div><div class="nk-progress-label">Basics</div></div>
        <div class="nk-progress-step" id="dot-2"><div class="nk-progress-dot">2</div><div class="nk-progress-label">Details</div></div>
        <div class="nk-progress-step" id="dot-3"><div class="nk-progress-dot">3</div><div class="nk-progress-label">Salary</div></div>
        <div class="nk-progress-step" id="dot-4"><div class="nk-progress-dot">4</div><div class="nk-progress-label">Publish</div></div>
    </div>

    <form action="" method="post" id="submit-job-form" class="job-manager-form nk-wizard-body" enctype="multipart/form-data">
        <?php do_action( 'submit_job_form_start' ); ?>
        <?php do_action( 'submit_job_form_job_fields_start' ); ?>

        <div class="nk-step-section active" id="step-1">
            <?php foreach ( $wizard_steps[1] as $key => $field ) : ?>
                <fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                    <div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
                        <?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', [ 'key' => $key, 'field' => $field ] ); ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>
            <div class="nk-wizard-footer" style="justify-content: flex-end;">
                <button type="button" class="nk-btn-nav nk-btn-next" onclick="nk_change_step(1, 2)">Next Step &rarr;</button>
            </div>
        </div>

        <div class="nk-step-section" id="step-2">
            <?php foreach ( $wizard_steps[2] as $key => $field ) : ?>
                <fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
                    <label for="<?php echo esc_attr( $key ); ?>"><span><?php echo esc_html( $field['label'] ); ?></span></label>
                    <div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
                        <?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', [ 'key' => $key, 'field' => $field ] ); ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>
            <div class="nk-wizard-footer">
                <button type="button" class="nk-btn-nav nk-btn-prev" onclick="nk_change_step(2, 1)">&larr; Back</button>
                <button type="button" class="nk-btn-nav nk-btn-next" onclick="nk_change_step(2, 3)">Next Step &rarr;</button>
            </div>
        </div>

        <div class="nk-step-section" id="step-3">
            <div style="background:#fffbeb; border:1px solid #fde68a; padding:15px; border-radius:8px; margin-bottom:25px; color:#92400e; font-size:14px; font-weight:500; display:flex; gap:10px; align-items:flex-start;">
                <span style="font-size:18px;">🔒</span> 
                <div><strong>Salary Privacy:</strong> The exact salary range may be hidden from regular users and is only visible to Premium Candidates. However, jobs with salary data still rank higher in search results!</div>
            </div>

            <?php foreach ( $wizard_steps[3] as $key => $field ) : ?>
                <fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                    <div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
                        <?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', [ 'key' => $key, 'field' => $field ] ); ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>

            <div class="nk-wizard-footer">
                <button type="button" class="nk-btn-nav nk-btn-prev" onclick="nk_change_step(3, 2)">&larr; Back</button>
                <button type="button" class="nk-btn-nav nk-btn-next" onclick="nk_change_step(3, 4)">Next Step &rarr;</button>
            </div>
        </div>

        <div class="nk-step-section" id="step-4">
            <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:20px; border-radius:8px; margin-bottom:25px;">
                <h4 style="margin:0 0 8px 0; color:#1e40af; font-size:16px;">🏢 Company Information</h4>
                <p style="margin:0; color:#3b82f6; font-size:14px;">We will automatically pull your Company Name and Website directly from your Employer Profile to save you time!</p>
            </div>

            <?php foreach ( $wizard_steps[4] as $key => $field ) : ?>
                <fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                    <?php if ( ! empty( $field['description'] ) ) : ?><div style="font-size:12px; color:#64748b; margin-bottom:8px;"><?php echo esc_html($field['description']); ?></div><?php endif; ?>
                    <div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
                        <?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', [ 'key' => $key, 'field' => $field ] ); ?>
                    </div>
                </fieldset>
            <?php endforeach; ?>

            <?php do_action( 'submit_job_form_job_fields_end' ); ?>

            <div class="nk-wizard-footer" style="align-items: center;">
                <button type="button" class="nk-btn-nav nk-btn-prev" onclick="nk_change_step(4, 3)">&larr; Back</button>
                
                <input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form ); ?>" />
                <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
                <input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
                
                <button type="submit" name="submit_job" class="nk-btn-nav nk-btn-submit" value="preview">Preview & Publish &rarr;</button>
            </div>
        </div>
        <?php do_action( 'submit_job_form_end' ); ?>
    </form>
</div>

<script>
    function nk_change_step(current_step, next_step) {
        const currentSection = document.getElementById('step-' + current_step);
        const requiredInputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                input.style.borderColor = '#ef4444'; 
                isValid = false;
            } else {
                input.style.borderColor = '#cbd5e1';
            }
        });

        if (!isValid && next_step > current_step) {
            alert('Please fill out all required fields before proceeding.');
            return;
        }

        document.getElementById('step-' + current_step).classList.remove('active');
        document.getElementById('step-' + next_step).classList.add('active');

        document.getElementById('dot-' + current_step).classList.remove('active');
        if (next_step > current_step) { document.getElementById('dot-' + current_step).classList.add('completed'); } 
        else { document.getElementById('dot-' + current_step).classList.remove('completed'); }
        
        document.getElementById('dot-' + next_step).classList.add('active');
        document.getElementById('dot-' + next_step).classList.remove('completed');

        document.querySelector('.nk-job-post-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });

        setTimeout(function() {
            if (typeof tinymce !== 'undefined') {
                for (var i = 0; i < tinymce.editors.length; i++) {
                    tinymce.editors[i].execCommand('mceRepaint');
                }
            }
        }, 50);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Dynamic Salary Ranges Logic
        const typeSelect = document.getElementById('salary_type');
        const rangeSelect = document.getElementById('salary_range');

        if(typeSelect && rangeSelect) {
            const defaultOptions = `
                <option value="">Negotiable</option>
                <option value="0-5000">0 - 5,000</option>
                <option value="5000-10000">5,000 - 10,000</option>
                <option value="10000-20000">10,000 - 20,000</option>
                <option value="20000-30000">20,000 - 30,000</option>
                <option value="30000-45000">30,000 - 45,000</option>
                <option value="45000-60000">45,000 - 60,000</option>
                <option value="60000-100000">60,000 - 100,000</option>
                <option value="100000+">100,000+ (More)</option>
            `;
            const hourlyOptions = `
                <option value="">Negotiable</option>
                <option value="1-10">1 - 10</option>
                <option value="10-20">10 - 20</option>
                <option value="20-40">20 - 40</option>
                <option value="50-80">50 - 80</option>
                <option value="100-200">100 - 200</option>
                <option value="200-500">200 - 500</option>
                <option value="500-1000">500 - 1,000</option>
                <option value="1000+">1,000+</option>
                <option value="2000+">2,000+</option>
            `;

            typeSelect.addEventListener('change', function() {
                if(this.value === 'hourly') {
                    rangeSelect.innerHTML = hourlyOptions;
                } else {
                    rangeSelect.innerHTML = defaultOptions;
                }
            });
        }

        // 2. Premium Lock for "Featured Job" Checkbox
        let isPremiumUser = <?php echo (isset($is_premium) && $is_premium === 'true') ? 'true' : 'false'; ?>; 
        let featureCheckbox = document.getElementById('featured_job');
        
        if (featureCheckbox && !isPremiumUser) {
            featureCheckbox.disabled = true;
            let label = document.querySelector('label[for="featured_job"]');
            if(label) {
                label.innerHTML += ' <span style="background:#fffbeb; color:#d97706; padding:3px 8px; border-radius:12px; font-size:11px; margin-left:8px; border:1px solid #fcd34d;">🔒 Premium</span>';
                label.style.cursor = 'pointer';
                label.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    if(confirm("🚀 Featuring a job is a Premium Feature. Pinning your job to the top drives 5x more applicants. Upgrade your account now?")) {
                        window.location.href = '/pricing/';
                    }
                });
            }
        }

        // 3. The ModSecurity WAF Bypass Tunnel
        let form = document.getElementById('submit-job-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }
                let richFields = ['job_responsibilities', 'job_requirements', 'job_summary'];
                richFields.forEach(function(id) {
                    let field = document.getElementById(id);
                    if (field && field.value && !field.value.startsWith('NKB64:')) {
                        try {
                            field.value = 'NKB64:' + btoa(unescape(encodeURIComponent(field.value)));
                        } catch(err) { console.log('Tunnel encoding bypassed for ' + id); }
                    }
                });
            });
        }
    });

    // 4. Force WPJM Native Searchable Dropdown
    jQuery(document).ready(function($) {
        function unlockChosenSearch() {
            var $dropdowns = $('.job-manager-enhanced-select, #job_country');
            $dropdowns.each(function() {
                if ($.fn.chosen) {
                    $(this).chosen('destroy');
                    $(this).chosen({
                        search_contains: true,
                        disable_search: false,
                        disable_search_threshold: 0, // 0 = ALWAYS show search box
                        width: '100%',
                        placeholder_text_single: "Select or search..."
                    });
                }
            });
        }
        
        unlockChosenSearch();
        setTimeout(unlockChosenSearch, 500);
        setTimeout(unlockChosenSearch, 1500); // Ultimate failsafe
    });
</script>