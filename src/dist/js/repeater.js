jQuery(function($) {

    /**
     * Toggle the repeater
     */
    $(document).on('click', '.repeater .title', function() {
        $(this).closest('.repeater').toggleClass('closed');
    });

    /**
     * Add new repeater element
     */
    $(document).on('click', '.add-new-repeater-item', function(e) {
        e.preventDefault();

        let $this = $(this);
        let $spinner = $this.siblings('.repeater-spinner');

        $spinner.show();

        let $config = $this.siblings('[name="config"]').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pom_form_get_repeater_item_html',
                config: $config
            },
            success: function($response) {
                $this.before($response);
                $spinner.hide();
            }
        });
    });

    /**
     * Delete repeater element
     */
    $(document).on('click', '.repeater-wrapper .delete', function(e) {
        e.preventDefault();

        $(this).closest('.repeater').remove();
    });

});
