<?php

/**
 * Main class - SP Subscriptions
 */

class Stripe_Subscriptions {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = null;

	/**
	 * Unique identifier
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'stripe-subscriptions';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->version = SIMPAY_SUB_VERSION;

		// Load plugin text domain.
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Include necessary files.
		add_action( 'init', array( $this, 'includes' ), 0 );

		// Init class instances.
		add_action( 'init', array( $this, 'init' ), 1 );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain( 'simpay_sub', false, SC_SUB_DIR_PATH . 'i18n/' );
	}

	/**
	 * Include necessary files.
	 *
	 * @since     1.0.0
	 */
	public function includes() {

		require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-scripts.php' );
		require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-functions.php' );
		require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-shortcodes.php' );

		// Admin side
		require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-license.php' );
	}

	/**
	 * Init class instances.
	 */
	public function init() {

		Stripe_Subscriptions_Scripts::get_instance();
		Stripe_Subscriptions_Functions::get_instance();
		Stripe_Subscriptions_Shortcodes::get_instance();

		if ( is_admin() ) {
			Stripe_Subscriptions_License::get_instance();
		}
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

	public static function get_plugin_slug() {
		return self::get_instance()->plugin_slug;
	}

	public static function get_plugin_version() {
		return self::get_instance()->version;
	}
}
