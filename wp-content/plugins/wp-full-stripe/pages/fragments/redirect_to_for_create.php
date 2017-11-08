<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2015.08.11.
 * Time: 13:36
 */
?>
<tr id="redirect_to_row" valign="top" style="display: none;">
	<th scope="row">
		<label class="control-label"><?php esc_html_e( 'Redirect to:', 'wp-full-stripe' ); ?></label>
	</th>
	<td>
		<label class="radio inline">
			<input type="radio" name="form_redirect_to" id="form_redirect_to_page_or_post" value="page_or_post" disabled>
			<?php esc_html_e( 'Page or Post', 'wp-full-stripe' ); ?>
		</label>
		<label class="radio inline">
			<input type="radio" name="form_redirect_to" id="form_redirect_to_url" value="url" disabled>
			<?php esc_html_e( 'URL entered manually', 'wp-full-stripe' ); ?>
		</label>
		<div id="redirect_to_page_or_post_section">
			<?php
			$query = new WP_Query( array( 'nopaging' => true, 'post_type' => array( 'page', 'post' ) ) );
			?>
			<div class="ui-widget">
				<select name="form_redirect_page_or_post_id" id="form_redirect_page_or_post_id" disabled>
					<option value=""><?php esc_html_e( 'Select from the list or start typing', 'wp-full-stripe' ); ?></option>
					<?php
					foreach ( $query->posts as $page_or_post ) {
						$option = '<option value="' . esc_attr( $page_or_post->ID ) . '">';
						$option .= esc_html( $page_or_post->post_title );
						$option .= '</option>';
						echo $option;
					}
					?>
				</select>
			</div>
			<label class="checkbox inline">
				<input type="checkbox" name="showDetailedSuccessPage" id="showDetailedSuccessPage" value="1" disabled><?php esc_html_e( 'Allow placeholder tokens on Thank You pages?', 'wp-full-stripe' ); ?>
			</label>
			<?php include( 'detailed_success_page_option_description.php' ); ?>
		</div>
		<div id="redirect_to_url_section" style="display: none;">
			<input type="text" class="regular-text" name="form_redirect_url" id="form_redirect_url" disabled placeholder="<?php esc_attr_e( 'Enter URL', 'wp-full-stripe' ); ?>" maxlength="<?php echo $form_data::REDIRECT_URL_LENGTH; ?>">
		</div>
	</td>
</tr>
