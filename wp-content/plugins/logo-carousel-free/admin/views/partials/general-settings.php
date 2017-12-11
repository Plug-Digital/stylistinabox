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

<div id="wpllc-tab-1" class="wplmb-tab-content nav-tab-active">

	<?php
	$this->metaboxform->select_layout( array(
		'id'   => 'lc_logo_layout',
		'name' => __( 'Layout', 'wpl-logo-carousel' ),
		'desc' => __( 'Select a layout to show the logos.', 'wpl-logo-carousel' ),
		'default'  => 'carousel'
	) );
	$this->metaboxform->number( array(
		'id'   => 'lc_number_of_total_logos',
		'name' => __( 'Total Logos', 'wpl-logo-carousel' ),
		'desc' => __( 'Number of Total logos to show. Default value is unlimited.', 'wpl-logo-carousel' ),
		'default'  => -1
	) );
	$this->metaboxform->number( array(
		'id'    => 'lc_number_of_column',
		'name'  => __( 'Logo Columns.', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logo column number in large desktop view.', 'wpl-logo-carousel' ),
		'after'  => __( 'Screen larger than 1280px.', 'wpl-logo-carousel' ),
		'default'   => 5
	) );
	$this->metaboxform->number( array(
		'id'    => 'lc_number_of_column_dt',
		'name'  => __( 'Logo Columns in Desktop.', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logo column number in desktop view.', 'wpl-logo-carousel' ),
		'after'  => __( 'Screen smaller than 1280px.', 'wpl-logo-carousel' ),
		'default'   => 5
	) );

	$this->metaboxform->number( array(
		'id'    => 'lc_number_of_column_smdt',
		'name'  => __( 'Logo Columns in Small Desktop.', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logo column number in small desktop view.', 'wpl-logo-carousel' ),
		'after'  => __( 'Screen smaller than 980px.', 'wpl-logo-carousel' ),
		'default'   => 4
	) );
	$this->metaboxform->number( array(
		'id'    => 'lc_number_of_column_tablet',
		'name'  => __( 'Logo Columns in Tablet.', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logo column number in tablet view.', 'wpl-logo-carousel' ),
		'after'  => __( 'Screen smaller than 736px.', 'wpl-logo-carousel' ),
		'default'   => 3
	) );
	$this->metaboxform->number( array(
		'id'    => 'lc_number_of_column_mobile',
		'name'  => __( 'Logo Columns in Mobile.', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logo column number in mobile view.', 'wpl-logo-carousel' ),
		'after'  => __( 'Screen smaller than 480px.', 'wpl-logo-carousel' ),
		'default'   => 2
	) );
	$this->metaboxform->select( array(
		'id'    => 'lc_logos_order_by',
		'name'  => __( 'Order By', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logos order by', 'wpl-logo-carousel' ),
		'options' => array(
			'title' => __('Title', 'wpl-logo-carousel'),
			'date' => __('Date', 'wpl-logo-carousel'),
		),
		'default'   => 'date'
	) );
	$this->metaboxform->select( array(
		'id'    => 'lc_logos_order',
		'name'  => __( 'Order', 'wpl-logo-carousel' ),
		'desc'  => __( 'Logos order', 'wpl-logo-carousel' ),
		'options' => array(
			'ASC' => __('Ascending', 'wpl-logo-carousel'),
			'DESC' => __('Descending', 'wpl-logo-carousel'),
		),
		'default'   => 'descending'
	) );

	?>

</div>