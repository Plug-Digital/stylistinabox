<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.02.27.
 * Time: 16:39
 */
?>
<div id="createplan" class="wrap">
	<h2><?php esc_html_e( 'Create subscription plan', 'wp-full-stripe' ); ?></h2>
	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
	<form class="form-horizontal" action="" method="POST" id="create-subscription-plan">
		<p class="tips"></p>
		<input type="hidden" name="action" value="wp_full_stripe_create_plan"/>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'ID:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_id" id="sub_id">
					<p class="description"><?php esc_html_e( 'This ID is used to identify this plan when creating a subscription form and on your Stripe dashboard.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Display Name:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_name" id="sub_name">
					<p class="description"><?php esc_html_e( 'The name you wish to be displayed to customers for this plan.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="currency"><?php _e( "Payment Currency: ", 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<div class="ui-widget">
						<select id="currency" name="sub_currency">
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
					<label class="control-label"><?php esc_html_e( 'Payment Amount:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_amount" id="sub_amount"/>
					<p class="description"><?php esc_html_e( 'The amount this plan will charge your customer, in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Setup Fee:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_setup_fee" id="sub_setup_fee" value="0">
					<p class="description"><?php esc_html_e( 'Amount to charge the customer to setup the subscription, in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10. Entering 0 will disable.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Interval:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<select id="sub_interval" name="sub_interval">
						<option value="week"><?php esc_html_e( 'Weekly', 'wp-full-stripe' ); ?></option>
						<option value="month" selected="selected"><?php esc_html_e( 'Monthly', 'wp-full-stripe' ); ?></option>
						<option value="year"><?php esc_html_e( 'Yearly', 'wp-full-stripe' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'How often the payment amount is charged to the customer.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Interval Count:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_interval_count" id="sub_interval_count" value="1"/>
					<p class="description"><?php esc_html_e( 'You could specify an interval count of 3 and an interval of \'Monthly\' for quarterly billing (every 3 months). Default is 1 for Weekly/Monthly/Yearly.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Payment Cancellation Count:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_cancellation_count" id="sub_cancellation_count" value="0"/>
					<p class="description"><?php esc_html_e( 'You could specify the number of charges after which the subscription is cancelled. Set to 0 to let the subscription run forever.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Trial Period Days:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="sub_trial" id="sub_trial" value="0"/>
					<p class="description"><?php esc_html_e( 'How many trial days the customer has before being charged. Set to 0 to disable trial period.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Create Plan', 'wp-full-stripe' ); ?></button>
			<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wp-full-stripe' ); ?></a>
			<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
		</p>
	</form>
</div>
