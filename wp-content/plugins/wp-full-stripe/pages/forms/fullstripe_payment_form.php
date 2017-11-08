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
$wpfsFormCount        = MM_WPFS::get_rendered_forms()->get_total();

$showLoadingId                    = 'show-loading__' . $formNameAsIdentifier;
$customAmountInputId              = 'fullstripe-custom-amount__' . $formNameAsIdentifier;
$listOfAmountsCustomAmountInputId = 'fullstripe-list-of-amounts-custom-amount__' . $formNameAsIdentifier;
$paymentFormSubmitId              = 'payment-form-submit' . '__' . $formNameAsIdentifier;
$addressCountryInputId            = 'fullstripe-address-country__' . $formNameAsIdentifier;
$htmlFormAttributes               = 'class="payment-form wpfs-payment-form form-horizontal"';
if ( $wpfsFormCount == 1 ) {
	$htmlFormAttributes .= ' id="payment-form"';
} else {
	$htmlFormAttributes .= ' id="payment-form__' . $formNameAsIdentifier . '"';
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

?>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<fieldset>
		<div id="legend__<?php echo $formNameAsIdentifier; ?>">
			<span class="fullstripe-form-title"><?php MM_WPFS::echo_translated_label( $paymentForm->formTitle ); ?></span>
		</div>
		<input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
		<input type="hidden" name="formName" value="<?php echo esc_attr( $paymentForm->name ); ?>"/>
		<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $paymentForm->paymentFormID ); ?>"/>
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe' ); ?></label>
			<div class="controls">
				<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_name" id="fullstripe_name__<?php echo $formNameAsIdentifier; ?>" data-stripe="name">
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
		<?php if ( $paymentForm->showCustomInput == 1 ): ?>
			<?php
			$customInputs = array();
			if ( $paymentForm->customInputs != null ) {
				$customInputs = explode( '{{', $paymentForm->customInputs );
			}
			?>
			<?php if ( $paymentForm->customInputs == null ): ?>
				<div class="control-group">
					<label class="control-label fullstripe-form-label"><?php MM_WPFS::echo_translated_label( $paymentForm->customInputTitle ); ?></label>
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
		<?php if ( $paymentForm->customAmount == 'custom_amount' ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" class="fullstripe-form-input fullstripe-custom-amount input-small" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo esc_attr( $currencyArray['symbol'] ); ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>"><br/>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $paymentForm->customAmount == 'list_of_amounts' ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe' ); ?></label>
				<div class="controls">
					<select name="fullstripe_custom_amount" id="<?php echo $customAmountInputId; ?>" class="fullstripe-form-input fullstripe-custom-amount input-xlarge" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
						<?php
						$list_of_amounts = json_decode( $paymentForm->listOfAmounts );
						foreach ( $list_of_amounts as $index => $list_element ) {
							$amount            = $list_element[0];
							$description       = $list_element[1];
							$amount_label      = MM_WPFS::format_amount_with_currency( $paymentForm->currency, $amount );
							$description_label = MM_WPFS::translate_label( $description );
							if ( strpos( $description, '{amount}' ) !== false ) {
								$description_label = str_replace( '{amount}', $amount_label, $description_label );
							}
							if ( is_null( $firstAmount ) ) {
								$firstAmount = $amount;
							}
							$option_row = '<option';
							$option_row .= ' value="' . MM_WPFS::format_amount( $paymentForm->currency, $amount ) . '"';
							$option_row .= " data-amount-index=\"$index\"";
							$option_row .= '>';
							$option_row .= $description_label;
							$option_row .= "</option>";
							echo $option_row;
						}
						if ( $paymentForm->allowListOfAmountsCustom == '1' ) {
							echo '<option value="other">' . __( 'Other', 'wp-full-stripe' ) . '</option>';
						}
						?>
					</select>
					<?php if ( $paymentForm->allowListOfAmountsCustom == '1' ): ?>
						<input type="text" name="fullstripe_list_of_amounts_custom_amount" id="<?php echo $listOfAmountsCustomAmountInputId; ?>" style="display: none;" class="input-small fullstripe-form-input fullstripe-list-of-amounts-custom-amount" data-button-title="<?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?>" data-show-amount="<?php echo $paymentForm->showButtonAmount; ?>" data-currency="<?php echo esc_attr( $paymentForm->currency ); ?>" data-zero-decimal-support="<?php echo $currencyArray['zeroDecimalSupport'] ? 'true' : 'false' ?>" data-currency-symbol="<?php echo $currencyArray['symbol']; ?>" data-form-id="<?php echo $formNameAsIdentifier; ?>">
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $paymentForm->showAddress == 1 ): ?>
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
					<select name="fullstripe_address_country" id="<?php echo $addressCountryInputId; ?>" class="fullstripe-form-input fullstripe-address-country input-xlarge">
						<option value=""><?php echo esc_html( __( 'Select country', 'wp-full-stripe' ) ); ?></option>
						<?php
						foreach ( MM_WPFS::get_available_countries() as $country_key => $country_obj ) {
							$option = '<option value="' . $country_key . '"';
							$option .= '>';
							$option .= MM_WPFS::translate_label( $country_obj['name'] );
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
		<?php if ( $paymentForm->customAmount == 'specified_amount' ): ?>
			<div class="control-group">
				<div class="controls">
					<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
							echo ' ' . MM_WPFS::format_amount_with_currency( $paymentForm->currency, $paymentForm->amount );
						} ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ) ?>" id="<?php echo $showLoadingId; ?>" class="loading-animation"/>
				</div>
			</div>
		<?php elseif ( $paymentForm->customAmount == 'list_of_amounts' ): ?>
			<div class="control-group">
				<div class="controls">
					<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
							echo ' ' . MM_WPFS::format_amount_with_currency( $paymentForm->currency, $firstAmount );
						} ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ) ?>" id="<?php echo $showLoadingId; ?>" class="loading-animation"/>
				</div>
			</div>
		<?php else: ?>
			<div class="control-group">
				<div class="controls">
					<button id="<?php echo $paymentFormSubmitId; ?>" type="submit"><?php MM_WPFS::echo_translated_label( $paymentForm->buttonTitle ); ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe' ) ?>" id="<?php echo $showLoadingId; ?>" class="loading-animation"/>
				</div>
			</div>
		<?php endif; ?>
	</fieldset>
</form>
