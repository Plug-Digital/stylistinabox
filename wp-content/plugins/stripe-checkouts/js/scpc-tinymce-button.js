(function() {
	tinymce.PluginManager.add('pushortcodes', function( editor, url ) {
		editor.addButton( 'pushortcodes', {
			title: 'WP Simple Pay Lite Shortcodes',
			type: 'menubutton',
			icon: 'icon stripe-pro-addon-shortcodes-icon',						
			menu: [
			       { 
				    text: 'Stripe Checkout Base',
				   		menu: [
							{
								text: 'Basic product checkout',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999"]'+'<br><br>');
								}
							},
							{
								text: 'Require shipping and billing addresses',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999" shipping="true" billing="true"]');
								}
							},
							{
								text: 'Use a custom image on overlay',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999" image_url="http://www.example.com/book_image.jpg"]');
								}
							},
							{
								text: 'Change the checkout button label and disable option to remember Stripe login',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999" checkout_button_label="Now only {{amount}}!" enable_remember="false"]');
								}
							},
							{
								text: 'Pre-fill email of current logged in user',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999" prefill_email="true"]');
								}
							},
							{
								text: 'Set to Mexican Peso currency',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="250" currency="MXN"]');
								}
							},
							{
								text: 'Payment details above or below',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="250" payment_details_placement="below"]');
								}
							}	
						]
				 },
				{
				   	text: 'Total',
				   		menu: [
							{
								text: 'Show the total',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999"]'+'<br>'+'[stripe_total]'+'<br>'+'[/stripe]');
								}
							},
							{
								text: 'Show the total with an alternate label',
								onclick: function() {
									editor.insertContent('[stripe name="My Store" description="My Product" amount="1999"][stripe_total label="Amount Due:"]'+'<br>'+'[/stripe]');
								}
							}
						]
				 }
        ]/* End */
		});
	});
})();