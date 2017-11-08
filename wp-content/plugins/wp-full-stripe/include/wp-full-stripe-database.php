<?php

class MM_WPFS_Database {

	/**
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function fullstripe_setup_db() {
		//require for dbDelta()
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;

		// todo tnagy we should use charset and collate definitions in our scripts like below
		// $charset_collate = $wpdb->get_charset_collate(); // this funcrion can be used from WP3.5 or create manually
		// $sql = "CREATE TABLE (...) $charset_collate;";

		$table = $wpdb->prefix . 'fullstripe_payments';

		$sql = "CREATE TABLE " . $table . " (
        paymentID INT NOT NULL AUTO_INCREMENT,
        eventID VARCHAR(100) NOT NULL,
        description VARCHAR(255) NOT NULL,
        paid TINYINT(1),
        livemode TINYINT(1),
        currency VARCHAR(3) NOT NULL,
        amount INT NOT NULL,
        fee INT NOT NULL,
        addressLine1 VARCHAR(500) NOT NULL,
        addressLine2 VARCHAR(500) NOT NULL,
        addressCity VARCHAR(500) NOT NULL,
        addressState VARCHAR(255) NOT NULL,
        addressZip VARCHAR(100) NOT NULL,
        addressCountry VARCHAR(100) NOT NULL,
        created DATETIME NOT NULL,
        stripeCustomerID VARCHAR(100),
        name VARCHAR(100),
        email VARCHAR(255) NOT NULL,
        formId INT,
        formType VARCHAR(30),
        UNIQUE KEY paymentID (paymentID)
        );";

		//database write/update
		dbDelta( $sql );

		$table = $wpdb->prefix . 'fullstripe_payment_forms';

		$sql = "CREATE TABLE " . $table . " (
        paymentFormID INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        formTitle VARCHAR(100) NOT NULL,
        amount INT NOT NULL,
        currency VARCHAR(3) NOT NULL,
        customAmount VARCHAR(32) NOT NULL,
        listOfAmounts VARCHAR(1024) DEFAULT NULL,
        allowListOfAmountsCustom TINYINT(1) DEFAULT '0',
        buttonTitle VARCHAR(100) NOT NULL DEFAULT 'Make Payment',
        showButtonAmount TINYINT(1) DEFAULT '1',
        showEmailInput TINYINT(1) DEFAULT '1',
        showCustomInput TINYINT(1) DEFAULT '0',
        customInputRequired TINYINT(1) DEFAULT '0',
        customInputTitle VARCHAR(100) NOT NULL DEFAULT 'Extra Information',
        customInputs TEXT,
        redirectOnSuccess TINYINT(1) DEFAULT '0',
        redirectPostID INT(5) DEFAULT 0,
        redirectUrl VARCHAR(1024) DEFAULT NULL,
        redirectToPageOrPost TINYINT(1) DEFAULT '1',
        showDetailedSuccessPage TINYINT(1) DEFAULT '0',
        showAddress TINYINT(1) DEFAULT '0',
        sendEmailReceipt TINYINT(1) DEFAULT '0',
        formStyle INT(5) DEFAULT 0,
        UNIQUE KEY paymentFormID (paymentFormID)
        );";

		//database write/update
		dbDelta( $sql );

		// tnagy migrate old values
		$sql         = "UPDATE $table SET customAmount = 'specified_amount' WHERE customAmount = '0'";
		$queryResult = $wpdb->query( $sql );
		self::handleDbError( $queryResult, __( 'Migration of fullstripe_payment_forms/customAmount failed!', 'wp-full-stripe' ) );

		$sql         = "UPDATE $table SET customAmount = 'custom_amount' WHERE customAmount = '1'";
		$queryResult = $wpdb->query( $sql );
		self::handleDbError( $queryResult, __( 'Migration of fullstripe_payment_forms/customAmount failed!', 'wp-full-stripe' ) );

		$table = $wpdb->prefix . 'fullstripe_subscription_forms';

		$sql = "CREATE TABLE " . $table . " (
        subscriptionFormID INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        formTitle VARCHAR(100) NOT NULL,
        plans VARCHAR(255) NOT NULL,
        showCouponInput TINYINT(1) DEFAULT '0',
        showCustomInput TINYINT(1) DEFAULT '0',
        customInputRequired TINYINT(1) DEFAULT '0',
        customInputTitle VARCHAR(100) NOT NULL DEFAULT 'Extra Information',
        customInputs TEXT,
        redirectOnSuccess TINYINT(1) DEFAULT '0',
        redirectPostID INT(5) DEFAULT 0,
        redirectUrl VARCHAR(1024) DEFAULT NULL,
        redirectToPageOrPost TINYINT(1) DEFAULT '1',
        showDetailedSuccessPage TINYINT(1) DEFAULT '0',
        showAddress TINYINT(1) DEFAULT '0',
        sendEmailReceipt TINYINT(1) DEFAULT '0',
        formStyle INT(5) DEFAULT 0,
        buttonTitle VARCHAR(100) NOT NULL DEFAULT 'Subscribe',
        setupFee INT NOT NULL DEFAULT '0',
        UNIQUE KEY subscriptionFormID (subscriptionFormID)
        );";

		//database write/update
		dbDelta( $sql );

		$table = $wpdb->prefix . 'fullstripe_subscribers';

		$sql = "CREATE TABLE " . $table . " (
        subscriberID INT NOT NULL AUTO_INCREMENT,
        stripeCustomerID VARCHAR(100) NOT NULL,
        stripeSubscriptionID VARCHAR(100) NOT NULL,
		chargeMaximumCount INT(5) NOT NULL,
		chargeCurrentCount INT(5) NOT NULL,
		status VARCHAR(32) NOT NULL,
		cancelled DATETIME DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        planID VARCHAR(100) NOT NULL,
        addressLine1 VARCHAR(500) NOT NULL,
        addressLine2 VARCHAR(500) NOT NULL,
        addressCity VARCHAR(500) NOT NULL,
        addressState VARCHAR(255) NOT NULL,
        addressZip VARCHAR(100) NOT NULL,
        addressCountry VARCHAR(100) NOT NULL,
        created DATETIME NOT NULL,
        livemode TINYINT(1),
        formId INT,
        UNIQUE KEY subscriberID (subscriberID),
		KEY stripeSubscriptionID (stripeSubscriptionID)
        );";

		//database write/update
		dbDelta( $sql );

		$table = $wpdb->prefix . 'fullstripe_checkout_forms';

		$sql = "CREATE TABLE " . $table . " (
        checkoutFormID INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        companyName VARCHAR(100) NOT NULL,
        productDesc VARCHAR(100) NOT NULL,
        amount INT NOT NULL,
        currency VARCHAR(3) NOT NULL,
        customAmount VARCHAR(32) NOT NULL,
        listOfAmounts VARCHAR(1024) DEFAULT NULL,
        allowListOfAmountsCustom TINYINT(1) DEFAULT '0',
        openButtonTitle VARCHAR(100) NOT NULL DEFAULT 'Pay With Card',
        buttonTitle VARCHAR(100) NOT NULL DEFAULT 'Pay {{amount}}',
        showBillingAddress TINYINT(1) DEFAULT '0',
        showShippingAddress TINYINT(1) DEFAULT '0',
        showCustomInput TINYINT(1) DEFAULT '0',
        customInputRequired TINYINT(1) DEFAULT '0',
        customInputTitle VARCHAR(100) NOT NULL DEFAULT 'Extra Information',
        customInputs TEXT,
        sendEmailReceipt TINYINT(1) DEFAULT '0',
        showRememberMe TINYINT(1) DEFAULT '0',
        image VARCHAR(500) NOT NULL DEFAULT '/img/checkout.png',
        redirectOnSuccess TINYINT(1) DEFAULT '0',
        redirectPostID INT(5) DEFAULT 0,
        redirectUrl VARCHAR(1024) DEFAULT NULL,
        redirectToPageOrPost TINYINT(1) DEFAULT '1',
        showDetailedSuccessPage TINYINT(1) DEFAULT '0',
        disableStyling TINYINT(1) DEFAULT 0,
        useBitcoin TINYINT(1) DEFAULT '0',
        useAlipay TINYINT(1) DEFAULT '0',
        UNIQUE KEY checkoutFormID (checkoutFormID)
        );";

		//database write/update
		dbDelta( $sql );

		//default form
		$defaultPaymentForm = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payment_forms" . " WHERE name='default';" );
		if ( $defaultPaymentForm === null ) {
			$data         = array(
				'name'         => 'default',
				'formTitle'    => 'Payment',
				'amount'       => 1000, //$10.00
				'currency'     => 'usd',
				'customAmount' => 'specified_amount'
			);
			$formats      = array( '%s', '%s', '%d', '%s', '%s' );
			$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_payment_forms', $data, $formats );
			self::handleDbError( $insertResult, __( 'Cannot insert default form!', 'wp-full-stripe' ) );
		}


		$sql = "CREATE TABLE {$wpdb->prefix}fullstripe_patch_info (
		id INT NOT NULL AUTO_INCREMENT,
		patch_id VARCHAR(255) NOT NULL,
		plugin_version VARCHAR(255) NOT NULL,
		applied_at DATETIME NOT NULL,
		description VARCHAR(500),
		UNIQUE KEY id (id),
		KEY patch_id (patch_id)
		);";

		dbDelta( $sql );

		$table = $wpdb->prefix . 'fullstripe_checkout_subscription_forms';

		$sql = "CREATE TABLE " . $table . " (
		checkoutSubscriptionFormID INT NOT NULL AUTO_INCREMENT,
		name VARCHAR(100) NOT NULL,
		companyName VARCHAR(100) NOT NULL,
		productDesc VARCHAR(100) NOT NULL,
		image VARCHAR(500) NOT NULL DEFAULT '/img/checkout.png',
		plans VARCHAR(255) NOT NULL,
		showCouponInput TINYINT(1) DEFAULT '0',
		showCustomInput TINYINT(1) DEFAULT '0',
		customInputRequired TINYINT(1) DEFAULT '0',
		customInputTitle VARCHAR(100) NOT NULL DEFAULT 'Extra Information',
		customInputs TEXT,
		redirectOnSuccess TINYINT(1) DEFAULT '0',
		redirectPostID INT(5) DEFAULT 0,
		redirectUrl VARCHAR(1024) DEFAULT NULL,
		redirectToPageOrPost TINYINT(1) DEFAULT '1',
		showDetailedSuccessPage TINYINT(1) DEFAULT '0',
		showBillingAddress TINYINT(1) DEFAULT '0',
		showShippingAddress TINYINT(1) DEFAULT '0',
		sendEmailReceipt TINYINT(1) DEFAULT '0',
		disableStyling TINYINT(1) DEFAULT 0,
        openButtonTitle VARCHAR(100) NOT NULL DEFAULT 'Pay With Card',
		buttonTitle VARCHAR(100) NOT NULL DEFAULT 'Subscribe',
		showRememberMe TINYINT(1) DEFAULT '0',
        UNIQUE KEY checkoutSubscriptionFormID (checkoutSubscriptionFormID)
        );";

		//database write/update
		dbDelta( $sql );

		do_action( 'fullstripe_setup_db' );

		return true;
	}

	/**
	 *
	 * @param $result
	 *
	 * @param $message
	 *
	 * @throws Exception
	 */
	private static function handleDbError( $result, $message ) {
		if ( $result === false ) {
			global $wpdb;
			error_log( sprintf( "%s: Raised exception with message=%s", 'WP Full Stripe/Database', $message ) );
			error_log( sprintf( "%s: SQL last error=%s", 'WP Full Stripe/Database', $wpdb->last_error ) );
			throw new Exception( $message );
		}
	}

