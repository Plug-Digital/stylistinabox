<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handles displays logo custom post.
 *
 * @package wpl-logo-carousel
 * @since 3.0
 */
class WPLLC_Logo {

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
			self::$_instance = new WPLLC_Logo();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Registers the custom post type
	 */
	public function register_post_type() {
		if ( post_type_exists( "wpl_logo_carousel" ) ) {
			return;
		}

		$args_post_type = array(
			'label'               => __( 'Logo', 'wpl-logo-carousel' ),
			'description'         => __( 'logo carousel post type', 'wpl-logo-carousel' ),
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => WPL_LC_URL . 'admin/assets/images/icon.png',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => array( 'slug' => '' ),
			'query_var'           => true,
			'supports'            => array(
				'title',
				'thumbnail'
			),
			'labels'              => array(
				'name'                  => __( 'Logos', 'wpl-logo-carousel' ),
				'singular_name'         => __( 'Logo', 'wpl-logo-carousel' ),
				'menu_name'             => __( 'Logo Carousel', 'wpl-logo-carousel' ),
				'add_new'               => __( 'Add New Logo', 'wpl-logo-carousel' ),
				'add_new_item'          => __( 'Add New Logo', 'wpl-logo-carousel' ),
				'edit'                  => __( 'Edit', 'wpl-logo-carousel' ),
				'edit_item'             => __( 'Edit Logo', 'wpl-logo-carousel' ),
				'new_item'              => __( 'New Logo', 'wpl-logo-carousel' ),
				'view'                  => __( 'View Logo', 'wpl-logo-carousel' ),
				'view_item'             => __( 'View Logo', 'wpl-logo-carousel' ),
				'all_items'             => __( 'All Logos', 'wpl-logo-carousel' ),
				'search_items'          => __( 'Search Logo', 'wpl-logo-carousel' ),
				'not_found'             => __( 'No Logo Found', 'wpl-logo-carousel' ),
				'not_found_in_trash'    => __( 'No Logo Found in Trash', 'wpl-logo-carousel' ),
				'parent'                => __( 'Parent Logo', 'wpl-logo-carousel' ),
				'featured_image'        => __( 'Logo Image', 'wpl-logo-carousel' ),
				'set_featured_image'    => __( 'Set logo image', 'wpl-logo-carousel' ),
				'remove_featured_image' => __( 'Remove logo image', 'wpl-logo-carousel' ),
				'use_featured_image'    => __( 'Use as logo image', 'wpl-logo-carousel' ),
			),
		);

		$args_post_type = apply_filters( 'wpl_lc_register_logo_post_type', $args_post_type );

		register_post_type( 'wpl_logo_carousel', $args_post_type );
	}

}