<?php
/**
 * Dropdown Menu Shortcode - Proper Menu Style
 * Shortcode: [nk_dropdown_menu_section]
 */

if ( ! function_exists( 'nk_dropdown_menu_section' ) ) {
    function nk_dropdown_menu_section() {

        // ✅ Enqueue dropdown assets
        if (function_exists('nk_enqueue_dropdown_menu_assets')) {
            nk_enqueue_dropdown_menu_assets();
        }

        // Reuse same categories and tags structure
        $categories_with_tags = [
            'Kitchen' => ['Bakery', 'Pastry', 'Hot Kitchen', 'Cold Kitchen', 'Butchery', 'Sauce', 'Stock & Gravy'],
            'Restaurant & Bar' => ['Types of Service', 'Table Setup', 'Types of Restaurant', 'Waiter Skills', 'Bar Management', 'Beverage Service'],
            'Operations' => ['Inventory Control', 'Purchasing', 'Menu Planning', 'Costing', 'Hygiene', 'Storage'],
            'Food Business Idea' => ['Processed Food', 'Cloud Kitchen', 'Catering', 'Processed Vegetable', 'Processed Meat', 'Franchise Model'],
            'Compliance' => ['HACCP', 'ISO', 'Local Gov Documents', 'Food Handler Certificates', 'Audit Checklists'],
            'Recipes' => ['Pastry Items', 'Fast Food', 'Continental', 'Japanese', 'Indian', 'Mexican', 'Pasta & Macaroni'],
            'Hospitality Skills' => ['Food Safety', 'Food Cost', 'Chef Course', 'History', 'Customer Service', 'Teamwork'],
            'Tourism Study' => ['Halal Tourism', 'Global Tourism', 'Food Tourism', 'Luxury Tourism', 'Eco Tourism', 'Sustainable Travel'],
        ];

        ob_start();
        ?>

        <section class="nk-dropdown-menu-wrapper">
            <div class="nk-dropdown-container">
                <?php foreach ( $categories_with_tags as $cat_name => $tags_list ) : ?>
                    <div class="nk-dropdown-column">
                        <div class="nk-dropdown-header">
                            <button class="nk-dropdown-trigger" type="button" aria-expanded="false">
                                <span class="nk-category-name"><?php echo esc_html( $cat_name ); ?></span>
                                <span class="nk-dropdown-arrow">▼</span>
                            </button>
                        </div>
                        
                        <div class="nk-dropdown-content">
                            <ul class="nk-dropdown-list">
                                <?php 
                                $shown = 0;
                                foreach ( $tags_list as $tag_name ) :
                                    if ( $shown >= 6 ) break;
                                    $tag = get_term_by( 'name', $tag_name, 'post_tag' );
                                    $tag_link = $tag ? get_tag_link( $tag->term_id ) : '#';
                                    ?>
                                    <li class="nk-dropdown-item">
                                        <a href="<?php echo esc_url( $tag_link ); ?>" class="nk-dropdown-link" title="<?php echo esc_attr( $tag_name ); ?>">
                                            <?php echo esc_html( $tag_name ); ?>
                                        </a>
                                    </li>
                                    <?php
                                    $shown++;
                                endforeach; 
                                ?>
                                
                                <?php
                                // More link
                                $cat_obj = get_category_by_slug( sanitize_title( $cat_name ) );
                                $more_link = $cat_obj ? get_category_link( $cat_obj->term_id ) : '#';
                                ?>
                                <li class="nk-dropdown-item nk-more-item">
                                    <a href="<?php echo esc_url( $more_link ); ?>" class="nk-dropdown-link nk-more-link">
                                        <?php esc_html_e( 'More', 'hello-child' ); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php
        return ob_get_clean();
    }
}
add_shortcode( 'nk_dropdown_menu_section', 'nk_dropdown_menu_section' );