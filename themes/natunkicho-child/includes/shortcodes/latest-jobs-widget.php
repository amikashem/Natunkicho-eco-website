<?php
if (!defined('ABSPATH')) exit;

/**
 * Latest Jobs Footer Widget Shortcode
 */

function nk_latest_jobs_widget_shortcode() {

    ob_start();
    ?>

    <div id="latest-jobs-widget" class="popular-topics-widget">

        <h3>Latest Jobs</h3>

        <ul id="latest-jobs-list">

            <?php

            $latest_jobs = new WP_Query(array(

                'post_type'      => 'job_listing',
                'posts_per_page' => 12,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC'

            ));

            if ($latest_jobs->have_posts()) :

                while ($latest_jobs->have_posts()) :

                    $latest_jobs->the_post();
            ?>

                    <li>

                        <a href="<?php the_permalink(); ?>">

                            <?php echo wp_trim_words(get_the_title(), 6, '...'); ?>

                        </a>

                    </li>

            <?php

                endwhile;

                wp_reset_postdata();

            else :
            ?>

                <li>No jobs found.</li>

            <?php endif; ?>

        </ul>

    </div>

    <style>

    .popular-topics-widget {
        font-family: Arial, sans-serif;
    }

    .popular-topics-widget h3 {
        font-size: 18px;
        margin-bottom: 14px;
        color: #10101f;
        font-weight: 700;
    }

    .popular-topics-widget ul {

        display: grid;
        grid-template-columns: repeat(2, 1fr);

        gap: 10px;

        list-style: none;

        padding: 0;
        margin: 0;
    }

    .popular-topics-widget li {
        margin: 0;
    }

    .popular-topics-widget a {

        display: block;

        padding: 10px 12px;

        border-radius: 12px;

        text-decoration: none;

        background: rgba(255,255,255,0.04);

        border: 1px solid rgba(255,255,255,0.08);

        color: #10101f;

        font-size: 14px;

        line-height: 1.5;

        transition: all 0.3s ease;
    }

    .popular-topics-widget a:hover {

        background: #005bb5;
        color: #ffffff;

        transform: translateY(-2px);
    }

    @media (max-width: 768px) {

        .popular-topics-widget ul {

            grid-template-columns: 1fr;

        }

    }

    </style>

    <?php

    return ob_get_clean();
}

/**
 * Register Shortcode
 */
add_shortcode('nk_latest_jobs', 'nk_latest_jobs_widget_shortcode');