<?php
/**
 * This file display meta box tab
 * @package wpl-logo-carousel
 */

$current_screen        = get_current_screen();
$the_current_post_type = $current_screen->post_type;
if ( $the_current_post_type == 'wpl_lcp_shortcodes' ) {
	global $post;
	?>
	<div class="wpl-metabox-framework">
		<div class="wpl_lcf_shortcode_header">
			<div class="wpl_lcf_shortcode_header_logo">
				<img src="<?php echo WPL_LC_URL ?>/admin/assets/images/logo.png" alt="">
			</div>
			<div class="wpl_lcf_shortcode_header_dashboard">
				<span>Dashboard</span>
			</div>
			<div class="wpl_lcf_shortcode_header_help">
				<a href="https://wordpress.org/support/plugin/logo-carousel-free" target="_blank"><i
						class="fa fa-support"></i><span>Help</span></a>
			</div>
		</div>
		<div class="wpl-mbf text-center">
			<div class="wpl-col-lg-3">
				<div class="wpllc-mbf-shortcode">
					<div class="lcplc-shortcode-box"><span
							class="wpllc-shortcode-label"><?php _e( 'Shortcode: ', 'logo-carousel-pro' ); ?></span><span
							class="wpllc-shortcode-text selectable">[logocarousel id="<?php echo $post->ID; ?>"]</span>
					</div>
				</div>
			</div>
			<div class="wpl-col-lg-3">
				<div class="wpllc-mbf-shortcode">
					<div class="lcplc-shortcode-box"><span
							class="wpllc-shortcode-label"><?php _e( 'PHP Function: ', 'logo-carousel-pro' ); ?></span><span
							class="wpllc-shortcode-text selectable">&lt;?php logocarousel("<?php echo $post->ID ?>"); ?&gt;</span>
					</div>
				</div>
			</div>
			<div class="wpl-col-lg-3">
				<div class="wpllc-mbf-shortcode">
					<div class="lcplc-shortcode-box"><span
							class="wpllc-shortcode-label"><?php _e( 'Page or Post editor: ', 'logo-carousel-pro' ); ?></span><img
							class="tmce-btn-image"
							src="<?php echo WPL_LC_URL . 'admin/assets/images/wpllcf-editor-button.png' ?>" alt="">
					</div>
				</div>
			</div>

		</div>
		<div class="wpllc-shortcode-body">
			<div class="wplmb-nav nav-tab-wrapper current">
				<a class="nav-tab nav-tab-active" data-tab="wpllc-tab-1"><i class="sp-icon fa fa-wrench"></i>General
					Settings</a>
				<a class="nav-tab" data-tab="wpllc-tab-2"><i class="sp-icon fa fa-sliders"></i>Carousel Settings</a>
				<a class="nav-tab" data-tab="wpllc-tab-3"><i class="sp-icon fa fa-paint-brush"></i>Stylization</a>
				<a class="nav-tab" data-tab="wpllc-tab-4"><i class="sp-icon fa fa-font"></i>Typography</a>
				<a class="nav-tab" data-tab="wpllc-tab-5 "><i class="sp-icon fa fa-rocket"></i>Upgrade to Pro</a>
			</div>
			<?php
			include_once( 'partials/general-settings.php' );
			include_once( 'partials/carousel-settings.php' );
			include_once( 'partials/stylization.php' );
			include_once( 'partials/typography.php' );
			include_once( 'partials/upgrade-to-pro.php' );
			?>
			<div class="wpllc-nav-background"></div>
		</div>

	</div>
	<?php
}