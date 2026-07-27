<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * =========================================================================
 * 1. CHECKOUT OPTIMIZER (Frictionless SaaS Flow)
 * =========================================================================
 */
// Force single item in cart & skip the Cart page entirely
add_filter( 'woocommerce_add_to_cart_validation', function( $passed, $product_id, $quantity ) {
    if( ! WC()->cart->is_empty() ) WC()->cart->empty_cart();
    return $passed;
}, 10, 3 );

add_filter( 'woocommerce_add_to_cart_redirect', function( $url ) {
    return wc_get_checkout_url();
});

// Remove "Added to cart" popups and alter checkout button text
add_filter( 'wc_add_to_cart_message_html', '__return_false' );
add_filter( 'woocommerce_order_button_text', function() { return 'Continue to Payment'; } );

// Move Coupon Box down to payment section
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_review_order_before_payment', 'woocommerce_checkout_coupon_form', 10 );

// Add "Return to Dashboard" button in Woo Account Area
add_action('woocommerce_before_my_account', function() {
    echo '<div style="margin-bottom: 25px;"><a href="' . esc_url(site_url('/dashboard/')) . '" style="background:#0A66C2; color:#fff; padding:10px 20px; text-decoration:none; border-radius:8px; font-weight:bold; font-size:14px;">&larr; Return to Workspace Dashboard</a></div>';
});

// Redirect to dashboard after successful payment
add_action( 'template_redirect', function() {
    if ( function_exists('is_wc_endpoint_url') && is_wc_endpoint_url( 'order-received' ) && empty( $_GET['empty-cart'] ) ) {
        wp_redirect( home_url( '/dashboard/?tab=settings&upgrade=success' ) );
        exit;
    }
});

/**
 * =========================================================================
 * 2. PREMIUM UPGRADE & EXPIRATION BRIDGE
 * =========================================================================
 */
// Auto-Complete Virtual Orders immediately (bypasses "Processing" status)
add_action( 'woocommerce_thankyou', function( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( 'processing' === $order->get_status() ) {
        $virtual_order = true;
        foreach ( $order->get_items() as $item ) {
            if ( ! $item->get_product() || ! $item->get_product()->is_virtual() ) { $virtual_order = false; break; }
        }
        if ( $virtual_order ) $order->update_status( 'completed' );
    }
}, 10, 1 );

