<?php
/**
 * Plugin Name:       Logo Carousel – Best Logo Showcase plugin for WordPress
 * Plugin URI:        https://wplimb.com/plugins/logo-carousel-pro/
 * Description:       A Powerful Logo Carousel plugin to display a list of clients, supporters, partners or sponsors logos in your WordPress website.
 * Version:           3.1
 * Author:            WPLimb
 * Author URI:        https://wplimb.com
 * Text Domain:       wpl-logo-carousel
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handles core plugin hooks and action setup.
 *
 * @package wpl-logol-carousel
 * @since 3.0
 */
if ( ! class_exists( 'WPLimb_WPLLC' ) ) {
	class WPLimb_WPLLC {
		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '3.1';

		/**
		 * @var WPLimb_WPLLC single instance of the class
		 *
		 * @since 3.0
		 */
		protected static $_instance = null;

		/**
		 * @var WPLLC_Logo $project
		 */
		public $logo;

		/**
		 * @var WPLLC_Router $router
		 */
		public $router;

		/**
		 * @var WPLLC_Router $shortcode
		 */
		public $shortcode;

		/**
		 * @var WPLLC_MetaBox $metabox
		 */
		public $metabox;

		/**
		 * Main WPLLC Instance
		 *
		 * @since 3.0
		 * @static
		 * @see wpl_lc()
		 * @return self Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor for the WPLimb_WPLLC class
		 */
		function __construct() {
			// Define constants
			$this->define_constants();

			//Required class file include
			spl_autoload_register( array( $this, 'autoload' ) );

			// Include required files
			$this->includes();

			// instantiate classes
			$this->instantiate();

			// Initialize the filter hooks
			$this->init_filters();

			// Initialize the action hooks
			$this->init_actions();
		}

		/**
		 * Flush rewrite rules
		 */
		function wpl_lc_flush_rewrites() {
			// call your CPT registration function here (it should also be hooked into 'init')
			$this->logo->register_post_type();
			flush_rewrite_rules();
		}

		/**
		 * Initialize WordPress filter hooks
		 *
		 * @return void
		 */
		function init_filters() {
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
			add_filter( "plugin_row_meta", array( $this, 'after_logo_carousel_row_meta'), 10, 4 );
			add_filter( 'manage_wpl_lcp_shortcodes_posts_columns', array( $this, 'add_shortcode_column' ) );
		}

		/**
		 * Initialize WordPress action hooks
		 *
		 * @return void
		 */
		function init_actions() {
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );
			//remove publish box
			add_action( 'admin_menu', array( $this, 'wpl_lc_remove_publish_box'));
			add_action('admin_print_scripts', array( $this, 'wpl_lcf_disable_autosave'));
			add_action( 'manage_wpl_lcp_shortcodes_posts_custom_column', array( $this, 'add_shortcode_form' ), 10, 2 );
		}

		/**
		 * Define wpl_lc constants
		 *
		 * @since 3.0
		 */
		public function define_constants() {
			$this->define( 'WPL_LC_VERSION', $this->version );
			$this->define( 'WPL_LC_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WPL_LC_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set
		 *
		 * @since 3.0
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Plugin Scripts and Styles
		 *
		 */
		function public_scripts() {
				// CSS Files
				wp_enqueue_style( 'slick', WPL_LC_URL . 'public/assets/css/slick.css', array(), WPL_LC_VERSION );
				wp_enqueue_style( 'font-awesome-min', WPL_LC_URL . 'public/assets/css/font-awesome.min.css', array(), WPL_LC_VERSION );
				wp_enqueue_style( 'lcf-style', WPL_LC_URL . 'public/assets/css/style.css', array(), WPL_LC_VERSION );

				//JS Files
				wp_enqueue_script( 'slick-min-js', WPL_LC_URL . 'public/assets/js/slick.min.js', array( 'jquery' ), WPL_LC_VERSION, false );
		}


		/**
		 * Load textdomain for plugin.
		 *
		 * @since 3.0
		 */
		public function load_plugin_textdomain() {
			load_textdomain( 'wpl-logo-carousel', WP_LANG_DIR . '/wpl-logo-carousel/wpl-logo-carousel-' . apply_filters( 'plugin_locale', get_locale(), 'wpl-logo-carousel' ) . '.mo' );
			load_plugin_textdomain( 'wpl-logo-carousel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add plugin action menu
		 *
		 * @since 3.0
		 *
		 * @param array $links
		 * @param string $file
		 *
		 * @return array
		 */
		function add_plugin_action_links( $links, $file ) {

			if ( $file == plugin_basename( __FILE__ ) ) {
				$new_links = array(
					sprintf( '<a href="%s" style="%s">%s</a>', 'https://wplimb.com/plugins/logo-carousel-pro/', 'color:red;font-weight:bold', __( 'Go Pro!', 'wpl-logo-carousel' ) ),
					sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=wpl_lcp_shortcodes' ), __( 'Shortcode Generator', 'wpl-logo-carousel' ) )
				);

				return array_merge( $new_links, $links );
			}

			return $links;
		}

		/**
		 * Add plugin row meta link
		 *
		 * @since 3.0
		 *
		 * @param $plugin_meta
		 * @param $file
		 *
		 * @return array
		 */

		function after_logo_carousel_row_meta( $plugin_meta, $file ){
			if ( $file == plugin_basename( __FILE__ ) ) {
				$plugin_meta[] = '<a href="https://wplimb.com/demo/logo-carousel-pro/" target="_blank">' . __( 'Live Demo', 'wpl-logo-carousel' ) . '</a>';
			}
			return $plugin_meta;
		}


		/**
		 * Autoload class files on demand
		 *
		 * @param string $class requested class name
		 */
		function autoload( $class ) {
			$name = explode( '_', $class );
			if ( isset( $name[1] ) ) {
				$class_name = strtolower( $name[1] );
				$filename   = WPL_LC_PATH . '/class/' . $class_name . '.php';

				if ( file_exists( $filename ) ) {
					require_once $filename;
				}
			}
		}

		/**
		 * Instantiate all the required classes
		 *
		 * @since 3.0
		 */
		function instantiate() {

			$this->logo      = WPLLC_Logo::getInstance();
			$this->shortcode = WPLLC_Shortcode::getInstance();
			$this->metabox   = WPLLC_MetaBox::getInstance();

			do_action( 'wpllc_instantiate', $this );
		}

		/**
		 * page router instantiate
		 *
		 * @since 3.0
		 */
		function page() {
			$this->router = WPLLC_Router::instance();

			return $this->router;
		}

		/**
		 * Include the required files
		 *
		 * @return void
		 */
		function includes() {
			//$this->version = WPL_LC_VERSION;
			$this->page()->wpllc_function();
			$this->router->includes();
		}

		/**
		 * Remove default auto save
		 * @since 3.1
		 */
		function wpl_lcf_disable_autosave() {

			global $post;
			if(isset($post->ID)&&get_post_type($post->ID) == 'wpl_lcp_shortcodes'){
				wp_dequeue_script('autosave');
			}
		}

		/**
		 * Remove default submit box
		 * @since 3.1
		 */
		public function wpl_lc_remove_publish_box(){
			remove_meta_box('submitdiv', 'wpl_lcp_shortcodes', 'side');
		}

		/**
		 * ShortCode Column
		 *
		 * @param $columns
		 *
		 * @return array
		 */
		function add_shortcode_column() {
			$new_columns['cb']        = '<input type="checkbox" />';
			$new_columns['title']     = __( 'Carousel Title', 'wpl-logo-carousel' );
			$new_columns['shortcode'] = __( 'Shortcode', 'wpl-logo-carousel' );
			$new_columns['']          = '';
			$new_columns['date']      = __( 'Date', 'wpl-logo-carousel' );

			return $new_columns;
		}

		/**
		 * @param $column
		 * @param $post_id
		 */
		function add_shortcode_form( $column, $post_id ) {

			switch ( $column ) {

				case 'shortcode':
					$column_field = '<input style="width: 270px;padding: 6px;" type="text" onClick="this.select();" readonly="readonly" value="[logocarousel ' . 'id=&quot;' . $post_id . '&quot;' . ']"/>';
					echo $column_field;
					break;
				default:
					break;

			} // end switch

		}

	}
}


/**
 * Returns the main instance.
 *
 * @since 3.0
 * @return WPLimb_WPLLC
 */
function wpl_lc() {
	return WPLimb_WPLLC::instance();
}

//wpl_lc instance.
$cpm = wpl_lc();