<?php

global $wpdb;
//get the data we need
$payment_forms  = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payment_forms;" );
$checkout_forms = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_checkout_forms;" );
$options        = get_option( 'fullstripe_options' );

$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'payments';

?>
<div class="wrap">
	<h2> <?php esc_html_e( 'Full Stripe Payments', 'wp-full-stripe' ); ?> </h2>

	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-payments&tab=payments' ); ?>" class="nav-tab <?php echo $active_tab == 'payments' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Payments', 'wp-full-stripe' ); ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ); ?>" class="nav-tab <?php echo $active_tab == 'forms' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Payment Forms', 'wp-full-stripe' ); ?></a>
	</h2>

	<div class="wpfs-tab-content">
		<?php if ( $active_tab == 'payments' ): ?>
			<div class="" id="payments">
				<h2>
					<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
				</h2>
				<form method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
					<label><?php esc_html_e( 'Customer: ', 'wp-full-stripe' ); ?></label><input type="text" name="customer" size="35" placeholder="<?php esc_attr_e( 'Enter name, email address, or stripe ID', 'wp-full-stripe' ); ?>" value="<?php echo isset( $_REQUEST['customer'] ) ? $_REQUEST['customer'] : ''; ?>">
					<label><?php esc_html_e( 'Payment: ', 'wp-full-stripe' ); ?></label><input type="text" name="payment" placeholder="<?php esc_attr_e( 'Enter charge ID', 'wp-full-stripe' ); ?>" value="<?php echo isset( $_REQUEST['payment'] ) ? $_REQUEST['payment'] : ''; ?>">
					<label><?php esc_html_e( 'Mode: ', 'wp-full-stripe' ); ?></label>
					<select name="mode">
						<option value="" <?php echo ! isset( $_REQUEST['mode'] ) || $_REQUEST['mode'] == '' ? 'selected' : ''; ?>><?php esc_html_e( 'All', 'wp-full-stripe' ); ?></option>
						<option value="live" <?php echo isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] == 'live' ? 'selected' : ''; ?>><?php esc_html_e( 'Live', 'wp-full-stripe' ); ?></option>
						<option value="test" <?php echo isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] == 'test' ? 'selected' : ''; ?>><?php esc_html_e( 'Test', 'wp-full-stripe' ); ?></option>
					</select>
					<span class="wpfs-search-actions">
						<button class="button button-primary"><?php esc_html_e( 'Search', 'wp-full-stripe' ); ?></button> <?php esc_html_e( 'or', 'wp-full-stripe' ); ?>
						<a href="<?php echo admin_url( 'admin.php?page=fullstripe-payments' ); ?>"><?php esc_html_e( 'Reset', 'wp-full-stripe' ); ?></a>
					</span>
					<?php
					/** @var WP_List_Table $table */
					$table->prepare_items();
					$table->display();
					?>
				</form>
			</div>
		<?php elseif ( $active_tab == 'forms' ): ?>
			<div class="" id="forms">
				<div style="min-height: 200px;">
					<h2><?php esc_html_e( 'Your Inline Forms', 'wp-full-stripe' ); ?>
						<a class="page-title-action" href="<?php echo add_query_arg(
							array(
								'page' => 'fullstripe-create-form',
								'type' => 'payment'
							),
							admin_url( "admin.php" )
						); ?>" title="<?php esc_attr_e( 'Create Inline Form', 'wp-full-stripe' ); ?>"><i class="fa fa-plus fa-fw"></i><?php esc_html_e( 'Create Inline Form', 'wp-full-stripe' ); ?>
						</a>
						<img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Loading...', 'wp-full-stripe' ); ?>" class="showLoading"/>
					</h2>
					<?php if ( count( $payment_forms ) === 0 ): ?>
						<p class="alert alert-info">
							<?php esc_html_e( "You have created no inline payment forms yet. Use the 'Create Inline Form' button to get started.", 'wp-full-stripe' ); ?>
						</p>
					<?php else: ?>
						<table class="wp-list-table widefat fixed payment-forms">
							<thead>
							<tr>
								<th class="manage-column column-action column-primary"><?php esc_html_e( 'Actions', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-title"><?php esc_html_e( 'Title', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-amount"><?php esc_html_e( 'Amount', 'wp-full-stripe' ); ?></th>
							</tr>
							</thead>
							<tbody id="paymentFormsTable">
							<?php foreach ( $payment_forms as $payment_form ): ?>
								<tr>
									<td class="column-action">
										<?php
										$shortcode = "[fullstripe_payment form=\"{$payment_form->name}\"]";
										?>
										<span id="shortcode-payment-tooltip__<?php echo $payment_form->paymentFormID; ?>" class="shortcode-tooltip" data-shortcode="<?php echo esc_attr( $shortcode ); ?>"></span>
										<a id="shortcode-payment__<?php echo $payment_form->paymentFormID; ?>" class="button button-primary shortcode-payment" data-form-id="<?php echo $payment_form->paymentFormID; ?>" title="<?php esc_attr_e( 'Shortcode', 'wp-full-stripe' ); ?>">
											<i class="fa fa-code fa-fw"></i>
										</a>
										<a class="button button-primary" href="<?php echo add_query_arg(
											array(
												'page' => 'fullstripe-edit-form',
												'form' => $payment_form->paymentFormID,
												'type' => 'payment'
											),
											admin_url( "admin.php" )
										); ?>" title="<?php esc_attr_e( 'Edit', 'wp-full-stripe' ); ?>"><i class="fa fa-pencil fa-fw"></i></a>
										<span class="form-action-last">
											<button class="button delete" data-id="<?php echo $payment_form->paymentFormID; ?>" data-type="paymentForm" title="<?php esc_attr_e( 'Delete', 'wp-full-stripe' ); ?>">
												<i class="fa fa-trash-o fa-fw"></i>
											</button>
										</span>
									</td>
									<td class="column-name"><?php echo esc_html( $payment_form->name ); ?></td>
									<td class="column-title"><?php echo esc_html( $payment_form->formTitle ); ?></td>
									<?php if ( $payment_form->customAmount == 'specified_amount' ): ?>
										<td class="column-amount"><?php echo MM_WPFS::format_amount_with_currency( $payment_form->currency, $payment_form->amount ); ?></td>
									<?php elseif ( $payment_form->customAmount == 'list_of_amounts' ): ?>
										<?php
										$table_cell                       = "<td class=\"column-amount\">";
										$initial_table_cell_markup_length = strlen( $table_cell );
										$list_of_amounts                  = json_decode( $payment_form->listOfAmounts );
										foreach ( $list_of_amounts as $list_element ) {
											$list_element_amount = $list_element[0];
											if ( strlen( $table_cell ) == $initial_table_cell_markup_length ) {
												$table_cell .= MM_WPFS::format_amount_with_currency( $payment_form->currency, $list_element_amount );
											} else {
												$table_cell .= ', ' . MM_WPFS::format_amount_with_currency( $payment_form->currency, $list_element_amount );
											}
										}
										if ( $payment_form->allowListOfAmountsCustom == '1' ) {
											$table_cell .= ', Custom';
										}
										$table_cell .= "</td>";
										echo $table_cell;
										?>
									<?php elseif ( $payment_form->customAmount == 'custom_amount' ): ?>
										<td class="column-amount"><?php esc_html_e( 'Custom', 'wp-full-stripe' ); ?></td>
									<?php else: ?>
										<td class="column-amount"><?php esc_html_e( 'Unknown', 'wp-full-stripe' ); ?></td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
				<div style="min-height: 200px;">
					<h2>
						<?php esc_html_e( 'Your Popup Forms', 'wp-full-stripe' ); ?>
						<a class="page-title-action" href="<?php echo add_query_arg(
							array(
								'page' => 'fullstripe-create-form',
								'type' => 'checkout'
							),
							admin_url( "admin.php" )
						); ?>" title="<?php esc_attr_e( 'Create Popup Form', 'wp-full-stripe' ); ?>"><i class="fa fa-plus fa-fw"></i><?php esc_html_e( 'Create Popup Form', 'wp-full-stripe' ); ?>
						</a>
					</h2>
					<?php if ( count( $checkout_forms ) === 0 ): ?>
						<p class="alert alert-info">
							<?php esc_html_e( "You have created no popup forms yet. Use the 'Create Popup Form' button to get started.", 'wp-full-stripe' ); ?>
						</p>
					<?php else: ?>
						<table class="wp-list-table widefat fixed checkout-forms">
							<thead>
							<tr>
								<th class="manage-column column-action column-primary"><?php esc_html_e( 'Actions', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-product"><?php esc_html_e( 'Product', 'wp-full-stripe' ); ?></th>
								<th class="manage-column column-amount"><?php esc_html_e( 'Amount', 'wp-full-stripe' ); ?></th>
							</tr>
							</thead>
							<tbody id="checkoutFormsTable">
							<?php foreach ( $checkout_forms as $checkout_form ): ?>
								<tr>
									<td>
										<?php
										$shortcode = "[fullstripe_checkout form=\"{$checkout_form->name}\"]"
										?>
										<span id="shortcode-checkout-tooltip__<?php echo $checkout_form->checkoutFormID; ?>" class="shortcode-tooltip" data-shortcode="<?php echo esc_attr( $shortcode ); ?>"></span>
										<a id="shortcode-checkout__<?php echo $checkout_form->checkoutFormID; ?>" class="button button-primary shortcode-checkout" data-form-id="<?php echo $checkout_form->checkoutFormID; ?>" title="<?php esc_attr_e( 'Shortcode', 'wp-full-stripe' ); ?>">
											<i class="fa fa-code fa-fw"></i>
										</a>
										<a class="button button-primary" href="<?php echo add_query_arg(
											array(
												'page' => 'fullstripe-edit-form',
												'form' => $checkout_form->checkoutFormID,
												'type' => 'checkout'
											),
											admin_url( "admin.php" )
										); ?>" title="<?php esc_attr_e( 'Edit', 'wp-full-stripe' ); ?>"><i class="fa fa-pencil fa-fw"></i></a>
										<span class="form-action-last">
											<button class="button delete" data-id="<?php echo $checkout_form->checkoutFormID; ?>" data-type="checkoutForm" title="<?php esc_attr_e( 'Delete', 'wp-full-stripe' ); ?>">
												<i class="fa fa-trash-o fa-fw"></i>
											</button>
										</span>
									</td>
									<td class="column-name"><?php echo esc_html( $checkout_form->name ); ?></td>
									<td class="column-product"><?php echo esc_html( $checkout_form->productDesc ); ?></td>
									<?php if ( $checkout_form->customAmount == 'specified_amount' ): ?>
										<td class="column-amount"><?php echo MM_WPFS::format_amount_with_currency( $checkout_form->currency, $checkout_form->amount ); ?></td>
									<?php elseif ( $checkout_form->customAmount == 'list_of_amounts' ): ?>
										<?php
										$table_cell                       = "<td class=\"column-amount\">";
										$initial_table_cell_markup_length = strlen( $table_cell );
										$list_of_amounts                  = json_decode( $checkout_form->listOfAmounts );
										foreach ( $list_of_amounts as $list_element ) {
											$list_element_amount = $list_element[0];
											if ( strlen( $table_cell ) == $initial_table_cell_markup_length ) {
												$table_cell .= MM_WPFS::format_amount_with_currency( $checkout_form->currency, $list_element_amount );
											} else {
												$table_cell .= ', ' . MM_WPFS::format_amount_with_currency( $checkout_form->currency, $list_element_amount );
											}
										}
										if ( $checkout_form->allowListOfAmountsCustom == '1' ) {
											$table_cell .= ', Custom';
										}
										$table_cell .= "</td>";
										echo $table_cell;
										?>
									<?php elseif ( $checkout_form->customAmount == 'custom_amount' ): ?>
										<td class="column-amount"><?php esc_html_e( 'Custom', 'wp-full-stripe' ); ?></td>
									<?php else: ?>
										<td class="column-amount"><?php esc_html_e( 'Unknown', 'wp-full-stripe' ); ?></td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
