<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.02.23.
 * Time: 14:57
 */
?>
<h2 id="create-checkout-form-tabs" class="nav-tab-wrapper wpfs-admin-form-tabs">
	<a href="#create-checkout-form-tab-payment" class="nav-tab"><?php esc_html_e( 'Payment', 'wp-full-stripe' ); ?></a>
	<a href="#create-checkout-form-tab-appearance" class="nav-tab"><?php esc_html_e( 'Appearance', 'wp-full-stripe' ); ?></a>
	<a href="#create-checkout-form-tab-custom-fields" class="nav-tab"><?php esc_html_e( 'Custom Fields', 'wp-full-stripe' ); ?></a>
	<a href="#create-checkout-form-tab-actions-after-payment" class="nav-tab"><?php esc_html_e( 'Actions after payment', 'wp-full-stripe' ); ?></a>
</h2>
<form class="form-horizontal wpfs-admin-form" action="" method="POST" id="create-checkout-form">
	<p class="tips"></p>
	<input type="hidden" name="action" value="wp_full_stripe_create_checkout_form">
	<div id="create-checkout-form-tab-payment" class="wpfs-tab-content">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Form Type:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td><?php esc_html_e( 'Popup payment form', 'wp-full-stripe' ); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Form Name:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="form_name" id="form_name" maxlength="<?php echo $form_data::NAME_LENGTH; ?>">

					<p class="description"><?php esc_html_e( 'This name will be used to identify this form in the shortcode i.e. [fullstripe_checkout form="FormName"].', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="currency"><?php esc_html_e( "Payment Currency: ", 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<div class="ui-widget">
						<select id="currency" name="form_currency">
							<option value=""><?php esc_attr_e( 'Select from the list or start typing', 'wp-full-stripe' ); ?></option>
							<?php
							foreach ( MM_WPFS::get_available_currencies() as $currency_key => $currency_obj ) {
								$currency_array = MM_WPFS::get_currency_for( $currency_key );
								$option         = '<option value="' . $currency_key . '"';
								$option .= ' data-currency-symbol="' . $currency_array['symbol'] . '"';
								$option .= ' data-zero-decimal-support="' . ( $currency_array['zeroDecimalSupport'] == true ? 'true' : 'false' ) . '"';
								if ( "usd" === $currency_key ) {
									$option .= ' selected="selected"';
								}
								$option .= '>';
								$option .= $currency_obj['name'] . ' (' . $currency_obj['code'] . ')';
								$option .= '</option>';
								echo $option;
							}
							?>
						</select>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Type: ', 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<label class="radio inline">
						<input type="radio" name="form_custom" id="set_specific_amount" value="specified_amount" checked="checked">
						<?php esc_html_e( 'Set Amount', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio inline">
						<input type="radio" name="form_custom" id="set_amount_list" value="list_of_amounts">
						<?php esc_html_e( 'Select Amount from List', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio inline">
						<input type="radio" name="form_custom" id="set_custom_amount" value="custom_amount">
						<?php esc_html_e( 'Custom Amount', 'wp-full-stripe' ); ?>
					</label>

					<p class="description"><?php esc_html_e( 'Choose to set a specific amount or a list of amounts for this form, or allow customers to set custom amounts.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top" id="payment_amount_row">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Amount: ', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="form_amount" id="form_amount"/>

					<p class="description"><?php esc_html_e( 'The amount this form will charge your customer, in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top" id="payment_amount_list_row" style="display: none;">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Amount Options:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<a href="#" class="button button-primary" id="add_payment_amount_button"><?php esc_html_e( 'Add', 'wp-full-stripe' ); ?></a>
					<input type="text" id="payment_amount_value" placeholder="<?php esc_attr_e( 'Amount', 'wp-full-stripe' ); ?>" maxlength="<?php echo $form_data::PAYMENT_AMOUNT_LENGTH; ?>"><input type="text" id="payment_amount_description" placeholder="<?php esc_attr_e( 'Description', 'wp-full-stripe' ); ?>" maxlength="<?php echo $form_data::PAYMENT_AMOUNT_DESCRIPTION_LENGTH; ?>"><br>
					<ul id="payment_amount_list"></ul>
					<input type="hidden" name="payment_amount_values">
					<input type="hidden" name="payment_amount_descriptions">

					<p class="description"><?php esc_html_e( 'The amount in smallest common currency unit. i.e. for $10.00 enter 1000, for ¥10 enter 10. The description will be displayed in the dropdown for the amount. Use the {amount} placeholder to include the amount value. You can use drag\'n\'drop to reorder the payment amounts.', 'wp-full-stripe' ); ?></p>
					<label class="checkbox inline"><input type="checkbox" name="allow_custom_payment_amount" id="allow_custom_payment_amount" value="1"><?php esc_html_e( 'Allow Custom Amount to Be Entered?', 'wp-full-stripe' ); ?>
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Use Bitcoin?', 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<span id="bitcoin_usage_info_panel" style="display: none;">
						<p class="alert alert-info"><?php printf( __( "In order to use Bitcoin for payments, you have to set the form currency to USD, and you have to link an US bank account to your Stripe account, then <a href=\"%s\">enable Bitcoin</a> on your Stripe account.", "wp-full-stripe" ), admin_url( "admin.php?page=fullstripe-settings" ), "https://dashboard.stripe.com/account/bitcoin/enable" ); ?></p>
					</span>
					<span id="bitcoin_usage_panel">
						<label class="radio inline">
							<input type="radio" name="form_use_bitcoin" id="use_bitcoin_no" value="0" checked="checked">
							<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
						</label>
						<label class="radio inline">
							<input type="radio" name="form_use_bitcoin" id="use_bitcoin_yes" value="1">
							<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Allow to use Bitcoin for payments.', 'wp-full-stripe' ); ?></p>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Use Alipay?', 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<div id="alipay_usage_info_panel" style="display: none;">
						<p class="alert alert-info"><?php esc_html_e( "In order to use AliPay for payments, you have to set the form currency to USD, and you have to link an US bank account to your Stripe account.", "wp-full-stripe" ); ?></p>
					</div>
					<div id="alipay_usage_panel">
						<label class="radio inline">
							<input type="radio" name="form_use_alipay" id="use_alipay_no" value="0" checked="checked">
							<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
						</label>
						<label class="radio inline">
							<input type="radio" name="form_use_alipay" id="use_alipay_yes" value="1">
							<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Accept payments from hundreds of millions of new customers using Alipay, China’s most popular payment method.', 'wp-full-stripe' ); ?></p>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div id="create-checkout-form-tab-appearance" class="wpfs-tab-content">
		<?php include( 'create_checkout_form_tab_appearance.php' ); ?>
	</div>
	<div id="create-checkout-form-tab-custom-fields" class="wpfs-tab-content">
		<?php include( 'create_payment_form_tab_custom_fields.php' ); ?>
	</div>
	<div id="create-checkout-form-tab-actions-after-payment" class="wpfs-tab-content">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Send Email Receipt?', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<label class="radio inline">
						<input type="radio" name="form_send_email_receipt" value="0" checked="checked">
						<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio inline">
						<input type="radio" name="form_send_email_receipt" value="1">
						<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
					</label>

					<p class="description"><?php esc_html_e( 'Send an email receipt on successful payment?', 'wp-full-stripe' ); ?> </p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Redirect On Success?', 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<label class="radio inline">
						<input type="radio" name="form_do_redirect" id="do_redirect_no" value="0" checked="checked">
						<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio inline">
						<input type="radio" name="form_do_redirect" id="do_redirect_yes" value="1">
						<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
					</label>

					<p class="description"><?php esc_html_e( 'When payment is successful you can choose to redirect to another page or post.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<?php include( 'redirect_to_for_create.php' ); ?>
		</table>
	</div>
	<p class="submit">
		<button class="button button-primary" type="submit"><?php esc_html_e( 'Create Form', 'wp-full-stripe' ); ?></button>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wp-full-stripe' ); ?></a>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
	</p>
</form>
