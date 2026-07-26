<?php
/**
 * Template Part: Learning Hero Section
 * The main banner and search functionality for the marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<section id="nk-home" class="nk-learning-section nk-hero-section">
    <div class="nk-learning-container">
        
        <div class="nk-hero-content-box">
            <h1 class="nk-hero-title">
                <span class="highlight">Learn Hospitality.</span><br>
                Build Your Career.<br>
                Get Hired Worldwide.
            </h1>
            
            <p class="nk-hero-subtitle">
                Discover Hospitality Courses, International Certifications, Private Tutors, Career Programs and Professional Training from Leading Institutes around the world.
            </p>
            
            <div class="nk-hero-button-group">
                <a href="#nk-courses" class="nk-btn nk-btn-primary">Explore Courses</a>
                <a href="#nk-career-paths" class="nk-btn nk-btn-secondary">Career Roadmaps</a>
                <a href="#become-instructor" class="nk-btn nk-btn-outline">Become Instructor</a>
            </div>
            
            <div class="nk-hero-search-wrapper">
                <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="nk-learning-search-form">
                    
                    <div class="nk-search-input-group">
                        <i class="dashicons dashicons-search"></i>
                        <input type="text" name="s" id="nk-learning-search" placeholder="Search Courses, Institutes, Skills, or Certificates..." required />
                        
                        <input type="hidden" name="post_type" value="nk_course" />
                    </div>
                    
                    <button type="submit" class="nk-btn nk-btn-search">Search</button>
                </form>
                
                <div class="nk-hero-trending">
                    <span>Trending:</span>
                    <a href="#">Hotel Management</a>
                    <a href="#">Culinary Arts</a>
                    <a href="#">HACCP</a>
                    <a href="#">IELTS Hospitality</a>
                </div>
            </div>
        </div>

        <div class="nk-hero-image-box">
            <div class="nk-hero-visual-placeholder"></div>
        </div>

    </div>
</section>