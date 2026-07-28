<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Includes the files needed for the Payout admin area
 *
 */
function slicewp_include_files_admin_payout() {

	// Get creative admin dir path
	$dir_path = plugin_dir_path( __FILE__ );

	// Include submenu page
	if( file_exists( $dir_path . 'class-submenu-page-payouts.php' ) )
		include $dir_path . 'class-submenu-page-payouts.php';

	// Include actions
	if( file_exists( $dir_path . 'functions-actions-payouts.php' ) )
		include $dir_path . 'functions-actions-payouts.php';

	// Include actions
	if( file_exists( $dir_path . 'functions-actions-payments.php' ) )
		include $dir_path . 'functions-actions-payments.php';

	// Include payout payments preview list table
	if( file_exists( $dir_path . 'class-list-table-payout-payments-preview.php' ) )
		include $dir_path . 'class-list-table-payout-payments-preview.php';

	// Include payouts list table
	if( file_exists( $dir_path . 'class-list-table-payouts.php' ) )
		include $dir_path . 'class-list-table-payouts.php';

	// Include payout payments list table
	if( file_exists( $dir_path . 'class-list-table-payout-payments.php' ) )
		include $dir_path . 'class-list-table-payout-payments.php';

	// Include payments list table
	if( file_exists( $dir_path . 'class-list-table-payments.php' ) )
		include $dir_path . 'class-list-table-payments.php';

	// Include payments commissions list table
	if( file_exists( $dir_path . 'class-list-table-payment-commissions.php' ) )
		include $dir_path . 'class-list-table-payment-commissions.php';

}
add_action( 'slicewp_include_files', 'slicewp_include_files_admin_payout' );


/**
 * Register the Payouts admin submenu page
 *
 */
function slicewp_register_submenu_page_payouts( $submenu_pages ) {

	if( ! is_array( $submenu_pages ) )
		return $submenu_pages;

	$submenu_pages['payouts'] = array(
		'class_name' => 'SliceWP_Submenu_Page_Payouts',
		'data' 		 => array(
			'page_title' => __( 'Payouts', 'slicewp' ),
			'menu_title' => __( 'Payouts', 'slicewp' ),
			'capability' => apply_filters( 'slicewp_submenu_page_capability_payouts', 'manage_options' ),
			'menu_slug'  => 'slicewp-payouts'
		)
	);

	return $submenu_pages;

}
add_filter( 'slicewp_register_submenu_page', 'slicewp_register_submenu_page_payouts', 35 );


/**
 * Localizes the payout methods custom messages before the plugin's admin script
 *
 * These messages are used for admin interaction purposes
 *
 */
function slicewp_enqueue_admin_scripts_payout_methods_messages() {

	$payout_methods = slicewp_get_payout_methods();
	$messages 		= array();

	foreach( $payout_methods as $payout_method_slug => $payout_method ) {

		if( ! empty( $payout_method['messages'] ) )
			$messages[$payout_method_slug] = $payout_method['messages'];

	}

	wp_localize_script( 'slicewp-script', 'slicewp_payout_methods_messages', $messages );
	
}
add_action( 'slicewp_enqueue_admin_scripts', 'slicewp_enqueue_admin_scripts_payout_methods_messages' );


/**
 * Generates a csv with the provided data
 *
 */
function slicewp_generate_csv( $header, $data, $filename = 'data.csv' ) {

	header( "Content-Type: text/csv; charset=utf-8" );
	header( "Content-Disposition: attachment; filename=" . $filename );
	header( "Pragma: no-cache" );
	header( "Expires: 0" );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, $header );

	foreach ( $data as $row ) {

		unset( $csv_line );

		foreach ( $header as $key => $value ) {
			
			if ( isset( $row[$key] ) ) {

		 		$csv_line[] = $row[$key];

			}
		}

		fputcsv( $output, $csv_line );

	}
	
	die();

}


/**
 * Generates and returns an array with eligible payments for payout based on the given args.
 * 
 * Attention: This is an experimental function that is not considered complete.
 * 			  It's considered private and it should not be used outside of the plugin's core.
 * 
 * @access private
 * 
 * @param array $args
 * 
 * @return array
 * 
 */
