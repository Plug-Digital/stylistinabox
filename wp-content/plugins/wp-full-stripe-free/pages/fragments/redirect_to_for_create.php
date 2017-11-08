<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2015.08.11.
 * Time: 13:36
 */
?>
<tr valign="top">
	<th scope="row">
		<label class="control-label">Redirect to:</label>
	</th>
	<td>
		<label class="radio inline">
			<input type="radio" name="form_redirect_to" id="form_redirect_to_page_or_post" value="page_or_post" disabled>
			Page or Post
		</label>
		<label class="radio inline">
			<input type="radio" name="form_redirect_to" id="form_redirect_to_url" value="url" disabled> URL entered
			manually
		</label>
		<div id="redirect_to_page_or_post_section">
			<?php
			$pages = get_pages();
			?>
			<div class="ui-widget">
				<select name="form_redirect_page_or_post_id" id="form_redirect_page_or_post_id" disabled>
					<option value=""><?php echo esc_attr( __( 'Select from the list or start typing', 'wp-full-stripe-free' ) ); ?></option>
					<?php
					foreach ( $pages as $page ) {
						if ( $page->post_type == 'post' || $page->post_type == 'page' ) {
							$option = '<option value="' . $page->ID . '">';
							$option .= $page->post_title;
							$option .= '</option>';
							echo $option;
						}
					}
					?>
				</select>
			</div>
		</div>
		<div id="redirect_to_url_section" style="display: none;">
			<input type="text" class="regular-text" name="form_redirect_url" id="form_redirect_url" disabled placeholder="Enter URL">
		</div>
	</td>
</tr>
