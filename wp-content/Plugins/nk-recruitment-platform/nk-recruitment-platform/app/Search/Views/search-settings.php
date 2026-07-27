<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-search" style="font-size: 28px; width: 28px; height: 28px; margin-top: 2px;"></span> 
        <?php esc_html_e('Search Engine & Shortcodes', 'nk-recruitment'); ?>
    </h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            
            <div id="post-body-content">
                <div class="postbox">
                    <h2 class="hndle"><span><?php esc_html_e('Frontend Search Shortcodes', 'nk-recruitment'); ?></span></h2>
                    <div class="inside" style="font-size: 15px; line-height: 1.6;">
                        <p><?php esc_html_e('Copy and paste these shortcodes onto any WordPress Page or Post to display the interactive API-powered search engine.', 'nk-recruitment'); ?></p>
                        
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th><strong>Search Type</strong></th>
                                    <th><strong>Shortcode</strong></th>
                                    <th><strong>Description</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Job Search</strong></td>
                                    <td><code style="font-size: 14px; background: #e0e7ff; color: #3730a3; padding: 5px 10px; border-radius: 4px;">[nk_search type="jobs"]</code></td>
                                    <td>Displays the live Job Board search engine with Location and Job Type filters.</td>
                                </tr>
                                <tr>
                                    <td><strong>Company Directory</strong></td>
                                    <td><code style="font-size: 14px; background: #e0e7ff; color: #3730a3; padding: 5px 10px; border-radius: 4px;">[nk_search type="companies"]</code></td>
                                    <td>Displays the verified Employers and Company Directory with Industry filters.</td>
                                </tr>
                                <tr>
                                    <td><strong>Candidate Database</strong></td>
                                    <td><code style="font-size: 14px; background: #e0e7ff; color: #3730a3; padding: 5px 10px; border-radius: 4px;">[nk_search type="candidates"]</code></td>
                                    <td>Displays the Candidate search. <em>(Note: Contact info is hidden by the API to protect privacy).</em></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="postbox">
                    <h2 class="hndle"><span><?php esc_html_e('Global Search Configurations (Coming Soon)', 'nk-recruitment'); ?></span></h2>
                    <div class="inside">
                        <p style="color: #64748b;"><em>Settings to control default search radius, strict keyword matching, and AI semantic fallback will be unlocked in Search Pack 2.</em></p>
                    </div>
                </div>
            </div>

            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox" style="border-top: 3px solid #10b981;">
                    <h2 class="hndle"><span><?php esc_html_e('Engine Status', 'nk-recruitment'); ?></span></h2>
                    <div class="inside">
                        <p><span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span> <strong>REST API Gateway:</strong> Active</p>
                        <p><span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span> <strong>Dynamic Queries:</strong> Active</p>
                        <p><span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span> <strong>DDoS Protection:</strong> Active (Max 100/req)</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>