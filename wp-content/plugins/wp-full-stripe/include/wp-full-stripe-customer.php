<?php

/**
 * Class MM_WPFS_Customer deals with customer front-end input i.e. payment forms submission
 */
class MM_WPFS_Customer {
	const REQUEST_PARAM_NAME_WPFS_TRANSACTION_DATA_KEY = 'wpfs_td_key';

	/* @var $stripe MM_WPFS_Stripe */
	private $stripe = null;

	/* @var $db MM_WPFS_Database */
	private $db = null;

	/* @var $mailer MM_WPFS_Mailer */
	private $mailer = null;

	/* @var MM_WPFS_TransactionDataService */
	private $transaction_data_service = null;

	public function __construct() {
		$this->db                       = new MM_WPFS_Database();
		$this->mailer                   = new MM_WPFS_Mailer();
		$this->stripe                   = new MM_WPFS_Stripe();
		$this->transaction_data_service = new MM_WPFS_TransactionDataService();
		$this->hooks();
	}

	private function hooks() {
		add_action( 'wp_ajax_wp_full_stripe_payment_charge', array( $this, 'fullstripe_payment_charge' ) );
		add_action( 'wp_ajax_nopriv_wp_full_stripe_payment_charge', array( $this, 'fullstripe_payment_charge' ) );
		add_action( 'wp_ajax_wp_full_stripe_subscription_charge', array( $this, 'fullstripe_subscription_charge' ) );
		add_action( 'wp_ajax_nopriv_wp_full_stripe_subscription_charge', array(
			$this,
			'fullstripe_subscription_charge'
		) );
		add_action( 'wp_ajax_wp_full_stripe_check_coupon', array( $this, 'fullstripe_check_coupon' ) );
		add_action( 'wp_ajax_nopriv_wp_full_stripe_check_coupon', array( $this, 'fullstripe_check_coupon' ) );
		add_action( 'wp_ajax_fullstripe_checkout_form_charge', array( $this, 'fullstripe_checkout_charge' ) );
		add_action( 'wp_ajax_nopriv_fullstripe_checkout_form_charge', array( $this, 'fullstripe_checkout_charge' ) );
		add_action( 'wp_ajax_fullstripe_checkout_subscription_form_charge', array(
			$this,
			'fullstripe_checkout_subscription_charge'
		) );
		add_action( 'wp_ajax_nopriv_fullstripe_checkout_subscription_form_charge', array(
			$this,
			'fullstripe_checkout_subscription_charge'
		) );
	}

