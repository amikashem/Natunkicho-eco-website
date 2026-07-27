<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Create New Hospitality Job', 'nk-recruitment'); ?></h1>
    <a href="?page=nkrp-jobs" class="page-title-action"><?php esc_html_e('Back to Jobs', 'nk-recruitment'); ?></a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_job'); ?>
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="title" size="30" value="" id="title" spellcheck="true" autocomplete="off" placeholder="<?php esc_attr_e('Enter job title here (e.g. Executive Sous Chef)', 'nk-recruitment'); ?>" required>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Job Description', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor('', 'description', ['textarea_name' => 'description', 'media_buttons' => false, 'textarea_rows' => 12]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Requirements & Qualifications', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor('', 'requirements', ['textarea_name' => 'requirements', 'media_buttons' => false, 'textarea_rows' => 8]); ?>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Benefits & Perks', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <?php wp_editor('', 'benefits', ['textarea_name' => 'benefits', 'media_buttons' => false, 'textarea_rows' => 6]); ?>
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
                                    <option value="publish"><?php esc_html_e('Published (Active)', 'nk-recruitment'); ?></option>
                                    <option value="draft" selected><?php esc_html_e('Draft (Hidden)', 'nk-recruitment'); ?></option>
                                    <option value="closed"><?php esc_html_e('Closed (Filled)', 'nk-recruitment'); ?></option>
                                </select>
                            </p>
                            <p>
                                <label><input type="checkbox" name="featured" value="1"> <strong><?php esc_html_e('Mark as Featured Job', 'nk-recruitment'); ?></strong></label>
                            </p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <input type="submit" name="save" id="publish" class="button button-primary button-large" value="<?php esc_attr_e('Create Job', 'nk-recruitment'); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Core Details', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label><?php esc_html_e('Company', 'nk-recruitment'); ?></label>
                        <select name="company_id" class="widefat" required>
                            <option value=""><?php esc_html_e('-- Select a Company --', 'nk-recruitment'); ?></option>
                            <?php if (!empty($companies)): ?>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= esc_attr($company->id) ?>" <?= (isset($job) && $job->company_id == $company->id) ? 'selected' : '' ?>>
                                        <?= esc_html($company->company_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No companies found. Please add a company first.', 'nk-recruitment'); ?></option>
                            <?php endif; ?>
                        </select>
                            </p>
                            <p>
                                <label><?php esc_html_e('External Application URL', 'nk-recruitment'); ?></label>
                                <input type="url" name="external_apply_url" class="widefat" value="<?= esc_url($job->external_apply_url ?? '') ?>" placeholder="https://careers.hotel.com/job/123">
                                <span class="description">Leave blank to use the internal Application system.</span>
                            </p>
                            
                            <p>
                                <label><?php esc_html_e('Job Type', 'nk-recruitment'); ?></label>
                                <select name="job_type" class="widefat">
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                            </p>
                            <p>
                                <label><?php esc_html_e('Department', 'nk-recruitment'); ?></label>
                                <input type="text" name="department" class="widefat" placeholder="e.g. Food & Beverage">
                            </p>
                            <p>
                                <label><?php esc_html_e('Location (City)', 'nk-recruitment'); ?></label>
                                <input type="text" name="location" class="widefat">
                            </p>
                            <p>
                                <label><?php esc_html_e('Country', 'nk-recruitment'); ?></label>
                                <input type="text" name="country" class="widefat">
                            </p>
                            <p>
                                <label><?php esc_html_e('Vacancies', 'nk-recruitment'); ?></label>
                                <input type="number" name="vacancies" class="widefat" value="1" min="1">
                            </p>
                            <p>
                                <label><?php esc_html_e('Application Deadline', 'nk-recruitment'); ?></label>
                                <input type="date" name="deadline" class="widefat">
                            </p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Compensation', 'nk-recruitment'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label><?php esc_html_e('Currency', 'nk-recruitment'); ?></label>
                                <input type="text" name="currency" class="widefat" value="USD" placeholder="e.g. USD, AED, EUR">
                            </p>
                            <p>
                                <label><?php esc_html_e('Minimum Salary', 'nk-recruitment'); ?></label>
                                <input type="number" step="0.01" name="salary_min" class="widefat">
                            </p>
                            <p>
                                <label><?php esc_html_e('Maximum Salary', 'nk-recruitment'); ?></label>
                                <input type="number" step="0.01" name="salary_max" class="widefat">
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>