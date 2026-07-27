<?php if (!defined('ABSPATH')) exit; 
// Scope: $user, $edit_company

$is_edit = isset($edit_company->id) && $edit_company->id > 0;
$title = $is_edit ? 'Edit Company Profile' : 'Add New Company';
$btn_text = $is_edit ? 'Update Company' : 'Create Company';

$logo_url = '';
if ($is_edit && !empty($edit_company->logo)) {
    $logo_url = is_numeric($edit_company->logo) ? wp_get_attachment_image_url($edit_company->logo, 'medium') : esc_url($edit_company->logo);
}

$clean_action_url = esc_url(home_url('/employer-dashboard/'));
?>

<div class="nkrp-dashboard-header">
    <h2><?= esc_html($title) ?></h2>
    <a href="<?= esc_url(add_query_arg('tab', 'companies', home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-secondary">
        <span class="dashicons dashicons-arrow-left-alt" style="margin-top:4px;"></span> Back to Companies
    </a>
</div>

<form method="POST" action="<?= $clean_action_url ?>" class="nkrp-frontend-form" enctype="multipart/form-data">
    
    <input type="hidden" name="nkrp_action" value="save_company">
    <?php wp_nonce_field('save_company_action', 'nkrp_company_nonce'); ?>
    
    <?php if ($is_edit): ?>
        <input type="hidden" name="company_id" value="<?= esc_attr((string)$edit_company->id) ?>">
    <?php endif; ?>
    
    <div class="nkrp-form-section">
        <h3><span class="dashicons dashicons-building"></span> Brand Identity</h3>
        <div class="nkrp-form-grid-avatar">
            <div class="nkrp-avatar-upload">
                <div class="nkrp-avatar-preview" id="nkrp-logo-preview" style="background-image: url('<?= $logo_url ?>');">
                    <?php if (empty($logo_url)): ?>
                        <span class="dashicons dashicons-images-alt2" style="font-size:32px; color:#94a3b8; width:32px; height:32px;"></span>
                    <?php endif; ?>
                </div>
                <div class="nkrp-file-input-wrapper">
                    <button type="button" class="nkrp-btn-secondary nkrp-sm">Upload Logo</button>
                    <input type="file" name="company_logo" accept="image/*" id="nkrp-logo-input">
                </div>
            </div>
            
            <div class="nkrp-form-grid-2">
                <div class="nkrp-form-group" style="grid-column: 1 / -1;">
                    <label>Company Name <span class="req">*</span></label>
                    <input type="text" name="company_name" required value="<?= esc_attr($edit_company->company_name ?? '') ?>">
                </div>
                <div class="nkrp-form-group">
                    <label>Industry</label>
                    <input type="text" name="industry" placeholder="e.g. Luxury Hotel" value="<?= esc_attr($edit_company->industry ?? '') ?>">
                </div>
                <div class="nkrp-form-group">
                    <label>Company Website</label>
                    <input type="text" name="website" placeholder="google.com or https://yourcompany.com" value="<?= esc_attr($edit_company->website ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="nkrp-form-section">
        <h3><span class="dashicons dashicons-location"></span> Contact & Location</h3>
        <div class="nkrp-form-grid-2">
            <div class="nkrp-form-group">
                <label>Public Email Address</label>
                <input type="email" name="company_email" placeholder="careers@company.com" value="<?= esc_attr($edit_company->company_email ?? '') ?>">
            </div>
            <div class="nkrp-form-group">
                <label>Public Phone Number</label>
                <input type="text" name="phone" placeholder="+1 234 567 8900" value="<?= esc_attr($edit_company->phone ?? '') ?>">
            </div>
            <div class="nkrp-form-group">
                <label>Country</label>
                <input type="text" name="country" value="<?= esc_attr($edit_company->country ?? '') ?>">
            </div>
            <div class="nkrp-form-group">
                <label>City</label>
                <input type="text" name="city" value="<?= esc_attr($edit_company->city ?? '') ?>">
            </div>
            <div class="nkrp-form-group" style="grid-column: 1 / -1;">
                <label>Full Address</label>
                <input type="text" name="address" value="<?= esc_attr($edit_company->address ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="nkrp-form-section">
        <h3><span class="dashicons dashicons-info"></span> Company Details</h3>
        <div class="nkrp-form-grid-2">
            <div class="nkrp-form-group">
                <label>Team Size</label>
                <select name="company_size" class="nkrp-input">
                    <option value="">Select Size...</option>
                    <option value="1-10" <?= selected($edit_company->company_size ?? '', '1-10', false) ?>>1-10 Employees</option>
                    <option value="11-50" <?= selected($edit_company->company_size ?? '', '11-50', false) ?>>11-50 Employees</option>
                    <option value="51-200" <?= selected($edit_company->company_size ?? '', '51-200', false) ?>>51-200 Employees</option>
                    <option value="201-500" <?= selected($edit_company->company_size ?? '', '201-500', false) ?>>201-500 Employees</option>
                    <option value="500+" <?= selected($edit_company->company_size ?? '', '500+', false) ?>>500+ Employees</option>
                </select>
            </div>
            <div class="nkrp-form-group">
                <label>Founded Year</label>
                <input type="number" name="founded_year" min="1800" max="<?= date('Y') ?>" placeholder="e.g. 2015" value="<?= esc_attr($edit_company->founded_year ?? '') ?>">
            </div>
            <div class="nkrp-form-group" style="grid-column: 1 / -1;">
                <label>About the Company</label>
                <!-- 10x FIX: Replaced dangerous wp_editor with a safe native textarea to stop memory crashes -->
                <textarea name="company_description" rows="8" class="nkrp-input" style="height: auto; padding: 15px; font-family: inherit;"><?= esc_textarea($edit_company->description ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="nkrp-form-actions" style="display:flex; justify-content:flex-end; margin-top:20px;">
        <button type="submit" class="nkrp-btn-primary">
            <span class="dashicons dashicons-yes"></span> <?= esc_html($btn_text) ?>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var logoInput = document.getElementById('nkrp-logo-input');
    var logoPreview = document.getElementById('nkrp-logo-preview');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    logoPreview.style.backgroundImage = 'url(' + event.target.result + ')';
                    logoPreview.innerHTML = ''; 
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});
</script>

