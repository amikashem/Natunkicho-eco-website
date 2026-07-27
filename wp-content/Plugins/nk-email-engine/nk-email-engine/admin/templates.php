<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$message = '';

// Handle Core Template Mapping
if ( isset( $_POST['nk_mapping_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nk_mapping_nonce'] ) ), 'nk_save_mapping' ) ) {
    update_option( 'nk_mapped_tpl_welcome', sanitize_text_field( $_POST['nk_mapped_tpl_welcome'] ) );
    update_option( 'nk_mapped_tpl_pwd_reset', sanitize_text_field( $_POST['nk_mapped_tpl_pwd_reset'] ) );
    $message = '<div class="notice notice-success is-dismissible" style="border-left-color: #10b981;"><p>✅ <strong>Core Event Mappings Saved!</strong> WordPress will now use your custom premium templates.</p></div>';
}

// Handle Template Creation
if ( isset( $_POST['nk_template_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nk_template_nonce'] ) ), 'nk_save_template' ) ) {
    $name    = isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : '';
    $type    = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
    $content = isset( $_POST['html_content'] ) ? wp_kses_post( wp_unslash( $_POST['html_content'] ) ) : '';

    NK_Template_Manager::create( $name, $type, $subject, $content );
    $message = '<div class="notice notice-success is-dismissible"><p>🚀 Premium Template saved successfully.</p></div>';
}

// Handle Template Deletion
if ( isset( $_GET['delete'] ) ) {
    NK_Template_Manager::delete( (int) $_GET['delete'] );
    $message = '<div class="notice notice-success is-dismissible"><p>🗑️ Template deleted.</p></div>';
}

