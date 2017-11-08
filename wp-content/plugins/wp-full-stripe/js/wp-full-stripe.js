/*
 Plugin Name: WP Full Stripe
 Plugin URI: https://paymentsplugin.com
 Description: Complete Stripe payments integration for Wordpress
 Author: Mammothology
 Version: 3.11.1
 Author URI: https://paymentsplugin.com
 */

Stripe.setPublishableKey(stripekey);

jQuery.noConflict();
(function ($) {

    $(function () {
        var FORM_TYPE_PAYMENT = 'payment';
        var FORM_STYLE_DEFAULT = 'default';
        var FORM_STYLE_COMPACT = 'compact';

        function parseCurrencyAmount(amount, zeroDecimalSupport, returnSmallestCommonCurrencyUnit) {
            var theAmount;
            if (zeroDecimalSupport == true) {
                theAmount = parseInt(amount);
            } else {
                theAmount = parseFloat(amount);
            }
            if (!isNaN(theAmount)) {
                if (returnSmallestCommonCurrencyUnit) {
                    if (zeroDecimalSupport == false) {
                        theAmount = Math.round(theAmount * 100);
                    }
                }
            }
            return theAmount;
        }

        function logError(handlerName, jqXHR, textStatus, errorThrown) {
            if (window.console) {
                console.log(handlerName + '.error(): textStatus=' + textStatus);
                console.log(handlerName + '.error(): errorThrown=' + errorThrown);
                if (jqXHR) {
                    console.log(handlerName + '.error(): jqXHR.status=' + jqXHR.status);
                    console.log(handlerName + '.error(): jqXHR.responseText=' + jqXHR.responseText);
                }
            }
        }

        function logException(source, response) {
            if (window.console && response) {
                if (response.ex_msg) {
                    console.log('ERROR: source=' + source + ', message=' + response.ex_msg);
                }
            }
        }

        function showErrorMessage(message, formId, selector, action) {
            var errorPanel;
            if (typeof selector == "undefined") {
                errorPanel = __getMessagePanelFor(formId, null, null);
            } else {
                errorPanel = __getMessagePanelFor(formId, selector, action);
            }
            errorPanel.addClass('alert alert-error').html(message);
            __scrollToMessagePanel(formId);
        }

        function showInfoMessage(message, formId, selector, action) {
            var infoPanel;
            if (typeof selector == "undefined") {
                infoPanel = __getMessagePanelFor(formId, null, false);
            } else {
                infoPanel = __getMessagePanelFor(formId, selector, action);
            }
            infoPanel.addClass('alert alert-success').html(message);
            __scrollToMessagePanel(formId);
        }

        function clearMessagePanel(formId, selector, action) {
            var panel = __getMessagePanelFor(formId, selector, action);
            panel.removeClass('alert alert-error alert-success');
            panel.html("");
        }

        function __getMessagePanelFor(formId, selector, action) {
            var panel = $('.payment-errors__' + formId);
            if (panel.length == 0) {
                if (selector == null) {
                    panel = $('<p>', {class: 'payment-errors__' + formId}).prependTo('form[data-form-id=' + formId + ']');
                } else {
                    panel = $('<p>', {class: 'payment-errors__' + formId});
                    if (action == "prependTo") {
                        panel.prependTo(selector);
                    } else if (action == "insertAfter") {
                        panel.insertAfter(selector);
                    } else {
                        panel.prependTo(selector);
                    }
                }
            }
            return panel;
        }

        function __scrollToMessagePanel(formId) {
            var panel = $('.payment-errors__' + formId);
            if (panel && panel.offset() && panel.offset().top) {
                if (!__isInViewport(panel)) {
                    $('html, body').animate({
                        scrollTop: panel.offset().top - 100
                    }, 1000);
                }
            }
            if (panel) {
                panel.fadeIn(500).fadeOut(500).fadeIn(500);
            }
        }

        function __isInViewport($elem) {
            var $window = $(window);

            var docViewTop = $window.scrollTop();
            var docViewBottom = docViewTop + $window.height();

            var elemTop = $elem.offset().top;
            var elemBottom = elemTop + $elem.height();

            return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
        }

        function updateButtonTitle(formId, buttonTitle, currencySymbol, amount, zeroDecimalSupport) {
            var pattern;
            var label;
            if (currencySymbol == null || amount == null) {
                label = buttonTitle;
            } else {
                var buttonTitleParams = [];
                buttonTitleParams.push(buttonTitle);
                buttonTitleParams.push(currencySymbol);
                buttonTitleParams.push(amount);
                if (zeroDecimalSupport) {
                    pattern = "%s %s%s";
                } else {
                    pattern = "%s %s%0.2f";
                }
                label = vsprintf(pattern, buttonTitleParams);
            }
            $('#payment-form-submit__' + formId).html(label);
        }

        function applyCoupon(currency, amount, coupon, zeroDecimalSupport) {
            var discount = 0;
            if (coupon) {
                if (coupon.percent_off != null) {
                    var percentOff = parseInt(coupon.percent_off) / 100;
                    discount = amount * percentOff;
                } else if (coupon.currency == currency && coupon.amount_off != null) {
                    if (zeroDecimalSupport == true) {
                        discount = parseInt(coupon.amount_off);
                    } else {
                        discount = parseInt(coupon.amount_off) / 100;
                    }
                }
            }
            var total = amount - discount;
            if (zeroDecimalSupport != true) {
                total = parseFloat(total.toFixed(2));
            }
            return total;
        }

        function createResponseHandlerByFormId(formId, selector, action) {
            return function (status, response) {

                var $form = $('form[data-form-id=' + formId + ']');

                if (response.error) {

                    var code = response.error.code;
                    var param = response.error.param;
                    if (code) {
                        if (code == 'invalid_number' && param != '' && wpfs_L10n.hasOwnProperty(code + '_' + param)) {
                            showErrorMessage(wpfs_L10n[code + '_' + param], formId, selector, action);
                        } else if (wpfs_L10n.hasOwnProperty(code)) {
                            showErrorMessage(wpfs_L10n[code], formId, selector, action);
                        }
                    } else {
                        showErrorMessage(response.error.message, formId, selector, action);
                    }
                    $form.find('button').prop('disabled', false);
                    $('#show-loading__' + formId).hide();
                } else {
                    // token contains id, last4, and card type
                    var token = response.id;
                    $form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

                    // post payment via ajax
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: $form.serialize(),
                        cache: false,
                        dataType: "json",
                        success: function (data) {
                            if (data.success) {
                                //clear form fields
                                $form.find('input:text, input:password').val('');
                                $('#fullstripe-custom-amount__' + formId).prop('selectedIndex', 0);
                                $('#fullstripe-plan__' + formId).prop('selectedIndex', 0);
                                $('#fullstripe-address-country__' + formId).prop('selectedIndex', 0);
                                //inform user of success
                                showInfoMessage(data.msg, formId, selector, action);
                                $form.find('button').prop('disabled', false);
                                if (data.redirect) {
                                    setTimeout(function () {
                                        window.location = data.redirectURL;
                                    }, 1500);
                                }
                            } else {
                                showErrorMessage(data.msg, formId, selector, action);
                                logException('Stripe form ' + formId, data);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            showErrorMessage(wpfs_L10n.internal_error, formId, selector, action);
                        },
                        complete: function () {
                            $form.find('button').prop('disabled', false);
                            $("#show-loading__" + formId).hide();
                        }
                    });
                }
            };
        }

        function createCheckoutHandler(formId, selector, action) {
            return StripeCheckout.configure({
                key: stripekey,
                token: function (token, args) {

                    var $form = $('form[data-form-id=' + formId + ']');
                    var showBillingAddress = $form.data('show-billing-address');

                    $form.append("<input type='hidden' name='stripeToken' value='" + token.id + "' />");
                    $form.append("<input type='hidden' name='stripeEmail' value='" + token.email + "' />");

                    //if billing address
                    if (showBillingAddress == 1) {
                        $form.append("<input type='hidden' name='billing_name' value='" + args.billing_name + "' />");
                        $form.append("<input type='hidden' name='billing_address_country' value='" + args.billing_address_country + "' />");
                        $form.append("<input type='hidden' name='billing_address_zip' value='" + args.billing_address_zip + "' />");
                        $form.append("<input type='hidden' name='billing_address_state' value='" + args.billing_address_state + "' />");
                        $form.append("<input type='hidden' name='billing_address_line1' value='" + args.billing_address_line1 + "' />");
                        $form.append("<input type='hidden' name='billing_address_city' value='" + args.billing_address_city + "' />");
                    }

                    $form.data('closed-by', 'token_callback');

                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: $form.serialize(),
                        cache: false,
                        dataType: "json",
                        success: function (data) {
                            if (data.success) {
                                showInfoMessage(data.msg, formId, selector, action);
                                if (data.redirect) {
                                    setTimeout(function () {
                                        window.location = data.redirectURL;
                                    }, 1500);
                                }
                            } else {
                                showErrorMessage(data.msg, formId, selector, action);
                                logException('handler__' + formId, data);
                            }
                        },
                        error: function () {
                            showErrorMessage(wpfs_L10n.internal_error, formId, selector, action);
                        },
                        complete: function () {
                            $('#show-loading__' + formId).hide();
                            $('.loading-animation').hide();
                        }

                    });
                },
                closed: function () {
                    var closedBy = $('form[data-form-id=' + formId + ']').data('closed-by');
                    if ('token_callback' != closedBy) {
                        $('#show-loading__' + formId).hide();
                        $('.loading-animation').hide();
                    }
                }
            });
        }

        $(".loading-animation").hide();

        $('.checkout-form').submit(function (e) {
            e.preventDefault();
            var formId = $(this).data('form-id');
            var errorPanelParentSelector = 'form[data-form-id=' + formId + '] fieldset';
            var errorPanelInsertAction = 'prependTo';

            // tnagy validate custom fields
            var customInputRequired = $(this).data('custom-input-required');
            if (customInputRequired == 1) {
                var customInputFieldWithMissingValue = null;
                var customInputValues = $("input[name='fullstripe_custom_input']", this);
                if (customInputValues.length == 0) {
                    customInputValues = $("input[name='fullstripe_custom_input[]']", this);
                }
                if (customInputValues) {
                    customInputValues.each(function () {
                        if (customInputFieldWithMissingValue == null && $(this).val().length == 0) {
                            customInputFieldWithMissingValue = this;
                        }
                    });
                }
            }
            if (customInputFieldWithMissingValue != null) {
                var labelOfMissingCustomInputValue = $(customInputFieldWithMissingValue).closest('div.control-group').find('label.control-label').html();
                var message = vsprintf(wpfs_L10n.mandatory_field_is_empty, [labelOfMissingCustomInputValue]);
                showErrorMessage(message, formId, errorPanelParentSelector, errorPanelInsertAction);
                return false;
            }

            var amountType = $(this).data('amount-type');
            var currency = $(this).data('currency');
            var zeroDecimalSupport = $(this).data('zero-decimal-support') == 1;
            var amount;
            var returnSmallestCommonCurrencyUnit = true;
            if (amountType == 'specified_amount') {
                amount = $(this).data('amount');
            } else if (amountType == 'list_of_amounts') {
                var allowCustomAmountValue = $(this).data('allow-list-of-amounts-custom');
                amount = $("select[name='fullstripe_custom_amount'] option:selected", this).val();
                if (allowCustomAmountValue == 1 && amount == 'other') {
                    amount = parseCurrencyAmount($('input[name=fullstripe_list_of_amounts_custom_amount]', this).val(), zeroDecimalSupport, returnSmallestCommonCurrencyUnit);
                    if (isNaN(amount) || amount <= 0) {
                        showErrorMessage(wpfs_L10n.custom_payment_amount_value_is_invalid, formId, errorPanelParentSelector, errorPanelInsertAction);
                        return false;
                    }
                } else {
                    amount = parseCurrencyAmount($("select[name='fullstripe_custom_amount'] option:selected", this).val(), zeroDecimalSupport, returnSmallestCommonCurrencyUnit);
                    var amountIndex = $("select[name='fullstripe_custom_amount'] option:selected", this).data('amount-index');
                    $(this).append($('<input type="hidden" name="fullstripe_amount_index">').val(amountIndex));
                }
            } else if (amountType == 'custom_amount') {
                amount = parseCurrencyAmount($("input[name='fullstripe_custom_amount']", this).val(), zeroDecimalSupport, returnSmallestCommonCurrencyUnit);
                if (isNaN(amount) || amount <= 0) {
                    showErrorMessage(wpfs_L10n.custom_payment_amount_value_is_invalid, formId, errorPanelParentSelector, errorPanelInsertAction);
                    return false;
                }
            }

            var companyName = $(this).data('company-name');
            var productDesc = $(this).data('product-description');
            var buttonTitle = $(this).data('button-title');
            var showBillingAddress = $(this).data('show-billing-address');
            var showRememberMe = $(this).data('show-remember-me');
            var image = $(this).data('image');
            var useBitcoin = $(this).data('use-bitcoin');
            var useAlipay = $(this).data('use-alipay');

            clearMessagePanel(formId, errorPanelParentSelector, errorPanelInsertAction);
            $('#show-loading__' + formId).show();

            var handler = createCheckoutHandler(formId, errorPanelParentSelector, errorPanelInsertAction);
            handler.open({
                name: companyName,
                description: productDesc,
                amount: amount,
                panelLabel: buttonTitle,
                billingAddress: (showBillingAddress == 1),
                allowRememberMe: (showRememberMe == 1),
                image: (typeof image == "undefined") ? '' : image,
                currency: currency,
                bitcoin: (useBitcoin == 1),
                alipay: (useAlipay == 1)
            });

            return false;
        });

        $('.checkout-subscription-form').submit(function (e) {
            e.preventDefault();

            var formId = $(this).data('form-id');
            var errorPanelParentSelector = 'form[data-form-id=' + formId + '] fieldset';
            var errorPanelInsertAction = 'prependTo';

            // tnagy validate custom fields
            var customInputRequired = $(this).data('custom-input-required');
            if (customInputRequired == 1) {
                var customInputFieldWithMissingValue = null;
                var customInputValues = $("input[name='fullstripe_custom_input']", this);
                if (customInputValues.length == 0) {
                    customInputValues = $("input[name='fullstripe_custom_input[]']", this);
                }
                if (customInputValues) {
                    customInputValues.each(function () {
                        if (customInputFieldWithMissingValue == null && $(this).val().length == 0) {
                            customInputFieldWithMissingValue = this;
                        }
                    });
                }
            }
            if (customInputFieldWithMissingValue != null) {
                var labelOfMissingCustomInputValue = $(customInputFieldWithMissingValue).closest('div.control-group').find('label.control-label').html();
                var message = vsprintf(wpfs_L10n.mandatory_field_is_empty, [labelOfMissingCustomInputValue]);
                showErrorMessage(message, formId, errorPanelParentSelector, errorPanelInsertAction);
                return false;
            }

            var companyName = $(this).data('company-name');
            var productDescription = $(this).data('product-description.');
            var buttonTitle = $(this).data('button-title');
            var showBillingAddress = $(this).data('show-billing-address');
            var showRememberMe = $(this).data('show-remember-me');
            var image = $(this).data('image');
            var selectedPlan = $("#fullstripe-plan__" + formId + ' option:selected');
            var setupFee = selectedPlan.data('setup-fee-in-smallest-common-currency');
            var interval = selectedPlan.data('interval');
            var intervalCount = parseInt(selectedPlan.data('interval-count'));
            var amount = selectedPlan.data('amount-in-smallest-common-currency');
            var currency = selectedPlan.data("currency");
            var currencySymbol = selectedPlan.data("currency-symbol");
            var zeroDecimalSupport = selectedPlan.data("zero-decimal-support") === "true";
            var amountWithCouponApplied = applyCoupon(currency, amount, coupon, zeroDecimalSupport);

            $(document).data('liveForm', $(this));

            clearMessagePanel(formId, errorPanelParentSelector, errorPanelInsertAction);
            $('#show-loading__' + formId).show();

            var handler = createCheckoutHandler(formId, errorPanelParentSelector, errorPanelInsertAction);
            var options = {
                name: companyName,
                description: (typeof productDescription == "undefined") ? '' : productDescription,
                panelLabel: buttonTitle,
                billingAddress: (showBillingAddress == 1),
                allowRememberMe: (showRememberMe == 1),
                image: (typeof image == "undefined") ? '' : image,
                amount: (amountWithCouponApplied + setupFee),
                currency: currency
            };

            handler.open(options);

            return false;
        });

        $('.fullstripe-custom-amount').change(function () {
            var formId = $(this).data('form-id');
            var showAmount = $(this).data('show-amount');
            var buttonTitle = $(this).data('button-title');
            var currencySymbol = $(this).data('currency-symbol');
            var zeroDecimalSupport = $(this).data('zero-decimal-support') === "true";
            var val = $(this).val();
            if (val == 'other') {
                $('#fullstripe-list-of-amounts-custom-amount__' + formId).val('').show().focus();
                updateButtonTitle(formId, buttonTitle);
            } else {
                $('#fullstripe-list-of-amounts-custom-amount__' + formId).val('').hide();
                var returnSmallestCommonCurrencyUnit = false;
                var amount = parseCurrencyAmount(val, zeroDecimalSupport, returnSmallestCommonCurrencyUnit);
                if (showAmount == '1' && !isNaN(amount)) {
                    updateButtonTitle(formId, buttonTitle, currencySymbol, amount, zeroDecimalSupport);
                }
            }
        });

        $('.fullstripe-list-of-amounts-custom-amount').change(function () {
            var formId = $(this).data('form-id');
            if ($('#fullstripe-list-of-amounts-custom-amount__' + formId).is(':visible')) {
                var showAmount = $(this).data('show-amount');
                var buttonTitle = $(this).data('button-title');
                var currencySymbol = $(this).data('currency-symbol');
                var zeroDecimalSupport = $(this).data('zero-decimal-support') === "true";
                var val = $(this).val();
                var parseForCharge = false;
                var amount = parseCurrencyAmount(val, zeroDecimalSupport, parseForCharge);
                if (showAmount == '1' && !isNaN(amount)) {
                    updateButtonTitle(formId, buttonTitle, currencySymbol, amount, zeroDecimalSupport);
                }
            }
        });

        $('.payment-form, .subscription-form').submit(function (e) {
            var formId = $(this).data('form-id');

            var errorPanelParentSelector = '#legend__' + formId;
            var errorPanelInsertAction = 'insertAfter';

            clearMessagePanel(formId, errorPanelParentSelector, errorPanelInsertAction);
            $("#show-loading__" + formId).show();

            var $form = $(this);

            var amountIndex = $form.find('select[name=fullstripe_custom_amount] option:selected').data('amount-index');
            $form.append($('<input type="hidden" name="fullstripe_amount_index">').val(amountIndex));

            // Disable the submit button
            $form.find('button').prop('disabled', true);

            var responseHandler = createResponseHandlerByFormId(formId, errorPanelParentSelector, errorPanelInsertAction);
            Stripe.createToken($form, responseHandler);
            return false;
        });

        $('.payment-form-compact').submit(function (e) {
            var formId = $(this).data('form-id');

            var errorPanelParentSelector = 'form[data-form-id=' + formId + ']';
            var errorPanelInsertAction = 'prependTo';

            clearMessagePanel(formId, errorPanelParentSelector, errorPanelInsertAction);
            $("#show-loading__" + formId).show();

            var $form = $(this);

            var amountIndex = $form.find('select[name=fullstripe_custom_amount] option:selected').data('amount-index');
            $form.append($('<input type="hidden" name="fullstripe_amount_index">').val(amountIndex));

            // Disable the submit button
            $form.find('button').prop('disabled', true);

            var responseHandler = createResponseHandlerByFormId(formId, errorPanelParentSelector, errorPanelInsertAction);
            Stripe.createToken($form, responseHandler);
            return false;
        });

        var coupon = false;
        $('.fullstripe-plan').change(function () {
            var formId = $(this).data('form-id');
            var plan = $("#fullstripe-plan__" + formId).val();
            var planSelector = "option[value='" + plan + "']";
            var option = $("#fullstripe-plan__" + formId).find($('<div/>').html(planSelector).text());
            var interval = option.attr('data-interval');
            var intervalCount = parseInt(option.attr("data-interval-count"));
            var amount = option.attr('data-amount');
            var setupFee = option.attr('data-setup-fee');
            var currency = option.attr("data-currency");
            var currencySymbol = option.attr("data-currency-symbol");
            var zeroDecimalSupport = option.attr("data-zero-decimal-support") === "true";

            var planDetailsPattern = wpfs_L10n.plan_details_with_singular_interval;
            var planDetailsParams = [];
            planDetailsParams.push(currencySymbol);
            planDetailsParams.push(amount);

            if (intervalCount > 1) {
                planDetailsPattern = wpfs_L10n.plan_details_with_plural_interval;
                planDetailsParams.push(intervalCount);
                planDetailsParams.push(interval);
            } else {
                planDetailsParams.push(interval);
            }

            if (coupon != false) {
                planDetailsPattern = intervalCount > 1 ? wpfs_L10n.plan_details_with_plural_interval_with_coupon : wpfs_L10n.plan_details_with_singular_interval_with_coupon;
                var total = applyCoupon(currency, amount, coupon, zeroDecimalSupport);
                planDetailsParams.push(total);
                $(this).parents('form:first').append($('<input type="hidden" name="amount_with_coupon_applied">').val(total));
            }

            if (setupFee > 0) {
                planDetailsPattern = intervalCount > 1 ? (coupon != false ? wpfs_L10n.plan_details_with_plural_interval_with_coupon_with_setupfee : wpfs_L10n.plan_details_with_plural_interval_with_setupfee) : (coupon != false ? wpfs_L10n.plan_details_with_singular_interval_with_coupon_with_setupfee : wpfs_L10n.plan_details_with_singular_interval_with_setupfee);
                planDetailsParams.push(currencySymbol);
                planDetailsParams.push(setupFee);
            }
            var planDetailsMessage = vsprintf(planDetailsPattern, planDetailsParams);
            var planDetails = $('#fullstripe-plan-details__' + formId);
            if (planDetails.length == 0) {
                planDetails = $('<p>', {id: 'fullstripe-plan-details__' + formId}).appendTo($('#payment-form-submit__' + formId).parent());
            }
            planDetails.text(planDetailsMessage);

        }).change();

        $('.payment-form-coupon').click(function (e) {
            e.preventDefault();

            var formId = $(this).data('form-id');
            var $form = $('form[data-form-id=' + formId + ']');

            var errorPanelParentSelector;
            var errorPanelInsertAction;
            if ($form.hasClass('subscription-form')) {
                errorPanelParentSelector = '#legend__' + formId;
                errorPanelInsertAction = 'insertAfter';
            } else if ($form.hasClass('checkout-subscription-form')) {
                errorPanelParentSelector = 'form[data-form-id=' + formId + '] fieldset';
                errorPanelInsertAction = 'prependTo';
            }

            var cc = $('#fullstripe-coupon-input__' + formId).val();
            if (cc.length > 0) {
                $(this).prop('disabled', true);
                clearMessagePanel(formId, errorPanelParentSelector, errorPanelInsertAction);
                $('#show-loading-coupon__' + formId).show();

                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {action: 'wp_full_stripe_check_coupon', code: cc},
                    cache: false,
                    dataType: "json",
                    success: function (data) {
                        if (data.valid) {
                            coupon = data.coupon;
                            $('#fullstripe-plan__' + formId).change();
                            showInfoMessage(data.msg, formId, errorPanelParentSelector, errorPanelInsertAction);
                        } else {
                            showErrorMessage(data.msg, formId, errorPanelParentSelector, errorPanelInsertAction);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        showErrorMessage(wpfs_L10n.internal_error, formId, errorPanelParentSelector, errorPanelInsertAction);
                    },
                    complete: function () {
                        $('#fullstripe-check-coupon-code__' + formId).prop('disabled', false);
                        $('#show-loading-coupon__' + formId).hide();
                    }
                });
            }
            return false;
        });
    });
})(jQuery);
