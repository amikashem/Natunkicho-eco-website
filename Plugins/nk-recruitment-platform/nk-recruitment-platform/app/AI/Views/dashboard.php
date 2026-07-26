<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-superhero" style="font-size: 28px; width: 28px; height: 28px; margin-top: 2px;"></span> 
        <?php esc_html_e('AI Core Telemetry', 'nk-recruitment'); ?>
    </h1>
    <hr class="wp-header-end">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible"><p>Settings securely saved.</p></div>
    <?php endif; ?>

    <div class="nkrp-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="nkrp-stat-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; border-left:4px solid #3b82f6;">
            <span style="color:#64748b; font-size:13px; font-weight:600; text-transform:uppercase;">Estimated Cost</span>
            <span style="display:block; font-size:28px; font-weight:700; color:#0f172a; margin-top:5px;">$<?= esc_html(number_format((float)$total_cost, 4)) ?></span>
        </div>
        <div class="nkrp-stat-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; border-left:4px solid #10b981;">
            <span style="color:#64748b; font-size:13px; font-weight:600; text-transform:uppercase;">Tokens Processed</span>
            <span style="display:block; font-size:28px; font-weight:700; color:#0f172a; margin-top:5px;"><?= esc_html(number_format((float)$total_tokens)) ?></span>
        </div>
        <div class="nkrp-stat-card" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; border-left:4px solid #8b5cf6;">
            <span style="color:#64748b; font-size:13px; font-weight:600; text-transform:uppercase;">Total API Calls</span>
            <span style="display:block; font-size:28px; font-weight:700; color:#0f172a; margin-top:5px;"><?= esc_html(number_format((float)$total_requests)) ?></span>
        </div>
    </div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            
            <div id="post-body-content">
                <div class="postbox">
                    <h2 class="hndle"><span>Recent AI Operations (Last 50)</span></h2>
                    <div class="inside" style="padding:0; margin:0;">
                        <table class="wp-list-table widefat fixed striped" style="border:none;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>Model</th>
                                    <th>Tokens</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr><td colspan="6" style="padding:20px; text-align:center; color:#64748b;">No AI requests logged yet.</td></tr>
                                <?php else: foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= esc_html(wp_date('M j, H:i', strtotime($log->created_at))) ?></td>
                                        <td><span style="background:#f1f5f9; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600; text-transform:uppercase;"><?= esc_html($log->module) ?></span></td>
                                        <td><strong><?= esc_html($log->action) ?></strong></td>
                                        <td><code style="font-size:11px;"><?= esc_html($log->model_used) ?></code></td>
                                        <td><?= esc_html(number_format((float)$log->total_tokens)) ?></td>
                                        <td style="color:#059669; font-weight:600;">$<?= esc_html(number_format((float)$log->estimated_cost, 6)) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="postbox-container-1" class="postbox-container">
                <form method="post">
                    <?php wp_nonce_field('save_ai_settings', 'nkrp_ai_nonce'); ?>
                    <div class="postbox" style="border-top:3px solid #f59e0b;">
                        <h2 class="hndle"><span>Provider Configuration</span></h2>
                        <div class="inside">
                            
                            <p>
                                <label><strong>OpenAI API Key</strong></label><br>
                                <?php if (defined('nkjp_openai_key') && nkjp_openai_key !== ''): ?>
                                    <input type="text" class="widefat" value="HIDDEN (Loaded from wp-config.php)" disabled style="background:#f8fafc; color:#166534; border-color:#bbf7d0;">
                                <?php else: ?>
                                    <input type="password" name="openai_key" value="<?= esc_attr($current_openai) ?>" class="widefat" placeholder="sk-..." autocomplete="off">
                                <?php endif; ?>
                            </p>

                            <hr style="margin: 20px 0;">

                            <p>
                                <label><strong>Google Gemini API Key</strong></label><br>
                                <?php if (defined('nkjp_gemini_key') && nkjp_gemini_key !== ''): ?>
                                    <input type="text" class="widefat" value="HIDDEN (Loaded from wp-config.php)" disabled style="background:#f8fafc; color:#166534; border-color:#bbf7d0;">
                                <?php else: ?>
                                    <input type="password" name="gemini_key" value="<?= esc_attr($current_gemini) ?>" class="widefat" placeholder="AIza..." autocomplete="off">
                                <?php endif; ?>
                            </p>
                            
                               <p>
                                <label><strong>xAI Grok API Key</strong></label><br>
                                <?php if (defined('nkjp_grok_key') && nkjp_grok_key !== ''): ?>
                                    <input type="text" class="widefat" value="HIDDEN (wp-config.php)" disabled style="background:#f8fafc; color:#166534; border-color:#bbf7d0;">
                                <?php else: ?>
                                    <input type="password" name="grok_key" value="<?= esc_attr($current_grok) ?>" class="widefat" placeholder="xai-...">
                                <?php endif; ?>
                            </p>
                            <hr style="margin: 20px 0;">

                            <p>
                                <label><strong>GitHub Free Models Key</strong></label><br>
                                <?php if (defined('nkjp_github_key') && nkjp_github_key !== ''): ?>
                                    <input type="text" class="widefat" value="HIDDEN (wp-config.php)" disabled style="background:#f8fafc; color:#166534; border-color:#bbf7d0;">
                                <?php else: ?>
                                    <input type="password" name="github_key" value="<?= esc_attr($current_github) ?>" class="widefat" placeholder="ghp_...">
                                <?php endif; ?>
                            </p>

                            <hr>
                            <p style="margin-bottom:0;">
                                <input type="submit" class="button button-primary widefat" value="Save Database Keys">
                            </p>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>