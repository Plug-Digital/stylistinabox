<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2015.08.11.
 * Time: 14:37
 */
?>
	<tr id="redirect_to_row" valign="top" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'style="display: none;"' : '' ?>>
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Redirect to:', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_redirect_to" id="form_redirect_to_page_or_post" value="page_or_post" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> <?php echo ( $form->redirectToPageOrPost == 1 ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'Page or Post', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_redirect_to" id="form_redirect_to_url" value="url" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> <?php echo ( $form->redirectToPageOrPost == 0 ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'URL entered manually', 'wp-full-stripe' ); ?>
			</label>
			<div id="redirect_to_page_or_post_section" <?php echo( $form->redirectToPageOrPost == 0 ? 'style="display: none;"' : '' ) ?>>
				<?php
				$query = new WP_Query( array( 'nopaging' => true, 'post_type' => array( 'page', 'post' ) ) );
				?>
				<div class="ui-widget">
					<select name="form_redirect_page_or_post_id" id="form_redirect_page_or_post_id" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'disabled' : '' ?>>
						<option value=""><?php esc_html_e( 'Select from the list or start typing', 'wp-full-stripe' ); ?></option>
						<?php
						foreach ( $query->posts as $page_or_post ) {
							$option = '<option value="' . esc_attr( $page_or_post->ID ) . '"';
							if ( $page_or_post->ID == $form->redirectPostID ) {
								$option .= ' selected';
							}
							$option .= '>';
							$option .= esc_html( $page_or_post->post_title );
							$option .= '</option>';
							echo $option;
						}
						?>
					</select>
				</div>
				<label class="checkbox inline">
					<input type="checkbox" name="showDetailedSuccessPage" id="showDetailedSuccessPage" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> value="1" <?php echo $form->showDetailedSuccessPage == '0' ? '' : 'checked'; ?>><?php esc_html_e( 'Allow placeholder tokens on Thank You pages?', 'wp-full-stripe' ); ?>
				</label>
				<?php include( 'detailed_success_page_option_description.php' ); ?>
			</div>
			<div id="redirect_to_url_section" <?php echo( $form->redirectToPageOrPost == 1 ? 'style="display: none;"' : '' ) ?>>
				<input type="text" class="regular-text" name="form_redirect_url" id="form_redirect_url" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> placeholder="<?php esc_attr_e( 'Enter URL', 'wp-full-stripe' ); ?>" value="<?php echo $form->redirectUrl; ?>" maxlength="<?php echo $form_data::REDIRECT_URL_LENGTH; ?>">
			</div>
		</td>
	</tr>
<?php if ( $form->redirectOnSuccess == '1' && $form->redirectToPageOrPost == 1 ): ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery('.page_or_post-combobox-input').prop('disabled', false);
			jQuery('.page_or_post-combobox-toggle').button("option", "disabled", false);
		});
	</script>
<?php endif; ?>