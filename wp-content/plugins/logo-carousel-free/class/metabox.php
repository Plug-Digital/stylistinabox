<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * This files is to create meta boxes for shortcode and logos
 * @package wpl-logo-carousel
 */
class WPLLC_MetaBox {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 3.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 3.0
	 * @static
	 * @return self Main instance.
	 */
	public static function getInstance() {
		if ( ! self::$_instance ) {
			self::$_instance = new WPLLC_MetaBox();
		}

		return self::$_instance;
	}

	/**
	 * Register the class with the WordPress API
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->metaboxform = new WPLLC_MetaBoxForm();
		add_action( 'add_meta_boxes', array( $this, 'wpl_lc_add_meta_boxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_link_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'create_carousel_meta_box' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );


		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		//Update messages and help text
		add_action( 'post_updated_messages', array( &$this, 'wpl_lc_updated_messages' ) );

	}

	/**
	 * Enqueue all styles for the meta boxes
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'wpllc-admin-style', WPL_LC_URL . 'admin/assets/css/admin.css', false, WPL_LC_VERSION );
		wp_enqueue_style( 'wpl-google-font', 'https://fonts.googleapis.com/css?family=Ubuntu:400,400i,500,500i,700' );
		if ( 'wpl_lcp_shortcodes' === get_current_screen()->id || 'wpl_logo_carousel' === get_current_screen()->id ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'wpllc-metabox-style', WPL_LC_URL . 'admin/assets/css/wpl-metabox.css', false, WPL_LC_VERSION );
			wp_enqueue_style( 'fontawesome', WPL_LC_URL . 'public/assets/css/font-awesome.min.css', false, WPL_LC_VERSION );
		}
	}

	/**
	 * Includes the JavaScript necessary to control the toggling of the tabs in the
	 * meta box that's represented by this class.
	 *
	 * @since 3.0
	 */
	public function admin_scripts() {

		if ( 'wpl_lcp_shortcodes' === get_current_screen()->id ) {
			wp_enqueue_script( 'wpllc-shortcode-tabs', WPL_LC_URL . 'admin/assets/js/admin.js', array(
				'jquery',
				'wp-color-picker'
			), WPL_LC_VERSION, true );
		}
	}

	/**
	 * The function responsible for creating the actual generator meta box
	 *
	 * @since 3.0
	 */
	public function wpl_lc_add_meta_boxes() {
		// Shortcode Fields
		add_meta_box(
			'wpl_lcp_shortcode_options',
			__( 'Shortcode Options', 'wpl-logo-carousel' ),
			array( $this, 'wpl_shortcode_meta_boxes' ),
			'wpl_lcp_shortcodes',
			'normal',
			'default'
		);
	}

	/**
	 * Link meta box
	 */
	public function add_link_meta_box() {
		add_meta_box(
			'wpl_lcp_logo_link_option',
			__( 'Logo Link', 'wpl-logo-carousel' ),
			array( $this, 'logo_post_meta_box' ),
			'wpl_logo_carousel',
			'normal',
			'default'
		);
	}

	/**
	 * Create Carousel meta box
	 * @since 3.1
	 */
	public function create_carousel_meta_box() {
		add_meta_box(
			'wpl_lcf_create_carousel_metabox',
			__( 'Save', 'wpl-logo-carousel' ),
			array( $this, 'wpl_create_carousel_meta_box' ),
			'wpl_lcp_shortcodes',
			'side',
			'default'
		);
	}

	/**
	 * Save button
	 *
	 * @param $post
	 *
	 * @since 3.1
	 */
	public function wpl_create_carousel_meta_box( $post ) {
		$carousel_id = $post->ID;
		$post_status = get_post_status( $carousel_id );
		$delete_url  = get_delete_post_link( $carousel_id );
		$nonce       = wp_create_nonce( 'wpl_lcp_shortcode_nonce' );
		?>
		<div class="submitbox" id="submitcarousel">
			<div id="wpl-publishing-actions">
				<div class="wpl-pub-section"></div>
			</div>
			<div id="major-publishing-actions">
				<?php if ( $post_status == 'publish' ): ?>
					<div id="delete-action">
						<a class="submitdelete deletion" href="<?php echo $delete_url ?>">
							<?php echo '<i title="Move to trash" class="fa fa-trash"></i>'; ?>
						</a>
					</div>
				<?php endif; ?>
				<div id="publishing-action">
					<?php if ( $post_status != 'publish' ):
						submit_button( __( 'Create Carousel', 'wpl-logo-carousel' ), 'primary', 'publish', false );
					else:
						submit_button( __( 'Update Carousel', 'wpl-logo-carousel' ), 'primary', 'submit', false );
					endif; ?>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="post_type_is_wpl_lcp_shortcodes" value="yes"/>
		<input type="hidden" name="carousel_order" id="unique_carousel_order" value=""/>

		<?php
	}

	/**
	 * Carousel Custom Notification e.g update message
	 *
	 * @param $messages
	 *
	 * @return mixed
	 * @since 3.1
	 */
	function wpl_lc_updated_messages( $messages ) {

		global $post, $post_ID;
		$messages['wpl_lcp_shortcodes'] = array(
			0  => '',
			1  => sprintf( __( 'Carousel updated.' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.', 'wpl-logo-carousel' ),
			3  => __( 'Custom field deleted.', 'wpl-logo-carousel' ),
			4  => __( 'Carousel updated.', 'wpl-logo-carousel' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Carousel restored to revision from %s', 'wpl-logo-carousel' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Carousel saved.', 'wpl-logo-carousel' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Carousel saved.' ),
			8  => sprintf( __( 'Carousel submitted.' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf( __( 'Carousel scheduled for: <strong>%1$s</strong>. ', 'wpl-logo-carousel' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Carousel draft updated.', 'wpl-logo-carousel' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;

	}

	/**
	 * Renders the the content of the meta box
	 *
	 * @since 3.0
	 */
	public function logo_post_meta_box() {
		include_once WPL_LC_PATH . 'admin/views/partials/logo-meta.php';
	}

	/**
	 * Renders the the content of the meta box
	 *
	 * @since 3.0
	 */
	public function wpl_shortcode_meta_boxes() {

		wp_nonce_field( 'wpl_lcp_shortcodes_nonce_action', 'wpl_lcp_shortcodes_nonce_name' );

		include_once WPL_LC_PATH . 'admin/views/tab-navigation.php';
	}


	/**
	 * Save Generator Meta Box
	 *
	 * @param $post_id
	 */
	public function save_meta_box( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if nonce is set
		if ( ! isset( $_POST['wpl_lcp_shortcodes_nonce_name'], $_POST['wpl_lc_meta_box'] ) ) {
			return;
		}
		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $_POST['wpl_lcp_shortcodes_nonce_name'], 'wpl_lcp_shortcodes_nonce_action' ) ) {
			return;
		}


		// Check if user has permissions to save data
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $_POST['wpl_lc_meta_box'] as $key => $val ) {
			if ( is_array( $val ) ) {
				$val = implode( ',', $val );
			}

			update_post_meta( $post_id, $key, sanitize_text_field( $val ) );
		}

	}


}
