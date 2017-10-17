<?php
/**
 * Plugin Name: WP Simple Pay Pro for Stripe - Subscriptions Add-on
 * Plugin URI:  https://wpsimplepay.com
 * Description: Subscriptions add-on for WP Simple Pay Pro for Stripe.
 * Author:      Moonstone Media
 * Author URI:  https://wpsimplepay.com
 * Version:     1.3.4
 * Text Domain: simpay_sub
 * Domain Path: /i18n
 *
 * @copyright   2014-2016 Moonstone Media/Phil Derksen. All rights reserved.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setup plugin constants.

// Plugin version
if ( ! defined( 'SIMPAY_SUB_VERSION' ) ) {
	define( 'SIMPAY_SUB_VERSION', '1.3.4' );
}

// Plugin folder path
// TODO SIMPAY_SUB_PLUGIN_DIR
if ( ! defined( 'SC_SUB_DIR_PATH' ) ) {
	define( 'SC_SUB_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL
// TODO SIMPAY_SUB_PLUGIN_URL
if ( ! defined( 'SC_SUB_DIR_URL' ) ) {
	define( 'SC_SUB_DIR_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin root file
// TODO SIMPAY_SUB_PLUGIN_FILE
if ( ! defined( 'SC_SUB_PLUGIN_FILE' ) ) {
	define( 'SC_SUB_PLUGIN_FILE', __FILE__ );
}

require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions.php' );

// Let's get going finally!
function simpay_sub_init() {

	// Do nothing if base plugin isn't active.
	if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
		return;
	}

	Stripe_Subscriptions::get_instance();
}

// Run after other plugins (and base plugin) are loaded.
add_action( 'plugins_loaded', 'simpay_sub_init' );
