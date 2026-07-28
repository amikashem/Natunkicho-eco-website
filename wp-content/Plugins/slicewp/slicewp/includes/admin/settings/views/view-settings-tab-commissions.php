<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Commissions Settings -->
<div id="slicewp-card-settings-commissions-settings" class="slicewp-card">

	<div class="slicewp-card-header">
		<span class="slicewp-card-title"><?php echo __( 'Commissions Settings', 'slicewp' ); ?></span>

		<div class="slicewp-card-actions">
			<a href="https://slicewp.com/docs/commission-rates/" target="_blank" class="slicewp-button-info" title="<?php echo esc_attr( __( 'Click to learn more...', 'slicewp' ) ); ?>"><svg height="18" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M13 9h-2V7h2v2zm0 2h-2v6h2v-6zm-1-7c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8m0-2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"></path></g></svg></a>
		</div>
	</div>

	<div class="slicewp-card-inner">

		<?php 
			$commission_types = slicewp_get_available_commission_types();
		?>

		<!-- Commission Rates -->
		<?php foreach ( $commission_types as $type => $details ): ?>

			<?php if ( in_array( $type, array( 'recurring', 'lifetime_sale', 'product_revenue_share' ) ) ) continue; ?>

			<?php 
				$rate 	   = slicewp_get_setting( 'commission_rate_' . $type );
				$rate_type = slicewp_get_setting( 'commission_rate_type_' . $type );
			?>

			<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-field-wrapper-commission-rate" style="display: none;">

				<div class="slicewp-field-label-wrapper">
					<label for="slicewp-commission-rate-<?php echo str_replace( '_', '-', $type ); ?>">
						<?php echo sprintf( __( '%s Rate', 'slicewp' ), $details['label'] ); ?>
					</label>
				</div>

				<input id="slicewp-commission-rate-<?php echo str_replace( '_', '-', $type ); ?>" name="settings[commission_rate_<?php echo $type; ?>]" type="number" step="any" min="0" value="<?php echo ( ! empty( $_POST['settings']['commission_rate_' . $type] ) ? esc_attr( $_POST['settings']['commission_rate_' . $type] ) : $rate) ?>" />					

				<select name="settings[commission_rate_type_<?php echo $type; ?>]" class="slicewp-select2" <?php echo ( count( $details['rate_types'] ) == 1 ? 'disabled' : '' ); ?>>
					<?php $currency_symbol = slicewp_get_currency_symbol( slicewp_get_setting( 'active_currency', 'USD' ) ); ?>
					<?php foreach( $details['rate_types'] as $details_rate_type ): ?>
						<option value="<?php echo esc_attr( $details_rate_type ); ?>" <?php selected( $rate_type, $details_rate_type ); ?>><?php echo ( $details_rate_type == 'percentage' ? __( 'Percentage (%)', 'slicewp' ) : __( 'Fixed Amount', 'slicewp' ) . ' (' . esc_attr( $currency_symbol ) . ')' ); ?></option>
					<?php endforeach; ?>
				</select>

			</div>

		<?php endforeach; ?>
		<!-- / Commission Rates -->

		<!-- Sale Fixed Amount Commission Basis -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline" style="display: none;">

			<div class="slicewp-field-label-wrapper">
				<label>
					<?php echo __( 'Sale Commission Basis', 'slicewp' ); ?>
				</label>
			</div>

			<select id="slicewp-fixed-amount-commission-basis" name="settings[commission_fixed_amount_rate_basis]" class="slicewp-select2">
				<option value="product" <?php echo ( slicewp_get_setting( 'commission_fixed_amount_rate_basis' ) == 'product' ? 'selected="selected"' : '' ); ?>><?php echo __( 'Fixed amount per product', 'slicewp' ); ?></option>
				<option value="order" <?php echo ( slicewp_get_setting( 'commission_fixed_amount_rate_basis' ) == 'order' ? 'selected="selected"' : '' ); ?>><?php echo __( 'Fixed amount per order', 'slicewp' ); ?></option>
			</select>

		</div>
		<!-- / Sale Fixed Amount Commission Basis -->

		<!-- Exclude Shipping -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-exclude-shipping">
					<?php echo __( 'Exclude Shipping', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( 'The option is applicable only for WooCommerce.', 'slicewp' ) ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-exclude-shipping" class="slicewp-toggle slicewp-toggle-round" name="settings[exclude_shipping]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['exclude_shipping'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'exclude_shipping' ) : '' ), '1' ); ?> />
				<label for="slicewp-exclude-shipping"></label>

			</div>

			<label for="slicewp-exclude-shipping"><?php echo __( 'Exclude shipping costs from commission calculations.', 'slicewp' ); ?></label>

		</div><!-- / Exclude Shipping -->

		<!-- Exclude Tax -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-exclude-tax">
					<?php echo __( 'Exclude Taxes', 'slicewp' ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-exclude-tax" class="slicewp-toggle slicewp-toggle-round" name="settings[exclude_tax]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['exclude_tax'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'exclude_tax' ) : '' ), '1' ); ?> />
				<label for="slicewp-exclude-tax"></label>

			</div>

			<label for="slicewp-exclude-tax"><?php echo __( 'Exclude taxes from commission calculations.', 'slicewp' ); ?></label>

		</div><!-- / Exclude Tax -->

		<!-- New Customers Only -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide" style="display: none;">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-new-customer-commissions-only">
					<?php echo __( 'New Customers Only', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . __( "By default, affiliates earn a commission for every referred purchase. When this option is enabled, commissions will only be awarded for a customer's first purchase.", 'slicewp' ) . '</p><p>' . sprintf( __( "%sRecurring commissions%s and %slifetime commissions%s will not be affected by this option.", 'slicewp' ), '<a href="https://slicewp.com/products/recurring-commissions/" target="_blank">', '</a>', '<a href="https://slicewp.com/products/lifetime-commissions/" target="_blank">', '</a>' ) . '</p>' ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-new-customer-commissions-only" class="slicewp-toggle slicewp-toggle-round" name="settings[new_customer_commissions_only]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['new_customer_commissions_only'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'new_customer_commissions_only' ) : '' ), '1' ); ?> />
				<label for="slicewp-new-customer-commissions-only"></label>

			</div>

			<label for="slicewp-new-customer-commissions-only"><?php echo __( "Reward commissions only for customers' first purchase.", 'slicewp' ); ?></label>

		</div><!-- / New Customers Only -->

		<!-- Reject Unpaid Commissions on Refund -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-reject-commissions-on-refund">
					<?php echo __( 'Reject Commissions on Refund', 'slicewp' ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-reject-commissions-on-refund" class="slicewp-toggle slicewp-toggle-round" name="settings[reject_commissions_on_refund]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['reject_commissions_on_refund'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'reject_commissions_on_refund' ) : '' ), '1' ); ?> />
				<label for="slicewp-reject-commissions-on-refund"></label>

			</div>

			<label for="slicewp-reject-commissions-on-refund"><?php echo __( 'Mark unpaid commissions as rejected if the originating purchase is refunded.', 'slicewp' ); ?></label>

		</div><!-- / Reject Unpaid Commissions on Refund -->

		<!-- Zero Amount Commissions -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-zero-amount-commissions">
					<?php echo __( 'Zero Amount Commissions', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( 'Enable the registration of commisions that have the total amount equal to zero. This is useful if you want to track conversions for fully discounted products.', 'slicewp' ) ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-zero-amount-commissions" class="slicewp-toggle slicewp-toggle-round" name="settings[zero_amount_commissions]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['zero_amount_commissions'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'zero_amount_commissions' ) : '' ), '1' ); ?> />
				<label for="slicewp-zero-amount-commissions"></label>

			</div>

			<label for="slicewp-zero-amount-commissions"><?php echo __( 'Enable the registration of zero sum commisions.', 'slicewp' ); ?></label>

		</div><!-- Zero Amount Commissions -->
        
        <!-- Affiliate Own Commissions -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-affiliates-own-commissions">
					<?php echo __( 'Affiliate Own Commissions', 'slicewp' ); ?>
				</label>
			</div>

			<div class="slicewp-switch">

				<input id="slicewp-affiliates-own-commissions" class="slicewp-toggle slicewp-toggle-round" name="settings[affiliate_own_commissions]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['affiliate_own_commissions'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_own_commissions' ) : '' ), '1' ); ?> />
				<label for="slicewp-affiliates-own-commissions"></label>

			</div>

			<label for="slicewp-affiliates-own-commissions"><?php echo __( "Allow affiliates to earn commissions on self-referred orders.", 'slicewp' ); ?></label>

		</div><!-- / Affiliate Own Commissions -->
		
	</div>

</div><!-- / Commisions Settings -->

<?php

	/**
	 * Hook to add extra cards if needed to the Commissions tab.
	 *
	 */
	do_action( 'slicewp_view_settings_tab_commissions_bottom' );

?>

<!-- Save Settings Button -->
<input type="submit" class="slicewp-form-submit slicewp-button-primary" value="<?php echo __( 'Save Settings', 'slicewp' ); ?>" />