	/**
	 *
	 * @param $stripe_charge
	 * @param $address
	 * @param $stripe_customer_id
	 * @param $customer_name
	 * @param $customer_email
	 * @param $form_id
	 * @param $form_type
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function fullstripe_insert_payment( $stripe_charge, $address, $stripe_customer_id, $customer_name, $customer_email, $form_id, $form_type ) {
		global $wpdb;

		$data = array(
			'eventID'          => $stripe_charge->id,
			'description'      => $stripe_charge->description,
			'paid'             => $stripe_charge->paid,
			'livemode'         => $stripe_charge->livemode,
			'currency'         => $stripe_charge->currency,
			'amount'           => $stripe_charge->amount,
			'fee'              => ( isset( $stripe_charge->fee ) && ! is_null( $stripe_charge->fee ) ) ? $stripe_charge->fee : 0,
			'addressLine1'     => $address['line1'],
			'addressLine2'     => $address['line2'],
			'addressCity'      => $address['city'],
			'addressState'     => $address['state'],
			'addressCountry'   => $address['country'],
			'addressZip'       => $address['zip'],
			'created'          => date( 'Y-m-d H:i:s', $stripe_charge->created ),
			'stripeCustomerID' => $stripe_customer_id,
			'name'             => $customer_name,
			'email'            => $customer_email,
			'formId'           => $form_id,
			'formType'         => $form_type
		);

		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_payments', apply_filters( 'fullstripe_insert_payment_data', $data ) );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert!', 'wp-full-stripe' ), 'Insert payment' ) );

		return $insertResult;
	}

	/**
	 *
	 * @param $customer
	 * @param $customerName
	 * @param $address
	 * @param $formId
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function fullstripe_insert_subscriber( $customer, $customerName, $address, $formId ) {
		$maximumCharge = 0;
		if ( isset( $customer->subscription ) && isset( $customer->subscription->plan ) && isset( $customer->subscription->plan->metadata ) && isset( $customer->subscription->plan->metadata->cancellation_count ) ) {
			$maximumCharge = intval( $customer->subscription->plan->metadata->cancellation_count );
		}
		$data = array(
			'stripeCustomerID'     => $customer->id,
			'stripeSubscriptionID' => $customer->subscription->id,
			'chargeMaximumCount'   => $maximumCharge,
			'chargeCurrentCount'   => 0,
			'status'               => 'running',
			'name'                 => $customerName,
			'email'                => $customer->email,
			'planID'               => $customer->subscription->plan->id,
			'addressLine1'         => $address['line1'],
			'addressLine2'         => $address['line2'],
			'addressCity'          => $address['city'],
			'addressState'         => $address['state'],
			'addressCountry'       => $address['country'],
			'addressZip'           => $address['zip'],
			'created'              => date( 'Y-m-d H:i:s' ),
			'livemode'             => $customer->livemode,
			'formId'               => $formId
		);

		global $wpdb;
		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_subscribers', apply_filters( 'fullstripe_insert_subscriber_data', $data ) );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert!', 'wp-full-stripe' ), 'Insert subscriber' ) );

		return $insertResult;
	}

	/**
	 * @deprecated unused
	 *
	 * @param $stripeCustomerID
	 *
	 * @return mixed
	 */
	function get_subscriber_by_stripeID( $stripeCustomerID ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscribers" . " WHERE stripeCustomerID='" . $stripeCustomerID . "';" );
	}

