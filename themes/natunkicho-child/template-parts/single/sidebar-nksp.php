<?php
/**
 * Sticky Sidebar Template - PERFORMANCE OPTIMIZED
 * All queries cached with transients
 */

if (!defined('ABSPATH')) exit;
global $post;
?>

<div class="nksp-sidebar-inner">

    <!-- Related Posts Section - CACHED -->
    <div class="nksp-widget nksp-widget-related">
        <h3 class="nksp-widget-title">Related Articles</h3>
        <ul class="nksp-widget-list">
            <?php
            $categories = wp_get_post_categories(get_the_ID());
            if (!empty($categories)) {
                $cache_key = 'nksp_related_' . get_the_ID();
                $related_posts = get_transient($cache_key);

                if (false === $related_posts) {
                    $related_query = new WP_Query(array(
                        'category__in'        => $categories,
                        'post__not_in'        => array(get_the_ID()),
                        'posts_per_page'      => 6,
                        'no_found_rows'       => true,
                        'ignore_sticky_posts' => true,
                        'orderby'             => 'rand',
                        'fields'              => 'ids' // Better performance
                    ));
                    $related_posts = $related_query->posts;
                    set_transient($cache_key, $related_posts, 3 * HOUR_IN_SECONDS);
                }

                if (!empty($related_posts)) {
                    foreach ($related_posts as $post_id) {
                        echo '<li class="nksp-list-item">';
                        echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="nksp-list-link">';
                        echo '<span class="nksp-list-title">' . esc_html(wp_trim_words(get_the_title($post_id), 8, '...')) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                } else {
                    echo '<li class="nksp-list-item nksp-no-items">No related articles found.</li>';
                }
            } else {
                echo '<li class="nksp-list-item nksp-no-items">No related articles found.</li>';
            }
            ?>
        </ul>
    </div>

    <!-- Recent Posts Section - CACHED -->
    <div class="nksp-widget nksp-widget-recent">
        <h3 class="nksp-widget-title">Recent Posts</h3>
        <ul class="nksp-widget-list">
            <?php
            $cache_key = 'nksp_recent_posts';
            $recent_posts = get_transient($cache_key);
            
            if (false === $recent_posts) {
                $recent_posts = wp_get_recent_posts(array(
                    'numberposts' => 6,
                    'post_status' => 'publish',
                    'exclude'     => array(get_the_ID()),
                    'fields'      => 'ids' // Better performance
                ), OBJECT);
                set_transient($cache_key, $recent_posts, 30 * MINUTE_IN_SECONDS); // 30 min cache
            }

            if (!empty($recent_posts)) {
                foreach ($recent_posts as $post_id) {
                    echo '<li class="nksp-list-item">';
                    echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="nksp-list-link">';
                    echo '<span class="nksp-list-title">' . esc_html(wp_trim_words(get_the_title($post_id), 8, '...')) . '</span>';
                    echo '</a>';
                    echo '</li>';
                }
            } else {
                echo '<li class="nksp-list-item nksp-no-items">No recent posts found.</li>';
            }
            ?>
        </ul>
    </div>

    <!-- Most Visited Section - CACHED -->
    <div class="nksp-widget nksp-widget-popular">
        <h3 class="nksp-widget-title">Most Visited</h3>
        <ul class="nksp-widget-list">
            <?php
            $cache_key = 'nksp_popular_posts';
            $popular_posts = get_transient($cache_key);
            
            if (false === $popular_posts) {
                $popular_query = new WP_Query(array(
                    'posts_per_page'      => 6,
                    'orderby'             => 'comment_count',
                    'order'               => 'DESC',
                    'no_found_rows'       => true,
                    'ignore_sticky_posts' => true,
                    'exclude'             => array(get_the_ID()),
                    'fields'              => 'ids' // Better performance
                ));
                $popular_posts = $popular_query->posts;
                set_transient($cache_key, $popular_posts, 2 * HOUR_IN_SECONDS);
            }

            if (!empty($popular_posts)) {
                foreach ($popular_posts as $post_id) {
                    echo '<li class="nksp-list-item">';
                    echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="nksp-list-link">';
                    echo '<span class="nksp-list-title">' . esc_html(wp_trim_words(get_the_title($post_id), 8, '...')) . '</span>';
                    echo '</a>';
                    echo '</li>';
                }
            } else {
                echo '<li class="nksp-list-item nksp-no-items">No popular posts found.</li>';
            }
            ?>
        </ul>
    </div>

    <!-- Keep Newsletter, Follow Us, Ads sections as they are -->
        <!-- Newsletter Subscription Section - UPDATED -->
    <div class="nksp-widget nksp-widget-newsletter">
        <h3 class="nksp-widget-title">Stay Updated</h3>
        <div class="nksp-newsletter-content">
            <p class="nksp-newsletter-text">Get the latest articles and updates directly in your inbox.</p>
            
            <form class="nksp-newsletter-form" id="nk-subscribe-form">
                <?php wp_nonce_field('nk_subscribe_nonce', 'subscription_nonce'); ?>
                <div class="nksp-form-group">
                    <input type="email" 
                           name="email"
                           class="nksp-form-input" 
                           placeholder="Enter your email address" 
                           required
                           id="nk-subscribe-email">
                    <button type="submit" class="nksp-form-submit" id="nk-subscribe-btn">
                        <span class="btn-text">Subscribe</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading-spinner"></span> Subscribing...
                        </span>
                    </button>
                </div>
                <p class="nksp-newsletter-note">No spam, unsubscribe at any time.</p>
                
                <!-- Messages will appear here -->
                <div class="nksp-form-messages"></div>
            </form>
        </div>
    </div>

    <!-- Follow Us Section -->
    <div class="nksp-widget nksp-widget-follow">
        <h3 class="nksp-widget-title">Follow Us</h3>
        <div class="nksp-follow-content">
            <p class="nksp-follow-text">Join our community on social media</p>
            <div class="nksp-social-links">
                <a href="https://www.facebook.com/natunkicho" class="nksp-social-link nksp-social-fb" title="Facebook" target="_blank" rel="noopener">
                    <span class="nksp-social-icon">📘</span>
                    <span class="nksp-social-text">Facebook</span>
                </a>
                <a href="#" class="nksp-social-link nksp-social-tw" title="Twitter" target="_blank" rel="noopener">
                    <span class="nksp-social-icon">🐦</span>
                    <span class="nksp-social-text">Twitter</span>
                </a>
                <a href="https://www.linkedin.com/company/food-business-success-lab/" class="nksp-social-link nksp-social-in" title="LinkedIn" target="_blank" rel="noopener">
                    <span class="nksp-social-icon">💼</span>
                    <span class="nksp-social-text">LinkedIn</span>
                </a>
                <a href="https://www.pinterest.com/Kashem25" class="nksp-social-link nksp-social-pt" title="Pinterest" target="_blank" rel="noopener">
                    <span class="nksp-social-icon">📌</span>
                    <span class="nksp-social-text">Pinterest</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Advertisement Space -->
    <div class="nksp-widget nksp-widget-ads">
        <h3 class="nksp-widget-title">Sponsored</h3>
        <div class="nksp-ads-content">
            <div class="nksp-ad-space">
                <p>Advertisement Space</p>
                <span class="nksp-ad-label">300x250</span>
            </div>
        </div>
    </div>

</div>