	function fullstripe_payment_charge() {

		// tnagy read data from POST
		$formName  = isset( $_POST['formName'] ) ? $_POST['formName'] : null;
		$formNonce = isset( $_POST['formNonce'] ) ? $_POST['formNonce'] : null;

		if ( ! is_null( $formName ) && ! is_null( $formNonce ) ) {
			$paymentForm = $this->db->get_payment_form_by_name( $formName );
			if ( isset( $paymentForm ) && wp_verify_nonce( $formNonce, $paymentForm->paymentFormID ) ) {

				$currencyArray           = MM_WPFS::get_currency_for( $paymentForm->currency );
				$productName             = '';
				$chargeDescription       = sprintf( __( 'Payment for %s', 'wp-full-stripe' ), $productName );
				$doRedirect              = $paymentForm->redirectOnSuccess;
				$redirectPostID          = $paymentForm->redirectPostID;
				$redirectUrl             = $paymentForm->redirectUrl;
				$redirectToPageOrPost    = $paymentForm->redirectToPageOrPost;
				$showAddress             = $paymentForm->showAddress;
				$sendReceipt             = $paymentForm->sendEmailReceipt;
				$showDetailedSuccessPage = $paymentForm->showDetailedSuccessPage;
				$customInputTitle        = $paymentForm->customInputTitle;
				$customInputs            = $paymentForm->customInputs;
				$customInputRequired     = $paymentForm->customInputRequired;

				$allowListOfAmountsCustom = isset( $_POST['allowListOfAmountsCustom'] ) ? $_POST['allowListOfAmountsCustom'] : 0;

				// tnagy read user input
				$stripeToken   = $_POST['stripeToken'];
				$customerEmail = isset( $_POST['fullstripe_email'] ) ? sanitize_text_field( $_POST['fullstripe_email'] ) : '';
				$customerName  = sanitize_text_field( $_POST['fullstripe_name'] );
				$amount        = null;
				if ( $paymentForm->customAmount == 'specified_amount' ) {
					$amount = $paymentForm->amount;
				} elseif ( $paymentForm->customAmount == 'list_of_amounts' ) {
					if ( $paymentForm->allowListOfAmountsCustom == 1 && 'other' == $_POST['fullstripe_custom_amount'] ) {
						$amount = MM_WPFS::parse_amount( $paymentForm->currency, $_POST['fullstripe_list_of_amounts_custom_amount'] );
					} else {
						$amountIndex   = $_POST['fullstripe_amount_index'];
						$listOfAmounts = json_decode( $paymentForm->listOfAmounts );
						if ( count( $listOfAmounts ) > $amountIndex ) {
							$listElement                 = $listOfAmounts[ $amountIndex ];
							$amount                      = $listElement[0];
							$listElementDescription      = $listElement[1];
							$listElementAmountLabel      = MM_WPFS::format_amount_with_currency( $paymentForm->currency, $amount );
							$listElementDescriptionLabel = MM_WPFS::translate_label( $listElementDescription );
							if ( strpos( $listElementDescription, '{amount}' ) !== false ) {
								$listElementDescriptionLabel = str_replace( '{amount}', $listElementAmountLabel, $listElementDescriptionLabel );
							}
							$productName = $listElementDescriptionLabel;
						}
					}
				} elseif ( $paymentForm->customAmount == 'custom_amount' ) {
					$amount = MM_WPFS::parse_amount( $paymentForm->currency, $_POST['fullstripe_custom_amount'] );
				}

				$customInputValues = isset( $_POST['fullstripe_custom_input'] ) ? $_POST['fullstripe_custom_input'] : array();

				// tnagy read billing details
				$billingAddressCountry = isset( $_POST['fullstripe_address_country'] ) ? MM_WPFS::get_country_name_for( sanitize_text_field( $_POST['fullstripe_address_country'] ) ) : '';
				$billingAddressZip     = isset( $_POST['fullstripe_address_zip'] ) ? sanitize_text_field( $_POST['fullstripe_address_zip'] ) : '';
				$billingAddressState   = isset( $_POST['fullstripe_address_state'] ) ? sanitize_text_field( $_POST['fullstripe_address_state'] ) : '';
				$billingAddressLine1   = isset( $_POST['fullstripe_address_line1'] ) ? sanitize_text_field( $_POST['fullstripe_address_line1'] ) : '';
				$billingAddressLine2   = isset( $_POST['fullstripe_address_line2'] ) ? sanitize_text_field( $_POST['fullstripe_address_line2'] ) : '';
				$billingAddressCity    = isset( $_POST['fullstripe_address_city'] ) ? sanitize_text_field( $_POST['fullstripe_address_city'] ) : '';

				// tnagy validate user input
				$valid = true;
				if ( ! is_numeric( trim( $amount ) ) || $amount <= 0 ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'The payment amount is invalid, please only use positive numbers and a decimal point.', 'wp-full-stripe' )
					);
				}
				if ( ! filter_var( $customerEmail, FILTER_VALIDATE_EMAIL ) ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'Please enter a valid email address.', 'wp-full-stripe' )
					);
				}
				if ( $valid && $showAddress == 1 ) {
					$valid = $this->is_valid_address( $billingAddressLine1, $billingAddressCity, $billingAddressZip, $billingAddressCountry );
					if ( ! $valid ) {
						$return = array(
							'success' => false,
							'msg'     => __( 'Please enter a valid billing address.', 'wp-full-stripe' )
						);
					}
				}
				if ( $valid && $customInputRequired == 1 ) {

					if ( $customInputs == null ) {
						if ( is_null( $customInputValues ) || ( trim( $customInputValues ) == false ) ) {
							$valid  = false;
							$return = array(
								'success' => false,
								'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $customInputTitle ) )
							);
						}
					} else {
						$labels = explode( '{{', $customInputs );
						foreach ( $labels as $i => $label ) {
							if ( $valid && ( is_null( $customInputValues[ $i ] ) || ( trim( $customInputValues[ $i ] ) == false ) ) ) {
								$valid  = false;
								$return = array(
									'success' => false,
									'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $label ) )
								);
							}
						}
					}
				}

				if ( $valid ) {

					$description = sprintf( __( 'Payment from %s on form: %s', 'wp-full-stripe' ), $customerName, $formName );
					$metadata    = array(
						'customer_name'           => $customerName,
						'customer_email'          => $customerEmail,
						'billing_address_line1'   => $billingAddressLine1,
						'billing_address_line2'   => $billingAddressLine2,
						'billing_address_city'    => $billingAddressCity,
						'billing_address_state'   => $billingAddressState,
						'billing_address_country' => $billingAddressCountry,
						'billing_address_zip'     => $billingAddressZip
					);

					try {

						$sendPluginEmail = true;
						$options         = get_option( 'fullstripe_options' );
						if ( $options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 && isset( $_POST['fullstripe_email'] ) ) {
							$sendPluginEmail = false;
						}

						do_action( 'fullstripe_before_payment_charge', $amount );
						$stripeCustomer      = $this->create_or_get_customer( $stripeToken, $customerEmail, $metadata, ( $options['apiMode'] === 'live' ) );
						$metadata            = $this->add_custom_inputs( $metadata, $customInputs, $customInputValues );
						$charge              = $this->stripe->charge_customer( $stripeCustomer->id, $paymentForm->currency, $amount, $description, $metadata, ( $sendPluginEmail == false && $sendReceipt == true ? $customerEmail : null ) );
						$charge['wpfs_form'] = $formName;
						do_action( 'fullstripe_after_payment_charge', $charge );

						$address = MM_WPFS_Utils::prepare_billing_address_data( $billingAddressLine1, $billingAddressLine2, $billingAddressCity, $billingAddressState, $billingAddressCountry, $billingAddressZip );
						$this->db->fullstripe_insert_payment( $charge, $address, $stripeCustomer->id, $customerName, $customerEmail, $formName, 'payment' /* form_type */ );

						$return = array( 'success' => true, 'msg' => __( 'Payment Successful!', 'wp-full-stripe' ) );
						if ( $doRedirect == 1 ) {
							if ( $redirectToPageOrPost == 1 ) {
								if ( $redirectPostID != 0 ) {
									$pageOrPostUrl = get_page_link( $redirectPostID );
									if ( $showDetailedSuccessPage == 1 ) {
										$transactionDataKey = $this->transaction_data_service->store( MM_WPFS_TransactionDataService::create_payment_data( $customerEmail, $paymentForm->currency, $amount, $productName, $charge['source']['name'], $address, $customInputValues ) );
										$pageOrPostUrl      = add_query_arg( array( self::REQUEST_PARAM_NAME_WPFS_TRANSACTION_DATA_KEY => $transactionDataKey ), $pageOrPostUrl );
									}
									$return['redirect']    = true;
									$return['redirectURL'] = $pageOrPostUrl;
								} else {
									MM_WPFS_Utils::log( "Inconsistent form data: formName=$formName, doRedirect=$doRedirect, redirectPostID=$redirectPostID" );
								}
							} else {
								$return['redirect']    = true;
								$return['redirectURL'] = $redirectUrl;
							}
						}

						if ( $sendPluginEmail && $sendReceipt == 1 ) {
							$this->mailer->send_payment_email_receipt( $customerEmail, $paymentForm->currency, $amount, $charge['source']['name'], $address, $productName, $customInputValues );
						}

					} catch ( \Stripe\Error\Card $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$message = $this->stripe->resolve_error_message_by_code( $e->getCode() );
						if ( is_null( $message ) ) {
							$message = MM_WPFS::translate_label( $e->getMessage() );
						}
						$return = array(
							'success' => false,
							'msg'     => $message,
							'ex_msg'  => $e->getMessage()
						);
					} catch ( Exception $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => MM_WPFS::translate_label( $e->getMessage() ),
							'ex_msg'  => $e->getMessage()
						);
					}
				} else {
					if ( ! isset( $return ) ) {
						$errorMessage = 'Incorrect data submitted';
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => __( 'Incorrect data submitted.', 'wp-full-stripe' )
						);
					}
				}
			} else {
				$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s) or form not found', $formName, $formNonce );
				MM_WPFS_Utils::log( $errorMessage );
				$return = array(
					'success' => false,
					'msg'     => __( 'Invalid form name or form nonce or form not found', 'wp-full-stripe' )
				);
			}
		} else {
			$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s)', $formName, $formNonce );
			MM_WPFS_Utils::log( $errorMessage );
			$return = array(
				'success' => false,
				'msg'     => __( 'Invalid form name or form nonce', 'wp-full-stripe' )
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( apply_filters( 'fullstripe_payment_charge_return_message', $return ) );
		exit;
	}

	private function is_valid_address( $address1, $city, $zip, $country ) {
		$valid = true;
		if ( $address1 == '' || $city == '' || $zip == '' || $country == '' ) {
			$valid = false;
		}

		return $valid;
	}

	private function create_or_get_customer( $token, $email, $metadata, $livemode = true ) {
		$customer = $this->find_existing_stripe_customer_by_email( $email, $livemode );

		if ( ! isset( $customer ) ) {
			return $this->stripe->create_customer_with_source( $token, $email, $metadata );
		} else {
			// update and return existing customer to charge
			return $this->stripe->add_customer_source( $customer['stripeCustomerID'], $token );
		}
	}

	private function find_existing_stripe_customer_by_email( $email, $livemode ) {
		$customers = $this->db->get_existing_stripe_customers_by_email( $email, $livemode );

		$res = null;
		foreach ( $customers as $customer ) {
			$stripeCustomer = null;
			try {
				$stripeCustomer = $this->stripe->retrieve_customer( $customer['stripeCustomerID'] );
			} catch ( Exception $ex ) {
				//-- Let it just fall through, we will check for isset below
			}

			if ( isset( $stripeCustomer ) && ( ! isset( $stripeCustomer->deleted ) || ! $stripeCustomer->deleted ) ) {
				$res = $customer;
				break;
			}
		}

		return $res;
	}

	/**
	 * Insert the inputs into the metadata
	 *
	 * @param $metadata
	 * @param $customInputs
	 * @param $customInputValues
	 *
	 * @return mixed
	 */
	private function add_custom_inputs( $metadata, $customInputs, $customInputValues ) {
		// if not set, it's the old version with just one value
		if ( $customInputs == null ) {
			$metadata['custom_input'] = is_array( $customInputValues ) ? implode( ",", $customInputValues ) : $customInputValues;
		} else {
			$labels = explode( '{{', $customInputs );
			foreach ( $labels as $i => $label ) {
				$metadata[ $label ] = $customInputValues[ $i ];
			}
		}

		return $metadata;
	}

	function fullstripe_subscription_charge() {

		// tnagy read data from POST
		$formName  = isset( $_POST['formName'] ) ? $_POST['formName'] : null;
		$formNonce = isset( $_POST['formNonce'] ) ? $_POST['formNonce'] : null;

		if ( ! is_null( $formName ) && ! is_null( $formNonce ) ) {
			$subscriptionForm = $this->db->get_subscription_form_by_name( $formName );
			if ( isset( $subscriptionForm ) && wp_verify_nonce( $formNonce, $subscriptionForm->subscriptionFormID ) ) {
				$productName             = '';
				$doRedirect              = $subscriptionForm->redirectOnSuccess;
				$redirectPostID          = $subscriptionForm->redirectPostID;
				$redirectUrl             = $subscriptionForm->redirectUrl;
				$redirectToPageOrPost    = $subscriptionForm->redirectToPageOrPost;
				$sendReceipt             = $subscriptionForm->sendEmailReceipt;
				$showAddress             = $subscriptionForm->showAddress;
				$customInputTitle        = $subscriptionForm->customInputTitle;
				$customInputs            = $subscriptionForm->customInputs;
				$customInputRequired     = $subscriptionForm->customInputRequired;
				$showDetailedSuccessPage = $subscriptionForm->showDetailedSuccessPage;

				// tnagy read user input
				$stripeToken             = $_POST['stripeToken'];
				$customerEmail           = isset( $_POST['fullstripe_email'] ) ? $_POST['fullstripe_email'] : '';
				$cardholderName          = sanitize_text_field( $_POST['fullstripe_name'] );
				$planID                  = stripslashes( html_entity_decode( $_POST['fullstripe_plan'] ) );
				$plan                    = $this->stripe->retrieve_plan( $planID );
				$couponCode              = isset( $_POST['fullstripe_coupon_input'] ) ? $_POST['fullstripe_coupon_input'] : '';
				$amountWithCouponApplied = isset( $_POST['amount_with_coupon_applied'] ) && is_numeric( $_POST['amount_with_coupon_applied'] ) ? $_POST['amount_with_coupon_applied'] : null;
				$customInputValues       = isset( $_POST['fullstripe_custom_input'] ) ? $_POST['fullstripe_custom_input'] : array();

				// tnagy read billing details
				$address1 = isset( $_POST['fullstripe_address_line1'] ) ? sanitize_text_field( $_POST['fullstripe_address_line1'] ) : '';
				$address2 = isset( $_POST['fullstripe_address_line2'] ) ? sanitize_text_field( $_POST['fullstripe_address_line2'] ) : '';
				$city     = isset( $_POST['fullstripe_address_city'] ) ? sanitize_text_field( $_POST['fullstripe_address_city'] ) : '';
				$state    = isset( $_POST['fullstripe_address_state'] ) ? sanitize_text_field( $_POST['fullstripe_address_state'] ) : '';
				$country  = isset( $_POST['fullstripe_address_country'] ) ? MM_WPFS::get_country_name_for( sanitize_text_field( $_POST['fullstripe_address_country'] ) ) : '';
				$zip      = isset( $_POST['fullstripe_address_zip'] ) ? sanitize_text_field( $_POST['fullstripe_address_zip'] ) : '';

				$valid = true;
				if ( is_null( $plan ) ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'Invalid plan selected, please contact the site administrator.', 'wp-full-stripe' )
					);
				}
				if ( ! filter_var( $customerEmail, FILTER_VALIDATE_EMAIL ) ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'Please enter a valid email address.', 'wp-full-stripe' )
					);
				}
				if ( $valid && $showAddress == 1 ) {
					$valid = $this->is_valid_address( $address1, $city, $zip, $country );
					if ( ! $valid ) {
						$return = array(
							'success' => false,
							'msg'     => __( 'Please enter a valid billing address.', 'wp-full-stripe' )
						);
					}
				}
				if ( $valid && $customInputRequired == 1 ) {
					if ( $customInputs == null ) {
						if ( is_null( $customInputValues ) || ( trim( $customInputValues ) == false ) ) {
							$valid  = false;
							$return = array(
								'success' => false,
								'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $customInputTitle ) )
							);
						}
					} else {
						$labels = explode( '{{', $customInputs );
						foreach ( $labels as $i => $label ) {
							if ( $valid && ( is_null( $customInputValues[ $i ] ) || ( trim( $customInputValues[ $i ] ) == false ) ) ) {
								$valid  = false;
								$return = array(
									'success' => false,
									'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $label ) )
								);
							}
						}
					}
				}

				if ( $valid ) {

					$metadata = array(
						'customer_name'           => $cardholderName,
						'customer_email'          => $customerEmail,
						'billing_address_line1'   => $address1,
						'billing_address_line2'   => $address2,
						'billing_address_city'    => $city,
						'billing_address_state'   => $state,
						'billing_address_country' => $country,
						'billing_address_zip'     => $zip,
					);
					$metadata = $this->add_custom_inputs( $metadata, $customInputs, $customInputValues );

					try {

						$sendPluginEmail = true;
						$options         = get_option( 'fullstripe_options' );
						if ( $options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 ) {
							$sendPluginEmail = false;
						}

						// Check if we already have a customer created from a previous time
						$stripeCustomer = $this->find_existing_stripe_customer_by_email( $customerEmail, ( $options['apiMode'] === 'live' ) );
						do_action( 'fullstripe_before_subscription_charge', $planID );

						$setupFee = 0;
						if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
							$setupFee = $plan->metadata->setup_fee;
						}
						$billingAddress  = MM_WPFS_Utils::prepare_billing_address_data( $address1, $address2, $city, $state, $country, $zip );
						$transactionData = MM_WPFS_TransactionDataService::create_subscription_data( $customerEmail, $plan->name, $plan->currency, is_null( $amountWithCouponApplied ) ? $plan->amount : $amountWithCouponApplied, $setupFee, $productName, $cardholderName, $billingAddress, $customInputValues );

						if ( $stripeCustomer && $stripeCustomer['stripeCustomerID'] ) {
							/** @noinspection PhpUnusedLocalVariableInspection */
							$subscription = $this->stripe->subscribe_existing( $stripeCustomer['stripeCustomerID'], $planID, $stripeToken, $couponCode, $setupFee, $metadata );
							$customer     = $this->stripe->retrieve_customer( $stripeCustomer['stripeCustomerID'] );
							$customer     = $this->include_customer_subscription( $customer );
							$this->db->fullstripe_insert_subscriber( $customer, $cardholderName, $billingAddress, $formName );
						} else {
							$subscriptionDescription = sprintf( __( 'Subscriber: %s', 'wp-full-stripe' ), $cardholderName );
							$customer                = $this->stripe->subscribe( $planID, $stripeToken, $customerEmail, $subscriptionDescription, $couponCode, $setupFee, $metadata );
							$customer                = $this->include_customer_subscription( $customer );
							$this->db->fullstripe_insert_subscriber( $customer, $cardholderName, $billingAddress, $formName );
						}

						// Do our after subscription action with the Stripe customer so other plugins can hook in
						$actionName  = 'fullstripe_after_subscription_charge';
						$macros      = MM_WPFS_Utils::get_subscription_macros();
						$macroValues = MM_WPFS_Utils::get_subscription_macro_values(
							$transactionData->getCustomerName(),
							$transactionData->getCustomerEmail(),
							$transactionData->getBillingAddress(),
							$transactionData->getPlanName(),
							$transactionData->getPlanCurrency(),
							$transactionData->getPlanSetupFee(),
							$transactionData->getPlanAmount(),
							$transactionData->getPlanAmount(),
							$transactionData->getProductName()
						);
						if ( ! is_null( $transactionData->getCustomInputValues() ) && is_array( $transactionData->getCustomInputValues() ) ) {
							$customFieldMacros      = MM_WPFS_Utils::get_custom_field_macros();
							$customFieldMacroValues = MM_WPFS_Utils::get_custom_field_macro_values( count( $customFieldMacros ), $transactionData->getCustomInputValues() );
							$macros                 = array_merge( $macros, $customFieldMacros );
							$macroValues            = array_merge( $macroValues, $customFieldMacroValues );
						}
						$additionalData = MM_WPFS_Utils::prepare_additional_data_for_subscription_charge( $actionName, $customer, $macros, $macroValues );
						do_action( $actionName, $customer, $additionalData );

						$return = array(
							'success' => true,
							'msg'     => __( 'Payment Successful. Thanks for subscribing!', 'wp-full-stripe' )
						);
						if ( $doRedirect == 1 ) {
							if ( $redirectToPageOrPost == 1 ) {
								if ( $redirectPostID != 0 ) {

									$pageOrPostUrl = get_page_link( $redirectPostID );

									if ( $showDetailedSuccessPage == 1 ) {
										$transactionDataKey = $this->transaction_data_service->store( $transactionData );
										$pageOrPostUrl      = add_query_arg( array( self::REQUEST_PARAM_NAME_WPFS_TRANSACTION_DATA_KEY => $transactionDataKey ), $pageOrPostUrl );
									}

									$return['redirect']    = true;
									$return['redirectURL'] = $pageOrPostUrl;
								} else {
									MM_WPFS_Utils::log( "Inconsistent form data: formName=$formName, doRedirect=$doRedirect, redirectPostID=$redirectPostID" );
								}
							} else {
								$return['redirect']    = true;
								$return['redirectURL'] = $redirectUrl;
							}
						}

						if ( $sendPluginEmail && $sendReceipt == 1 ) {
							$this->mailer->send_subscription_started_email_receipt( $customerEmail, $plan->name, $plan->currency, $setupFee, is_null( $amountWithCouponApplied ) ? $plan->amount : $amountWithCouponApplied, $cardholderName, $billingAddress, $productName, $customInputValues );
						}
					} catch ( \Stripe\Error\Card $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$message = $this->stripe->resolve_error_message_by_code( $e->getCode() );
						if ( is_null( $message ) ) {
							$message = MM_WPFS::translate_label( $e->getMessage() );
						}
						$return = array(
							'success' => false,
							'msg'     => $message,
							'ex_msg'  => $e->getMessage()
						);
					} catch ( Exception $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => MM_WPFS::translate_label( $e->getMessage() ),
							'ex_msg'  => $e->getMessage()
						);
					}
				} else {
					if ( ! isset( $return ) ) {
						$errorMessage = 'Incorrect data submitted';
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => __( 'Incorrect data submitted.', 'wp-full-stripe' )
						);
					}
				}
			} else {
				$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s) or form not found', $formName, $formNonce );
				MM_WPFS_Utils::log( $errorMessage );
				$return = array(
					'success' => false,
					'msg'     => __( 'Invalid form name or form nonce or form not found', 'wp-full-stripe' )
				);
			}
		} else {
			$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s)', $formName, $formNonce );
			MM_WPFS_Utils::log( $errorMessage );
			$return = array(
				'success' => false,
				'msg'     => __( 'Invalid form name or form nonce', 'wp-full-stripe' )
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( apply_filters( 'fullstripe_subscription_charge_return_message', $return ) );
		exit;
	}

	/**
	 * In later versions of the Stripe API, the subscription property is removed so we must create it ourselves for compatibility
	 *
	 * @param $customer
	 *
	 * @return mixed
	 */
	private function include_customer_subscription( $customer ) {
		// the value is already set meaning user has Stripe API version 2013-02-13 or older
		if ( isset( $customer->subscription ) ) {
			return $customer;
		}

		// get the first item from the subscriptions data as the most recently added
		$customer->subscription = $customer->subscriptions->data[0];

		return $customer;
	}

	function fullstripe_checkout_charge() {

		// tnagy read data from POST
		$formName  = isset( $_POST['formName'] ) ? $_POST['formName'] : null;
		$formNonce = isset( $_POST['formNonce'] ) ? $_POST['formNonce'] : null;

		if ( ! is_null( $formName ) && ! is_null( $formNonce ) ) {
			$checkoutForm = $this->db->get_checkout_form_by_name( $formName );
			if ( isset( $checkoutForm ) && wp_verify_nonce( $formNonce, $checkoutForm->checkoutFormID ) ) {
				$currencyArray           = MM_WPFS::get_currency_for( $checkoutForm->currency );
				$productName             = $checkoutForm->productDesc;
				$chargeDescription       = sprintf( __( 'Payment for %s', 'wp-full-stripe' ), $productName );
				$doRedirect              = $checkoutForm->redirectOnSuccess;
				$redirectPostID          = $checkoutForm->redirectPostID;
				$redirectUrl             = $checkoutForm->redirectUrl;
				$redirectToPageOrPost    = $checkoutForm->redirectToPageOrPost;
				$showBillingAddress      = $checkoutForm->showBillingAddress;
				$sendReceipt             = $checkoutForm->sendEmailReceipt;
				$showDetailedSuccessPage = $checkoutForm->showDetailedSuccessPage;
				$customInputTitle        = $checkoutForm->customInputTitle;
				$customInputs            = $checkoutForm->customInputs;
				$customInputRequired     = $checkoutForm->customInputRequired;

				// tnagy read user input
				$stripeToken = $_POST['stripeToken'];
				$stripeEmail = isset( $_POST['stripeEmail'] ) ? sanitize_text_field( $_POST['stripeEmail'] ) : null;
				$amount      = null;
				if ( $checkoutForm->customAmount == 'specified_amount' ) {
					$amount = $checkoutForm->amount;
				} elseif ( $checkoutForm->customAmount == 'list_of_amounts' ) {
					if ( $checkoutForm->allowListOfAmountsCustom == 1 && 'other' == $_POST['fullstripe_custom_amount'] ) {
						$amount = MM_WPFS::parse_amount( $checkoutForm->currency, $_POST['fullstripe_list_of_amounts_custom_amount'] );
					} else {
						$amountIndex   = $_POST['fullstripe_amount_index'];
						$listOfAmounts = json_decode( $checkoutForm->listOfAmounts );
						if ( count( $listOfAmounts ) > $amountIndex ) {
							$listElement = $listOfAmounts[ $amountIndex ];
							$amount      = $listElement[0];
						}
					}
				} elseif ( $checkoutForm->customAmount == 'custom_amount' ) {
					$amount = MM_WPFS::parse_amount( $checkoutForm->currency, $_POST['fullstripe_custom_amount'] );
				}
				$customInputValues = isset( $_POST['fullstripe_custom_input'] ) ? $_POST['fullstripe_custom_input'] : array();

				// tnagy read billing details
				$billingName           = isset( $_POST['billing_name'] ) ? sanitize_text_field( $_POST['billing_name'] ) : '';
				$billingAddressCountry = isset( $_POST['billing_address_country'] ) ? MM_WPFS::get_country_name_for( sanitize_text_field( $_POST['billing_address_country'] ) ) : '';
				$billingAddressZip     = isset( $_POST['billing_address_zip'] ) ? sanitize_text_field( $_POST['billing_address_zip'] ) : '';
				$billingAddressState   = isset( $_POST['billing_address_state'] ) ? sanitize_text_field( $_POST['billing_address_state'] ) : '';
				$billingAddressLine1   = isset( $_POST['billing_address_line1'] ) ? sanitize_text_field( $_POST['billing_address_line1'] ) : '';
				$billingAddressCity    = isset( $_POST['billing_address_city'] ) ? sanitize_text_field( $_POST['billing_address_city'] ) : '';

				// tnagy validate user input
				$valid = true;
				if ( ! is_numeric( trim( $amount ) ) || $amount <= 0 ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'The payment amount is invalid, please only use positive numbers and a decimal point.', 'wp-full-stripe' )
					);
				}

				if ( $valid && $showBillingAddress == 1 ) {
					$valid = $this->is_valid_address( $billingAddressLine1, $billingAddressCity, $billingAddressZip, $billingAddressCountry );
					if ( ! $valid ) {
						$return = array(
							'success' => false,
							'msg'     => __( 'Please enter a valid billing address.', 'wp-full-stripe' )
						);
					}
				}

				if ( $valid && $customInputRequired == 1 ) {

					if ( $customInputs == null ) {
						if ( is_null( $customInputValues ) || ( trim( $customInputValues ) == false ) ) {
							$valid  = false;
							$return = array(
								'success' => false,
								'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $customInputTitle ) )
							);
						}
					} else {
						$labels = explode( '{{', $customInputs );
						foreach ( $labels as $i => $label ) {
							if ( $valid && ( is_null( $customInputValues[ $i ] ) || ( trim( $customInputValues[ $i ] ) == false ) ) ) {
								$valid  = false;
								$return = array(
									'success' => false,
									'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $label ) )
								);
							}
						}
					}
				}

				// tnagy perform charge and actions after payment
				if ( $valid ) {
					$options = get_option( 'fullstripe_options' );
					try {

						$sendPluginEmail = true;
						if ( $options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 && isset( $_POST['stripeEmail'] ) ) {
							$sendPluginEmail = false;
						}

						do_action( 'fullstripe_before_checkout_payment_charge', $amount );
						$stripeCustomer = $this->create_or_get_customer( $stripeToken, $stripeEmail, null, ( $options['apiMode'] === 'live' ) );

						$metadata            = array(
							'customer_email'          => $stripeEmail,
							'billing_name'            => $billingName,
							'billing_address_line1'   => $billingAddressLine1,
							'billing_address_city'    => $billingAddressCity,
							'billing_address_state'   => $billingAddressState,
							'billing_address_zip'     => $billingAddressZip,
							'billing_address_country' => $billingAddressCountry
						);
						$metadata            = $this->add_custom_inputs( $metadata, $customInputs, $customInputValues );
						$charge              = $this->stripe->charge_customer( $stripeCustomer->id, $checkoutForm->currency, $amount, $chargeDescription, $metadata );
						$charge['wpfs_form'] = $checkoutForm->name;
						do_action( 'fullstripe_after_checkout_payment_charge', $charge );

						$billingAddress = MM_WPFS_Utils::prepare_billing_address_data( $billingAddressLine1, '', $billingAddressCity, $billingAddressState, $billingAddressCountry, $billingAddressZip );
						$customerName   = ! is_null( $billingName ) ? $billingName : null;
						$this->db->fullstripe_insert_payment( $charge, $billingAddress, $stripeCustomer->id, $customerName, $stripeEmail, $formName, 'checkout' /* form_type */ );

						$return = array( 'success' => true, 'msg' => __( 'Payment Successful!', 'wp-full-stripe' ) );
						if ( $doRedirect == 1 ) {
							if ( $redirectToPageOrPost == 1 ) {
								if ( $redirectPostID != 0 ) {

									$pageOrPostUrl = get_page_link( $redirectPostID );

									if ( $showDetailedSuccessPage == 1 ) {
										$transaction_data_key = $this->transaction_data_service->store( MM_WPFS_TransactionDataService::create_payment_data( $stripeEmail, $checkoutForm->currency, $amount, $productName, $billingName != null ? $billingName : '', $billingAddress, $customInputValues ) );
										$pageOrPostUrl        = add_query_arg( array( self::REQUEST_PARAM_NAME_WPFS_TRANSACTION_DATA_KEY => $transaction_data_key ), $pageOrPostUrl );
									}

									$return['redirect']    = true;
									$return['redirectURL'] = $pageOrPostUrl;
								} else {
									MM_WPFS_Utils::log( "Inconsistent form data: formName=$formName, doRedirect=$doRedirect, redirectPostID=$redirectPostID" );
								}
							} else {
								$return['redirect']    = true;
								$return['redirectURL'] = $redirectUrl;
							}
						}

						if ( $sendPluginEmail && $sendReceipt == 1 && isset( $_POST['stripeEmail'] ) ) {
							$this->mailer->send_payment_email_receipt( $stripeEmail, $checkoutForm->currency, $amount, $billingName != null ? $billingName : '', $billingAddress, $productName );
						}

					} catch ( \Stripe\Error\Card $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$message = $this->stripe->resolve_error_message_by_code( $e->getCode() );
						if ( is_null( $message ) ) {
							$message = MM_WPFS::translate_label( $e->getMessage() );
						}
						$return = array(
							'success' => false,
							'msg'     => $message,
							'ex_msg'  => $e->getMessage()
						);
					} catch ( Exception $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => MM_WPFS::translate_label( $e->getMessage() ),
							'ex_msg'  => $e->getMessage()
						);
					}
				} else {
					if ( ! isset( $return ) ) {
						$errorMessage = 'Incorrect data submitted';
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => __( 'Incorrect data submitted.', 'wp-full-stripe' )
						);
					}
				}
			} else {
				$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s) or form not found', $formName, $formNonce );
				MM_WPFS_Utils::log( $errorMessage );
				$return = array(
					'success' => false,
					'msg'     => __( 'Invalid form name or form nonce or form not found', 'wp-full-stripe' )
				);
			}
		} else {
			$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s)', $formName, $formNonce );
			MM_WPFS_Utils::log( $errorMessage );
			$return = array(
				'success' => false,
				'msg'     => __( 'Invalid form name or form nonce', 'wp-full-stripe' )
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( apply_filters( 'fullstripe_checkout_charge_return_message', $return ) );
		exit;
	}

	function fullstripe_checkout_subscription_charge() {

		// tnagy read data from POST
		$formName  = isset( $_POST['formName'] ) ? $_POST['formName'] : null;
		$formNonce = isset( $_POST['formNonce'] ) ? $_POST['formNonce'] : null;

		if ( ! is_null( $formName ) && ! is_null( $formNonce ) ) {
			$checkoutSubscriptionForm = $this->db->get_checkout_subscription_form_by_name( $formName );
			if ( isset( $checkoutSubscriptionForm ) && wp_verify_nonce( $formNonce, $checkoutSubscriptionForm->checkoutSubscriptionFormID ) ) {
				$doRedirect              = $checkoutSubscriptionForm->redirectOnSuccess;
				$redirectPostID          = $checkoutSubscriptionForm->redirectPostID;
				$redirectUrl             = $checkoutSubscriptionForm->redirectUrl;
				$redirectToPageOrPost    = $checkoutSubscriptionForm->redirectToPageOrPost;
				$showBillingAddress      = $checkoutSubscriptionForm->showBillingAddress;
				$sendReceipt             = $checkoutSubscriptionForm->sendEmailReceipt;
				$showDetailedSuccessPage = $checkoutSubscriptionForm->showDetailedSuccessPage;
				$customInputTitle        = $checkoutSubscriptionForm->customInputTitle;
				$customInputs            = $checkoutSubscriptionForm->customInputs;
				$customInputRequired     = $checkoutSubscriptionForm->customInputRequired;

				// tnagy read user input
				$stripeToken             = $_POST['stripeToken'];
				$stripeEmail             = isset( $_POST['stripeEmail'] ) ? sanitize_text_field( $_POST['stripeEmail'] ) : null;
				$planID                  = stripslashes( html_entity_decode( $_POST['fullstripe_plan'] ) );
				$plan                    = $this->stripe->retrieve_plan( $planID );
				$couponCode              = isset( $_POST['fullstripe_coupon_input'] ) ? $_POST['fullstripe_coupon_input'] : '';
				$amountWithCouponApplied = isset( $_POST['amount_with_coupon_applied'] ) && is_numeric( $_POST['amount_with_coupon_applied'] ) ? $_POST['amount_with_coupon_applied'] : null;
				$customInputValues       = isset( $_POST['fullstripe_custom_input'] ) ? $_POST['fullstripe_custom_input'] : array();

				// tnagy read billing details
				$billingName           = isset( $_POST['billing_name'] ) ? sanitize_text_field( $_POST['billing_name'] ) : '';
				$billingAddressCountry = isset( $_POST['billing_address_country'] ) ? MM_WPFS::get_country_name_for( sanitize_text_field( $_POST['billing_address_country'] ) ) : '';
				$billingAddressZip     = isset( $_POST['billing_address_zip'] ) ? sanitize_text_field( $_POST['billing_address_zip'] ) : '';
				$billingAddressState   = isset( $_POST['billing_address_state'] ) ? sanitize_text_field( $_POST['billing_address_state'] ) : '';
				$billingAddressLine1   = isset( $_POST['billing_address_line1'] ) ? sanitize_text_field( $_POST['billing_address_line1'] ) : '';
				$billingAddressCity    = isset( $_POST['billing_address_city'] ) ? sanitize_text_field( $_POST['billing_address_city'] ) : '';

				// tnagy validate user input
				$valid = true;
				if ( is_null( $plan ) ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'Invalid plan selected, please contact the site administrator.', 'wp-full-stripe' )
					);
				}
				if ( $valid && $showBillingAddress == 1 ) {
					$valid = $this->is_valid_address( $billingAddressLine1, $billingAddressCity, $billingAddressZip, $billingAddressCountry );
					if ( ! $valid ) {
						$return = array(
							'success' => false,
							'msg'     => __( 'Please enter a valid billing address.', 'wp-full-stripe' )
						);
					}
				}
				if ( $valid && $customInputRequired == 1 ) {

					if ( $customInputs == null ) {
						if ( is_null( $customInputValues ) || ( trim( $customInputValues ) == false ) ) {
							$valid  = false;
							$return = array(
								'success' => false,
								'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $customInputTitle ) )
							);
						}
					} else {
						$labels = explode( '{{', $customInputs );
						foreach ( $labels as $i => $label ) {
							if ( $valid && ( is_null( $customInputValues[ $i ] ) || ( trim( $customInputValues[ $i ] ) == false ) ) ) {
								$valid  = false;
								$return = array(
									'success' => false,
									'msg'     => sprintf( __( 'Please enter a value for "%s".', 'wp-full-stripe' ), MM_WPFS::translate_label( $label ) )
								);
							}
						}
					}
				}

				if ( $valid ) {

					$metadata = array(
						'customer_name'           => $billingName,
						'customer_email'          => $stripeEmail,
						'billing_address_line1'   => $billingAddressLine1,
						'billing_address_city'    => $billingAddressCity,
						'billing_address_state'   => $billingAddressState,
						'billing_address_country' => $billingAddressCountry,
						'billing_address_zip'     => $billingAddressZip,
					);
					$metadata = $this->add_custom_inputs( $metadata, $customInputs, $customInputValues );

					try {
						$sendPluginEmail = true;
						$options         = get_option( 'fullstripe_options' );
						if ( $options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 ) {
							$sendPluginEmail = false;
						}

						$stripeCustomer = $this->find_existing_stripe_customer_by_email( $stripeEmail, ( $options['apiMode'] === 'live' ) );
						do_action( 'fullstripe_before_checkout_subscription_charge', $planID );

						$planSetupFee = 0;
						if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
							$planSetupFee = $plan->metadata->setup_fee;
						}
						$billingAddress  = MM_WPFS_Utils::prepare_billing_address_data( $billingAddressLine1, '', $billingAddressCity, $billingAddressState, $billingAddressCountry, $billingAddressZip );
						$transactionData = MM_WPFS_TransactionDataService::create_subscription_data( $stripeEmail, $plan->name, $plan->currency, is_null( $amountWithCouponApplied ) ? $plan->amount : $amountWithCouponApplied, $planSetupFee, $checkoutSubscriptionForm->productDesc, $billingName, $billingAddress, $customInputValues );

						if ( $stripeCustomer && $stripeCustomer['stripeCustomerID'] ) {
							/** @noinspection PhpUnusedLocalVariableInspection */
							$subscription = $this->stripe->subscribe_existing( $stripeCustomer['stripeCustomerID'], $planID, $stripeToken, $couponCode, $planSetupFee, $metadata );
							$customer     = $this->stripe->retrieve_customer( $stripeCustomer['stripeCustomerID'] );
							$customer     = $this->include_customer_subscription( $customer );
							$this->db->fullstripe_insert_subscriber( $customer, $customer->metadata->customer_name, $billingAddress, $formName );
						} else {
							$subscriptionDescription = sprintf( __( 'Subscriber: %s', 'wp-full-stripe' ), $billingName );
							$customer                = $this->stripe->subscribe( $planID, $stripeToken, $stripeEmail, $subscriptionDescription, $couponCode, $planSetupFee, $metadata );
							$customer                = $this->include_customer_subscription( $customer );
							$this->db->fullstripe_insert_subscriber( $customer, $billingName, $billingAddress, $formName );
						}

						$actionName  = 'fullstripe_after_subscription_charge';
						$macros      = MM_WPFS_Utils::get_subscription_macros();
						$macroValues = MM_WPFS_Utils::get_subscription_macro_values(
							$transactionData->getCustomerName(),
							$transactionData->getCustomerEmail(),
							$transactionData->getBillingAddress(),
							$transactionData->getPlanName(),
							$transactionData->getPlanCurrency(),
							$transactionData->getPlanSetupFee(),
							$transactionData->getPlanAmount(),
							$transactionData->getPlanAmount(),
							$transactionData->getProductName()
						);
						if ( ! is_null( $transactionData->getCustomInputValues() ) && is_array( $transactionData->getCustomInputValues() ) ) {
							$customFieldMacros      = MM_WPFS_Utils::get_custom_field_macros();
							$customFieldMacroValues = MM_WPFS_Utils::get_custom_field_macro_values( count( $customFieldMacros ), $transactionData->getCustomInputValues() );
							$macros                 = array_merge( $macros, $customFieldMacros );
							$macroValues            = array_merge( $macroValues, $customFieldMacroValues );
						}
						$additionalData = MM_WPFS_Utils::prepare_additional_data_for_subscription_charge( $actionName, $customer, $macros, $macroValues );
						do_action( $actionName, $customer, $additionalData );

						$return = array(
							'success' => true,
							'msg'     => __( 'Payment Successful. Thanks for subscribing!', 'wp-full-stripe' )
						);
						if ( $doRedirect == 1 ) {
							if ( $redirectToPageOrPost == 1 ) {
								if ( $redirectPostID != 0 ) {

									$pageOrPostUrl = get_page_link( $redirectPostID );

									if ( $showDetailedSuccessPage == 1 ) {
										$transactionDataKey = $this->transaction_data_service->store( $transactionData );
										$pageOrPostUrl      = add_query_arg( array( self::REQUEST_PARAM_NAME_WPFS_TRANSACTION_DATA_KEY => $transactionDataKey ), $pageOrPostUrl );
									}

									$return['redirect']    = true;
									$return['redirectURL'] = $pageOrPostUrl;
								} else {
									MM_WPFS_Utils::log( "Inconsistent form data: formName=$formName, doRedirect=$doRedirect, redirectPostID=$redirectPostID" );
								}
							} else {
								$return['redirect']    = true;
								$return['redirectURL'] = $redirectUrl;
							}
						}

						if ( $sendPluginEmail && $sendReceipt == 1 ) {
							$this->mailer->send_subscription_started_email_receipt( $stripeEmail, $plan->name, $plan->currency, $planSetupFee, is_null( $amountWithCouponApplied ) ? $plan->amount : $amountWithCouponApplied, $billingName, $billingAddress, $checkoutSubscriptionForm->productDesc, $customInputValues );
						}
					} catch ( \Stripe\Error\Card $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$message = $this->stripe->resolve_error_message_by_code( $e->getCode() );
						if ( is_null( $message ) ) {
							$message = MM_WPFS::translate_label( $e->getMessage() );
						}
						$return = array(
							'success' => false,
							'msg'     => $message,
							'ex_msg'  => $e->getMessage()
						);
					} catch ( Exception $e ) {
						$errorMessage = sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() );
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => MM_WPFS::translate_label( $e->getMessage() ),
							'ex_msg'  => $e->getMessage()
						);
					}
				} else {
					// tnagy return validation error messages or create a general message to return
					if ( ! isset( $return ) ) {
						$errorMessage = 'Incorrect data submitted';
						MM_WPFS_Utils::log( $errorMessage );
						$return = array(
							'success' => false,
							'msg'     => __( 'Incorrect data submitted.', 'wp-full-stripe' )
						);
					}
				}
			} else {
				$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s) or form not found', $formName, $formNonce );
				MM_WPFS_Utils::log( $errorMessage );
				$return = array(
					'success' => false,
					'msg'     => __( 'Invalid form name or form nonce or form not found', 'wp-full-stripe' )
				);
			}
		} else {
			$errorMessage = sprintf( 'Invalid form name (%s) or form nonce (%s)', $formName, $formNonce );
			MM_WPFS_Utils::log( $errorMessage );
			$return = array(
				'success' => false,
				'msg'     => __( 'Invalid form name or form nonce', 'wp-full-stripe' )
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( apply_filters( 'fullstripe_checkout_subscription_charge_return_message', $return ) );
		exit;
	}

	function fullstripe_check_coupon() {
		$code = $_POST['code'];

		try {
			$coupon = $this->stripe->get_coupon( $code );
			if ( $coupon->valid == false ) {
				$return = array( 'msg' => __( 'This coupon has expired.', 'wp-full-stripe' ), 'valid' => false );
			} else {
				$return = array(
					'msg'    => __( 'The coupon has been applied successfully.', 'wp-full-stripe' ),
					'coupon' => array(
						'currency'    => $coupon->currency,
						'percent_off' => $coupon->percent_off,
						'amount_off'  => $coupon->amount_off
					),
					'valid'  => true
				);
			}
		} catch ( Exception $e ) {
			MM_WPFS_Utils::log( sprintf( 'Message=%s, Stack=%s ', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'msg'   => __( 'You have entered an invalid coupon code.', 'wp-full-stripe' ),
				'valid' => false
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}


}