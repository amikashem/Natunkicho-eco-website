<?php
/**
 * NK Recruitment - Single Job Override
 * Forces Astra's Header & Footer while loading our custom Enterprise Job UI.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// 1. Force Astra Header
get_header(); 
?>

<div id="primary" class="content-area" style="background-color: #f8fafc; padding: 40px 0;">
    <main id="main" class="site-main" role="main">
        <div class="nkrp-container" style="max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 15px; box-sizing: border-box;">
            
            <?php 
            // 2. Inject our beautiful SaaS Job Details UI
            echo do_shortcode('[nk_job_details]'); 
            ?>

        </div>
    </main>
</div>

<?php 
// 3. Force Astra Footer
get_footer(); 
?>