=== WP Full Stripe ===
Contributors: Mammothology
Tags: commerce, membership, payment, payments, payments plugin, product, stripe, subscribe, subscription, wordpress payments, wordpress subscription
Requires at least: 4.0.0
Tested up to: 4.8.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Online payments made easy in WordPress.

== Description ==

Full Stripe is a WordPress plugin designed to make it easy for you to accept payments and create subscriptions from your WordPress site. Powered by Stripe, you can embed payment forms into any post or page and take payments directly from your website without making your customers leave for a 3rd party website.

###WP Full Stripe Features:###

* IMPORTANT! The v3.11.0 release contains critical security fixes and critical bugfixes. Please update your Full Stripe installation as soon as possible!!!
* NEW! Added support for "custom amount" and "select amount from list" payment types to popup one-time payment forms (Stripe checkout forms).
* Securely take payments from any page or post on your Wordpress website in over 100 currencies
* Take payments in Bitcoin and with Alipay
* Allow your users to subscribe for recurring (ever-running, or terminating) payments with setup fees and non-standard intervals
* Collect one-time payments for set amount, custom amount, or amount selectable from list
* Customize the forms: add custom fields, style the forms with custom CSS
* Send custom payment emails
* Inline and Stripe Checkout-style payment forms
* Easily view, and manage your received payments, subscribers, plans and more
* Form shortcode generator added for embedding forms easily into pages and posts (simple copy'n'paste)!
* The plugin can auto-update to the latest version with the click of a button!
* Fully supported, professionally written and regularly updated software

== Installation ==

1. Uninstall any previous version of the plugin (No data will be lost)
1. Download this plugin.
1. Login to your WordPress admin.
1. Click on the plugins tab.
1. Click the Add New button.
1. Click the Upload button.
1. Click "Install Now", then Activate, then head to the new menu item on the left labeled "Full Stripe".

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= v3.11.1 (Sep 5, 2017) =
* Fixed a billing address validation issue on inline one-time forms.

= v3.11.0 (Aug 22, 2017) =
* IMPORTANT! This release contains critical security fixes and critical bugfixes. Please update your Full Stripe installation as soon as possible!!!
* Added support for "custom amount" and "select amount from list" payment types to popup one-time payment forms (Stripe checkout forms).
* Updated the Stripe PHP client to the latest version (v5.1.1)

= v3.10.0 (Aug 18, 2017) =
* Added popup (Stripe checkout) support to subscription forms.
* Added custom field support to all form types (one-time and subscription, inline and popup).
* Fixed issues with zero-decimal currencies (like the Japanese Yen).
* Fixed WP admin URLs linking to Stripe charges and Stripe subscriptions.
* Fixed the value of the %AMOUNT% placeholder on subscription forms where both setup fee and plan fee have to be charged.
* Fixed an issue of not being able to select certain payment confirmation ("Thank you") pages for redirects

= v3.9.1 (April 19, 2017) =
* Fixed a bug on the edit page of popup forms in WP admin

= v3.9.0 (April 18, 2017) =
* Payment currency can be set per form.
* Payment currency can be set per subscription plan.
* Setup fee can be set per subscription plan.
* Split all form editor pages into tabs in order to make room for new features.
* Added the %DATE% placeholder token for email notifications.

= v3.8.2 (March 1, 2017) =
* Fixed a bug related to customizable "Thank you" (payment confirmation) pages

= v3.8.1 (February 26, 2017) =
* Fixed a bug related to PHP 5.3.x compatibility.

= v3.8.0 (February 24, 2017) =
* All forms are now responsive and mobile friendly.
* "Thank you" pages after payment are customizable with placeholder tokens.
* Payment types "Select amount from list" and "Custom amount" can be combined on one-time payment (and donation) forms.
* New option added to make custom fields mandatory.
* Minimum plugin requirements are verified at activation time.
* Added collision prevention code to handle those cases when other plugins load jQuery in a non-standard way.
* Fixed a bug with the %PRODUCT_NAME% placeholder when the payment type is "Select Amount from List".
* Fixed a bug with form names containing only digits.
* Fixed a bug with error messages when invalid card expiry date is provided.

= v3.7.5 (February 15, 2017) =
* Fixed an issue with payment descriptions containing commas when the payment type is "Select Amount from List".

= v3.7.4 (January 23, 2017) =
* Increased amount length from 6 digits to 8.
* The Stripe PHP client has been upgraded to v4.4.0 .
* Fixed a bug that caused the product description not properly being mapped to the %PRODUCT_NAME% placeholder on Stripe checkout forms.

= v3.7.3 (December 2, 2016) =
* The Stripe PHP client has been upgraded to v4.2.0 .

= v3.7.2 (November 24, 2016) =
* Fixed a bug related to missing button icons in WP Admin.
* Fixed a bug that prevented the plugin from being activated (class name collision with other plugins).
* Plan label handling modified to work with themes that remove empty &lt;p&gt;tags.

= v3.7.1 (November 16, 2016) =
* Error pane handling modified to work with themes that remove empty <p> tags.
* Fixed a bug that would prevent the plugin from displaying more than 100 subscription plans.
* Removed placeholders for the card and name fields on subscription forms

