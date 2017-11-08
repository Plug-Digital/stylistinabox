<?php

/** @var stdClass $subscriptionForm */
/** @var array $plans */

$options   = get_option( 'fullstripe_options' );
$lockEmail = $options['lock_email_field_for_logged_in_users'];

$emailAddress   = "";
$isUserLoggedIn = is_user_logged_in();
if ( $lockEmail == '1' && $isUserLoggedIn ) {
	$currentUser  = wp_get_current_user();
	$emailAddress = $currentUser->user_email;
}

$formNameAsIdentifier = esc_attr( $subscriptionForm->name );
$wpfsFormCount        = MM_WPFS::get_rendered_forms()->get_total();
$htmlFormAttributes   = 'class="subscription-form wpfs-payment-form form-horizontal"';
if ( $wpfsFormCount == 1 ) {
	$htmlFormAttributes .= ' id="payment-form"';
} else {
	$htmlFormAttributes .= ' id="subscription-form__' . $formNameAsIdentifier . '"';
}
$htmlFormAttributes .= ' data-form-id="' . $formNameAsIdentifier . '"';
$htmlFormAttributes .= ' data-show-address="' . esc_attr( $subscriptionForm->showAddress ) . '"';
if ( $subscriptionForm->showCustomInput == 1 && $subscriptionForm->customInputs ) {
	$htmlFormAttributes .= ' data-custom-input-title="' . esc_attr( $subscriptionForm->customInputTitle ) . '"';
	$htmlFormAttributes .= ' data-custom-inputs="' . esc_attr( $subscriptionForm->customInputs ) . '"';
	$htmlFormAttributes .= ' data-custom-input-required="' . esc_attr( $subscriptionForm->customInputRequired ) . '"';
}

$showLoadingId             = 'show-loading__' . $formNameAsIdentifier;
$showLoadingCouponId       = 'show-loading-coupon__' . $formNameAsIdentifier;
$paymentFormSubmitId       = 'payment-form-submit__' . $formNameAsIdentifier;
$paymentFormCouponSubmitId = 'fullstripe-check-coupon-code__' . $formNameAsIdentifier;
$couponInputId             = 'fullstripe-coupon-input__' . $formNameAsIdentifier;
$planInputId               = 'fullstripe-plan__' . $formNameAsIdentifier;
$addressCountryInputId     = 'fullstripe-address-country__' . $formNameAsIdentifier;

