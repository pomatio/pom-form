jQuery(function($) {

    // Store the icon picker button that has been clicked
    let $clicked_button;

    $('#pom-form-icons-modal').dialog({
        title: '',
        dialogClass: 'wp-dialog pom-form-icons-modal',
        autoOpen: false,
        draggable: false,
        minHeight: '95%',
        width: '95%',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "top",
            at: "top+25",
            of: window
        },
        open: function() {
            $('.ui-widget-overlay').on('click', function() {
                $('#pom-form-icons-modal').dialog('close');
            });
        },
        create: function() {
            $('.pom-form-icons-modal .ui-dialog-titlebar').css('display', 'none');
        }
    });

    $(document).on('click', '.open-icon-picker-modal', function(e) {
        e.preventDefault();
        $clicked_button = $(this);
        $('#pom-form-icons-modal').dialog('open');
    });

    $(document).on('click', '.close-icon-picker-modal', function(e) {
        e.preventDefault();
        $('#pom-form-icons-modal').dialog('close');
    });

    $(document).on('click', '#pom-form-icons-modal .media-menu-item', function() {
        let $this = $(this);

        $this.closest('#pom-form-icons-modal').find('.media-frame-content').empty().append('Loading...');

        $('#pom-form-icons-modal .media-menu-item').removeClass('active').attr('aria-selected', '');
        $this.addClass('active').attr('aria-selected', 'true');

        let $library = $this.attr('data-slug');
        let $label = $this.attr('data-label');
        $this.closest('#pom-form-icons-modal').find('.media-frame-title').empty().append('<h1>' + $label + '</h1>');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pom_form_get_icon_library_icons',
                library: $library,
            },
            success: function ($response) {
                $this.closest('#pom-form-icons-modal').find('.media-frame-content').empty().append($response);
            }
        });
    });

    $(document).on('click', '#pom-form-icons-modal li.attachment', function(e) {
        $('#pom-form-icons-modal li.attachment').removeClass('selected');
        $(this).addClass('selected');
        $('#pom-form-icons-modal .pom-form-icon-select-button').removeClass('disabled').prop('disabled', false);
    });

    $(document).on('click', '#pom-form-icons-modal .pom-form-icon-select-button', function(e) {
        e.preventDefault();

        let $icon_url = $('#pom-form-icons-modal .attachment.selected img').attr('src');
        $clicked_button.closest('.icon-picker-wrapper').find('input[type="hidden"]').val($icon_url);

        $clicked_button.closest('.icon-picker-wrapper').find('.icon-wrapper').empty().append('<img src="' + $icon_url + '">');

        $('#pom-form-icons-modal').dialog('close');
    });

});
