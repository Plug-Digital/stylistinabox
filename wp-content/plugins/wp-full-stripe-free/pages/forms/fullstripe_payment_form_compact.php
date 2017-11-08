<?php

/** @var stdClass $paymentForm */
/** @var string $creditCardImage */

$formNameAsIdentifier = esc_attr( $paymentForm->name );

$htmlFormAttributes = 'id="payment-form-style"';

?>
<h4><span class="fullstripe-form-title"><?php MM_WPFSF::echo_translated_label( $paymentForm->formTitle ); ?></span></h4>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
	<input type="hidden" name="formName" value="<?php echo $formNameAsIdentifier; ?>"/>
	<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $paymentForm->paymentFormID ); ?>"/>
	<?php if ( $paymentForm->showEmailInput == 1 ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Email Address', 'wp-full-stripe-free' ); ?></label>
			<input type="text" name="fullstripe_email" id="fullstripe_email">
		</div>
	<?php endif; ?>
	<?php if ( $paymentForm->showCustomInput == 1 ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php MM_WPFSF::echo_translated_label( $paymentForm->customInputTitle ); ?></label>
			<input type="text" name="fullstripe_custom_input" id="fullstripe_custom_input">
		</div>
	<?php endif; ?>
	<?php if ( $paymentForm->customAmount == 1 ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe-free' ); ?></label><br/>
			<input type="text" style="width: 100px;" name="fullstripe_custom_amount" id="fullstripe_custom_amount">
		</div>
	<?php endif; ?>

	<?php if ( $paymentForm->showAddress == 1 ): ?>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Street', 'wp-full-stripe-free' ); ?></label>
			<input type="text" name="fullstripe_address_line1" id="fullstripe_address_line1"><br/>
		</div>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Line 2', 'wp-full-stripe-free' ); ?></label>
			<input type="text" name="fullstripe_address_line2" id="fullstripe_address_line2"><br/>
		</div>
		<div class="_100">
			<label class="control-label fullstripe-form-label"><?php _e( 'City', 'wp-full-stripe-free' ); ?></label>
			<input type="text" name="fullstripe_address_city" id="fullstripe_address_city"><br/>
		</div>
		<div class="_50">
			<label class="control-label fullstripe-form-label"><?php _e( 'State', 'wp-full-stripe-free' ); ?></label><br/>
			<input type="text" name="fullstripe_address_state" id="fullstripe_address_state">
		</div>
		<div class="_50">
			<label class="control-label fullstripe-form-label"><?php _e( 'Zip', 'wp-full-stripe-free' ); ?></label><br/>
			<input type="text" name="fullstripe_address_zip" id="fullstripe_address_zip">
		</div>
		<div class="_100">
			<hr/>
		</div>
	<?php endif; ?>
	<div class="_100" style="padding-bottom: 5px;">
		<img src="<?php echo plugins_url( '../img/' . $creditCardImage, dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Credit Cards', 'wp-full-stripe-free' ); ?>"/>
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe-free' ); ?></label>
		<input type="text" name="fullstripe_name" id="fullstripe_name">
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card Number', 'wp-full-stripe-free' ); ?></label>
		<input type="text" autocomplete="off" size="20" data-stripe="number">
	</div>
	<div class="_50">
		<label class="control-label fullstripe-form-label"><?php _e( 'Card CVV', 'wp-full-stripe-free' ); ?></label><br/>
		<input type="password" autocomplete="off" size="4" data-stripe="cvc" style="width: 80px;"/>
	</div>
	<div class="_25">
		<label class="control-label fullstripe-form-label"><?php _e( 'Month', 'wp-full-stripe-free' ); ?></label>
		<select data-stripe="exp-month">
			<option value="01"><?php _e( 'January', 'wp-full-stripe-free' ); ?></option>
			<option value="02"><?php _e( 'February', 'wp-full-stripe-free' ); ?></option>
			<option value="03"><?php _e( 'March', 'wp-full-stripe-free' ); ?></option>
			<option value="04"><?php _e( 'April', 'wp-full-stripe-free' ); ?></option>
			<option value="05"><?php _e( 'May', 'wp-full-stripe-free' ); ?></option>
			<option value="06"><?php _e( 'June', 'wp-full-stripe-free' ); ?></option>
			<option value="07"><?php _e( 'July', 'wp-full-stripe-free' ); ?></option>
			<option value="08"><?php _e( 'August', 'wp-full-stripe-free' ); ?></option>
			<option value="09"><?php _e( 'September', 'wp-full-stripe-free' ); ?></option>
			<option value="10"><?php _e( 'October', 'wp-full-stripe-free' ); ?></option>
			<option value="11"><?php _e( 'November', 'wp-full-stripe-free' ); ?></option>
			<option value="12"><?php _e( 'December', 'wp-full-stripe-free' ); ?></option>
		</select>
	</div>
	<div class="_25">
		<label class="control-label fullstripe-form-label"><?php _e( 'Year', 'wp-full-stripe-free' ); ?></label>
		<select data-stripe="exp-year">
			<?php
			$startYear = date( 'Y' );
			$numYears  = 20;
			for ( $i = 0; $i < $numYears; $i ++ ) {
				$yr = $startYear + $i;
				echo "<option value='" . $yr . "'>" . $yr . "</option>";
			}
			?>
		</select>
	</div>
	<div class="_100">
		<br/>
	</div>
	<div class="_100">
		<?php if ( $paymentForm->customAmount == 0 ): ?>
			<button type="submit"><?php echo $paymentForm->buttonTitle; ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
					printf( ' %s%0.2f', $currencySymbol, $paymentForm->amount / 100.0 );
				} ?></button>
		<?php else: ?>
			<button type="submit"><?php MM_WPFSF::echo_translated_label( $paymentForm->buttonTitle ); ?></button>
		<?php endif; ?>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe-free' ); ?>" id="showLoading"/>
	</div>
</form>