<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<!-- Register website -->
<div class="slicewp-card">

	<div class="slicewp-card-header">
		<span class="slicewp-card-title"><?php echo __( 'Register Website', 'slicewp' ); ?></span>
	</div>

	<div class="slicewp-card-inner">

		<!-- License Key -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-last">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-license-key">
					<?php echo __( 'License Key', 'slicewp' ); ?>
				</label>
			</div>

			<div class="slicewp-flex">

				<?php $license_key = get_option( 'slicewp_license_key', '' ); ?>

				<input id="slicewp-license-key" name="license_key" type="<?php echo ( empty( $license_key ) ? 'text' : 'password' ); ?>" value="<?php echo esc_attr( $license_key ); ?>">
				
				<a id="slicewp-register-license-key" class="slicewp-button-secondary" href="#">
					<span class="slicewp-register" <?php echo ( slicewp_is_website_registered() ? 'style="display: none;"' : '' ); ?>><?php echo __( 'Register', 'slicewp' ); ?></span>
					<span class="slicewp-deregister" <?php echo ( ! slicewp_is_website_registered() ? 'style="display: none;"' : '' ); ?>><?php echo __( 'Deregister', 'slicewp' ); ?></span>
					<div class="spinner"></div>
				</a>
				
			</div>

			<input id="slicewp-is-website-registered" type="hidden" value="<?php echo ( slicewp_is_website_registered() ? 'true' : 'false' ); ?>" />

			<?php if( ! slicewp_is_website_registered() ): ?>
				<div class="slicewp-field-notice slicewp-field-notice-warning" style="display: block;">
					<p><strong><?php echo __( 'Enter your license key.', 'slicewp' ); ?></strong> <?php echo sprintf( __( 'Your license key can be found in your %sSliceWP account%s.', 'slicewp' ), '<a href="https://slicewp.com/account/?utm_source=plugin-free&amp;utm_medium=plugin-card-license-key&amp;utm_campaign=SliceWPFree" target="_blank">', '</a>' ); ?></p>
					<p><?php echo sprintf( __( 'You can use this core version of SliceWP for free. For priority support and advanced functionality, %sa license key is required%s.', 'slicewp' ), '<a href="https://slicewp.com/pricing/?utm_source=plugin-free&amp;utm_medium=plugin-card-license-key&amp;utm_campaign=SliceWPFree" target="_blank">', '</a>' ); ?></p>
				</div>
			<?php endif; ?>

		</div><!-- / License Key -->

	</div>

</div>
<!-- / Register website -->

