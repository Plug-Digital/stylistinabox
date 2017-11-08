<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.03.02.
 * Time: 16:47
 */
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Form Style:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<select class="regular-text" name="form_style" id="form_style">
				<option value="0" <?php if ( $form->formStyle == 0 ) {
					echo 'selected="selected"';
				} ?> ><?php esc_html_e( 'Default', 'wp-full-stripe' ); ?>
				</option>
				<option value="1" <?php if ( $form->formStyle == 1 ) {
					echo 'selected="selected"';
				} ?> ><?php esc_html_e( 'Compact', 'wp-full-stripe' ); ?>
				</option>
			</select>

			<p class="description"><?php esc_html_e( 'Choose how you\'d like the form to look. (More coming soon!)', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Form Title:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_title" id="form_title" value="<?php echo $form->formTitle; ?>" maxlength="<?php echo $form_data::FORM_TITLE_LENGTH; ?>">

			<p class="description"><?php esc_html_e( 'The title of the form.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Payment Button Text:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_button_text" id="form_button_text" value="<?php echo $form->buttonTitle; ?>" maxlength="<?php echo $form_data::BUTTON_TITLE_LENGTH; ?>">

			<p class="description"><?php esc_html_e( 'The text on the payment button.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Include Amount on Button?', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_button_amount" id="hide_button_amount" value="0" <?php echo ( $form->showButtonAmount == '0' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Hide', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_button_amount" id="show_button_amount" value="1" <?php echo ( $form->showButtonAmount == '1' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Show', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'For set amount forms, choose to show/hide the amount on the payment button.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Include Billing Address Field?', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_show_address_input" id="hide_address_input" value="0" <?php echo ( $form->showAddress == '0' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Hide', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_show_address_input" id="show_address_input" value="1" <?php echo ( $form->showAddress == '1' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Show', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'Should this payment form also ask for the customers billing address?', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
</table>
