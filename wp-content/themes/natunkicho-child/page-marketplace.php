<?php
/**
 * Template Name: SaaS Marketplace
 * Description: Custom template for the premium services and digital products marketplace.
 */

get_header(); ?>

<main id="primary" class="site-main nk-marketplace-wrapper">
    <div class="nk-marketplace-container">
        
        <header class="nk-marketplace-header">
            <h1 class="nk-marketplace-title">Career & Employer Services</h1>
            <p class="nk-marketplace-subtitle">Accelerate your growth in the hospitality industry with our expert tools and services.</p>
        </header>

        <section class="nk-services-grid">
            <?php
            // Query WooCommerce Products
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => -1, // Fetches all products
                'post_status'    => 'publish',
            );
            $loop = new WP_Query( $args );

            if ( $loop->have_posts() ) :
                while ( $loop->have_posts() ) : $loop->the_post(); 
                    global $product; 
                    ?>
                    
                    <div class="nk-service-card">
                        <div class="nk-service-card-header">
                            <h2 class="nk-service-title"><?php the_title(); ?></h2>
                            <div class="nk-service-price">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                        </div>
                        
                        <div class="nk-service-card-body">
                            <div class="nk-service-excerpt">
                                <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?>
                            </div>
                        </div>
                        
                        <div class="nk-service-card-footer">
                            <a href="<?php echo esc_url( '?add-to-cart=' . $product->get_id() ); ?>" class="nk-btn nk-btn-primary">
                                Select Plan
                            </a>
                        </div>
                    </div>

                <?php 
                endwhile;
            else :
                echo '<p class="nk-no-services">Our premium services are launching soon. Stay tuned!</p>';
            endif;

            wp_reset_postdata();
            ?>
        </section>

    </div>
</main>

<?php get_footer(); ?>