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

    	})
    });
})(jQuery);