?>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<fieldset>
		<div id="legend__<?php echo $formNameAsIdentifier; ?>">
            <span class="fullstripe-form-title">
                <?php MM_WPFS::echo_translated_label( $subscriptionForm->formTitle ); ?>
            </span>
		</div>
		<input type="hidden" name="action" value="wp_full_stripe_subscription_charge"/>
		<input type="hidden" name="formName" value="<?php echo esc_attr( $subscriptionForm->name ); ?>"/>
		<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $subscriptionForm->subscriptionFormID ); ?>"/>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<input type="text" autocomplete="off" class="input-xlarge fullstripe-form-input" name="fullstripe_name" id="fullstripe_name__<?php echo $formNameAsIdentifier; ?>" data-stripe="name">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Email Address', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<?php if ( $lockEmail == '1' && $isUserLoggedIn ): ?>
					<label class="fullstripe-data-label"><?php echo $emailAddress; ?></label>
					<input type="hidden" value="<?php echo $emailAddress; ?>" name="fullstripe_email" id="fullstripe_email__<?php echo $formNameAsIdentifier; ?>">
				<?php else: ?>
					<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_email" id="fullstripe_email__<?php echo $formNameAsIdentifier; ?>">
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $subscriptionForm->showCustomInput == 1 ): ?>
			<?php
			$customInputs = array();
			if ( $subscriptionForm->customInputs != null ) {
				$customInputs = explode( '{{', $subscriptionForm->customInputs );
			}
			?>
			<?php if ( $subscriptionForm->customInputs == null ): ?>
				<div class="control-group">
					<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $subscriptionForm->customInputTitle ); ?></label>
					<div class="controls">
						<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input" id="fullstripe-custom-input__<?php echo $formNameAsIdentifier; ?>">
					</div>
				</div>
			<?php endif; ?>
			<?php foreach ( $customInputs as $i => $label ): ?>
				<div class="control-group">
					<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $label ); ?></label>
					<div class="controls">
						<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input[]" id="fullstripe-custom-input__<?php echo $formNameAsIdentifier . '__' . ( $i + 1 ); ?>">
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Subscription Plan', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<select id="<?php echo $planInputId; ?>" name="fullstripe_plan" class="fullstripe-plan fullstripe-form-input input-xlarge" data-form-id="<?php echo $formNameAsIdentifier; ?>">
					<?php foreach ( $plans as $plan ): ?>
						<?php
						$currencyArray = MM_WPFS::get_currency_for( $plan->currency );
						$setupFee      = 0;
						if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
							$setupFee = $plan->metadata->setup_fee;
						}
						?>
						<option value="<?php echo esc_attr( $plan->id ); ?>"
						        data-value="<?php echo esc_attr( $plan->id ); ?>"
						        data-amount="<?php echo MM_WPFS::format_amount( $plan->currency, $plan->amount ); ?>"
						        data-amount-in-smallest-common-currency="<?php echo $plan->amount; ?>"
						        data-interval="<?php echo esc_attr( MM_WPFS::get_translated_interval_label( $plan->interval, $plan->interval_count ) ); ?>"
						        data-interval-count="<?php echo esc_attr( $plan->interval_count ); ?>"
						        data-currency="<?php echo esc_attr( $plan->currency ); ?>"
						        data-zero-decimal-support="<?php echo( $currencyArray['zeroDecimalSupport'] == true ? 'true' : 'false' ); ?>"
						        data-currency-symbol="<?php echo esc_attr( MM_WPFS::get_currency_symbol_for( $plan->currency ) ); ?>"
						        data-setup-fee="<?php echo MM_WPFS::format_amount( $plan->currency, $setupFee ); ?>"
						        data-setup-fee-in-smallest-common-currency="<?php echo $setupFee; ?>">
							<?php echo esc_html( MM_WPFS::translate_label( $plan->name ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php if ( $subscriptionForm->showAddress == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Street', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_line1" id="fullstripe_address_line1__<?php echo $formNameAsIdentifier; ?>" class="fullstripe-form-input input-xlarge"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Line 2', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_line2" id="fullstripe_address_line2__<?php echo $formNameAsIdentifier; ?>" class="fullstripe-form-input input-xlarge"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'City', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_city" id="fullstripe_address_city__<?php echo $formNameAsIdentifier; ?>" class="fullstripe-form-input input-xlarge"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Zip', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_zip" id="fullstripe_address_zip__<?php echo $formNameAsIdentifier; ?>" class="fullstripe-form-input input-medium"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'State', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_state" id="fullstripe_address_state__<?php echo $formNameAsIdentifier; ?>" class="fullstripe-form-input input-medium"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Country', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<select name="fullstripe_address_country" id="<?php echo $addressCountryInputId; ?>" class="fullstripe-form-input input-xlarge">
						<option value=""><?php echo esc_html( __( 'Select country', 'wp-full-stripe' ) ); ?></option>
						<?php
						foreach ( MM_WPFS::get_available_countries() as $countryKey => $countryObject ) {
							$option = "<option value=\"{$countryKey}\">";
							$option .= MM_WPFS::translate_label( $countryObject['name'] );
							$option .= '</option>';
							echo $option;
						}
						?>
					</select><br/>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Number', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<input type="text" autocomplete="off" class="input-xlarge fullstripe-form-input" size="20" data-stripe="number">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Expiry Date', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<input type="text" size="2" data-stripe="exp-month" class="fullstripe-form-input input-mini"/>
				<span> / </span>
				<input type="text" size="4" data-stripe="exp-year" class="fullstripe-form-input input-mini"/>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card CVV', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<input type="password" autocomplete="off" class="input-mini fullstripe-form-input" size="4" maxlength="4" data-stripe="cvc"/>
			</div>
		</div>
		<?php if ( $subscriptionForm->showCouponInput == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Coupon Code', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" class="input-medium fullstripe-form-input" name="fullstripe_coupon_input" id="<?php echo $couponInputId; ?>">
					<button id="<?php echo $paymentFormCouponSubmitId; ?>" class="payment-form-coupon" data-form-id="<?php echo $formNameAsIdentifier; ?>"><?php _e( 'Apply', 'wp-full-stripe' ); ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ); ?>" id="<?php echo $showLoadingCouponId; ?>" class="loading-animation"/>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $subscriptionForm->buttonTitle ); ?></button>
				<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ); ?>" id="<?php echo $showLoadingId; ?>" class="loading-animation"/>
			</div>
		</div>
	</fieldset>
</form>
