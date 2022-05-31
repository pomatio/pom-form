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

        let $this = $(this);

        let $wrapper = $this.closest('.repeater-wrapper');
        $wrapper.find('.repeater-value').val('');
        $this.closest('.repeater').remove();
        $update_repeater($wrapper);
    });

    /**
     * Update repeater value
     */
    $(document).on('change', '.repeater-wrapper input, .repeater-wrapper textarea, .repeater-wrapper select', function() {
        let $this = $(this);
        $update_repeater($this.closest('.repeater-wrapper'));
    });

    let $update_repeater = function($wrapper) {
        let $repeater_elements = $wrapper.find('.repeater');

        let $value = [];

        for (let $i = 0; $i < $repeater_elements.length; $i++) {
            let $repeater_fields = $repeater_elements[$i].querySelectorAll("input, select, checkbox, textarea");
            let $obj = {};

            for (let $i2 = 0; $i2 < $repeater_fields.length; $i2++) {
                let $field_name = $repeater_fields[$i2].getAttribute("name");
                $obj[$field_name] = $repeater_fields[$i2].value;
            }

            $value.push($obj);
        }

        $wrapper.find('.repeater-value').val(JSON.stringify($value));
    }

});
