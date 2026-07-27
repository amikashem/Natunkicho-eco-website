<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Edit Candidate:', 'nk-recruitment'); ?> <?= esc_html($candidate->first_name . ' ' . $candidate->last_name) ?></h1>
    <a href="?page=nkrp-candidate-create" class="page-title-action"><?php esc_html_e('Add New', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_candidate'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($candidate->id) ?>">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Basic Information', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th><label><?php esc_html_e('First Name', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="first_name" class="regular-text" value="<?= esc_attr($candidate->first_name) ?>" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Last Name', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="last_name" class="regular-text" value="<?= esc_attr($candidate->last_name) ?>" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Email Address', 'nk-recruitment'); ?></label></th>
                                    <td><input type="email" name="email" class="regular-text" value="<?= esc_attr($candidate->email) ?>" required></td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Phone Number', 'nk-recruitment'); ?></label></th>
                                    <td><input type="text" name="phone" class="regular-text" value="<?= esc_attr($candidate->phone) ?>"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Professional Biography', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($candidate->bio ?? ''), 'bio', ['textarea_name' => 'bio', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Skills & Languages', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><strong><?php esc_html_e('Skills (Comma separated)', 'nk-recruitment'); ?></strong></label></p>
                            <textarea name="skills" class="large-text" rows="3"><?= esc_textarea($candidate->skills ?? '') ?></textarea>
                            
                            <p class="mt-4"><label><strong><?php esc_html_e('Languages', 'nk-recruitment'); ?></strong></label></p>
                            <input type="text" name="languages" class="large-text" value="<?= esc_attr($candidate->languages ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Status & Visibility', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <select name="status" class="widefat">
                                    <option value="active" <?= selected($candidate->status, 'active', false) ?>><?php esc_html_e('Active (Seeking Jobs)', 'nk-recruitment'); ?></option>
                                    <option value="inactive" <?= selected($candidate->status, 'inactive', false) ?>><?php esc_html_e('Inactive (Not Looking)', 'nk-recruitment'); ?></option>
                                    <option value="hired" <?= selected($candidate->status, 'hired', false) ?>><?php esc_html_e('Hired', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p><label><input type="checkbox" name="is_featured" value="1" <?= checked($candidate->is_featured, 1, false) ?>> <strong><?php esc_html_e('Featured Candidate', 'nk-recruitment'); ?></strong></label></p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" class="button button-primary button-large" value="<?php esc_attr_e('Update Candidate', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Career Details', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><?php esc_html_e('Professional Title', 'nk-recruitment'); ?></label><input type="text" name="professional_title" class="widefat" value="<?= esc_attr($candidate->professional_title ?? '') ?>"></p>
                            <p><label><?php esc_html_e('Experience (Years)', 'nk-recruitment'); ?></label><input type="number" name="experience_years" class="widefat" value="<?= esc_attr($candidate->experience_years ?? 0) ?>" min="0"></p>
                            <p><label><?php esc_html_e('Education Level', 'nk-recruitment'); ?></label><input type="text" name="education_level" class="widefat" value="<?= esc_attr($candidate->education_level ?? '') ?>"></p>
                            <p><label><?php esc_html_e('LinkedIn URL', 'nk-recruitment'); ?></label><input type="url" name="linkedin_url" class="widefat" value="<?= esc_url($candidate->linkedin_url ?? '') ?>"></p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Location & Demographics', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p><label><?php esc_html_e('City', 'nk-recruitment'); ?></label><input type="text" name="location_city" class="widefat" value="<?= esc_attr($candidate->location_city ?? '') ?>"></p>
                            <p><label><?php esc_html_e('Country', 'nk-recruitment'); ?></label><input type="text" name="location_country" class="widefat" value="<?= esc_attr($candidate->location_country ?? '') ?>"></p>
                            <p><label><?php esc_html_e('Nationality', 'nk-recruitment'); ?></label><input type="text" name="nationality" class="widefat" value="<?= esc_attr($candidate->nationality ?? '') ?>"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>