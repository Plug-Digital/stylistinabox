<?php
/**
 * This file add meta box to logo post
 * @package wpl-logo-carousel
 */

$this->metaboxform->url_disabled( array(
	'id'   => 'wpl_lcp_logo_link_option',
	'name' => __( 'Logo URL', 'wpl-logo-carousel' ),
	'desc' => __( 'Write link to make the logo linkable.', 'wpl-logo-carousel' ),
	'after' => __( ' This feature is available in <a href="https://wplimb.com/plugins/logo-carousel-pro/" target="_blank">Pro Version</a> only.', 'wpl-logo-carousel' ),
	'std'  => '#'
) );