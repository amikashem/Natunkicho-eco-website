<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-frontend-form-container">
    <div class="nkrp-form-header">
        <h2><?php esc_html_e('Build Your Resume', 'nk-recruitment'); ?></h2>
        <p><?php esc_html_e('Create a powerful digital profile to apply for hospitality jobs instantly.', 'nk-recruitment'); ?></p>
    </div>

    <?php if (isset($_GET['resume_error'])): ?>
        <div class="nkrp-alert nkrp-alert-error">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e('An error occurred saving your resume. Please check all fields and try again.', 'nk-recruitment'); ?>
        </div>
    <?php endif; ?>

    <!-- CRITICAL: enctype="multipart/form-data" is required for file uploads -->
    <form method="POST" action="" class="nkrp-job-form" id="nkrp-resume-form" enctype="multipart/form-data">
        <?php wp_nonce_field('nkrp_create_resume_action', 'nkrp_create_resume_nonce'); ?>
        
        <!-- SECTION 1: Basics -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-id"></span> <?php esc_html_e('Basic Information', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <label for="resume_title"><?php esc_html_e('Resume Title *', 'nk-recruitment'); ?></label>
                <input type="text" id="resume_title" name="resume_title" required placeholder="e.g. Senior Sous Chef with 10 Years Experience">
            </div>

            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="objective"><?php esc_html_e('Professional Summary / Objective', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                    </button>
                </div>
                <textarea id="objective" name="objective" rows="4" placeholder="Briefly describe your career goals and top strengths..."></textarea>
            </div>
        </div>

        <!-- NEW SECTION 2: Secure File Upload -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-media-document"></span> <?php esc_html_e('Attach Physical CV (Optional)', 'nk-recruitment'); ?></h3>
            <div class="nkrp-form-group">
                <label for="resume_file"><?php esc_html_e('Upload PDF or Word Document (Max 5MB)', 'nk-recruitment'); ?></label>
                <input type="file" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx" class="nkrp-file-input" style="width: 100%; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; cursor: pointer;">
            </div>
        </div>

        <!-- SECTION 3: Work Experience -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e('Work Experience', 'nk-recruitment'); ?></h3>
            
            <div id="experience-container">
                <div class="nkrp-repeater-block experience-block">
                    <div class="nkrp-repeater-header">
                        <h4>Experience 1</h4>
                        <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)" style="display:none;"><span class="dashicons dashicons-trash"></span> Remove</button>
                    </div>
                    <div class="nkrp-form-grid">
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('Job Title *', 'nk-recruitment'); ?></label>
                            <input type="text" name="experience[0][job_title]" required placeholder="e.g. Head Waiter">
                        </div>
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('Company / Hotel *', 'nk-recruitment'); ?></label>
                            <input type="text" name="experience[0][company]" required placeholder="e.g. The Ritz-Carlton">
                        </div>
                    </div>
                    
                    <div class="nkrp-form-grid">
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('Start Date', 'nk-recruitment'); ?></label>
                            <div style="display: flex; gap: 10px;">
                                <select name="experience[0][start_month]" style="width: 50%;">
                                    <option value="">Month</option>
                                    <option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option>
                                </select>
                                <input type="number" name="experience[0][start_year]" placeholder="YYYY" min="1950" max="2030" style="width: 50%;">
                            </div>
                        </div>
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('End Date', 'nk-recruitment'); ?></label>
                            <div style="display: flex; gap: 10px;">
                                <select name="experience[0][end_month]" style="width: 50%;">
                                    <option value="">Month</option>
                                    <option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option>
                                </select>
                                <input type="number" name="experience[0][end_year]" placeholder="YYYY" min="1950" max="2030" style="width: 50%;">
                            </div>
                            <label style="font-weight: normal; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" name="experience[0][current]"> I currently work here
                            </label>
                        </div>
                    </div>

                    <div class="nkrp-form-group">
                        <div class="nkrp-label-with-ai">
                            <label><?php esc_html_e('Description of Duties', 'nk-recruitment'); ?></label>
                            <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                                <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                            </button>
                        </div>
                        <textarea name="experience[0][description]" rows="3" placeholder="Describe your responsibilities and achievements..."></textarea>
                    </div>
                </div>
            </div>
            
            <button type="button" class="nkrp-btn-add-repeater" onclick="addExperience()">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Another Experience', 'nk-recruitment'); ?>
            </button>
        </div>

        <!-- SECTION 4: Education -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Education', 'nk-recruitment'); ?></h3>
            
            <div id="education-container">
                <div class="nkrp-repeater-block education-block">
                    <div class="nkrp-repeater-header">
                        <h4>Education 1</h4>
                        <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)" style="display:none;"><span class="dashicons dashicons-trash"></span> Remove</button>
                    </div>
                    <div class="nkrp-form-grid">
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('Degree / Certificate *', 'nk-recruitment'); ?></label>
                            <input type="text" name="education[0][degree]" required placeholder="e.g. B.S. in Hospitality Management">
                        </div>
                        <div class="nkrp-form-group">
                            <label><?php esc_html_e('School / Institution *', 'nk-recruitment'); ?></label>
                            <input type="text" name="education[0][institution]" required placeholder="e.g. Cornell University">
                        </div>
                    </div>
                    <div class="nkrp-form-group">
                        <label><?php esc_html_e('Graduation Year', 'nk-recruitment'); ?></label>
                        <input type="number" name="education[0][grad_year]" placeholder="e.g. 2020" style="max-width: 200px;">
                    </div>
                </div>
            </div>
            
            <button type="button" class="nkrp-btn-add-repeater" onclick="addEducation()">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Another Education', 'nk-recruitment'); ?>
            </button>
        </div>

        <!-- SECTION 5: Skills -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e('Top Skills', 'nk-recruitment'); ?></h3>
            <div class="nkrp-form-group">
                <label for="skills"><?php esc_html_e('Skills (Comma Separated)', 'nk-recruitment'); ?></label>
                <input type="text" id="skills" name="skills" placeholder="e.g. Team Leadership, Fine Dining, Inventory Management, POS Systems">
            </div>
        </div>

        <div class="nkrp-form-actions">
            <a href="<?= esc_url(home_url('/candidate-dashboard/')) ?>" class="nkrp-btn-secondary" style="margin-right: 15px; text-decoration: none;">
                <?php esc_html_e('Cancel & Return', 'nk-recruitment'); ?>
            </a>
            
            <button type="submit" name="nkrp_create_resume_submit" class="nkrp-btn-submit">
                <span class="dashicons dashicons-saved"></span> <?php esc_html_e('Save Resume Profile', 'nk-recruitment'); ?>
            </button>
        </div>
    </form>
