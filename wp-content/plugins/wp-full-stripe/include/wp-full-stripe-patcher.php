<?php

/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.03.01.
 * Time: 14:43
 */
class MM_WPFS_Patcher {

	public static function apply_patches() {

		error_log( 'WPFS INFO apply_patches(): Apply patches...' );

		$patches         = self::prepare_patches();
		$applied_patches = self::load_applied_patches();

		foreach ( $patches as $patch ) {
			/* @var $patch MM_WPFS_Patch */
			$apply = false;
			if ( array_key_exists( $patch->getId(), $applied_patches ) ) {
				if ( $patch->isRepeatable() ) {
					$apply = true;
				}
			} else {
				$apply = true;
			}
			if ( $apply ) {

				try {

					error_log( 'WPFS INFO apply_patches(): Applying ' . $patch->getId() . '...' );

					$result = $patch->apply();

					if ( $result ) {

						self::book_applied( $patch );

						error_log( 'WPFS INFO apply_patches(): ' . $patch->getId() . ' applied successfully.' );
					} else {
						error_log( 'WPFS ERROR apply_patches(): ' . $patch->getId() . ' failed!' );
					}

				} catch ( Exception $e ) {
					error_log( sprintf( 'WPFS ERROR apply_patches(): Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
				}

			}
		}

		error_log( 'WPFS INFO apply_patches(): Patches applied.' );

	}

	/**
	 * @return array
	 */
	private static function prepare_patches() {

		$convert_subscription_form_plans           = new MM_WPFS_ConvertSubscriptionFormPlansPatch();
		$convert_email_receipts                    = new MM_WPFS_ConvertEmailReceiptsPatch();
		$convert_subscription_status               = new MM_WPFS_ConvertSubscriptionStatus();
		$set_current_currency_for_payments         = new MM_WPFS_SetCurrentCurrencyForPayments();
		$set_list_of_amount_custom                 = new MM_WPFS_SetAllowListOfAmountsCustom();
		$set_show_detailed_success_page            = new MM_WPFS_SetShowDetailedSuccessPage();
		$set_custom_input_required                 = new MM_WPFS_SetCustomInputRequired();
		$migrate_currency_and_setup_fee            = new MM_WPFS_MigrateCurrencyAndSetupFee();
		$set_specified_amount_for_checkout_forms   = new MM_WPFS_SetSpecifiedAmountForCheckoutForms();
		$drop_checkout_subscription_alipay_columns = new MM_WPFS_DropCheckoutSubscriptionAlipayColumns();

		$patches = array(
			$convert_subscription_form_plans->getId()           => $convert_subscription_form_plans,
			$convert_email_receipts->getId()                    => $convert_email_receipts,
			$convert_subscription_status->getId()               => $convert_subscription_status,
			$set_current_currency_for_payments->getId()         => $set_current_currency_for_payments,
			$set_list_of_amount_custom->getId()                 => $set_list_of_amount_custom,
			$set_show_detailed_success_page->getId()            => $set_show_detailed_success_page,
			$set_custom_input_required->getId()                 => $set_custom_input_required,
			$migrate_currency_and_setup_fee->getId()            => $migrate_currency_and_setup_fee,
			$drop_checkout_subscription_alipay_columns->getId() => $drop_checkout_subscription_alipay_columns,
			$set_specified_amount_for_checkout_forms->getId()   => $set_specified_amount_for_checkout_forms
		);

		return $patches;
	}

	/**
	 * @return array
	 */
	private static function load_applied_patches() {
		global $wpdb;

		$result = $wpdb->get_results( "select id,patch_id,plugin_version,applied_at,description from {$wpdb->prefix}fullstripe_patch_info" );

		$applied_patches = array();

		foreach ( $result as $applied_patch ) {
			$applied_patches[ $applied_patch->patch_id ] = $applied_patch;
		}

		return $applied_patches;
	}

	private static function book_applied( $patch ) {

		if ( ! isset( $patch ) ) {
			return;
		}

		/* @var $patch MM_WPFS_Patch */

		global $wpdb;

		$data = array(
			'patch_id'       => $patch->getId(),
			'plugin_version' => $patch->getPluginVersion(),
			'applied_at'     => current_time( 'mysql', 1 ),
			'description'    => $patch->getDescription()
		);

		if ( $wpdb->insert( "{$wpdb->prefix}fullstripe_patch_info", $data ) === false ) {
			throw new Exception( 'Cannot insert patch_info: ' . $wpdb->last_error );
		}
	}

}

class MM_WPFS_SetSpecifiedAmountForCheckoutForms extends MM_WPFS_Patch {
	/**
	 * MM_WPFS_SetSpecifiedAmountForCheckoutForms constructor.
	 */
	public function __construct() {
		$this->id             = 'set_specified_amount_for_checkout_forms';
		$this->plugin_version = '3.11.0';
		$this->description    = 'A patch for setting the \'customAmount\' field for popup (checkout) forms to an initial value. JIRA reference: WPFS-412';
		$this->repeatable     = true;
	}


	/**
	 * @return boolean
	 */
	public function apply() {
		$this->update_checkout_forms_custom_amount();
	}

	private function update_checkout_forms_custom_amount() {
		global $wpdb;

		return $wpdb->update( "{$wpdb->prefix}fullstripe_checkout_forms", array( 'customAmount' => 'specified_amount' ), array( 'customAmount' => '' ) );
	}

}

class MM_WPFS_DropCheckoutSubscriptionAlipayColumns extends MM_WPFS_Patch {
	/**
	 * MM_WPFS_DropCheckoutSubscriptionAlipayColumns constructor.
	 */
	public function __construct() {
		$this->id             = 'drop_alipay_columns';
		$this->plugin_version = '3.10.0';
		$this->description    = 'A patch for dropping Alipay related columns from checkout subscription table. JIRA reference: WPFS-424';
		$this->repeatable     = false;
	}

	/**
	 * @return boolean
	 */
	public function apply() {
		global $wpdb;

		$use_alipay_column_exists = $wpdb->get_results( $wpdb->prepare( "select * from information_schema.columns where table_schema=%s and table_name=%s and column_name=%s", DB_NAME, "{$wpdb->prefix}fullstripe_checkout_subscription_forms", 'useAlipay' ) );
		if ( ! empty( $use_alipay_column_exists ) ) {
			$wpdb->query( "alter table {$wpdb->prefix}fullstripe_checkout_subscription_forms drop column useAlipay" );
		}
		$alipay_reusable_column_exists = $wpdb->get_results( $wpdb->prepare( "select * from information_schema.columns where table_schema=%s and table_name=%s and column_name=%s", DB_NAME, "{$wpdb->prefix}fullstripe_checkout_subscription_forms", 'alipayReusable' ) );
		if ( ! empty( $alipay_reusable_column_exists ) ) {
			$wpdb->query( "alter table {$wpdb->prefix}fullstripe_checkout_subscription_forms drop column alipayReusable" );
		}
	}
}

class MM_WPFS_MigrateCurrencyAndSetupFee extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_MigrateCurrency constructor.
	 */
	public function __construct() {
		$this->id             = 'migrate_currency';
		$this->plugin_version = '3.9.0';
		$this->description    = 'A patch for setting the currency field for forms and currency and setup fee for plans. JIRA reference: WPFS-356';
		$this->repeatable     = true;
	}

	public function apply() {
		$update_form_currencies_result = $this->update_currency_for_forms();
		if ( $update_form_currencies_result !== false ) {
			error_log( 'MM_WPFS_MigrateCurrency::apply(): ' . sprintf( 'Updated %d forms with current currency.', $update_form_currencies_result ) );
		} else {
			error_log( 'MM_WPFS_MigrateCurrency::apply(): Failed to update forms!' );
		}
		$update_plan_setup_fees_result = $this->update_setup_fees_for_plans();
		if ( $update_plan_setup_fees_result !== false ) {
			error_log( 'MM_WPFS_MigrateCurrency::apply(): ' . sprintf( 'Updated %d plans with setup fee.', $update_plan_setup_fees_result ) );
		} else {
			error_log( 'MM_WPFS_MigrateCurrency::apply(): Failed to update forms!' );
		}
		if ( $update_form_currencies_result !== false ) {
			$this->remove_currency_from_fullstripe_options();
			error_log( 'MM_WPFS_MigrateCurrency::apply(): Currency removed from options.' );
		} else {
			return false;
		}

		return true;
	}

	private function update_currency_for_forms() {
		$options = get_option( 'fullstripe_options' );
		if ( is_array( $options ) ) {
			if ( array_key_exists( 'currency', $options ) ) {
				$currency = $options['currency'];
				global $wpdb;
				$payment_form_update_result  = $wpdb->update( "{$wpdb->prefix}fullstripe_payment_forms", array( 'currency' => $currency ), array( 'currency' => '' ) );
				$checkout_form_update_result = $wpdb->update( "{$wpdb->prefix}fullstripe_checkout_forms", array( 'currency' => $currency ), array( 'currency' => '' ) );
				if ( $payment_form_update_result === false || $checkout_form_update_result === false ) {
					return false;
				} else {
					return $payment_form_update_result + $checkout_form_update_result;
				}
			}
		}

		return 0;
	}

	private function update_setup_fees_for_plans() {
		$subscription_forms = $this->load_subscription_forms();

		$updated_plan_count = 0;

		if ( isset( $subscription_forms ) ) {
			foreach ( $subscription_forms as $form ) {
				if ( $form->setupFee != - 1 ) {
					$subscription_form_plans = json_decode( $form->plans );
					foreach ( $subscription_form_plans as $plan_id ) {
						$plan = \Stripe\Plan::retrieve( $plan_id );
						if ( isset( $plan ) ) {
							if ( isset( $plan->metadata ) && ! isset( $plan->metadata->setup_fee ) ) {
								$plan->metadata->setup_fee = $form->setupFee;
								$plan->save();
								$updated_plan_count += 1;
							}
						}
					}
					$this->update_subscription_form_setup_fee( $form->subscriptionFormID, - 1 /* setupFee */ );
				}
			}
		}

		return $updated_plan_count;
	}

	private function load_subscription_forms() {
		global $wpdb;

		return $wpdb->get_results( "select * from {$wpdb->prefix}fullstripe_subscription_forms" );
	}

	private function update_subscription_form_setup_fee( $id, $setupFee ) {
		global $wpdb;

		return $wpdb->update( "{$wpdb->prefix}fullstripe_subscription_forms", array( 'setupFee' => $setupFee ), array( 'subscriptionFormID' => $id ) );
	}

	private function remove_currency_from_fullstripe_options() {
		$options = get_option( 'fullstripe_options' );
		if ( is_array( $options ) ) {
			if ( array_key_exists( 'currency', $options ) ) {
				unset( $options['currency'] );
				update_option( 'fullstripe_options', $options );
			}
		}
	}

}

class MM_WPFS_SetCustomInputRequired extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_SetCustomInputRequired constructor.
	 */
	public function __construct() {
		$this->id             = 'set_custom_input_required';
		$this->plugin_version = '3.8.0';
		$this->description    = 'A patch for setting the customInputRequired field for forms. JIRA reference: WPFS-318';
		$this->repeatable     = true;
	}

