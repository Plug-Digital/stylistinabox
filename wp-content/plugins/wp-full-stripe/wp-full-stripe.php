<?php
/*
Plugin Name: WP Full Stripe
Plugin URI: https://paymentsplugin.com
Description: Complete Stripe payments integration for Wordpress
Author: Mammothology
Version: 3.11.1
Author URI: https://paymentsplugin.com
Text Domain: wp-full-stripe
Domain Path: /languages
*/

//defines

// define( 'WP_FULL_STRIPE_DEMO_MODE', true );

define( 'WP_FULL_STRIPE_MIN_PHP_VERSION', '5.3.0' );
define( 'WP_FULL_STRIPE_MIN_WP_VERSION', '4.0.0' );
define( 'WP_FULL_STRIPE_STRIPE_API_VERSION', '5.1.1' );

if ( ! defined( 'WP_FULL_STRIPE_NAME' ) ) {
	define( 'WP_FULL_STRIPE_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}

if ( ! defined( 'WP_FULL_STRIPE_BASENAME' ) ) {
	define( 'WP_FULL_STRIPE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WP_FULL_STRIPE_DIR' ) ) {
	define( 'WP_FULL_STRIPE_DIR', plugin_dir_path( __FILE__ ) );
}

// tnagy check minimum requirements
if ( version_compare( PHP_VERSION, WP_FULL_STRIPE_MIN_PHP_VERSION ) == - 1 ) {
	wp_die( plugin_basename( __FILE__ ) . ': ' . sprintf( __( "The minimum PHP version for running WP Full Stripe is %s but %s is found.<br/><br/>Please press the 'Back' browser button, upgrade PHP, and activate the plugin again.", 'wp-full-stripe' ), WP_FULL_STRIPE_MIN_PHP_VERSION, PHP_VERSION ) );
}
if ( version_compare( get_bloginfo( 'version' ), WP_FULL_STRIPE_MIN_WP_VERSION ) == - 1 ) {
	wp_die( plugin_basename( __FILE__ ) . ': ' . sprintf( __( "The minimum WordPress version for running WP Full Stripe is %s but %s is found.<br/><br/>Please press the 'Back' browser button, upgrade Wordpress, and activate the plugin again.", 'wp-full-stripe' ), WP_FULL_STRIPE_MIN_WP_VERSION, get_bloginfo( 'version' ) ) );
}
if ( extension_loaded( 'curl' ) === false ) {
	wp_die( plugin_basename( __FILE__ ) . ': ' . sprintf( __( "WP Full Stripe cannot find a required PHP extension called '%s'.<br/><br/>Please press the 'Back' browser button, install/enable '%s' for PHP, and activate the plugin again.", 'wp-full-stripe' ), 'cURL', 'cURL' ) );
}
if ( extension_loaded( 'mbstring' ) === false ) {
	wp_die( plugin_basename( __FILE__ ) . ': ' . sprintf( __( "WP Full Stripe cannot find a required PHP extension called '%s'.<br/><br/>Please press the 'Back' browser button, install/enable '%s' for PHP, and activate the plugin again.", 'wp-full-stripe' ), 'MBString', 'MBString' ) );
}

//Stripe PHP library
if ( ! class_exists( '\Stripe\Stripe' ) ) {
	require_once( dirname( __FILE__ ) . '/vendor/stripe/stripe-php/init.php' );
} else {
	if ( substr( \Stripe\Stripe::VERSION, 0, strpos( \Stripe\Stripe::VERSION, '.' ) ) != substr( WP_FULL_STRIPE_STRIPE_API_VERSION, 0, strpos( WP_FULL_STRIPE_STRIPE_API_VERSION, '.' ) ) ) {
		$reflector = new ReflectionClass( '\Stripe\Stripe' );
		wp_die( plugin_basename( __FILE__ ) . ': ' . __( 'Another plugin has loaded an incompatible Stripe API client. Deactivate all other Stripe plugins, and try to activate Full Stripe again.', 'wp-full-stripe' ) . ' ' . \Stripe\Stripe::VERSION . ' != ' . STRIPE_API_VERSION . ', ' . $reflector->getFileName() );
	}
}

if ( ! class_exists( 'MM_WPFS_License' ) ) {
	// load our custom updater if it doesn't already exist
	include( dirname( __FILE__ ) . '/include/wp-full-stripe-edd-license.php' );
}

if ( ! class_exists( 'WPFS_EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist
	include( dirname( __FILE__ ) . '/include/edd/WPFS_EDD_SL_Plugin_Updater.php' );
}

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'wp-full-stripe-main.php';
register_activation_hook( __FILE__, array( 'MM_WPFS', 'setup_db' ) );

\Stripe\Stripe::setAppInfo( 'WP Full Stripe', MM_WPFS::VERSION, 'https://paymentsplugin.com' );

$options     = get_option( 'fullstripe_options' );
$license_key = trim( $options['edd_license_key'] );
$edd_updater = new WPFS_EDD_SL_Plugin_Updater( WPFS_EDD_SL_STORE_URL, __FILE__, array(
	'version'   => MM_WPFS::VERSION,
	'license'   => $license_key,
	'item_name' => WPFS_EDD_SL_ITEM_NAME,
	'author'    => 'Mammothology',
	'url'       => home_url()
) );

function wp_full_stripe_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-full-stripe', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'wp_full_stripe_load_plugin_textdomain' );