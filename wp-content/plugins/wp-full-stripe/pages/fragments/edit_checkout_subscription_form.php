<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2017.04.05.
 * Time: 9:55
 */

$customInputs = array();
if ( $form->customInputs ) {
	$customInputs = explode( '{{', $form->customInputs );
}

$plans = MM_WPFS::getInstance()->get_plans();

?>
<h2 id="edit-checkout-subscription-form-tabs" class="nav-tab-wrapper wpfs-admin-form-tabs">
	<a href="#edit-checkout-subscription-form-tab-payment" class="nav-tab"><?php esc_html_e( 'Payment', 'wp-full-stripe' ); ?></a>
	<a href="#edit-checkout-subscription-form-tab-appearance" class="nav-tab"><?php esc_html_e( 'Appearance', 'wp-full-stripe' ); ?></a>
	<a href="#edit-checkout-subscription-form-tab-custom-fields" class="nav-tab"><?php esc_html_e( 'Custom Fields', 'wp-full-stripe' ); ?></a>
	<a href="#edit-checkout-subscription-form-tab-actions-after-payment" class="nav-tab"><?php esc_html_e( 'Actions after payment', 'wp-full-stripe' ); ?></a>
</h2>
<form class="form-horizontal wpfs-admin-form" action="" method="POST" id="create-checkout-subscription-form">
	<p class="tips"></p>
	<input type="hidden" name="action" value="wp_full_stripe_edit_checkout_subscription_form">
	<input type="hidden" name="formID" value="<?php echo $form->checkoutSubscriptionFormID; ?>">
	<div id="edit-checkout-subscription-form-tab-payment" class="wpfs-tab-content">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Form Type:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td><?php esc_html_e( 'Popup subscription form', 'wp-full-stripe' ); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Form Name:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<input type="text" class="regular-text" name="form_name" id="form_name" value="<?php echo $form->name; ?>" maxlength="<?php echo $form_data::NAME_LENGTH; ?>">

					<p class="description"><?php esc_html_e( 'This name will be used to identify this form in the shortcode i.e. [fullstripe_subscription_checkout form="FormName"].', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Include Coupon Input Field?', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<label class="radio inline">
						<input type="radio" name="form_include_coupon_input" id="noinclude_coupon_input" value="0" <?php echo ( $form->showCouponInput == '0' ) ? 'checked' : '' ?> >
						<?php esc_html_e( 'No', 'wp-full-stripe' ); ?>
					</label>
					<label class="radio inline">
						<input type="radio" name="form_include_coupon_input" id="include_coupon_input" value="1" <?php echo ( $form->showCouponInput == '1' ) ? 'checked' : '' ?> >
						<?php esc_html_e( 'Yes', 'wp-full-stripe' ); ?>
					</label>

					<p class="description"><?php esc_html_e( 'You can allow customers to input coupon codes for discounts. Must create the coupon in your Stripe account dashboard.', 'wp-full-stripe' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label class="control-label"><?php esc_html_e( 'Plans:', 'wp-full-stripe' ); ?> </label>
				</th>
				<td>
					<div class="plan_checkboxes">
						<ul class="plan_checkbox_list">
							<?php
							$form_plans    = json_decode( $form->plans );
							$ordered_plans = array();
							$plan_order    = array();
							foreach ( $plans as $plan ) {
								$i = array_search( $plan->id, $form_plans );
								if ( $i !== false ) {
									$ordered_plans[ $i ] = $plan;
								}
							}
							ksort( $ordered_plans );
							?>
							<?php foreach ( $ordered_plans as $plan ): ?>
								<?php
								$plan_order[]    = $plan->id;
								$currency_symbol = MM_WPFS::get_currency_symbol_for( $plan->currency );
								?>
								<li class="ui-state-default" data-toggle="tooltip" title="<?php esc_attr_e( 'You can reorder this list by using drag\'n\'drop.', 'wp-full-stripe' ); ?>" data-plan-id="<?php echo esc_attr( $plan->id ); ?>">
									<label class="checkbox inline">
										<input type="checkbox" class="plan_checkbox" id="check_<?php echo esc_attr( $plan->id ); ?>" value="<?php echo esc_attr( $plan->id ); ?>" checked>
                                        <span class="plan_checkbox_text"><?php echo esc_html( $plan->name ); ?> (
	                                        <?php
	                                        $str = MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount );
	                                        if ( $plan->interval_count == 1 ) {
		                                        $str .= ' ' . ucfirst( $plan->interval ) . 'ly';
	                                        } else {
		                                        $str .= ' every ' . $plan->interval_count . ' ' . $plan->interval . 's';
	                                        }
	                                        echo esc_html( $str );
	                                        ?>
	                                        )</span>
									</label>
								</li>
							<?php endforeach; ?>
							<?php foreach ( $plans as $plan ): ?>
								<?php if ( ! in_array( $plan->id, $form_plans ) ): ?>
									<?php
									$plan_order[]    = $plan->id;
									$currency_symbol = MM_WPFS::get_currency_symbol_for( $plan->currency );
									?>
									<li class="ui-state-default" data-toggle="tooltip" title="<?php esc_attr_e( 'You can reorder this list by using drag\'n\'drop.', 'wp-full-stripe' ); ?>" data-plan-id="<?php echo esc_attr( $plan->id ); ?>">
										<label class="checkbox inline">
											<input type="checkbox" class="plan_checkbox" id="check_<?php echo esc_attr( $plan->id ); ?>" value="<?php echo esc_attr( $plan->id ); ?>">
                                            <span class="plan_checkbox_text"><?php echo esc_html( $plan->name ); ?> (
	                                            <?php
	                                            // todo tnagy make this section internationalizable
	                                            $str = MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount );
	                                            if ( $plan->interval_count == 1 ) {
		                                            $str .= ' ' . ucfirst( $plan->interval ) . 'ly';
	                                            } else {
		                                            $str .= ' every ' . $plan->interval_count . ' ' . $plan->interval . 's';
	                                            }
	                                            echo esc_html( $str );
	                                            ?>
	                                            )</span>
										</label>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
					<p class="description"><?php esc_html_e( 'Which subscription plans can be chosen on this form. The list can be reordered by using drag\'n\'drop.', 'wp-full-stripe' ); ?></p>
					<input type="hidden" name="plan_order" value="<?php echo rawurlencode( json_encode( $plan_order ) ); ?>"/>
				</td>
			</tr>
		</table>
	</div>
	<div id="edit-checkout-subscription-form-tab-appearance" class="wpfs-tab-content">
		<?php include( 'edit_checkout_form_tab_appearance.php' ); ?>
	</div>
	<div id="edit-checkout-subscription-form-tab-custom-fields" class="wpfs-tab-content">
		<?php include( 'edit_payment_form_tab_custom_fields.php' ); ?>
	</div>
	<div id="edit-checkout-subscription-form-tab-actions-after-payment" class="wpfs-tab-content">
		<?php include( 'edit_payment_form_tab_actions_after_payment.php' ); ?>
	</div>
	<p class="submit">
		<button class="button button-primary" type="submit"><?php esc_html_e( 'Save Changes', 'wp-full-stripe' ); ?></button>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wp-full-stripe' ); ?></a>
		<img src="<?php echo plugins_url( '../img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
	</p>
</form>