// The Central Role Upgrade Logic
function nk_woo_auto_upgrade_roles_v2( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    $user_id = $order->get_user_id();
    if ( ! $user_id ) return;
    $user = get_userdata( $user_id );
    if ( ! $user ) return;

    // YOUR WOOCOMMERCE PRODUCT IDs
    $candidate_packages = [ '3_months' => 2975, '6_months' => 2976, '12_months' => 2977, 'lifetime' => 2978 ];
    $employer_packages  = [ '3_months' => 2979, '6_months' => 2980, '12_months' => 2981, 'lifetime' => 2982 ]; // IMPORTANT: Update '0' to Employer Lifetime ID!

    $upgraded = false;
    $new_role = '';
    $plan_name = '';
    $expiry_timestamp = '';

    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();
        $duration_key = false; 

        if ( in_array( $product_id, $candidate_packages ) ) {
            $user->set_role( 'premium_job_seeker' );
            $new_role = 'Candidate';
            $duration_key = array_search($product_id, $candidate_packages);
        } elseif ( in_array( $product_id, $employer_packages ) ) {
            $user->set_role( 'premium_employer' );
            $new_role = 'Employer';
            $duration_key = array_search($product_id, $employer_packages);
        }

        if ( $duration_key !== false ) {
            $upgraded = true;
            update_user_meta( $user_id, 'nk_is_premium', 'yes' );
            if ( $duration_key === '3_months' ) { $plan_name = 'Premium Pro - 3 Months'; $expiry_timestamp = strtotime('+3 months'); }
            elseif ( $duration_key === '6_months' ) { $plan_name = 'Premium Pro - 6 Months'; $expiry_timestamp = strtotime('+6 months'); }
            elseif ( $duration_key === '12_months' ) { $plan_name = 'Premium Pro - 12 Months'; $expiry_timestamp = strtotime('+12 months'); }
            elseif ( $duration_key === 'lifetime' ) { $plan_name = 'Premium Pro - Lifetime Access'; $expiry_timestamp = 'lifetime'; }
            
            update_user_meta( $user_id, 'nk_premium_plan_name', $plan_name );
            update_user_meta( $user_id, 'nk_premium_expiry', $expiry_timestamp );
        }
    }

    if ( $upgraded && function_exists('nk_get_branded_email_html') ) {
        $subject = "Welcome to NatunKicho Premium Pro!";
        $content = "<p>Congratulations <strong>" . esc_html( $user->display_name ) . "</strong>!</p>";
        $content .= "<p>Your account has been successfully upgraded to the <strong>" . $plan_name . "</strong> tier.</p>";
        $content .= "<p>You now have full access to our advanced ecosystem features. Log in now to see your new tools in action.</p>";
        $content .= '<a href="' . esc_url(site_url('/dashboard/')) . '" style="display:inline-block; background:#0A66C2; color:#fff; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; margin-top:15px;">Go to Dashboard</a>';
        
        $final_html = nk_get_branded_email_html( $subject, $content );
        add_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
        wp_mail( $user->user_email, $subject, $final_html );
        remove_filter( 'wp_mail_content_type', 'nk_set_html_mail_content_type' );
    }
}
add_action( 'woocommerce_order_status_processing', 'nk_woo_auto_upgrade_roles_v2', 10, 1 );
add_action( 'woocommerce_order_status_completed', 'nk_woo_auto_upgrade_roles_v2', 10, 1 );

// Auto-Downgrade Engine
add_action( 'wp_loaded', function() {
    if ( ! is_user_logged_in() ) return;
    $user_id = get_current_user_id();
    if ( get_user_meta( $user_id, 'nk_is_premium', true ) === 'yes' ) {
        $expiry = get_user_meta( $user_id, 'nk_premium_expiry', true );
        if ( $expiry && $expiry !== 'lifetime' && time() > (int)$expiry ) {
            $user = wp_get_current_user();
            if ( in_array('premium_employer', (array)$user->roles) ) $user->set_role('employer');
            elseif ( in_array('premium_job_seeker', (array)$user->roles) ) $user->set_role('job_seeker');
            
            delete_user_meta( $user_id, 'nk_is_premium' );
            delete_user_meta( $user_id, 'nk_premium_expiry' );
            delete_user_meta( $user_id, 'nk_premium_plan_name' );
        }
    }
}); 
/**
 * =========================================================================
 * 3. SAAS CHECKOUT FIELD CLEANUP (Ultra-minimal billing)
 * =========================================================================
 */
add_filter( 'woocommerce_checkout_fields' , 'nk_simplify_saas_checkout' );
function nk_simplify_saas_checkout( $fields ) {
    // Remove all unnecessary physical address fields for digital SaaS
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    
    // Remove order notes/comments field
    unset($fields['order']['order_comments']); 
    
    return $fields;
}

// Auto-Fill Checkout Fields for Logged-In Users
add_filter( 'woocommerce_checkout_get_value', 'nk_autofill_checkout_fields', 10, 2 );
function nk_autofill_checkout_fields( $value, $input ) {
    if ( is_user_logged_in() && empty( $value ) ) {
        $current_user = wp_get_current_user();
        
        switch ( $input ) {
            case 'billing_first_name':
                return $current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name;
            case 'billing_last_name':
                return $current_user->user_lastname;
            case 'billing_email':
                return $current_user->user_email;
        }
    }
    return $value;
}

/**
 * =========================================================================
 * 4. SECURITY: PREVENT CROSS-ROLE PURCHASING
 * =========================================================================
 */
