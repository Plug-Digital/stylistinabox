<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.03.27.
 * Time: 14:36
 */
?>
<h2 id="edit-checkout-subscription-form-tabs" class="nav-tab-wrapper wpfs-admin-form-tabs">
	<a href="#edit-checkout-subscription-form-tab-payment" class="nav-tab"><?php esc_html_e( 'Payment', 'wp-full-stripe' ); ?></a>
	<a href="#edit-checkout-subscription-form-tab-appearance" class="nav-tab"><?php esc_html_e( 'Appearance', 'wp-full-stripe' ); ?></a>
	<a href="#edit-checkout-subscription-form-tab-actions-after-payment" class="nav-tab"><?php esc_html_e( 'Actions after payment', 'wp-full-stripe' ); ?></a>
</h2>
<form class="form-horizontal wpfs-admin-form" action="" method="POST" id="edit-checkout-form">
	<p class="tips"></p>
	<input type="hidden" name="action" value="wp_full_stripe_edit_checkout_subscription_form">
	<input type="hidden" name="formID" value="<?php echo $form->checkoutSubscriptionFormID; ?>">
	<div id="edit-checkout-subscription-form-tab-payment" class="wpfs-tab-content">
	</div>
	<div id="edit-checkout-subscription-form-tab-appearance" class="wpfs-tab-content">
	</div>
	<div id="edit-checkout-subscription-form-tab-actions-after-payment" class="wpfs-tab-content">
	</div>
</form>
