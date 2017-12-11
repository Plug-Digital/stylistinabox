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

<div id="wpllc-tab-3" class="wplmb-tab-content">
	<?php
	$this->metaboxform->checkbox( array(
		'id'      => 'lc_logo_border',
		'name'    => __( 'Logo Border', 'wpl-logo-carousel' ),
		'desc'    => __( 'Check to show logo border.', 'wpl-logo-carousel' ),
		'default' => 'off'
	) );

	$this->metaboxform->color( array(
		'id'      => 'lc_brand_color',
		'type'    => 'color',
		'name'    => __( 'Brand Color	', 'wpl-logo-carousel' ),
		'desc'    => __( 'Brand/Main color includes all hover & active color of the carousel.', 'wpl-logo-carousel' ),
		'default' => '#ff9933'
	) );

	?>
</div>