Stripe.setPublishableKey(stripekey);


jQuery(document).ready(function ($) {

    function showErrorMessage(message, formSelector, afterSelector) {
        var errorPanel;
        if (typeof afterSelector == "undefined") {
            errorPanel = __getMessagePanelFor(formSelector, null);
        } else {
            errorPanel = __getMessagePanelFor(formSelector, afterSelector);
        }
        errorPanel.addClass('alert alert-error').html(message);
        __scrollToMessagePanel();
    }

    function showInfoMessage(message, formSelector, afterSelector) {
        var infoPanel;
        if (typeof afterSelector == "undefined") {
            infoPanel = __getMessagePanelFor(formSelector, null);
        } else {
            infoPanel = __getMessagePanelFor(formSelector, afterSelector);
        }
        infoPanel.addClass('alert alert-success').html(message);
        __scrollToMessagePanel();
    }

    function clearMessagePanel(formSelector, afterSelector) {
        var panel = __getMessagePanelFor(formSelector, afterSelector);
        panel.removeClass('alert alert-error alert-success');
        panel.html("");
    }

    function __getMessagePanelFor(formSelector, afterSelector) {
        var panel = $('.payment-errors');
        if (panel.length == 0) {
            if (afterSelector == null) {
                panel = $('<p>', {class: 'payment-errors'}).prependTo(formSelector);
            } else {
                panel = $('<p>', {class: 'payment-errors'}).insertAfter(afterSelector);
            }
        }
        return panel;
    }

    function __scrollToMessagePanel() {
        var panel = $('.payment-errors');
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

    $("#showLoading").hide();

    $('#payment-form').submit(function (e) {
        $("#showLoading").show();

        clearMessagePanel('#payment-form', '#legend');

        var $form = $(this);

        // Disable the submit button
        $form.find('button').prop('disabled', true);

        Stripe.createToken($form, stripeResponseHandler);
        return false;
    });

    var stripeResponseHandler = function (status, response) {
        var formSelector = '#payment-form';
        var $form = $(formSelector);

        if (response.error) {
            // Show the errors
            if (response.error.code && wpfsf_L10n.hasOwnProperty(response.error.code)) {
                showErrorMessage(wpfsf_L10n[response.error.code], formSelector, '#legend');
            } else {
                showErrorMessage(response.error.message, formSelector, '#legend');
            }
            $form.find('button').prop('disabled', false);
            $("#showLoading").hide();
        } else {
            // token contains id, last4, and card type
            var token = response.id;
            $form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

            //post payment via ajax
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: $form.serialize(),
                cache: false,
                dataType: "json",
                success: function (data) {
                    $("#showLoading").hide();

                    if (data.success) {
                        //clear form fields
                        $form.find('input:text, input:password').val('');
                        //inform user of success
                        showInfoMessage(data.msg, formSelector, '#legend');
                        $form.find('button').prop('disabled', false);
                        if (data.redirect) {
                            setTimeout(function () {
                                window.location = data.redirectURL;
                            }, 1500);
                        }
                    } else {
                        // re-enable the submit button
                        $form.find('button').prop('disabled', false);
                        // show the errors on the form
                        showErrorMessage(data.msg, formSelector, '#legend');
                    }
                }
            });
        }
    };

    $('#payment-form-style').submit(function (e) {
        $("#showLoading").show();

        clearMessagePanel('#payment-form-style');

        var $form = $(this);

        // Disable the submit button
        $form.find('button').prop('disabled', true);

        Stripe.createToken($form, stripeResponseHandler2);
        return false;
    });

    var stripeResponseHandler2 = function (status, response) {
        var formSelector = '#payment-form-style';
        var $form = $(formSelector);

        if (response.error) {
            // Show the errors
            if (response.error.code && wpfsf_L10n.hasOwnProperty(response.error.code)) {
                showErrorMessage(wpfsf_L10n[response.error.code], formSelector);
            } else {
                showErrorMessage(response.error.message, formSelector);
            }
            $form.find('button').prop('disabled', false);
            $("#showLoading").hide();
        } else {
            // token contains id, last4, and card type
            var token = response.id;
            $form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

            //post payment via ajax
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: $form.serialize(),
                cache: false,
                dataType: "json",
                success: function (data) {
                    $("#showLoading").hide();

                    if (data.success) {
                        //clear form fields
                        $form.find('input:text, input:password').val('');
                        //inform user of success
                        showInfoMessage(data.msg, formSelector);
                        $form.find('button').prop('disabled', false);
                        if (data.redirect) {
                            setTimeout(function () {
                                window.location = data.redirectURL;
                            }, 1500);
                        }
                    } else {
                        // re-enable the submit button
                        $form.find('button').prop('disabled', false);
                        // show the errors on the form
                        showErrorMessage(data.msg, formSelector);
                    }
                }
            });
        }
    };

});