<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.04.13.
 * Time: 15:27
 */

$options = get_option( 'fullstripe_options' );

?>
<div id="appearance-tab">
	<form class="form-horizontal" action="#" method="post" id="settings-appearance-form">
		<p class="tips"></p>
		<input type="hidden" name="action" value="wp_full_stripe_update_settings"/>
		<input type="hidden" name="tab" value="appearance">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label" for="form_css"><?php _e( "Custom Form CSS: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<textarea name="form_css" id="form_css" class="large-text code" rows="10" cols="50"><?php echo $options['form_css']; ?></textarea>

					<p class="description"><?php _e( 'Add extra styling to the form. NOTE: if you don\'t know what this is do not change it.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php _e( "Include Default Styles: ", 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<label class="radio">
						<input type="radio" name="includeStyles" id="includeStylesY" value="1" <?php echo ( $options['includeStyles'] == '1' ) ? 'checked' : '' ?> >
						<?php _e( 'Include', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio">
						<input type="radio" name="includeStyles" id="includeStylesN" value="0" <?php echo ( $options['includeStyles'] == '0' ) ? 'checked' : '' ?>>
						<?php _e( 'Exclude', 'wp-full-stripe' ); ?>
					</label>

					<p class="description"><?php _e( 'Exclude styles if the payment forms do not appear properly. This can indicate a conflict with your theme.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes' ) ?></button>
			<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
		</p>
	</form>
</div>
