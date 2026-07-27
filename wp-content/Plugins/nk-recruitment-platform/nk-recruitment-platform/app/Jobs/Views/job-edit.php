<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Edit Job:', 'nk-recruitment'); ?> <?= esc_html($job->title) ?></h1>
    <a href="?page=nkrp-job-create" class="page-title-action"><?php esc_html_e('Add New', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_job'); ?>
        <input type="hidden" name="id" value="<?= esc_attr($job->id) ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="title" size="30" value="<?= esc_attr($job->title) ?>" id="title" spellcheck="true" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Job Description', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($job->description ?? ''), 'description', ['textarea_name' => 'description', 'media_buttons' => false, 'textarea_rows' => 12]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Key Responsibilities', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($job->responsibilities ?? ''), 'responsibilities', ['textarea_name' => 'responsibilities', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Requirements & Qualifications', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($job->requirements ?? ''), 'requirements', ['textarea_name' => 'requirements', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Benefits & Perks', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor(wp_kses_post($job->benefits ?? ''), 'benefits', ['textarea_name' => 'benefits', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Publishing', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label><strong><?php esc_html_e('Status:', 'nk-recruitment'); ?></strong></label>
                                <select name="status" class="widefat">
                                    <option value="publish" <?= selected($job->status ?? 'draft', 'publish', false) ?>><?php esc_html_e('Published', 'nk-recruitment'); ?></option>
                                    <option value="draft" <?= selected($job->status ?? 'draft', 'draft', false) ?>><?php esc_html_e('Draft', 'nk-recruitment'); ?></option>
                                    <option value="closed" <?= selected($job->status ?? 'draft', 'closed', false) ?>><?php esc_html_e('Closed', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p>
                                <label><input type="checkbox" name="featured" value="1" <?= checked($job->featured ?? 0, 1, false) ?>> <strong><?php esc_html_e('Mark as Featured Job', 'nk-recruitment'); ?></strong></label>
                            </p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" name="save" id="publish" class="button button-primary button-large" value="<?php esc_attr_e('Update Job', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Core Details', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label><?php esc_html_e('Company ID', 'nk-recruitment'); ?></label>
                                <input type="number" name="company_id" class="widefat" value="<?= esc_attr($job->company_id ?? 0) ?>">
                            </p>
                            <p>
                                <label><?php esc_html_e('External Application URL', 'nk-recruitment'); ?></label>
                                <input type="url" name="external_apply_url" class="widefat" value="<?= esc_url($job->external_apply_url ?? '') ?>" placeholder="https://careers.hotel.com/job/123">
                                <span class="description">Leave blank to use the internal Application system.</span>
                            </p>
                             
                            <p>
                                <label><?php esc_html_e('Job Type', 'nk-recruitment'); ?></label>
                                <select name="job_type" class="widefat">
                                    <option value="full_time" <?= selected($job->job_type ?? '', 'full_time', false) ?>>Full Time</option>
                                    <option value="part_time" <?= selected($job->job_type ?? '', 'part_time', false) ?>>Part Time</option>
                                    <option value="contract" <?= selected($job->job_type ?? '', 'contract', false) ?>>Contract</option>
                                    <option value="freelance" <?= selected($job->job_type ?? '', 'freelance', false) ?>>Freelance</option>
                                </select>
                            </p>
                            <p>
                                <label><?php esc_html_e('Department', 'nk-recruitment'); ?></label>
                                <input type="text" name="department" class="widefat" value="<?= esc_attr($job->department ?? $job->job_category ?? '') ?>"> 
                            </p>
                            <p>
                                <label><?php esc_html_e('Location (City)', 'nk-recruitment'); ?></label>
                                <input type="text" name="location" class="widefat" value="<?= esc_attr($job->location ?? $job->city ?? '') ?>"> 
                            </p>
                            <p>
                                <label><?php esc_html_e('Country', 'nk-recruitment'); ?></label>
                                <input type="text" name="country" class="widefat" value="<?= esc_attr($job->country ?? '') ?>">
                            </p>
                            <p>
                                <label><?php esc_html_e('Vacancies', 'nk-recruitment'); ?></label>
                                <input type="number" name="vacancies" class="widefat" value="<?= esc_attr($job->vacancies ?? 1) ?>">
                            </p>
                            <p>
                                <label><?php esc_html_e('Application Deadline', 'nk-recruitment'); ?></label>
                                <input type="date" name="deadline" class="widefat" value="<?= esc_attr($job->deadline ?? '') ?>">
                            </p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Compensation', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label><?php esc_html_e('Currency', 'nk-recruitment'); ?></label>
                                <input type="text" name="currency" class="widefat" value="<?= esc_attr($job->currency ?? 'USD') ?>">
                            </p>
                            <p>
                                <label><?php esc_html_e('Minimum Salary', 'nk-recruitment'); ?></label>
                                <input type="number" step="0.01" name="salary_min" class="widefat" value="<?= esc_attr($job->salary_min ?? '') ?>">
                            </p>
                            <p>
                                <label><?php esc_html_e('Maximum Salary', 'nk-recruitment'); ?></label>
                                <input type="number" step="0.01" name="salary_max" class="widefat" value="<?= esc_attr($job->salary_max ?? '') ?>">
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>