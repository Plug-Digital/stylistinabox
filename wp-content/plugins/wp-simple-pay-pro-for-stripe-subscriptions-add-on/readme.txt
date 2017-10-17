=== WP Simple Pay Pro for Stripe - Subscriptions Add-on ===
Requires at least: 4.2
Tested up to: 4.6
License: GPLv2 or later

== Description ==

WP Simple Pay Pro for Stripe - Subscriptions Add-on

== Changelog ==

= 1.3.4 - September 28, 2016 =

* Fix: Honor base plugin checkout button label attribute or setting value for trial checkout button text.
* Dev: Tested up to WordPress 4.6.

= 1.3.3 - June 18, 2016 =

* Dev: Improve store name url encoding and escaping for display on payment success page.
* Dev: Improve product name escaping for display on payment success page.

= 1.3.2 - May 16, 2016 =

* Feature: Tested with Easy Pricing Tables Premium.
* Fix: Generate a plan name for user-entered amount subscriptions if the description attribute isn't specified.

= 1.3.1 - April 14, 2016 =

* Fix: Set dynamic plan names to match the product description.

= 1.3.0 - March 28, 2016 =

* Feature: End subscriptions after a specific number of charges with the new max_occurrences attribute (aka "installment plans").
* Feature: Quantities of subscriptions can now be set using dropdown, radio or numeric custom fields contained in [stripe] base shortcode. This value is then passed to the Stripe subscription object and indicated on Stripe invoice records.
* Feature: Allow coupon code and setup fee on same form. Coupon code applies to recurring amount only.
* Fix: Detect test mode when attribute set to parent shortcode.
* Fix: Hide success message for trials when disabled in settings.
* Fix: Various amount calculation corrections.
* Dev: Added Stripe customer ID filter (sc_customer_id).
* Dev: Front-end JS refactored for easier maintenance and feature updates.
* Dev: Converted text in front-end JS to translatable strings.
* Dev: Improved plugin updates process.
* Dev: Tested up to WordPress 4.5.

= 1.2.7 - October 6, 2015 =

* Fix: Fixed a bug with non-subscription forms having a NaN amount.

= 1.2.6 - October 1, 2015 =

* Feature: Added [stripe_recurring_total] shortcode.
* Tweak: Updated interval output for subscriptions with a setup fee.

= 1.2.5 - September 29, 2015 =

* Feature: Added setup_fee attribute to main shortcode and plan shortcodes.
* Tweak: Show subscription interval for stripe_total output.

= 1.2.4 - September 10, 2015 =

* Fix: Fixed an intermittent fatal error bug for some users.
* Fix: Fixed an issue with subscriptions with a trial not showing success message.
* Dev: Added sc_trial_payment_details filter.

= 1.2.3 - August 31, 2015 =

* Feature: Added ability via checkbox for users to select a one-time or recurring payment for the amount they enter.

= 1.2.2 - August 15, 2015 =

* Dev: Updated existing sc_redirect filter.
* Dev: Added new sc_redirect_args filter.

= 1.2.1 - August 8, 2015 =

* Fix: Prevent conflicts with other plugins using Stripe.
* Dev: Now using Grunt to automate build and file minification tasks.
* Dev: Now JS & CSS files referenced are the minified versions. If SCRIPT_DEBUG set to true, all JS & CSS files referenced are the debug/unminified versions.

= 1.2.0 - July 25, 2015 =

* Change of base plugin product name (WP Simple Pay Pro for Stripe) and domain name (wpsimplepay.com).
* Major code refactor.
* Tested up to WordPress 4.3.

= 1.1.7 - May 20, 2015 =

* Added check for existence of extra fields before saving to meta data.

= 1.1.6 - April 22, 2015 =

* Updated calls to add_query_arg to prevent any possible XSS attacks.

= 1.1.5 - April 6, 2015 =

* Remove WPUpdatePHP class and usage since Stripe Checkout Pro (base) plugin already makes this check.
* Renamed use of key statement_description to statement_descriptor (Stripe API change).
* Fixed bug with statement_descriptor length.
* Payment success output now properly encodes slashes for store name and description.
* Updated shortcode tracker class.
* Tested up to WordPress 4.2.

= 1.1.4.1 - March 24, 2015 =

* Add extra check for WPUpdatePHP class existence. Fixes redeclare error in some cases.

= 1.1.4 - March 21, 2015 =

* Fixed a bug occurring with multiple plans in some cases.
* Now checks that host is running PHP 5.3.3 or higher using the WPupdatePHP library.

= 1.1.3 - March 18, 2015 =

* Fixed bug when using subscriptions with user-entered amount.
* Now warning, not deactivating this plugin, when the base Stripe Checkout Pro plugin is inactive or missing.
* Fixed bug where test_mode attribute wasn't setting test mode properly.

= 1.1.2 - March 12, 2015 =

* Updated to work with the most recent Stripe PHP library (v2.1.1).

= 1.1.1 =

* Check setting in Stripe Checkout Pro to always show scripts.

= 1.1.0.1 =

* Fixed bug where fatal errors may occur when an old version of Stripe Checkout Pro is still active.

= 1.1.0 =

* Tested up to WordPress 4.1.
* Plugin updater performance improvements.
* Updated to use new Stripe Checkout Pro filters to keep better separation between add-on and base plugin code.
* Fixed bug with use of do_shortcode() with subscription shortcodes in theme template files.
* Updated to utilize current Stripe API updates.
* Display clearer admin warning when shortcode syntax is invalid.
* Plugin script now only enqueued on posts and pages where required.

= 1.0.9 =

* Subscription plans created by user-entered amounts now have clearer plan names, which show up on email receipts and other places.
* Plugin updater performance improvements.

= 1.0.8 =

* Added support and messaging for trial subscription signups.
* Fixed bug with subscription plans and user-entered amounts in different forms on the same page.
* Error handling improvements.
* Simplified text domain function.

= 1.0.7 = 

* Added use_amount shortcode attribute to support new user-entered radio button or drop-down amount fields.

= 1.0.6 =

* Adjustment for charge details change in Stripe Checkout Pro 2.0.7.

= 1.0.5 =

* Fixed plan labels and payment success message to show custom interval counts (i.e. "$50.00 every 3 months").

= 1.0.4 =

* Adjustment for payment button class change in Stripe Checkout Pro 2.0.5.

= 1.0.3 =

* Enabled subscription integration with user entered amounts.
* Enabled subscription integration with custom fields.
* Enabled subscription integration with Stripe coupons.
* Improved invalid Stripe API key handling.
* Better admin-only notices for invalid shortcode combinations.
* Now allows plans of different currencies.

= 1.0.2 =

* Fixed a bug where live keys were throwing errors.

= 1.0.1 =

* Fixed bug with amounts when multiple Stripe checkout forms exist on a single page.

= 1.0.0 =

* Initial add-on release.
