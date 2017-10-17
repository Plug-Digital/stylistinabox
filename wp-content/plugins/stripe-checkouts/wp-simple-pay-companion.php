<?php
/**
 *
 * @package   Simple Stripe Checkout Companion
 * @author    Kyle M. Brown <kyle@kylembrown.com>
 * @license   GPL-2.0+
 * @link      https://kylembrown.com/stripe-checkout-pro-companion
 * @copyright 2014 Kyle M. Brown
 *
 * @wordpress-plugin
 * Plugin Name:       WP Simple Pay Lite for Stripe Companion
 * Plugin URI:        https://kylembrown.com/stripe-checkout-pro-companion
 * Description:       The WP Simple Pay Lite Companion add-on makes it easy to insert shortcodes into your WordPress editor post and pages.
 * Version:           1.3.7
 * Author:            Kyle M. Brown
 * Author URI:        https://kylembrown.com/stripe-checkout-pro-companion
 * Text Domain:       simple-stripe-checout-companion
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

/* Start Plugin Code */

/* Check if SCP is installed */
add_action( 'admin_init', 'stripe_inactive_notice');
function stripe_inactive_notice()
{
	if ( ! class_exists( 'Stripe_Checkout' ) ) {
		deactivate_plugins(plugin_basename( __FILE__ ));
		wp_die( sprintf( __(  'WP Simple Pay Lite Companion requires WP Simple Pay Lite to run properly. Please install WP Simple Pay Lite before activating this plugin. <a href="%s">Return to Plugins</a>'), get_admin_url( '', 'plugins.php') ) );
	}
}

register_activation_hook(  __FILE__, 'install_stripe_companion');
register_uninstall_hook( __FILE__, 'uninstall_stripe_companion' );
add_action('init','install_stripe_companion');

function install_stripe_companion(){
	add_action('admin_menu', 'wpautop_control_menu');		
			
}

/* News */  
// Start Dashboad widget
add_action('wp_dashboard_setup', 'wpspc_custom_dashboard_widgets');
 
function wpspc_custom_dashboard_widgets() {
global $wp_meta_boxes;

wp_add_dashboard_widget('custom_help_widget', 'Stripe for WordPress News', 'wpspc_dashboard_news');
}

function wpspc_dashboard_news() {

echo _e('<a href="http://www.kylembrown.com/stripe/why-i-canceled-paypal-pro-and-built-a-virtual-terminal-app-using-stripe/?utm_source=wp_dashboard_widget&utm_medium=button&utm_campaign=wp_simple_pay_companion“>Kylembrown.com: Why I canceled Paypal Pro and built a virtual terminal app using Stripe</a><br>
<a href="http://www.kylembrown.com/stripe/setup-fee-then-recurring-subscriptions-with-stripe/?utm_source=wp_dashboard_widget&utm_medium=link&utm_campaign=wp_simple_pay_companion“>Kylembrown.com: Setup then recurring subscriptions with Stripe</a>');
  
echo _e('<p><a href="http://kylembrown.com/stripe/?utm_source=wp_dashboard_widget&utm_medium=button&utm_campaign=wp_simple_pay_companion" class="button-primary" target="_blank">Visit our Blog</a></p>');
}
// End Dashboad widget 

/* Upgrade to Pro Menu */

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'sscc_plugin_action_links' );

function sscc_plugin_action_links( $links ) {
  $links[] = '<a href="http://www.kylembrown.com/stripe-checkout-pro-companion-help?utm_source=wordpress_org&utm_medium=plugin_upgrade_link&utm_campaign=simple_stripe_checkout_companion" target="_blank">Help</a>'; 
  $links[] = '<a href="http://www.kylembrown.com/stripe-checkout-pro-companion?utm_source=wordpress_org&utm_medium=plugin_upgrade_link&utm_campaign=simple_stripe_checkout_companion" target="_blank">Upgrade to Pro</a>';
$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/stripe-checkouts" target="_blank">Rate this add-on!</a>';
   return $links;
}

function wpautop_control_menu()
{
  add_submenu_page('stripe-checkout', 'Companion', 'Companion', 'manage_options', 'stripe-checkout-pro-companion', 'scpc_tabbed_kyle');	 
}

add_action('init', 'scpc_add_mce_button');

function scpc_add_mce_button() {
	if( current_user_can('edit_posts') &&  current_user_can('edit_pages') ){
		add_filter( 'mce_external_plugins', 'pu_add_buttons');
		add_filter( 'mce_buttons', 'pu_register_buttons');
	}
}
			
function pu_add_buttons( $plugin_array ){
  $plugin_array['pushortcodes'] = plugin_dir_url( __FILE__ ) . '/js/scpc-tinymce-button.js';
  return $plugin_array;
}

function pu_register_buttons( $buttons ){
	array_push( $buttons, 'separator', 'pushortcodes' );
	return $buttons;
}

