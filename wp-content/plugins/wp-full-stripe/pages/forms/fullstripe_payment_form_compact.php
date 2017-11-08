<?php

/** @var stdClass $paymentForm */

$options   = get_option( 'fullstripe_options' );
$lockEmail = $options['lock_email_field_for_logged_in_users'];

$emailAddress   = "";
$isUserLoggedIn = is_user_logged_in();
if ( $lockEmail == '1' && $isUserLoggedIn ) {
	$currentUser  = wp_get_current_user();
	$emailAddress = $currentUser->user_email;
}

$firstAmount = null;

$formNameAsIdentifier = esc_attr( $paymentForm->name );

$wpfsFormCount = MM_WPFS::get_rendered_forms()->get_total();

$showLoadingId                    = 'show-loading' . '__' . $formNameAsIdentifier;
$paymentFormSubmitId              = 'payment-form-submit' . '__' . $formNameAsIdentifier;
$customAmountInputId              = 'fullstripe-custom-amount__' . $formNameAsIdentifier;
$listOfAmountsCustomAmountInputId = 'fullstripe-list-of-amounts-custom-amount__' . $formNameAsIdentifier;
$addressCountryInputId            = 'fullstripe-address-country__' . $formNameAsIdentifier;

$htmlFormAttributes = 'class="payment-form-compact wpfs-payment-form-compact"';
if ( $wpfsFormCount == 1 ) {
	$htmlFormAttributes .= ' id="payment-form-style"';
} else {
	$htmlFormAttributes .= ' id="payment-form-compact__' . $formNameAsIdentifier . '"';
}
$htmlFormAttributes .= ' data-form-id="' . $formNameAsIdentifier . '"';
$htmlFormAttributes .= ' data-amount-type="' . esc_attr( $paymentForm->customAmount ) . '"';
if ( $paymentForm->customAmount == 'list_of_amounts' ) {
	$htmlFormAttributes .= ' data-allow-list-of-amounts-custom="' . esc_attr( $paymentForm->allowListOfAmountsCustom ) . '"';
} elseif ( $paymentForm->customAmount == 'specified_amount' ) {
	$htmlFormAttributes .= ' data-amount="' . esc_attr( $paymentForm->amount ) . '"';
}
$htmlFormAttributes .= ' data-show-address="' . esc_attr( $paymentForm->showAddress ) . '"';

if ( $paymentForm->showCustomInput == 1 && $paymentForm->customInputs ) {
	$htmlFormAttributes .= ' data-custom-input-title="' . esc_attr( $paymentForm->customInputTitle ) . '"';
	$htmlFormAttributes .= ' data-custom-inputs="' . esc_attr( $paymentForm->customInputs ) . '"';
	$htmlFormAttributes .= ' data-custom-input-required="' . esc_attr( $paymentForm->customInputRequired ) . '"';
}

$currencyArray = MM_WPFS::get_currency_for( $paymentForm->currency );

$credit_card_image = MM_WPFS::get_credit_card_image_for( $paymentForm->currency );

