<?php

/**
 * Class MM_WPFS_Admin deals with admin back-end input i.e. create plans, transfers
 */
class MM_WPFS_Admin {

	/* @var $stripe MM_WPFS_Stripe */
	private $stripe = null;

	/* @var $db MM_WPFS_Database */
	private $db = null;

	/* @var $mailer MM_WPFS_Mailer */
	private $mailer = null;

	public function __construct() {
		$this->stripe = new MM_WPFS_Stripe();
		$this->db     = new MM_WPFS_Database();
		$this->mailer = new MM_WPFS_Mailer();
		$this->hooks();
	}

	private function hooks() {

		// tnagy actions for settings
		add_action( 'wp_ajax_wp_full_stripe_update_settings', array( $this, 'fullstripe_update_settings_post' ) );

		// tnagy actions for subscription plans
		add_action( 'wp_ajax_wp_full_stripe_create_plan', array( $this, 'fullstripe_create_plan_post' ) );
		add_action( 'wp_ajax_wp_full_stripe_edit_subscription_plan', array(
			$this,
			'fullstripe_edit_subscription_plan_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_delete_subscription_plan', array(
			$this,
			'fullstripe_delete_subscription_plan'
		) );

		// tnagy actions for subscription forms
		add_action( 'wp_ajax_wp_full_stripe_create_subscripton_form', array(
			$this,
			'fullstripe_create_subscription_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_edit_subscription_form', array(
			$this,
			'fullstripe_edit_subscription_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_delete_subscription_form', array(
			$this,
			'fullstripe_delete_subscription_form'
		) );

		// tnagy actions for checkout subscriptions
		add_action( 'wp_ajax_wp_full_stripe_create_checkout_subscription_form', array(
			$this,
			'fullstripe_create_checkout_subscription_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_edit_checkout_subscription_form', array(
			$this,
			'fullstripe_edit_checkout_subscription_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_delete_checkout_subscription_form', array(
			$this,
			'fullstripe_delete_checkout_subscription_form'
		) );

		// tnagy actions for subscriptions
		add_action( 'wp_ajax_wp_full_stripe_cancel_subscription', array( $this, 'fullstripe_cancel_subscription' ) );
		add_action( 'wp_ajax_wp_full_stripe_delete_subscription_record', array(
			$this,
			'fullstripe_delete_subscription_record'
		) );

		// tnagy actions for payment forms
		add_action( 'wp_ajax_wp_full_stripe_create_payment_form', array(
			$this,
			'fullstripe_create_payment_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_edit_payment_form', array( $this, 'fullstripe_edit_payment_form_post' ) );
		add_action( 'wp_ajax_wp_full_stripe_delete_payment_form', array( $this, 'fullstripe_delete_payment_form' ) );

		// tnagy actions for checkouts
		add_action( 'wp_ajax_wp_full_stripe_create_checkout_form', array(
			$this,
			'fullstripe_create_checkout_form_post'
		) );
		add_action( 'wp_ajax_wp_full_stripe_edit_checkout_form', array( $this, 'fullstripe_edit_checkout_form_post' ) );
		add_action( 'wp_ajax_wp_full_stripe_delete_checkout_form', array( $this, 'fullstripe_delete_checkout_form' ) );

		// tnagy actions for payments
		add_action( 'wp_ajax_wp_full_stripe_delete_payment', array( $this, 'fullstripe_delete_payment_local' ) );

		// tnagy actions for recipients
		add_action( 'wp_ajax_wp_full_stripe_create_recipient', array( $this, 'fullstripe_create_recipient' ) );
		add_action( 'wp_ajax_wp_full_stripe_create_recipient_card', array(
			$this,
			'fullstripe_create_recipient_card'
		) );

		// tnagy actions for transfers
		add_action( 'wp_ajax_wp_full_stripe_create_transfer', array( $this, 'fullstripe_create_transfer' ) );

		// tnagy handle stripe webhook events
		add_action( 'admin_post_nopriv_handle_wpfs_event', array(
			$this,
			'fullstripe_handle_wpfs_event'
		) );
	}

	function fullstripe_create_plan_post() {

		$validation_result = array();
		if ( ! $this->is_valid_plan( $validation_result ) ) {
			header( "Content-Type: application/json" );
			echo json_encode( array( 'success' => false, 'validation_result' => $validation_result ) );
			exit;
		}

		$id                = stripslashes( $_POST['sub_id'] );
		$name              = $_POST['sub_name'];
		$currency          = $_POST['sub_currency'];
		$amount            = $_POST['sub_amount'];
		$setup_fee         = $_POST['sub_setup_fee'];
		$interval          = $_POST['sub_interval'];
		$intervalCount     = $_POST['sub_interval_count'];
		$cancellationCount = $_POST['sub_cancellation_count'];
		$trial             = $_POST['sub_trial'];

		$return                = $this->stripe->create_plan( $id, $name, $currency, $amount, $setup_fee, $interval, $trial, $intervalCount, $cancellationCount );
		$return['redirectURL'] = admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' );

		do_action( 'fullstripe_admin_create_plan_action', $return );

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	private function is_valid_plan( &$validation_result ) {
		if ( isset( $_POST['sub_cancellation_count'] ) ) {
			if ( ! is_numeric( $_POST['sub_cancellation_count'] ) ) {
				$validation_result['sub_cancellation_count'] = __( 'Field value is invalid.', 'wp-full-stripe' );
			} else if ( intval( $_POST['sub_cancellation_count'] ) < 0 ) {
				$validation_result['sub_cancellation_count'] = __( 'Field value is invalid.', 'wp-full-stripe' );
			}
		} else {
			$validation_result['sub_cancellation_count'] = __( 'Required field.', 'wp-full-stripe' );
		}

		return empty( $validation_result );
	}

	function fullstripe_create_subscription_form_post() {
		$name                = $_POST['form_name'];
		$title               = $_POST['form_title'];
		$plan_order          = json_decode( rawurldecode( stripslashes( $_POST['plan_order'] ) ) );
		$selected_plans      = json_decode( rawurldecode( stripslashes( $_POST['selected_plans'] ) ) );
		$plans               = json_encode( $this->order_plans( $selected_plans, $plan_order ) );
		$showCustomInput     = $_POST['form_include_custom_input'];
		$customInputRequired = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$showCouponInput     = $_POST['form_include_coupon_input'];
		$sendEmailReceipt    = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$showAddressInput    = $_POST['form_show_address_input'];
		$customInputs        = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$buttonTitle         = $_POST['form_button_text'];
		$doRedirect          = $_POST['form_do_redirect'];
		// tnagy old form field name $redirectPostID = isset($_POST['form_redirect_post_id']) ? $_POST['form_redirect_post_id'] : 0;
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		try {
			$this->db->insert_subscription_form(
				array(
					'name'                    => $name,
					'formTitle'               => $title,
					'plans'                   => $plans,
					'showCouponInput'         => $showCouponInput,
					'showCustomInput'         => $showCustomInput,
					'customInputRequired'     => $customInputRequired,
					'customInputs'            => $customInputs,
					'redirectOnSuccess'       => $doRedirect,
					'redirectPostID'          => $redirectPostID,
					'redirectUrl'             => $redirectUrl,
					'redirectToPageOrPost'    => $redirectToPageOrPost,
					'sendEmailReceipt'        => $sendEmailReceipt,
					'showAddress'             => $showAddressInput,
					'buttonTitle'             => $buttonTitle,
					'showDetailedSuccessPage' => $showDetailedSuccessPage
				)
			);

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' )
			);

		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	private function order_plans( $selected_plans_array, $plan_order_array ) {
		$ordered_plans = array();
		if ( count( $selected_plans_array ) > 0 ) {
			foreach ( $plan_order_array as $plan ) {
				if ( in_array( $plan, $selected_plans_array ) ) {
					$ordered_plans[] = $plan;
				}
			}
		}

		return $ordered_plans;
	}

	private function add_http_prefix( $url ) {
		if ( ! isset( $url ) ) {
			return null;
		}
		if ( substr( $url, 0, 7 ) != 'http://' && substr( $url, 0, 8 ) != 'https://' ) {
			return 'http://' . $url;
		}

		return $url;
	}

	function fullstripe_edit_subscription_form_post() {
		$id                  = $_POST['formID'];
		$name                = $_POST['form_name'];
		$title               = $_POST['form_title'];
		$plan_order          = json_decode( rawurldecode( stripslashes( $_POST['plan_order'] ) ) );
		$selected_plans      = json_decode( rawurldecode( stripslashes( $_POST['selected_plans'] ) ) );
		$plans               = json_encode( $this->order_plans( $selected_plans, $plan_order ) );
		$showCustomInput     = $_POST['form_include_custom_input'];
		$customInputRequired = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$showCouponInput     = $_POST['form_include_coupon_input'];
		$sendEmailReceipt    = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$showAddressInput    = $_POST['form_show_address_input'];
		$customInputs        = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$buttonTitle         = $_POST['form_button_text_sub'];
		$doRedirect          = $_POST['form_do_redirect'];
		// $redirectPostID = isset($_POST['form_redirect_post_id']) ? $_POST['form_redirect_post_id'] : 0;
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		try {
			$this->db->update_subscription_form( $id,
				array(
					'name'                    => $name,
					'formTitle'               => $title,
					'plans'                   => $plans,
					'showCouponInput'         => $showCouponInput,
					'showCustomInput'         => $showCustomInput,
					'customInputRequired'     => $customInputRequired,
					'customInputs'            => $customInputs,
					'redirectOnSuccess'       => $doRedirect,
					'redirectPostID'          => $redirectPostID,
					'redirectUrl'             => $redirectUrl,
					'redirectToPageOrPost'    => $redirectToPageOrPost,
					'sendEmailReceipt'        => $sendEmailReceipt,
					'showAddress'             => $showAddressInput,
					'buttonTitle'             => $buttonTitle,
					'showDetailedSuccessPage' => $showDetailedSuccessPage
				)
			);

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' )
			);

		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_payment_form_post() {
		$name                    = $_POST['form_name'];
		$title                   = $_POST['form_title'];
		$currency                = $_POST['form_currency'];
		$amount                  = isset( $_POST['form_amount'] ) ? $_POST['form_amount'] : '0';
		$custom                  = $_POST['form_custom'];
		$listOfAmounts           = null;
		$allowListOfAmountCustom = 0;
		if ( $custom == 'list_of_amounts' ) {
			$listOfAmounts             = array();
			$paymentAmountValues       = explode( ',', sanitize_text_field( $_POST['payment_amount_values'] ) );
			$paymentAmountDescriptions = explode( ',', $_POST['payment_amount_descriptions'] );
			for ( $i = 0; $i < count( $paymentAmountValues ); $i ++ ) {
				$listElement = array( $paymentAmountValues[ $i ], rawurldecode( $paymentAmountDescriptions[ $i ] ) );
				array_push( $listOfAmounts, $listElement );
			}
			$allowListOfAmountCustom = isset( $_POST['allow_custom_payment_amount'] ) ? $_POST['allow_custom_payment_amount'] : 0;
		}
		$buttonTitle         = $_POST['form_button_text'];
		$showButtonAmount    = $_POST['form_button_amount'];
		$showCustomInput     = $_POST['form_include_custom_input'];
		$customInputRequired = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$customInputs        = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$doRedirect          = $_POST['form_do_redirect'];
		// $redirectPostID = isset($_POST['form_redirect_post_id']) ? $_POST['form_redirect_post_id'] : 0;
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showAddressInput        = $_POST['form_show_address_input'];
		$sendEmailReceipt        = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$formStyle               = $_POST['form_style'];
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		$data = array(
			'name'                     => $name,
			'formTitle'                => $title,
			'currency'                 => $currency,
			'amount'                   => $amount,
			'customAmount'             => $custom,
			'listOfAmounts'            => isset( $listOfAmounts ) ? json_encode( $listOfAmounts ) : null,
			'allowListOfAmountsCustom' => $allowListOfAmountCustom,
			'buttonTitle'              => $buttonTitle,
			'showButtonAmount'         => $showButtonAmount,
			'showEmailInput'           => 1,
			'showCustomInput'          => $showCustomInput,
			'customInputRequired'      => $customInputRequired,
			'customInputs'             => $customInputs,
			'redirectOnSuccess'        => $doRedirect,
			'redirectPostID'           => $redirectPostID,
			'redirectUrl'              => $redirectUrl,
			'redirectToPageOrPost'     => $redirectToPageOrPost,
			'showAddress'              => $showAddressInput,
			'sendEmailReceipt'         => $sendEmailReceipt,
			'formStyle'                => $formStyle,
			'showDetailedSuccessPage'  => $showDetailedSuccessPage
		);

		try {

			$this->db->insert_payment_form( $data );

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' )
			);
		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_edit_payment_form_post() {

		$id                      = $_POST['formID'];
		$name                    = $_POST['form_name'];
		$title                   = $_POST['form_title'];
		$currency                = $_POST['form_currency'];
		$amount                  = isset( $_POST['form_amount'] ) ? $_POST['form_amount'] : '0';
		$custom                  = $_POST['form_custom'];
		$listOfAmounts           = null;
		$allowListOfAmountCustom = 0;
		if ( $custom == 'list_of_amounts' ) {
			$listOfAmounts             = array();
			$paymentAmountValues       = explode( ',', sanitize_text_field( $_POST['payment_amount_values'] ) );
			$paymentAmountDescriptions = explode( ',', $_POST['payment_amount_descriptions'] );
			for ( $i = 0; $i < count( $paymentAmountValues ); $i ++ ) {
				$listElement = array( $paymentAmountValues[ $i ], rawurldecode( $paymentAmountDescriptions[ $i ] ) );
				array_push( $listOfAmounts, $listElement );
			}
			$allowListOfAmountCustom = isset( $_POST['allow_custom_payment_amount'] ) ? $_POST['allow_custom_payment_amount'] : 0;
		}
		$buttonTitle         = $_POST['form_button_text'];
		$showButtonAmount    = $_POST['form_button_amount'];
		$showCustomInput     = $_POST['form_include_custom_input'];
		$customInputRequired = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$customInputs        = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$doRedirect          = $_POST['form_do_redirect'];
		// $redirectPostID = isset($_POST['form_redirect_post_id']) ? $_POST['form_redirect_post_id'] : 0;
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showAddressInput        = $_POST['form_show_address_input'];
		$sendEmailReceipt        = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$formStyle               = $_POST['form_style'];
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		$data = array(
			'name'                     => $name,
			'formTitle'                => $title,
			'currency'                 => $currency,
			'amount'                   => $amount,
			'customAmount'             => $custom,
			'listOfAmounts'            => isset( $listOfAmounts ) ? json_encode( $listOfAmounts ) : null,
			'allowListOfAmountsCustom' => $allowListOfAmountCustom,
			'buttonTitle'              => $buttonTitle,
			'showButtonAmount'         => $showButtonAmount,
			'showEmailInput'           => 1,
			'showCustomInput'          => $showCustomInput,
			'customInputRequired'      => $customInputRequired,
			'customInputs'             => $customInputs,
			'redirectOnSuccess'        => $doRedirect,
			'redirectPostID'           => $redirectPostID,
			'redirectUrl'              => $redirectUrl,
			'redirectToPageOrPost'     => $redirectToPageOrPost,
			'showAddress'              => $showAddressInput,
			'sendEmailReceipt'         => $sendEmailReceipt,
			'formStyle'                => $formStyle,
			'showDetailedSuccessPage'  => $showDetailedSuccessPage
		);

		try {
			$this->db->update_payment_form( $id, $data );

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' )
			);
		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_checkout_form_post() {
		$name                    = $_POST['form_name'];
		$companyName             = $_POST['company_name'];
		$currency                = $_POST['form_currency'];
		$amount                  = isset( $_POST['form_amount'] ) ? $_POST['form_amount'] : '0';
		$custom                  = $_POST['form_custom'];
		$listOfAmounts           = null;
		$allowListOfAmountCustom = 0;
		if ( $custom == 'list_of_amounts' ) {
			$listOfAmounts             = array();
			$paymentAmountValues       = explode( ',', sanitize_text_field( $_POST['payment_amount_values'] ) );
			$paymentAmountDescriptions = explode( ',', $_POST['payment_amount_descriptions'] );
			for ( $i = 0; $i < count( $paymentAmountValues ); $i ++ ) {
				$listElement = array( $paymentAmountValues[ $i ], rawurldecode( $paymentAmountDescriptions[ $i ] ) );
				array_push( $listOfAmounts, $listElement );
			}
			$allowListOfAmountCustom = isset( $_POST['allow_custom_payment_amount'] ) ? $_POST['allow_custom_payment_amount'] : 0;
		}
		$productDescription   = $_POST['prod_desc'];
		$openButtonText       = $_POST['open_form_button_text'];
		$buttonText           = $_POST['form_button_text'];
		$showCustomInput      = $_POST['form_include_custom_input'];
		$customInputRequired  = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$customInputs         = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$sendEmailReceipt     = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$showBillingAddress   = $_POST['form_show_address_input'];
		$showRememberMe       = $_POST['form_show_remember_me'];
		$doRedirect           = $_POST['form_do_redirect'];
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$image                   = $_POST['form_checkout_image'];
		$disableStyling          = $_POST['form_disable_styling'];
		$useBitcoin              = isset( $_POST['form_use_bitcoin'] ) ? $_POST['form_use_bitcoin'] : 0;
		$useAlipay               = isset( $_POST['form_use_alipay'] ) ? $_POST['form_use_alipay'] : 0;
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		if ( "usd" !== $currency ) {
			$useAlipay  = 0;
			$useBitcoin = 0;
		}

		$data = array(
			'name'                     => $name,
			'companyName'              => $companyName,
			'currency'                 => $currency,
			'amount'                   => $amount,
			'customAmount'             => $custom,
			'listOfAmounts'            => isset( $listOfAmounts ) ? json_encode( $listOfAmounts ) : null,
			'allowListOfAmountsCustom' => $allowListOfAmountCustom,
			'productDesc'              => $productDescription,
			'openButtonTitle'          => $openButtonText,
			'buttonTitle'              => $buttonText,
			'showCustomInput'          => $showCustomInput,
			'customInputRequired'      => $customInputRequired,
			'customInputs'             => $customInputs,
			'sendEmailReceipt'         => $sendEmailReceipt,
			'showBillingAddress'       => $showBillingAddress,
			'showRememberMe'           => $showRememberMe,
			'redirectOnSuccess'        => $doRedirect,
			'redirectPostID'           => $redirectPostID,
			'redirectUrl'              => $redirectUrl,
			'redirectToPageOrPost'     => $redirectToPageOrPost,
			'image'                    => $image,
			'disableStyling'           => $disableStyling,
			'useBitcoin'               => $useBitcoin,
			'useAlipay'                => $useAlipay,
			'showDetailedSuccessPage'  => $showDetailedSuccessPage
		);

		try {
			$this->db->insert_checkout_form( $data );

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' )
			);
		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_edit_checkout_form_post() {

		$id                      = $_POST['formID'];
		$name                    = $_POST['form_name'];
		$companyName             = $_POST['company_name'];
		$currency                = $_POST['form_currency'];
		$amount                  = isset( $_POST['form_amount'] ) ? $_POST['form_amount'] : '0';
		$custom                  = $_POST['form_custom'];
		$listOfAmounts           = null;
		$allowListOfAmountCustom = 0;
		if ( $custom == 'list_of_amounts' ) {
			$listOfAmounts             = array();
			$paymentAmountValues       = explode( ',', sanitize_text_field( $_POST['payment_amount_values'] ) );
			$paymentAmountDescriptions = explode( ',', $_POST['payment_amount_descriptions'] );
			for ( $i = 0; $i < count( $paymentAmountValues ); $i ++ ) {
				$listElement = array( $paymentAmountValues[ $i ], rawurldecode( $paymentAmountDescriptions[ $i ] ) );
				array_push( $listOfAmounts, $listElement );
				MM_WPFS_Utils::log( 'listOfAmounts=' . print_r( $listOfAmounts, true ) );
			}
			$allowListOfAmountCustom = isset( $_POST['allow_custom_payment_amount'] ) ? $_POST['allow_custom_payment_amount'] : 0;
		}
		$productDescription   = $_POST['prod_desc'];
		$openButtonText       = $_POST['open_form_button_text'];
		$buttonText           = $_POST['form_button_text'];
		$showCustomInput      = $_POST['form_include_custom_input'];
		$customInputRequired  = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$customInputs         = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;
		$sendEmailReceipt     = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$showBillingAddress   = $_POST['form_show_address_input'];
		$showRememberMe       = $_POST['form_show_remember_me'];
		$doRedirect           = $_POST['form_do_redirect'];
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$image                   = $_POST['form_checkout_image'];
		$disableStyling          = $_POST['form_disable_styling'];
		$useBitcoin              = isset( $_POST['form_use_bitcoin'] ) ? $_POST['form_use_bitcoin'] : 0;
		$useAlipay               = isset( $_POST['form_use_alipay'] ) ? $_POST['form_use_alipay'] : 0;
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		$data = array(
			'name'                     => $name,
			'companyName'              => $companyName,
			'currency'                 => $currency,
			'amount'                   => $amount,
			'customAmount'             => $custom,
			'listOfAmounts'            => isset( $listOfAmounts ) ? json_encode( $listOfAmounts ) : null,
			'allowListOfAmountsCustom' => $allowListOfAmountCustom,
			'productDesc'              => $productDescription,
			'openButtonTitle'          => $openButtonText,
			'buttonTitle'              => $buttonText,
			'showCustomInput'          => $showCustomInput,
			'customInputRequired'      => $customInputRequired,
			'customInputs'             => $customInputs,
			'sendEmailReceipt'         => $sendEmailReceipt,
			'showBillingAddress'       => $showBillingAddress,
			'showRememberMe'           => $showRememberMe,
			'redirectOnSuccess'        => $doRedirect,
			'redirectPostID'           => $redirectPostID,
			'redirectUrl'              => $redirectUrl,
			'redirectToPageOrPost'     => $redirectToPageOrPost,
			'image'                    => $image,
			'disableStyling'           => $disableStyling,
			'useBitcoin'               => $useBitcoin,
			'useAlipay'                => $useAlipay,
			'showDetailedSuccessPage'  => $showDetailedSuccessPage
		);

		try {
			$this->db->update_checkout_form( $id, $data );

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' )
			);
		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-payments&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_checkout_subscription_form_post() {
		// tnagy payment tab
		$form_name            = $_POST['form_name'];
		$include_coupon_input = $_POST['form_include_coupon_input'];
		$plan_order           = json_decode( rawurldecode( stripslashes( $_POST['plan_order'] ) ) );
		$selected_plans       = json_decode( rawurldecode( stripslashes( $_POST['selected_plans'] ) ) );
		$plans                = json_encode( $this->order_plans( $selected_plans, $plan_order ) );

		// tnagy appearance tab
		// $form_title                  = $_POST['form_title'];
		$company_name                = $_POST['company_name'];
		$product_description         = $_POST['prod_desc'];
		$open_form_button_text       = $_POST['open_form_button_text'];
		$button_text                 = $_POST['form_button_text'];
		$show_address_input          = $_POST['form_show_address_input'];
		$show_shipping_address_input = isset( $_POST['form_show_shipping_address_input'] ) ? $_POST['form_show_shipping_address_input'] : 0;
		$show_remember_me            = $_POST['form_show_remember_me'];
		$checkout_image              = $_POST['form_checkout_image'];
		$disable_styling             = $_POST['form_disable_styling'];

		// tnagy custom fields tab
		$include_custom_input  = $_POST['form_include_custom_input'];
		$custom_input_required = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$custom_inputs         = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;

		// tnagy actions after payment tab
		$sendEmailReceipt     = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$doRedirect           = $_POST['form_do_redirect'];
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		try {
			$this->db->insert_checkout_subscription_form(
				array(
					'name'                    => $form_name,
					'showCouponInput'         => $include_coupon_input,
					'plans'                   => $plans,
					'companyName'             => $company_name,
					'productDesc'             => $product_description,
					'openButtonTitle'         => $open_form_button_text,
					'buttonTitle'             => $button_text,
					'showBillingAddress'      => $show_address_input,
					'showShippingAddress'     => $show_shipping_address_input,
					'showRememberMe'          => $show_remember_me,
					'image'                   => $checkout_image,
					'disableStyling'          => $disable_styling,
					'showCustomInput'         => $include_custom_input,
					'customInputRequired'     => $custom_input_required,
					'customInputs'            => $custom_inputs,
					'sendEmailReceipt'        => $sendEmailReceipt,
					'redirectOnSuccess'       => $doRedirect,
					'redirectPostID'          => $redirectPostID,
					'redirectUrl'             => $redirectUrl,
					'redirectToPageOrPost'    => $redirectToPageOrPost,
					'showDetailedSuccessPage' => $showDetailedSuccessPage
				)
			);

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' )
			);

		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_edit_checkout_subscription_form_post() {
		$id = $_POST['formID'];
		// tnagy payment tab
		$form_name            = $_POST['form_name'];
		$include_coupon_input = $_POST['form_include_coupon_input'];
		$plan_order           = json_decode( rawurldecode( stripslashes( $_POST['plan_order'] ) ) );
		$selected_plans       = json_decode( rawurldecode( stripslashes( $_POST['selected_plans'] ) ) );
		$plans                = json_encode( $this->order_plans( $selected_plans, $plan_order ) );

		// tnagy appearance tab
		// $form_title                  = $_POST['form_title'];
		$company_name                = $_POST['company_name'];
		$product_description         = $_POST['prod_desc'];
		$open_form_button_text       = $_POST['open_form_button_text'];
		$button_text                 = $_POST['form_button_text'];
		$show_address_input          = $_POST['form_show_address_input'];
		$show_shipping_address_input = isset( $_POST['form_show_shipping_address_input'] ) ? $_POST['form_show_shipping_address_input'] : 0;
		$show_remember_me            = $_POST['form_show_remember_me'];
		$checkout_image              = $_POST['form_checkout_image'];
		$disable_styling             = $_POST['form_disable_styling'];

		// tnagy custom fields tab
		$include_custom_input  = $_POST['form_include_custom_input'];
		$custom_input_required = isset( $_POST['form_custom_input_required'] ) ? $_POST['form_custom_input_required'] : 0;
		$custom_inputs         = isset( $_POST['customInputs'] ) ? $_POST['customInputs'] : null;

		// tnagy actions after payment tab
		$sendEmailReceipt     = isset( $_POST['form_send_email_receipt'] ) ? $_POST['form_send_email_receipt'] : 0;
		$doRedirect           = $_POST['form_do_redirect'];
		$redirectTo           = isset( $_POST['form_redirect_to'] ) ? $_POST['form_redirect_to'] : null;
		$redirectPostID       = isset( $_POST['form_redirect_page_or_post_id'] ) ? $_POST['form_redirect_page_or_post_id'] : 0;
		$redirectUrl          = isset( $_POST['form_redirect_url'] ) ? $_POST['form_redirect_url'] : null;
		$redirectUrl          = $this->add_http_prefix( $redirectUrl );
		$redirectToPageOrPost = 1;
		if ( 'page_or_post' === $redirectTo ) {
			$redirectToPageOrPost = 1;
		} else if ( 'url' === $redirectTo ) {
			$redirectToPageOrPost = 0;
		}
		$showDetailedSuccessPage = isset( $_POST['showDetailedSuccessPage'] ) ? $_POST['showDetailedSuccessPage'] : 0;

		try {
			$this->db->update_checkout_subscription_form( $id,
				array(
					'name'                    => $form_name,
					'showCouponInput'         => $include_coupon_input,
					'plans'                   => $plans,
					'companyName'             => $company_name,
					'productDesc'             => $product_description,
					'openButtonTitle'         => $open_form_button_text,
					'buttonTitle'             => $button_text,
					'showBillingAddress'      => $show_address_input,
					'showShippingAddress'     => $show_shipping_address_input,
					'showRememberMe'          => $show_remember_me,
					'image'                   => $checkout_image,
					'disableStyling'          => $disable_styling,
					'showCustomInput'         => $include_custom_input,
					'customInputRequired'     => $custom_input_required,
					'customInputs'            => $custom_inputs,
					'sendEmailReceipt'        => $sendEmailReceipt,
					'redirectOnSuccess'       => $doRedirect,
					'redirectPostID'          => $redirectPostID,
					'redirectUrl'             => $redirectUrl,
					'redirectToPageOrPost'    => $redirectToPageOrPost,
					'showDetailedSuccessPage' => $showDetailedSuccessPage
				)
			);

			$return = array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' )
			);

		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
			$return = array(
				'success'     => false,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=forms' ),
				'ex_msg'      => $e->getMessage(),
				'ex_stack'    => $e->getTraceAsString()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_update_settings_post() {
		if ( defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			header( "Content-Type: application/json" );
			echo json_encode( array(
				'success'     => true,
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-settings' )
			) );
			exit;
		}

		$validation_result = array();
		if ( ! $this->is_valid_options( $validation_result ) ) {
			header( "Content-Type: application/json" );
			echo json_encode( array( 'success' => false, 'validation_result' => $validation_result ) );
			exit;
		}

		// Save the posted value in the database
		$options = get_option( 'fullstripe_options' );

		$tab = null;
		if ( isset( $_POST['tab'] ) ) {
			$tab = sanitize_text_field( $_POST['tab'] );
		}
		if ( isset( $_POST['publishKey_test'] ) ) {
			$options['publishKey_test'] = sanitize_text_field( $_POST['publishKey_test'] );
		}
		if ( isset( $_POST['secretKey_test'] ) ) {
			$options['secretKey_test'] = sanitize_text_field( $_POST['secretKey_test'] );
		}
		if ( isset( $_POST['publishKey_live'] ) ) {
			$options['publishKey_live'] = sanitize_text_field( $_POST['publishKey_live'] );
		}
		if ( isset( $_POST['secretKey_live'] ) ) {
			$options['secretKey_live'] = sanitize_text_field( $_POST['secretKey_live'] );
		}
		if ( isset( $_POST['apiMode'] ) ) {
			$options['apiMode'] = sanitize_text_field( $_POST['apiMode'] );
		}
		if ( isset( $_POST['form_css'] ) ) {
			$options['form_css'] = stripslashes( $_POST['form_css'] );
		}
		if ( isset( $_POST['includeStyles'] ) ) {
			$options['includeStyles'] = sanitize_text_field( $_POST['includeStyles'] );
		}
		if ( isset( $_POST['receiptEmailType'] ) ) {
			$options['receiptEmailType'] = sanitize_text_field( $_POST['receiptEmailType'] );
		}
		if ( isset( $_POST['email_receipts'] ) ) {
			$options['email_receipts'] = json_encode( json_decode( rawurldecode( stripslashes( $_POST['email_receipts'] ) ) ) );
		}
		if ( isset( $_POST['email_receipt_sender_address'] ) ) {
			$options['email_receipt_sender_address'] = sanitize_email( $_POST['email_receipt_sender_address'] );
		}
		if ( isset( $_POST['admin_payment_receipt'] ) ) {
			$options['admin_payment_receipt'] = sanitize_text_field( $_POST['admin_payment_receipt'] );
		}
		if ( isset( $_POST['lock_email_field_for_logged_in_users'] ) ) {
			$options['lock_email_field_for_logged_in_users'] = sanitize_text_field( $_POST['lock_email_field_for_logged_in_users'] );
		}

		$activeTab = null;
		if ( $tab === 'stripe' ) {
			$activeTab = $tab;
		} else if ( $tab === 'appearance' ) {
			$activeTab = $tab;
		} else if ( $tab === 'email-receipts' ) {
			$activeTab = $tab;
		} else if ( $tab === 'users' ) {
			$activeTab = $tab;
		}
		update_option( 'fullstripe_options', $options );
		do_action( 'fullstripe_admin_update_options_action', $options );

		header( "Content-Type: application/json" );
		echo json_encode( array(
			'success'     => true,
			'redirectURL' => admin_url( 'admin.php?page=fullstripe-settings' . ( isset( $activeTab ) ? "&tab=$activeTab" : "" ) )
		) );
		exit;
	}

	private function is_valid_options( &$validation_result ) {
		if ( ! $this->is_not_set_or_empty( 'email_receipt_sender_address' ) ) {
			if ( ! filter_var( sanitize_email( $_POST['email_receipt_sender_address'] ), FILTER_VALIDATE_EMAIL ) ) {
				$validation_result['email_receipt_sender_address'] = __( 'Please enter a valid email address or leave the field empty', 'wp-full-stripe' );
			}
		}

		return empty( $validation_result );
	}

	private function is_not_set_or_empty( $key ) {
		return ! isset( $_POST[ $key ] ) || empty( $_POST[ $key ] );
	}

	function fullstripe_delete_payment_form() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_payment_form_action', $id );

			try {
				$this->db->delete_payment_form( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);

			}
		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_delete_subscription_form() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_subscription_form_action', $id );

			try {
				$this->db->delete_subscription_form( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}

		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_delete_checkout_form() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_checkout_form_action', $id );

			try {

				$this->db->delete_checkout_form( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => true,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}
		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_delete_checkout_subscription_form() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_checkout_subscription_form_action', $id );

			try {
				$this->db->delete_checkout_subscription_form( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}

		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_cancel_subscription() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];

			do_action( 'fullstripe_admin_delete_subscriber_action', $id );

			try {

				$subscriber = $this->db->find_subscriber_by_id( $id );

				if ( $subscriber ) {
					$this->db->cancel_subscription( $id );
					$this->stripe->cancel_subscription( $subscriber->stripeCustomerID, $subscriber->stripeSubscriptionID );
					$return = array(
						'success'     => true,
						'remove'      => false,
						'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions' )
					);
				} else {
					$return = array( 'success' => false );
				}
			} catch ( \Stripe\Error\InvalidRequest $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				// tnagy log exception but return success to continue on client side
				$return = array(
					'success'     => true,
					'with_errors' => true,
					'remove'      => false,
					'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions' ),
					'ex_msg'      => $e->getMessage(),
					'ex_stack'    => $e->getTraceAsString()
				);
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}

		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	public function fullstripe_delete_subscription_record() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];

			do_action( 'fullstripe_admin_delete_subscription_record_action', $id );

			try {

				$this->db->delete_subscription_by_id( $id );

				$return = array(
					'success'     => true,
					'remove'      => false,
					'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions' )
				);
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}

		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	/**
	 * @deprecated
	 */
	function fullstripe_delete_subscriber_local() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_subscriber_action', $id );

			try {
				$this->db->delete_subscriber( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}

		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_delete_payment_local() {
		if ( ! defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$id = $_POST['id'];
			do_action( 'fullstripe_admin_delete_payment_action', $id );

			try {
				$this->db->delete_payment( $id );

				$return = array( 'success' => true );
			} catch ( Exception $e ) {
				error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				$return = array(
					'success'  => false,
					'ex_msg'   => $e->getMessage(),
					'ex_stack' => $e->getTraceAsString()
				);
			}
		} else {
			$return = array( 'success' => true );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_recipient() {
		$token = $_POST['stripeToken'];
		$name  = $_POST['recipient_name'];
		$type  = $_POST['recipient_type'];
		$taxID = isset( $_POST['recipient_tax_id'] ) ? $_POST['recipient_tax_id'] : '';
		$email = isset( $_POST['recipient_email'] ) ? $_POST['recipient_email'] : '';

		$data = array(
			'name'         => $name,
			'type'         => $type,
			'bank_account' => $token
		);
		//optional fields
		if ( $taxID !== '' ) {
			$data['tax_id'] = $taxID;
		}
		if ( $email !== '' ) {
			$data['email'] = $email;
		}

		try {
			$recipient = $this->stripe->create_recipient( $data );

			do_action( 'fullstripe_admin_create_recipient_action', $recipient );

			$return = array( 'success' => true, 'msg' => 'Recipient created' );

		} catch ( Exception $e ) {
			//show notification of error
			$return = array(
				'success' => false,
				'msg'     => sprintf( __( 'There was an error creating the recipient: %s', 'wp-full-stripe' ), $e->getMessage() )
			);
		}

		//correct way to return JS results in wordpress
		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_recipient_card() {
		$token = $_POST['stripeToken'];
		$name  = $_POST['recipient_name_card'];
		$type  = $_POST['recipient_type_card'];
		$taxID = isset( $_POST['recipient_tax_id_card'] ) ? $_POST['recipient_tax_id_card'] : '';
		$email = isset( $_POST['recipient_email_card'] ) ? $_POST['recipient_email_card'] : '';

		$data = array(
			'name' => $name,
			'type' => $type,
			'card' => $token
		);
		//optional fields
		if ( $taxID !== '' ) {
			$data['tax_id'] = $taxID;
		}
		if ( $email !== '' ) {
			$data['email'] = $email;
		}

		try {
			$recipient = $this->stripe->create_recipient( $data );

			do_action( 'fullstripe_admin_create_recipient_action', $recipient );

			$return = array( 'success' => true, 'msg' => 'Recipient created' );

		} catch ( Exception $e ) {
			//show notification of error
			$return = array(
				'success' => false,
				'msg'     => __( 'There was an error creating the recipient: ', 'wp-full-stripe' ) . $e->getMessage()
			);
		}

		//correct way to return JS results in wordpress
		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_create_transfer() {
		$amount    = $_POST['transfer_amount'];
		$desc      = $_POST['transfer_desc'];
		$recipient = $_POST['transfer_recipient'];

		try {
			$transfer = $this->stripe->create_transfer( array(
				"amount"                => $amount,
				"currency"              => "usd",
				"recipient"             => $recipient,
				"statement_description" => $desc
			) );

			do_action( 'fullstripe_admin_create_transfer_action', $transfer );

			$return = array( 'success' => true );
		} catch ( Exception $e ) {
			//show notification of error
			$return = array(
				'success' => false,
				'msg'     => __( 'There was an error creating the transfer: ', 'wp-full-stripe' ) . $e->getMessage()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_edit_subscription_plan_post() {

		$plan_id              = stripslashes( $_POST['plan'] );
		$display_name         = $_POST['plan_display_name'];
		$statement_descriptor = isset( $_POST['plan_statement_descriptor'] ) ? $_POST['plan_statement_descriptor'] : null;
		$setup_fee            = isset( $_POST['plan_setup_fee'] ) ? $_POST['plan_setup_fee'] : null;

		try {

			$this->stripe->update_plan( $plan_id, array(
				'setup_fee'            => $setup_fee,
				'name'                 => $display_name,
				'statement_descriptor' => $statement_descriptor
			) );

			$return = array(
				'success'     => true,
				'msg'         => 'Subscription plan updated',
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' )
			);
		} catch ( Exception $e ) {
			$return = array(
				'success' => false,
				'msg'     => __( 'There was an error updating the subscription plan: ', 'wp-full-stripe' ) . $e->getMessage()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	function fullstripe_delete_subscription_plan() {

		$plan_id = stripslashes( $_POST['id'] );

		try {

			$this->stripe->delete_plan( $plan_id );

			$return = array(
				'success'     => true,
				'msg'         => 'Subscription plan deleted',
				'redirectURL' => admin_url( 'admin.php?page=fullstripe-subscriptions&tab=plans' )
			);
		} catch ( Exception $e ) {
			$return = array(
				'success' => false,
				'msg'     => __( 'There was an error deleting the subscription plan: ', 'wp-full-stripe' ) . $e->getMessage()
			);
		}

		header( "Content-Type: application/json" );
		echo json_encode( $return );
		exit;
	}

	/**
	 * Stripe Webhook handler
	 */
	function fullstripe_handle_wpfs_event() {

		error_log( 'DEBUG: ' . 'fullstripe_handle_wpfs_event():' . ' CALLED' );

		$auth_token    = empty( $_REQUEST['auth_token'] ) ? '' : $_REQUEST['auth_token'];
		$webhook_token = self::get_webhook_token();

		if ( $webhook_token != $auth_token ) {
			error_log( 'DEBUG: ' . 'fullstripe_handle_wpfs_event(): ' . 'Authentication failed, abort.' );
			// return HTTP Unathorized
			status_header( 401 );
			header( 'Content-Type: application/json' );
			exit;
		}

		try {

			// Retrieve the request's body and parse it as JSON
			$input = @file_get_contents( "php://input" );
			$event = json_decode( $input );

			// Do something with $event_json
			error_log( 'DEBUG: event=' . json_encode( $event ) );

			if ( isset( $event ) && isset( $event->type ) ) {
				$eventType = $event->type;
				if ( 'customer.subscription.deleted' == $eventType ) {
					$stripeSubscriptionID = $event->data->object->id;
					$subscription         = $this->db->find_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
					if ( isset( $subscription ) ) {
						if ( $subscription->status != 'ended' && $subscription->status != 'cancelled' ) {
							if ( $subscription->chargeMaximumCount > 0 ) {
								if ( $subscription->chargeCurrentCount >= $subscription->chargeMaximumCount ) {
									$this->db->complete_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
								} else {
									$this->db->cancel_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
								}
							} else {
								$this->db->cancel_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
							}
						}
					}
				} elseif ( 'invoice.payment_succeeded' == $eventType ) {
					foreach ( $event->data->object->lines->data as $line ) {
						$stripeSubscriptionID = $line->id;
						$subscription         = $this->db->find_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
						if ( isset( $subscription ) ) {
							if ( $subscription->status != 'ended' && $subscription->status != 'cancelled' ) {
								$this->db->update_subscription_with_payment( $stripeSubscriptionID );
							}
						}
					}
				} elseif ( 'invoice.created' == $eventType ) {
					foreach ( $event->data->object->lines->data as $line ) {
						$stripeSubscriptionID = $line->id;
						$subscription         = $this->db->find_subscription_by_stripe_subscription_id( $stripeSubscriptionID );
						if ( isset( $subscription ) ) {
							if ( $subscription->status != 'ended' && $subscription->status != 'cancelled' ) {
								if ( $subscription->chargeMaximumCount > 0 ) {
									if ( $subscription->chargeCurrentCount >= $subscription->chargeMaximumCount ) {
										$this->complete_subscription( $subscription );
									}
								}
							}
						}
					}
				}
			}
			// return HTTP OK
			status_header( 200 );
		} catch ( Exception $e ) {
			error_log( 'ERROR: Message=' . $e->getMessage() . ', Trace=' . $e->getTraceAsString() );
			// return HTTP Internal Server Error
			status_header( 500 );
		}

		header( "Content-Type: application/json" );
		exit;
	}

	/**
	 * Generates the md5 hash by site_url and admin_email to create a unique ID for a WordPress installation.
	 * @return string
	 */
	public static function get_webhook_token() {
		$options = get_option( 'fullstripe_options' );

		return $options['webhook_token'];
	}

	/**
	 * @param $subscription
	 */
	private function complete_subscription( $subscription ) {

		$this->db->complete_subscription_by_stripe_subscription_id( $subscription->stripeSubscriptionID );
		$this->stripe->cancel_subscription( $subscription->stripeCustomerID, $subscription->stripeSubscriptionID );

		$plan           = $this->stripe->retrieve_plan( $subscription->planID );
		$plan_setup_fee = 0;
		if ( isset( $plan->metadata ) && isset( $plan->metadata->setup_fee ) ) {
			$plan_setup_fee = $plan->metadata->setup_fee;
		}

		$address      = MM_WPFS_Utils::prepare_billing_address_data( $subscription->addressLine1, $subscription->addressLine2, $subscription->addressCity, $subscription->addressState, $subscription->addressZip, $subscription->addressCountry );
		$product_name = '';

		$send_receipt = false;
		$subscriber   = $this->db->find_subscription_by_stripe_subscription_id( $subscription->stripeSubscriptionID );
		if ( isset( $subscriber ) ) {
			$form_send_receipt = false;
			$form              = $this->db->get_subscription_form_by_id( $subscriber->formId );
			if ( isset( $form ) ) {
				$form_send_receipt = $form->sendEmailReceipt == 1 ? true : false;
			}
			if ( $form_send_receipt ) {
				$options           = get_option( 'fullstripe_options' );
				$send_plugin_email = false;
				if ( $options['receiptEmailType'] == 'plugin' ) {
					$send_plugin_email = true;
				}
				$send_receipt = $form_send_receipt && $send_plugin_email;
			}
		}

		if ( $send_receipt ) {
			$this->mailer->send_subscription_finished_email_receipt( $subscription->email, $plan->name, $plan->currency, $plan_setup_fee, $plan->amount, $subscription->name, $address, $product_name );
		}

	}

}
