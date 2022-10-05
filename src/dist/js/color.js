jQuery(function($) {
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.pomatio-framework-color-picker').wpColorPicker();
    }

    $(document).ajaxComplete(function() {
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.pomatio-framework-color-picker').wpColorPicker();
        }
    });
});
