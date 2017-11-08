<?php

/**
 * @var WPFS_FormValidationData
 */
$form_data = MM_WPFS::getInstance()->get_form_validation_data();

global $wpdb;
//get the data we need
$form_id   = - 1;
$form_type = "";
if ( isset( $_GET['form'] ) ) {
	$form_id = $_GET['form'];
}
if ( isset( $_GET['type'] ) ) {
	$form_type = $_GET['type'];
}

$valid = true;
if ( $form_id == - 1 || $form_type == "" ) {
	$valid = false;
}

/** @var $plans array */
$plans = array();
$form  = null;

if ( $valid ) {

	if ( $form_type == "payment" ) {
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payment_forms WHERE paymentFormID=%d", $form_id ) );
	} else if ( $form_type == "subscription" ) {
		$form  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscription_forms WHERE subscriptionFormID=%d", $form_id ) );
		$plans = MM_WPFS::getInstance()->get_plans();
	} else if ( $form_type == "checkout" ) {
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "fullstripe_checkout_forms WHERE checkoutFormID=%d", $form_id ) );
	} else if ( $form_type == "checkout-subscription" ) {
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "fullstripe_checkout_subscription_forms WHERE checkoutSubscriptionFormID=%d", $form_id ) );
		$plans = MM_WPFS::getInstance()->get_plans();
	} else {
		$valid = false;
	}

	if ( $form == null ) {
		$valid = false;
	}
}

?>
<div class="wrap">
	<h2><?php esc_html_e( 'Full Stripe Edit Form', 'wp-full-stripe' ); ?></h2>

	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
	<?php if ( ! $valid ): ?>
		<p><?php esc_html_e( 'Form not found!', 'wp-full-stripe' ); ?></p>
	<?php else: ?>
		<?php if ( $form_type == "payment" ): ?>
			<?php include( 'fragments/edit_payment_form.php' ); ?>
		<?php elseif ( $form_type == "subscription" ): ?>
			<?php include( 'fragments/edit_subscription_form.php' ); ?>
		<?php elseif ( $form_type == "checkout" ): ?>
			<?php include( 'fragments/edit_checkout_form.php' ); ?>
		<?php elseif ( $form_type == "checkout-subscription" ): ?>
			<?php include( 'fragments/edit_checkout_subscription_form.php' ); ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
