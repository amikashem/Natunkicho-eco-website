<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================
 * AI CV BUILDER (Modular)
 * Path: inc/ai/ai-cv-builder.php
 * =========================================
 */

// 1. AJAX Handler to instantly save AI text to Profile Bio
function nk_ajax_save_ai_bio() {
    check_ajax_referer('nk_ai_save_nonce', 'security');
    if (!is_user_logged_in()) wp_send_json_error('Please login.');
    
    $bio = sanitize_textarea_field($_POST['ai_bio']);
    update_user_meta(get_current_user_id(), 'nk_bio', $bio);
    
    wp_send_json_success('Successfully saved to your profile!');
}
add_action('wp_ajax_nk_ajax_save_ai_bio', 'nk_ajax_save_ai_bio');

// 2. The Main Shortcode
function nk_ai_cv_builder_shortcode() {
    // --- LOCKED STATE ---
    if (!is_user_logged_in()) {
        $current_url = urlencode($_SERVER['REQUEST_URI']);
        return '
        <div class="nk-dash-card" style="text-align:center; padding: 60px 20px; max-width: 600px; margin: 40px auto; background: #fff; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
            <div style="font-size: 48px; margin-bottom: 20px;">🤖</div>
            <h2 style="font-size: 28px; margin-bottom: 15px;">Candidate Account Required</h2>
            <p style="color: #666; font-size: 16px; margin-bottom: 30px;">You need to be signed in as a Candidate to access the AI CV Builder.</p>
            <div style="display:flex; gap:15px; justify-content:center;">
                <a href="'.esc_url(home_url('/login/?redirect_to=' . $current_url)).'" class="nk-btn-primary">Sign In</a>
            </div>
        </div>';
    }

    $user_id = get_current_user_id();
    $output  = '';
    $error   = '';

    // Fetch existing profile data
    $saved_skills     = get_user_meta( $user_id, 'nk_skills', true );
    $saved_experience = get_user_meta( $user_id, 'nk_experience', true );

    // Process AI Request using the new NK AI Core Gateway
    if ( isset( $_POST['nk_generate_ai_cv'] ) && wp_verify_nonce( $_POST['nk_ai_cv_nonce'], 'generate_ai_cv' ) ) {
        $skills     = sanitize_text_field( $_POST['nk_skills'] );
        $experience = sanitize_textarea_field( $_POST['nk_experience'] );
        
        // 1. Prepare the user's data
        $user_prompt = "Skills: {$skills}\n\nExperience: {$experience}";

        // 2. Check if Gateway exists
        if ( ! class_exists('NK_AI_Gateway') ) {
            $error = 'AI Gateway is not active. Please contact support.';
        } else {
            // 3. Ping the Gateway! (bypass cache = true for fresh summaries)
            $response = NK_AI_Gateway::run( 'cv_module', 'generate_summary', $user_prompt, true );

            // 4. Handle the Response
            if ( $response['success'] ) {
                $output = $response['data'];
            } else {
                $error = 'AI Gateway Error: ' . $response['error'];
            }
        }
    }

    ob_start();
    // Load PDF generator library
    wp_enqueue_script('html2pdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js', [], null, true);
    ?>
    <div class="nk-ai-cv-builder nk-dash-card">
        <div class="nk-manage-header" style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h2 style="margin: 0;">AI CV Builder</h2>
            <p style="margin: 5px 0 0 0; color: #666;">Let our AI craft a professional resume summary based on your profile.</p>
        </div>

        <?php if ( ! empty( $error ) ) : ?>
            <div class="nk-error-notice" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo esc_html( $error ); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="nk-professional-form">
            <?php wp_nonce_field( 'generate_ai_cv', 'nk_ai_cv_nonce' ); ?>
            <fieldset>
                <label>Hospitality Skills</label>
                <input type="text" name="nk_skills" value="<?php echo esc_attr( $saved_skills ); ?>" required>
            </fieldset>
            <fieldset>
                <label>Experience</label>
                <textarea name="nk_experience" rows="4" required><?php echo esc_textarea( $saved_experience ); ?></textarea>
            </fieldset>
            <button type="submit" name="nk_generate_ai_cv" class="nk-btn-primary" style="background: #10b981; border: none; width: 100%;">
                ✨ Generate AI Summary
            </button>
        </form>

        <?php if ( ! empty( $output ) ) : ?>
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px dashed #cbd5e1;">
                <h3 style="margin-bottom: 15px;">✨ Your AI-Generated CV Summary</h3>
                
                <div id="nk-cv-document" style="background: #fff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; color: #333; line-height: 1.6;">
                    <?php echo nl2br(esc_html( $output )); ?>
                </div>
                
                <input type="hidden" id="nk_raw_ai_text" value="<?php echo esc_attr($output); ?>">
                <?php wp_nonce_field('nk_ai_save_nonce', 'nk_ai_save_security'); ?>

                <div style="display: flex; gap: 15px;">
                    <button id="nk-save-bio-btn" class="nk-btn-primary" style="flex:1;">
                        💾 Save to My Profile
                    </button>
                    <button id="nk-download-pdf-btn" class="nk-btn-primary" style="flex:1; background: #fff !important; color: #0A66C2 !important; border: 2px solid #0A66C2 !important;">
                        📄 Download PDF
                    </button>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1-Click Save to Profile
                const saveBtn = document.getElementById('nk-save-bio-btn');
                if(saveBtn){
                    saveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const originalText = this.innerText;
                        this.innerText = 'Saving...';
                        this.disabled = true;

                        let formData = new FormData();
                        formData.append('action', 'nk_ajax_save_ai_bio'); 
                        formData.append('ai_bio', document.getElementById('nk_raw_ai_text').value);
                        formData.append('security', document.getElementById('nk_ai_save_security').value);

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            saveBtn.innerText = '✅ Saved!';
                            if(typeof nk_show_toast === 'function') nk_show_toast(data.data, 'success');
                            setTimeout(() => { saveBtn.innerText = originalText; saveBtn.disabled = false; }, 3000);
                        });
                    });
                }

                // Download as PDF
                const pdfBtn = document.getElementById('nk-download-pdf-btn');
                if(pdfBtn){
                    pdfBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        this.innerText = 'Generating PDF...';
                        const element = document.getElementById('nk-cv-document');
                        const opt = {
                            margin:       1,
                            filename:     'Hospitality_CV_Summary.pdf',
                            image:        { type: 'jpeg', quality: 0.98 },
                            html2canvas:  { scale: 2 },
                            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                        };
                        html2pdf().set(opt).from(element).save().then(() => {
                            this.innerText = '📄 Download PDF';
                        });
                    });
                }
            });
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nk_ai_cv_builder', 'nk_ai_cv_builder_shortcode' );