<div class="nk-footer-navigation">
    <div class="nk-container nk-footer-grid">
        
        <?php
        $menus = array(
            'footer-employers' => array('Employers', 'dashicons-building'),
            'footer-jobseekers'=> array('Job Seekers', 'dashicons-businessman'),
            'footer-learning'  => array('Learning & Academy', 'dashicons-welcome-learn-more'),
            'footer-resources' => array('Resources', 'dashicons-book'),
            'footer-company'   => array('Company', 'dashicons-groups'),
            'footer-support'   => array('Support & Legal', 'dashicons-shield')
        );

        foreach ($menus as $location => $data) : ?>
            <div class="nk-footer-column">
                <h4 class="nk-footer-col-title">
                    <span class="dashicons <?php echo esc_attr($data[1]); ?>"></span> 
                    <?php echo esc_html($data[0]); ?>
                    <span class="nk-mobile-toggle dashicons dashicons-arrow-down-alt2"></span>
                </h4>
                <div class="nk-footer-menu-wrapper">
                    <?php
                    if ( has_nav_menu( $location ) ) {
                        wp_nav_menu( array(
                            'theme_location' => $location,
                            'container'      => false,
                            'menu_class'     => 'nk-footer-menu',
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ) );
                    } else {
                        echo '<p class="nk-setup-notice">Assign menu in Admin.</p>';
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>