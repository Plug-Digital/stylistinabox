<?php

/* @var $plans array */
$plans = MM_WPFS::getInstance()->get_plans();
/* @var $plan \Stripe\Plan */
$plan      = null;
$setup_fee = 0;

$plan_id = '';
if ( isset( $_GET['plan'] ) ) {
	$plan_id = $_GET['plan'];
}

$valid = false;
if ( $plan_id == '' ) {
	$valid = false;
} else {
	$plan_id = stripslashes( $plan_id );
	if ( $plans ) {
		foreach ( $plans as $a_plan ) {
			if ( $a_plan->id === $plan_id ) {
				$plan = $a_plan;
				if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
					$metadata  = $plan->metadata;
					$setup_fee = $metadata->setup_fee;
				}
				$valid = true;
			}
		}
	} else {
		$valid = false;
	}
}

?>
<div class="wrap">
	<h2><?php esc_html_e( 'Modify subscription plan', 'wp-full-stripe' ); ?></h2>

	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
	<?php if ( ! $valid ): ?>
		<p><?php esc_html_e( 'Plan not found!', 'wp-full-stripe' ); ?></p>
	<?php else: ?>
		<form class="form-horizontal" action="" method="POST" id="edit-subscription-plan">
			<p class="tips"></p>
			<input type="hidden" name="action" value="wp_full_stripe_edit_subscription_plan"/>
			<input type="hidden" name="plan" value="<?php echo esc_attr( $plan->id ); ?>">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'ID:', 'wp-full-stripe' ); ?></label>
					</th>
					<td>
						<?php echo esc_html( $plan->id ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Price:', 'wp-full-stripe' ); ?></label>
					</th>
					<td>
						<?php
						$price_label = '';
						if ( $plan->interval_count === 1 ) {
							$price_label = sprintf( '%s / %s', MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount ), $plan->interval );
						} else {
							if ( $plan->interval === 'week' ) {
								$price_label = sprintf( _n( '%s / %d week', '%s / %d weeks', $plan->interval_count, 'wp-full-stripe' ), MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount ), $plan->interval_count );
							} else if ( $plan->interval === 'month' ) {
								$price_label = sprintf( _n( '%s / %d month', '%s / %d months', $plan->interval_count, 'wp-full-stripe' ), MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount ), $plan->interval_count );
							} else if ( $plan->interval === 'year' ) {
								$price_label = sprintf( _n( '%s / %d year', '%s / %d years', $plan->interval_count, 'wp-full-stripe' ), MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount ), $plan->interval_count );
							}
						}
						echo esc_html( $price_label );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Setup Fee:', 'wp-full-stripe' ); ?> </label>
					</th>
					<td>
						<input type="text" class="regular-text" name="plan_setup_fee" id="form_plan_setup_fee" value="<?php echo esc_attr( $setup_fee ); ?>">
						<p class="description"><?php esc_html_e( 'Amount to charge the customer to setup the subscription, in the smallest unit for the currency. i.e. for $10.00 enter 1000, for ¥10 enter 10. Entering 0 will disable.', 'wp-full-stripe' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Duration:', 'wp-full-stripe' ); ?></label>
					</th>
					<td>
						<?php
						$duration = __( 'Forever', 'wp-full-stripe' );
						if ( isset( $plan->metadata ) ) {
							if ( isset( $plan->metadata->cancellation_count ) ) {
								if ( is_numeric( $plan->metadata->cancellation_count ) ) {
									$cancellation_count = intval( $plan->metadata->cancellation_count );
									if ( $cancellation_count > 0 ) {
										$cardinality = $cancellation_count;
										if ( isset( $plan->interval_count ) && is_numeric( $plan->interval_count ) ) {
											$cardinality = intval( $plan->interval_count ) * $cardinality;
										}
										$by_interval = null;
										if ( $plan->interval === 'week' ) {
											$by_interval = sprintf( _n( '%d week', '%d weeks', $cardinality, 'wp-full-stripe' ), $cardinality );
										} else if ( $plan->interval === 'month' ) {
											$by_interval = sprintf( _n( '%d month', '%d months', $cardinality, 'wp-full-stripe' ), $cardinality );
										} else if ( $plan->interval === 'year' ) {
											$by_interval = sprintf( _n( '%d year', '%d years', $cardinality, 'wp-full-stripe' ), $cardinality );
										}
										if ( empty( $by_interval ) ) {
											$duration = sprintf( _n( '%d charge', '%d charges', $cancellation_count, 'wp-full-stripe' ), $cancellation_count );
										} else {
											$duration = sprintf( _n( '%d charge (%s)', '%d charges (%s)', $cancellation_count, 'wp-full-stripe' ), $cancellation_count, $by_interval );
										}
									}
								} else {
									$duration = $plan->metadata->cancellation_count;
								}
							}
						}
						echo esc_html( $duration );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Trial:', 'wp-full-stripe' ); ?></label>
					</th>
					<td>
						<?php echo isset( $plan->trial_period_days ) ? esc_html( $plan->trial_period_days . ' days' ) : __( 'No trial', 'wp-full-stripe' ) ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Display name:', 'wp-full-stripe' ); ?>*</label>
					</th>
					<td>
						<input type="text" class="regular-text" name="plan_display_name" id="form_plan_display_name" value="<?php echo esc_attr( $plan->name ); ?>"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label class="control-label"><?php esc_html_e( 'Statement descriptor:', 'wp-full-stripe' ); ?></label>
					</th>
					<td>
						<input type="text" class="regular-text" name="plan_statement_descriptor" id="form_plan_statement_descriptor" value="<?php echo esc_attr( $plan->statement_descriptor ); ?>" maxlength="<?php echo WPFS_PlanValidationData::STATEMENT_DESCRIPTOR_LENGTH; ?>"/>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button class="button button-primary" type="submit"><?php esc_html_e( 'Modify plan', 'wp-full-stripe' ); ?></button>
				<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wp-full-stripe' ); ?></a>
				<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
			</p>

		</form>
	<?php endif; ?>
</div>