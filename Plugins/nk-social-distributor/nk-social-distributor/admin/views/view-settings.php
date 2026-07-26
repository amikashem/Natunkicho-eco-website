<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Retrieve existing settings from the database
$options = get_option( 'nk_social_settings' );
?>

<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
    <form method="post" action="options.php">
        <?php
        // Output security fields for the registered setting
        settings_fields( 'nk_social_option_group' );
        do_settings_sections( 'nk_social_option_group' );
        ?>
        
        <h2>Platform API Credentials</h2>
        <p>Enable the platforms you want to post to and enter their connection credentials.</p>

        <table class="form-table">
            
            <tr valign="top">
                <th scope="row" colspan="2" style="background:#f0f0f1; padding: 10px; border-left: 4px solid #2271b1;">
                    <strong style="font-size: 16px;">Telegram Channel Integration</strong>
                </th>
            </tr>
            <tr valign="top">
                <th scope="row">Enable Telegram Auto-Post</th>
                <td>
                    <input type="checkbox" name="nk_social_settings[telegram_enabled]" value="1" <?php checked( 1, isset($options['telegram_enabled']) ? $options['telegram_enabled'] : 0, true ); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Telegram Bot Token</th>
                <td>
                    <input type="password" name="nk_social_settings[telegram_bot_token]" value="<?php echo esc_attr( isset($options['telegram_bot_token']) ? $options['telegram_bot_token'] : '' ); ?>" class="regular-text" placeholder="e.g. 123456789:ABCdefGHIjklMNOpqrSTUvwxyz" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Telegram Chat/Channel ID</th>
                <td>
                    <input type="text" name="nk_social_settings[telegram_chat_id]" value="<?php echo esc_attr( isset($options['telegram_chat_id']) ? $options['telegram_chat_id'] : '' ); ?>" class="regular-text" placeholder="e.g. @natunkicho" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" colspan="2" style="background:#f0f0f1; padding: 10px; border-left: 4px solid #0077b5; margin-top:20px;">
                    <strong style="font-size: 16px;">LinkedIn Company Page</strong>
                </th>
            </tr>
            <tr valign="top">
                <th scope="row">Enable LinkedIn Auto-Post</th>
                <td>
                    <input type="checkbox" name="nk_social_settings[linkedin_enabled]" value="1" <?php checked( 1, isset($options['linkedin_enabled']) ? $options['linkedin_enabled'] : 0, true ); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">LinkedIn Access Token</th>
                <td>
                    <input type="password" name="nk_social_settings[linkedin_token]" value="<?php echo esc_attr( isset($options['linkedin_token']) ? $options['linkedin_token'] : '' ); ?>" class="regular-text" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">LinkedIn Organization URN</th>
                <td>
                    <input type="text" name="nk_social_settings[linkedin_org_urn]" value="<?php echo esc_attr( isset($options['linkedin_org_urn']) ? $options['linkedin_org_urn'] : '' ); ?>" class="regular-text" placeholder="e.g. urn:li:organization:12345678" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" colspan="2" style="background:#f0f0f1; padding: 10px; border-left: 4px solid #1877f2; margin-top:20px;">
                    <strong style="font-size: 16px;">Facebook Page</strong>
                </th>
            </tr>
            <tr valign="top">
                <th scope="row">Enable Facebook Auto-Post</th>
                <td>
                    <input type="checkbox" name="nk_social_settings[facebook_enabled]" value="1" <?php checked( 1, isset($options['facebook_enabled']) ? $options['facebook_enabled'] : 0, true ); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Facebook Page Access Token</th>
                <td>
                    <input type="password" name="nk_social_settings[facebook_token]" value="<?php echo esc_attr( isset($options['facebook_token']) ? $options['facebook_token'] : '' ); ?>" class="regular-text" placeholder="EAAB..." />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Facebook Page ID</th>
                <td>
                    <input type="text" name="nk_social_settings[facebook_page_id]" value="<?php echo esc_attr( isset($options['facebook_page_id']) ? $options['facebook_page_id'] : '' ); ?>" class="regular-text" placeholder="e.g. 1029384756" />
                </td>
            </tr>

        </table>
        
        <?php submit_button('Save API Settings', 'primary', 'submit', true, array('style' => 'margin-top: 20px; font-size: 16px; padding: 5px 20px;')); ?>
    </form>
</div>