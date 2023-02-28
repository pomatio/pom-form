jQuery(function($) {
    $(document).on('click', 'input[name="horizontal_alignment"]', function() {
        if ($('input[name="horizontal_alignment"][value="custom"]').is(':checked')) {
            $('.custom-horizontal-wrapper').show();
        }
        else {
            $('.custom-horizontal-wrapper').hide();
        }
    });

    $(document).on('click', 'input[name="vertical_alignment"]', function() {
        if ($('input[name="vertical_alignment"][value="custom"]').is(':checked')) {
            $('.custom-vertical-wrapper').show();
        }
        else {
            $('.custom-vertical-wrapper').hide();
        }
    });
});