	public function apply() {
		$this->update_custom_input_required_for_forms();

		return true;
	}

	private function update_custom_input_required_for_forms() {
		global $wpdb;

		$payment_update_result      = $wpdb->update( "{$wpdb->prefix}fullstripe_payment_forms", array( 'customInputRequired' => '0' ), array( 'customInputRequired' => '' ) );
		$subscription_update_result = $wpdb->update( "{$wpdb->prefix}fullstripe_subscription_forms", array( 'customInputRequired' => '0' ), array( 'customInputRequired' => '' ) );

		if ( $payment_update_result === false || $subscription_update_result === false ) {
			return false;
		} else {
			return $payment_update_result + $subscription_update_result;
		}
	}

}

class MM_WPFS_SetShowDetailedSuccessPage extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_SetShowDetailedSuccessPage constructor.
	 */
	public function __construct() {
		$this->id             = 'set_show_detailed_success_page';
		$this->plugin_version = '3.8.0';
		$this->description    = 'A patch for setting the showDetailedSuccessPage field for forms. JIRA reference: WPFS-313';
		$this->repeatable     = true;
	}

	public function apply() {
		$this->update_show_detailed_success_page_for_forms();

		return true;
	}

	private function update_show_detailed_success_page_for_forms() {
		global $wpdb;

		$payment_update_result      = $wpdb->update( "{$wpdb->prefix}fullstripe_payment_forms", array( 'showDetailedSuccessPage' => '0' ), array( 'showDetailedSuccessPage' => '' ) );
		$checkout_update_result     = $wpdb->update( "{$wpdb->prefix}fullstripe_checkout_forms", array( 'showDetailedSuccessPage' => '0' ), array( 'showDetailedSuccessPage' => '' ) );
		$subscription_update_result = $wpdb->update( "{$wpdb->prefix}fullstripe_subscription_forms", array( 'showDetailedSuccessPage' => '0' ), array( 'showDetailedSuccessPage' => '' ) );

		if ( $payment_update_result === false || $checkout_update_result === false || $subscription_update_result === false ) {
			// tnagy an error occurred
			return false;
		} else {
			return $payment_update_result + $checkout_update_result + $subscription_update_result;
		}
	}

}

