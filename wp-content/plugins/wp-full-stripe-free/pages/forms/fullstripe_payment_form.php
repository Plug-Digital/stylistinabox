<?php

/** @var stdClass $paymentForm */

$formNameAsIdentifier = esc_attr( $paymentForm->name );

$htmlFormAttributes = 'id="payment-form"';
$htmlFormAttributes .= ' class="form-horizontal"';

?>
<form action="" method="POST" <?php echo $htmlFormAttributes; ?>>
	<fieldset>
		<div id="legend">
                <span class="fullstripe-form-title">
                    <?php MM_WPFSF::echo_translated_label( $paymentForm->formTitle ); ?>
                </span>
		</div>
		<input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
		<input type="hidden" name="formName" value="<?php echo $formNameAsIdentifier; ?>"/>
		<input type="hidden" name="formNonce" value="<?php echo wp_create_nonce( $paymentForm->paymentFormID ); ?>"/>
		<!-- Name -->
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe-free' ); ?></label>
			<div class="controls">
				<input type="text" placeholder="Name" class="input-xlarge fullstripe-form-input" name="fullstripe_name" id="fullstripe_name" data-stripe="name">
			</div>
		</div>
		<?php if ( $paymentForm->showEmailInput == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Email Address', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_email" id="fullstripe_email">
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $paymentForm->showCustomInput == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php MM_WPFSF::echo_translated_label( $paymentForm->customInputTitle ); ?></label>
				<div class="controls">
					<input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input" id="fullstripe_custom_input">
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $paymentForm->customAmount == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" placeholder="10.00" style="width: 60px;" name="fullstripe_custom_amount" id="fullstripe_custom_amount" class="fullstripe-form-input"><br/>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $paymentForm->showAddress == 1 ): ?>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Street', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_line1" id="fullstripe_address_line1" class="fullstripe-form-input"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Line 2', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_line2" id="fullstripe_address_line2" class="fullstripe-form-input"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'City', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" name="fullstripe_address_city" id="fullstripe_address_city" class="fullstripe-form-input"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'State', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" style="width: 60px;" name="fullstripe_address_state" id="fullstripe_address_state" class="fullstripe-form-input"><br/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label fullstripe-form-label"><?php _e( 'Zip', 'wp-full-stripe-free' ); ?></label>
				<div class="controls">
					<input type="text" style="width: 60px;" name="fullstripe_address_zip" id="fullstripe_address_zip" class="fullstripe-form-input"><br/>
				</div>
			</div>
		<?php endif; ?>
		<!-- Card Number -->
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Number', 'wp-full-stripe-free' ); ?></label>
			<div class="controls">
				<input type="text" autocomplete="off" placeholder="4242424242424242" class="input-xlarge fullstripe-form-input" size="20" data-stripe="number">
			</div>
		</div>
		<!-- Expiry-->
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card Expiry Date', 'wp-full-stripe-free' ); ?></label>
			<div class="controls">
				<input type="text" style="width: 60px;" size="2" placeholder="10" data-stripe="exp-month" class="fullstripe-form-input"/>
				<span> / </span>
				<input type="text" style="width: 60px;" size="4" placeholder="2016" data-stripe="exp-year" class="fullstripe-form-input"/>
			</div>
		</div>
		<!-- CVV -->
		<div class="control-group">
			<label class="control-label fullstripe-form-label"><?php _e( 'Card CVV', 'wp-full-stripe-free' ); ?></label>
			<div class="controls">
				<input type="password" autocomplete="off" placeholder="123" class="input-mini fullstripe-form-input" size="4" data-stripe="cvc"/>
			</div>
		</div>
		<!-- Submit -->
		<?php if ( $paymentForm->customAmount == 0 ): ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit"><?php MM_WPFSF::echo_translated_label( $paymentForm->buttonTitle ); ?><?php if ( $paymentForm->showButtonAmount == 1 ) {
							printf( ' %s%0.2f', $currencySymbol, $paymentForm->amount / 100.0 );
						} ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe-free' ); ?>" id="showLoading"/>
				</div>
			</div>
		<?php else: ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit"><?php MM_WPFSF::echo_translated_label( $paymentForm->buttonTitle ); ?></button>
					<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe-free' ); ?>" id="showLoading"/>
				</div>
			</div>
		<?php endif; ?>
	</fieldset>
</form>
