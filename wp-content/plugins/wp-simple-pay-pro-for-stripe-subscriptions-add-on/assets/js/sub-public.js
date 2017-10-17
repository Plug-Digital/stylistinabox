// Sub public main

/* global simplePayFrontendGlobals, simplePaySubscriptionsFrontendGlobals, simplePayFormSettings, accounting, spApp */

// Define submodule off of global spApp to hold properties & functions for use in this add-on only.
spApp.Subscriptions = {};

(function( $ ) {
	'use strict';

	var body = $( document.body );

	// *** Subscriptions add-on Functions ***/

	spApp.Subscriptions = {

		init: function() {

			spApp.debugLog( 'spApp.Subscriptions.init', this );

			// Loop through and initialize each form.
			spApp.spFormElList.each( function() {

				var spFormEl = $( this );
				spApp.Subscriptions.processForm( spFormEl );
			} );
		},

		processForm: function( spFormEl ) {

			// Get internal numeric ID (should start from 1).
			var formId = spFormEl.data( 'sc-id' );

			// Local var for form data
			var formData = spApp.spFormData[ formId ];

			// Init Subscription-specific property values
			formData.subBaseRecurringAmount = formData.baseAmount;
			formData.subDiscountedRecurringAmount = formData.subBaseRecurringAmount;
			formData.subRecurringAmount = formData.subBaseRecurringAmount;

			// Defaults also in PHP. May be unnecessary to set here.
			formData.subPlanId = '';
			formData.subInterval = 'month';
			formData.subIntervalCount = 1;
			formData.subSetupFee = 0;
			formData.subDefaultSetupFee = 0;
			formData.subTrialDays = 0;
			formData.subDefaultCurrency = formData.currency;

			// Max occurrences working without property in JS.
			// subQuantity property not needed since we're using base itemQuantity property.

			// Set base plugin zero amount to base checkout button label if set, otherwise use our default "Start Free Trial" text.
			if ( formData.checkoutButtonLabel ) {
				formData.zeroAmountCheckoutButtonLabel = formData.checkoutButtonLabel;
			} else {
				// Set base plugin zero amount checkout button label to subscriptions trial checkout button label.
				formData.zeroAmountCheckoutButtonLabel = simplePaySubscriptionsFrontendGlobals.trialCheckoutButtonLabel;
			}

			this.setValuesFromBaseForm( spFormEl, formData );
			this.updateRecurringAmount( spFormEl, formData );

			// *** Subscriptions-only Form-level Event Handlers ***/

			spFormEl.find( '.sc-uea-custom-amount' ).on( 'keyup.spUserEnteredAmount change.spUserEnteredAmount', function( e ) {
				// Set base recurring amount from base plugin's amount as it's already calculated there within the same event.
				formData.subBaseRecurringAmount = formData.baseAmount;
				spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
			} );

			spFormEl.find( '.sc-cf-amount' ).on( 'change.spSelectAmount', function( e ) {
				formData.subBaseRecurringAmount = formData.baseAmount;

				// Update recurring amount now only if no coupon code, otherwise wait for spCouponApplied event.
				if ( formData.couponCode.trim() === '' ) {
					spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
				}
			} );

			// This event is NOT triggered in the base plugin (unlike the others).
			spFormEl.find( '.sc_sub_wrapper' ).find( '.sp-sub-plan' ).on( 'change.spSubPlan', function( e ) {
				spApp.Subscriptions.setValuesFromSelectedPlan( spFormEl, formData );

				// Update recurring amount now only if no coupon code, otherwise wait for spCouponApplied event.
				if ( formData.couponCode.trim() === '' ) {
					spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
				}
			} );

			// Custom event fired when starting the coupon apply process.
			// Not needed currently.
			spFormEl.on( 'spCouponApplyStart', function( e ) {
			} );

			// Custom event fired after each successful coupon applied and new values returned after ajax post.
			spFormEl.on( 'spCouponApplied', function( e ) {
				// Set discounted recurring amount from base plugin's discounted amount after calculating coupon.
				formData.subDiscountedRecurringAmount = formData.discountedAmount;
				spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
			} );

			// Custom event fired after coupon removed.
			spFormEl.on( 'spCouponRemoved', function( e ) {
				formData.subDiscountedRecurringAmount = formData.discountedAmount;
				spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
			} );

			// Custom event fired when is_quantity custom field values change.
			spFormEl.on( 'spQuantityChanged', function( e ) {
				// Set hidden field for subscription quantities from base form itemQuantity property.
				if ( spFormEl.find( '.sc_sub_quantity' ).length > 0 ) {
					spFormEl.find( '.sc_sub_quantity' ).val( formData.itemQuantity );
				}

				spApp.Subscriptions.updateRecurringAmount( spFormEl, formData );
			} );

			// Trigger event handlers on first load.

			// Trigger each dropdown or radio button selected plan.
			var selectedPlanEl = this.getSelectedPlan( spFormEl );
			selectedPlanEl.trigger( 'change.spSubPlan' );

			// Trigger quantity in case default quantity other than 1 is set.
			spFormEl.trigger( 'spQuantityChanged' );
		},

		// Set base form subscription properties to use.
		// Can be overridden with selected plan values.
		setValuesFromBaseForm: function( spFormEl, spFormData ) {

			if ( spFormEl.find( '.sc_sub_amount' ).length > 0 ) {
				spFormData.subBaseRecurringAmount = parseInt( spFormEl.find( '.sc_sub_amount' ).val() );
			}
			if ( spFormEl.find( '.sc_sub_id' ).length > 0 ) {
				spFormData.subPlanId = spFormEl.find( '.sc_sub_id' ).val();
			}
			if ( spFormEl.find( '.sc_sub_interval' ).length > 0 ) {
				spFormData.subInterval = spFormEl.find( '.sc_sub_interval' ).val();
			}
			if ( spFormEl.find( '.sc_sub_interval_count' ).length > 0 ) {
				spFormData.subIntervalCount = parseInt( spFormEl.find( '.sc_sub_interval_count' ).val() );
			}
			if ( spFormEl.find( '.sc_sub_setup_fee' ).length > 0 ) {
				spFormData.subSetupFee = parseInt( spFormEl.find( '.sc_sub_setup_fee' ).val() );
			}
			if ( spFormEl.find( '.sc_sub_trial_days' ).length > 0 ) {
				spFormData.subTrialDays = parseInt( spFormEl.find( '.sc_sub_trial_days' ).val() );
			}

			// Set base form currency from preset plan currency in case using single plan format.
			if ( spFormEl.find( '.sc_sub_currency' ).length > 0 ) {
				spFormData.currency = spFormEl.find( '.sc_sub_currency' ).val();
			}

			// All hidden fields that we read from are ready for form post at this point.
			// Trial days don't need to post, but we ready from this value so leave as is.

			// Set a default setup fee at base form level in case selected plan setup fees need it.
			spFormData.subDefaultSetupFee = spFormData.subSetupFee;

			// Attempt to run applyCoupon in case a coupon code is in effect.
			spApp.applyCoupon( spFormEl, spFormData );
		},

		// Get selected plan from dropdown or radio button list.
		getSelectedPlan: function( spFormEl ) {

			var planGroupEl = spFormEl.find( '.sc_sub_wrapper' ).find( '.sp-sub-plan' );

			// Check for radio button vs dropdown.
			if ( planGroupEl.is( 'input[type="radio"]' ) ) {
				return planGroupEl.filter( ':checked' );
			} else {
				return planGroupEl.find( 'option:selected' );
			}
		},

		// Update subscription properties from select plan when there are multiple plans.
		setValuesFromSelectedPlan: function( spFormEl, spFormData ) {

			// Get selected plan when radio button list exists.
			// Returns zero-length object if not found.
			var selectedPlanEl = this.getSelectedPlan( spFormEl );

			if ( selectedPlanEl.length > 0 ) {

				// Retrieve and store values from selected plan data attributes.
				if ( selectedPlanEl.data( 'sub-amount' ) !== undefined ) {
					spFormData.subBaseRecurringAmount = parseInt( selectedPlanEl.data( 'sub-amount' ) );

					//Set base plugin's amount from base recurring amount for calculating coupons and such.
					spFormData.baseAmount = spFormData.subBaseRecurringAmount;
				}
				if ( selectedPlanEl.data( 'sub-id' ) !== undefined ) {
					spFormData.subPlanId = selectedPlanEl.data( 'sub-id' );
				}
				if ( selectedPlanEl.data( 'sub-interval' ) !== undefined ) {
					spFormData.subInterval = selectedPlanEl.data( 'sub-interval' );
				}
				if ( selectedPlanEl.data( 'sub-interval-count' ) !== undefined ) {
					spFormData.subIntervalCount = parseInt( selectedPlanEl.data( 'sub-interval-count' ) );
				}

				// Check for setup fee attribute in selected plan.
				// If it doesn't exist or is zero, use the base form (default) setup fee.
				if ( selectedPlanEl.data( 'sub-setup-fee' ) !== undefined ) {
					spFormData.subSetupFee = parseInt( selectedPlanEl.data( 'sub-setup-fee' ) );
				}
				if ( spFormData.subSetupFee === 0 ) {
					spFormData.subSetupFee = spFormData.subDefaultSetupFee;
				}

				if ( selectedPlanEl.data( 'sub-trial-days' ) !== undefined ) {
					spFormData.subTrialDays = parseInt( selectedPlanEl.data( 'sub-trial-days' ) );
				}

				// Check for currency attribute in selected plan.
				// If it doesn't exist or is blank, use the base form (default) currency.
				if ( selectedPlanEl.data( 'sub-currency' ) !== undefined ) {
					spFormData.currency = selectedPlanEl.data( 'sub-currency' );
				}
				if ( spFormData.currency.trim() === '' ) {
					spFormData.currency = spFormData.subDefaultCurrency;
				}

				// Update base form hidden fields for submitting custom plans during form post.
				// Don't need to trial days.
				spFormEl.find( '.sc_sub_amount' ).val( spFormData.subBaseRecurringAmount );
				spFormEl.find( '.sc_sub_id' ).val( spFormData.subPlanId ); // Needed for preset plans
				spFormEl.find( '.sc_sub_interval' ).val( spFormData.subInterval ); // Needed for custom plans
				spFormEl.find( '.sc_sub_interval_count' ).val( spFormData.subIntervalCount ); // Needed for custom plans
				spFormEl.find( '.sc_sub_setup_fee' ).val( spFormData.subSetupFee );

				// Attempt to run applyCoupon in case a coupon code is in effect.
				spApp.applyCoupon( spFormEl, spFormData );
			}
		},

		// Update recurring amount & label.
		// At the end it runs the base plugin's updateTotalAmount function.
		updateRecurringAmount: function( spFormEl, spFormData ) {

			// First update the subRecurringAmount property.
			// It should equal baseAmount * itemQuantity also if no coupon is applied.
			if ( spFormData.couponCode.trim() === '' ) {

				spFormData.subRecurringAmount = spFormData.subBaseRecurringAmount * spFormData.itemQuantity;
			} else {

				// discountedAmount is already baseAmount * itemQuantity with coupon code applied.
				spFormData.subRecurringAmount = spFormData.subDiscountedRecurringAmount;
			}

			var recurringAmountEl = spFormEl.find( '.sc-recurring-total-amount' );

			// Check for recurring amount label existence.
			if ( recurringAmountEl.length > 0 ) {

				// Unformatted decimal amount
				var unformattedAmount = spApp.unformatFromStripe( spFormData.subRecurringAmount, spFormData.currency );

				// Interval text should be appended.
				recurringAmountEl.text( spApp.formatCurrency( unformattedAmount, spFormData.currency ) + this.getIntervalText( spFormEl, spFormData ) );
			}

			// Now update base plugin's totalAmount property and label.
			spFormData.baseAmount = spFormData.subBaseRecurringAmount;

			// Init add-on operand property (used for adding setup fee, subtracting if trial period, etc).
			spFormData.addOnTotalAmountOperand = 0;

			// Add setup fee to add-on operand.
			spFormData.addOnTotalAmountOperand += spFormData.subSetupFee;

			// Subtract main recurring amount from add-on operand if we're in a trial.
			// Can make this value negative.
			if ( spFormData.subTrialDays > 0 ) {
				spFormData.addOnTotalAmountOperand -= spFormData.subRecurringAmount;
			}

			spApp.updateTotalAmount( spFormEl, spFormData );
		},

		getIntervalText: function( spFormEl, spFormData ) {

			if ( spFormData.subIntervalCount === 1 ) {
				return '/' + spFormData.subInterval;

			} else if ( spFormData.subIntervalCount > 1 ) {
				return ' ' + simplePaySubscriptionsFrontendGlobals.intervalEveryText + ' ' + spFormData.subIntervalCount + ' ' + spFormData.subInterval + 's';

			} else {
				return '';
			}
		}
	};

	// Custom event fired when base plugin init is done.
	body.on( 'spBaseInitComplete', function( e ) {
		spApp.Subscriptions.init();
	} );

}( jQuery ));
