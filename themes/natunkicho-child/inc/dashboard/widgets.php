<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nk_employer_dashboard_widgets() {
    ?>
    <div class="nk-dash-card">
        <h3>Post New Job</h3>
        <p>Publish new hospitality jobs to the platform.</p>
    </div>
    <div class="nk-dash-card">
        <h3>Manage Listings</h3>
        <p>Edit, renew, or expire active job posts.</p>
    </div>
    <div class="nk-dash-card">
        <h3>Applications</h3>
        <p>Review and shortlist candidate applications.</p>
    </div>
    <?php
}

function nk_candidate_dashboard_widgets() {
    ?>
    <div class="nk-dash-card">
        <h3>Saved Jobs</h3>
        <p>Track your favorite hospitality jobs.</p>
    </div>
    <div class="nk-dash-card">
        <h3>Applications</h3>
        <p>Manage and track your job applications.</p>
    </div>
    <div class="nk-dash-card">
        <?php echo do_shortcode('[nk_profile_strength]'); ?>
        <p>Complete your professional profile.</p>
    </div>
    <?php
}