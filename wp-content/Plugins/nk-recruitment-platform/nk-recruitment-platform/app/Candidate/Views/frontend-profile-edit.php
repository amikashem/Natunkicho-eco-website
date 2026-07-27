<?php if (!defined('ABSPATH')) exit; 
// Scope: $candidate
?>

<div class="nkrp-dashboard-header">
    <h2>My Master Profile & CV</h2>
    <div style="display:flex; gap:10px;">
        <a href="<?= esc_url(add_query_arg('nkrp_action', 'export_cv', home_url('/candidate-dashboard/'))) ?>" target="_blank" class="nkrp-btn-secondary">
            <span class="dashicons dashicons-download"></span> Export Profile as CV
        </a>
        <a href="<?= esc_url(add_query_arg('tab', 'preview')) ?>" class="nkrp-btn-primary">
            <span class="dashicons dashicons-visibility"></span> Preview Public Profile
        </a>
    </div>
</div>

<?php if (isset($_GET['profile_updated']) && $_GET['profile_updated'] == '1'): ?>
    <div class="nkrp-alert nkrp-alert-success"><span class="dashicons dashicons-yes-alt"></span> Profile & CV updated successfully!</div>
<?php endif; ?>

<!-- PREMIUM TEASER BANNER -->
<div class="nkrp-premium-banner">
    <div class="nkrp-premium-content">
        <span class="dashicons dashicons-star-filled" style="color:#fbbf24; font-size:24px; width:24px; height:24px;"></span>
        <div>
            <strong>Unlock Premium Features</strong>
            <p>Create up to 5 tailored CVs for different jobs, Auto-Write your bio with AI, and get an Instant CV Audit.</p>
        </div>
    </div>
    <a href="<?= esc_url(home_url('/membership/')) ?>" class="nkrp-btn-upgrade">Upgrade Now</a>
</div>

