<?php
/**
 * Single Post Template - NKSP Redesign
 * Optimized for sticky sidebar and clean layout
 */

if (!defined('ABSPATH')) exit;

get_header();
?>

<!-- NKSP Single Post Wrapper -->
<div id="nksp-single-wrapper" class="nksp-single-wrapper">
    <div class="nksp-container">
        
        <!-- Main Content Area -->
        <main class="nksp-main-content">
            <?php
            // Load main post content
            if (file_exists(get_stylesheet_directory() . '/template-parts/single/content-single-nksp.php')) {
                get_template_part('template-parts/single/content-single-nksp');
            } else {
                // Fallback
                if (have_posts()) : while (have_posts()) : the_post();
                    the_content();
                endwhile; endif;
            }
            ?>
        </main>

        <!-- Sidebar Area -->
        <aside class="nksp-sidebar">
            <?php
            // Load sidebar content
            if (file_exists(get_stylesheet_directory() . '/template-parts/single/sidebar-nksp.php')) {
                include get_stylesheet_directory() . '/template-parts/single/sidebar-nksp.php';
            } else {
                echo '<!-- Sidebar content file not found -->';
            }
            ?>
        </aside>

    </div>
</div>

<?php get_footer(); ?>