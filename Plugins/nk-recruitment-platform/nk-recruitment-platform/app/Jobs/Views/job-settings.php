<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Job Platform Settings', 'nk-recruitment'); ?></h1>
    <hr class="wp-header-end">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e('Settings saved successfully.', 'nk-recruitment'); ?></strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('nkrp_job_settings'); ?>
        
        <div class="metabox-holder">
            
            <div class="postbox">
                <h2 class="hndle"><span><?php esc_html_e('General Configurations', 'nk-recruitment'); ?></span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="default_currency"><?php esc_html_e('Default Currency', 'nk-recruitment'); ?></label></th>
                            <td>
                                <input name="default_currency" type="text" id="default_currency" value="<?= esc_attr($default_currency) ?>" class="regular-text" style="max-width: 100px;">
                                <p class="description"><?php esc_html_e('Standard currency code (e.g., USD, AED, EUR).', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="job_expiry_days"><?php esc_html_e('Job Expiry Duration', 'nk-recruitment'); ?></label></th>
                            <td>
                                <input name="job_expiry_days" type="number" id="job_expiry_days" value="<?= esc_attr($job_expiry_days) ?>" class="regular-text" min="1" style="max-width: 100px;"> <strong><?php esc_html_e('Days', 'nk-recruitment'); ?></strong>
                                <p class="description"><?php esc_html_e('Number of days before a published job automatically expires and closes.', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="job_moderation"><?php esc_html_e('Employer Submissions', 'nk-recruitment'); ?></label></th>
                            <td>
                                <select name="job_moderation" id="job_moderation">
                                    <option value="publish" <?= selected($job_moderation, 'publish', false) ?>><?php esc_html_e('Auto-Publish Immediately', 'nk-recruitment'); ?></option>
                                    <option value="pending" <?= selected($job_moderation, 'pending', false) ?>><?php esc_html_e('Pending Review (Requires Admin Approval)', 'nk-recruitment'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('What happens when an employer submits a new job from the frontend?', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="salary_privacy"><?php esc_html_e('Salary Visibility', 'nk-recruitment'); ?></label></th>
                            <td>
                                <select name="salary_privacy" id="salary_privacy">
                                    <option value="public" <?= selected($salary_privacy, 'public', false) ?>><?php esc_html_e('Public (Everyone can see)', 'nk-recruitment'); ?></option>
                                    <option value="logged_in" <?= selected($salary_privacy, 'logged_in', false) ?>><?php esc_html_e('Logged-in Users Only', 'nk-recruitment'); ?></option>
                                    <option value="premium" <?= selected($salary_privacy, 'premium', false) ?>><?php esc_html_e('Premium Members Only', 'nk-recruitment'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Control who is allowed to see the exact salary figures on the frontend. Gating this behind Premium is a great way to drive subscriptions.', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><span><span class="dashicons dashicons-admin-site-alt3" style="margin-top:2px;"></span> <?php esc_html_e('Global Taxonomies & Lists', 'nk-recruitment'); ?></span></h2>
                <div class="inside">
                    <p class="description" style="margin-bottom: 20px;">
                        <?php esc_html_e('These lists control the dropdown menus seen by Employers when posting jobs and Candidates when searching. Enter ONE item per line.', 'nk-recruitment'); ?>
                    </p>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="global_countries"><?php esc_html_e('Available Countries', 'nk-recruitment'); ?></label>
                            </th>
                            <td>
                                <textarea name="global_countries" id="global_countries" rows="8" class="large-text code" style="max-width: 400px;"><?= esc_textarea($global_countries) ?></textarea>
                                <p class="description"><?php esc_html_e('E.g. United States, United Arab Emirates. (One country per line).', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="global_departments"><?php esc_html_e('Job Departments / Categories', 'nk-recruitment'); ?></label>
                            </th>
                            <td>
                                <textarea name="global_departments" id="global_departments" rows="8" class="large-text code" style="max-width: 400px;"><?= esc_textarea($global_departments) ?></textarea>
                                <p class="description"><?php esc_html_e('E.g. Engineering, Food & Beverage, Culinary. (One department per line).', 'nk-recruitment'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary button-large" value="<?php esc_attr_e('Save Configuration', 'nk-recruitment'); ?>">
        </p>
    </form>
</div>