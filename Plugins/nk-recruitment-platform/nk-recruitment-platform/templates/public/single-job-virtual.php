<?php
/**
 * NK Recruitment - Virtual Single Job Page
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. FORCE THE ASTRA HEADER
get_header(); 
?>

<div id="primary" class="content-area" style="background-color: #f8fafc; padding: 40px 0;">
    <main id="main" class="site-main" role="main">
        <div class="nkrp-container" style="max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 15px; box-sizing: border-box;">
            
            <?php 
            // 2. LOAD YOUR JOB DETAILS
            echo do_shortcode('[nk_job_details]'); 
            ?>

        </div>
    </main>
</div>

<?php 
// 3. FORCE THE ASTRA FOOTER
get_footer(); 
?>