	/**
	 *
	 * @param $email
	 * @param bool $livemode
	 *
	 * @return mixed
	 */
	function get_subscriber_by_email( $email, $livemode = true ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscribers" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . ";" );
	}

	/**
	 * @deprecated
	 *
	 * @param $id
	 * @param $subscriber
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function update_subscriber( $id, $subscriber ) {
		global $wpdb;
		$updateResult = $wpdb->update( $wpdb->prefix . 'fullstripe_subscribers', $subscriber, array( 'subscriberID' => $id ) );
		self::handleDbError( $updateResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update subscriber' ) );

		return $updateResult;
	}

	/**
	 *
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function insert_subscription_form( $form ) {
		global $wpdb;
		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_subscription_forms', $form );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert!', 'wp-full-stripe' ), 'Insert subscription form' ) );

		return $insertResult;
	}

	/**
	 *
	 * @param $id
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function update_subscription_form( $id, $form ) {
		global $wpdb;
		$updateResult = $wpdb->update( $wpdb->prefix . 'fullstripe_subscription_forms', $form, array( 'subscriptionFormID' => $id ) );
		self::handleDbError( $updateResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update subscription form' ) );

		return $updateResult;
	}

	/**
	 * @param $form
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public function insert_checkout_subscription_form( $form ) {
		global $wpdb;
		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_checkout_subscription_forms', $form );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert!', 'wp-full-stripe' ), 'Insert checkout subscription form' ) );

		return $insertResult;
	}

	public function update_checkout_subscription_form( $id, $form ) {
		global $wpdb;
		$updateResult = $wpdb->update( $wpdb->prefix . 'fullstripe_checkout_subscription_forms', $form, array( 'checkoutSubscriptionFormID' => $id ) );
		self::handleDbError( $updateResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update checkout subscription form' ) );

		return $updateResult;
	}

	/**
	 *
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function insert_payment_form( $form ) {
		global $wpdb;
		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_payment_forms', $form );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert.', 'wp-full-stripe' ), 'Insert payment form' ) );

		return $insertResult;
	}

	/**
	 *
	 * @param $id
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function update_payment_form( $id, $form ) {
		global $wpdb;
		$updateResult = $wpdb->update( $wpdb->prefix . 'fullstripe_payment_forms', $form, array( 'paymentFormID' => $id ) );
		self::handleDbError( $updateResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update payment form' ) );

		return $updateResult;
	}

	/**
	 *
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function insert_checkout_form( $form ) {
		global $wpdb;
		$insertResult = $wpdb->insert( $wpdb->prefix . 'fullstripe_checkout_forms', $form );
		self::handleDbError( $insertResult, sprintf( __( '%s: an error occurred during insert!', 'wp-full-stripe' ), 'Insert checkout form' ) );

		return $insertResult;
	}

	/**
	 *
	 * @param $id
	 * @param $form
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function update_checkout_form( $id, $form ) {
		global $wpdb;
		$updateResult = $wpdb->update( $wpdb->prefix . 'fullstripe_checkout_forms', $form, array( 'checkoutFormID' => $id ) );
		self::handleDbError( $updateResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update checkout form' ) );

		return $updateResult;
	}

	/**
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function delete_payment_form( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_payment_forms' . " WHERE paymentFormID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete payment form' ) );

		return $queryResult;
	}

	/**
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function delete_subscription_form( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_subscription_forms' . " WHERE subscriptionFormID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete subscription form' ) );

		return $queryResult;
	}

	/**
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function delete_checkout_form( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_checkout_forms' . " WHERE checkoutFormID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete checkout form' ) );

		return $queryResult;
	}

	/**
	 * @param $id
	 *
	 * @return false|int
	 * @throws Exception
	 */
	function delete_checkout_subscription_form( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_checkout_subscription_forms' . " WHERE checkoutSubscriptionFormID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete checkout subscription form' ) );

		return $queryResult;
	}

	/**
	 * @deprecated
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function delete_subscriber( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_subscribers' . " WHERE subscriberID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete subscriber' ) );

		return $queryResult;
	}

	/**
	 * @param $id
	 *
	 * @return false|int
	 * @throws Exception
	 */
	function cancel_subscription( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fullstripe_subscribers SET status=%s WHERE subscriberID=%d", 'cancelled', $id ) );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Cancel subscription' ) );

		return $queryResult;
	}

	/**
	 * @param $id
	 *
	 * @return false|int
	 * @throws Exception
	 */
	function delete_subscription_by_id( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}fullstripe_subscribers WHERE subscriberID=%d", $id ) );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete subscription record' ) );

		return $queryResult;
	}

	/**
	 * @param $stripeSubscriptionID
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public function cancel_subscription_by_stripe_subscription_id( $stripeSubscriptionID ) {
		global $wpdb;
		$queryResult = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fullstripe_subscribers SET status=%s,cancelled=NOW() WHERE stripeSubscriptionID=%s", 'cancelled', $stripeSubscriptionID ) );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Cancel subscription by Stripe Subscription ID' ) );

		return $queryResult;
	}

	/**
	 * @param $stripeSubscriptionID
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public function complete_subscription_by_stripe_subscription_id( $stripeSubscriptionID ) {
		global $wpdb;
		$queryResult = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fullstripe_subscribers SET status=%s,cancelled=NOW() WHERE stripeSubscriptionID=%s", 'ended', $stripeSubscriptionID ) );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Complete subscription by Stripe Subscription ID' ) );

		return $queryResult;
	}

	/**
	 * @param $stripeSubscriptionID
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public function update_subscription_with_payment( $stripeSubscriptionID ) {
		global $wpdb;
		$queryResult = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fullstripe_subscribers SET chargeCurrentCount=chargeCurrentCount + 1 WHERE stripeSubscriptionID=%s", $stripeSubscriptionID ) );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during update!', 'wp-full-stripe' ), 'Update subscription charge count by Stripe Subscription ID' ) );

		return $queryResult;
	}

	/**
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	function delete_payment( $id ) {
		global $wpdb;
		$queryResult = $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'fullstripe_payments' . " WHERE paymentID='" . $id . "';" );
		self::handleDbError( $queryResult, sprintf( __( '%s: an error occurred during delete!', 'wp-full-stripe' ), 'Delete payment' ) );

		return $queryResult;
	}

	////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	function get_payment_form_by_name( $name ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payment_forms" . " WHERE name='" . $name . "';" );
	}

	/**
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	function get_subscription_form_by_name( $name ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscription_forms" . " WHERE name='" . $name . "';" );
	}

	/**
	 * @param $formId
	 *
	 * @return array|null|object|void
	 */
	public function get_subscription_form_by_id( $formId ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_subscription_forms WHERE subscriptionFormID=%d", $formId ) );
	}

	/**
	 * @param $formId
	 *
	 * @return array|null|object|void
	 */
	public function get_checkout_subscription_form_by_id( $formId ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_checkout_subscription_forms WHERE checkoutSubscriptionFormID=%d", $formId ) );
	}

	/**
	 * @param $formName
	 *
	 * @return mixed
	 */
	public function get_checkout_form_by_name( $formName ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_checkout_forms WHERE name=%s", $formName ) );
	}

	/**
	 * @param $formName
	 *
	 * @return array|null|object|void
	 */
	public function get_checkout_subscription_form_by_name( $formName ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_checkout_subscription_forms WHERE name=%s", $formName ) );
	}

	/**
	 *
	 * @param $email
	 * @param $livemode
	 *
	 * @return null
	 */
	public function get_customer_id_from_payments( $email, $livemode ) {
		global $wpdb;
		$id      = null;
		$payment = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payments" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . ";" );
		if ( $payment ) {
			// if no ID set, will be set to null.
			$id = $payment->stripeCustomerID;
		}

		return $id;
	}


	/**
	 *
	 * search payments and subscribers table for existing customer
	 *
	 * @param $email
	 * @param $livemode
	 *
	 * @return null
	 */
	public function find_existing_stripe_customer_by_email( $email, $livemode ) {
		global $wpdb;
		$subscriber = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscribers" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . ";", ARRAY_A );
		if ( $subscriber ) {
			$subscriber['is_subscriber'] = true;

			return $subscriber;
		} else {
			$payment = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payments" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . ";", ARRAY_A );
			if ( $payment ) {
				$subscriber['is_subscriber'] = false;

				return $payment;
			}
		}

		return null;
	}


	/**
	 *
	 * return customers from the payment and subscriber tables where the email address and the mode match
	 *
	 * @param $email
	 * @param $livemode
	 *
	 * @return null
	 */
	public function get_existing_stripe_customers_by_email( $email, $livemode ) {
		global $wpdb;

		$subscribers = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_subscribers" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . " group by StripeCustomerID;", ARRAY_A );
		$payees      = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "fullstripe_payments" . " WHERE email='" . $email . "' AND livemode=" . ( $livemode ? '1' : '0' ) . " group by StripeCustomerID;", ARRAY_A );

		$res = array_merge( $subscribers, $payees );

		return $res;
	}


	/**
	 * @param $id
	 *
	 * @return array|null|object|void
	 */
	public function find_subscriber_by_id( $id ) {
		global $wpdb;
		$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_subscribers WHERE subscriberID=%d", $id ) );

		return $subscription;
	}

	/**
	 * @param $stripe_subscription_id
	 *
	 * @return array|null|object|void
	 */
	public function find_subscription_by_stripe_subscription_id( $stripe_subscription_id ) {
		global $wpdb;
		$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fullstripe_subscribers WHERE stripeSubscriptionID=%s", $stripe_subscription_id ) );

		return $subscription;
	}


}

?>