class MM_WPFS_SetAllowListOfAmountsCustom extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_SetAllowListOfAmountsCustom constructor.
	 */
	public function __construct() {
		$this->id             = 'set_allow_list_of_amounts_custom';
		$this->plugin_version = '3.8.0';
		$this->description    = 'A patch for setting the allowListOfAmountsCustom field for payment forms. JIRA reference: WPFS-307';
		$this->repeatable     = true;
	}

	public function apply() {
		$this->update_allow_list_of_amounts_custom_for_payment_forms();

		return true;
	}

	private function update_allow_list_of_amounts_custom_for_payment_forms() {
		global $wpdb;

		return $wpdb->update( "{$wpdb->prefix}fullstripe_payment_forms", array( 'allowListOfAmountsCustom' => '0' ), array( 'allowListOfAmountsCustom' => '' ) );
	}

}

class MM_WPFS_SetCurrentCurrencyForPayments extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_SetCurrentCurrencyForPayments constructor.
	 */
	public function __construct() {
		$this->id             = 'set_current_currency_for_payments';
		$this->plugin_version = '3.7.0';
		$this->description    = 'A patch for setting the current currency for payments made before 3.7.0 without saved currency. JIRA reference: WPFS-240';
		$this->repeatable     = true;
	}

	public function apply() {
		$this->update_currency_for_payments();

		return true;
	}

	private function update_currency_for_payments() {
		$options = get_option( 'fullstripe_options' );
		if ( is_array( $options ) ) {
			if ( array_key_exists( 'currency', $options ) ) {
				$currency = $options['currency'];

				global $wpdb;

				return $wpdb->update( "{$wpdb->prefix}fullstripe_payments", array( 'currency' => $currency ), array( 'currency' => '' ) );
			}
		}

		return false;
	}

}

