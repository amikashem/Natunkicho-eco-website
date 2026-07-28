<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The class that defines the Paid Member Subscriptions integration
 *
 */
Class SliceWP_Integration_Paid_Member_Subscriptions extends SliceWP_Integration {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		/**
		 * Set the name of the integration
		 *
		 */
		$this->name = 'Paid Member Subscriptions';

		/**
		 * Set the supports values
		 *
		 */
		$supports = array(
			'commission_types' 				=> array( 'subscription' ),
			'new_customer_commissions_only' => true
		);

		/**
		 * Filter the supports array
		 *
		 * @param array $supports
		 *
		 */
		$this->supports = apply_filters( 'slicewp_integration_supports_pms', $supports );

	}


	/**
	 * Returns the admin edit page URL for the given coupon ID.
	 * 
	 * @param int $coupon_id
	 * 
	 * @return string
	 * 
	 */
	public function get_coupon_admin_url( $coupon_id ) {

		return add_query_arg( array( 'post' => absint( $coupon_id ), 'action' => 'edit' ), admin_url( 'post.php' ) );

	}

}