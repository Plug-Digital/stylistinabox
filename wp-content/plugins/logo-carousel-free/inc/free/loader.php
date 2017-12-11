<?php

/**
 * The Free Loader Class
 * @package wpl-logo-carousel
 * @since 3.0
 */
class WPLLC_Free_Loader {

	/*
	 * Free Loader constructor
	 */

	function __construct() {
		add_action( 'admin_menu', array( $this, 'lc_admin_menu' ), 5 );
		require_once( WPL_LC_PATH . 'public/views/shortcoderender.php' );
		require_once( WPL_LC_PATH . 'public/views/shortcode-deprecated.php' );
		require_once( WPL_LC_PATH . 'admin/wpl-mce-button/button.php' );
	}

	/**
	 * Admin Menus
	 */
	function lc_admin_menu() {
		$capability = 'read'; //minimum level: subscriber

		add_submenu_page( 'edit.php?post_type=wpl_logo_carousel', __( 'Logo Categories', 'wpl-logo-carousel' ), __( 'Logo Categories', 'wpl-logo-carousel' ), $capability, 'lc_category', array(
			$this,
			'show_the_admin_page_handler'
		) );
	}

	/**
	 * The upgrade to pro notice handler
	 * @return string
	 *
	 * @since 3.0
	 */
	public function admin_page_handler() {
		$the_pro_notice = '<div class="wrap">
			<div class="postbox wpllc-pro-notice">
				<div class="wpllc-text">' . __( 'This feature is only available in the Pro version', 'wpl-logo-carousel' ) . '</div>
				<a target="_blank" href="https://wplimb.com/plugins/logo-carousel-pro/"
				   class="button button-primary">' . __( 'Upgrade to Pro version', 'wpl-logo-carousel' ) . '</a>
			</div>
		</div>';

		return $the_pro_notice;
	}

	public function show_the_admin_page_handler(){
		echo $this->admin_page_handler();
	}

}

new WPLLC_Free_Loader();