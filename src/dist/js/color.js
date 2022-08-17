jQuery(function($) {
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.pom-form-color-picker').wpColorPicker();
    }

    $(document).ajaxComplete(function() {
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.pom-form-color-picker').wpColorPicker();
        }
    });
});