<form method="POST" action="" class="nkrp-frontend-form" id="nkrp-profile-form" enctype="multipart/form-data">
    <?php wp_nonce_field('update_candidate_profile', 'nkrp_profile_nonce'); ?>
    
    <!-- SECTION: Photo & Basics -->
    <div class="nkrp-form-section">
        <h3>Personal Information</h3>
        <div class="nkrp-form-grid-avatar">
            <div class="nkrp-avatar-upload">
                <div class="nkrp-avatar-preview" id="nkrp-photo-preview" style="background-image: url('<?= !empty($candidate->profile_photo_id) ? esc_url(wp_get_attachment_image_url($candidate->profile_photo_id, 'medium')) : '' ?>');">
                    <?php if (empty($candidate->profile_photo_id)): ?><span class="dashicons dashicons-camera" style="font-size:32px; color:#94a3b8; width:32px; height:32px;"></span><?php endif; ?>
                </div>
                <div class="nkrp-file-input-wrapper">
                    <button type="button" class="nkrp-btn-secondary nkrp-sm">Choose Photo</button>
                    <input type="file" name="profile_photo" accept="image/*" id="nkrp-photo-input">
                </div>
            </div>
            <div class="nkrp-form-grid-2">
                <div class="nkrp-form-group">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" required value="<?= esc_attr($candidate->first_name ?? '') ?>">
                </div>
                <div class="nkrp-form-group">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" required value="<?= esc_attr($candidate->last_name ?? '') ?>">
                </div>
                <div class="nkrp-form-group">
                    <label>Professional Title</label>
                    <input type="text" name="professional_title" id="nkrp_title_input" placeholder="e.g. Senior Software Engineer" value="<?= esc_attr($candidate->professional_title ?? '') ?>">
                </div>
                <div class="nkrp-form-group">
                    <label>Years of Experience</label>
                    <input type="number" name="experience_years" min="0" value="<?= esc_attr((string)($candidate->experience_years ?? 0)) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION: Bio & Skills (AI ENABLED) -->
    <div class="nkrp-form-section">
        <h3>Professional Summary & Skills</h3>
        <div class="nkrp-form-group">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <label>Professional Summary</label>
                <button type="button" class="nkrp-btn-ai" onclick="nkrpGenerateAI('generate_bio', 'nkrp_bio_input')">
                    <span class="dashicons dashicons-superhero"></span> Auto-Write with AI
                </button>
            </div>
            <textarea name="bio" id="nkrp_bio_input" rows="5"><?= esc_textarea($candidate->bio ?? '') ?></textarea>
        </div>
        <div class="nkrp-form-group">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <label>Top Skills</label>
                <button type="button" class="nkrp-btn-ai" onclick="nkrpGenerateAI('suggest_skills', 'nkrp_skills_input')">
                    <span class="dashicons dashicons-lightbulb"></span> AI Suggestions
                </button>
            </div>
            <input type="text" name="skills" id="nkrp_skills_input" value="<?= esc_attr($candidate->skills ?? '') ?>">
        </div>
    </div>

    <!-- SECTION: Experience (Repeater) -->
    <div class="nkrp-form-section">
        <h3>Work Experience</h3>
        <div id="nkrp-experience-wrapper">
            <?php 
            $experiences = !empty($candidate->experience_data) ? $candidate->experience_data : [['title'=>'', 'company'=>'', 'date'=>'', 'desc'=>'']];
            foreach($experiences as $index => $exp): 
            ?>
            <div class="nkrp-repeater-row">
                <div class="nkrp-form-grid-2">
                    <div class="nkrp-form-group"><label>Job Title</label><input type="text" name="experience[<?= $index ?>][title]" value="<?= esc_attr($exp['title']) ?>"></div>
                    <div class="nkrp-form-group"><label>Company</label><input type="text" name="experience[<?= $index ?>][company]" value="<?= esc_attr($exp['company']) ?>"></div>
                    <div class="nkrp-form-group"><label>Dates (e.g. 2020 - Present)</label><input type="text" name="experience[<?= $index ?>][date]" value="<?= esc_attr($exp['date']) ?>"></div>
                </div>
                <div class="nkrp-form-group">
                    <label>Description</label>
                    <textarea name="experience[<?= $index ?>][desc]" rows="3"><?= esc_textarea($exp['desc']) ?></textarea>
                </div>
                <button type="button" class="nkrp-btn-remove-row" onclick="this.parentElement.remove()">Remove Entry</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="nkrp-btn-secondary" id="nkrp-add-exp-btn">+ Add Another Experience</button>
    </div>

    <!-- SECTION: Education (Repeater) -->
    <div class="nkrp-form-section">
        <h3>Education & Training</h3>
        <div id="nkrp-education-wrapper">
            <?php 
            $educations = !empty($candidate->education_data) ? $candidate->education_data : [['degree'=>'', 'institution'=>'', 'year'=>'']];
            foreach($educations as $index => $edu): 
            ?>
            <div class="nkrp-repeater-row">
                <div class="nkrp-form-grid-2">
                    <div class="nkrp-form-group"><label>Degree / Qualification</label><input type="text" name="education[<?= $index ?>][degree]" value="<?= esc_attr($edu['degree']) ?>"></div>
                    <div class="nkrp-form-group"><label>Institution</label><input type="text" name="education[<?= $index ?>][institution]" value="<?= esc_attr($edu['institution']) ?>"></div>
                    <div class="nkrp-form-group"><label>Graduation Year</label><input type="text" name="education[<?= $index ?>][year]" value="<?= esc_attr($edu['year']) ?>"></div>
                </div>
                <button type="button" class="nkrp-btn-remove-row" onclick="this.parentElement.remove()">Remove Entry</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="nkrp-btn-secondary" id="nkrp-add-edu-btn">+ Add Another Education</button>
    </div>

    <!-- SECTION: Attached CV Preview -->
    <div class="nkrp-form-section" style="background:#f8fafc; padding:20px; border-radius:8px; border:1px dashed #cbd5e1;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0 0 5px 0;">Attached CV Document</h3>
                <?php if (!empty($candidate->cv_id)): ?>
                    <p style="margin:5px 0; color:#166534;"><span class="dashicons dashicons-saved"></span> Current CV Uploaded</p>
                    <a href="<?= esc_url(wp_get_attachment_url($candidate->cv_id)) ?>" target="_blank" style="color:#2563eb; font-size:13px; font-weight:600; text-decoration:none;">
                        <span class="dashicons dashicons-external" style="font-size:14px; margin-top:3px;"></span> Preview Attached CV
                    </a>
                <?php else: ?>
                    <p style="margin:0; font-size:13px; color:#64748b;">No external CV attached.</p>
                <?php endif; ?>
            </div>
            <div class="nkrp-file-input-wrapper">
                <button type="button" class="nkrp-btn-secondary" id="nkrp-cv-btn-text">Upload/Replace Document</button>
                <input type="file" name="cv_document" accept=".pdf,.doc,.docx" id="nkrp-cv-input">
            </div>
        </div>
    </div>

    <!-- ACTION BAR -->
    <div class="nkrp-form-actions" style="display:flex; justify-content:space-between; margin-top:30px;">
        <button type="button" class="nkrp-btn-ai-audit" onclick="nkrpGenerateAI('audit_cv', '')">
            <span class="dashicons dashicons-analytics"></span> AI CV Audit
        </button>
        <button type="submit" name="nkrp_update_profile" class="nkrp-btn-primary">Save Profile</button>
    </div>
</form>