<style>
    .nkrp-frontend-form { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .nkrp-form-section { margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 30px; }
    .nkrp-form-section:last-of-type { border-bottom: none; margin-bottom: 20px; padding-bottom: 0; }
    .nkrp-form-section h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; color: #0f172a; margin-top: 0; margin-bottom: 20px; font-weight: 600; }
    .nkrp-form-section h3 .dashicons { color: #2563eb; }
    .nkrp-form-grid-avatar { display: grid; grid-template-columns: 120px 1fr; gap: 30px; }
    .nkrp-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-avatar-upload { text-align: center; }
    .nkrp-avatar-preview { width: 100px; height: 100px; border-radius: 12px; border: 2px dashed #cbd5e1; margin: 0 auto 10px auto; background-color: #f8fafc; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .nkrp-file-input-wrapper { position: relative; overflow: hidden; display: inline-block; }
    .nkrp-file-input-wrapper input[type="file"] { font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; height: 100%; }
    .nkrp-form-group { margin-bottom: 15px; }
    .nkrp-form-group label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
    .nkrp-form-group label .req { color: #dc2626; }
    .nkrp-form-group input, .nkrp-form-group textarea, .nkrp-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff; transition: border 0.2s; }
    .nkrp-form-group input:focus, .nkrp-input:focus { border-color: #2563eb; outline: none; }
    .nkrp-input { height: 46px; }
    @media(max-width: 768px) { .nkrp-form-grid-avatar { grid-template-columns: 1fr; text-align: center; } .nkrp-form-grid-2 { grid-template-columns: 1fr; } }
</style>