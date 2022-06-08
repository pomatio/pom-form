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
        let $repeater_elements = $wrapper.children('.repeater');
        let $is_child_repeater = $wrapper.parents('.repeater-wrapper').length > 0;

        let $value = [];

        // For each repeater element
        for (let $i = 0; $i < $repeater_elements.length; $i++) {
            let $repeater_fields = $repeater_elements[$i].querySelectorAll("input, select, checkbox, textarea");
            let $obj = {};

            // For each field inside the repeater element
            for (let $i2 = 0; $i2 < $repeater_fields.length; $i2++) {
                let $field = $repeater_fields[$i2];
                let $field_name = $field.getAttribute("name");
                let $is_child_repeater_field = $($field).parents('.repeater-wrapper').length > 1;

                /**
                 * Exclude config hidden field and
                 * inner repeater fields in main repeater.
                 */
                if ($field_name === 'config' || (!$is_child_repeater && $is_child_repeater_field)) {
                    continue;
                }

                $obj[$field_name] = $field.value.trim();
            }

            $value.push($obj);

            $wrapper.children('.repeater-value').val(JSON.stringify($value));

            /**
             * Appends the content of possible child repeaters to the parent repeater.
             */
            if ($is_child_repeater) {
                let $parent_index = $wrapper.closest('.repeater').index();
                let $child_repeater_name = $wrapper.find('.repeater-value').attr('name');
                let $parent_repeater = $wrapper.parents('.repeater-wrapper').last();
                let $parent_value = $parent_repeater.find('.repeater-value').last().val();

                $parent_value = JSON.parse($parent_value);
                $parent_value[$parent_index][$child_repeater_name] = $value;

                $parent_repeater.find('.repeater-value').last().val(JSON.stringify($parent_value));
            }
        }
    }

    /**
     * Make repeater elements sortable
     */
    if (typeof $.fn.sortable !== 'undefined') {
        $('.repeater-wrapper.sortable').sortable({
            update: function (event, ui) {
                $update_repeater($(this));
            }
        });
    }

    /**
     * Update repeater title live
     */
    $(document).on('keyup', '.repeater .use-for-title', function() {
        let $title_holder =  $(this).closest('.repeater').find('.title span').first();

        if ($(this).val()) {
            $title_holder.html(' - ' + $(this).val());
        }
        else {
            $title_holder.html('');
        }
    });

    let $append_to_title = function() {
        $('.repeater-wrapper .use-for-title').each(function(i,v) {
            let $input_value = $(this).val();
            let $title_holder =  $(this).closest('.repeater').find('.title span').first();

            if ($input_value) {
                $title_holder.html(' - ' + $input_value);
            }
        });
    }
    $append_to_title();
    $(document).ajaxComplete(function() {
        $append_to_title();
    });

});
