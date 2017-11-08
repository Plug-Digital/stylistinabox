<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.02.23.
 * Time: 16:21
 */

$form_type = isset( $_GET['type'] ) ? $_GET['type'] : 'payment';

/**
 * @var WPFS_FormValidationData
 */
$form_data      = MM_WPFS::getInstance()->get_form_validation_data();

?>
<div class="wrap">
	<h2> <?php esc_html_e( 'Full Stripe Create Form', 'wp-full-stripe' ); ?> </h2>

	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>

	<div id="create">
		<?php if ( $form_type == 'payment' ): ?>
			<?php include 'fragments/create_payment_form.php' ?>
		<?php elseif ( $form_type == 'checkout' ): ?>
			<?php include 'fragments/create_checkout_form.php' ?>
		<?php elseif ( $form_type == 'subscription' ): ?>
			<?php include 'fragments/create_subscription_form.php' ?>
		<?php elseif ( $form_type == 'checkout-subscription' ): ?>
			<?php include 'fragments/create_checkout_subscription_form.php' ?>
		<?php endif; ?>
	</div>

</div>