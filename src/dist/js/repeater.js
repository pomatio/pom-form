/**
 * @var pomatio_framework_repeater Object containing the translation strings.
 */

jQuery(function($) {
    /**
     * Toggle the repeater
     */
    $(document).on('click', '.repeater .title', function() {
        $(this).closest('.repeater').toggleClass('closed');

        $('.CodeMirror').each(function(i, el) {
            el.CodeMirror.refresh();
        });
    });

    let $update_repeater = function($wrapper) {
        let $repeater_elements = $wrapper.children('.repeater');
        let $is_child_repeater = $wrapper.parents('.repeater-wrapper').length > 0;

        let $value = {};

        // For each repeater element
        for (let $i = 0; $i < $repeater_elements.length; $i++) {
            let $is_default = $repeater_elements[$i].classList.contains('default');
            let $repeater_type = $is_default ? 'default' : 'new';

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

                if ($field_name === 'repeater_identifier' || $field_name === 'default_values') {
                    $obj[$field_name] = $field.value.trim();
                }
                else {
                    $obj[$field_name] = {
                        'value': $field.value.trim(),
                        'type': $field.getAttribute('data-type') || ''
                    };
                }
            }

            /**
             * If the type exists add it, if it doesn't exist, create it first.
             */
            if ($value.hasOwnProperty($repeater_type)) {
                $value[$repeater_type].push($obj);
            }
            else {
                $value[$repeater_type] = [];
                $value[$repeater_type].push($obj);
            }

            $wrapper.children('.repeater-value').val(JSON.stringify($value));

            /**
             * Appends the content of possible child repeaters to the parent repeater.
             */
            if ($is_child_repeater) {
                const $parent_index = $wrapper.closest('.repeater').index();
                const $child_repeater_name = $wrapper.find('.repeater-value').attr('name');
                const $parent_repeater = $wrapper.parents('.repeater-wrapper').last();
                let $parent_value = $parent_repeater.find('.repeater-value').last().val();

                $parent_value = JSON.parse($parent_value);
                $parent_value[$parent_index][$child_repeater_name] = $value;

                $parent_repeater.find('.repeater-value').last().val(JSON.stringify($parent_value));
            }
        }
    }

    /**
     * Add new repeater element.
     */
    $(document).on('click', '.add-new-repeater-item', function(e) {
        e.preventDefault();

        let $this = $(this);

        let $wrapper = $this.closest('.repeater-wrapper');
        let $limit = parseInt($wrapper.attr('data-limit'));
        let $item_count = $wrapper.find('> .repeater').length;

        if ($item_count >= $limit) {
            if (!$('.repeater-limit-warning').length) {
                $this.after('<span class="repeater-limit-warning">' + pomatio_framework_repeater.limit + '</span>');
            }

            setTimeout(function() {
                $('.repeater-limit-warning').remove();
            }, 2000);

            return;
        }

        let $spinner = $this.siblings('.repeater-spinner');

        $spinner.show();

        let $config = $this.siblings('[name="config"]').val();

        $.ajax({
            url: pomatio_framework_repeater.ajax_url,
            type: 'POST',
            data: {
                action: 'pomatio_framework_get_repeater_item_html',
                config: $config,
                items: $item_count
            },
            success: function($response) {
                $this.before($response);
                $spinner.hide();
            }
        });
    });

    /**
     * Delete repeater element.
     * If it is a parent repeater, we directly remove it from the value using its index.
     * If it's a child repeater, we update the value of the child repeater and then call the function to update the parent repeater.
     * TODO: Delete file from server on delete when repeater has code fields.
     */
    $(document).on('click', '.repeater-wrapper .delete', function(e) {
        e.preventDefault();

        let $execute = confirm(pomatio_framework_repeater.delete_repeater);
        if (!$execute) {
            return;
        }

        const $this = $(this);
        const $repeater_type = $this.closest('.repeater').hasClass('default') ? 'default' : 'new';
        const $wrapper = $this.closest('.repeater-wrapper');

        const $is_child_repeater = $wrapper.parents('.repeater-wrapper').length > 0;

        if (!$is_child_repeater) {
            const $repeater_index = $this.closest('.repeater').index();
            let $repeater_value = $wrapper.find('.repeater-value').last().val();

            $repeater_value = JSON.parse($repeater_value);
            $repeater_value[$repeater_type].splice($repeater_index, 1);

            $wrapper.find('.repeater-value').last().val(JSON.stringify($repeater_value));
            $this.closest('.repeater').remove();

            $update_repeater($wrapper);

            return;
        }

        $wrapper.find('.repeater-value').val('');
        $this.closest('.repeater').remove();

        $update_repeater($wrapper);
    });

    /**
     * Update repeater value.
     */
    $(document).on('change', '.repeater-wrapper input, .repeater-wrapper textarea, .repeater-wrapper select', function() {
        const $this = $(this);
        $update_repeater($this.closest('.repeater-wrapper'));
    });

    /**
     * Make repeater elements sortable.
     */
    if (typeof $.fn.sortable !== 'undefined') {
        $('.repeater-wrapper.sortable').sortable({
            update: function (event, ui) {
                $update_repeater($(this));
            }
        });
    }

    /**
     * Update repeater title live.
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

    /**
     * Add repeater title on page load.
     */
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

    /**
     * Restore repeater inputs to default value.
     */
    $(document).on('click', '.restore-default', function(e) {
        e.preventDefault();

        let $this = $(this);
        let $wrapper = $this.closest('.repeater-wrapper');
        let $repeater_defaults = $this.closest('.repeater').find('input[name="default_values"').val();
        if ($repeater_defaults) {
            let $decoded = JSON.parse($repeater_defaults);
            Object.keys($decoded).map(function ($key) {
                let $value = $decoded[$key]['value'];

                $this.closest('.repeater').find('input, select, checkbox, textarea').each(function () {
                    let $field_name = this.getAttribute("name");
                    if ($field_name === $key) {
                        this.value = $value;
                    }
                });
            });

            $update_repeater($wrapper);
        }
    });

    /**
     * Restore all repeater defaults.
     */
    $(document).on('click', '.restore-repeater-defaults', function(e) {
        e.preventDefault();

        let $execute = confirm(pomatio_framework_repeater.restore_msg);
        if (!$execute) {
            return;
        }

        let $this = $(this);
        let $wrapper = $this.closest('.repeater-wrapper');

        let $spinner = $this.closest('.repeater-wrapper').find('.repeater-spinner');
        $spinner.show();

        $('.repeater.default').remove();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pomatio_framework_restore_repeater_defaults',
                defaults: $this.attr('data-defaults'),
                fields: $this.attr('data-fields'),
                title: $this.attr('data-title'),
            },
            success: function($response) {
                $this.closest('.repeater-wrapper').prepend($response);
                $spinner.hide();

                $update_repeater($wrapper);
            }
        });
    });

    /**
     * Duplicates a repeater generating a new identifier for it.
     */
    $(document).on('click', '.clone-repeater', function(e) {
        e.preventDefault();

        let $this = $(this);
        let $repeater = $this.closest('.repeater');
        let $wrapper = $this.closest('.repeater-wrapper');
        let $clone = $repeater.clone();

        /**
         * Generate repeater new identifier.
         */
        $clone.find('input[name="repeater_identifier"]').val($generate_random_string(10, false));

        /**
         * If the repeater has code fields replace their id's in order to render Codemirror with unique IDs.
         */
        let $code_editor = $clone.find('.pomatio-framework-code-editor-html, .pomatio-framework-code-editor-js, .pomatio-framework-code-editor-css');
        for (let $i = 0; $i < $code_editor.length; $i++) {
            $code_editor[$i].id = $generate_random_string(10, false);

            // Delete existing code mirror instance
            $($code_editor).next().remove();

            wp.codeEditor.initialize($code_editor[$i], settings.codeMirrorSettings);
        }

        /**
         * A cloned repeater can never be a default, so we change its key.
         */
        if ($clone.hasClass('default')) {
            $clone = $clone.removeClass('default').addClass('new');
            $clone.find('input[name="default_values"]').remove();
        }

        $repeater.after($clone);

        $update_repeater($wrapper);
    });

    /**
     * Generate a random string.
     *
     * The same helper exists but in PHP.
     * @see Pomatio_Framework_Helper::generate_random_string()
     *
     * @param $length
     * @param $numbers
     * @returns {string}
     */
    let $generate_random_string = function($length = 10, $numbers = true) {
        const $number_string = $numbers ? '0123456789' : '';
        const $characters = $number_string + 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        let $randomString = '';
        for (let $i = 0; $i < $length; $i++) {
            $randomString += $characters[Math.floor(Math.random() * $characters.length)];
        }

        return $randomString;
    }

});
