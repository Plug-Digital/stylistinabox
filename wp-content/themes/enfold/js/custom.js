(function($)
{	
    $(document).ready(function() {
    	$('.select-profile').on('click', function(e) {
    		var selection = $(this).attr('data-select');
    		// $('#get-started input[name="style_profile[]"').filter('input[value=' + selection + ']').parent().trigger('click');

    		$('#get-started input[name="style_profile[]"]:checked').prop('checked', false);
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').prop('checked', true);
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().siblings().removeClass('active');
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().siblings().find('.um-field-radio-state i').removeClass('um-icon-android-radio-button-on').addClass('um-icon-android-radio-button-off');

    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().addClass('active');
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().find('.um-field-radio-state i').removeClass('um-icon-android-radio-button-off').addClass('um-icon-android-radio-button-on');

    	});

    	$('#avia-menu .menu-item a').on('click', function(e) {
    		var selection = $(this).find('.avia-menu-text').text();
    		selection = selection.indexOf('Women') >= 0 ? 'Women' : 'Men';
    		// $('#get-started input[name="style_profile[]"').filter('input[value=' + selection + ']').parent().trigger('click');

    		$('#get-started input[name="style_profile[]"]:checked').prop('checked', false);
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').prop('checked', true);
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().siblings().removeClass('active');
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().siblings().find('.um-field-radio-state i').removeClass('um-icon-android-radio-button-on').addClass('um-icon-android-radio-button-off');

    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().addClass('active');
    		$('#get-started input[name="style_profile[]"]').filter('input[value=' + selection + ']').parent().find('.um-field-radio-state i').removeClass('um-icon-android-radio-button-off').addClass('um-icon-android-radio-button-on');

    	});

        $('.fullstripe-form-input[data-stripe="number"]').keyup(function () {
            var v = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            var matches = v.match(/\d{1,16}/g);
            var match = matches && matches[0] || '';
            var cardnum = "";
            if (match.length > 1 && (match.slice(0, 2) == "34" || match.slice(0, 2) == "37")) {
                //amex
                if (match.length > 15) {
                    match = match.slice(0, 15);
                }
                for (i = 0, len = match.length; i < len; i++) {
                    if (i == 4 || i == 10) {
                        cardnum += " ";
                    }
                    cardnum += match[i];
                }
            } else {
                var parts = [];
                for (i = 0, len = match.length; i < len; i += 4) {
                    parts.push(match.substring(i, i + 4));
                }
                if (parts.length) {
                    cardnum = parts.join(' ');
                } else {
                    cardnum = match;
                }
            }
            $(this).val(cardnum);
        });

        $('.fullstripe-form-input[data-stripe="exp-month"]').keyup(function () {
            var v = $(this).val();
            var matches = v.match(/\d{1,2}/g);
            var expMon = matches && matches[0] || '';
            if(parseInt(expMon) > 12)
                expMon = 12;
            $(this).val(expMon);
        });

        $('.fullstripe-form-input[data-stripe="exp-year"]').keyup(function () {
            var v = $(this).val();
            var matches = v.match(/\d{1,4}/g);
            var expYear = matches && matches[0] || '';
            
            $(this).val(expYear);
        });

        $('.user-info-first-name input').val(user_info.first_name);
        $('.user-info-last-name input').val(user_info.last_name);
        $('.user-info-full-name input').val(user_info.first_name + ' ' + user_info.last_name);
    });
})(jQuery);