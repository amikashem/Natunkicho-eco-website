<?php if (!defined('ABSPATH')) exit; 

if (isset($_GET['job_posted']) && $_GET['job_posted'] === 'success') {
    echo '<div style="padding: 40px; text-align: center; max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: -apple-system, sans-serif;">';
    echo '<span class="dashicons dashicons-email-alt" style="font-size: 56px; width: 56px; height: 56px; color: #2563eb; margin-bottom: 20px;"></span>';
    echo '<h2 style="font-size: 26px; color: #0f172a; margin-bottom: 10px; font-weight: 700;">Check Your Email to Publish</h2>';
    echo '<p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 0;">We saved your job securely! We just sent a <b>Magic Link</b> to your email address. Click the link to instantly verify your account and publish your job to the network.</p>';
    echo '</div>';
    return; 
}

global $wpdb;
$user_id = get_current_user_id();
$is_edit = isset($_GET['id']) && (int)$_GET['id'] > 0;
$job_id = $is_edit ? (int)$_GET['id'] : 0;

$edit_job = null;
if ($is_edit) {
    $jobs_table = $wpdb->prefix . 'nkrp_jobs';
    $edit_job = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$jobs_table} WHERE id = %d AND user_id = %d", $job_id, $user_id));
    if (!$edit_job) {
        echo "<div style='padding: 20px; background: #fef2f2; color: #b91c1c; border-radius: 8px;'>Error: Job not found or unauthorized.</div>";
        return;
    }
}

$j_title = $edit_job ? ($edit_job->job_title ?? $edit_job->title ?? '') : '';
$j_company = $edit_job ? ($edit_job->company_id ?? 0) : 0;
$j_dept = $edit_job ? ($edit_job->department ?? '') : '';
$j_loc = $edit_job ? ($edit_job->location ?? '') : '';
$j_country = $edit_job ? ($edit_job->country ?? '') : '';
$j_type = $edit_job ? ($edit_job->job_type ?? '') : '';
$j_sal_type = $edit_job ? ($edit_job->salary_type ?? 'Monthly') : 'Monthly';
$j_sal_range = $edit_job ? ($edit_job->salary_range ?? 'Negotiable') : 'Negotiable';
$j_vacancies = $edit_job ? ($edit_job->vacancies ?? 1) : 1;
$j_exp = $edit_job ? ($edit_job->experience ?? '') : '';
$j_edu = $edit_job ? ($edit_job->education ?? '') : '';
$j_deadline = ($edit_job && !empty($edit_job->deadline)) ? date('Y-m-d', strtotime($edit_job->deadline)) : '';
$j_app_email = $edit_job ? ($edit_job->notification_email ?? '') : '';
$j_ext_url = $edit_job ? ($edit_job->external_apply_url ?? '') : '';
$j_desc = $edit_job ? ($edit_job->description ?? '') : '';
$j_req = $edit_job ? ($edit_job->requirements ?? '') : '';
$j_resp = $edit_job ? ($edit_job->responsibilities ?? '') : '';

$raw_countries = get_option('nkrp_global_countries', "United Arab Emirates\nSaudi Arabia\nUnited States\nUnited Kingdom\nBangladesh");
$countries_array = array_filter(array_map('trim', explode("\n", $raw_countries)));
$raw_departments = get_option('nkrp_global_departments', "Management\nFood & Beverage\nCulinary");
$departments_array = array_filter(array_map('trim', explode("\n", $raw_departments)));

$employer_companies = [];
if (is_user_logged_in()) {
    $comp_table = $wpdb->prefix . 'nkrp_companies';
    $employer_companies = $wpdb->get_results($wpdb->prepare("SELECT id, company_name FROM {$comp_table} WHERE user_id = %d", $user_id));
}
?>

