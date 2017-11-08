<?php
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'stripe';
$wpfs_main  = MM_WPFS::getInstance();
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Full Stripe Settings', 'wp-full-stripe' ); ?></h2>

	<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
	<?php $wpfs_main->getAdminMenu()->display_settings_nav_tabs(); ?>
	<div class="wpfs-tab-content">
		<?php $wpfs_main->getAdminMenu()->display_settings_active_tab(); ?>
	</div>
</div>
