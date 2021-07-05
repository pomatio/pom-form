jQuery(function($) {
    let $slider = $('.range');
    let $range = $('.range .slider');
    let $value = $('.range .value');

    $slider.each(function() {
        $value.each(function () {
            let value = $(this).prev().attr('value');
            let suffix = ($(this).prev().attr('suffix')) ? $(this).prev().attr('suffix') : '';
            $(this).html(value + suffix);
        });

        $range.on('input', function() {
            let suffix = ($(this).attr('suffix')) ? $(this).attr('suffix') : '';
            $(this).next($value).html(this.value + suffix);
        });
    });
});