function slicewp_generate_payout_payments_preview( $args ) {

	global $wpdb;

	$payments = array();

	// Set up to date.
	if ( ! empty( $args['date_range'] ) && $args['date_range'] == 'up_to' ) {

		$date_min = '';
		$date_max = new DateTime( ( ! empty( $args['date_up_to'] ) ? sanitize_text_field( $args['date_up_to'] ) : date( 'Y-m-d' ) ) . ' 23:59:59' );

	}

	// Set custom date range arguments.
	if ( ! empty( $args['date_range'] ) && $args['date_range'] == 'custom_range' ) {

		$date_min = ( ! empty( $args['date_min'] ) ? new DateTime( sanitize_text_field( $args['date_min'] ) . ' 00:00:00' ) : '' );
		$date_max = ( ! empty( $args['date_max'] ) ? new DateTime( sanitize_text_field( $args['date_max'] ) . ' 23:59:59') : '' );

	}

	// Set affiliates.
	if ( ! empty( $args['included_affiliates'] ) && $args['included_affiliates'] == 'selected' ) {

		if ( ! empty( $args['selected_affiliates'] ) && is_array( $args['selected_affiliates'] ) ) {

			$selected_affiliate_ids = array_values( array_filter( array_map( 'absint', $args['selected_affiliates'] ) ) );

		}

	}

	// Take into account the grace period.
	$grace_period = slicewp_get_setting( 'commissions_grace_period', 0 );

	if ( ! empty( $date_max ) && ! empty( $grace_period ) && empty( $args['include_grace_period'] ) ) {

		$date_grace_end = new DateTime( date( 'Y-m-d' ) . ' 23:59:59');
		$date_grace_end = $date_grace_end->modify( '-' . absint( $grace_period ) . ' day' );

		if ( $date_max > $date_grace_end ) {

			$date_max = $date_grace_end;

		}

	}

	// Get the currency.
	$currency = slicewp_get_setting( 'active_currency', 'USD' );

	// Get the payments minimum amount setting.
	$minimum_payment_amount = slicewp_sanitize_amount( isset( $args['payments_minimum_amount'] ) ? esc_attr( $args['payments_minimum_amount'] ) : 0 );

	// Prepare the commissions query.
	$table_name_affiliates  = slicewp()->db['affiliates']->table_name;
	$table_name_commissions = slicewp()->db['commissions']->table_name;

	// Prepare additional where clauses.
	$where_clause = '';

	if ( ! empty( $selected_affiliate_ids ) ) {
		$where_clause .= "AND a.id IN(" . implode( ',', $selected_affiliate_ids ) . ")";
	}

	if ( ! empty( $date_min ) ) {
		$where_clause .= "AND c.date_created >= '" . get_gmt_from_date( $date_min->format( 'Y-m-d H:i:s' ) ) . "'";
	}

	if ( ! empty( $date_max ) ) {
		$where_clause .= "AND c.date_created <= '" . get_gmt_from_date( $date_max->format( 'Y-m-d H:i:s' ) ) . "'";
	}

	// Prepare query string.
	$query = "SELECT 
			c.id AS id,
			c.affiliate_id,
			CAST( c.amount AS DECIMAL( 26, 8 ) ) AS amount
		FROM $table_name_commissions c
		INNER JOIN $table_name_affiliates a
				ON c.affiliate_id = a.id
		WHERE 
			a.status = 'active'
			AND c.status = 'unpaid'
			AND c.payment_id = 0
			{$where_clause}
		";

	// Query the commissions results.
	$commissions = $wpdb->get_results( $query, ARRAY_A );

	// Prepare the commissions into the payments.
	foreach ( $commissions as $commission ) {

		$affiliate_id = $commission['affiliate_id'];

		if ( ! isset( $payments[$affiliate_id] ) ) {

			$payments[$affiliate_id] = array(
				'affiliate_id'	 => $affiliate_id,
				'amount'		 => 0,
				'currency'		 => $currency,
				'commission_ids' => array()
			);

		}

		$payments[$affiliate_id]['amount'] 			+= (float)$commission['amount'];
		$payments[$affiliate_id]['commission_ids'][] = $commission['id'];

	}

	// Make sure only payments that reach the minimum payment amount are taken into account.
	foreach ( $payments as $affiliate_id => $payment ) {

		if ( $payment['amount'] < $minimum_payment_amount ) {
			unset( $payments[$affiliate_id] );
		}

	}

	// Refresh the array keys.
	$payments = array_values( $payments );

	return $payments;

}