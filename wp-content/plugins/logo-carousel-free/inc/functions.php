<?php

/**
 * This file contains all the helper functions for Logo Carousel
 *
 * @since 3.0
 * @Package wpl-log-carousel
 */

/**
 * Check if this is a pro version
 *
 * @return boolean
 */
function wpllc_is_pro() {

	if ( file_exists( WPL_LC_PATH . '/inc/pro/loader.php' ) ) {
		return true;
	}

	return false;
}

/**
 * Change the post type
 */

global $wpdb;
$old_post_types = array( 'logo-carousel-free' => 'wpl_logo_carousel' );
foreach ( $old_post_types as $old_type => $type ) {
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_type = REPLACE(post_type, %s, %s) 
                         WHERE post_type LIKE %s", $old_type, $type, $old_type ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid = REPLACE(guid, %s, %s) 
                         WHERE guid LIKE %s", "post_type={$old_type}", "post_type={$type}", "%post_type={$type}%" ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid = REPLACE(guid, %s, %s) 
                         WHERE guid LIKE %s", "/{$old_type}/", "/{$type}/", "%/{$old_type}/%" ) );
}

/**
 * Logo and URL columns on admin panel
 *
 * @since 3.0.1
 * @param $columns
 *
 * @return array
 */
function wpl_logo_carousel_add_columns( $columns ) {
	$columns = array(
		"cb"       => "cb",
		"title"    => __( "Title", "wpl-logo-carousel" ),
		"thumb"    => __( "Logo", "wpl-logo-carousel" ),
		"taxonomy" => __( "Categories", "wpl-logo-carousel" ),
		"url"      => __( "URL", "wpl-logo-carousel" ),
		"date"     => __( "Date", "wpl-logo-carousel" ),
	);

	return $columns;
}

add_action( 'manage_wpl_logo_carousel_posts_columns', 'wpl_logo_carousel_add_columns' );

function wpl_logo_carousel_logo_thumb( $column ) {
	if ( $column == "thumb" ) {
		the_post_thumbnail( 'thumb' );
	}
}

add_action( 'manage_wpl_logo_carousel_posts_custom_column', 'wpl_logo_carousel_logo_thumb', 10, 2 );

/**
 * Review Text
 *
 * @param $text
 *
 * @return string
 */
function wpl_logo_carousle_admin_footer( $text ) {
	$screen = get_current_screen();
	if ( 'wpl_lcp_shortcodes' == get_post_type() || 'wpl_logo_carousel' == get_post_type() || $screen->id=='wpl_logo_carousel_page_lc_category') {
		$url = 'https://wordpress.org/support/plugin/logo-carousel-free/reviews/?filter=5#new-post';
		$text = sprintf( __( 'If you like <strong>Logo Carousel</strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Your Review is very important to us as it helps us to grow more. ', 'wpl-logo-carousel' ), $url );
	}

	return $text;
}
add_filter( 'admin_footer_text', 'wpl_logo_carousle_admin_footer', 1, 2 );

/**
 * Do Shortcode used as a function
 * @since 3.1
 * @param $id
 */
function logocarousel($id) {
	echo do_shortcode( '[logocarousel id="' . $id . '"]' );
}

/**
 * Widget area support
 * @since 3.0.1
 */
add_filter('widget_text', 'do_shortcode');