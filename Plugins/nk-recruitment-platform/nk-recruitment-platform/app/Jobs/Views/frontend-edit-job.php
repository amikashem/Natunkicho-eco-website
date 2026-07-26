<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-frontend-form-container">
    <div class="nkrp-form-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:15px;">
        <div>
            <h2 style="margin: 0 0 8px 0; color: #0f172a; font-size: 24px;"><?php esc_html_e('Edit Job', 'nk-recruitment'); ?></h2>
            <p style="margin: 0; color: #64748b;"><?php esc_html_e('Update the details of your job listing.', 'nk-recruitment'); ?></p>
        </div>
        <a href="<?= esc_url(home_url('/employer-dashboard/')) ?>" class="nkrp-btn-secondary" style="text-decoration:none;">
            <span class="dashicons dashicons-arrow-left-alt" style="margin-top:4px;"></span> Back to Dashboard
        </a>
    </div>

    <form method="POST" action="" class="nkrp-job-form">
        <?php wp_nonce_field('nkrp_edit_job_action', 'nkrp_edit_job_nonce'); ?>
        <input type="hidden" name="job_id" value="<?= esc_attr((string)$job->id) ?>">
        
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e('Basic Details', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <label for="company_id"><?php esc_html_e('Hiring Company', 'nk-recruitment'); ?></label>
                <select id="company_id" name="company_id" class="nkrp-select2-company">
                    <option value="0"><?php esc_html_e('None - Post as Independent Recruiter', 'nk-recruitment'); ?></option>
                    <?php if (!empty($employer_companies)): ?>
                        <optgroup label="Your Registered Companies">
                        <?php foreach ($employer_companies as $comp): ?>
                            <option value="<?= esc_attr((string)$comp->id) ?>" <?php selected($job->company_id, $comp->id); ?>><?= esc_html($comp->company_name) ?></option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <div class="nkrp-form-group">
                <label for="title"><?php esc_html_e('Job Title *', 'nk-recruitment'); ?></label>
                <input type="text" id="title" name="title" value="<?= esc_attr($job->title) ?>" required>
            </div>

            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="job_type"><?php esc_html_e('Job Type', 'nk-recruitment'); ?></label>
                    <select id="job_type" name="job_type" class="nkrp-select2">
                        <option value="Full-time" <?php selected($job->job_type, 'Full-time'); ?>>Full-time</option>
                        <option value="Part-time" <?php selected($job->job_type, 'Part-time'); ?>>Part-time</option>
                        <option value="Contract" <?php selected($job->job_type, 'Contract'); ?>>Contract</option>
                        <option value="Freelance" <?php selected($job->job_type, 'Freelance'); ?>>Freelance</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="department"><?php esc_html_e('Department', 'nk-recruitment'); ?></label>
                    <select id="department" name="department" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Select department... (Optional)', 'nk-recruitment'); ?></option>
                        <?php foreach ($departments_array as $dept): ?>
                            <option value="<?= esc_attr($dept) ?>" <?php selected($job->department, $dept); ?>><?= esc_html($dept) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="vacancies"><?php esc_html_e('Number of Vacancies', 'nk-recruitment'); ?></label>
                    <input type="number" id="vacancies" name="vacancies" value="<?= esc_attr((string)$job->vacancies) ?>" min="1" required>
                </div>
                <div class="nkrp-form-group">
                    <label for="deadline"><?php esc_html_e('Application Deadline', 'nk-recruitment'); ?></label>
                    <input type="date" id="deadline" name="deadline" value="<?= esc_attr(substr((string)$job->deadline, 0, 10)) ?>">
                </div>
            </div>

            <div class="nkrp-form-group" style="margin-top:15px;">
                <label for="external_apply_url"><?php esc_html_e('Company Website Application URL (Optional)', 'nk-recruitment'); ?></label>
                <input type="text" id="external_apply_url" name="external_apply_url" value="<?= esc_attr($job->external_apply_url ?? '') ?>" placeholder="e.g. google.com or https://yourcompany.com">
                <p style="color: #64748b; font-size: 12px; margin-top: 6px;">
                    <em><?php esc_html_e('Leave blank if you only want candidates to apply internally. If provided, candidates will see both an "Apply Now" button and a link to your website.', 'nk-recruitment'); ?></em>
                </p>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-location"></span> <?php esc_html_e('Location & Compensation', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="country"><?php esc_html_e('Country', 'nk-recruitment'); ?></label>
                    <select id="country" name="country" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Search country... (Optional)', 'nk-recruitment'); ?></option>
                        <?php foreach ($countries_array as $country): ?>
                            <option value="<?= esc_attr($country) ?>" <?php selected($job->country, $country); ?>><?= esc_html($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="location"><?php esc_html_e('City / Exact Location ', 'nk-recruitment'); ?></label>
                    <input type="text" id="location" name="location" value="<?= esc_attr($job->location) ?>" >
                </div>
            </div>

            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="salary_min"><?php esc_html_e('Minimum Salary', 'nk-recruitment'); ?></label>
                    <input type="number" id="salary_min" name="salary_min" value="<?= esc_attr((string)$job->salary_min) ?>">
                </div>
                <div class="nkrp-form-group">
                    <label for="salary_max"><?php esc_html_e('Maximum Salary', 'nk-recruitment'); ?></label>
                    <input type="number" id="salary_max" name="salary_max" value="<?= esc_attr((string)$job->salary_max) ?>">
                </div>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Candidate Profile', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="experience"><?php esc_html_e('Experience Level', 'nk-recruitment'); ?></label>
                    <select id="experience" name="experience" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Select level...', 'nk-recruitment'); ?></option>
                        <option value="Entry Level" <?php selected($job->experience ?? '', 'Entry Level'); ?>>Entry Level</option>
                        <option value="Mid Level" <?php selected($job->experience ?? '', 'Mid Level'); ?>>Mid Level</option>
                        <option value="Senior Level" <?php selected($job->experience ?? '', 'Senior Level'); ?>>Senior Level</option>
                        <option value="Executive" <?php selected($job->experience ?? '', 'Executive'); ?>>Executive</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="education"><?php esc_html_e('Education Level', 'nk-recruitment'); ?></label>
                    <select id="education" name="education" class="nkrp-select2">
                        <option value=""><?php esc_html_e('No minimum requirement', 'nk-recruitment'); ?></option>
                        <option value="High School" <?php selected($job->education ?? '', 'High School'); ?>>High School</option>
                        <option value="Bachelor\'s Degree" <?php selected($job->education ?? '', 'Bachelor\'s Degree'); ?>>Bachelor's Degree</option>
                        <option value="Master\'s Degree" <?php selected($job->education ?? '', 'Master\'s Degree'); ?>>Master's Degree</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-edit"></span> <?php esc_html_e('Job Description & Details', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <label for="description"><?php esc_html_e('Detailed Description *', 'nk-recruitment'); ?></label>
                <?php wp_editor($job->description, 'description', ['media_buttons' => false, 'textarea_rows' => 8, 'teeny' => true]); ?>
            </div>

            <div class="nkrp-form-group">
                <label for="responsibilities"><?php esc_html_e('Key Responsibilities', 'nk-recruitment'); ?></label>
                <textarea id="responsibilities" name="responsibilities" rows="5"><?= esc_textarea($job->responsibilities ?? '') ?></textarea>
            </div>

            <div class="nkrp-form-group">
                <label for="requirements"><?php esc_html_e('Requirements / Skills', 'nk-recruitment'); ?></label>
                <textarea id="requirements" name="requirements" rows="5"><?= esc_textarea($job->requirements ?? '') ?></textarea>
            </div>
        </div>

        <div class="nkrp-form-actions">
            <button type="submit" name="nkrp_edit_job_submit" class="nkrp-btn-submit">
                <span class="dashicons dashicons-yes"></span> <?php esc_html_e('Update Job', 'nk-recruitment'); ?>
            </button>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.nkrp-select2').select2({ width: '100%' });
    });
</script>

<style>
    .nkrp-frontend-form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 0 auto; }
    .nkrp-form-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
    .nkrp-form-section { margin-bottom: 40px; }
    .nkrp-form-section h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; color: #1e293b; margin-bottom: 20px; font-weight: 600; }
    .nkrp-form-section h3 .dashicons { color: #2563eb; }
    .nkrp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-form-group { margin-bottom: 20px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="number"], .nkrp-form-group input[type="date"], .nkrp-form-group select, .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: #ffffff; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="number"], .nkrp-form-group input[type="date"], .nkrp-form-group select { height: auto !important; min-height: 48px; line-height: 1.5; appearance: auto; }
    .nkrp-form-group textarea { font-family: inherit; resize: vertical; }
    .select2-container--default .select2-selection--single { height: 48px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 48px !important; padding-left: 12px !important; color: #334155 !important; font-size: 15px !important;}
    
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;}
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    
    .nkrp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: right; }
    .nkrp-btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
</style>