class MM_WPFS_ConvertSubscriptionStatus extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_ConvertSubscriptionStatus constructor.
	 */
	public function __construct() {
		$this->id             = 'convert_subscription_status';
		$this->plugin_version = '3.6.0';
		$this->description    = 'A patch for converting subscription status fields from version before 3.6.0. JIRA reference: WPFS-194';
		$this->repeatable     = true;
	}

	public function apply() {

		$this->update_subscription_status();

		return true;
	}

	private function update_subscription_status() {
		global $wpdb;

		return $wpdb->update( "{$wpdb->prefix}fullstripe_subscribers", array( 'status' => 'running' ), array( 'status' => '' ) );
	}

}

class MM_WPFS_ConvertEmailReceiptsPatch extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_ConvertEmailReceiptsPatch constructor.
	 */
	public function __construct() {
		$this->id             = 'convert_email_receipts';
		$this->plugin_version = '3.6.0';
		$this->description    = 'A patch for converting email receipts to JSON format. JIRA reference: WPFS-170';
		$this->repeatable     = true;
	}

	public function apply() {
		$options = get_option( 'fullstripe_options' );
		if ( is_array( $options ) ) {
			if (
				array_key_exists( 'email_receipt_subject', $options )
				&& array_key_exists( 'email_receipt_html', $options )
				&& array_key_exists( 'subscription_email_receipt_subject', $options )
				&& array_key_exists( 'subscription_email_receipt_html', $options )
			) {
				$emailReceipts                         = array();
				$paymentMade                           = new stdClass();
				$subscriptionStarted                   = new stdClass();
				$subscriptionFinished                  = new stdClass();
				$paymentMade->subject                  = $options['email_receipt_subject'];
				$paymentMade->html                     = html_entity_decode( $options['email_receipt_html'] );
				$subscriptionStarted->subject          = $options['subscription_email_receipt_subject'];
				$subscriptionStarted->html             = html_entity_decode( $options['subscription_email_receipt_html'] );
				$subscriptionFinished->subject         = 'Subscription ended';
				$subscriptionFinished->html            = '<html><body><p>Hi,</p><p>Your %PLAN_NAME% subscription has come to an end.</p><p>Thanks</p><br/>%NAME%</body></html>';
				$emailReceipts['paymentMade']          = $paymentMade;
				$emailReceipts['subscriptionStarted']  = $subscriptionStarted;
				$emailReceipts['subscriptionFinished'] = $subscriptionFinished;

				$options['email_receipts'] = json_encode( $emailReceipts );
				unset( $options['email_receipt_subject'] );
				unset( $options['email_receipt_html'] );
				unset( $options['subscription_email_receipt_subject'] );
				unset( $options['subscription_email_receipt_html'] );

				update_option( 'fullstripe_options', $options );
			}
		}

		return true;
	}

}

