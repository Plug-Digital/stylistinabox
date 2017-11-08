<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2015.08.11.
 * Time: 14:37
 */
?>
	<tr valign="top">
		<th scope="row">
			<label class="control-label">Redirect to: </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_redirect_to" id="form_redirect_to_page_or_post" value="page_or_post" <?php echo ( $editForm->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> <?php echo ( $editForm->redirectToPageOrPost == 1 ) ? 'checked' : '' ?>>
				Page or Post
			</label>
			<label class="radio inline">
				<input type="radio" name="form_redirect_to" id="form_redirect_to_url" value="url" <?php echo ( $editForm->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> <?php echo ( $editForm->redirectToPageOrPost == 0 ) ? 'checked' : '' ?>>
				URL entered manually
			</label>
			<div id="redirect_to_page_or_post_section" <?php echo( $editForm->redirectToPageOrPost == 0 ? 'style="display: none;"' : '' ) ?>>
				<?php
				$pages = get_pages();
				?>
				<div class="ui-widget">
					<select name="form_redirect_page_or_post_id" id="form_redirect_page_or_post_id" <?php echo ( $editForm->redirectOnSuccess == '0' ) ? 'disabled' : '' ?>>
						<option value=""><?php echo esc_attr( __( 'Select from the list or start typing', 'wp-full-stripe-free' ) ); ?></option>
						<?php
						foreach ( $pages as $page ) {
							if ( $page->post_type == 'post' || $page->post_type == 'page' ) {
								$option = '<option value="' . $page->ID . '"';
								if ( $page->ID == $editForm->redirectPostID ) {
									$option .= ' selected';
								}
								$option .= '>';
								$option .= $page->post_title;
								$option .= '</option>';
								echo $option;
							}
						}
						?>
					</select>
				</div>
			</div>
			<div id="redirect_to_url_section" <?php echo( $editForm->redirectToPageOrPost == 1 ? 'style="display: none;"' : '' ) ?>>
				<input type="text" class="regular-text" name="form_redirect_url" id="form_redirect_url" <?php echo ( $editForm->redirectOnSuccess == '0' ) ? 'disabled' : '' ?> placeholder="Enter URL" value="<?php echo $editForm->redirectUrl; ?>">
			</div>
		</td>
	</tr>
<?php if ( $editForm->redirectOnSuccess == '1' && $editForm->redirectToPageOrPost == 1 ): ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery('.page_or_post-combobox-input').prop('disabled', false);
			jQuery('.page_or_post-combobox-toggle').button("option", "disabled", false);
		});
	</script>
<?php endif; ?>