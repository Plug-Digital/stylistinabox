<?php

/**
 * This is to register the shortcode post type.
 * @package wpl-logo-carousel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WPLLC_Shortcode {

	private static $_instance;

	/**
	 * WPLLC_Shortcode constructor.
	 */
	public function __construct() {
		add_filter('init', array($this, 'register_post_type'));
	}

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * @return WPLLC_Shortcode
	 */
	public static function getInstance() {
		if ( ! self::$_instance ) {
			self::$_instance = new WPLLC_Shortcode();
		}

		return self::$_instance;
	}

	/**
	 * Shortcode Post Type
	 */
	function register_post_type() {
		register_post_type( 'wpl_lcp_shortcodes', array(
			'label'           => __( 'Generate Shortcode', 'wpl-logo-carousel' ),
			'description'     => __( 'Generate Shortcode', 'wpl-logo-carousel' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=wpl_logo_carousel',
			'hierarchical'    => false,
			'query_var'       => false,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'labels'          => array(
				'name'               => __( 'Logo Carousels', 'wpl-logo-carousel' ),
				'singular_name'      => __( 'Logo Carousel', 'wpl-logo-carousel' ),
				'menu_name'          => __( 'Shortcode Generator', 'wpl-logo-carousel' ),
				'add_new'            => __( 'Add New', 'wpl-logo-carousel' ),
				'add_new_item'       => __( 'Add New Carousel', 'wpl-logo-carousel' ),
				'edit'               => __( 'Edit', 'wpl-logo-carousel' ),
				'edit_item'          => __( 'Edit Carousel', 'wpl-logo-carousel' ),
				'new_item'           => __( 'New Carousel', 'wpl-logo-carousel' ),
				'view'               => __( 'View Shortcode', 'wpl-logo-carousel' ),
				'view_item'          => __( 'View Shortcode', 'wpl-logo-carousel' ),
				'search_items'       => __( 'Search Carousel', 'wpl-logo-carousel' ),
				'not_found'          => __( 'No Logo Carousel Found', 'wpl-logo-carousel' ),
				'not_found_in_trash' => __( 'No Logo Carousel Found in Trash', 'wpl-logo-carousel' ),
				'parent'             => __( 'Parent Logo Carousel', 'wpl-logo-carousel' ),
			)
		) );
	}


}