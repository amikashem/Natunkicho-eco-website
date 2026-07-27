<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Add New Resume', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-resumes" class="page-title-action"><?php esc_html_e('Back to List', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_resume'); ?>
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
                                        <input type="text" name="resume_title" class="large-text" required placeholder="e.g. Senior Sous Chef - 10 Yrs Experience">
                                    </td>
                                </tr>
                                <tr>
                                    <th><label><?php esc_html_e('Assign to Candidate', 'nk-recruitment'); ?></label></th>
                                    <td>
                                        <select name="candidate_id" class="regular-text" required>
                                            <option value=""><?php esc_html_e('-- Select a Candidate --', 'nk-recruitment'); ?></option>
                                            <?php foreach ($candidates as $cand): ?>
                                                <option value="<?= esc_attr($cand->id) ?>">
                                                    <?= esc_html($cand->first_name . ' ' . $cand->last_name . ' (' . $cand->email . ')') ?>
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
                            <?php wp_editor('', 'objective', ['textarea_name' => 'objective', 'media_buttons' => false, 'textarea_rows' => 6]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Raw JSON Data (Phase 1 MVP)', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p class="description"><?php esc_html_e('In Phase 1, we store structural data as JSON arrays. The CV Builder (Phase 2) will replace these with interactive UI blocks.', 'nk-recruitment'); ?></p>
                            
                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Experience Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="experience_data" class="large-text" rows="4" placeholder='[{"role":"Chef","company":"Hilton","years":"2018-2022"}]'>[]</textarea>

                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Education Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="education_data" class="large-text" rows="4" placeholder='[{"degree":"BSc Culinary Arts","school":"Le Cordon Bleu"}]'>[]</textarea>

                            <h4 style="margin-bottom:5px;"><?php esc_html_e('Skills Array (JSON)', 'nk-recruitment'); ?></h4>
                            <textarea name="skills_data" class="large-text" rows="3" placeholder='["Fine Dining", "Inventory Management", "French Cuisine"]'>[]</textarea>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Publish Settings', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <select name="status" class="widefat">
                                    <option value="active"><?php esc_html_e('Active (Searchable)', 'nk-recruitment'); ?></option>
                                    <option value="hidden"><?php esc_html_e('Hidden (Private)', 'nk-recruitment'); ?></option>
                                    <option value="draft"><?php esc_html_e('Draft (Incomplete)', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p><label><input type="checkbox" name="is_primary" value="1"> <strong><?php esc_html_e('Primary Resume', 'nk-recruitment'); ?></strong></label></p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" class="button button-primary button-large" value="<?php esc_attr_e('Save Resume', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </form>
</div>