</div>

<!-- DYNAMIC JAVASCRIPT FOR REPEATERS -->
<script>
    let expCount = 1;
    let eduCount = 1;

    function addExperience() {
        const container = document.getElementById('experience-container');
        const newBlock = document.createElement('div');
        newBlock.className = 'nkrp-repeater-block experience-block';
        
        const monthOptions = '<option value="">Month</option><option value="Jan">Jan</option><option value="Feb">Feb</option><option value="Mar">Mar</option><option value="Apr">Apr</option><option value="May">May</option><option value="Jun">Jun</option><option value="Jul">Jul</option><option value="Aug">Aug</option><option value="Sep">Sep</option><option value="Oct">Oct</option><option value="Nov">Nov</option><option value="Dec">Dec</option>';

        newBlock.innerHTML = `
            <div class="nkrp-repeater-header">
                <h4>Experience ${expCount + 1}</h4>
                <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)"><span class="dashicons dashicons-trash"></span> Remove</button>
            </div>
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label>Job Title *</label>
                    <input type="text" name="experience[${expCount}][job_title]" required>
                </div>
                <div class="nkrp-form-group">
                    <label>Company / Hotel *</label>
                    <input type="text" name="experience[${expCount}][company]" required>
                </div>
            </div>
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label>Start Date</label>
                    <div style="display: flex; gap: 10px;">
                        <select name="experience[${expCount}][start_month]" style="width: 50%;">${monthOptions}</select>
                        <input type="number" name="experience[${expCount}][start_year]" placeholder="YYYY" min="1950" max="2030" style="width: 50%;">
                    </div>
                </div>
                <div class="nkrp-form-group">
                    <label>End Date</label>
                    <div style="display: flex; gap: 10px;">
                        <select name="experience[${expCount}][end_month]" style="width: 50%;">${monthOptions}</select>
                        <input type="number" name="experience[${expCount}][end_year]" placeholder="YYYY" min="1950" max="2030" style="width: 50%;">
                    </div>
                    <label style="font-weight: normal; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="experience[${expCount}][current]"> I currently work here
                    </label>
                </div>
            </div>
            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label>Description of Duties</label>
                    <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                    </button>
                </div>
                <textarea name="experience[${expCount}][description]" rows="3"></textarea>
            </div>
        `;
        container.appendChild(newBlock);
        expCount++;
    }

    function addEducation() {
        const container = document.getElementById('education-container');
        const newBlock = document.createElement('div');
        newBlock.className = 'nkrp-repeater-block education-block';
        newBlock.innerHTML = `
            <div class="nkrp-repeater-header">
                <h4>Education ${eduCount + 1}</h4>
                <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)"><span class="dashicons dashicons-trash"></span> Remove</button>
            </div>
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label>Degree / Certificate *</label>
                    <input type="text" name="education[${eduCount}][degree]" required>
                </div>
                <div class="nkrp-form-group">
                    <label>School / Institution *</label>
                    <input type="text" name="education[${eduCount}][institution]" required>
                </div>
            </div>
            <div class="nkrp-form-group">
                <label>Graduation Year</label>
                <input type="number" name="education[${eduCount}][grad_year]" style="max-width: 200px;">
            </div>
        `;
        container.appendChild(newBlock);
        eduCount++;
    }

    function removeBlock(button) {
        button.closest('.nkrp-repeater-block').remove();
    }
