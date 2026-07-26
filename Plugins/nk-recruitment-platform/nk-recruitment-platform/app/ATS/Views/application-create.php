<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline">Manual Application Entry</h1>
    <a href="?page=nkrp-applications" class="page-title-action">Back to Pipeline</a>
    <hr class="wp-header-end">
    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-1">
            <div id="post-body-content">
                <form method="post">
                    <?php wp_nonce_field('nkrp_application'); ?>
                    <div class="postbox">
                        <div class="inside">
                            <table class="form-table">
                                <tr><th><label>Select Candidate</label></th><td>
                                    <select name="candidate_id" required class="regular-text">
                                        <option value="">-- Choose Candidate --</option>
                                        <?php foreach($candidates as $c): ?><option value="<?= esc_attr((string)$c->id) ?>"><?= esc_html($c->label) ?></option><?php endforeach; ?>
                                    </select>
                                </td></tr>
                                <tr><th><label>Select Job</label></th><td>
                                    <select name="job_id" required class="regular-text">
                                        <option value="">-- Choose Job --</option>
                                        <?php foreach($jobs as $j): ?><option value="<?= esc_attr((string)$j->id) ?>"><?= esc_html($j->label) ?></option><?php endforeach; ?>
                                    </select>
                                </td></tr>
                                <tr><th><label>Select Company</label></th><td>
                                    <select name="company_id" required class="regular-text">
                                        <option value="">-- Choose Company --</option>
                                        <?php foreach($companies as $com): ?><option value="<?= esc_attr((string)$com->id) ?>"><?= esc_html($com->label) ?></option><?php endforeach; ?>
                                    </select>
                                </td></tr>
                                <tr><th><label>Cover Letter / Pitch</label></th><td><textarea name="cover_letter" rows="5" class="large-text"></textarea></td></tr>
                            </table>
                            <p class="submit"><input type="submit" class="button button-primary button-large" value="Inject into Pipeline"></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>