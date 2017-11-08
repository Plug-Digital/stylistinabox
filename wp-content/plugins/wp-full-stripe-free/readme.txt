=== Plugin Name ===
Contributors: Mammothology
Donate link: https://paymentsplugin.com
Tags: payments, stripe, credit cards, payment gateway, payment plugin, commerce
Requires at least: 4.0.0
Tested up to: 4.8.2
Stable tag: 1.6.0
Version: 1.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Full Stripe (Free Edition) is a Wordpress plugin designed to make it easy for you to accept payments from your Wordpress site.

== Description ==

Full Stripe (Free Edition) is a Wordpress plugin designed to make it easy for you to accept payments from your Wordpress site.
Powered by Stripe, you can embed payment forms into any post or page and take payments directly from your website without making your customers leave for a 3rd party website.

The free edition of WP Full Stripe has the following features:

1. Create payment forms to take payments via Stripe.
1. Drop payment forms on any page or post with a simple shortcode.
1. Choose between set price and donation style payment forms.
1. View list of received payment details from within WordPress.
1. Choose to send email receipts on successful payment, via Stripe.
1. Choose to redirect to a page/post following succesful payment.
1. Customize forms with an extra custom field, email address and billing address.
1. Ajax style forms with no page redirects to take payments.
1. Create multiple versions of payment forms to suit your needs.

[Upgrading to the paid version of WP Full Stripe](https://paymentsplugin.com) will give you these extra features:

1. Sign up users to recurring subscriptions.
1. Create subscription plans that run forever, or terminate after certain number of charges.
1. Customizable subscription forms to drop on any page or post.
1. Manage list of subscribers and subscription plans from within Wordpress.
1. Customizable one-time payment forms to drop on any page or post.
1. Choose from set amount, select amount from list, or custom amount payment forms.
1. Use Stripe Checkout style popup & responsive forms.
1. Add Bitcoin and Alipay payment options to Stripe checkout forms.
1. Manage list of one-time payments from within Wordpress.
1. Add custom fields to payment & subscription forms.
1. Redirect after payment to a page or post.
1. Easily add custom CSS on your forms.
1. One-click form shortcode builder to copy'n'paste shortcodes.
1. Send custom email payment receipts with dynamic content.
1. Translate the plugin to any language, translation templates are included.
1. Regular updates and feature additions.
1. Premium support.

[Check out the paid version of WP Full Stripe](https://paymentsplugin.com)

We have a demo at [paymentsplugin.com](https://paymentsplugin.com/demo) and you can see the latest changelog for the premium version on our [WP Full Stripe CodeCanyon page](http://codecanyon.net/item/wp-full-stripe/5266365?ref=mammothology)

== Installation ==

1. Upload plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set your Stripe API keys in the Full Stripe -> Settings menu
1. Create a payment form in the Full Stripe -> Payments menu
1. Use the `[fullstripe_payment form="form"]` on any page or post to embed your payment forms

== Support ==

The free version of WP Full Stripe has limited support via our [Support Forms](https://paymentsplugin/forums/).
Our paid members receive fast turnaround email support as well.


== Frequently Asked Questions ==

We maintain a set of frequently asked questions on the [Support page](https://paymentsplugin.com/support).

== Changelog ==
= 1.6.0 =
* Stripe PHP client upgraded to v5.2.2 .
* Fixed an issue related to unnecessarily sending form data to the web browser.
* Fixed an issue related to requiring email address even if the email address field is hidden.
= 1.5.1 =
* Fixed an issue with the cardholder name not showing up in the card data in Stripe.
= 1.5.0 =
* Error pane handling modified to work with themes that remove empty p tags.
* Stripe PHP client upgraded to v4.4.0 .
= 1.4.0 =
* Now you can translate also the form title, custom field text, payment button text, and Stripe success and error messages.
* Success messages and error messages are scrolled into view automatically.
* The spinning wheel has been moved next to the payment button.
* Fixed a bug related to the disabled/enabled state of the custom field when editing the form.
= 1.3.0 =
* The plugin is translation-ready! You can translate it to your language without touching the plugin code.
* Added all currencies supported by Stripe.
* New dropdown with autocomplete for currency selection.
* New dropdown with autocomplete for selecting pages/posts the plugin can redirect to after payment.
= 1.1.1 =
* Tested & working for WordPress 4.4
= 1.1 =
* Tested & working for WordPress 4.1
= 1.0 =
* Initial release of WP Full Stripe Free.

== Screenshots ==

For live demo and screenshots, check out [PaymentsPlugin.com](http://paymentsplugin.com)