<style>
    /* Styling */
    .nkrp-premium-banner { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .nkrp-premium-content { display: flex; align-items: center; gap: 15px; }
    .nkrp-premium-content p { margin: 4px 0 0 0; font-size: 13px; color: #94a3b8; }
    .nkrp-btn-upgrade { background: #fbbf24; color: #78350f; padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .nkrp-btn-upgrade:hover { background: #f59e0b; color: #78350f; }

    .nkrp-frontend-form { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; }
    .nkrp-form-section { margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 30px; }
    .nkrp-form-section h3 { font-size: 18px; color: #0f172a; margin-top: 0; margin-bottom: 20px; }
    .nkrp-form-grid-avatar { display: grid; grid-template-columns: 120px 1fr; gap: 30px; }
    .nkrp-avatar-upload { text-align: center; }
    .nkrp-avatar-preview { width: 100px; height: 100px; border-radius: 50%; background-color: #f8fafc; border: 2px dashed #cbd5e1; margin: 0 auto 10px auto; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; }
    .nkrp-file-input-wrapper { position: relative; overflow: hidden; display: inline-block; }
    .nkrp-file-input-wrapper input[type="file"] { font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; height: 100%; }
    .nkrp-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-form-group { margin-bottom: 15px; }
    .nkrp-form-group label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .nkrp-form-group input, .nkrp-form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    
    /* Repeater Styles */
    .nkrp-repeater-row { background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; position: relative; }
    .nkrp-btn-remove-row { background: none; border: none; color: #dc2626; font-size: 13px; font-weight: 600; cursor: pointer; padding: 0; margin-top: 10px; }
    .nkrp-btn-remove-row:hover { text-decoration: underline; }
    
    /* AI Buttons */
    .nkrp-btn-ai { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s; }
    .nkrp-btn-ai:hover { background: #dcfce7; }
    .nkrp-btn-ai-audit { background: #fdf2f8; border: 1px solid #fbcfe8; color: #9d174d; border-radius: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .nkrp-btn-ai-audit:hover { background: #fce7f3; }
</style>

<script>
// File Previews
document.addEventListener('DOMContentLoaded', function() {
    var photoInput = document.getElementById('nkrp-photo-input');
    var photoPreview = document.getElementById('nkrp-photo-preview');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    photoPreview.style.backgroundImage = 'url(' + event.target.result + ')';
                    photoPreview.innerHTML = ''; 
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    var cvInput = document.getElementById('nkrp-cv-input');
    var cvBtnText = document.getElementById('nkrp-cv-btn-text');
    if (cvInput) {
        cvInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                cvBtnText.textContent = e.target.files[0].name;
                cvBtnText.style.backgroundColor = '#dcfce7';
                cvBtnText.style.color = '#166534';
                cvBtnText.style.borderColor = '#bbf7d0';
            }
        });
    }

    let expIndex = 99;
    document.getElementById('nkrp-add-exp-btn').addEventListener('click', function() {
        expIndex++;
        const tpl = `<div class="nkrp-repeater-row">
            <div class="nkrp-form-grid-2">
                <div class="nkrp-form-group"><label>Job Title</label><input type="text" name="experience[${expIndex}][title]"></div>
                <div class="nkrp-form-group"><label>Company</label><input type="text" name="experience[${expIndex}][company]"></div>
                <div class="nkrp-form-group"><label>Dates</label><input type="text" name="experience[${expIndex}][date]"></div>
            </div>
            <div class="nkrp-form-group"><label>Description</label><textarea name="experience[${expIndex}][desc]" rows="3"></textarea></div>
            <button type="button" class="nkrp-btn-remove-row" onclick="this.parentElement.remove()">Remove Entry</button>
        </div>`;
        document.getElementById('nkrp-experience-wrapper').insertAdjacentHTML('beforeend', tpl);
    });

    let eduIndex = 99;
    document.getElementById('nkrp-add-edu-btn').addEventListener('click', function() {
        eduIndex++;
        const tpl = `<div class="nkrp-repeater-row">
            <div class="nkrp-form-grid-2">
                <div class="nkrp-form-group"><label>Degree / Qualification</label><input type="text" name="education[${eduIndex}][degree]"></div>
                <div class="nkrp-form-group"><label>Institution</label><input type="text" name="education[${eduIndex}][institution]"></div>
                <div class="nkrp-form-group"><label>Graduation Year</label><input type="text" name="education[${eduIndex}][year]"></div>
            </div>
            <button type="button" class="nkrp-btn-remove-row" onclick="this.parentElement.remove()">Remove Entry</button>
        </div>`;
        document.getElementById('nkrp-education-wrapper').insertAdjacentHTML('beforeend', tpl);
    });
});

// AI AJAX Logic - With Smart Membership Redirect
function nkrpGenerateAI(actionType, targetInputId) {
    const btn = event.currentTarget;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = 'Loading AI...';
    btn.disabled = true;

    const title = document.getElementById('nkrp_title_input').value;
    const nonce = document.querySelector('input[name="nkrp_profile_nonce"]').value;

    jQuery.ajax({
        url: '<?= admin_url('admin-ajax.php') ?>',
        type: 'POST',
        data: {
            action: 'nkrp_ai_generate',
            security: nonce,
            ai_action: actionType,
            context: title
        },
        success: function(response) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if(response.success) {
                if(actionType === 'audit_cv') {
                    alert(response.data.data); 
                } else {
                    document.getElementById(targetInputId).value = response.data.data;
                }
            } else {
                // If they are not Premium, ask if they want to upgrade, then redirect to Pricing
                if (confirm(response.data.message + '\n\nClick OK to view our Premium Plans.')) {
                    window.location.href = response.data.redirect || '<?= esc_url(home_url('/membership/')) ?>';
                }
            }
        }
    });
}
</script>