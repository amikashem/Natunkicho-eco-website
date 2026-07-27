<?php
/**
 * Template Part: Dynamic Course Categories (Tutor LMS)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<section id="nk-categories" class="nk-learning-section">
    <div class="nk-learning-container">
        
        <div class="nk-section-header">
            <h2 class="nk-section-title">Top Hospitality Sectors</h2>
            <p class="nk-section-subtitle">Choose your specialization and start learning today.</p>
        </div>

        <div class="nk-category-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php
            // Fetch categories directly from Tutor LMS
            $categories = get_terms( array(
                'taxonomy'   => 'course-category', // Tutor LMS custom taxonomy
                'hide_empty' => true, // Only show categories that have at least 1 course
                'number'     => 6     // Show top 6 categories
            ) );

            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                foreach ( $categories as $category ) {
                    // Get the category link
                    $term_link = get_term_link( $category );
                    ?>
                    <a href="<?php echo esc_url( $term_link ); ?>" class="nk-category-card" style="background: #fff; padding: 25px; border-radius: 10px; text-align: center; border: 1px solid #eee; box-shadow: 0 4px 10px rgba(0,0,0,0.03); text-decoration: none; color: inherit; transition: all 0.3s ease;">
                        <i class="dashicons dashicons-portfolio" style="font-size: 30px; color: #0056b3; margin-bottom: 15px;"></i>
                        <h3 style="font-size: 1.1rem; margin: 0;"><?php echo esc_html( $category->name ); ?></h3>
                        <span style="font-size: 0.9rem; color: #777; margin-top: 5px; display: block;"><?php echo esc_html( $category->count ); ?> Courses</span>
                    </a>
                    <?php
                }
            } else {
                echo '<p>Add some course categories in Tutor LMS to see them here!</p>';
            }
            ?>
        </div>
        
    </div>
</section>