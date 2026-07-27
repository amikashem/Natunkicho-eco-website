<?php
/**
 * Template Name: Learning Marketplace
 * Description: The main hub for the Natunkicho Hospitality Learning Ecosystem.
 */

get_header(); ?>

<div id="nk-learning-ecosystem" class="nk-learning-wrapper">

    <?php 
    /**
     * 1. The Sticky Marketplace Navigation
     * Floating menu specific to the learning platform
     */
    get_template_part( 'template-parts/learning/nav-marketplace' ); 
    ?>

    <main class="nk-learning-main-content">
        
        <?php
        /**
         * 2. Hero Section
         * Search bars, dynamic headings, and CTA buttons
         */
        get_template_part( 'template-parts/learning/section-hero' );

        /**
         * 3. Featured Categories
         * Grid of hospitality niches (Hotel Management, Culinary Arts, etc.)
         */
        get_template_part( 'template-parts/learning/section-categories' );

        /**
         * 4. Featured Institutes
         * Dynamic carousel of universities and training partners
         */
        get_template_part( 'template-parts/learning/section-institutes' );

        /**
         * 5. Affiliate & Popular Courses
         * Grid of top courses with prices, ratings, and affiliate badges
         */
        get_template_part( 'template-parts/learning/section-course-grid' );

        /**
         * 6. Career Roadmaps
         * Interactive cards showing steps to become an Executive Chef, Hotel Manager, etc.
         */
        get_template_part( 'template-parts/learning/section-roadmaps' );

        /**
         * 7. Private Tutors
         * Profiles of independent mentors and language trainers
         */
        get_template_part( 'template-parts/learning/section-tutors' );

        /**
         * 8. Testimonials
        
         */
         
         get_template_part( 'template-parts/learning/section-testimonials' );
         
         /**
         * 9. Dynamic Promotional Block
         * Admin-controlled area for flash sales, Black Friday banners, or custom HTML
         */ 
        get_template_part( 'template-parts/learning/section-dynamic-promo' );
        ?>

    </main>

</div><?php 
get_footer(); 
?>