class MM_WPFS_ConvertSubscriptionFormPlansPatch extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_ConvertSubscriptionFormPlansPatch constructor.
	 */
	public function __construct() {
		$this->id             = 'convert_subscription_form_plans';
		$this->plugin_version = '3.6.0';
		$this->description    = 'A patch for converting subscription forms\' plans column to JSON format. JIRA reference: WPFS-15';
		$this->repeatable     = true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function apply() {
		$subscription_forms = $this->load_subscription_forms();

		if ( isset( $subscription_forms ) ) {
			foreach ( $subscription_forms as $form ) {
				json_decode( $form->plans );
				if ( json_last_error() != JSON_ERROR_NONE ) {
					$this->update_subscription_form_plans( $form->subscriptionFormID, json_encode( explode( ',', $form->plans ) ) );
				}
			}
		}

		return true;
	}

	private function load_subscription_forms() {
		global $wpdb;

		return $wpdb->get_results( "select * from {$wpdb->prefix}fullstripe_subscription_forms" );
	}

	private function update_subscription_form_plans( $id, $plans ) {
		global $wpdb;

		return $wpdb->update( "{$wpdb->prefix}fullstripe_subscription_forms", array( 'plans' => $plans ), array( 'subscriptionFormID' => $id ) );
	}
}

class MM_WPFS_DummyPatch extends MM_WPFS_Patch {

	/**
	 * MM_WPFS_Dummy constructor.
	 */
	public function __construct() {
		$this->id             = 'dummy';
		$this->plugin_version = '3.6.0';
		$this->description    = 'A dummy patch for testing purposes.';
		$this->repeatable     = false;
	}

	public function apply() {
		error_log( 'WPFS DEBUG apply(): ' . 'Starting DummyPatch...' );
		error_log( 'WPFS DEBUG apply(): ' . 'DummyPatch finished.' );

		return true;
	}
}

abstract class MM_WPFS_Patch {

	/* @var $id string */
	protected $id;
	/* @var $plugin_version string */
	protected $plugin_version;
	/* @var $description string */
	protected $description;
	/* @var $repeatable boolean */
	protected $repeatable = false;

	/**
	 * @return boolean
	 */
	public abstract function apply();

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getPluginVersion() {
		return $this->plugin_version;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return boolean
	 */
	public function isRepeatable() {
		return $this->repeatable;
	}

	/**
	 *
	 * @param $result
	 *
	 * @param $message
	 *
	 * @throws Exception
	 */
	protected function handleDbError( $result, $message ) {
		if ( $result === false ) {
			global $wpdb;
			error_log( sprintf( "%s: Raised exception with message=%s", 'WPFS ERROR', $message ) );
			error_log( sprintf( "%s: SQL last error=%s", 'WPFS ERROR', $wpdb->last_error ) );
			throw new Exception( $message );
		}
	}

}