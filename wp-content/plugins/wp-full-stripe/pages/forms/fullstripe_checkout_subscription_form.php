<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.03.27.
 * Time: 14:42
 */

/** @var stdClass $checkoutSubscriptionForm */
/** @var array $plans */

$options   = get_option( 'fullstripe_options' );
$lockEmail = $options['lock_email_field_for_logged_in_users'];

$emailAddress   = "";
$isUserLoggedIn = is_user_logged_in();
if ( $lockEmail == '1' && $isUserLoggedIn ) {
	$currentUser  = wp_get_current_user();
	$emailAddress = $currentUser->user_email;
}

$formNameAsIdentifier = esc_attr( $checkoutSubscriptionForm->name );

$htmlFormAttributes = 'class="checkout-subscription-form wpfs-payment-form form-horizontal"';
$htmlFormAttributes .= ' id="checkout-subscription-form__' . $formNameAsIdentifier . '"';
$htmlFormAttributes .= ' data-form-id="' . $formNameAsIdentifier . '"';
$htmlFormAttributes .= ' data-company-name="' . esc_attr( $checkoutSubscriptionForm->companyName ) . '"';
$htmlFormAttributes .= ' data-product-description="' . MM_WPFS::translate_label( $checkoutSubscriptionForm->productDesc ) . '"';
$htmlFormAttributes .= ' data-button-title="' . MM_WPFS::translate_label( $checkoutSubscriptionForm->buttonTitle ) . '"';
$htmlFormAttributes .= ' data-show-billing-address="' . esc_attr( $checkoutSubscriptionForm->showBillingAddress ) . '"';
$htmlFormAttributes .= ' data-show-remember-me="' . esc_attr( $checkoutSubscriptionForm->showRememberMe ) . '"';
if ( ! is_null( $checkoutSubscriptionForm->image ) && trim( $checkoutSubscriptionForm->image ) !== '' ) {
	$htmlFormAttributes .= ' data-image="' . esc_attr( $checkoutSubscriptionForm->image ) . '"';
}
if ( $checkoutSubscriptionForm->showCustomInput == 1 && $checkoutSubscriptionForm->customInputs ) {
	$htmlFormAttributes .= ' data-custom-input-title="' . esc_attr( $checkoutSubscriptionForm->customInputTitle ) . '"';
	$htmlFormAttributes .= ' data-custom-inputs="' . esc_attr( $checkoutSubscriptionForm->customInputs ) . '"';
	$htmlFormAttributes .= ' data-custom-input-required="' . esc_attr( $checkoutSubscriptionForm->customInputRequired ) . '"';
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
		<input type="hidden" name="action" value="fullstripe_checkout_subscription_form_charge"/>
		<input type="hidden" name="formName" value="<?php echo esc_attr( $checkoutSubscriptionForm->name ); ?>"/>
		<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $checkoutSubscriptionForm->checkoutSubscriptionFormID ); ?>"/>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Subscription Plan', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<select id="<?php echo $planInputId; ?>" name="fullstripe_plan" class="fullstripe-plan fullstripe-form-input input-xlarge" data-form-id="<?php echo $formNameAsIdentifier; ?>">
					<?php foreach ( $plans as $plan ): ?>
						<?php
						$currency_array = MM_WPFS::get_currency_for( $plan->currency );
						$setup_fee      = 0;
						if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
							$setup_fee = $plan->metadata->setup_fee;
						}
						?>
						<option value="<?php echo esc_attr( $plan->id ); ?>"
						        data-value="<?php echo esc_attr( $plan->id ); ?>"
						        data-amount="<?php echo MM_WPFS::format_amount( $plan->currency, $plan->amount ); ?>"
						        data-amount-in-smallest-common-currency="<?php echo $plan->amount; ?>"
						        data-interval="<?php echo esc_attr( MM_WPFS::get_translated_interval_label( $plan->interval, $plan->interval_count ) ); ?>"
						        data-interval-count="<?php echo esc_attr( $plan->interval_count ); ?>"
						        data-currency="<?php echo esc_attr( $plan->currency ); ?>"
						        data-zero-decimal-support="<?php echo( $currency_array['zeroDecimalSupport'] == true ? 'true' : 'false' ); ?>"
						        data-currency-symbol="<?php echo esc_attr( $currency_array['symbol'] ); ?>"
						        data-setup-fee="<?php echo MM_WPFS::format_amount( $plan->currency, $setup_fee ); ?>"
						        data-setup-fee-in-smallest-common-currency="<?php echo $setup_fee; ?>">
							<?php echo esc_html( MM_WPFS::translate_label( $plan->name ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php if ( $checkoutSubscriptionForm->showCustomInput == 1 ): ?>
			<?php
			$customInputs = array();
			if ( $checkoutSubscriptionForm->customInputs != null ) {
				$customInputs = explode( '{{', $checkoutSubscriptionForm->customInputs );
			}
			?>
			<?php if ( $checkoutSubscriptionForm->customInputs == null ): ?>
				<div class="control-group">
					<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $checkoutSubscriptionForm->customInputTitle ); ?></label>
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
		<?php if ( $checkoutSubscriptionForm->showCouponInput == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Coupon Code', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" class="input-medium fullstripe-form-input" name="fullstripe_coupon_input" id="<?php echo $couponInputId; ?>">
					<button id="<?php echo $paymentFormCouponSubmitId; ?>" class="payment-form-coupon" data-form-id="<?php echo $formNameAsIdentifier; ?>"><?php _e( 'Apply', 'wp-full-stripe' ); ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ); ?>" id="<?php echo $showLoadingCouponId; ?>" class="loading-animation"/>
				</div>
			</div>
		<?php endif; ?>
		<button id="<?php echo $paymentFormSubmitId; ?>" class="fullstripe_checkout_button <?php echo ( $checkoutSubscriptionForm->disableStyling == '0' ) ? 'stripe-button-el' : '' ?> " type="submit">
			<span class="fullstripe_checkout_button_text" <?php echo ( $checkoutSubscriptionForm->disableStyling == '0' ) ? 'style="display: block; min-height: 30px;"' : '' ?> ><?php MM_WPFS::echo_translated_label( $checkoutSubscriptionForm->openButtonTitle ); ?></span>
		</button>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="loading-animation" id="show-loading__<?php echo $formNameAsIdentifier; ?>" style="display: none;"/>
	</fieldset>
</form>
