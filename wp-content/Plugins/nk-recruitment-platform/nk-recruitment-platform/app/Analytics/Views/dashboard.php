<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap nkrp-admin-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-chart-area" style="font-size: 28px; width: 28px; height: 28px; margin-top: 2px;"></span> 
            <?php esc_html_e('Platform Analytics & Intelligence', 'nk-recruitment'); ?>
        </h1>
        
        <div class="nkrp-export-actions">
            <a href="?page=nkrp-analytics&export_format=csv" class="button"><span class="dashicons dashicons-media-spreadsheet"></span> CSV</a>
            <a href="?page=nkrp-analytics&export_format=excel" class="button"><span class="dashicons dashicons-media-spreadsheet"></span> Excel</a>
            <a href="?page=nkrp-analytics&export_format=pdf" class="button button-primary"><span class="dashicons dashicons-pdf"></span> Report</a>
        </div>
    </div>
    <hr class="wp-header-end">

    <style>
        .nkrp-metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .nkrp-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .nkrp-card-title { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .nkrp-card-value { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
        .nkrp-card-sub { font-size: 12px; color: #94a3b8; display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px solid #f1f5f9; }
    </style>

    <h2 class="title">Jobs & Market Demand</h2>
    <div class="nkrp-metric-grid">
        <div class="nkrp-card">
            <span class="nkrp-card-title">Total Jobs</span>
            <p class="nkrp-card-value"><?= number_format((float) $stats['jobs']->total) ?></p>
            <div class="nkrp-card-sub">
                <span>Active: <?= number_format((float) $stats['jobs']->published) ?></span>
                <span>Drafts: <?= number_format((float) $stats['jobs']->draft) ?></span>
            </div>
        </div>
        <div class="nkrp-card">
            <span class="nkrp-card-title">Job Engagement</span>
            <p class="nkrp-card-value">Coming Soon</p>
            <div class="nkrp-card-sub">
                <span>Total Views</span>
                <span>Avg. Applies/Job</span>
            </div>
        </div>
    </div>

    <h2 class="title">ATS Application Funnel</h2>
    <div class="nkrp-metric-grid">
        <div class="nkrp-card" style="border-left: 4px solid #3b82f6;">
            <span class="nkrp-card-title">Total Applications</span>
            <p class="nkrp-card-value"><?= number_format((float) $stats['apps']->total) ?></p>
            <div class="nkrp-card-sub">
                <span>Pending: <?= number_format((float) $stats['apps']->pending) ?></span>
            </div>
        </div>
        <div class="nkrp-card" style="border-left: 4px solid #eab308;">
            <span class="nkrp-card-title">Interviews Scheduled</span>
            <p class="nkrp-card-value"><?= number_format((float) $stats['apps']->interview) ?></p>
            <div class="nkrp-card-sub">
                <span>Shortlisted: <?= number_format((float) $stats['apps']->shortlisted) ?></span>
            </div>
        </div>
        <div class="nkrp-card" style="border-left: 4px solid #10b981;">
            <span class="nkrp-card-title">Successfully Hired</span>
            <p class="nkrp-card-value"><?= number_format((float) $stats['apps']->hired) ?></p>
            <div class="nkrp-card-sub">
                <span>Offered: <?= number_format((float) $stats['apps']->offered) ?></span>
                <span>Rejected: <?= number_format((float) $stats['apps']->rejected) ?></span>
            </div>
        </div>
    </div>

    <h2 class="title">AI Core & Search Intelligence</h2>
    <div class="nkrp-metric-grid">
        <div class="nkrp-card" style="background: #f8fafc;">
            <span class="nkrp-card-title">AI Requests Processed</span>
            <p class="nkrp-card-value"><?= number_format((float) $stats['ai']->total_requests) ?></p>
            <div class="nkrp-card-sub">
                <span>Tokens: <?= number_format((float) $stats['ai']->total_tokens) ?></span>
                <span style="color: #059669; font-weight: bold;">Cost: $<?= number_format((float) $stats['ai']->total_cost, 4) ?></span>
            </div>
        </div>
        <div class="nkrp-card" style="background: #f8fafc;">
            <span class="nkrp-card-title">Search Engine Queries</span>
            <p class="nkrp-card-value">Tracking Active</p>
            <div class="nkrp-card-sub">
                <span>(Awaiting historical data)</span>
            </div>
        </div>
    </div>

</div>