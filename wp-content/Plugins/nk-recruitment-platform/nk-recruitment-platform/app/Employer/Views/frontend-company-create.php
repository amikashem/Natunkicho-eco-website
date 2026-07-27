<?php if (!defined('ABSPATH')) exit; ?>

<div class="nkrp-frontend-form-container">
    <div class="nkrp-form-header">
        <h2><?php esc_html_e('Register Company Profile', 'nk-recruitment'); ?></h2>
        <p><?php esc_html_e('Create your corporate identity to start posting jobs and attracting talent.', 'nk-recruitment'); ?></p>
    </div>

    <?php if (isset($_GET['company_error'])): ?>
        <div class="nkrp-alert nkrp-alert-error">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e('An error occurred while creating your company. Please try again.', 'nk-recruitment'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="nkrp-job-form">
        <?php wp_nonce_field('nkrp_create_company_action', 'nkrp_create_company_nonce'); ?>
        
        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-building"></span> <?php esc_html_e('Basic Information', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <label for="company_name"><?php esc_html_e('Company Name *', 'nk-recruitment'); ?></label>
                <input type="text" id="company_name" name="company_name" required placeholder="e.g. The Grand Plaza Hotel">
            </div>

            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="industry"><?php esc_html_e('Industry Sector', 'nk-recruitment'); ?></label>
                    <select id="industry" name="industry" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Select sector...', 'nk-recruitment'); ?></option>
                        <option value="Hotels & Resorts">Hotels & Resorts</option>
                        <option value="Restaurant & Fine Dining">Restaurant & Fine Dining</option>
                        <option value="Catering & Events">Catering & Events</option>
                        <option value="Cruise Lines">Cruise Lines</option>
                        <option value="Spa & Wellness">Spa & Wellness</option>
                        <option value="Travel & Tourism">Travel & Tourism</option>
                        <option value="Airlines & Aviation">Airlines & Aviation</option>
                        <option value="Casino & Gaming">Casino & Gaming</option>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="company_size"><?php esc_html_e('Company Size', 'nk-recruitment'); ?></label>
                    <select id="company_size" name="company_size">
                        <option value=""><?php esc_html_e('Select size...', 'nk-recruitment'); ?></option>
                        <option value="1-10 Employees">1-10 Employees</option>
                        <option value="11-50 Employees">11-50 Employees</option>
                        <option value="51-200 Employees">51-200 Employees</option>
                        <option value="201-500 Employees">201-500 Employees</option>
                        <option value="500+ Employees">500+ Employees</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-phone"></span> <?php esc_html_e('Contact Details', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="company_email"><?php esc_html_e('Public Contact Email', 'nk-recruitment'); ?></label>
                    <input type="email" id="company_email" name="company_email" placeholder="e.g. careers@hotel.com">
                </div>
                <div class="nkrp-form-group">
                    <label for="phone"><?php esc_html_e('Phone Number', 'nk-recruitment'); ?></label>
                    <input type="text" id="phone" name="phone" placeholder="e.g. +1 (555) 000-0000">
                </div>
            </div>

            <div class="nkrp-form-group">
                <label for="website"><?php esc_html_e('Website URL', 'nk-recruitment'); ?></label>
                <input type="url" id="website" name="website" placeholder="https://www.yourhotel.com">
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-location"></span> <?php esc_html_e('Headquarters Location', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-grid">
                <div class="nkrp-form-group">
                    <label for="country"><?php esc_html_e('Country', 'nk-recruitment'); ?></label>
                    <select id="country" name="country" class="nkrp-select2">
                        <option value=""><?php esc_html_e('Select country...', 'nk-recruitment'); ?></option>
                        <?php foreach ($countries_array as $country): ?>
                            <option value="<?= esc_attr($country) ?>"><?= esc_html($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nkrp-form-group">
                    <label for="city"><?php esc_html_e('City', 'nk-recruitment'); ?></label>
                    <input type="text" id="city" name="city" placeholder="e.g. Dubai">
                </div>
            </div>

            <div class="nkrp-form-group">
                <label for="address"><?php esc_html_e('Full Address', 'nk-recruitment'); ?></label>
                <input type="text" id="address" name="address" placeholder="e.g. 123 Marina Boulevard">
            </div>
        </div>

        <div class="nkrp-form-section">
            <h3><span class="dashicons dashicons-edit"></span> <?php esc_html_e('About the Company', 'nk-recruitment'); ?></h3>
            
            <div class="nkrp-form-group">
                <label for="founded_year"><?php esc_html_e('Year Founded', 'nk-recruitment'); ?></label>
                <input type="number" id="founded_year" name="founded_year" placeholder="e.g. 2015" min="1800" max="2099" style="max-width: 200px;">
            </div>

            <div class="nkrp-form-group">
                <div class="nkrp-label-with-ai">
                    <label for="description"><?php esc_html_e('Company Overview', 'nk-recruitment'); ?></label>
                    <button type="button" class="nkrp-ai-trigger" disabled title="Upgrade to Premium to use AI Assist">
                        <span class="dashicons dashicons-lock"></span> AI Write (Premium)
                    </button>
                </div>
                <?php wp_editor('', 'description', ['media_buttons' => false, 'textarea_rows' => 6, 'teeny' => true]); ?>
                <p style="font-size:12px; color:#64748b; margin-top:5px;">Note: Logo and Cover Image uploads will be available in your Dashboard once the profile is created.</p>
            </div>
        </div>

        <div class="nkrp-form-actions">
            <button type="submit" name="nkrp_create_company_submit" class="nkrp-btn-submit">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Create Company Profile', 'nk-recruitment'); ?>
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
    /* SaaS Form Styling */
    .nkrp-frontend-form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 0 auto; }
    .nkrp-form-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
    .nkrp-form-header h2 { margin: 0 0 8px 0; color: #0f172a; font-size: 24px; }
    .nkrp-form-header p { margin: 0; color: #64748b; }
    .nkrp-form-section { margin-bottom: 40px; }
    .nkrp-form-section h3 { display: flex; align-items: center; gap: 8px; font-size: 18px; color: #1e293b; margin-bottom: 20px; font-weight: 600; }
    .nkrp-form-section h3 .dashicons { color: #2563eb; }
    .nkrp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .nkrp-form-group { margin-bottom: 20px; }
    .nkrp-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
    .nkrp-form-group input[type="text"], .nkrp-form-group input[type="number"], .nkrp-form-group input[type="email"], .nkrp-form-group input[type="url"], .nkrp-form-group select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: #ffffff; height: auto !important; min-height: 48px; line-height: 1.5; appearance: auto; }
    .nkrp-form-group input:focus, .nkrp-form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .select2-container--default .select2-selection--single { height: 48px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 48px !important; padding-left: 12px !important; color: #334155 !important; font-size: 15px !important;}
    .nkrp-label-with-ai { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px; }
    .nkrp-label-with-ai label { margin-bottom: 0; }
    .nkrp-ai-trigger { display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: not-allowed; transition: all 0.2s; }
    .nkrp-ai-trigger .dashicons { font-size: 12px; width: 12px; height: 12px; color: #cbd5e1; margin-top: 2px;}
    .nkrp-form-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: right; }
    .nkrp-btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .nkrp-btn-submit:hover { background: #1d4ed8; }
</style>