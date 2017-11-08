<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.04.13.
 * Time: 15:26
 */

$options = get_option( 'fullstripe_options' );

$test_secret_key      = $options['secretKey_test'];
$test_publishable_key = $options['publishKey_test'];
$live_secret_key      = $options['secretKey_live'];
$live_publishable_key = $options['publishKey_live'];
if ( defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
	$test_secret_key      = __( 'Test secret key here', 'wp-full-stripe' );
	$test_publishable_key = __( 'Test publishable key here', 'wp-full-stripe' );
	$live_secret_key      = __( 'Live secret key here', 'wp-full-stripe' );
	$live_publishable_key = __( 'Live publishable key here', 'wp-full-stripe' );
}
?>
<div id="stripe-tab">
	<p class="alert alert-info"><?php _e( 'The Stripe API keys are required for payments to work. You can find your keys on your <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe Dashboard -> API</a> page', 'wp-full-stripe' ); ?></p>

	<form class="form-horizontal" action="#" method="post" id="settings-stripe-form">
		<p class="tips"></p>
		<input type="hidden" name="action" value="wp_full_stripe_update_settings"/>
		<input type="hidden" name="tab" value="stripe">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php _e( "API mode: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<label class="radio">
						<input type="radio" name="apiMode" id="modeTest" value="test" <?php echo ( $options['apiMode'] == 'test' ) ? 'checked' : '' ?> >
						Test
					</label>
					<label class="radio">
						<input type="radio" name="apiMode" id="modeLive" value="live" <?php echo ( $options['apiMode'] == 'live' ) ? 'checked' : '' ?>>
						Live
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="secretKey_test"><?php _e( "Stripe Test Secret Key: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" name="secretKey_test" id="secretKey_test" value="<?php echo $test_secret_key; ?>" class="regular-text code">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="publishKey_test"><?php _e( "Stripe Test Publishable Key: ", 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<input type="text" id="publishKey_test" name="publishKey_test" value="<?php echo $test_publishable_key; ?>" class="regular-text code">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="secretKey_live"><?php _e( "Stripe Live Secret Key: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" name="secretKey_live" id="secretKey_live" value="<?php echo $live_secret_key; ?>" class="regular-text code">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="publishKey_live"><?php _e( "Stripe Live Publishable Key: ", 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<input type="text" id="publishKey_live" name="publishKey_live" value="<?php echo $live_publishable_key; ?>" class="regular-text code">
				</td>
			</tr>
		</table>
		<hr>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php _e( 'Stripe Webhook URL: ', 'wp-full-stripe' ); ?></label>
				</th>
				<td>
					<input id="stripe-webhook-url" class="large-text" type="text" value="<?php echo esc_attr( add_query_arg( array(
						'action'     => 'handle_wpfs_event',
						'auth_token' => MM_WPFS_Admin::get_webhook_token()
					), admin_url( 'admin-post.php' ) ) ); ?>" readonly>
					<p class="description"><?php printf( __( 'This URL must be set in Stripe as a webhook endpoint. See the <a target="_blank" href="%s">"Setup" chapter</a> of the "Help" page for more information.', 'wp-full-stripe' ), admin_url( "admin.php?page=fullstripe-help#" ) ); ?>
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes' ) ?></button>
			<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
		</p>
	</form>
</div>
