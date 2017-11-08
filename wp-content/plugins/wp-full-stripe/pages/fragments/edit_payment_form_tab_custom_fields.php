<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.02.23.
 * Time: 14:30
 */

/** @var array $customInputs */
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Include Custom Input Fields?', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_include_custom_input" id="noinclude_custom_input" value="0" <?php echo ( $form->showCustomInput == '0' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_include_custom_input" id="include_custom_input" value="1" <?php echo ( $form->showCustomInput == '1' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'You can ask for extra information from the customer to be included in the payment details.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
</table>
<table id="customInputSection" class="form-table" style="<?php echo ( $form->showCustomInput == '0' ) ? 'display:none;' : '' ?>">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Make Custom Input Fields Required?', 'wp-full-stripe' ); ?></label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_custom_input_required" id="custom_input_required_no" value="0" <?php echo ( $form->customInputRequired == '0' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_custom_input_required" id="custom_input_required_yes" value="1" <?php echo ( $form->customInputRequired == '1' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Number of inputs:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<select id="customInputNumberSelect">
				<option value="1" <?php echo ( count( $customInputs ) == 1 ) ? 'selected="selected"' : '' ?>>
					1
				</option>
				<option value="2" <?php echo ( count( $customInputs ) == 2 ) ? 'selected="selected"' : '' ?>>
					2
				</option>
				<option value="3" <?php echo ( count( $customInputs ) == 3 ) ? 'selected="selected"' : '' ?>>
					3
				</option>
				<option value="4" <?php echo ( count( $customInputs ) == 4 ) ? 'selected="selected"' : '' ?>>
					4
				</option>
				<option value="5" <?php echo ( count( $customInputs ) == 5 ) ? 'selected="selected"' : '' ?>>
					5
				</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 1:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_1" id="form_custom_input_label_1" <?php echo ( count( $customInputs ) > 0 ) ? 'value="' . $customInputs[0] . '"' : '' ?> />

			<p class="description"><?php esc_html_e( 'The text for the label next to the custom input field.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci2">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 2:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_2" id="form_custom_input_label_2" <?php echo ( count( $customInputs ) > 1 ) ? 'value="' . $customInputs[1] . '"' : '' ?> />
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci3">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 3:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_3" id="form_custom_input_label_3" <?php echo ( count( $customInputs ) > 2 ) ? 'value="' . $customInputs[2] . '"' : '' ?> />
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci4">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 4:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_4" id="form_custom_input_label_4" <?php echo ( count( $customInputs ) > 3 ) ? 'value="' . $customInputs[3] . '"' : '' ?> />
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci5">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 5:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_5" id="form_custom_input_label_5" <?php echo ( count( $customInputs ) > 4 ) ? 'value="' . $customInputs[4] . '"' : '' ?> />
		</td>
	</tr>
</table>
