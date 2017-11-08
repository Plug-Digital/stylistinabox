<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.03.02.
 * Time: 16:48
 */
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Form Type:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<?php esc_html_e( 'Inline payment form', 'wp-full-stripe' ); ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Form Name:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_name" id="form_name" value="<?php echo $form->name; ?>" maxlength="<?php echo $form_data::NAME_LENGTH; ?>">

			<p class="description"><?php esc_html_e( 'This name will be used to identify this form in the shortcode i.e. [fullstripe_payment form="FormName"].', 'wp-full-stripe' ); ?></p>
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
						if ( $form->currency === $currency_key ) {
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
			<label class="control-label"><?php esc_html_e( 'Payment Type:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_custom" id="set_specific_amount" value="specified_amount" <?php echo ( $form->customAmount == 'specified_amount' ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'Set Amount', 'wp-full-stripe' ); ?> </label>
			<label class="radio inline">
				<input type="radio" name="form_custom" id="set_amount_list" value="list_of_amounts" <?php echo ( $form->customAmount == 'list_of_amounts' ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'Select Amount from List', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_custom" id="set_custom_amount" value="custom_amount" <?php echo ( $form->customAmount == 'custom_amount' ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'Custom Amount', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'Choose to set a specific amount or a list of amounts for this form, or allow customers to set custom amounts.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top" id="payment_amount_row" <?php echo $form->customAmount == 'list_of_amounts' || $form->customAmount == 'custom_amount' ? 'style="display: none;"' : '' ?>>
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Payment Amount:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_amount" id="form_amount" value="<?php echo $form->amount; ?>">

			<p class="description"><?php esc_html_e( 'The amount this form will charge your customer, in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top" id="payment_amount_list_row" <?php echo $form->customAmount != 'list_of_amounts' ? 'style="display: none;"' : '' ?>>
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Payment Amount Options:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<a href="#" class="button button-primary" id="add_payment_amount_button"><?php esc_html_e( 'Add', 'wp-full-stripe' ); ?></a><input type="text" id="payment_amount_value" placeholder="<?php esc_attr_e( 'Amount', 'wp-full-stripe' ); ?>" maxlength="<?php echo $form_data::PAYMENT_AMOUNT_LENGTH; ?>"><input type="text" id="payment_amount_description" placeholder="<?php esc_attr_e( 'Description', 'wp-full-stripe' ); ?>" maxlength="<?php echo $form_data::PAYMENT_AMOUNT_DESCRIPTION_LENGTH; ?>" class="large-text"><br>

			<ul id="payment_amount_list">
				<?php
				$list_of_amounts = json_decode( $form->listOfAmounts );
				if ( isset( $list_of_amounts ) && ! empty( $list_of_amounts ) ) {
					foreach ( $list_of_amounts as $list_element ) {
						$list_item_row = "<li";
						$list_item_row .= " class=\"ui-state-default\"";
						$list_item_row .= " title=\"" . __( 'You can reorder this list by using drag\'n\'drop.', 'wp-full-stripe' ) . "\"";
						$list_item_row .= " data-toggle=\"tooltip\"";
						$list_item_row .= " data-payment-amount-value=\"{$list_element[0]}\"";
						$list_item_row .= " data-payment-amount-description=\"" . rawurlencode( $list_element[1] ) . "\"";
						$list_item_row .= ">";
						$list_item_row .= "<a href=\"#\" class=\"dd_delete\">" . __( 'Delete', 'wp-full-stripe' ) . "</a>";
						$list_item_row .= "<span class=\"amount\">" . MM_WPFS::format_amount_with_currency( $form->currency, $list_element[0] ) . "</span>";
						$list_item_row .= "<span class=\"desc\">{$list_element[1]}</span>";
						$list_item_row .= "</li>";
						echo $list_item_row;
					}
				}
				?>
			</ul>
			<input type="hidden" name="payment_amount_values">
			<input type="hidden" name="payment_amount_descriptions">

			<p class="description"><?php esc_html_e( 'The amount in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10. The description will be displayed in the dropdown for the amount. Use the {amount} placeholder to include the amount value. You can use drag\'n\'drop to reorder the payment amounts.', 'wp-full-stripe' ); ?></p>
			<label class="checkbox inline"><input type="checkbox" name="allow_custom_payment_amount" id="allow_custom_payment_amount" value="1" <?php echo $form->allowListOfAmountsCustom == '1' ? 'checked' : '' ?>><?php esc_html_e( 'Allow Custom Amount to Be Entered?', 'wp-full-stripe' ); ?>
			</label>
		</td>
	</tr>
</table>
