<?php
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'news';
?>
<div class="wrap about-wrap">

	<h2><?php printf( __( 'Welcome to WP Full Stripe', 'wp-full-stripe' ) . ' (v%s)', MM_WPFS::VERSION ); ?></h2>

	<div class="about-text">
		<p><?php printf( __( 'Accept payments and subscriptions from your WordPress website. Created by <a href="%s">Mammothology</a><div class=""></div>', 'wp-full-stripe' ), 'https://paymentsplugin.com' ); ?></p>
	</div>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-about&tab=news' ); ?>" class="nav-tab <?php echo $active_tab == 'news' ? 'nav-tab-active' : ''; ?>"><?php _e( 'News', 'wp-full-stripe' ); ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-about&tab=help_and_support' ); ?>" class="nav-tab <?php echo $active_tab == 'help_and_support' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Help & Support', 'wp-full-stripe' ); ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=fullstripe-about&tab=changelog' ); ?>" class="nav-tab <?php echo $active_tab == 'changelog' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Changelog', 'wp-full-stripe' ); ?></a>
	</h2>

	<div class="wpfs-tab-content">
		<?php
		if ( $active_tab == 'news' ) {
			include( 'fragments/about_news.php' );
		} elseif ( $active_tab == 'help_and_support' ) {
			include( 'fragments/about_help_and_support.php' );
		} elseif ( $active_tab == 'changelog' ) {
			include( 'fragments/about_changelog.php' );
		}
		?>
	</div>

</div>