<?php

/**
 * Main class - SP Subscriptions
 */
class Stripe_Subscriptions_License {

	private static $instance = null;

	private $version;
	private $item_id;
	private $item_name;
	private $author;
	private $api_url;
	private $file;

	/**
	 * Class constructor.
	 */
	private function __construct() {

		$this->version   = SIMPAY_SUB_VERSION;
		$this->item_id   = 1275;
		$this->item_name = 'WP Simple Pay Pro for Stripe - Subscriptions Add-on';
		$this->author    = 'Moonstone Media';
		$this->api_url   = SIMPAY_EDD_STORE_URL;
		$this->file      = SC_SUB_PLUGIN_FILE;

		// Setup hooks.
		$this->hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {
		// Register EDDSL plugin auto-updater with top priority.
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );
	}

	/**
	 * Auto updater. Mimics auto updater function in base plugin licenses class.
	 *
	 * @access  public
	 * @return  void
	 */
	public function auto_updater() {

		/*
		 * EDD Auto Updater Notes:
		 * License must be active for plugin update to authorize.
		 * Even with correct item_id passed, EDD_BYPASS_ITEM_ID_CHECK must be set to true on server for plugin update to authorize.
		 * License key must be set to bundle license key from base plugin (use call to base plugin class).
		 * Pass in the item ID & item name even though they're not needed iwth EDD_BYPASS_ITEM_ID/NAME_CHECK's set to true.
		 */

		// Get bundle license key from base plugin licenses class.
		$sp_licenses        = Stripe_Checkout_Pro_Licenses::get_instance();
		$bundle_license_key = $sp_licenses->get_bundle_license_key();

		$edd_sl_updater = new EDD_SL_Plugin_Updater( $this->api_url, $this->file, array(
			'version'   => $this->version,
			'license'   => $bundle_license_key,
			'item_id'   => $this->item_id,
			'item_name' => $this->item_name,
			'author'    => $this->author,
		) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}
