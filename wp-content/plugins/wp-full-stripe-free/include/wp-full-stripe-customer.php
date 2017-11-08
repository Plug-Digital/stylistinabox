<?php

/**
 * Class MM_WPFSF_Customer
 *
 * Deals with customer front-end input i.e. payment forms submission.
 *
 */
class MM_WPFSF_Customer {
	private $stripe = null;

	public function __construct() {
		$this->stripe = new MM_WPFSF_Stripe();
		$this->db     = new MM_WPFSF_Database();
		$this->hooks();
	}

	private function hooks() {
		add_action( 'wp_ajax_wp_full_stripe_payment_charge', array( $this, 'fullstripe_payment_charge' ) );
		add_action( 'wp_ajax_nopriv_wp_full_stripe_payment_charge', array( $this, 'fullstripe_payment_charge' ) );
	}

	function fullstripe_payment_charge() {

		$formName  = isset( $_POST['formName'] ) ? $_POST['formName'] : null;
		$formNonce = isset( $_POST['formNonce'] ) ? $_POST['formNonce'] : null;

		if ( ! is_null( $formName ) && ! is_null( $formNonce ) ) {
			$paymentForm = $this->db->get_payment_form_by_name( $formName );
			if ( isset( $paymentForm ) && wp_verify_nonce( $formNonce, $paymentForm->paymentFormID ) ) {

				$useCustomAmount      = $paymentForm->customAmount;
				$doRedirect           = $paymentForm->redirectOnSuccess;
				$redirectPostID       = $paymentForm->redirectPostID;
				$redirectUrl          = $paymentForm->redirectUrl;
				$redirectToPageOrPost = $paymentForm->redirectToPageOrPost;
				$showAddress          = $paymentForm->showAddress;
				$sendEmailReceipt     = $paymentForm->sendEmailReceipt;
                $showEmailInput       = $paymentForm->showEmailInput;

				$stripeToken   = $_POST['stripeToken'];
				$customerName  = sanitize_text_field( $_POST['fullstripe_name'] );
				$customerEmail = 'n/a';
				if ( isset( $_POST['fullstripe_email'] ) ) {
					$customerEmail = sanitize_text_field( $_POST['fullstripe_email'] );
				}

				$amount = null;
				if ( $useCustomAmount == 1 ) {
					$amount = sanitize_text_field( trim( $_POST['fullstripe_custom_amount'] ) );
					if ( is_numeric( $amount ) ) {
						$amount = $amount * 100;
					}
				} else {
					$amount = $paymentForm->amount;
				}

				$billingAddressLine1 = isset( $_POST['fullstripe_address_line1'] ) ? sanitize_text_field( $_POST['fullstripe_address_line1'] ) : '';
				$billingAddressLine2 = isset( $_POST['fullstripe_address_line2'] ) ? sanitize_text_field( $_POST['fullstripe_address_line2'] ) : '';
				$billingAddressCity  = isset( $_POST['fullstripe_address_city'] ) ? sanitize_text_field( $_POST['fullstripe_address_city'] ) : '';
				$billingAddressState = isset( $_POST['fullstripe_address_state'] ) ? sanitize_text_field( $_POST['fullstripe_address_state'] ) : '';
				$billingAddressZip   = isset( $_POST['fullstripe_address_zip'] ) ? sanitize_text_field( $_POST['fullstripe_address_zip'] ) : '';

				$customInput = isset( $_POST['fullstripe_custom_input'] ) ? $_POST['fullstripe_custom_input'] : 'n/a';

				$valid = true;
				if ( ! is_numeric( trim( $amount ) ) || $amount <= 0 ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'The payment amount is invalid, please only use numbers and a decimal point.', 'wp-full-stripe-free' )
					);
				}

				if ( $valid && $showAddress == 1 ) {
					if ( $billingAddressLine1 == '' || $billingAddressCity == '' || $billingAddressZip == '' ) {
						$valid  = false;
						$return = array(
							'success' => false,
							'msg'     => __( 'Please enter a valid billing address.', 'wp-full-stripe-free' )
						);
					}
				}

				if ( $valid && $showEmailInput && ! filter_var( $customerEmail, FILTER_VALIDATE_EMAIL ) ) {
					$valid  = false;
					$return = array(
						'success' => false,
						'msg'     => __( 'Please enter a valid email address.', 'wp-full-stripe-free' )
					);
				}

				if ( $valid ) {

					$description = "Payment from {$customerName} on form: {$formName} \nCustom Data: {$customInput}";
					$metadata    = array(
						'customer_name'         => $customerName,
						'customer_email'        => $customerEmail,
						'billing_address_line1' => $billingAddressLine1,
						'billing_address_line2' => $billingAddressLine2,
						'billing_address_city'  => $billingAddressCity,
						'billing_address_state' => $billingAddressState,
						'billing_address_zip'   => $billingAddressZip
					);

					try {

						$sendPluginEmail = true;
						if ( $sendEmailReceipt == 1 && isset( $customerEmail ) ) {
							$sendPluginEmail = false;
						}

						do_action( 'fullstripe_before_payment_charge', $amount );
						$charge = $this->stripe->charge( $amount, $stripeToken, $description, $metadata, ( $sendPluginEmail == false ? $customerEmail : null ) );
						do_action( 'fullstripe_after_payment_charge', $charge );

						$billingAddress = array(
							'line1' => $billingAddressLine1,
							'line2' => $billingAddressLine2,
							'city'  => $billingAddressCity,
							'state' => $billingAddressState,
							'zip'   => $billingAddressZip
						);
						$this->db->fullstripe_insert_payment( $charge, $billingAddress );

						$return = array(
							'success' => true,
							'msg'     => __( 'Payment Successful!', 'wp-full-stripe-free' )
						);

						if ( $doRedirect == 1 ) {
							if ( $redirectToPageOrPost == 1 ) {
								if ( $redirectPostID != 0 ) {
									$return['redirect']    = true;
									$return['redirectURL'] = get_page_link( $redirectPostID );
								} else {
									error_log( "Inconsistent form data: formName=$formName, doRedirect=$doRedirect, redirectPostID=$redirectPostID" );
								}
							} else {
								$return['redirect']    = true;
								$return['redirectURL'] = $redirectUrl;
							}
						}

					} catch ( \Stripe\Error\Card $e ) {
						$message = $this->stripe->resolve_error_message_by_code( $e->getCode() );
						if ( is_null( $message ) ) {
							$message = MM_WPFSF::translate_label( $e->getMessage() );
						}
						$return = array(
							'success' => false,
							'msg'     => $message
						);
					} catch ( Exception $e ) {
						$return = array(
							'success' => false,
							'msg'     => MM_WPFSF::translate_label( $e->getMessage() )
						);
					}
				} else {
					if ( ! isset( $return ) ) {
						$return = array(
							'success' => false,
							'msg'     => __( 'Incorrect data submitted.', 'wp-full-stripe-free' )
						);
					}

				}

			} else {
				$return = array(
					'success' => false,
					'msg'     => __( 'Invalid form name or form nonce or form not found', 'wp-full-stripe-free' )
				);
			}

		} else {
			$return = array(
				'success' => false,
				'msg'     => __( 'Invalid form name or form nonce', 'wp-full-stripe-free' )
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( apply_filters( 'fullstripe_payment_charge_return_message', $return ) );
		exit;
	}

}