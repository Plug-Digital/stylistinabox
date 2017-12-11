<?php
/**
 * Provides the 'Resources' view for the corresponding tab in the Shortcode Meta Box.
 *
 * @since 3.0
 *
 * @package    wpl-logo-carousel
 * @subpackage wpl-logo-carousel/admin/views/partials
 */
?>

<div id="wpllc-tab-2" class="wplmb-tab-content">
	<?php
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_auto_play',
		'name'  => __( 'AutoPlay', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to on autoplay carousel.', 'wpl-logo-carousel' ),
		'default'   => true,
	) );
	$this->metaboxform->number( array(
		'id'   => 'lc_auto_play_speed',
		'name' => __( 'AutoPlay Speed', 'wpl-logo-carousel' ),
		'desc' => __( 'Set autoplay speed.', 'wpl-logo-carousel' ),
		'after' => __( '(Millisecond)', 'wpl-logo-carousel' ),
		'default'  => 3000
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_pause_on_hover',
		'name'  => __( 'Pause on Hover', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to activate pause on hover.', 'wpl-logo-carousel' ),
		'default'   => 'on'
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_show_navigation',
		'name'  => __( 'Navigation', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to show navigation arrows.', 'wpl-logo-carousel' ),
		'default'   => 'on'
	) );
	$this->metaboxform->color( array(
		'id'      => 'lc_nav_arrow_color',
		'type'    => 'color',
		'name'    => __( 'Navigation Color	', 'wpl-logo-carousel' ),
		'desc'    => __( 'Pick a color for navigation arrows.', 'wpl-logo-carousel' ),
		'default' => '#fff'
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_show_pagination_dots',
		'name'  => __( 'Pagination Dots', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to show pagination dots.', 'wpl-logo-carousel' ),
		'default'   => 'on'
	) );
	$this->metaboxform->color( array(
		'id'      => 'lc_pagination_color',
		'type'    => 'color',
		'name'    => __( 'Pagination Color	', 'wpl-logo-carousel' ),
		'desc'    => __( 'Pick a color for pagination dots.', 'wpl-logo-carousel' ),
		'default' => '#ddd'
	) );
	$this->metaboxform->number( array(
		'id'   => 'lc_scroll_speed',
		'name' => __( 'Pagination Speed.', 'wpl-logo-carousel' ),
		'desc' => __( 'Set pagination/slide scroll speed.', 'wpl-logo-carousel' ),
		'after' => __( '(Millisecond).', 'wpl-logo-carousel' ),
		'default'  => 450
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_touch_swipe',
		'name'  => __( 'Touch Swipe', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to on touch swipe.', 'wpl-logo-carousel' ),
		'default'   => 'on'
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_mouse_draggable',
		'name'  => __( 'Mouse Draggable', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check to on mouse draggable.', 'wpl-logo-carousel' ),
		'default'   => 'on'
	) );
	$this->metaboxform->checkbox( array(
		'id'    => 'lc_logo_rtl',
		'name'  => __( 'RTL Mode', 'wpl-logo-carousel' ),
		'desc'  => __( 'Check and Set a RTL language from admin settings to make the rtl option work.', 'wpl-logo-carousel' ),
		'default'   => 'off'
	) );
	?>
</div>