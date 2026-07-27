<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Add New Candidate', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-candidates" class="page-title-action"><?php esc_html_e('Back to List', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_candidate'); ?>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Basic Information', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th><label><?php esc_html_e('First Name', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="first_name" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Last Name', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="last_name" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Email Address', 'nk-recruitment'); ?></label></th>
                                    <td><input type="email" name="email" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Phone Number', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="phone" class="regular-text"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Professional Biography', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor('', 'bio', ['textarea_name' => 'bio', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Skills & Languages', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><strong><?php esc_html_e('Skills (Comma separated)', 'nk-recruitment'); ?></strong></label></p>
                            <textarea name="skills" class="large-text" rows="3" placeholder="e.g. Fine Dining, Micros POS, Team Leadership"></textarea>
                            
                            <p class="mt-4"><label><strong><?php esc_html_e('Languages', 'nk-recruitment'); ?></strong></label></p>
                            <input type="text" name="languages" class="large-text" placeholder="e.g. English (Fluent), French (Basic)">
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Status & Visibility', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <select name="status" class="widefat">
                                    <option value="active"><?php esc_html_e('Active (Seeking Jobs)', 'nk-recruitment'); ?></option>
                                    <option value="inactive"><?php esc_html_e('Inactive (Not Looking)', 'nk-recruitment'); ?></option>
                                    <option value="hired"><?php esc_html_e('Hired', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p><label><input type="checkbox" name="is_featured" value="1"> <strong><?php esc_html_e('Featured Candidate', 'nk-recruitment'); ?></strong></label></p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" class="button button-primary button-large" value="<?php esc_attr_e('Save Candidate', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Career Details', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><?php esc_html_e('Professional Title', 'nk-recruitment'); ?></label><input type="text" name="professional_title" class="widefat" placeholder="e.g. Executive Sous Chef"></p>
                            <p><label><?php esc_html_e('Experience (Years)', 'nk-recruitment'); ?></label><input type="number" name="experience_years" class="widefat" value="0" min="0"></p>
                            <p><label><?php esc_html_e('Education Level', 'nk-recruitment'); ?></label><input type="text" name="education_level" class="widefat"></p>
                            <p><label><?php esc_html_e('LinkedIn URL', 'nk-recruitment'); ?></label><input type="url" name="linkedin_url" class="widefat"></p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Location & Demographics', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><?php esc_html_e('City', 'nk-recruitment'); ?></label><input type="text" name="location_city" class="widefat"></p>
                            <p><label><?php esc_html_e('Country', 'nk-recruitment'); ?></label><input type="text" name="location_country" class="widefat"></p>
                            <p><label><?php esc_html_e('Nationality', 'nk-recruitment'); ?></label><input type="text" name="nationality" class="widefat"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>