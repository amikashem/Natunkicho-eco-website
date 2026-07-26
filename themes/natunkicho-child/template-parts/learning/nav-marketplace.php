<?php
/**
 * Template Part: Premium Marketplace Navigation (Dynamic Smart Menu)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- SMART USER LOGIC ---
$is_logged_in   = is_user_logged_in();
$show_dashboard = false;

if ( $is_logged_in ) {
    $user = wp_get_current_user();
    $user_roles = (array) $user->roles;

    // Condition 1: User is an Admin or an active Tutor LMS Instructor
    if ( in_array( 'administrator', $user_roles ) || in_array( 'tutor_instructor', $user_roles ) ) {
        $show_dashboard = true;
    } 
    // Condition 2: Check if a normal user/candidate/employer has actually enrolled in any courses
    elseif ( function_exists('tutor_utils') ) {
        $enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user->ID );
        if ( $enrolled_courses && $enrolled_courses->have_posts() ) {
            $show_dashboard = true;
        }
    }
}
// ------------------------
?>

<div class="nk-learning-nav-wrapper nk-margin-top-gap">
    <nav class="nk-learning-nav-container" style="display: flex; justify-content: space-between; align-items: center;">
        
        <div class="nk-premium-menu-scroll">
            <ul class="nk-learning-menu-pills">
                <li><a href="https://natunkicho.com/acquisition/" class="active">Overview</a></li>
                <li><a href="#nk-courses">Top Courses</a></li>
                <li><a href="#nk-institutes">Partners</a></li>
                <li><a href="#nk-career-paths">Career Paths</a></li>
                <li><a href="#nk-tutors">1-on-1 Mentors</a></li>
                <li><a href="#nk-student-hub">Success Stories</a></li>
            </ul>
        </div>

        <div class="nk-learning-nav-actions">
            
            <?php if ( $show_dashboard ) : ?>
                
                <a href="<?php echo esc_url( home_url( '/tutor-lms-dashboard/' ) ); ?>" class="nk-btn nk-btn-dark-sm">Academy Dashboard</a>
            
            <?php else : ?>
                
                <div class="nk-academy-dropdown">
                    <button class="nk-btn nk-btn-dark-sm">Join Academy ▾</button>
                    
                    <div class="nk-academy-dropdown-menu">
                        <ul>
                            <li>
                                <a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">
                                    <span style="margin-right: 8px; font-size: 16px;">👨‍🎓</span> Become a Student
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( home_url( '/instructor-registration/' ) ); ?>">
                                    <span style="margin-right: 8px; font-size: 16px;">👨‍🏫</span> Become an Instructor
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( home_url( '/institute-registration/' ) ); ?>">
                                    <span style="margin-right: 8px; font-size: 16px;">🏫</span> Register Your Institute
                                </a>
                            </li>
                            
                            <?php if ( ! $is_logged_in ) : ?>
                                <li class="nk-divider"></li>
                                <li>
                                    <a href="<?php echo esc_url( home_url( '/academy-login/' ) ); ?>" class="nk-login-text">
                                        Already have an account? Login
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </nav>
</div>