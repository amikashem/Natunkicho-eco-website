<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Resume Settings & Guide', 'nk-recruitment'); ?></h1>
    <hr class="wp-header-end">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e('Resume configuration saved successfully.', 'nk-recruitment'); ?></strong></p>
        </div>
    <?php endif; ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            
            <div id="post-body-content">
                <form method="post" action="">
                    <?php wp_nonce_field('nkrp_resume_settings'); ?>
                    
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Global Resume Configurations', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="default_template"><?php esc_html_e('Default CV Template', 'nk-recruitment'); ?></label></th>
                                    <td>
                                        <select name="default_template" id="default_template" class="regular-text">
                                            <option value="default" <?= selected($default_template, 'default', false) ?>><?php esc_html_e('Default (ATS-Friendly & Minimal)', 'nk-recruitment'); ?></option>
                                            <option value="modern" <?= selected($default_template, 'modern', false) ?>><?php esc_html_e('Modern (2-Column Creative)', 'nk-recruitment'); ?></option>
                                            <option value="professional" <?= selected($default_template, 'professional', false) ?>><?php esc_html_e('Professional (Corporate & Structured)', 'nk-recruitment'); ?></option>
                                            <option value="executive" <?= selected($default_template, 'executive', false) ?>><?php esc_html_e('Executive (Premium Dark Sidebar)', 'nk-recruitment'); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e('If a shortcode does not specify a template, this design will be used automatically.', 'nk-recruitment'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="allow_pdf_export"><?php esc_html_e('PDF Export (Future Feature)', 'nk-recruitment'); ?></label></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="allow_pdf_export" id="allow_pdf_export" value="yes" <?= checked($allow_pdf_export, 'yes', false) ?>>
                                            <strong><?php esc_html_e('Enable Frontend PDF Downloads', 'nk-recruitment'); ?></strong>
                                        </label>
                                        <p class="description"><?php esc_html_e('Prepares the system to allow candidates to download their rendered CV as a PDF.', 'nk-recruitment'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Configuration', 'nk-recruitment'); ?>">
                            </p>
                        </div>
                    </div>
                </form>
            </div>

            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox" style="border-top: 3px solid #3b82f6;">
                    <h2 class="hndle"><span class="dashicons dashicons-editor-code" style="margin-top:2px;"></span> <span><?php esc_html_e('Shortcode Implementation Guide', 'nk-recruitment'); ?></span></h2>
                    <div class="inside" style="font-size: 14px; line-height: 1.6;">
                        <p><?php esc_html_e('You can embed any candidate\'s resume on any WordPress page, post, or widget using the core shortcode:', 'nk-recruitment'); ?></p>
                        
                        <div style="background: #f1f5f9; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-family: monospace; font-size: 15px; margin-bottom: 15px;">
                            [nk_resume id="1"]
                        </div>

                        <h4 style="margin-bottom: 5px;"><?php esc_html_e('Attributes', 'nk-recruitment'); ?></h4>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li><strong>id</strong>: (Required) The numeric ID of the Resume.</li>
                            <li><strong>template</strong>: (Optional) Override the global default template setting.</li>
                        </ul>

                        <h4 style="margin-bottom: 5px;"><?php esc_html_e('Examples:', 'nk-recruitment'); ?></h4>
                        <code style="display:block; padding: 5px; background: #1e293b; color: #a5b4fc; border-radius: 4px; margin-bottom: 5px;">[nk_resume id="12" template="modern"]</code>
                        <code style="display:block; padding: 5px; background: #1e293b; color: #a5b4fc; border-radius: 4px; margin-bottom: 5px;">[nk_resume id="4" template="executive"]</code>
                        
                        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">
                        <p style="color: #64748b; font-style: italic;"><?php esc_html_e('Note: Only resumes with the status "Active" will render on the frontend. Hidden or Draft resumes will return an error message to protect candidate privacy.', 'nk-recruitment'); ?></p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>