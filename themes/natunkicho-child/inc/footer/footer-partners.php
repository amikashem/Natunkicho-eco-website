<div class="nk-footer-partners">
    <div class="nk-container">
        <h4 class="nk-partner-title">Trusted by Top Hospitality Brands</h4>
        <div class="nk-partner-track-wrapper">
            <div class="nk-partner-track">
                <?php
                $partners = new WP_Query(array(
                    'post_type' => 'nk_partner',
                    'posts_per_page' => -1,
                    'orderby' => 'menu_order',
                    'order' => 'ASC'
                ));

                if ($partners->have_posts()) :
                    // We duplicate the output twice inside the track to create the infinite CSS loop effect seamlessly
                    for ($i = 0; $i < 2; $i++) {
                        while ($partners->have_posts()) : $partners->the_post();
                            $url = get_post_meta(get_the_ID(), 'partner_url', true) ?: '#';
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="nk-partner-logo" target="_blank" rel="noopener nofollow">
                                <?php the_post_thumbnail('medium', array('loading' => 'lazy', 'alt' => get_the_title())); ?>
                            </a>
                            <?php
                        endwhile;
                        $partners->rewind_posts();
                    }
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </div>
</div>