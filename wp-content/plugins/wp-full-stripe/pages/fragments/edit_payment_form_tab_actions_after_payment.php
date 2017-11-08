<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.02.23.
 * Time: 14:30
 */
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Send Email Receipt?', 'wp-full-stripe' ); ?> </label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_send_email_receipt" value="0" <?php echo ( $form->sendEmailReceipt == '0' ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_send_email_receipt" value="1" <?php echo ( $form->sendEmailReceipt == '1' ) ? 'checked' : '' ?>>
				<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'Send an email receipt on successful payment?', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label class="control-label"><?php esc_html_e( 'Redirect On Success?', 'wp-full-stripe' ); ?></label>
		</th>
		<td>
			<label class="radio inline">
				<input type="radio" name="form_do_redirect" id="do_redirect_no" value="0" <?php echo ( $form->redirectOnSuccess == '0' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
			</label>
			<label class="radio inline">
				<input type="radio" name="form_do_redirect" id="do_redirect_yes" value="1" <?php echo ( $form->redirectOnSuccess == '1' ) ? 'checked' : '' ?> >
				<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
			</label>

			<p class="description"><?php esc_html_e( 'When payment is successful you can choose to redirect to another page or post.', 'wp-full-stripe' ); ?></p>
		</td>
	</tr>
	<?php include( 'redirect_to_for_edit.php' ); ?>
</table>