<div class="nkrp-frontend-form-container">
    <div class="nkrp-form-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:15px;">
        <div>
            <h2 style="margin: 0 0 8px 0; color: #0f172a; font-size: 24px;"><?= $is_edit ? 'Edit Job Posting' : esc_html__('Post a New Job', 'nk-recruitment') ?></h2>
            <p style="margin: 0; color: #64748b;"><?php esc_html_e('Fill out the details below to publish your job to the candidate network.', 'nk-recruitment'); ?></p>
        </div>
        
        <?php if (is_user_logged_in()): ?>
            <a href="<?= esc_url(home_url('/employer-dashboard/')) ?>" class="nkrp-btn-secondary" style="text-decoration:none;">
                <span class="dashicons dashicons-arrow-left-alt" style="margin-top:4px;"></span> Back to Dashboard
            </a>
        <?php else: ?>
            <a href="<?= esc_url(wp_login_url(home_url('/post-a-job/'))) ?>" class="nkrp-btn-secondary" style="text-decoration:none; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;">
                <span class="dashicons dashicons-admin-users" style="margin-top:4px;"></span> Existing Employer? Log In
            </a>
        <?php endif; ?>
    </div>

    <form method="POST" action="" class="nkrp-job-form">
        <?php wp_nonce_field('nkrp_edit_job_action', 'nkrp_edit_job_nonce'); ?>
        <input type="hidden" name="nkrp_action" value="edit_job_submit">
        <input type="hidden" name="job_id" value="<?= esc_attr((string)$job_id) ?>">
        
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e('Basic Details', 'nk-recruitment'); ?></h3>
            
            <?php if (is_user_logged_in()): ?>
                <div class="nkrp-form-group">
                    <div class="nkrp-label-with-ai">
                        <label for="company_id"><?php esc_html_e('Hiring Company', 'nk-recruitment'); ?></label>
                        <span style="font-size: 11px; background: #fef9c3; color: #a16207; padding: 3px 8px; border-radius: 12px; border: 1px solid #fde047; font-weight: 600;">
                            <span class="dashicons dashicons-star-filled" style="font-size:10px; width:10px; height:10px; margin-top:1px;"></span> Premium Branding
                        </span>
                    </div>
                    <select id="company_id" name="company_id" class="nkrp-select2-company">
                        <option value="0"><?php esc_html_e('None - Post as Independent Recruiter', 'nk-recruitment'); ?></option>
                        <?php if (!empty($employer_companies)): ?>
                            <optgroup label="Your Registered Companies">
                            <?php foreach ($employer_companies as $comp): ?>
                                <option value="<?= esc_attr((string)$comp->id) ?>" <?= $j_company == $comp->id ? 'selected' : '' ?>><?= esc_html($comp->company_name) ?></option>
                            <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="nkrp-form-group">
                    <label for="guest_company">Company Name</label>
                    <input type="text" id="guest_company" name="guest_company" placeholder="e.g. The Grand Hotel" required>
                </div>
            <?php endif; ?>

            <div class="nkrp-form-group">
                <label for="title"><?php esc_html_e('Job Title *', 'nk-recruitment'); ?></label>
                <input type="text" id="title" name="title" value="<?= esc_attr($j_title) ?>" required placeholder="e.g. Executive Chef">
            </div>

            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="job_type"><?php esc_html_e('Job Type', 'nk-recruitment'); ?></label>
                    <select id="job_type" name="job_type" class="nkrp-select2">
                        <option value="Full-Time" <?= $j_type === 'Full-Time' ? 'selected' : '' ?>>Full-Time</option>
                        <option value="Part-Time" <?= $j_type === 'Part-Time' ? 'selected' : '' ?>>Part-Time</option>
                        <option value="Contract" <?= $j_type === 'Contract' ? 'selected' : '' ?>>Contract</option>
                        <option value="Freelance" <?= $j_type === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="department"><?php esc_html_e('Department', 'nk-recruitment'); ?></label>
                    <select id="department" name="department" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Select department... (Optional)', 'nk-recruitment'); ?></option>
                        <?php foreach ($departments_array as $dept): ?>
                            <option value="<?= esc_attr($dept) ?>" <?= $j_dept === $dept ? 'selected' : '' ?>><?= esc_html($dept) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="vacancies"><?php esc_html_e('Number of Vacancies', 'nk-recruitment'); ?></label>
                    <input type="number" id="vacancies" name="vacancies" value="<?= esc_attr((string)$j_vacancies) ?>" min="1" required>
                </div>
                <div class="nkrp-form-group">
                    <label for="deadline"><?php esc_html_e('Application Deadline', 'nk-recruitment'); ?></label>
                    <input type="date" id="deadline" name="deadline" value="<?= esc_attr($j_deadline) ?>">
                </div>
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
                            <option value="<?= esc_attr($country) ?>" <?= $j_country === $country ? 'selected' : '' ?>><?= esc_html($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="location"><?php esc_html_e('City / Exact Location ', 'nk-recruitment'); ?></label>
                    <input type="text" id="location" name="location" value="<?= esc_attr($j_loc) ?>" placeholder="e.g. Dubai Marina">
                </div>
            </div>

            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="salary_type"><?php esc_html_e('Salary Base', 'nk-recruitment'); ?></label>
                    <select id="salary_type" name="salary_type" class="nkrp-select2">
                        <option value="Hourly" <?= $j_sal_type === 'Hourly' ? 'selected' : '' ?>>Hourly</option>
                        <option value="Monthly" <?= $j_sal_type === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="Yearly" <?= $j_sal_type === 'Yearly' ? 'selected' : '' ?>>Yearly</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="salary_range"><?php esc_html_e('Salary Range', 'nk-recruitment'); ?> <span style="font-weight:normal; color:#64748b; font-size:12px;">(Hidden from free users)</span></label>
                    <select name="salary_range" id="nk-salary-range" class="nkrp-select2" data-selected="<?= esc_attr($j_sal_range) ?>">
                    </select>
                </div>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e('Candidate Profile', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="experience"><?php esc_html_e('Experience Level', 'nk-recruitment'); ?></label>
                    <select id="experience" name="experience" class="nkrp-select2">
                        <option value="No Experience" <?= $j_exp === 'No Experience' ? 'selected' : '' ?>>No Experience</option>
                        <option value="1-3 Years" <?= $j_exp === '1-3 Years' ? 'selected' : '' ?>>1-3 Years</option>
                        <option value="3-5 Years" <?= $j_exp === '3-5 Years' ? 'selected' : '' ?>>3-5 Years</option>
                        <option value="5+ Years" <?= $j_exp === '5+ Years' ? 'selected' : '' ?>>5+ Years</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="education"><?php esc_html_e('Education Level', 'nk-recruitment'); ?></label>
                    <select id="education" name="education" class="nkrp-select2">
                        <option value="Not Required" <?= $j_edu === 'Not Required' ? 'selected' : '' ?>><?php esc_html_e('No minimum requirement', 'nk-recruitment'); ?></option>
                        <option value="High School" <?= $j_edu === 'High School' ? 'selected' : '' ?>>High School</option>
                        <option value="Bachelor's Degree" <?= $j_edu === 'Bachelor\'s Degree' ? 'selected' : '' ?>>Bachelor's Degree</option>
                        <option value="Master's Degree" <?= $j_edu === 'Master\'s Degree' ? 'selected' : '' ?>>Master's Degree</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-edit"></span> <?php esc_html_e('Job Description & Details', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="description"><?php esc_html_e('Detailed Description *', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" onclick="if(confirm('This is a Premium Feature. Upgrade to let AI write your job descriptions instantly.\n\nClick OK to view our Premium Plans.')) { window.location.href='<?= esc_url(home_url('/membership/')) ?>'; }" title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-superhero"></span> AI Write (Premium)
                    </button>
                </div>
                <?php wp_editor($j_desc, 'description', ['media_buttons' => false, 'textarea_rows' => 8, 'teeny' => true]); ?>
            </div>

            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="responsibilities"><?php esc_html_e('Key Responsibilities', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" onclick="if(confirm('This is a Premium Feature. Upgrade to let AI write your job responsibilities instantly.\n\nClick OK to view our Premium Plans.')) { window.location.href='<?= esc_url(home_url('/membership/')) ?>'; }" title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-superhero"></span> AI Write (Premium)
                    </button>
                </div>
                <textarea id="responsibilities" name="responsibilities" rows="5" placeholder="What will this person do on a daily basis?"><?= esc_textarea($j_resp) ?></textarea>
            </div>

            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="requirements"><?php esc_html_e('Requirements / Skills', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" onclick="if(confirm('This is a Premium Feature. Upgrade to let AI write your job requirements instantly.\n\nClick OK to view our Premium Plans.')) { window.location.href='<?= esc_url(home_url('/membership/')) ?>'; }" title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-superhero"></span> AI Write (Premium)
                    </button>
                </div>
                <textarea id="requirements" name="requirements" rows="5" placeholder="What skills are expected?"><?= esc_textarea($j_req) ?></textarea>
            </div>
            
            <div class="nkrp-form-group" style="margin-top:15px; border-top: 1px solid #e2e8f0; padding-top:20px;">
                <label for="notification_email"><?php esc_html_e('Application Notification Email (Optional)', 'nk-recruitment'); ?></label>
                <input type="email" id="notification_email" name="notification_email" value="<?= esc_attr($j_app_email) ?>" placeholder="e.g. hr@yourcompany.com">
                <p style="color: #64748b; font-size: 12px; margin-top: 6px;">
                    <em><?php esc_html_e('Leave blank to use your account\'s default registered email. If provided, all new applications for this specific job will be sent here.', 'nk-recruitment'); ?></em>
                </p>
            </div>

            <div class="nkrp-form-group" style="margin-top:15px;">
                <label for="external_apply_url"><?php esc_html_e('Company Website Application URL (Optional)', 'nk-recruitment'); ?></label>
                <input type="text" id="external_apply_url" name="external_apply_url" value="<?= esc_attr($j_ext_url) ?>" placeholder="e.g. google.com or https://yourcompany.com">
                <p style="color: #64748b; font-size: 12px; margin-top: 6px;">
                    <em><?php esc_html_e('Leave blank if you only want candidates to apply internally. If provided, candidates will see both an "Apply Now" button and a link to your website.', 'nk-recruitment'); ?></em>
                </p>
            </div>
        </div>

        <?php if (!is_user_logged_in()): ?>
            <div class="nkrp-form-section" style="background: #eff6ff; padding: 25px; border-radius: 12px; border: 1px solid #bfdbfe; margin-bottom: 30px;">
                <h3 style="margin-top: 0; font-size: 20px; color: #1e3a8a;"><span class="dashicons dashicons-email" style="color: #2563eb;"></span> Almost done! Let's secure your dashboard.</h3>
                <p style="color: #475569; font-size: 14px; margin-bottom: 20px;">Enter your primary email to create your free Employer account and publish this job instantly.</p>
                <div class="nkrp-form-grid">
                    <div class="nkrp-form-group" style="margin-bottom:0;">
                        <label for="guest_email">Your Account Email <span style="color:red;">*</span></label>
                        <input type="email" id="guest_email" name="guest_email" required placeholder="manager@hotel.com" style="border-color: #2563eb; box-shadow: 0 0 0 1px #2563eb;">
                    </div>
                    <div class="nkrp-form-group" style="margin-bottom:0;">
                        <label for="guest_name">Your Name (Optional)</label>
                        <input type="text" id="guest_name" name="guest_name" placeholder="e.g. John Doe">
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="nkrp-form-actions">
            <button type="submit" name="nkrp_post_job_submit" class="nkrp-btn-submit">
                <?php if (!is_user_logged_in()): ?>
                    <span class="dashicons dashicons-yes"></span> Publish Job & Create Account
                <?php else: ?>
                    <span class="dashicons dashicons-yes"></span> <?= $is_edit ? 'Update Job Details' : esc_html__('Publish Job', 'nk-recruitment') ?>
                <?php endif; ?>
            </button>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.nkrp-select2').select2({ width: '100%' });
        
        $('.nkrp-select2-company').select2({
            width: '100%',
            tags: true, 
            placeholder: "Select your company or type a new name...",
            allowClear: true,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') { return data; }
                if (typeof data.text === 'undefined') { return null; }
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) { return data; }
                return null;
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') { return null; }
                return { id: term, text: term + ' (Create New Company)', newTag: true }
            }
        });

        const typeSelect = document.getElementById("salary_type");
        const rangeSelect = document.getElementById("nk-salary-range");
        const selectedRange = rangeSelect.getAttribute("data-selected");

        const salaryRanges = {
            "Hourly": ["Negotiable", "1-10", "10-20", "20-40", "50-80", "100-200", "200-500", "500+"],
            "Monthly": ["Negotiable", "0-5000", "5000-10000", "10000-20000", "20000-30000", "30000-45000", "45000+"],
            "Yearly": ["Negotiable", "0-50000", "50000-100000", "100000-200000", "200000+"]
        };

        function updateRanges() {
            const currentType = typeSelect.value;
            const options = salaryRanges[currentType] || salaryRanges["Monthly"];
            $(rangeSelect).empty();
            options.forEach(opt => {
                let displayText = opt;
                if (opt !== "Negotiable") {
                    let parts = opt.split("-");
                    if (parts.length === 2) {
                        displayText = parseInt(parts[0]).toLocaleString() + " - " + parseInt(parts[1]).toLocaleString();
                    } else if (opt.includes("+")) {
                        displayText = parseInt(opt.replace("+", "")).toLocaleString() + "+";
                    }
                }
                const newOption = new Option(displayText, opt, false, opt === selectedRange);
                $(rangeSelect).append(newOption);
            });
            $(rangeSelect).trigger('change');
        }

        $('#salary_type').on('change', updateRanges);
        updateRanges(); 
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
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="email"], .nkrp-form-group input[type="number"], .nkrp-form-group input[type="date"], .nkrp-form-group select, .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: #ffffff; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="email"], .nkrp-form-group input[type="number"], .nkrp-form-group input[type="date"], .nkrp-form-group select { height: auto !important; min-height: 48px; line-height: 1.5; appearance: auto; }
    .nkrp-form-group textarea { font-family: inherit; resize: vertical; }
    .select2-container--default .select2-selection--single { height: 48px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 48px !important; padding-left: 12px !important; color: #334155 !important; font-size: 15px !important;}
    .nkrp-label-with-ai { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; }
    .nkrp-label-with-ai label { margin-bottom: 0; }
    .nkrp-ai-trigger { display: inline-flex; align-items: center; gap: 4px; background: #fdf2f8; border: 1px solid #fbcfe8; color: #9d174d; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
    .nkrp-ai-trigger:hover { background: #fce7f3; }
    .nkrp-ai-trigger .dashicons { font-size: 12px; width: 12px; height: 12px; color: #db2777; margin-top: 2px;}
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;}
    .nkrp-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    .nkrp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: right; }
    .nkrp-btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
    
    /* 🔥 NEW: MOBILE RESPONSIVENESS FIX */
    @media (max-width: 768px) {
        .nkrp-frontend-form-container { padding: 20px; }
        .nkrp-form-grid { grid-template-columns: 1fr; gap: 15px; }
    }
</style>