add_filter( 'woocommerce_add_to_cart_validation', 'nk_restrict_product_by_workspace', 10, 3 );
function nk_restrict_product_by_workspace( $passed, $product_id, $quantity ) {
    $user_id = get_current_user_id();
    // Check if they are currently acting as an employer or candidate
    $active_view = function_exists('nk_get_active_workspace') ? nk_get_active_workspace($user_id) : 'candidate';

    // ðŸ”´ UPDATE THESE ARRAYS WITH YOUR 8 FINAL WOOCOMMERCE PRODUCT IDs
    $candidate_packages = [ 2975, 2976, 2977, 2978 ]; 
    $employer_packages  = [ 2979, 2980, 2981, 2982 ]; // <-- Replace 0 with your NEW Employer Lifetime ID!

    // If a candidate tries to buy an employer package
    if ( $active_view === 'candidate' && in_array( $product_id, $employer_packages ) ) {
        wc_add_notice( 'Security Alert: You cannot purchase an Employer package while logged into the Candidate workspace.', 'error' );
        return false;
    }
    // If an employer tries to buy a candidate package
    if ( $active_view === 'employer' && in_array( $product_id, $candidate_packages ) ) {
        wc_add_notice( 'Security Alert: You cannot purchase a Candidate package while logged into the Employer workspace.', 'error' );
        return false;
    }
    
    return $passed;
}

/**
 * =========================================================================
 * 5. DYNAMIC CURRENCY CONVERSION (USD to BDT for EPS Gateway)
 * =========================================================================
 */

// --- A. The Frontend Notice ---
add_action( 'woocommerce_review_order_before_payment', 'nk_display_bdt_conversion_notice', 11 );
function nk_display_bdt_conversion_notice() {
    if ( null === WC()->cart ) return;

    // Get the currently selected payment gateway
    $chosen_gateway = WC()->session->get( 'chosen_payment_method' );

    // ONLY show this notice if the selected gateway is exactly 'eps'
    if ( $chosen_gateway !== 'eps' ) return; 

    // Calculate the BDT total
    $total_usd = WC()->cart->get_total( 'edit' );
    $exchange_rate = 127; 
    $total_bdt = $total_usd * $exchange_rate;

    // Display the premium UI notice
    echo '<div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px; margin-bottom: 24px; color: #166534; font-size: 0.95rem;">';
    echo '<strong style="display:block; margin-bottom: 8px; color: #14532d;">💳 Secure & 🛡 ️Encrypted Payment (EPS)</strong>';
    echo 'Bank-regulated gateway. Your final charge will be automatically converted to BDT. No hidden fees.<br><br>';
    echo 'Exchange Rate: <strong>$1.00 USD = ' . $exchange_rate . ' BDT</strong><br>';
    echo 'Amount to be charged: <strong style="font-size: 1.1rem;">৳' . number_format( $total_bdt, 2 ) . ' BDT</strong>';
    echo '</div>';
}

// Force checkout to refresh when payment method is changed so the notice appears/disappears seamlessly
add_action( 'wp_footer', 'nk_force_checkout_refresh_script' );
function nk_force_checkout_refresh_script() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        ?>
        <script type="text/javascript">
            jQuery(function($){
                $('form.checkout').on('change', 'input[name="payment_method"]', function(){
                    $('body').trigger('update_checkout');
                });
            });
        </script>
        <?php
    }
}

// --- B. The Backend Math (Intercepting the Order) ---
add_action( 'woocommerce_checkout_create_order', 'nk_convert_order_to_bdt_for_eps', 10, 2 );
function nk_convert_order_to_bdt_for_eps( $order, $data ) {
    // If the user selected EPS, we convert the final order details right before saving
    if ( $data['payment_method'] === 'eps' ) {
        $exchange_rate = 127;
        $total_usd = $order->get_total();
        
        // Change the currency to BDT and apply the exchange rate multiplier
        $order->set_currency('BDT');
        $order->set_total( $total_usd * $exchange_rate );
    }
}