$(document).ready(function() {
	$('.select-profile').on('click', function() {
		var selection = $(this).attr('data-select');
		$('#get-started input[name="style_profile[]"').filter('input[value=' + selection + ']').parent().trigger('click');
	})
});