<!-- Referral Tracking -->
<div class="slicewp-card">

	<?php $affiliate_keyword = ( ! empty( $_POST['settings']['affiliate_keyword'] ) ? $_POST['settings']['affiliate_keyword'] : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_keyword' ) : '' ) ); ?>

	<div class="slicewp-card-header">
		<span class="slicewp-card-title"><?php echo __( 'Referral Tracking', 'slicewp' ); ?></span>

		<div class="slicewp-card-actions">
			<a href="https://slicewp.com/docs/affiliate-links/" target="_blank" class="slicewp-button-info" title="<?php echo esc_attr( __( 'Click to learn more...', 'slicewp' ) ); ?>"><svg height="18" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M13 9h-2V7h2v2zm0 2h-2v6h2v-6zm-1-7c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8m0-2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"></path></g></svg></a>
		</div>
	</div>

	<div class="slicewp-card-inner">

		<!-- Affiliate Keyword -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-affiliate-keyword">
					<?php echo __( 'Affiliate Keyword', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . __( 'The URL query parameter name used for affiliate identification.', 'slicewp' ) . '</p><p>' . sprintf( __( 'Example: %s', 'slicewp' ), '<code style="font-family: inherit;">' . trailingslashit( site_url() ) . '?' . '<strong>' . esc_html( $affiliate_keyword ) . '</strong>' . '=' . $affiliate_id ) . '</code>' . '</p>' . '<hr />' . '<a href="https://slicewp.com/docs/affiliate-links/" target="_blank">' . __( 'Click here to learn more', 'slicewp' ) . '</a>' ); ?>
				</label>
			</div>

			<input id="slicewp-affiliate-keyword" name="settings[affiliate_keyword]" type="text" value="<?php echo esc_attr( ! empty( $_POST['settings']['affiliate_keyword'] ) ? $_POST['settings']['affiliate_keyword'] : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_keyword' ) : '' ) ); ?>">

		</div><!-- / Affiliate Keyword -->

		<!-- Cookie Duration -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-cookie-duration">
					<?php echo __( 'Tracking Cookie Duration', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . __( 'The number of days a referred visitor is being tracked.' , 'slicewp' ) . '</p><p>' . __( 'If the referred visitor makes a purchase in this timeframe, the referring affiliate will be rewarded a commission.', 'slicewp' ) . '</p>' . '<hr />' . '<a href="https://slicewp.com/docs/cookie-duration/" target="_blank">' . __( 'Click here to learn more', 'slicewp' ) . '</a>' ); ?>
				</label>
			</div>

			<div style="display: flex; gap: 10px;">
				<input id="slicewp-cookie-duration" name="settings[cookie_duration]" type="number" value="<?php echo ( ! empty( $_POST['settings']['cookie_duration'] ) ? esc_attr( $_POST['settings']['cookie_duration'] ) : ( slicewp_get_setting( 'cookie_duration' ) ) ); ?>">
				<input type="text" value="<?php echo __( 'Days', 'slicewp' ); ?>" disabled />
			</div>

		</div><!-- / Cookie Duration -->

		<!-- Credit First/Last Affiliate -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label>
					<?php echo __( 'Credit First/Last Affiliate', 'slicewp' ); ?>
				</label>
			</div>

			<select id="slicewp-affiliate-credit" name="settings[affiliate_credit]" class="slicewp-select2">
				<option value="first" <?php echo selected( ( ! empty( $_POST['settings']['affiliate_credit'] ) ? $_POST['settings']['affiliate_credit'] : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_credit' ) : '' ) ) , 'first' ); ?>><?php echo __( 'First Affiliate', 'slicewp' ); ?></option>
				<option value="last" <?php echo selected( ( ! empty( $_POST['settings']['affiliate_credit'] ) ? $_POST['settings']['affiliate_credit'] : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_credit' ) : '' ) ) , 'last' ); ?>><?php echo __( 'Last Affiliate', 'slicewp' ); ?></option>
			</select>

		</div><!-- / Credit First/Last Affiliate -->

        <!-- Friendly Affiliate URLs -->
        <div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide">

            <div class="slicewp-field-label-wrapper">
                <label for="slicewp-friendly-affiliate-urls">
                    <?php echo __( 'Friendly Affiliate URLs', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . __( 'When enabled, the affiliate referral links will look like this:', 'slicewp' ) . '<br />' . '<code style="display: inline-block; margin-top: 3px; font-family: inherit;">' . untrailingslashit( site_url() ) . '<strong>' . '/' . '<span>' . esc_html( $affiliate_keyword ) . '</span>' . '/' . $affiliate_id . '/' . '</strong>' . '</code>' . '</p><p>' . __( 'Instead of this:', 'slicewp' ) . '<br />' . '<code style="display: inline-block; margin-top: 3px; font-family: inherit;">' . untrailingslashit( site_url() ) . '<strong>' . '/?' . '<span>' . esc_html( $affiliate_keyword ) . '</span>' . '=' . $affiliate_id . '</strong>' . '</code>' . '</p>' . '<hr />' . '<a href="https://slicewp.com/docs/affiliate-links/" target="_blank">' . __( 'Click here to learn more', 'slicewp' ) . '</a>' ); ?>
                </label>
            </div>

            <div class="slicewp-switch">

                <input id="slicewp-friendly-affiliate-urls" class="slicewp-toggle slicewp-toggle-round" name="settings[friendly_affiliate_url]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['friendly_affiliate_url'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'friendly_affiliate_url' ) : '' ), '1' ); ?> />
                <label for="slicewp-friendly-affiliate-urls"></label>

            </div>

            <label for="slicewp-friendly-affiliate-urls"><?php echo __( 'Use friendly affiliate URLs.', 'slicewp' ); ?></label>

        </div><!-- / Friendly Affiliate URLs -->

		<!-- Referral Links QR Code -->
        <div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

            <div class="slicewp-field-label-wrapper">
                <label for="slicewp-referral-link-qr-code">
                    <?php echo __( 'Affiliate Link QR Code', 'slicewp' ); ?>
                    <?php echo slicewp_output_tooltip( __( 'When enabled, your affiliates will have the option to view and download the QR code for their affiliate referral links.', 'slicewp' ) ); ?>
                </label>
            </div>

            <div class="slicewp-switch">

                <input id="slicewp-referral-link-qr-code" class="slicewp-toggle slicewp-toggle-round" name="settings[referral_link_qr_code]" type="checkbox" value="1" <?php checked( ! empty( $_POST['settings']['referral_link_qr_code'] ) ? '1' : ( empty( $_POST ) ? slicewp_get_setting( 'referral_link_qr_code' ) : '' ), '1' ); ?> />
                <label for="slicewp-referral-link-qr-code"></label>

            </div>

            <label for="slicewp-referral-link-qr-code"><?php echo __( 'Show QR code for affiliate URLs in the affiliate account.', 'slicewp' ); ?></label>

        </div><!-- Referral Links QR Code -->

		<!-- Affiliate Default URL Override -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-last">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-affiliate-referral-url">
					<?php echo __( 'Default URL Override', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( "The default affiliate referral URL shown in the affiliate account. If left empty, your website's home URL will be used.", 'slicewp' ) ); ?>
				</label>
			</div>

			<input id="slicewp-affiliate-referral-url" name="settings[affiliate_url_base]" type="url" value="<?php echo esc_attr( ! empty( $_POST['settings']['affiliate_url_base'] ) ? $_POST['settings']['affiliate_url_base'] : ( empty( $_POST ) ? slicewp_get_setting( 'affiliate_url_base' ) : '' ) ); ?>">
			
		</div><!-- / Affiliate Default URL Override -->

	</div>

</div><!-- / Referral Tracking -->

<!-- Payouts Settings -->
<div id="slicewp-card-payouts-settings" class="slicewp-card">

	<div class="slicewp-card-header">
		<span class="slicewp-card-title"><?php echo __( 'Payouts Settings', 'slicewp' ); ?></span>

		<div class="slicewp-card-actions">
			<a href="https://slicewp.com/docs/paying-your-affiliates/" target="_blank" class="slicewp-button-info" title="<?php echo esc_attr( __( 'Click to learn more...', 'slicewp' ) ); ?>"><svg height="18" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M13 9h-2V7h2v2zm0 2h-2v6h2v-6zm-1-7c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8m0-2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"></path></g></svg></a>
		</div>
	</div>

	<div class="slicewp-card-inner">

		<!-- Default Payout Method -->
		<?php $payout_methods = slicewp_get_payout_methods(); ?>

		<?php if ( count( $payout_methods ) > 1 ): ?>

			<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide">

				<div class="slicewp-field-label-wrapper">
					<label for="slicewp-default-payout-method">
						<?php echo __( 'Default Payout Method', 'slicewp' ); ?>
					</label>
				</div>

				<select class="slicewp-select2" name="settings[default_payout_method]">

					<?php foreach ( $payout_methods as $method_slug => $method_data ): ?>
						<option value="<?php echo esc_attr( $method_slug ); ?>" <?php echo selected( ( ! empty( $_POST['settings']['default_payout_method'] ) ? $_POST['settings']['default_payout_method'] : ( empty( $_POST ) ? slicewp_get_setting( 'default_payout_method' ) : '' ) ) , $method_slug ); ?>><?php echo $method_data['label']; ?></option>
					<?php endforeach; ?>

				</select>

			</div>

		<?php else: ?>

			<input type="hidden" name="settings[default_payout_method]" value="<?php echo esc_attr( count( $payout_methods ) == 1 && array_key_exists( ( ! empty( $_POST['settings']['default_payout_method'] ) ? $_POST['settings']['default_payout_method'] : ( empty( $_POST ) ? slicewp_get_setting( 'default_payout_method' ) : 'manual' ) ), $payout_methods ) ? : 'manual' ); ?>" />
			
		<?php endif; ?>
		<!-- / Default Payout Method -->

		<!-- Payments Minimum Amount -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-payments-minimum-amount">
					<?php echo __( 'Payments Minimum Amount', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . sprintf( __( 'Set a minimum commissions total amount that your affiliates need to reach to be eligible for payment.', 'slicewp' ) ) . '</p>' . '<hr />' . '<a href="https://slicewp.com/docs/paying-your-affiliates/#payments-minimum-amount" target="_blank">' . __( 'Click here to learn more', 'slicewp' ) . '</a>' ); ?>
				</label>
			</div>

			<div class="slicewp-field-currency-amount">
				<div class="slicewp-field-currency-symbol"><?php echo slicewp_get_currency_symbol( slicewp_get_setting( 'active_currency', 'USD' ) ); ?></div>
				<input id="slicewp-payments-minimum-amount" name="settings[payments_minimum_amount]" type="number" value="<?php echo( ! empty( $_POST['settings']['payments_minimum_amount'] ) ? esc_attr( $_POST['settings']['payments_minimum_amount'] ) : ( ! empty( slicewp_get_setting( 'payments_minimum_amount' ) ) ? slicewp_get_setting( 'payments_minimum_amount' ) : 0 ) ); ?>">
			</div>

		</div><!-- / Payments Minimum Amount -->

		<!-- Refund Grace Period -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-tooltip-wide slicewp-last">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-refund-grace-period">
					<?php echo __( 'Refund Grace Period', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( '<p>' . sprintf( __( 'The grace period (set in number of days) is used when generating payouts for your affiliates. It helps you filter out commissions that could still be rejected due to a refund of the underlying purchase. We recommend you to set this equal to your store refund policy.', 'slicewp' ) ) . '</p>' . '<hr />' . '<a href="https://slicewp.com/docs/paying-your-affiliates/#refund-grace-period" target="_blank">' . __( 'Click here to learn more', 'slicewp' ) . '</a>' ); ?>
				</label>
			</div>

			<input id="slicewp-refund-grace-period" name="settings[commissions_grace_period]" type="number" value="<?php echo( ! empty( $_POST['settings']['commissions_grace_period'] ) ? esc_attr( $_POST['settings']['commissions_grace_period'] ) : ( ! empty( slicewp_get_setting( 'commissions_grace_period' ) ) ? slicewp_get_setting( 'commissions_grace_period' ) : 0 ) ); ?>">

		</div><!-- / Refund Grace Period -->

		<?php
		
			/**
			 * Hook to add extra fields if needed to the Payouts Settings card.
			 *
			 */
			do_action( 'slicewp_view_settings_section_payouts_settings_bottom' );
		
		?>

	</div>

</div><!-- / Payouts Settings -->

<!-- Currency Settings -->
<div id="slicewp-card-settings-currency" class="slicewp-card">

	<div class="slicewp-card-header">
		<span class="slicewp-card-title"><?php echo __( 'Currency Settings', 'slicewp' ); ?></span>
	</div>

	<div class="slicewp-card-inner">

		<!-- Currency -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-active-currency">
					<?php echo __( 'Currency', 'slicewp' ); ?>
				</label>
			</div>

			<select id="slicewp-active-currency" name="settings[active_currency]" class="slicewp-select2">
				<?php foreach( slicewp_get_currencies() as $currency_code => $currency_name ): ?>
					<?php $currency_symbol = slicewp_get_currency_symbol( $currency_code ); ?>
					<option value="<?php echo esc_attr( $currency_code ); ?>" <?php echo selected( ! empty( $_POST['settings']['active_currency'] ) ? $_POST['settings']['active_currency'] : ( empty( $_POST ) ? slicewp_get_setting( 'active_currency' ) : '' ), $currency_code ); ?>><?php echo esc_attr( $currency_name ) . ( ! empty( $currency_symbol ) ? ( ' (' . $currency_symbol . ')' ) : '' ); ?></option>
				<?php endforeach; ?>
			</select>

		</div><!-- / Currency -->

		<!-- Currency Symbol Position -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-currency-symbol-position">
					<?php echo __( 'Currency Symbol Position', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( 'The position of the currency symbol in relation with the amount value, when displaying amounts.', 'slicewp' ) ); ?>
				</label>
			</div>
			
			<select id="slicewp-currency-symbol-position" name="settings[currency_symbol_position]" class="slicewp-select2">
				<option value="before" <?php echo selected( ( ! empty( $_POST['settings']['currency_symbol_position'] ) ? $_POST['settings']['currency_symbol_position'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_symbol_position' ) : '' ) ) , 'before' ); ?>><?php echo __( 'Before amount', 'slicewp' ); ?></option>
				<option value="after" <?php echo selected( ( ! empty( $_POST['settings']['currency_symbol_position'] ) ? $_POST['settings']['currency_symbol_position'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_symbol_position' ) : '' ) ) , 'after' ); ?>><?php echo __( 'After amount', 'slicewp' ); ?></option>
				<option value="before_space" <?php echo selected( ( ! empty( $_POST['settings']['currency_symbol_position'] ) ? $_POST['settings']['currency_symbol_position'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_symbol_position' ) : '' ) ) , 'before_space' ); ?>><?php echo __( 'Before amount with space', 'slicewp' ); ?></option>
				<option value="after_space" <?php echo selected( ( ! empty( $_POST['settings']['currency_symbol_position'] ) ? $_POST['settings']['currency_symbol_position'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_symbol_position' ) : '' ) ) , 'after_space' ); ?>><?php echo __( 'After amount with space', 'slicewp' ); ?></option>
			</select>

		</div><!-- / Currency Symbol Position -->

		<!-- Thousands Separator -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-currency-thousands-separator">
					<?php echo __( 'Thousands Separator', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( 'The symbol to separate thousands. This is usually a , (comma) or a . (dot).', 'slicewp' ) ); ?>
				</label>
			</div>

			<input id="slicewp-currency-thousands-separator" name="settings[currency_thousands_separator]" type="text" value="<?php echo esc_attr( ! empty( $_POST['settings']['currency_thousands_separator'] ) ? $_POST['settings']['currency_thousands_separator'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_thousands_separator' ) : '' ) ); ?>">

		</div><!-- / Thousands Separator -->

		<!-- Decimal Separator -->
		<div class="slicewp-field-wrapper slicewp-field-wrapper-inline slicewp-last">

			<div class="slicewp-field-label-wrapper">
				<label for="slicewp-currency-decimal-separator">
					<?php echo __( 'Decimal Separator', 'slicewp' ); ?>
					<?php echo slicewp_output_tooltip( __( 'The symbol to separate decimal points. This is usually a , (comma) or a . (dot).', 'slicewp' ) ); ?>
				</label>
			</div>

			<input id="slicewp-currency-decimal-separator" name="settings[currency_decimal_separator]" type="text" value="<?php echo esc_attr( ! empty( $_POST['settings']['currency_decimal_separator'] ) ? $_POST['settings']['currency_decimal_separator'] : ( empty( $_POST ) ? slicewp_get_setting( 'currency_decimal_separator' ) : '' ) ); ?>">

		</div><!-- / Decimal Separator -->

	</div>

</div><!-- / Currency Settings -->

<?php 

	/**
	 * Hook to add extra cards if needed to the General Settings tab
	 *
	 */
	do_action( 'slicewp_view_settings_tab_general_bottom' );

	/**
	 * Hook to add extra cards if needed to the General Settings tab
	 *
	 * @deprecated 1.0.12 - No longer used in core and not recommended for external usage.
	 * 					    Replaced by "slicewp_view_settings_tab_general_bottom" action.
	 *					    Slated for removal in version 2.0.0
	 *
	 */
	do_action( 'slicewp_view_settings_tab_bottom_general' );

?>

<!-- Save Settings Button -->
<input type="submit" class="slicewp-form-submit slicewp-button-primary" value="<?php echo __( 'Save Settings', 'slicewp' ); ?>" />