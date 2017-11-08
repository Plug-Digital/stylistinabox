<?php

/** @var stdClass $checkoutForm */

$currencyArray                    = MM_WPFS::get_currency_for( $checkoutForm->currency );
$formNameAsIdentifier             = esc_attr( $checkoutForm->name );
$customAmountInputId              = 'fullstripe-custom-amount__' . $formNameAsIdentifier;
$listOfAmountsCustomAmountInputId = 'fullstripe-list-of-amounts-custom-amount__' . $formNameAsIdentifier;
$htmlFormAttributes               = 'class="checkout-form wpfs-checkout-form form-horizontal"';
$htmlFormAttributes .= ' id="checkout-form__' . $formNameAsIdentifier . '"';
$htmlFormAttributes .= ' data-form-id="' . esc_attr( $checkoutForm->name ) . '"';
$htmlFormAttributes .= ' data-currency="' . esc_attr( $checkoutForm->currency ) . '"';
$htmlFormAttributes .= ' data-zero-decimal-support="' . esc_attr( $currencyArray['zeroDecimalSupport'] === true ? 1 : 0 ) . '"';
$htmlFormAttributes .= ' data-company-name="' . esc_attr( $checkoutForm->companyName ) . '"';
$htmlFormAttributes .= ' data-product-description="' . MM_WPFS::translate_label( $checkoutForm->productDesc ) . '"';
$htmlFormAttributes .= ' data-amount-type="' . esc_attr( $checkoutForm->customAmount ) . '"';
if ( $checkoutForm->customAmount == 'list_of_amounts' ) {
	$htmlFormAttributes .= ' data-allow-list-of-amounts-custom="' . esc_attr( $checkoutForm->allowListOfAmountsCustom ) . '"';
} elseif ( $checkoutForm->customAmount == 'specified_amount' ) {
	$htmlFormAttributes .= ' data-amount="' . esc_attr( $checkoutForm->amount ) . '"';
}
$htmlFormAttributes .= ' data-use-bitcoin="' . esc_attr( $checkoutForm->useBitcoin ) . '"';
$htmlFormAttributes .= ' data-use-alipay="' . esc_attr( $checkoutForm->useAlipay ) . '"';
$htmlFormAttributes .= ' data-button-title="' . MM_WPFS::translate_label( $checkoutForm->buttonTitle ) . '"';
$htmlFormAttributes .= ' data-show-billing-address="' . esc_attr( $checkoutForm->showBillingAddress ) . '"';
$htmlFormAttributes .= ' data-show-remember-me="' . esc_attr( $checkoutForm->showRememberMe ) . '"';
if ( ! is_null( $checkoutForm->image ) && trim( $checkoutForm->image ) !== '' ) {
	$htmlFormAttributes .= ' data-image="' . esc_attr( $checkoutForm->image ) . '"';
}
if ( $checkoutForm->showCustomInput == 1 && $checkoutForm->customInputs ) {
	$htmlFormAttributes .= ' data-custom-input-title="' . esc_attr( $checkoutForm->customInputTitle ) . '"';
	$htmlFormAttributes .= ' data-custom-inputs="' . esc_attr( $checkoutForm->customInputs ) . '"';
	$htmlFormAttributes .= ' data-custom-input-required="' . esc_attr( $checkoutForm->customInputRequired ) . '"';
}

$firstAmount = null;

?>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<?php if ( $checkoutForm->showCustomInput == 1 || $checkoutForm->customAmount == 'list_of_amounts' || $checkoutForm->customAmount == 'custom_amount' ): ?>
	<fieldset><?php endif; ?>
		<input type="hidden" name="action" value="fullstripe_checkout_form_charge"/>
		<input type="hidden" name="formName" value="<?php echo esc_attr( $checkoutForm->name ); ?>"/>
		<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $checkoutForm->checkoutFormID ); ?>"/>
		<?php if ( $checkoutForm->showCustomInput == 1 ): ?>
			<?php
			$customInputs = array();
			if ( $checkoutForm->customInputs != null ) {
				$customInputs = explode( '{{', $checkoutForm->customInputs );
			}
			?>
			<?php if ( $checkoutForm->customInputs == null ): ?>
				<div class="control-group">
					<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $checkoutForm->customInputTitle ); ?></label>
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
		<?php if ( $checkoutForm->customAmount == 'custom_amount' ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" class="fullstripe-form-input fullstripe-custom-amount input-small" data-currency="<?php echo esc_attr( $checkoutForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo esc_attr( $currencyArray['symbol'] ); ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>"><br/>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $checkoutForm->customAmount == 'list_of_amounts' ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<select name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" class="fullstripe-form-input fullstripe-custom-amount input-xlarge" data-currency="<?php echo esc_attr( $checkoutForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
						<?php
						$listOfAmounts = json_decode( $checkoutForm->listOfAmounts );
						foreach ( $listOfAmounts as $index => $listElement ) {
							$amount           = $listElement[0];
							$description      = $listElement[1];
							$amountLabel      = MM_WPFS::format_amount_with_currency( $checkoutForm->currency, $amount );
							$descriptionLabel = MM_WPFS::translate_label( $description );
							if ( strpos( $description, '{amount}' ) !== false ) {
								$descriptionLabel = str_replace( '{amount}', $amountLabel, $descriptionLabel );
							}
							if ( is_null( $firstAmount ) ) {
								$firstAmount = $amount;
							}
							$optionRow = '<option';
							$optionRow .= ' value="' . MM_WPFS::format_amount( $checkoutForm->currency, $amount ) . '"';
							$optionRow .= " data-amount-index=\"$index\"";
							$optionRow .= '>';
							$optionRow .= $descriptionLabel;
							$optionRow .= "</option>";
							echo $optionRow;
						}
						if ( $checkoutForm->allowListOfAmountsCustom == '1' ) {
							echo '<option value="other">' . __( 'Other', 'wp-full-stripe' ) . '</option>';
						}
						?>
					</select>
					<?php if ( $checkoutForm->allowListOfAmountsCustom == '1' ): ?>
						<input type="text" name="fullstripe_list_of_amounts_custom_amount" id="<?php echo $listOfAmountsCustomAmountInputId; ?>" style="display: none;" class="input-small fullstripe-form-input fullstripe-list-of-amounts-custom-amount" data-currency="<?php echo esc_attr( $checkoutForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<button class="fullstripe_checkout_button <?php echo ( $checkoutForm->disableStyling == '0' ) ? 'stripe-button-el' : '' ?> " type="submit">
			<span class="fullstripe_checkout_button_text" <?php echo ( $checkoutForm->disableStyling == '0' ) ? 'style="display: block; min-height: 30px;"' : '' ?> ><?php MM_WPFS::echo_translated_label( $checkoutForm->openButtonTitle ); ?></span>
		</button>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="loading-animation" id="show-loading__<?php echo $formNameAsIdentifier; ?>" style="display: none;"/>
		<?php if ( $checkoutForm->showCustomInput == 1 || $checkoutForm->customAmount == 'list_of_amounts' || $checkoutForm->customAmount == 'custom_amount' ): ?>
	</fieldset><?php endif; ?>
</form>

