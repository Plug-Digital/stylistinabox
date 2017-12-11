/**
 *  Tab navigation for shortcode generator
 */
(function ($) {
    'use strict';

    $(document).ready(function(){

        $('div.wplmb-nav a').click(function(){
            var tab_id = $(this).attr('data-tab');

            $('.wplmb-nav a').removeClass('nav-tab-active');
            $('.wplmb-tab-content').removeClass('nav-tab-active');

            $(this).addClass('nav-tab-active');
            $("#"+tab_id).addClass('nav-tab-active');
        })

    });

    // Initializing WP Color Picker
    $('.wpl-color-picker').each(function(){
        $(this).wpColorPicker();
    });

})(jQuery);