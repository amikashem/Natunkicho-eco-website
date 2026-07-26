<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Edit Resume:', 'nk-recruitment'); ?> <?= esc_html($resume->resume_title) ?></h1>
    <a href="?page=nkrp-resume-create" class="page-title-action"><?php esc_html_e('Add New', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_resume'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($resume->id) ?>">
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Resume Core', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th><label><?php esc_html_e('Resume Title', 'nk-recruitment'); ?></label></th>
                                    <td>
                                        <input type="text" name="resume_title" class="large-text" value="<?= esc_attr($resume->resume_title) ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Assign to Candidate', 'nk-recruitment'); ?></label></th>
                                    <td>
                                        <select name="candidate_id" class="regular-text" required>
                                            <option value=""><?php esc_html_e('-- Select a Candidate --', 'nk-recruitment'); ?></option>
                                            <?php foreach ($candidates as $cand): ?>
                                                <option value="<?= esc_attr($cand->id) ?>" <?= selected($resume->candidate_id, $cand->id, false) ?>>
                                                    <?= esc_html($cand->first_name . ' ' . $cand->last_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Executive Objective / Summary', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($resume->objective ?? ''), 'objective', ['textarea_name' => 'objective', 'media_buttons' => false, 'textarea_rows' => 6]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Raw JSON Data (Phase 1 MVP)', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Experience Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="experience_data" class="large-text" rows="4"><?= esc_textarea($resume->experience_data) ?></textarea>

                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Education Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="education_data" class="large-text" rows="4"><?= esc_textarea($resume->education_data) ?></textarea>

                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Skills Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="skills_data" class="large-text" rows="3"><?= esc_textarea($resume->skills_data) ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Publish Settings', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <select name="status" class="widefat">
                                    <option value="active" <?= selected($resume->status, 'active', false) ?>><?php esc_html_e('Active (Searchable)', 'nk-recruitment'); ?></option>
                                    <option value="hidden" <?= selected($resume->status, 'hidden', false) ?>><?php esc_html_e('Hidden (Private)', 'nk-recruitment'); ?></option>
                                    <option value="draft" <?= selected($resume->status, 'draft', false) ?>><?php esc_html_e('Draft (Incomplete)', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p><label><input type="checkbox" name="is_primary" value="1" <?= checked($resume->is_primary, 1, false) ?>> <strong><?php esc_html_e('Primary Resume', 'nk-recruitment'); ?></strong></label></p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" class="button button-primary button-large" value="<?php esc_attr_e('Update Resume', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </form>
</div>