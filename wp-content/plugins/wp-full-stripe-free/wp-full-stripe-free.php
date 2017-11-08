<?php
/*
Plugin Name: WP Full Stripe Free
Plugin URI: https://paymentsplugin.com
Description: Free version of WP Full Stripe, a Stripe payments plugin.
Author: Mammothology
Version: 1.6.0
Author URI: https://paymentsplugin.com
Text Domain: wp-full-stripe-free
Domain Path: /languages
*/

//defines

define( 'STRIPE_API_VERSION', '4.4.0' );

if ( ! defined( 'WP_FULL_STRIPE_NAME' ) ) {
	define( 'WP_FULL_STRIPE_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}

if ( ! defined( 'WP_FULL_STRIPE_BASENAME' ) ) {
	define( 'WP_FULL_STRIPE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WP_FULL_STRIPE_DIR' ) ) {
	define( 'WP_FULL_STRIPE_DIR', WP_PLUGIN_DIR . '/' . WP_FULL_STRIPE_NAME );
}


//Stripe PHP library
if ( ! class_exists( '\Stripe\Stripe' ) ) {
	require_once( dirname( __FILE__ ) . '/vendor/stripe/stripe-php/init.php' );
} else {
	if ( substr( \Stripe\Stripe::VERSION, 0, strpos( \Stripe\Stripe::VERSION, '.' ) ) != substr( STRIPE_API_VERSION, 0, strpos( STRIPE_API_VERSION, '.' ) ) ) {
		wp_die( plugin_basename( __FILE__ ) . ': ' . __( 'Another plugin has loaded an incompatible Stripe API client. Deactivate all other Stripe plugins, and try to activate Full Stripe Free again.' ) );
	}
}

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'wp-full-stripe-main.php';
register_activation_hook( __FILE__, array( 'MM_WPFSF', 'setup_db' ) );

function wp_full_stripe_free_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-full-stripe-free', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'wp_full_stripe_free_load_plugin_textdomain' );
