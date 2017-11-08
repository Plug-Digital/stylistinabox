<?php

$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'subscribers';

global $wpdb;

//Load based on what tab we have open
/** @var $plans array */
$plans                       = array();
$subscription_forms          = array();
$checkout_subscription_forms = array();
if ( $active_tab == 'forms' ) {
	$subscription_forms          = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscription_forms;" );
	$checkout_subscription_forms = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_checkout_subscription_forms;" );
} else if ( $active_tab == 'plans' || $active_tab == 'createform' ) {
	$plans = MM_WPFS::getInstance()->get_plans();
}
?>
<div class="wrap">
	<h2> <?php esc_html_e( 'Full Stripe Subscriptions', 'wp-full-stripe' ); ?> </h2>
	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=subscribers' ); ?>" class="nav-tab <?php echo $active_tab == 'subscribers' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Subscribers', 'wp-full-stripe' ); ?>
		</a>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ); ?>" class="nav-tab <?php echo $active_tab == 'forms' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Subscription Forms', 'wp-full-stripe' ); ?>
		</a>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' ); ?>" class="nav-tab <?php echo $active_tab == 'plans' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Subscription Plans', 'wp-full-stripe' ); ?>
		</a>
	</h2>
	<div class="wpfs-tab-content">
		<?php if ( $active_tab == 'subscribers' ): ?>
			<div class="" id="subscribers">
				<h2>
					<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
				</h2>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
					<label><?php _e( 'Subscriber: ', 'wp-full-stripe' ); ?></label><input type="text" name="subscriber" size="35" placeholder="<?php _e( 'Enter name, email address, or stripe ID', 'wp-full-stripe' ); ?>" value="<?php echo isset( $_REQUEST['subscriber'] ) ? $_REQUEST['subscriber'] : ''; ?>">
					<label><?php _e( 'Subscription: ', 'wp-full-stripe' ); ?></label><input type="text" name="subscription" placeholder="<?php _e( 'Enter subscription ID', 'wp-full-stripe' ); ?>" value="<?php echo isset( $_REQUEST['subscription'] ) ? $_REQUEST['subscription'] : ''; ?>">
					<label><?php _e( 'Mode: ', 'wp-full-stripe' ); ?></label>
					<select name="mode">
						<option value="" <?php echo ! isset( $_REQUEST['mode'] ) || $_REQUEST['mode'] == '' ? 'selected' : ''; ?>><?php _e( 'All', 'wp-full-stripe' ); ?></option>
						<option value="live" <?php echo isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] == 'live' ? 'selected' : ''; ?>><?php _e( 'Live', 'wp-full-stripe' ); ?></option>
						<option value="test" <?php echo isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] == 'test' ? 'selected' : ''; ?>><?php _e( 'Test', 'wp-full-stripe' ); ?></option>
					</select>
					<span class="wpfs-search-actions">
						<button class="button button-primary"><?php _e( 'Search', 'wp-full-stripe' ); ?></button> <?php _e( 'or', 'wp-full-stripe' ); ?>
						<a href="<?php echo admin_url( 'admin.php?page=fullstripe-subscriptions' ); ?>"><?php _e( 'Reset', 'wp-full-stripe' ); ?></a>
					</span>
					<?php
					/** @var WP_List_Table $subscribersTable */
					$subscribersTable->prepare_items();
					$subscribersTable->display();
					?>
				</form>
			</div>
		<?php elseif ( $active_tab == 'forms' ): ?>
			<div class="" id="wpfs-subscription-forms">
				<div style="min-height: 200px;">
					<h2><?php esc_html_e( 'Your Inline Forms', 'wp-full-stripe' ); ?>
						<a class="page-title-action" href="<?php echo add_query_arg(
							array(
								'page' => 'fullstripe-create-form',
								'type' => 'subscription'
							),
							admin_url( "admin.php" )
						); ?>" title="<?php esc_attr_e( 'Create Inline Form', 'wp-full-stripe' ); ?>"><i class="fa fa-plus fa-fw"></i><?php esc_html_e( 'Create Inline Form', 'wp-full-stripe' ); ?>
						</a>
						<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
					</h2>
					<?php if ( count( $subscription_forms ) === 0 ): ?>
						<p class="alert alert-info">
							<?php esc_html_e( "No inline subscription forms created. Use the 'Create Inline Form' button to get started.", 'wp-full-stripe' ); ?>
						</p>
					<?php else: ?>
						<table class="wp-list-table widefat fixed subscription-forms">
							<thead>
							<tr>
								<th class="manage-column column-action column-primary"><?php esc_html_e( 'Actions', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-title"><?php esc_html_e( 'Title', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-plan_ids"><?php esc_html_e( 'Plan IDs', 'wp-full-stripe' ); ?></th>
							</tr>
							</thead>
							<tbody id="subscriptionFormsTable">
							<?php foreach ( $subscription_forms as $subscription_form ): ?>
								<?php
								$plans_label = null;
								$sf_plans    = json_decode( $subscription_form->plans );
								if ( json_last_error() == JSON_ERROR_NONE ) {
									$plans_label = implode( ', ', $sf_plans );
								}
								?>
								<tr>
									<td class="column-action">
										<?php
										$shortcode = "[fullstripe_subscription form=\"{$subscription_form->name}\"]"
										?>
										<span id="shortcode-subscription-tooltip__<?php echo $subscription_form->subscriptionFormID; ?>" class="shortcode-tooltip" data-shortcode="<?php echo esc_attr( $shortcode ); ?>"></span>
										<a id="shortcode-subscription__<?php echo $subscription_form->subscriptionFormID; ?>" class="button button-primary shortcode-subscription" data-form-id="<?php echo $subscription_form->subscriptionFormID; ?>" title="<?php _e( 'Shortcode', 'wp-full-stripe' ); ?>">
											<i class="fa fa-code fa-fw"></i>
										</a>
										<a class="button button-primary" href="<?php echo add_query_arg(
											array(
												'page' => 'fullstripe-edit-form',
												'form' => $subscription_form->subscriptionFormID,
												'type' => 'subscription'
											),
											admin_url( "admin.php" )
										); ?>" title="<?php _e( 'Edit', 'wp-full-stripe' ); ?>"><i class="fa fa-pencil fa-fw"></i></a>
										<span class="form-action-last">
											<button class="button delete" data-id="<?php echo $subscription_form->subscriptionFormID; ?>" data-type="subscriptionForm" title="<?php _e( 'Delete', 'wp-full-stripe' ); ?>">
												<i class="fa fa-trash-o fa-fw"></i>
											</button>
										</span>
									</td>
									<td class="column-name"><?php echo esc_html( $subscription_form->name ); ?></td>
									<td class="column-title"><?php echo esc_html( $subscription_form->formTitle ); ?></td>
									<td class="column-plan_ids"><?php echo esc_html( $plans_label ); ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
				<div style="min-height: 200px;">
					<h2><?php esc_html_e( 'Your Popup Forms', 'wp-full-stripe' ); ?>
						<a class="page-title-action" href="<?php echo add_query_arg(
							array(
								'page' => 'fullstripe-create-form',
								'type' => 'checkout-subscription'
							),
							admin_url( "admin.php" )
						); ?>" title="<?php esc_attr_e( 'Create Popup Form', 'wp-full-stripe' ); ?>"><i class="fa fa-plus fa-fw"></i><?php esc_html_e( 'Create Popup Form', 'wp-full-stripe' ); ?>
						</a>
						<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
					</h2>
					<?php if ( count( $checkout_subscription_forms ) === 0 ): ?>
						<p class="alert alert-info">
							<?php esc_html_e( 'No checkout subscription forms created yet. Use the Create Popup Form button to get started.', 'wp-full-stripe' ); ?>
						</p>
					<?php else: ?>
						<table class="wp-list-table widefat fixed subscription-forms">
							<thead>
							<tr>
								<th class="manage-column column-action column-primary"><?php esc_html_e( 'Actions', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-title"><?php esc_html_e( 'Title', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-plan_ids"><?php esc_html_e( 'Plan IDs', 'wp-full-stripe' ); ?></th>
							</tr>
							</thead>
							<tbody id="subscriptionFormsTable">
							<?php foreach ( $checkout_subscription_forms as $checkout_subscription_form ): ?>
								<?php
								$plans_label = null;
								$scf_plans   = json_decode( $checkout_subscription_form->plans );
								if ( json_last_error() == JSON_ERROR_NONE ) {
									$plans_label = implode( ', ', $scf_plans );
								}
								?>
								<tr>
									<td class="column-action">
										<?php
										$shortcode = "[fullstripe_subscription_checkout form=\"{$checkout_subscription_form->name}\"]"
										?>
										<span id="shortcode-checkout-subscription-tooltip__<?php echo $checkout_subscription_form->checkoutSubscriptionFormID; ?>" class="shortcode-tooltip" data-shortcode="<?php echo esc_attr( $shortcode ); ?>"></span>
										<a id="shortcode-checkout-subscription__<?php echo $checkout_subscription_form->checkoutSubscriptionFormID; ?>" class="button button-primary shortcode-checkout-subscription" data-form-id="<?php echo $checkout_subscription_form->checkoutSubscriptionFormID; ?>" title="<?php _e( 'Shortcode', 'wp-full-stripe' ); ?>">
											<i class="fa fa-code fa-fw"></i>
										</a>
										<a class="button button-primary" href="<?php echo add_query_arg(
											array(
												'page' => 'fullstripe-edit-form',
												'form' => $checkout_subscription_form->checkoutSubscriptionFormID,
												'type' => 'checkout-subscription'
											),
											admin_url( "admin.php" )
										); ?>" title="<?php _e( 'Edit', 'wp-full-stripe' ); ?>"><i class="fa fa-pencil fa-fw"></i></a>
										<span class="form-action-last">
											<button class="button delete" data-id="<?php echo $checkout_subscription_form->checkoutSubscriptionFormID; ?>" data-type="checkoutSubscriptionForm" title="<?php _e( 'Delete', 'wp-full-stripe' ); ?>">
												<i class="fa fa-trash-o fa-fw"></i>
											</button>
										</span>
									</td>
									<td class="column-name"><?php echo esc_html( $checkout_subscription_form->name ); ?></td>
									<td class="column-title"><?php echo esc_html( $checkout_subscription_form->companyName ); ?></td>
									<td class="column-plan_ids"><?php echo esc_html( $plans_label ); ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		<?php elseif ( $active_tab == 'plans' ): ?>
			<div class="" id="plans">
				<h2>
					<?php esc_html_e( 'Your Subscription Plans', 'wp-full-stripe' ); ?>
					<a class="page-title-action" href="<?php echo add_query_arg(
						array(
							'page' => 'fullstripe-create-plan'
						),
						admin_url( "admin.php" )
					); ?>" title="<?php esc_attr_e( 'Create Subscription Plan', 'wp-full-stripe' ); ?>"><i class="fa fa-plus fa-fw"></i><?php esc_html_e( 'Create Subscription Plan', 'wp-full-stripe' ); ?>
					</a>
					<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
				</h2>
				<?php if ( count( $plans ) === 0 ): ?>
					<p class="alert alert-info">
						<?php esc_html_e( 'You have no subscription plans created yet. Use the Subscription Plans tab to get started', 'wp-full-stripe' ); ?>
					</p>
				<?php else: ?>
					<table class="wp-list-table widefat fixed subscription-plans">
						<thead>
						<tr>
							<th class="manage-column column-action column-primary"><?php esc_html_e( 'Actions', 'wp-full-stripe' ); ?></th>
							<th class="manage-column column-id_display_name"><?php esc_html_e( 'ID', 'wp-full-stripe' ); ?>
								/ <?php esc_html_e( 'Display Name', 'wp-full-stripe' ); ?></th>
							<th class="manage-column column-amount_interval"><?php esc_html_e( 'Amount', 'wp-full-stripe' ); ?>
								/ <?php esc_html_e( 'Setup Fee', 'wp-full-stripe' ); ?></th>
							<th class="manage-column column-trial_duration"><?php esc_html_e( 'Trial', 'wp-full-stripe' ); ?>
								/ <?php esc_html_e( 'Duration', 'wp-full-stripe' ); ?></th>
						</tr>
						</thead>
						<tbody id="plansTable">
						<?php foreach ( $plans as $plan ): ?>
							<?php
							$setup_fee = 0;
							if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
								$setup_fee = $plan->metadata->setup_fee;
							}
							$interval_label = __( 'No interval', 'wp-full-stripe' );
							if ( $plan->interval === "year" ) {
								$interval_label = sprintf( _n( 'year', '%d years', $plan->interval_count, 'wp-full-stripe' ), number_format_i18n( $plan->interval_count ) );
							} elseif ( $plan->interval === "month" ) {
								$interval_label = sprintf( _n( 'month', '%d months', $plan->interval_count, 'wp-full-stripe' ), number_format_i18n( $plan->interval_count ) );
							} elseif ( $plan->interval === "week" ) {
								$interval_label = sprintf( _n( 'week', '%d weeks', $plan->interval_count, 'wp-full-stripe' ), number_format_i18n( $plan->interval_count ) );
							}
							?>
							<tr>
								<td class="column-action">
									<a class="button button-primary" href="<?php echo add_query_arg( array(
										'page' => 'fullstripe-edit-plan',
										'plan' => rawurlencode( $plan->id )
									), admin_url( "admin.php" ) ); ?>" title="<?php _e( 'Edit', 'wp-full-stripe' ); ?>"><i class="fa fa-pencil fa-fw"></i></a>
									<span class="form-action-last">
										<button class="button delete" data-id="<?php echo esc_attr( $plan->id ); ?>" data-type="subscriptionPlan" data-confirm="true" title="<?php _e( 'Delete', 'wp-full-stripe' ); ?>">
											<i class="fa fa-trash-o fa-fw"></i>
										</button>
									</span>
								</td>
								<td class="column-id_display_name">
									<b><?php echo esc_html( $plan->id ); ?></b><br>
									<a href="<?php echo add_query_arg( array(
										'page' => 'fullstripe-edit-plan',
										'plan' => rawurlencode( $plan->id )
									), admin_url( 'admin.php' ) ); ?>"><?php echo esc_html( $plan->name ); ?></a>
								</td>
								<td class="column-amount_interval">
									<b><?php
										$amount_label    = MM_WPFS::format_amount_with_currency( $plan->currency, $plan->amount );
										$setup_fee_label = MM_WPFS::format_amount_with_currency( $plan->currency, $setup_fee );
										echo $amount_label; ?>
										/ <?php echo esc_html( $interval_label ); ?></b><br>
									<?php if ( $setup_fee > 0 ): ?>
										<?php echo $setup_fee_label; ?>
									<?php else: ?>
										<?php esc_html_e( 'No setup fee', 'wp-full-stripe' ); ?>
									<?php endif; ?>
								</td>
								<td class="column-trial_duration">
									<?php
									if ( isset( $plan->trial_period_days ) ) {
										echo esc_html( sprintf( _n( "%d day", "%d days", $plan->trial_period_days, 'wp-full-stripe' ), $plan->trial_period_days ) );
									} else {
										esc_html_e( 'No Trial', 'wp-full-stripe' );
									}
									?><br>
									<?php
									$duration = __( 'Forever', 'wp-full-stripe' );
									if ( isset( $plan->metadata ) ) {
										if ( isset( $plan->metadata->cancellation_count ) ) {
											if ( is_numeric( $plan->metadata->cancellation_count ) ) {
												$cancellation_count = intval( $plan->metadata->cancellation_count );
												if ( $cancellation_count > 0 ) {
													$duration = sprintf( _n( '%d charge', '%d charges', $cancellation_count, 'wp-full-stripe' ), $cancellation_count );
												}
											}
										}
									}
									echo esc_html( $duration );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>