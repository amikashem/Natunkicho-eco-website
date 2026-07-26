<?php
/**
 * Main Content Template for Single Posts
 * Professional layout with all required sections
 */

if (!defined('ABSPATH')) exit;
global $post;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('nksp-article'); ?>>

    <!-- Post Header -->
    <header class="nksp-entry-header">
        <h1 class="nksp-title"><?php the_title(); ?></h1>
        
        <div class="nksp-meta">
            <span class="nksp-date"><?php echo esc_html(get_the_date()); ?></span>
            <span class="nksp-author"> | <?php the_author(); ?></span>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if (has_post_thumbnail()) : ?>
    <div class="nksp-featured-image">
        <?php 
        echo wp_get_attachment_image(
            get_post_thumbnail_id(), 
            'large', 
            false, 
            array(
                'loading' => 'lazy',
                'alt' => esc_attr(get_the_title()),
                'class' => 'nksp-featured-img'
            )
        ); 
        ?>
    </div>
    <?php endif; ?>

    <!-- Post Content -->
    <div class="nksp-content">
        <?php 
        the_content();
        wp_link_pages(array(
            'before' => '<div class="nksp-page-links">' . esc_html__('Pages:', 'natun-kicho'),
            'after'  => '</div>',
        ));
        ?>
    </div>

  
    <!-- You May Also Like Section - OPTIMIZED -->
<?php
$categories = wp_get_post_categories(get_the_ID());
if (!empty($categories)) :
    $cache_key = 'nksp_you_may_' . get_the_ID();
    $you_may_posts = get_transient($cache_key);
    
    if (false === $you_may_posts) {
        $related_args = array(
            'category__in'        => $categories,
            'post__not_in'        => array(get_the_ID()),
            'posts_per_page'      => 2,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'orderby'             => 'rand',
            'fields'              => 'ids' // Only get IDs for performance
        );
        $you_may_query = new WP_Query($related_args);
        $you_may_posts = $you_may_query->posts;
        set_transient($cache_key, $you_may_posts, 2 * HOUR_IN_SECONDS); // 2 hour cache
    }
    
    if (!empty($you_may_posts)) :
?>
<div class="nksp-you-may-also">
    <div class="nksp-section-header">
        <h3 class="nksp-section-title">Expand Your Reading</h3>
        <p class="nksp-section-subtitle">Discover more content you might enjoy</p>
    </div>
    
    <div class="nksp-related-grid">
        <?php foreach ($you_may_posts as $post_id) : ?>
        <div class="nksp-related-item">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="nksp-related-link">
                <h4 class="nksp-related-title"><?php echo esc_html(wp_trim_words(get_the_title($post_id), 10, '...')); ?></h4>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php 
    endif;
endif;
?>
    <!-- Social Share Section -->
    <div class="nksp-social-share">
        <div class="nksp-share-header">
            <span class="nksp-share-label">Share This Article</span>
        </div>
        
        <div class="nksp-share-buttons">
            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="nksp-share-btn nksp-share-fb"
               title="Share on Facebook">
                <span class="nksp-share-icon">📘</span>
                <span class="nksp-share-text">Facebook</span>
            </a>
            
            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php the_permalink(); ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="nksp-share-btn nksp-share-in"
               title="Share on LinkedIn">
                <span class="nksp-share-icon">💼</span>
                <span class="nksp-share-text">LinkedIn</span>
            </a>
            
            <!-- Pinterest -->
            <a href="https://pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&media=<?php echo get_the_post_thumbnail_url(); ?>&description=<?php the_title(); ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="nksp-share-btn nksp-share-pin"
               title="Share on Pinterest">
                <span class="nksp-share-icon">📌</span>
                <span class="nksp-share-text">Pinterest</span>
            </a>
            
            <!-- Email -->
            <a href="mailto:?subject=<?php echo rawurlencode('Check this article: ' . get_the_title()); ?>&body=<?php echo rawurlencode(get_permalink()); ?>" 
               class="nksp-share-btn nksp-share-email"
               title="Share via Email">
                <span class="nksp-share-icon">✉️</span>
                <span class="nksp-share-text">Email</span>
            </a>
            
            <!-- Copy Link -->
            <button type="button" 
                    class="nksp-share-btn nksp-share-copy"
                    title="Copy link to clipboard"
                    onclick="nkspCopyToClipboard('<?php the_permalink(); ?>')">
                <span class="nksp-share-icon">🔗</span>
                <span class="nksp-share-text">Copy Link</span>
            </button>
        </div>
    </div>
       <!-- NKSP Comments Wrapper -->
<div class="nksp-comments-section">
    <h3 class="nksp-comments-title">Join the Discussion</h3>

    <?php 
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        else :
            echo '<p class="nksp-no-comment-msg">Comments are closed for this article.</p>';
        endif;
    ?>
</div>
 

</article>

<script>
function nkspCopyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>