<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.03.28.
 * Time: 14:05
 */
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Include Custom Input Fields?', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_include_custom_input" id="noinclude_custom_input" value="0" checked="checked">
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_include_custom_input" id="include_custom_input" value="1">
				<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'You can ask for extra information from the customer to be included in the payment details.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
</table>
<table id="customInputSection" class="form-table" style="display: none;">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Make Custom Input Fields Required?', 'wp-full-stripe' ); ?></label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_custom_input_required" id="custom_input_required_no" value="0" checked="checked">
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_custom_input_required" id="custom_input_required_yes" value="1">
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
				<option value="1" selected="selected">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 1:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_1" id="form_custom_input_label_1"/>
			<p class="description"><?php esc_html_e( 'The text for the label next to the custom input field.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci2">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 2:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_2" id="form_custom_input_label_2"/>
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci3">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 3:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_3" id="form_custom_input_label_3"/>
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci4">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 4:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_4" id="form_custom_input_label_4"/>
		</td>
	</tr>
	<tr valign="top" style="display: none;" class="ci5">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Custom Input Label 5:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<input type="text" class="regular-text" name="form_custom_input_label_5" id="form_custom_input_label_5"/>
		</td>
	</tr>
</table>
