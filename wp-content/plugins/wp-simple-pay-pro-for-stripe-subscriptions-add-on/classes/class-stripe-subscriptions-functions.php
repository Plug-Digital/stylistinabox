<?php

/**
 * Functions class - SP Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscriptions_Functions' ) ) {

	class Stripe_Subscriptions_Functions {

		protected static $instance = null;

		private function __construct() {

			// Since a trial does not actually create a charge we need to make a distinction between which kind of subscription is being charged and
			// load a success message accordingly.
			if ( isset( $_GET['charge'] ) ) {

				// When using this filter for further custom output, you'll need to increase the priority number higher
				// so it gets run even later.
				// TODO Add a separate filter here for Subscriptions only?
				add_filter( 'sc_payment_details', array( $this, 'add_payment_details' ), 10, 2 );

			} elseif ( isset( $_GET['trial'] ) ) {

				add_filter( 'the_content', array( $this, 'trial_payment_details' ) );
			}

			$sub_toggle = null;

			if ( isset( $_POST['sc_sub_id'] ) && isset( $_POST['wp-simple-pay'] ) ) {

				if ( isset( $_POST['sc_form_field'] ) ) {
					foreach ( $_POST['sc_form_field'] as $k => $v ) {
						if ( 'sc_sub_toggle' == substr( $k, 0, 13 ) ) {

							$v = strtolower( $v );

							if ( $v === 'yes' ) {
								$sub_toggle = true;
							} elseif ( $v === 'no' ) {
								$sub_toggle = false;
							}
						}
					}
				}

				// Checked
				if ( $sub_toggle === true ) {
					add_action( 'sc_do_charge', array( $this, 'do_charge' ) );
				} elseif ( $sub_toggle === false ) {
					// Exists but is not checked
					// Do nothing
				} else {
					// Standard procedure
					add_action( 'sc_do_charge', array( $this, 'do_charge' ) );
				}
			}

			add_filter( 'sc_before_payment_button', array( $this, 'validate_subscription' ) );
		}

		/**
		 * Add a success message for subscriptions with a trial.
		 */
		public function trial_payment_details( $content ) {

			global $sc_options;

			if ( isset( $_GET['trial'] ) ) {

				if ( null === $sc_options->get_setting_value( 'disable_success_message' ) ) {

					$cust_id   = $_GET['cust_id'];
					$sub_id    = $_GET['sub_id'];
					$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : 'false' );
					$is_above  = ( isset( $_GET['details_placement'] ) && $_GET['details_placement'] == 'below' ? false : true );

					Stripe_Checkout_Functions::set_key( $test_mode );

					$customer     = \Stripe\Customer::retrieve( $cust_id );
					$subscription = $customer->subscriptions->retrieve( $sub_id );

					$interval_count = $subscription->plan->interval_count;
					$interval       = $subscription->plan->interval;
					$amount         = $subscription->plan->amount;
					$currency       = $subscription->plan->currency;
					$product        = $subscription->metadata['product'];

					$html = '<div class="sc-payment-details-wrap">' . "\n";

					$html .= '<p>' . __( 'Congratulations, you have started your free trial!', 'simpay_sub' ) . '</p>' . "\n";
					$html .= '<p>' . __( 'Your card will not be charged until your trial is over.', 'simpay_sub' ) . '</p>' . "\n";

					$html .= '<p>' . sprintf( __( 'Your trial will end on: %1$s', 'simpay_sub' ), date_i18n( get_option( 'date_format' ), $subscription->trial_end ) ) . '</p>' . "\n";
					$html .= '<p>' . "\n";

					if ( ! empty( $product ) ) {
						$html .= __( "Here's what you purchased:", 'simpay_sub' ) . '<br />' . "\n";
						$html .= stripslashes( $product ) . '<br />' . "\n";
					}

					if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
						$html .= __( 'From: ', 'simpay_sub' ) . esc_html( $_GET['store_name'] ) . '<br />' . "\n";
					}

					$html .= '<br />' . "\n";

					$html .= '</p>' . "\n";

					$html .= '<p>' . __( 'You will be charged ', 'simpay_sub' );

					$html .= '<strong>' . Stripe_Checkout_Misc::to_formatted_amount( $amount, $currency ) . ' ' . strtoupper( $currency );

					// For interval count of 1, use $1.00/month format.
					// For a count > 1, use $1.00 every 3 months format.
					if ( $interval_count == 1 ) {
						$html .= '/' . $interval;
					} else {
						$html .= ' ' . __( 'every', 'simpay_sub' ) . ' ' . $interval_count . ' ' . $interval . 's';
					}

					$html .= '</strong>';

					$html .= __( ' when your trial is over.', 'simpay_sub' );

					$html .= '</p>' . "\n";

					$html .= '<p>' . sprintf( __( 'Your customer ID is: %1$s', 'simpay_sub' ), $cust_id ) . '</p>';

					$html .= '</div>';

					if ( ! $is_above ) {
						return $content . apply_filters( 'sc_trial_payment_details', $html, $subscription );
					} else {
						return apply_filters( 'sc_trial_payment_details', $html, $subscription ) . $content;
					}
				}
			}

			return $content;
		}

		/**
		 * Helper function to grab the subscription by ID and return the subscription object
		 *
		 * @since 1.0.0
		 */
		public static function get_subscription_by_id( $id, $test_mode = 'false' ) {

			global $sc_options;

			$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : $test_mode );

			Stripe_Checkout_Functions::set_key( $test_mode );

			try {
				$return = \Stripe\Plan::retrieve( trim( $id ) );

			} catch ( \Stripe\Error\Card $e ) {

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\Authentication $e ) {
				// Authentication with Stripe's API failed
				// (maybe you changed API keys recently)

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\ApiConnection $e ) {
				// Network communication with Stripe failed

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\Base $e ) {

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( Exception $e ) {
				// Something else happened, completely unrelated to Stripe
				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );
			}

			return $return;
		}

		// $details = charge object
		public function add_payment_details( $html, $details ) {

			if ( ! isset( $details->invoice ) ) {
				return $html;
			}

			$invoice        = \Stripe\Invoice::retrieve( $details->invoice );
			$customer       = \Stripe\Customer::retrieve( $details->customer );
			$subscription   = $customer->subscriptions->retrieve( $invoice->subscription );
			$interval_count = $subscription->plan->interval_count;
			$interval       = $subscription->plan->interval;

			$upcoming_invoice = \Stripe\Invoice::upcoming( array(
				'customer'     => $customer->id,
				'subscription' => $subscription->id,
			) );

			$recurring_amount = $upcoming_invoice->total;
			$starting_balance = $invoice->starting_balance;

			$html = '<div class="sc-payment-details-wrap">' . "\n";

			$html .= '<p>' . __( 'Congratulations. Your payment went through!', 'simpay_sub' ) . '</p>' . "\n";
			$html .= '<p>' . "\n";

			if ( ! empty( $details->description ) ) {
				$html .= __( "Here's what you purchased:", 'simpay_sub' ) . '<br />' . "\n";
				$html .= esc_html( $details->description ) . '<br />' . "\n";
			}

			if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
				$html .= __( 'From: ', 'simpay_sub' ) . esc_html( $_GET['store_name'] ) . '<br />' . "\n";
			}

			$html .= '<br />' . "\n";
			$html .= '<strong>' . __( 'Total Paid: ', 'simpay_sub' ) . Stripe_Checkout_Misc::to_formatted_amount( $details->amount, $details->currency ) . ' ' . strtoupper( $details->currency ) . '</strong>' . "\n";

			$html .= '</p>' . "\n";

			$html .= '<p>';

			if ( $starting_balance > 0 ) {
				$html .= sprintf( __( 'You have been charged a one-time setup fee of: %1$s %2$s', 'simpay_sub' ), Stripe_Checkout_Misc::to_formatted_amount( $starting_balance, $details->currency ), strtoupper( $details->currency ) ) . '<br />';
			}

			$html .= __( 'You will be charged ', 'simpay_sub' );

			// Use future recurring charge amount.
			$html .= Stripe_Checkout_Misc::to_formatted_amount( $recurring_amount, $details->currency ) . ' ' . strtoupper( $details->currency );

			// For interval count of 1, use $1.00/month format.
			// For a count > 1, use $1.00 every 3 months format.
			if ( $interval_count == 1 ) {
				$html .= '/' . $interval . '.';
			} else {
				$html .= ' ' . __( 'every', 'simpay_sub' ) . ' ' . $interval_count . ' ' . $interval . 's.';
			}

			$html .= '</p>' . "\n";

			// Max occurrences output.
			if ( isset( $subscription->max_occurrences ) ) {
				$html .= '<p>' . sprintf( __( 'You will be charged a total of %1$s times.', 'simpay_sub' ), $subscription->max_occurrences ) . '</p>' . "\n";
			}

			// Display Stripe transaction ID.
			$html .= '<p>' . sprintf( __( 'Your transaction ID is: %s', 'simpay_sub' ), $details->id ) . '</p>';

			$html .= '</div>';

			return $html;
		}

		public static function print_errors( $err = array() ) {

			$message = '';

			if ( current_user_can( 'manage_options' ) ) {
				foreach ( $err as $k => $v ) {
					$message = '<h6>' . $k . ': ' . $v . '</h6>';
				}
			} else {
				$message = '<h6>' . __( 'An error has occurred. If the problem persists, please contact a site administrator.', 'simpay_sub' ) . '</h6>';
			}

			return apply_filters( 'sc_error_message', $message );
		}

		public function do_charge() {

			if ( wp_verify_nonce( $_POST['wp-simple-pay-pro-nonce'], 'charge_card' ) ) {
				global $sc_options;

				// Set redirect
				$redirect      = $_POST['sc-redirect'];
				$fail_redirect = $_POST['sc-redirect-fail'];
				$failed        = null;

				// Get the credit card details submitted by the form
				$token             = $_POST['stripeToken'];
				$payment_email     = $_POST['stripeEmail'];
				$amount            = $_POST['sc-amount'];
				$description       = ( isset( $_POST['sc-description'] ) ? $_POST['sc-description'] : '' );
				$store_name        = ( isset( $_POST['sc-name'] ) ? $_POST['sc-name'] : '' );
				$currency          = ( isset( $_POST['sc-currency'] ) ? $_POST['sc-currency'] : '' );
				$test_mode         = ( isset( $_POST['sc_test_mode'] ) ? $_POST['sc_test_mode'] : 'false' );
				$details_placement = ( isset( $_POST['sc-details-placement'] ) ? $_POST['sc-details-placement'] : '' );

				// sub_id is same as existing plan ID.
				// Set to "custom" if non-existent.
				$sub_id                = ( isset( $_POST['sc_sub_id'] ) && ! empty( $_POST['sc_sub_id'] ) ? $_POST['sc_sub_id'] : 'custom' );
				$interval              = ( isset( $_POST['sc_sub_interval'] ) ? $_POST['sc_sub_interval'] : 'month' );
				$interval_count        = ( isset( $_POST['sc_sub_interval_count'] ) ? $_POST['sc_sub_interval_count'] : 1 );
				$statement_description = ( isset( $_POST['sc_sub_statement_description'] ) ? $_POST['sc_sub_statement_description'] : '' );
				$setup_fee             = ( isset( $_POST['sc_sub_setup_fee'] ) ? $_POST['sc_sub_setup_fee'] : 0 );
				$coupon                = ( isset( $_POST['sc_coup_coupon_applied'] ) ? $_POST['sc_coup_coupon_applied'] : '' );
				$max_occurrences       = ( isset( $_POST['sc_sub_max_occurrences'] ) ? $_POST['sc_sub_max_occurrences'] : null );
				$quantity              = ( isset( $_POST['sc_sub_quantity'] ) ? $_POST['sc_sub_quantity'] : 1 );
				$customer              = null;
				$subscription          = null;
				$charge                = array();

				Stripe_Checkout_Functions::set_key( $test_mode );

				try {

					// Init metadata object with filter allowing it to be overridden/added to.
					$meta = apply_filters( 'sc_meta_values', array() );

					// Add setup fee to metadata.
					if ( ! empty( $setup_fee ) ) {
						$meta['Setup Fee'] = Stripe_Checkout_Misc::to_formatted_amount( $setup_fee, $currency );
					}

					// Add max occurrencess to metadata.
					if ( ! empty( $max_occurrences ) ) {
						$meta['Max Occur'] = $max_occurrences;
					}

					// Init customer ID with filter allowing it to be overridden.
					$customer_id = apply_filters( 'sc_customer_id', null );

					// Create new customer unless there's an existing customer ID set through filters.
					if ( empty( $customer_id ) ) {
						$customer = \Stripe\Customer::create( array(
							'email' => $payment_email,
							'card'  => $token,
						) );
					} else {
						$customer = \Stripe\Customer::retrieve( $customer_id );
					}

					// Add setup fee to customer in the form of account balance.
					// https://support.stripe.com/questions/subscription-setup-fees
					if ( ! empty( $setup_fee ) ) {
						$customer->account_balance = $setup_fee;
						$customer->save();
					}

					$create_sub_args = array();

					// Create custom subscription plan.
					if ( $sub_id == 'custom' ) {

						$timestamp = time();

						$plan_id = $payment_email . '_' . $amount . '_' . $timestamp;

						// Set plan name to product description.
						$plan_name = $description;

						// If description is empty, set to generated plan name.
						if ( empty( $plan_name ) ) {
							$plan_name = Stripe_Checkout_Misc::to_formatted_amount( $amount, $currency ) . ' ' . strtoupper( $currency ) . '/' . $interval . ' plan';
						}

						// Create the plan.
						$plan_args = array(
							'amount'         => $amount,
							'interval'       => $interval,
							'interval_count' => $interval_count,
							'currency'       => $currency,
							'id'             => $plan_id,
							'name'           => $plan_name,
						);

						if ( ! empty( $statement_description ) ) {
							$plan_args['statement_descriptor'] = $statement_description;
						}

						// Need to create plan here but don't need to retreive anything from it since we already have the plan ID.
						\Stripe\Plan::create( $plan_args );

						// Add custom plan ID to sub args.
						$create_sub_args['plan'] = $plan_id;

					} else {

						// Use existing subscription plan.

						// Add existing plan ID to sub args. sub_id is same as existing plan ID.
						$create_sub_args['plan'] = $sub_id;

						// Add coupon to subscription args.
						// Not supported for UEA.
						// Don't save coupon to customer since they could have multiple subscriptions.
						if ( ! empty( $coupon ) ) {
							$create_sub_args['coupon'] = $coupon;
						}
					}

					// Add max occurrences (installment plan) if specified.
					if ( ! empty( $max_occurrences ) ) {
						$create_sub_args['max_occurrences'] = $max_occurrences;
					}

					// Add quantity from custom field if specified.
					$create_sub_args['quantity'] = $quantity;

					// Add customer ID to params.
					$create_sub_args['customer'] = $customer->id;

					// Finally create the subscription. New way as of 5/10/16.
					$subscription = \Stripe\Subscription::create( $create_sub_args );

					// Get trial period days from plan object.
					$trial_days = $subscription->plan->trial_period_days;

					if ( empty( $trial_days ) || ! empty( $setup_fee ) ) {

						// If this is not a trial and/or there's a setup fee a charge was automatically made.

						// Get last recorded charge based on customer ID.
						$charge_list = \Stripe\Charge::all( array(
							'customer' => $customer->id,
							'limit'    => 1,
						) );

						$charge  = $charge_list->data[0];
						$invoice = \Stripe\Invoice::retrieve( $charge->invoice );

						// We want to add the metadata and description to the charge so that users can still view metadata sent with
						// a subscription + custom fields the same way that they would normally view it without subscriptions installed.
						// Besides the charge, add the metadata & description to the invoice & subscription.
						if ( ! empty( $meta ) ) {
							$charge->metadata       = $meta;
							$invoice->metadata      = $meta;
							$subscription->metadata = $meta;
						}
						if ( ! empty( $description ) ) {
							$charge->description  = $description;
							$invoice->description = $description;
							// Subscriptions don't have a description property, so merge a new "Description" metadata field containing the description.
							$subscription->metadata = array_merge( $meta, array( 'Description' => $description ) );
						}

						// Save all 3 objects if extra data exists.
						if ( ! empty( $meta ) || ! empty( $description ) ) {
							$charge->save();
							$invoice->save();
							$subscription->save();
						}

						$query_args = array(
							'charge'     => $charge->id,
							'store_name' => sanitize_text_field( $store_name ),
						);

						$failed = false;

					} else {

						// If this is a trial and there's no setup fee we won't have an initial charge.

						// Add the metadata & description to the subscription.
						// There is no charge to update.
						// Upcoming invoices cannot be edited.

						if ( ! empty( $meta ) ) {
							$subscription->metadata = $meta;
						}
						if ( ! empty( $description ) ) {
							// Subscriptions don't have a description property, so merge a new "Product" field containing the description.
							$subscription->metadata = array_merge( $meta, array( 'Product' => $description ) );
						}

						// Save subscription object if extra data exists.
						if ( ! empty( $meta ) || ! empty( $description ) ) {
							$subscription->save();
						}

						// Build querystring for payment success page.
						$query_args = array(
							'cust_id'    => $customer->id,
							'sub_id'     => $subscription->id,
							'store_name' => sanitize_text_field( $store_name ),
						);

						$failed = false;
					}

				} catch ( Exception $e ) {

					// Something else happened, completely unrelated to Stripe

					$redirect = $fail_redirect;

					$failed = true;

					$e = $e->getJsonBody();

					$query_args = array( 'sub' => true, 'error_code' => $e['error']['type'], 'charge_failed' => true );

					// TODO Uncomment to show Stripe error message before redirect.
					//echo $description . '<br/>';
					//echo '<pre>' . print_r( $e['error']['message'], true ) . '</pre>';
					//exit;
				}

				unset( $_POST['stripeToken'] );

				do_action( 'sc_redirect_before' );

				if ( $test_mode == 'true' ) {
					$query_args['test_mode'] = 'true';
				}

				if ( 'below' == $details_placement ) {
					$query_args['details_placement'] = $details_placement;
				}

				if ( ! empty( $trial_days ) && empty( $setup_fee ) ) {
					$query_args['trial'] = 1;
				}

				wp_redirect( esc_url_raw( add_query_arg( apply_filters( 'sc_redirect_args', $query_args, $charge ), apply_filters( 'sc_redirect', $redirect, $failed ) ) ) );

				exit;
			}
		}

		public function validate_subscription( $html ) {

			$sub = Shortcode_Tracker::shortcode_exists_current( 'stripe_subscription' );
			$uea = Shortcode_Tracker::shortcode_exists_current( 'stripe_amount' );

			// Neither exist so we can just exit now
			if ( $sub === false && $uea === false ) {
				return $html;
			}

			$sub_id       = isset( $sub['attr']['id'] ) ? true : false;
			$sub_children = isset( $sub['children'] ) ? true : false;
			$use_amount   = ( isset( $sub['attr']['use_amount'] ) && $sub['attr']['use_amount'] == 'true' ) ? true : false;

			// Can't have both an ID and UEA
			if ( ( $sub_id || $sub_children ) && $uea ) {
				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'Subscriptions must specify a plan ID or include a user-entered amount field. You cannot include both or omit both.', 'simpay_sub' ) . '</h6>' );
				}
			}

			if ( empty( $sub_id ) && ( $uea || $use_amount ) && $sub != false ) {

				$interval              = ( isset( $sub['attr']['interval'] ) ? $sub['attr']['interval'] : 'month' );
				$interval_count        = ( isset( $sub['attr']['interval_count'] ) ? $sub['attr']['interval_count'] : 1 );
				$statement_description = ( isset( $sub['attr']['statement_description'] ) ? $sub['attr']['statement_description'] : '' );

				$html .= '<input type="hidden" name="sc_sub_id" class="sc_sub_id" value="" />';
				$html .= '<input type="hidden" name="sc_sub_interval" class="sc_sub_interval" value="' . $interval . '" />';
				$html .= '<input type="hidden" name="sc_sub_interval_count" class="sc_sub_interval_count" value="' . $interval_count . '" />';
				$html .= '<input type="hidden" name="sc_sub_statement_description" class="sc_sub_statement_description" value="' . $statement_description . '" />';
			}

			if ( empty( $sub_id ) && ! $uea && empty( $sub_children ) && $use_amount === false ) {
				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'Subscriptions must specify a plan ID or include a user-entered amount field. You cannot include both or omit both.', 'simpay_sub' ) . '</h6>' );
				}
			}

			return $html;
		}

		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}
