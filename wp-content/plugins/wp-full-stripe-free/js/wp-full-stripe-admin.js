Stripe.setPublishableKey(stripekey);

jQuery(document).ready(function ($) {

    var regexPattern_AN_DASH_U = /^[a-zA-Z0-9-_]+$/;

    var $loading = $(".showLoading");
    var $update = $("#updateDiv");
    $loading.hide();
    $update.hide();

    function resetForm($form) {
        $form.find('input:text, input:password, input:file, select, textarea').val('');
        $form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
    }

    function validField(field, fieldName, errorField) {
        var valid = true;
        if (field.val() === "") {
            showError(fieldName + " must contain a value");
            valid = false;
        }
        return valid;
    }

    function validFieldByRegex(field, regexPattern, errorMessage) {
        var valid = true;
        if (!regexPattern.test(field.val())) {
            showError(errorMessage);
            valid = false;
        }
        return valid;
    }

    function validFieldByLength(field, len, errorMessage) {
        var valid = true;
        if (field.val().length > len) {
            showError(errorMessage);
            valid = false;
        }
        return valid;
    }

    function validFieldWithMsg(field, msg) {
        var valid = true;
        if (field.val() === "") {
            showError(msg);
            valid = false;
        }
        return valid;
    }

    function showError(message) {
        showMessage('error', 'updated', message);
    }

    function showUpdate(message) {
        showMessage('updated', 'error', message);
    }

    function showMessage(addClass, removeClass, message) {
        $update.removeClass(removeClass);
        $update.addClass(addClass);
        $update.html("<p>" + message + "</p>");
        $update.show();
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

    function clearUpdateAndError() {
        $update.html("");
        $update.removeClass('error');
        $update.removeClass('update');
        $update.hide();
    }

    //for uploading images using WordPress media library
    var custom_uploader;

    function uploadImage(inputID) {
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function () {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $(inputID).val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();
    }

    // called on form submit when we know includeCustomFields = 1
    function processCustomFields(form) {
        var valid = true;
        var count = $('#customInputNumberSelect').val();
        var customValues = '';
        for (var i = 1; i <= count; i++) {
            // first validate the field
            var field = '#form_custom_input_label_' + i;
            var fieldName = 'Custom Input Label ' + i;
            valid = validField($(field), fieldName, $update);
            valid = valid && validFieldByLength($(field), 40, 'You can enter up to 40 characters for ' + fieldName);
            if (!valid) return false;
            // save the value, stripping all single & double quotes
            customValues += $(field).val().replace(/['"]+/g, '');
            if (i < count)
                customValues += '{{';
        }

        // now append to the form
        form.append('<input type="hidden" name="customInputs" value="' + customValues + '"/>');

        return valid;
    }

    function validate_redirect() {
        var valid_redirect;
        if ($('#do_redirect_yes').prop('checked')) {
            if ($('#form_redirect_to_page_or_post').prop('checked')) {
                valid_redirect = validFieldWithMsg($('#form_redirect_page_or_post_id'), 'Select page or post to redirect to');
            } else if ($('#form_redirect_to_url').prop('checked')) {
                valid_redirect = validFieldWithMsg($('#form_redirect_url'), 'Enter an URL to redirect to', $update);
            } else {
                showError('You must check at least one redirect type');
                valid_redirect = false;
            }
        } else {
            valid_redirect = true;
        }
        return valid_redirect;
    }

    function do_ajax_post(ajaxUrl, form, successMessage, doRedirect) {
        $loading.show();
        // Disable the submit button
        form.find('button').prop('disabled', true);

        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: form.serialize(),
            cache: false,
            dataType: "json",
            success: function (data) {
                $loading.hide();
                document.body.scrollTop = document.documentElement.scrollTop = 0;

                if (data.success) {
                    showUpdate(successMessage);
                    form.find('button').prop('disabled', false);
                    resetForm(form);

                    if (doRedirect) {
                        setTimeout(function () {
                            window.location = data.redirectURL;
                        }, 1000);
                    }
                } else {
                    // re-enable the submit button
                    form.find('button').prop('disabled', false);
                    // show the errors on the form
                    if (data.msg) {
                        showError(data.msg);
                    }
                    if (data.validation_result) {
                        var elementWithError = null;
                        for (var f in data.validation_result) {
                            if (data.validation_result.hasOwnProperty(f)) {
                                $('input[name=' + f + ']').after('<div class="error"><p>' + data.validation_result[f] + '</p></div>');
                                elementWithError = f;
                            }
                        }
                        if (elementWithError) {
                            var $el = $('input[name=' + elementWithError + ']');
                            if ($el && $el.offset() && $el.offset().top);
                            $('html, body').animate({
                                scrollTop: $el.offset().top
                            }, 2000);
                        }
                    }
                }
            }
        });
    }

    function enable_combobox() {
        $('#createPaymentFormSection .page_or_post-combobox-input').prop('disabled', false);
        $('#createPaymentFormSection .page_or_post-combobox-toggle').button("option", "disabled", false);
        $('#edit-payment-form .page_or_post-combobox-input').prop('disabled', false);
        $('#edit-payment-form .page_or_post-combobox-toggle').button("option", "disabled", false);
    }

    function disable_combobox() {
        $('#createPaymentFormSection .page_or_post-combobox-input').prop('disabled', true);
        $('#createPaymentFormSection .page_or_post-combobox-toggle').button("option", "disabled", true);
        $('#edit-payment-form .page_or_post-combobox-input').prop('disabled', true);
        $('#edit-payment-form .page_or_post-combobox-toggle').button("option", "disabled", true);
    }

    function init_page_or_post_redirect() {
        $('#form_redirect_to_url').prop('checked', false);
        $('#form_redirect_to_page_or_post').prop('checked', true);
        $('#form_redirect_to_page_or_post').prop('disabled', false);
        $('#form_redirect_to_url').prop('disabled', false);
        enable_combobox();
        $('#form_redirect_page_or_post_id').prop('disabled', false);
        $('#form_redirect_url').prop('disabled', false);
    }

    $('#create-payment-form').submit(function (e) {
        clearUpdateAndError();

        var customAmount = $('input[name=form_custom]:checked', '#create-payment-form').val();
        var includeCustom = $('input[name=form_include_custom_input]:checked', '#create-payment-form').val();

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validFieldByRegex($('#form_name'), regexPattern_AN_DASH_U, 'Form Name should contain only alphanumerical characters, dashes, underscores, and whitespaces.');
        valid = valid && validField($('#form_title'), 'Form Title', $update);
        if (customAmount == 'specified_amount') {
            valid = valid && validField($('#form_amount'), 'Amount', $update);
        }
        valid = valid && validate_redirect();
        if (includeCustom == 1) {
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.
        }

        if (valid) {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Payment form created.", true);
        }

        return false;
    });

    $('#edit-payment-form').submit(function (e) {
        clearUpdateAndError();

        var customAmount = $('input[name=form_custom]:checked', '#edit-payment-form').val();
        var includeCustom = $('input[name=form_include_custom_input]:checked', '#edit-payment-form').val();

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validFieldByRegex($('#form_name'), regexPattern_AN_DASH_U, 'Form Name should contain only alphanumerical characters, dashes, underscores, and whitespaces.');
        valid = valid && validField($('#form_title'), 'Form Title', $update);
        if (customAmount == 'specified_amount') {
            valid = valid && validField($('#form_amount'), 'Amount', $update);
        }
        valid = valid && validate_redirect();
        if (includeCustom == 1) {
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.
        }

        if (valid) {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Payment form updated.", true);
        }

        return false;
    });

    //The forms delete button
    $('button.delete').click(function () {
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
        var to_confirm = $(this).attr('data-confirm');
        if (to_confirm == null) {
            to_confirm = 'true';
        }
        var confirm_message = 'Are you sure you want to delete the record?';
        var update_message = 'Record deleted.';
        var action = '';
        if (type === 'paymentForm') {
            action = 'wp_full_stripe_delete_payment_form';
            confirm_message = 'Are you sure you want to delete this payment form?';
            update_message = 'Payment form deleted.';
        } else if (type === 'subscriptionForm') {
            action = 'wp_full_stripe_delete_subscription_form';
            confirm_message = 'Are you sure you want to delete this subscription form?';
            update_message = 'Subscription form deleted.';
        } else if (type === 'subscriber') {
            action = 'wp_full_stripe_delete_subscriber';
        } else if (type === 'payment') {
            action = 'wp_full_stripe_delete_payment';
        } else if (type === 'subscriptionPlan') {
            action = 'wp_full_stripe_delete_subscription_plan';
            confirm_message = 'Are you sure you want to delete this subscription plan?';
            update_message = 'Subscription plan deleted.';
        }

        var row = $(this).parents('tr:first');

        $loading.show();

        var confirmed = true;
        if (to_confirm === 'true' || to_confirm === 'yes') {
            confirmed = confirm(confirm_message);
        }
        if (confirmed == true) {
            $.ajax({
                type: "POST",
                url: admin_ajaxurl,
                data: {id: id, action: action},
                cache: false,
                dataType: "json",
                success: function (data) {
                    $loading.hide();

                    if (data.success) {
                        $(row).remove();
                        showUpdate(update_message);
                    }
                }
            });
        }

        return false;

    });

    //payment type toggle
    $('#set_custom_amount').click(function () {
        $('#form_amount').prop('disabled', true);
    });
    $('#set_specific_amount').click(function () {
        $('#form_amount').prop('disabled', false);
    });

    $('#form_redirect_to_page_or_post').change(function () {
        if ($(this).prop('checked')) {
            enable_combobox();
            $('#redirect_to_page_or_post_section').show();
            $('#redirect_to_url_section').hide();
        } else {
            disable_combobox();
            $('#redirect_to_page_or_post_section').hide();
        }
    });
    $('#form_redirect_to_url').change(function () {
        if ($(this).prop('checked')) {
            $('#redirect_to_page_or_post_section').hide();
            $('#redirect_to_url_section').show();
        } else {
            $('#redirect_to_url_section').hide();
        }
    });
    $('#do_redirect_no').click(function () {
        $('#form_redirect_page_or_post_id').val($('#form_redirect_page_or_post_id').prop('defaultSelected'));
        $('#form_redirect_url').val('');

        $('#form_redirect_to_page_or_post').prop('disabled', true);
        $('#form_redirect_to_url').prop('disabled', true);
        disable_combobox();
        $('#form_redirect_page_or_post_id').prop('disabled', true);
        $('#form_redirect_url').prop('disabled', true);
    });

    $('#do_redirect_yes').click(function () {
        $('#redirect_to_url_section').hide();
        init_page_or_post_redirect();
        $('#redirect_to_page_or_post_section').show();
    });

    // custom inputs
    $('#noinclude_custom_input').click(function () {
        $('#form_custom_input_label').prop('disabled', true);
    });
    $('#include_custom_input').click(function () {
        $('#form_custom_input_label').prop('disabled', false);
    });

    // page or post combobox
    $.widget("custom.page_or_post_combobox", {
        _create: function () {
            this.wrapper = $("<span>")
                .addClass("page_or_post-combobox")
                .insertAfter(this.element);

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },

        _createAutocomplete: function () {
            var selected = this.element.children(":selected"),
                value = selected.val() ? selected.text() : "";

            this.input = $("<input>")
                .appendTo(this.wrapper)
                .val(value)
                .prop("disabled", true)
                .attr("title", "")
                .attr("placeholder", "Select from the list or start typing")
                .addClass("ui-widget")
                .addClass("ui-widget-content")
                .addClass("ui-corner-left")
                .addClass("page_or_post-combobox-input")
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy(this, "_source")
                })
                .tooltip({
                    tooltipClass: "ui-state-highlight"
                });
            this._on(this.input, {
                autocompleteselect: function (event, ui) {
                    ui.item.option.selected = true;
                    this._trigger("select", event, {
                        item: ui.item.option
                    });
                },

                autocompletechange: "_removeIfInvalid"
            });
        },

        _createShowAllButton: function () {
            var input = this.input,
                wasOpen = false;

            $("<a>")
                .attr("tabIndex", -1)
                .attr("title", "Show all page and post")
                .tooltip()
                .appendTo(this.wrapper)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false,
                    disabled: true
                })
                .removeClass("ui-corner-all")
                .addClass("page_or_post-combobox-toggle ui-corner-right")
                .mousedown(function () {
                    wasOpen = input.autocomplete("widget").is(":visible");
                })
                .click(function () {
                    input.focus();

                    // Close if already visible
                    if (wasOpen) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                });
        },

        _source: function (request, response) {
            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
            response(this.element.children("option").map(function () {
                var text = $(this).text();
                if (this.value && ( !request.term || matcher.test(text) ))
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }));
        },

        _removeIfInvalid: function (event, ui) {

            // Selected an item, nothing to do
            if (ui.item) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children("option").each(function () {
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if (valid) {
                return;
            }

            // Remove invalid value
            this.input
                .val("")
                .attr("title", value + " didn't match any item")
                .tooltip("open");
            this.element.val("");
            this._delay(function () {
                this.input.tooltip("close").attr("title", "");
            }, 2500);
            this.input.autocomplete("instance").term = "";
        },

        _destroy: function () {
            this.wrapper.remove();
            this.element.show();
        }
    });

    $("#form_redirect_page_or_post_id").page_or_post_combobox();

    // currency combobox
    $.widget("custom.currency_combobox", {
        _create: function () {
            this.wrapper = $("<span>")
                .addClass("currency-combobox")
                .insertAfter(this.element);

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },

        _createAutocomplete: function () {
            var selected = this.element.children(":selected"),
                value = selected.val() ? selected.text() : "";

            this.input = $("<input>")
                .appendTo(this.wrapper)
                .val(value)
                .attr("title", "")
                .attr("placeholder", "Select from the list or start typing")
                .addClass("ui-widget")
                .addClass("ui-widget-content")
                .addClass("ui-corner-left")
                .addClass("currency-combobox-input")
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy(this, "_source")
                })
                .tooltip({
                    tooltipClass: "ui-state-highlight"
                });
            this._on(this.input, {
                autocompleteselect: function (event, ui) {
                    ui.item.option.selected = true;
                    this._trigger("select", event, {
                        item: ui.item.option
                    });
                },

                autocompletechange: "_removeIfInvalid"
            });
        },

        _createShowAllButton: function () {
            var input = this.input,
                wasOpen = false;

            $("<a>")
                .attr("tabIndex", -1)
                .attr("title", "Show all currencies")
                .tooltip()
                .appendTo(this.wrapper)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false,
                    disabled: false
                })
                .removeClass("ui-corner-all")
                .addClass("currency-combobox-toggle ui-corner-right")
                .mousedown(function () {
                    wasOpen = input.autocomplete("widget").is(":visible");
                })
                .click(function () {
                    input.focus();

                    // Close if already visible
                    if (wasOpen) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                });
        },

        _source: function (request, response) {
            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
            response(this.element.children("option").map(function () {
                var text = $(this).text();
                if (this.value && ( !request.term || matcher.test(text) ))
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }));
        },

        _removeIfInvalid: function (event, ui) {

            // Selected an item, nothing to do
            if (ui.item) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children("option").each(function () {
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if (valid) {
                return;
            }

            // Remove invalid value
            this.input
                .val("")
                .attr("title", value + " didn't match any item")
                .tooltip("open");
            this.element.val("");
            this._delay(function () {
                this.input.tooltip("close").attr("title", "");
            }, 2500);
            this.input.autocomplete("instance").term = "";
        },

        _destroy: function () {
            this.wrapper.remove();
            this.element.show();
        }
    });

    $("#currency").currency_combobox();

    $('#settings-form').submit(function (e) {
        $(".showLoading").show();
        $(".tips").removeClass('alert alert-error');
        $(".tips").html("");

        var $form = $(this);

        // Disable the submit button
        $form.find('button').prop('disabled', true);

        var valid = true;

        if (valid) {
            $.ajax({
                type: "POST",
                url: admin_ajaxurl,
                data: $form.serialize(),
                cache: false,
                dataType: "json",
                success: function (data) {
                    $(".showLoading").hide();
                    document.body.scrollTop = document.documentElement.scrollTop = 0;

                    if (data.success) {
                        $("#updateMessage").text("Settings updated");
                        $("#updateDiv").addClass('updated').show();
                        $form.find('button').prop('disabled', false);
                    }
                    else {
                        // re-enable the submit button
                        $form.find('button').prop('disabled', false);
                        // show the errors on the form
                        $(".tips").addClass('alert alert-error');
                        $(".tips").html(data.msg);
                        $(".tips").fadeIn(500).fadeOut(500).fadeIn(500);
                    }
                }
            });

            return false;
        }

    });

});