?>
<h4><span class="fullstripe-form-title"><?php MM_WPFS::echo_translated_label( $paymentForm->formTitle ); ?></span></h4>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
	<input type="hidden" name="formName" value="<?php echo esc_attr( $paymentForm->name ); ?>"/>
	<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $paymentForm->paymentFormID ); ?>"/>
	<div class="_100">
		<label class="control-label fullstripe-form-label"><?php _e( 'Email Address', 'wp-full-stripe' ); ?></label>
		<?php if ( $lockEmail == '1' && $isUserLoggedIn ): ?>
			<br>
			<label class="fullstripe-data-label"><?php echo $emailAddress; ?></label>
			<input type="hidden" value="<?php echo $emailAddress; ?>" name="fullstripe_email" id="fullstripe_email__<?php echo $formNameAsIdentifier; ?>">
		<?php else: ?>
			<input type="text" name="fullstripe_email" id="fullstripe_email__<?php echo $formNameAsIdentifier; ?>">
		<?php endif; ?>
	</div>
	<?php if ( $paymentForm->showCustomInput == 1 ): ?>
		<?php
		$customInputs = array();
		if ( $paymentForm->customInputs != null ) {
			$customInputs = explode( '{{', $paymentForm->customInputs );
		}
		?>
		<?php if ( $paymentForm->customInputs == null ): ?>
			<div class="_100">
				<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $paymentForm->customInputTitle ); ?></label>
				<input type="text" name="fullstripe_custom_input" id="fullstripe_custom_input__<?php echo $formNameAsIdentifier; ?>">
			</div>
		<?php endif; ?>

		<?php foreach ( $customInputs as $i => $label ): ?>
			<div class="_100">
				<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $label ); ?></label>
				<input type="text" name="fullstripe_custom_input[]" id="fullstripe-custom-input__<?php echo $formNameAsIdentifier . '__' . ( $i + 1 ); ?>">
			</div>
		<?php endforeach; ?>

	<?php endif; ?>
	<?php if ( $paymentForm->customAmount == 'custom_amount' ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
			<input class="fullstripe-custom-amount" type="text" name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
		</div>
	<?php endif; ?>
	<?php if ( $paymentForm->customAmount == 'list_of_amounts' ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
			<select class="fullstripe-custom-amount" name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
				<?php
				$listOfAmounts = json_decode( $paymentForm->listOfAmounts );
				$firstAmount   = null;
				foreach ( $listOfAmounts as $index => $listElement ) {
					$amount           = $listElement[0];
					$description      = $listElement[1];
					$amountLabel      = MM_WPFS::format_amount_with_currency( $paymentForm->currency, $amount );
					$descriptionLabel = MM_WPFS::translate_label( $description );
					if ( strpos( $description, '{amount}' ) !== false ) {
						$descriptionLabel = str_replace( '{amount}', $amountLabel, $descriptionLabel );
					}
					if ( is_null( $firstAmount ) ) {
						$firstAmount = $amount;
					}
					$optionRow = '<option';
					$optionRow .= ' value="' . MM_WPFS::format_amount( $paymentForm->currency, $amount ) . '"';
					$optionRow .= " data-amount-index=\"$index\"";
					$optionRow .= '>';
					$optionRow .= $descriptionLabel;
					$optionRow .= "</option>";
					echo $optionRow;
				}
				if ( $paymentForm->allowListOfAmountsCustom == '1' ) {
					echo '<option value="other">' . __( 'Other', 'wp-full-stripe' ) . '</option>';
				}
				?>
			</select>
			<?php if ( $paymentForm->allowListOfAmountsCustom == '1' ): ?>
				<input type="text" name="fullstripe_list_of_amounts_custom_amount" id="<?php echo $listOfAmountsCustomAmountInputId; ?>" style="display: none;" class="fullstripe-list-of-amounts-custom-amount" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ( $paymentForm->showAddress == 1 ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Street', 'wp-full-stripe' ); ?></label>
			<input type="text" name="fullstripe_address_line1" id="fullstripe_address_line1__<?php echo $formNameAsIdentifier; ?>">
		</div>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Line 2', 'wp-full-stripe' ); ?></label>
			<input type="text" name="fullstripe_address_line2" id="fullstripe_address_line2__<?php echo $formNameAsIdentifier; ?>">
		</div>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'City', 'wp-full-stripe' ); ?></label>
			<input type="text" name="fullstripe_address_city" id="fullstripe_address_city__<?php echo $formNameAsIdentifier; ?>">
		</div>
		<div class="_50">
			<label class="control-label fullstripe-form-label"><?php _e( 'Zip', 'wp-full-stripe' ); ?></label>
			<input type="text" name="fullstripe_address_zip" id="fullstripe_address_zip__<?php echo $formNameAsIdentifier; ?>">
		</div>
		<div class="_50">
			<label class="control-label fullstripe-form-label"><?php _e( 'State', 'wp-full-stripe' ); ?></label>
			<input type="text" name="fullstripe_address_state" id="fullstripe_address_state__<?php echo $formNameAsIdentifier; ?>">
		</div>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Country', 'wp-full-stripe' ); ?></label>
			<select name="fullstripe_address_country" id="<?php echo $addressCountryInputId; ?>">
				<option value=""><?php echo esc_html( __( 'Select country' ) ); ?></option>
				<?php
				foreach ( MM_WPFS::get_available_countries() as $countryKey => $countryObject ) {
					$option = '<option value="' . $countryKey . '"';
					$option .= '>';
					$option .= MM_WPFS::translate_label( $countryObject['name'] );
					$option .= '</option>';
					echo $option;
				}
				?>
			</select>
		</div>
	<?php endif; ?>
	<div class="_100" style="padding-bottom: 5px;">
		<img src="<?php echo plugins_url( '../img/' . $credit_card_image, dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Credit Cards', 'wp-full-stripe' ); ?>"/>
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe' ); ?></label>
		<input type="text" name="fullstripe_name" id="fullstripe_name__<?php echo $formNameAsIdentifier; ?>" data-stripe="name">
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card Number', 'wp-full-stripe' ); ?></label>
		<input type="text" autocomplete="off" size="20" data-stripe="number">
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card CVV', 'wp-full-stripe' ); ?></label>
		<input type="password" autocomplete="off" size="4" maxlength="4" data-stripe="cvc"/>
	</div>
	<div class="_25">
		<label class="control-label fullstripe-form-label"><?php _e( 'Month', 'wp-full-stripe' ); ?></label>
		<select data-stripe="exp-month">
			<option value="01"><?php _e( 'January', 'wp-full-stripe' ); ?></option>
			<option value="02"><?php _e( 'February', 'wp-full-stripe' ); ?></option>
			<option value="03"><?php _e( 'March', 'wp-full-stripe' ); ?></option>
			<option value="04"><?php _e( 'April', 'wp-full-stripe' ); ?></option>
			<option value="05"><?php _e( 'May', 'wp-full-stripe' ); ?></option>
			<option value="06"><?php _e( 'June', 'wp-full-stripe' ); ?></option>
			<option value="07"><?php _e( 'July', 'wp-full-stripe' ); ?></option>
			<option value="08"><?php _e( 'August', 'wp-full-stripe' ); ?></option>
			<option value="09"><?php _e( 'September', 'wp-full-stripe' ); ?></option>
			<option value="10"><?php _e( 'October', 'wp-full-stripe' ); ?></option>
			<option value="11"><?php _e( 'November', 'wp-full-stripe' ); ?></option>
			<option value="12"><?php _e( 'December', 'wp-full-stripe' ); ?></option>
		</select>
	</div>
	<div class="_25">
		<label class="control-label fullstripe-form-label"><?php _e( 'Year', 'wp-full-stripe' ); ?></label>
		<select data-stripe="exp-year">
			<?php
			$startYear     = date( 'Y' );
			$numberOfYears = 20;
			for ( $i = 0; $i < $numberOfYears; $i ++ ) {
				$aYear = $startYear + $i;
				echo "<option value='" . $aYear . "'>" . $aYear . "</option>";
			}
			?>
		</select>
	</div>
	<div class="_100">
		<br/>
	</div>
	<div class="_100">
		<?php if ( $paymentForm->customAmount == 'specified_amount' ): ?>
			<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
					echo ' ' . MM_WPFS::format_amount_with_currency( $paymentForm->currency, $paymentForm->amount );
				} ?></button>
		<?php elseif ( $paymentForm->customAmount == 'list_of_amounts' ): ?>
			<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
					echo ' ' . MM_WPFS::format_amount_with_currency( $paymentForm->currency, $firstAmount );
				} ?></button>
		<?php else: ?>
			<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?></button>
		<?php endif; ?>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ); ?>" id="<?php echo $showLoadingId; ?>" class="loading-animation"/>
	</div>
</form>