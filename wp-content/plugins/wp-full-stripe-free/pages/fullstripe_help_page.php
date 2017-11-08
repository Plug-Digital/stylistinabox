<div class="wrap">
	<h2><?php echo __( 'Full Stripe Help', 'wp-full-stripe-free' ); ?></h2>
	<p>This plugin is designed to make it easy for you to accept payments from your Wordpress site. Powered by Stripe,
		you can embed payment forms into any post or page and take payments directly from your website without making
		your customers leave for a 3rd party website.</p>
	<h4>Setup</h4>
	<ul>
		<li>You need a free Stripe account from <a href="https://stripe.com">Stripe.com</a></li>
		<li>Get your Stripe API keys from your
			<a href="https://manage.stripe.com">Stripe Dashboard</a> -> Account Settings -> API Keys tab
		</li>
		<li>Update the Full Stripe settings with your API keys and select the mode. (Test most is recommended initially
			to make sure everything is setup correctly)
		</li>
	</ul>
	<h4>Payments</h4>
	<p>Now that the Stripe keys are set, you can create a payment form from the Full Stripe Payments page. A payment
		form is setup to take a specific payment amount from your customers. Create the form by setting it's name, title
		and payment amount.
		You can also choose to allow your customers to enter custom amounts on the form. This makes creating things like
		donation forms easier.
		The form name is used in the shortcode (see below) to display the form.</p>
	<p>To show a payment form, add the following shortcode to any post or page:
		<code>[fullstripe_payment form="formName"]</code> where "formName" equals the name you used to create the form.
	</p>
	<p>Once a payment is taken using the form, the payment information will appear on the Full Stripe Payments page as
		well as on your Stripe Dashboard.</p>

	<h4>SSL</h4>
	<p>Use of SSL is
		<strong>highly recommended</strong> as this will protect your customers card details. No card details are ever
		stored on your server however without SSL they are still subject to certain types of hacking. SSL certificates
		are extremely affordable from companies like
		<a href="http://www.namecheap.com/?aff=51961">Namecheap</a> and well worth it for the security of your
		customers.
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
	<p>You can add an extra field to payment forms to include any extra data you want to request from the customer. When
		creating the payment form you can choose to include this extra field and it's title which will be shown to the
		user on the form.
		The extra data will be appended to the payment information and viewable in your Stripe dashboard once the
		payment is complete.</p>
	<h4>Redirects</h4>
	<p>When creating a form you have the option to redirect to a specific page or post after
		a successful payment. To do this you must turn on redirects when creating the form and also to select the
		post/page you wish to redirect to.</p>
	<h4>How to translate the plugin</h4>
	<p>You can translate the public labels of the plugin by following these steps:
		<ol>
			<li>
				Copy the "wp-content/plugins/wp-full-stripe-free/languages/wp-full-stripe.pot" file to
				"wp-content/plugins/wp-full-stripe-free/languages/wp-full-stripe-&lt;language code&gt;_&lt;country code&gt;.po"
				file<br/>
				where &lt;language code&gt; is the two-letter ISO language code and &lt;country code&gt; is the
				two-letter
				ISO country code.<br/>
				Please refer to <a href="http://www.gnu.org/software/gettext/manual/gettext.html#Locale-Names"
				                   target="_blank">Locale names</a> section of the <code>gettext</code> utility manual
				for
				more information.
			</li>
			<li>
				Edit the "wp-content/plugins/wp-full-stripe-free/languages/wp-full-stripe-free-&lt;language code&gt;_&lt;country
				code&gt;.po"
				file:
				<ol>
					<li>Translate the strings that are already in the .po file</li>
					<li>Add strings and their translations to the .po file for these custom labels:
						<ul>
							<li>- Form title</li>
							<li>- Custom field name</li>
							<li>- Payment button text</li>
						</ul>
	<p>
		As an example, if your form title is "Order now!" then you would add these lines to the .po file:<br>
		<code>msgid "Order now!"<br/>
			msgstr "Order now! translated"</code>
	</p>
	</li>
	</ol>
	</li>
	<li>
		Run the <code>msgfmt</code> utility (part of the gettext distribution) to convert the .po file to an .mo
		file, for example:<br/><br/>
		<code>msgfmt -cv -o \<br/>
			wp-content/plugins/wp-full-stripe-free/languages/wp-full-stripe-de_DE.mo \<br/>
			wp-content/plugins/wp-full-stripe-free/languages/wp-full-stripe-de_DE.po
		</code>
	</li>
	<li>
		Make sure that the newly created .mo file is in the "wp-content/plugins/wp-full-stripe-free/languages" folder
		and
		its name conforms to "wp-full-stripe-free-&lt;language code&gt;_&lt;country code&gt;.mo".
	</li>
	</ol>
	</p>
	<h4>More Help</h4>
	<p>If you require any more help with this plugin, you can always go to
		<a href="https://paymentsplugin.com/forums">the WP Full Stripe Support Forums</a> to ask your question, or email us
		directly at
		<a href="mailto:support@paymentsplugin.com">support@paymentsplugin.com</a></p>
	<div style="padding-top: 30px;">
		<h4>Notices</h4>
		<p>Please note that while every care has been taken to write secure and working code, Mammothology and Infinea
			Consulting Ltd.
			take no responsibility for any errors, faults or other problems arising from using this payments
			plugin. Use at your own risk. Mammothology cannot foresee every possible usage and user error and does not
			condone the use of this plugin for any illegal means. Mammothology has no affiliation with
			<a href="https://stripe.com">Stripe</a> and any issues with payments should be directed to
			<a href="https://stripe.com">Stripe.com</a>.</p>
		<p>Please also note that this is free software provided 'as is' with no liability placed on Mammothology or
			Infinea Consulting Ltd. and by using this software you
			agree to do so at your own risk.</p>

	</div>
</div>