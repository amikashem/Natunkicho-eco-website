<?php
/**
 * Dynamic Category + Tag Menu Section (custom tag sets)
 */

if ( ! function_exists( 'nk_dynamic_menu_section' ) ) {
    function nk_dynamic_menu_section() {

        // ✅ ADD THIS LINE - Enqueue the CSS and JS
        nk_enqueue_dynamic_menu_assets();

        // Define main categories and their related tags
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

        ob_start(); ?>

        <section class="nk-dynamic-menu-wrapper transparent">
          <div class="nk-dynamic-menu-container single-row">
            <?php foreach ( $categories_with_tags as $cat_name => $tags_list ) : ?>
              <div class="nk-dynamic-column">
                <h4 class="nk-dynamic-category-title"><?php echo esc_html( $cat_name ); ?></h4>
                <ul class="nk-dynamic-tag-list">
                    <?php
                    $shown = 0;
                    foreach ( $tags_list as $tag_name ) :
                        if ( $shown >= 6 ) break;
                        $tag = get_term_by( 'name', $tag_name, 'post_tag' );
                        $tag_link = $tag ? get_tag_link( $tag->term_id ) : '#';
                        echo '<li><a href="' . esc_url( $tag_link ) . '">' . esc_html( $tag_name ) . '</a></li>';
                        $shown++;
                    endforeach;
                    ?>
                    <?php
// Determine category link: try by name -> slug fallback
$cat_obj = get_category_by_slug( sanitize_title( $cat_name ) );
$more_link = $cat_obj ? get_category_link( $cat_obj->term_id ) : '#';
?>
<li class="nk-more"><a class="nk-more-link" href="<?php echo esc_url( $more_link ); ?>"><?php esc_html_e( 'More', 'hello-child' ); ?></a></li>

                </ul>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <?php
        return ob_get_clean();
    }
}
add_shortcode( 'nk_dynamic_menu_section', 'nk_dynamic_menu_section' );