$templates = NK_Template_Manager::get_all( '' );
$welcome_mapped = get_option('nk_mapped_tpl_welcome', '');
$pwd_mapped = get_option('nk_mapped_tpl_pwd_reset', '');
?>
<div class="wrap">
    <h1 style="font-weight: 800; border-left: 4px solid #0A66C2; padding-left: 15px; margin-bottom: 25px;">Email Templates & Core Mapping</h1>
    <?php echo $message; // phpcs:ignore ?>

    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid #0A66C2; max-width: 900px;">
        <h2 style="margin-top: 0; padding:0; font-size:18px;">🔄 Core System Email Assignments</h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:20px;">Force WordPress to stop sending generic plaintext emails. Select a premium template below, and the engine will automatically map native events to your design and route them through Amazon SES/Brevo.</p>
        
        <form method="post" style="display: flex; gap: 20px; align-items: flex-end;">
            <?php wp_nonce_field( 'nk_save_mapping', 'nk_mapping_nonce' ); ?>
            
            <div style="flex: 1;">
                <label style="display:block; font-weight:bold; margin-bottom:8px; font-size: 13px;">👋 New User Welcome Email</label>
                <select name="nk_mapped_tpl_welcome" style="width: 100%; padding: 6px;">
                    <option value="">-- Use Default WordPress Email --</option>
                    <?php if ( $templates ) : foreach ( $templates as $t ) : ?>
                        <option value="<?php echo esc_attr($t['id']); ?>" <?php selected($welcome_mapped, $t['id']); ?>><?php echo esc_html($t['template_name']); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div style="flex: 1;">
                <label style="display:block; font-weight:bold; margin-bottom:8px; font-size: 13px;">🔐 Password Reset Email</label>
                <select name="nk_mapped_tpl_pwd_reset" style="width: 100%; padding: 6px;">
                    <option value="">-- Use Default WordPress Email --</option>
                    <?php if ( $templates ) : foreach ( $templates as $t ) : ?>
                        <option value="<?php echo esc_attr($t['id']); ?>" <?php selected($pwd_mapped, $t['id']); ?>><?php echo esc_html($t['template_name']); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <button type="submit" class="button button-primary" style="background:#0f172a; border-color:#0f172a; font-weight:bold; height: 35px;">Save Mappings</button>
        </form>
    </div>

    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px; max-width: 900px;">
        <h2 style="margin-top: 0; padding:0; font-size:18px;">✨ Create New Template</h2>
        <form method="post">
            <?php wp_nonce_field( 'nk_save_template', 'nk_template_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="template_name" style="font-weight:bold;">Template Name</label></th>
                    <td><input type="text" id="template_name" name="template_name" class="regular-text" required placeholder="e.g. Master Welcome Template"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="template_type" style="font-weight:bold;">Category Type</label></th>
                    <td>
                        <select id="template_type" name="template_type" style="padding:4px 8px;">
                            <option value="transactional">Transactional (Password/Welcome)</option>
                            <option value="job_alert">Job Alert / Candidate Match</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="blog">Blog Notification</option>
                            <option value="recipe">Recipe Update</option>
                            <option value="training">Training / Courses</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="subject" style="font-weight:bold;">Email Subject Line</label></th>
                    <td><input type="text" id="subject" name="subject" class="large-text" required placeholder="Welcome to NatunKicho, {{name}}!"></td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="html_content" style="font-weight:bold;">HTML Content</label><br><br>
                        <span style="font-size:11px; color:#64748b; font-weight:normal;">Paste your inline HTML email template here. Do not use Visual mode.</span>
                    </th>
                    <td>
                        <textarea id="html_content" name="html_content" rows="12" class="large-text" required style="font-family: monospace; font-size: 13px; background: #f8fafc;"></textarea>
                        <div style="background:#f1f5f9; padding:12px; border-radius:6px; margin-top:10px; font-size:12px; color:#475569;">
                            <strong>⚡ Available Dynamic Tags:</strong><br>
                            <code>{{name}}</code>, <code>{{email}}</code>, <code>{{job_title}}</code>, <code>{{company_name}}</code>, <code>{{unsubscribe_link}}</code>, <code>{{action_link}}</code>, <code>{{action_text}}</code>
                        </div>
                    </td>
                </tr>
            </table>
            <p><button type="submit" class="button button-primary" style="background:#10b981; border-color:#059669; font-weight:bold; padding: 6px 20px; height:auto;">Save Premium Template</button></p>
        </form>
    </div>

    <h2 style="margin-top: 0; padding:0; font-size:18px;">Saved Templates Library</h2>
    <table class="widefat striped" style="border-radius:8px; overflow:hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <thead><tr style="background:#f8fafc;"><th style="padding:15px;">Name</th><th>Type</th><th>Subject Line</th><th>Status</th><th>Last Updated</th><th>Action</th></tr></thead>
        <tbody>
        <?php if ( $templates ) : foreach ( $templates as $t ) : ?>
            <tr>
                <td style="padding:12px; font-weight:bold; color:#0f172a;"><?php echo esc_html( $t['template_name'] ); ?></td>
                <td><span style="background:#e2e8f0; color:#334155; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold; text-transform:uppercase;"><?php echo esc_html( $t['template_type'] ); ?></span></td>
                <td style="color:#64748b;"><?php echo esc_html( $t['subject'] ); ?></td>
                <td><span style="color:#10b981; font-weight:bold; font-size:11px; text-transform:uppercase;"><?php echo esc_html( $t['status'] ); ?></span></td>
                <td style="color:#64748b; font-size:12px;"><?php echo esc_html( date('M j, Y g:i A', strtotime($t['updated_at'])) ); ?></td>
                <td><a href="?page=nk-email-templates&delete=<?php echo esc_attr( $t['id'] ); ?>" onclick="return confirm('Are you sure you want to delete this template?');" style="color:#ef4444; font-weight:bold; text-decoration:none;">Delete</a></td>
            </tr>
        <?php endforeach; else : ?>
            <tr><td colspan="6" style="padding:30px; text-align:center; color:#64748b;">No templates exist yet. Create your first premium template above.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>