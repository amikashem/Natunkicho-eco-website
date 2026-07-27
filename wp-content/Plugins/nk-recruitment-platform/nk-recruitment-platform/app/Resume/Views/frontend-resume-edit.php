<?php if (!defined('ABSPATH')) exit; ?>

<!-- Escape Hatch Navigation -->
<div class="nkrp-dashboard-nav-bar" style="margin-bottom: 20px;">
    <a href="<?= esc_url(home_url('/candidate-dashboard/')) ?>" class="nkrp-btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
        <span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e('Back to Dashboard', 'nk-recruitment'); ?>
    </a>
</div>

<div class="nkrp-frontend-form-container">
    <div class="nkrp-form-header">
        <h2><?php esc_html_e('Update Your Resume', 'nk-recruitment'); ?></h2>
        <p><?php esc_html_e('Keep your profile updated to increase your chances of getting hired.', 'nk-recruitment'); ?></p>
    </div>

    <!-- CRITICAL: enctype="multipart/form-data" is required for file uploads -->
    <form method="POST" action="" class="nkrp-job-form" id="nkrp-resume-form" enctype="multipart/form-data">
        <?php wp_nonce_field('nkrp_edit_resume_action', 'nkrp_edit_resume_nonce'); ?>
        <input type="hidden" name="resume_id" value="<?= esc_attr((string)$resume_id) ?>">

        <!-- SECTION 1: Basics -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-id"></span> <?php esc_html_e('Basic Information', 'nk-recruitment'); ?></h3>
            <div class="nkrp-form-group">
                <label for="resume_title"><?php esc_html_e('Resume Title *', 'nk-recruitment'); ?></label>
                <input type="text" id="resume_title" name="resume_title" required value="<?= esc_attr($resume_data->resume_title) ?>">
            </div>
            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="objective"><?php esc_html_e('Professional Summary / Objective', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                    </button>
                </div>
                <textarea id="objective" name="objective" rows="4"><?= esc_textarea($resume_data->objective) ?></textarea>
            </div>
        </div>

        <!-- SECTION 2: Secure File Upload (PDF/DOC) -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-media-document"></span> <?php esc_html_e('Attach Physical CV (Optional)', 'nk-recruitment'); ?></h3>
            
            <?php if (!empty($resume_data->file_path)): ?>
                <div class="nkrp-current-file-box">
                    <strong>Current Attached File:</strong> 
                    <a href="<?= esc_url($resume_data->file_path) ?>" target="_blank">View Document</a>
                    <input type="hidden" name="existing_file_path" value="<?= esc_attr($resume_data->file_path) ?>">
                </div>
            <?php endif; ?>

            <div class="nkrp-form-group">
                <label for="resume_file"><?php esc_html_e('Upload New PDF or Word Document (Max 5MB)', 'nk-recruitment'); ?></label>
                <input type="file" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx" class="nkrp-file-input">
                <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Uploading a new file will replace the current one.</p>
            </div>
        </div>

        <!-- SECTION 3: Work Experience -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e('Work Experience', 'nk-recruitment'); ?></h3>
            
            <div id="experience-container">
                <?php 
                if (empty($experience_array)): 
                    // Fallback to 1 empty block if nothing exists
                    $experience_array = [['job_title' => '', 'company' => '', 'start_date' => '', 'end_date' => '', 'description' => '']];
                endif;

                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

                foreach ($experience_array as $index => $exp): 
                    // Parse the saved text string (e.g. "Jan 2020") back into dropdowns
                    $start_parts = explode(' ', $exp['start_date'] ?? '');
                    $s_month = $start_parts[0] ?? '';
                    $s_year = $start_parts[1] ?? '';

                    $is_current = ($exp['end_date'] ?? '') === 'Present';
                    $end_parts = $is_current ? [] : explode(' ', $exp['end_date'] ?? '');
                    $e_month = $end_parts[0] ?? '';
                    $e_year = $end_parts[1] ?? '';
                ?>
                    <div class="nkrp-repeater-block experience-block">
                        <div class="nkrp-repeater-header">
                            <h4>Experience <?= $index + 1 ?></h4>
                            <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)" <?= $index === 0 ? 'style="display:none;"' : '' ?>><span class="dashicons dashicons-trash"></span> Remove</button>
                        </div>
                        <div class="nkrp-form-grid">
                            <div class="nkrp-form-group">
                                <label>Job Title *</label>
                                <input type="text" name="experience[<?= $index ?>][job_title]" required value="<?= esc_attr($exp['job_title'] ?? '') ?>">
                            </div>
                            <div class="nkrp-form-group">
                                <label>Company / Hotel *</label>
                                <input type="text" name="experience[<?= $index ?>][company]" required value="<?= esc_attr($exp['company'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="nkrp-form-grid">
                            <div class="nkrp-form-group">
                                <label>Start Date</label>
                                <div style="display: flex; gap: 10px;">
                                    <select name="experience[<?= $index ?>][start_month]" style="width: 50%;">
                                        <option value="">Month</option>
                                        <?php foreach($months as $m): ?>
                                            <option value="<?= $m ?>" <?= $s_month === $m ? 'selected' : '' ?>><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="experience[<?= $index ?>][start_year]" placeholder="YYYY" style="width: 50%;" value="<?= esc_attr($s_year) ?>">
                                </div>
                            </div>
                            <div class="nkrp-form-group">
                                <label>End Date</label>
                                <div style="display: flex; gap: 10px;">
                                    <select name="experience[<?= $index ?>][end_month]" style="width: 50%;">
                                        <option value="">Month</option>
                                        <?php foreach($months as $m): ?>
                                            <option value="<?= $m ?>" <?= $e_month === $m ? 'selected' : '' ?>><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="experience[<?= $index ?>][end_year]" placeholder="YYYY" style="width: 50%;" value="<?= esc_attr($e_year) ?>">
                                </div>
                                <label style="font-weight: normal; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" name="experience[<?= $index ?>][current]" <?= $is_current ? 'checked' : '' ?>> I currently work here
                                </label>
                            </div>
                        </div>
                        <div class="nkrp-form-group">
                            <label>Description of Duties</label>
                            <textarea name="experience[<?= $index ?>][description]" rows="3"><?= esc_textarea($exp['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="nkrp-btn-add-repeater" onclick="addExperience()">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Another Experience', 'nk-recruitment'); ?>
            </button>
        </div>

        <!-- SECTION 4: Education -->
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Education', 'nk-recruitment'); ?></h3>
            
            <div id="education-container">
                <?php 
                if (empty($education_array)): 
                    $education_array = [['degree' => '', 'institution' => '', 'grad_year' => '']];
                endif;

                foreach ($education_array as $index => $edu): 
                ?>
                    <div class="nkrp-repeater-block education-block">
                        <div class="nkrp-repeater-header">
                            <h4>Education <?= $index + 1 ?></h4>
                            <button type="button" class="nkrp-btn-remove" onclick="removeBlock(this)" <?= $index === 0 ? 'style="display:none;"' : '' ?>><span class="dashicons dashicons-trash"></span> Remove</button>
                        </div>
                        <div class="nkrp-form-grid">
                            <div class="nkrp-form-group">
                                <label>Degree / Certificate *</label>
                                <input type="text" name="education[<?= $index ?>][degree]" required value="<?= esc_attr($edu['degree'] ?? '') ?>">
                            </div>
                            <div class="nkrp-form-group">
                                <label>School / Institution *</label>
                                <input type="text" name="education[<?= $index ?>][institution]" required value="<?= esc_attr($edu['institution'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="nkrp-form-group">
                            <label>Graduation Year</label>
                            <input type="number" name="education[<?= $index ?>][grad_year]" style="max-width: 200px;" value="<?= esc_attr($edu['grad_year'] ?? '') ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
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
                <input type="text" id="skills" name="skills" value="<?= esc_attr($skills_string) ?>">
            </div>
        </div>

        <div class="nkrp-form-actions">
            <a href="<?= esc_url(home_url('/candidate-dashboard/')) ?>" class="nkrp-btn-secondary" style="margin-right: 15px; text-decoration: none;">
                <?php esc_html_e('Cancel', 'nk-recruitment'); ?>
            </a>
            <button type="submit" name="nkrp_edit_resume_submit" class="nkrp-btn-submit">
                <span class="dashicons dashicons-update"></span> <?php esc_html_e('Update Resume Profile', 'nk-recruitment'); ?>
            </button>
        </div>
    </form>
</div>

<!-- DYNAMIC JAVASCRIPT FOR REPEATERS -->
<script>
    // Initialize count based on how many blocks PHP just rendered!
    let expCount = <?= max(1, count($experience_array)) ?>;
    let eduCount = <?= max(1, count($education_array)) ?>;

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
                        <input type="number" name="experience[${expCount}][start_year]" placeholder="YYYY" style="width: 50%;">
                    </div>
                </div>
                <div class="nkrp-form-group">
                    <label>End Date</label>
                    <div style="display: flex; gap: 10px;">
                        <select name="experience[${expCount}][end_month]" style="width: 50%;">${monthOptions}</select>
                        <input type="number" name="experience[${expCount}][end_year]" placeholder="YYYY" style="width: 50%;">
                    </div>
                    <label style="font-weight: normal; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="experience[${expCount}][current]"> I currently work here
                    </label>
                </div>
            </div>
            <div class="nkrp-form-group">
                <label>Description of Duties</label>
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
    
    .nkrp-current-file-box { margin-bottom: 15px; padding: 15px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 14px; color: #166534; display: flex; align-items: center; gap: 10px;}
    .nkrp-current-file-box a { color: #15803d; font-weight: 600; text-decoration: underline; }
    .nkrp-file-input { width: 100%; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; cursor: pointer; }

    /* Repeater Specific Styles */
    .nkrp-repeater-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; position: relative; }
    .nkrp-repeater-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
    .nkrp-repeater-header h4 { margin: 0; font-size: 15px; color: #475569; }
    .nkrp-btn-remove { background: none; border: none; color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; }
    .nkrp-btn-remove:hover { text-decoration: underline; }
    
    .nkrp-btn-add-repeater { background: #ffffff; border: 1px dashed #cbd5e1; color: #2563eb; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; width: 100%; justify-content: center; transition: all 0.2s; }
    .nkrp-btn-add-repeater:hover { background: #eff6ff; border-color: #2563eb; }
    
    .nkrp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-form-group { margin-bottom: 20px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="number"], .nkrp-form-group select, .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: #ffffff; height: auto; min-height: 48px;}
    .nkrp-form-group input:focus, .nkrp-form-group textarea:focus, .nkrp-form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    
    /* AI Premium Label */
    .nkrp-label-with-ai { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; }
    .nkrp-label-with-ai label { margin-bottom: 0; }
    .nkrp-ai-trigger { display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: not-allowed; transition: all 0.2s; }
    
    .nkrp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: right; }
    .nkrp-btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
</style>