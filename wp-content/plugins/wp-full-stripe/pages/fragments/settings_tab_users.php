<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.04.13.
 * Time: 15:28
 */

$options    = get_option( 'fullstripe_options' );

?>
<div id="users-tab">
	<form class="form-horizontal" action="#" method="post" id="settings-email-receipts-form">
		<p class="tips"></p>
		<input type="hidden" name="action" value="wp_full_stripe_update_settings"/>
		<input type="hidden" name="tab" value="users">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php _e( "Lock e-mail address field for logged in users?: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<label class="radio">
						<input type="radio" name="lock_email_field_for_logged_in_users" id="lock_email_field_for_logged_in_users_no" value="0" <?php echo ( $options['lock_email_field_for_logged_in_users'] == '0' ) ? 'checked' : '' ?>>
						<?php _e( 'No', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio">
						<input type="radio" name="lock_email_field_for_logged_in_users" id="lock_email_field_for_logged_in_users_yes" value="1" <?php echo ( $options['lock_email_field_for_logged_in_users'] == '1' ) ? 'checked' : '' ?> >
						<?php _e( 'Yes', 'wp-full-stripe' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes' ) ?></button>
			<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
		</p>
	</form>
</div>
