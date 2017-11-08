<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.04.04.
 * Time: 9:52
 */
?>
<div class="help-and-support">
	<p><?php printf( __( 'Check out our <a href="%s" target="_blank">Help section</a> or visit the <a href="%s" target="_blank">Support page</a> if you have questions.', 'wp-full-stripe' ), admin_url( "admin.php?page=fullstripe-help" ), 'http://paymentsplugin.com/support/' ); ?></p>
	<p><?php printf( __( 'You can subscribe for premium support for FREE by <a href="%s" target="_blank">adding your email address to our mailing list</a>.', 'wp-full-stripe' ), 'http://eepurl.com/5zJG1' ); ?></p>
	<a href="http://eepurl.com/5zJG1" target="_blank" class="button button-primary"><?php _e( 'Subscribe for premium support', 'wp-full-stripe' ); ?></a>
</div>