function scpc_mce_css() {
	wp_enqueue_style('stripeaddon_shortcodes-tc', plugins_url('/css/scpc_tinymce_style.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'scpc_mce_css' );

function uninstall_stripe_companion(){	
	deactivate_plugins(plugin_basename( __FILE__ ));
}

function scpc_page_kyle(){
	//Testing realtime source delivery of shortcodes
	echo '<div style="margin-right: 310px;"><iframe src="//wpstripe.net/docs/shortcodes/stripe-checkout/" width="100%" height="800"></iframe></div>';
}

/* WP Tabbed Menu Start */
function scpc_tabbed_kyle(){?>
<div class="wrap">		
<h2>WP Simple Pay Lite Companion</h2> 
<div id="scc_tab_container" style="margin-right: 310px;">  
 <div id="scc_inner_container" style="width:100%;"> 
 <h2 class="nav-tab-wrapper">	
	<a class="nav-tab <?php if(!isset($_REQUEST['tab']) ||($_REQUEST['tab']=="shortcodes")){?>nav-tab-active<?php }?>" href="<?php echo admin_url() ?>admin.php?page=stripe-checkout-pro-companion&tab=shortcodes">Shortcodes</a>		
	<a class="nav-tab <?php if($_REQUEST['tab']=="help"){?>nav-tab-active<?php }?>" href="<?php echo admin_url() ?>admin.php?page=stripe-checkout-pro-companion&tab=help">Help</a>		
	<a class="nav-tab <?php if($_REQUEST['tab']=="pro"){?>nav-tab-active<?php }?>" href="<?php echo admin_url() ?>admin.php?page=stripe-checkout-pro-companion&tab=pro">Pro</a>		
	</h2>

<!-- Sidebar Start --> 
<div id="scc_side_bar" style="float: right;margin-right: -310px;width: 290px;">
<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e( 'Need More Options?', 'default' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="last-blurb centered">
					<h4><center><?php _e( "Additional shortcodes you'll get with WP Simple Pay Pro", 'default' ); ?></center></h4>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Let customers enter an amount', 'default' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Collect data with custom fields', 'default' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Offer discounts with coupon codes', 'default' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscriptions integration', 'default' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'New shortcodes as they\'re released', 'default' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Automatic updates & email support', 'default' ); ?></li>
				</ul>

				<div class="centered">
					<center><a href="https://www.kylembrown.com/stripe-checkout-pro-companion/?utm_source=lite-companion&utm_medium=sidebar&utm_campaign=Companion%20Lite"
					   class="button-primary button-large" target="_blank">
						<?php _e( 'Upgrade to Pro Now', 'default' ); ?></a></center>
				</div>
			</div>
		</div>
	</div>
</div>  

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<div class="inside">
			<p>
				<?php _e( 'Your review helps more folks find our plugin. Thanks so much!', 'default' ); ?>
			</p>
			<div class="centered">
				<center><a href="https://wordpress.org/support/view/plugin-reviews/stripe-checkouts#postform" class="button-primary" target="_blank">
					<?php _e( 'Review this Plugin Now', 'default' ); ?></a></center>
			</div>
		</div>
	</div>
</div>  
  
<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<div class="inside">
			<ul>
				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="//kylembrown.com/stripe-checkout-pro-companion-help" target="_blank">
						<?php _e( 'Documentation', 'default' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="https://wordpress.org/support/plugin/stripe-checkouts" target="_blank">
						<?php _e( 'Community support forums', 'default' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="https://dashboard.stripe.com/" target="_blank">
						<?php _e( 'Stripe Dashboard', 'default' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>   
  
 <div class="sidebar-container metabox-holder">
	<div class="postbox-nobg">
		<div class="inside centered">
			<a href="https://stripe.com/" target="_blank">
			<center><div id="powered-by-stripe"></div></center>
			</a>
		</div>
	</div>
 </div>
</div>   
   
<!-- Sidebar End -->
   
</div><!-- scc_inner_container -->    
</div><!-- scc_tab_container -->  
	<?php
		if(!isset($_REQUEST['tab']) ||($_REQUEST['tab']=="shortcodes")){	
			//echo 'shortcode tab';
			scpc_page_kyle();
		} else if($_REQUEST['tab']=="help"){		
		scpc_help_page();		
	}		
	  else if($_REQUEST['tab']=="pro"){		
		scpc_pro_page();
		//stripe_pro_page();		
	}?>		
		</div>		
		<?php		
	}		
	function scpc_help_page()		
	{?>		
	<div style="height:20px;"></div>
	<div>Help documentation can be found at our<a href="https://www.kylembrown.com/stripe-checkout-pro-companion-help" target="_blank"> support</a> page.</div>		
	    <?php		
	}		
	function scpc_pro_page()		
	{?>		
	<div style="height:20px;"></div>
	<div><a href="https://www.kylembrown.com/stripe-checkout-pro-companion/?utm_source=lite-companion&utm_medium=pro-tab&utm_campaign=Companion%20Lite"
					   class="button-primary button-large" target="_blank">
						<?php _e( 'Upgrade to Pro Now', 'default' ); ?></a></div>
	
<?php 
}
/* WP Tabbed Menu End */

function scpc_page(){/* Original page listing codes replaced with scpc_page-kyle */ ?>
<style>
.row-1.odd {
    text-align: left;
}
</style>
	<div class="wrap">
		<h2>List all shortcode</h2>
		
		<p><strong>Shortcode Options</strong></p>
		<p>Below are the available options (attributes) for this shortcode.</p>

		<table class="tablepress tablepress-id-1" id="tablepress-1">
		<thead>
		<tr class="row-1 odd">
			<th class="column-1"><div>Option</div></th><th class="column-2"><div>Description</div></th><th class="column-3"><div>Default</div></th>
		</tr>
		</thead>
		<tbody>
		<tr class="row-2 even">
			<td class="column-1">name</td><td class="column-2">The name of your company or website.</td><td class="column-3">Site title</td>
		</tr>
		<tr class="row-3 odd">
			<td class="column-1">description</td><td class="column-2">A description of the product or service being purchased (optional).</td><td class="column-3"></td>
		</tr>
		<tr class="row-4 even">
			<td class="column-1">amount</td><td class="column-2">Amount in desired currency. Use smallest common currency unit U.S. amounts are in cents. Amount is required.</td><td class="column-3"></td>
		</tr>
		<tr class="row-5 odd">
			<td class="column-1">image_url</td><td class="column-2">A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.</td><td class="column-3"></td>
		</tr>
		<tr class="row-6 even">
			<td class="column-1">currency</td><td class="column-2">Specify a specific currency by using it's 3-letter ISO code.</td><td class="column-3">USD</td>
		</tr>
		<tr class="row-7 odd">
			<td class="column-1">payment_button_label</td><td class="column-2">Text to display on the default payment button that launches the checkout overlay.</td><td class="column-3">Pay with Card</td>
		</tr>
		<tr class="row-8 even">
			<td class="column-1">billing</td><td class="column-2">Used to gather the billing address during the checkout process. (true or false)</td><td class="column-3">false</td>
		</tr>
		<tr class="row-9 odd">
			<td class="column-1">shipping</td><td class="column-2">Used to gather the shipping address during the checkout process. (true or false)</td><td class="column-3">false</td>
		</tr>
		<tr class="row-10 even">
			<td class="column-1">enable_remember</td><td class="column-2">Adds a "remember me" checkbox to the checkout form. (true or false)</td><td class="column-3">true</td>
		</tr>
		<tr class="row-11 odd">
			<td class="column-1">checkout_button_label</td><td class="column-2">Text to display on the final checkout button within the checkout overlay form. Insert {{amount}} where you'd like to display the amount. If {{amount}} is omitted, it will be appended at the end of the button label.</td><td class="column-3">Pay {{amount}}</td>
		</tr>
		<tr class="row-12 even">
			<td class="column-1">success_redirect_url</td><td class="column-2">The URL that the user should be redirected to after a <strong>successful</strong> payment.</td><td class="column-3">Originating page</td>
		</tr>
		<tr class="row-13 odd">
			<td class="column-1">failure_redirect_url</td><td class="column-2">The URL that the user should be redirected to after a <strong>failed</strong> payment.</td><td class="column-3">Originating page</td>
		</tr>
		<tr class="row-14 even">
			<td class="column-1">prefill_email</td><td class="column-2">Prefill the email address box with the email address of the current logged in user.</td><td class="column-3">false</td>
		</tr>
		<tr class="row-15 odd">
			<td class="column-1">verify_zip</td><td class="column-2">Verifies the zipcode of the card. You'll also need to enable this in your Stripe.com account and check the box to decline charges that fail zip code verification).</td><td class="column-3">false</td>
		</tr>
		<tr class="row-16 even">
			<td class="column-1">test_mode</td><td class="column-2">Puts this particular form into test mode even if live mode is selected in the main settings. </td><td class="column-3">false</td>
		</tr>
		<tr class="row-17 odd">
			<td class="column-1">payment_button_style</td><td class="column-2"><strong>(Pro only)</strong> Set to "stripe" to use Stripe's button styles. Set to "none" to ignore Stripe's button styles. Base button CSS class: <code>sc-payment-btn</code>.</td><td class="column-3">stripe</td>
		</tr>
		</tbody>
		</table>

		<p><strong>Basic product checkout:</strong></p>

		<ul style="list-style: disc outside none;margin-left: 50px;">
			<li>[stripe name="My Store" description="My Product" amount="1999"]</li>

			<li>Require shipping and billing addresses:<br/>
				[stripe name="My Store" description="My Product" amount="1999" shipping="true" billing="true"]</li>

			<li>Use a custom image on overlay:<br/>
				[stripe name="My Store" description="My Product" amount="1999" image_url="http://www.example.com/book_image.jpg"]</li>

			<li>Change the checkout button label and disable option to remember Stripe login:<br/>
				[stripe name="My Store" description="My Product" amount="1999" checkout_button_label="Now only {{amount}}!" enable_remember="false"]</li>

			<li>Pre-fill email of current logged in user:<br/>
				[stripe name="My Store" description="My Product" amount="1999" prefill_email="true"]</li>

			<li>Set to Mexican Peso currency:<br/>
				[stripe name="My Store" description="My Product" amount="250" currency="MXN"]</li>
		</ul>
	</div>

<?php
}
?>