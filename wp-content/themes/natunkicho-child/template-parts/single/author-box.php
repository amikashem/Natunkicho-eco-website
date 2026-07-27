<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$author_id = get_post_field( 'post_author', get_the_ID() );
if ( ! $author_id ) return;
?>

<div class="nksp-author-box">
  <div class="nksp-author-avatar">
    <?php echo get_avatar( $author_id, 80 ); ?>
  </div>
  <div class="nksp-author-meta">
    <h4 class="nksp-author-name"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></h4>
    <p class="nksp-author-bio"><?php echo esc_html( get_the_author_meta( 'description', $author_id ) ); ?></p>
  </div>
</div>