</script>

<style>
    /* SaaS Form Styling */
    .nkrp-frontend-form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 0 auto; }
    .nkrp-form-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
    .nkrp-form-header h2 { margin: 0 0 8px 0; color: #0f172a; font-size: 24px; }
    .nkrp-form-header p { margin: 0; color: #64748b; }
    .nkrp-form-section { margin-bottom: 40px; }
    .nkrp-form-section h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; color: #1e293b; margin-bottom: 20px; font-weight: 600; }
    .nkrp-form-section h3 .dashicons { color: #2563eb; }
    
    /* AI Premium Button Styling */
    .nkrp-label-with-ai { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; }
    .nkrp-label-with-ai label { margin-bottom: 0; }
    .nkrp-ai-trigger { display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: not-allowed; transition: all 0.2s; }
    .nkrp-ai-trigger .dashicons { font-size: 12px; width: 12px; height: 12px; color: #cbd5e1; margin-top: 2px;}

    /* Repeater Specific Styles */
    .nkrp-repeater-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; position: relative; }
    .nkrp-repeater-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
    .nkrp-repeater-header h4 { margin: 0; font-size: 15px; color: #475569; }
    .nkrp-btn-remove { background: none; border: none; color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; }
    .nkrp-btn-add-repeater { background: #ffffff; border: 1px dashed #cbd5e1; color: #2563eb; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; width: 100%; justify-content: center; transition: all 0.2s; }
    
    .nkrp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-form-group { margin-bottom: 20px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="number"], .nkrp-form-group select, .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: #ffffff; height: auto; min-height: 48px;}
    .nkrp-form-group input:focus, .nkrp-form-group textarea:focus, .nkrp-form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .nkrp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: right; }
    .nkrp-btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    
    .nkrp-alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
    .nkrp-alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    
    /* NEW: Mobile Responsiveness for Resume Forms */
    @media (max-width: 768px) {
        .nkrp-frontend-form-container { padding: 25px; }
        .nkrp-form-grid { grid-template-columns: 1fr; gap: 15px; }
        .nkrp-form-grid > .nkrp-form-group { margin-bottom: 0; }
        
        /* Force date dropdowns to stack or fit nicely */
        .nkrp-form-group select, .nkrp-form-group input[type="number"] { width: 100% !important; margin-bottom: 10px; }
        .nkrp-form-group > div { flex-direction: column; gap: 0 !important; }
        
        .nkrp-form-actions { display: flex; flex-direction: column; gap: 15px; text-align: center; }
        .nkrp-btn-secondary { margin-right: 0 !important; justify-content: center; }
        .nkrp-btn-submit { width: 100%; justify-content: center; }
    }
</style>