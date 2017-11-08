<?php

/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.11.29.
 * Time: 16:38
 */
class MM_WPFS_TransactionDataService {

	const KEY_PREFIX = 'wpfs_td_';

	public static function create_payment_data( $customer_email, $currency, $amount, $product_name, $customer_name, $billing_address, $custom_input_values = null ) {
		$an_instance = new MM_WPFS_TransactionData();

		$an_instance->setCustomerEmail( $customer_email );
		$an_instance->setCurrency( $currency );
		$an_instance->setAmount( $amount );
		$an_instance->setProductName( $product_name );
		$an_instance->setCustomerName( $customer_name );
		$an_instance->setBillingAddress( $billing_address );
		$an_instance->setCustomInputValues( $custom_input_values );

		return $an_instance;
	}

	/**
	 * @param $customer_email
	 * @param $plan_name
	 * @param $plan_currency
	 * @param $plan_amount
	 * @param $plan_setup_fee
	 * @param $product_name
	 * @param $customer_name
	 * @param $billing_address
	 * @param $custom_input_values
	 *
	 * @return MM_WPFS_SubscriptionTransactionData
	 */
	public static function create_subscription_data( $customer_email, $plan_name, $plan_currency, $plan_amount, $plan_setup_fee, $product_name, $customer_name, $billing_address, $custom_input_values ) {
		$an_instance = new MM_WPFS_SubscriptionTransactionData();

		$an_instance->setCustomerEmail( $customer_email );
		$an_instance->setPlanName( $plan_name );
		$an_instance->setPlanCurrency( $plan_currency );
		$an_instance->setPlanAmount( $plan_amount );
		$an_instance->setPlanSetupFee( $plan_setup_fee );
		$an_instance->setProductName( $product_name );
		$an_instance->setCustomerName( $customer_name );
		$an_instance->setBillingAddress( $billing_address );
		$an_instance->setCustomInputValues( $custom_input_values );

		return $an_instance;
	}

	/**
	 * Store transaction data as a transient.
	 *
	 * @param $data MM_WPFS_TransactionData
	 *
	 * @return null|string
	 */
	public function store( $data ) {
		$key = $this->generate_key();
		set_transient( $key, $data );

		return rawurlencode( $key );
	}

	/**
	 * Generates a random key currently not in use as a transient key.
	 */
	private function generate_key() {
		$key = null;
		do {
			$key = self::KEY_PREFIX . crypt( strval( round( microtime( true ) * 1000 ) ), strval( rand() ) );
		} while ( get_transient( $key ) !== false );

		return $key;
	}

	/**
	 * @param $data_key
	 *
	 * @return bool|MM_WPFS_TransactionData
	 */
	public function retrieve( $data_key ) {
		if ( is_null( $data_key ) ) {
			return false;
		}
		$prefix_position = strpos( $data_key, self::KEY_PREFIX );
		if ( $prefix_position === false ) {
			return false;
		}
		if ( $prefix_position == 0 ) {
			$data = get_transient( $data_key );

			if ( $data !== false ) {
				delete_transient( $data_key );
			}

			return $data;
		} else {
			return false;
		}
	}

}

class MM_WPFS_TransactionData {

	protected $customer_name;
	protected $customer_email;
	protected $billing_address;
	protected $currency;
	protected $amount;
	protected $product_name;
	protected $custom_input_values;

	/**
	 * @return mixed
	 */
	public function getCustomerName() {
		return $this->customer_name;
	}

	/**
	 * @param mixed $customer_name
	 */
	public function setCustomerName( $customer_name ) {
		$this->customer_name = $customer_name;
	}

	/**
	 * @return mixed
	 */
	public function getCustomerEmail() {
		return $this->customer_email;
	}

	/**
	 * @param mixed $customer_email
	 */
	public function setCustomerEmail( $customer_email ) {
		$this->customer_email = $customer_email;
	}

	/**
	 * @return mixed
	 */
	public function getBillingAddress() {
		return $this->billing_address;
	}

	/**
	 * @param mixed $billing_address
	 */
	public function setBillingAddress( $billing_address ) {
		$this->billing_address = $billing_address;
	}

	/**
	 * @return mixed
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @param mixed $currency
	 */
	public function setCurrency( $currency ) {
		$this->currency = $currency;
	}

	/**
	 * @return mixed
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @param mixed $amount
	 */
	public function setAmount( $amount ) {
		$this->amount = $amount;
	}

	/**
	 * @return mixed
	 */
	public function getProductName() {
		return $this->product_name;
	}

	/**
	 * @param mixed $product_name
	 */
	public function setProductName( $product_name ) {
		$this->product_name = $product_name;
	}

	/**
	 * @return mixed
	 */
	public function getCustomInputValues() {
		return $this->custom_input_values;
	}

	/**
	 * @param mixed $custom_input_values
	 */
	public function setCustomInputValues( $custom_input_values ) {
		$this->custom_input_values = $custom_input_values;
	}

}

class MM_WPFS_SubscriptionTransactionData extends MM_WPFS_TransactionData {

	protected $plan_name;
	protected $plan_currency;
	protected $plan_amount;
	protected $plan_setup_fee;

	/**
	 * @return mixed
	 */
	public function getPlanName() {
		return $this->plan_name;
	}

	/**
	 * @param mixed $plan_name
	 */
	public function setPlanName( $plan_name ) {
		$this->plan_name = $plan_name;
	}

	/**
	 * @return mixed
	 */
	public function getPlanAmount() {
		return $this->plan_amount;
	}

	/**
	 * @param mixed $plan_amount
	 */
	public function setPlanAmount( $plan_amount ) {
		$this->plan_amount = $plan_amount;
	}

	public function getPlanAmountAndSetupFee() {
		return $this->plan_amount + $this->plan_setup_fee;
	}

	/**
	 * @return mixed
	 */
	public function getPlanCurrency() {
		return $this->plan_currency;
	}

	/**
	 * @param mixed $plan_currency
	 */
	public function setPlanCurrency( $plan_currency ) {
		$this->plan_currency = $plan_currency;
	}

	/**
	 * @return mixed
	 */
	public function getPlanSetupFee() {
		return $this->plan_setup_fee;
	}

	/**
	 * @param mixed $plan_setup_fee
	 */
	public function setPlanSetupFee( $plan_setup_fee ) {
		$this->plan_setup_fee = $plan_setup_fee;
	}

}