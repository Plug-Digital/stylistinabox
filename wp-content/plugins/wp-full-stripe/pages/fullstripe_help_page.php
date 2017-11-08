<p class="wrap">
	<h2><?php esc_html_e( 'WP Full Stripe Help' . ' (v' . MM_WPFS::VERSION . ')', 'wp-full-stripe' ); ?></h2>
	<p>This plugin is designed to make it easy for you to accept payments and create subscriptions from your Wordpress
		site. Powered by Stripe, you can embed payment forms into any post or page and take payments directly from your
		website without making your customers leave for a 3rd party website.</p>
	<h4>Setup</h4>
	<ol>
		<li>You need a free Stripe account from <a target="_blank" href="https://stripe.com">Stripe.com</a></li>
		<li>Get your Stripe API keys from your
            <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Stripe Dashboard -> API</a> page
		</li>
		<li>Update the Full Stripe settings with your API keys and select the mode.<br/>(Test mode is recommended
			initially to make sure everything is set up correctly)
		</li>
		<li>Get your plugin's webhook URL on the Full Stripe settings page.</li>
		<li>Set the webhook URL on the <a target="_blank" href="https://dashboard.stripe.com/account/webhooks">Stripe Dashboard ->
			Webhooks -> Settings</a> page.<br/>
			Add endpoints for both live and test mode, and make sure that all events ("Send me all events" option) are
			sent.
		</li>
	</ol>


    <h4>Introduction to forms: inline forms and popup forms</h4>
    <p>Now that the Stripe keys are set, you can create payment forms and subscription forms as well.<br/><br/>
        Forms are available in two flavors: inline and popup:<br/>
        <ul>
            <li>Inline forms are standard HTML forms, all fields rendered as part of your page, no popups used.</li>
            <li>Popup forms take advantage of <a href="https://stripe.com/docs/checkout">Stripe Checkout</a>
                functionality which will give you the option to place a button on any post or page. <br/>
                The button will trigger loading of a pre-styled form with built in validation. The styling and functionality of
                the form is all controlled by Stripe and offers a fast and easy way to get started.</li>
        </ul>
    </p>


	<h4>One-time payments</h4>
	<p>You can create one-time payment forms on the "Full Stripe -> Payments -> Payment Forms" page.<br/>
        A payment form is set up to take a specific payment amount from your customers. Create the form by setting it's name, title
		and payment amount.
		You can also choose to allow your customers to enter custom amounts on the form, or to select the amount from a
		list. This makes creating things like donation forms easier.
		The form name is used in the shortcode (see below) to display the form.</p>
	<p>To show an inline payment form, add the following shortcode to any post or page:
		<code>[fullstripe_payment form="formName"]</code> where "formName" equals the name you used to create the form.
	</p>
    <p>To show a popup payment form, add the following shortcode to any post or page:
        <code>[fullstripe_checkout form="formName"]</code> where "formName" equals the name you used to create the form.
    </p>

	<p>Once a payment is taken using the form, the payment information will appear on the "Full Stripe -> Payments -> Payments" page as
		well as on your Stripe Dashboard.</p>
	<p>You can also accept Bitcoin payments with popup payment forms. In order to use Bitcoin payments, make sure that the
		payment currency of your website is USD, and enable the "Use Bitcoin?" option on your popup payment form.</p>

	<h4>Subscriptions</h4>
	<p>Similar to one-time payments, you can sign customers up for recurring subscriptions using a subscription form. Before
		creating subscription forms, you will need to create a subscription plan. You can do this from the "Full Stripe -> Subscriptions ->
		Subscription Plans" page.
	</p>
	<p>When creating a subscription form you choose the name, title and the plans you wish to offer your customer using
        this form.</p>
    <p>To show an inline subscription form, add the following shortcode to any post or page:
		<code>[fullstripe_subscription form="formName"]</code> where "formName" equals the name you used to create the
		form.
	</p>
    <p>To show a popup subscription form, add the following shortcode to any post or page:
        <code>[fullstripe_subscription_checkout form="formName"]</code> where "formName" equals the name you used to create the
        form.
    </p>

	<p>You can view your list of subscribers on the "Full Stripe -> Subscriptions -> Subscribers" page or directly
		on the
		<a href="https://manage.stripe.com">Stripe Dashboard</a></p>
	<p>You can also cancel subscriptions from the "Subscribers" page.<br/>
		(Note: Cancelling subscriptions works only for subscriptions created with the v3.6+ versions of the plugin)
	</p>
	<h4>Subscriptions ending automatically - Payments in installments</h4>
	<p>You can create subscriptions ending automatically after a certain number of charges.<br/>As an example, your
		customers could pay for your consulting or courses in installments.</p>
	<p>In order to do this, create a subscription plan with the "Payment cancellation count" option set to the number of
		charges after which the subscription should end, and use this plan on the subscription form.</p>
	<p>IMPORTANT: Make sure that webhooks are set up properly, otherwise this functionality will not work.</p>
	<h4>SSL</h4>
	<p>Use of SSL is
		<strong>highly recommended</strong> as this will protect your customers card details. No card details are ever
		stored on your server. However, without SSL they are still subject to certain types of hacking. SSL certificates
		are extremely affordable from companies like
		<a href="http://www.namecheap.com">Namecheap</a> and well worth it for the security of your customers.
	</p>
	<h4>Payment Currency</h4>
	<p>The currencies Stripe supports depend on where your business is located. If you select a country/currency
		combination that Stripe does not support then the payment will fail.</p>
	<p>Currently, businesses in the US and Europe can create charges in 138 currencies for Visa, Mastercard and American
		Express credit cards.
		Businesses based in Canada can charge in Canadian dollars (CAD) and US Dollars (USD).
		Australian businesses can create charges in 117 currencies for Visa and MasterCard cards.
		Businesses based in Japan can charge in Japanese Yen (JPY).<br/>
		Please refer to the
		<a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">Stripe
			documentation</a> for more details.
	</p>

	<h4>Custom Fields</h4>
	<p>You can add up to 5 extra fields to payment & subscription forms to include any extra data you want to request
		from the customer. When creating the form you can choose to include the extra fields and it's title will be
		shown to the user on the form.
		The extra data will be appended to the payment information and viewable in your Stripe dashboard once the
		payment is complete.</p>

	<h4>Coupons</h4>
	<p>You can accept coupon codes with subscriptions. First you must create the coupon in your Stripe dashboard. When
		creating your subscription forms you can turn on the option to allow a coupon code input and if the customer
		adds the correct code this will be applied to their payment(s).</p>
	<a name="receipt-tokens"></a>

	<h4>Email Receipts</h4>
	<p>All payment forms can send customized email notifications.<br/>
		Payment forms can send a payment receipt.<br/>
		Subscription forms can send a subscription receipt, and a notification when a subscription has ended.<br>
		<br/>
		You have some placetokens that can be placed in the email HTML
		and WP Full Stripe will replace them with the relevant values. The tokens that can be used are: <br/>
	<ul>
		<li><strong>%AMOUNT%</strong> - The payment amount</li>
		<li><strong>%PRODUCT_NAME%</strong> - The name of the selected payment option (payment forms only, used when
			payment type is "Select Amount from List")
		</li>
		<li><strong>%SETUP_FEE%</strong> - The setup fee of the subscription (subscription forms only)</li>
		<li><strong>%PLAN_NAME%</strong> - The name of the subscription plan (subscription forms only)</li>
		<li><strong>%PLAN_AMOUNT%</strong> - The amount of the subscription plan (subscription forms only)</li>
		<li><strong>%NAME%</strong> - The name of your WordPress blog</li>
		<li><strong>%CUSTOMERNAME%</strong> - The customer's cardholder name</li>
		<li><strong>%CUSTOMER_EMAIL%</strong> - The customer's email address</li>
		<li><strong>%ADDRESS1%</strong> - The customer's billing address line 1 (street)</li>
		<li><strong>%ADDRESS2%</strong> - The customer's billing address line 2</li>
		<li><strong>%CITY%</strong> - The customer's billing address city</li>
		<li><strong>%STATE%</strong> - The customer's billing address state (or region/county)</li>
		<li><strong>%ZIP%</strong> - The customer's billing address zip (or postal) code</li>
        <li><strong>%COUNTRY%</strong> - The customer's billing address country</li>
		<li><strong>%CUSTOMFIELD1%</strong> - Token for the 1st custom field of the form</li>
		<li><strong>%CUSTOMFIELD2%</strong> - Token for the 2nd custom field of the form</li>
		<li><strong>%CUSTOMFIELD3%</strong> - Token for the 3rd custom field of the form</li>
		<li><strong>%CUSTOMFIELD4%</strong> - Token for the 4th custom field of the form</li>
		<li><strong>%CUSTOMFIELD5%</strong> - Token for the 5th custom field of the form</li>
        <li><strong>%DATE%</strong> - The date on which the email notification is sent (uses the preferred date format of Wordpress)</li>
    </ul>
	</p>

	<a name="thank-you-page-tokens"></a>
	<h4>Redirects and confirmation ("Thank you") pages</h4>
    <p>After a successful payment, the plugin can redirect to any page, post, or external URL known as the confirmation ("Thank you") page.</p>
    <p>
        You can enable redirects on a form by:
        <ol>
            <li>Setting the "Redirect on Success?" option to "Yes"; and</li>
            <li>Setting the page or post in the "Redirect to" option.</li>
        </ol>
    </p>

    <p>
        You can use placeholder tokens on "Thank you" pages, similar to email receipts.<br/>
        It can be turned on by checking the "Enable placeholder tokens on Thank You pages?" option of the form.<br/>
        <br/>
        When placeholder tokens are allowed on a Thank you page, then payment-related information can be displayed only once, after having redirected from the payment page.
    </p>

	<p>Use the following shortcodes on a Thank you page to display payment-related information:</p>
	<p>
        <pre>
        [fullstripe_thankyou]
        [fullstripe_thankyou_success]
		Thank you for your purchase, %CUSTOMERNAME%!
        [/fullstripe_thankyou_success]
        [fullstripe_thankyou_default]
		No payment data available
        [/fullstripe_thankyou_default]
        [/fullstripe_thankyou]
        </pre>
	</p>
    <p>
        You can use the <code>[fullstripe_thankyou]</code> shortcode to mark area where you'd like the payment information to be displayed.<br/>
        <br/>
        Inside <code>[fullstripe_thankyou]</code>, use the <code>[fullstripe_thankyou_success]</code> shortcode to display payment-related data (see the <a href="#receipt-tokens">Email Receipts</a> section for the placeholder tokens),
        and use <code>[fullstripe_thankyou_default]</code> to display feedback to the user when the Thank you page has been called directly.
    </p>
	<h4>How to translate the plugin</h4>
	<p>You can translate the public labels of the plugin by following these steps:
	<ol>
		<li>
			Copy the "wp-content/plugins/wp-full-stripe/languages/wp-full-stripe.pot" file to
			"wp-content/plugins/wp-full-stripe/languages/wp-full-stripe-&lt;language code&gt;_&lt;country code&gt;.po"
			file<br/>
			where &lt;language code&gt; is the two-letter ISO language code and &lt;country code&gt; is the two-letter
			ISO country code.<br/>
			Please refer to
			<a href="http://www.gnu.org/software/gettext/manual/gettext.html#Locale-Names" target="_blank">Locale
				names</a> section of the <code>gettext</code> utility manual for more information.
		</li>
		<li>
			Edit the "wp-content/plugins/wp-full-stripe/languages/wp-full-stripe-&lt;language code&gt;_&lt;country code&gt;.po"
			file and add your translations to it.
		</li>
        <li>
            Use the <a href="http://po2mo.net" target="_blank">http://po2mo.net</a> website or a similar service to convert your .po file to an .mo file.
        </li>
		<li>
			If you don't want to upload your .po file to a second party, then run the <code>msgfmt</code> utility (part of the gettext distribution) to convert the .po file to an .mo
			file, for example:<br/><br/>
			<code>msgfmt -cv -o \<br/>
				wp-content/plugins/wp-full-stripe/languages/wp-full-stripe-de_DE.mo \<br/>
				wp-content/plugins/wp-full-stripe/languages/wp-full-stripe-de_DE.po
			</code>
		</li>
		<li>
			Make sure that the newly created .mo file is in the "wp-content/plugins/wp-full-stripe/languages" folder and
			its name conforms to "wp-full-stripe-&lt;language code&gt;_&lt;country code&gt;.po".
		</li>
	</ol>
	</p>
	<h4>Plugin Updates</h4>
	<p>If you are having any issues when updating the plugin to the latest code, please try re-installing the plugin
		first and then de-activate, activate again. This forces the database to update any changes. Don't worry, none of
		your data will be lost if you do this!</p>
	<h4>More Help</h4>
	<p>If you require any more help with this plugin, you can always go to the
		<a href="http://paymentsplugin.com/support/">WP Full Stripe support page</a> to ask your question, or email us
		directly at
		<a href="mailto:support@paymentsplugin.com">support@paymentsplugin.com</a></p>
	<div style="padding-top: 50px;">
		<h4>Notices</h4>
		<p>Please note that while every care has been taken to write secure and working code, Mammothology and Infinea
			Consulting Ltd take no responsibility for any errors, faults or other problems arising from using this
			payments plugin. Use at your own risk. Mammothology cannot foresee every possible usage and user error and
			does not condone the use of this plugin for any illegal means. Mammothology has no affiliation with
			<a href="https://stripe.com">Stripe</a> and any issues with payments should be directed to
			<a href="https://stripe.com">Stripe.com</a>.</p>
	</div>
</div>