= v3.7.0 (November 2, 2016) =
* Any number of forms can be embedded into a page or post!
* The plugin can auto-update to the latest version with the click of a button!
* Form shortcode generator added for embedding forms easily into pages and posts (simple copy'n'paste)!
* AliPay support added for one-time payments on Stripe checkout-style payment forms.
* Subscriptions can now be deleted on the "Subscribers" page.
* Country dropdown has been added to the billing address on all form types.
* The "Action" column has been redesigned on all admin pages (iconified buttons).
* The "Payments" page has a new layout, it is more structured and more spacious.
* The "Payments" page has got a search box. Find payments based on customer's name and email address, Stripe customer id, Stripe charge id, or mode (live/test).
* The "Settings" page can now be extended by add-ons.
* "Newsfeed" tab has been added to the "About" page.
* Fixed an issue related to being unable to save subscription forms with selected subscription plan names containing spaces.
* The "Transfers" feature has been removed due to incompatibility with the latest Stripe API (will be reintroduced later).
* The Stripe client and API used by the plugin has been upgraded to v3.21.0 in order to be compatible with TLS 1.2.

= v3.6.0 (June 3, 2016) =
* Support for subscriptions that terminate after certain number of charges!
* Subscriptions can be cancelled from the “Subscribers” page.
* The “Subscribers” page has a new layout, it is more structured and more spacious.
* The “Subscribers” page has a search box. Find subscriptions based on subscribers’ name and email address, Stripe customer id, Stripe subscription id, or mode (live/test).
* The “Settings / E-mail receipts” page has a new layout for managing e-mail notifications (new email types coming soon).
* Now you can translate form titles and custom field labels to other languages as well.
* Stripe webhook support added for advanced features in the coming releases.
* Fixed an issue related to the value of the PLAN_AMOUNT token when a coupon is applied to the subscription.
* Fixed an issue related to plan ids, now they can contain comma characters.
* Improved error handling and error messages for internal errors.

= v3.5.1 (March 15, 2016) =
* Added PRODUCT_NAME token to email receipts (used when payment type is “Select Amount from List”)
* Added extra error handling for failed cards (declined, expired, invalid CVC).
* Fixed issue with long plan lists on subscription forms.

= v3.5.0 (February 21, 2016) =
* Added Bitcoin support for checkout forms!
* The e-mail field can be locked and filled in automatically for logged in users.
* Success messages and error messages are scrolled into view automatically.
* The spinning wheel has been moved next to the payment button on all form types.
* The lists on the "Payments" and "Subscribers" pages now are descending and ordered by date by default.
* Fixed an issue withX payment forms on Wordpress 4.4.x: the submitted forms never returned.

= v3.4.0 (December 6, 2016) =
* New payment type introduced on payment forms: the customer can select the payment amount from a list.
* The “Settings” page is now easier to use, it has been divided into three tabs: Stripe, Appearance, and Email receipts.
* The e-mail receipt sender address is now configurable.
* All payment forms (payment, checkout, subscription) add the same metadata fields to the Stripe “Payment” and “Customer” objects.
* CSS style improvements to assure compatibility with the KOMetrics plugin.

= v3.3.0 (October 30, 2016) =
* The plugin is translation-ready! You can translate it to your language without touching the plugin code. (Public labels only)
* Usability improvements made to the currency selector on the “Settings” page.
* Improved error handling on all form types (payment, checkout, and subscription).
* Version number of the plugin is displayed on the “About” and “Help” pages in WP Admin.
* Confirmation dialog has been added to delete operations where it was missing.
* Fixed an issue on subscription forms with the progress indicator spinning endlessly, never returning.
* Fixed an issue on checkout forms with the CUSTOMERNAME token not resolved properly in email receipts.

= v3.2.0 (August 22, 2016) =
* Subscription plans on subscription forms can be reordered by using drag and drop!
* Subscription plans can be modified or deleted directly from WP Full Stripe.
* Page or post redirects can be selected using an autocomplete, no time wasted with figuring out post ids.
* Arbitrary URLs can be used as redirect URLs.
* Placeholder tokens for custom fields are available in email receipts.

= v3.1.1 (July 18, 2016) =
* Fixed a bug with Stripe receipt emails on subscription forms.

= v3.1.0 (June 25, 2016) =
* Now you can use plugin email receipts for all form types (payment, checkout, and subscription) !!
* New email receipt tokens: customer email, subscription plan name, subscription plan amount, subscription setup fee.
* Separate email template and subject fields for payment forms and subscription forms.
* Support for all countries supported by Stripe (20 countries currently).
* Support for all currencies supported by Stripe (138 currencies in total, number varies by country).

= December 30, 2014 =
* You can now use multiple checkout buttons on the same page!
* Checkout button styling can now be disabled (useful for theme conflicts).
* Some minor changes added for future extensions.

= December 5, 2014 =
* Removing form input placeholders as they conflict with some themes.
* SSN is no longer a required field for transfer forms.
* Support for KO Metrics added.
* Bugfix: settings upgrade properly when installing a new version of the plugin.

= November 4, 2014 =
* You can now add up to 5 custom input fields to payment & subscription forms!
* Subscribers and payment records can now be deleted locally (they remain in your Stripe dashboard).
* Lots of UI/UX improvements including appropriate table styling and useful redirects.
* Added livemode status to subscribers.
* Cardholder name correctly added to payment details.
