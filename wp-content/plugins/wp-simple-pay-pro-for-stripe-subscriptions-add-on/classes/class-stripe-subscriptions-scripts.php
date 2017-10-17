<?php

/**
 * Scripts class - SP Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscription_Scripts' ) ) {

	class Stripe_Subscriptions_Scripts {

		// class instance variable
		protected static $instance = null;

		private $min = '';
		private $plugin_slug = '';
		private $plugin_version = '';
		private $core_plugin_slug = '';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$this->plugin_slug = Stripe_Subscriptions::get_plugin_slug();
			$this->plugin_version = Stripe_Subscriptions::get_plugin_version();
			$this->core_plugin_slug = Stripe_Checkout_Pro::get_plugin_slug();

			// Front-end JS
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		}

		/**
		 * Enqueue Front-end Scripts
		 *
		 * @since 1.0.0
		 */
		public function enqueue_frontend_scripts() {

			$js_dir = SC_SUB_DIR_URL . 'assets/js/';

			// Localized PHP to JS global vars for front-end
			$localized_frontend_globals = apply_filters( 'simple_pay_subscriptions_global_script_vars', array(

				// Load i18n strings here
				'intervalEveryText'        => __( 'every', 'simpay_sub' ),
				'trialCheckoutButtonLabel' => __( 'Start Free Trial', 'simpay_sub' ),
			) );

			// Main public JS file with all dependencies.
			wp_register_script( $this->plugin_slug . '-public', $js_dir . 'sub-public' . $this->min . '.js',
				array(
					'jquery',
					$this->core_plugin_slug . '-public'
				),
				$this->plugin_version, true );

			wp_enqueue_script( $this->plugin_slug . '-public' );

			// Localize front-end global vars
			wp_localize_script( $this->plugin_slug . '-public', 'simplePaySubscriptionsFrontendGlobals', $localized_frontend_globals );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}
