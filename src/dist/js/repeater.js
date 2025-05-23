/**
 * @var pomatio_framework_repeater Object containing the translation strings.
 */

jQuery(function($) {
  function handleFieldVisibility($field) {
    if ($field.getAttribute('data-dependencies')) {
      let json = $field.getAttribute('data-dependencies');
      json = json.replaceAll('\'', '"');

      let dependencies = JSON.parse(json);
      let $repeaterWrapper = $($field).closest('.repeater');
      let groupConditionsMet = false;

      for (let group of dependencies) {
        let allConditionsMet = true;

        for (let condition of group) {
          const $dependentField = $repeaterWrapper.find(`[name="${condition.field}"]`);
          const fieldValue = $dependentField.val();

          // If any condition fails, mark as false
          if (!condition.values.includes(fieldValue)) {
            allConditionsMet = false;
            break;
          }
        }

        // If all conditions in the group are met, set groupConditionsMet to true
        if (allConditionsMet) {
          groupConditionsMet = true;
          break;
        }
      }

      if (groupConditionsMet) {
        $($field).closest('.form-group').show();
      }
      else {
        $($field).closest('.form-group').hide();
      }
    }
  }

  /**
   * Function to initialize the visibility handling for all fields
   */
  function initializeFieldVisibility() {
    $('.repeater').each(function() {
      let $repeater_fields = $(this).find('input, select, textarea');
      $repeater_fields.each(function() {
        handleFieldVisibility(this);
      });
    });
  }

  /**
   * Manage dependent fields on page load.
   */
  initializeFieldVisibility();

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

      let $repeater_fields = $repeater_elements[$i].querySelectorAll('input, select, textarea');
      let $obj = {};

      let $font_input_val = {};

      // For each field inside the repeater element
      for (let $i2 = 0; $i2 < $repeater_fields.length; $i2++) {
        let $field = $repeater_fields[$i2];
        let $field_name = $field.getAttribute('name');
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
          let $field_value = $field.value.trim();
          if ($field.getAttribute('data-type') === 'checkbox' || $field.getAttribute('data-type') === 'toggle') {
            $field_value = $($field).is(':checked') ? 'yes' : 'no';
          }

          if ($field.getAttribute('data-type') === 'select' && $($field).prop('multiple')) {
            $field_value = $($field).val().join(',');
          }

          if ($field.getAttribute('data-type') === 'font_picker') {
            let $font_type = $field_name.match(/\[(.*)\]/)[1];
            $field_name = $field_name.split('[')[0];

            if (!$font_input_val.hasOwnProperty($font_type)) {
              $font_input_val[$font_type] = $field_value;
            }

            $field_value = $font_input_val;
          }

          $obj[$field_name] = {
            'value': $field_value,
            'type': $field.getAttribute('data-type') || ''
          };
        }

        /**
         * Manage dependent fields.
         */
        handleFieldVisibility($field);
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
        const $parent_repeater_type = $parent_repeater.find('.repeater').hasClass('new') ? 'new' : 'default';
        let $parent_value = $parent_repeater.find('.repeater-value').last().val();

        $parent_value = JSON.parse($parent_value);

        if (!$parent_value.hasOwnProperty($parent_repeater_type)) {
          $parent_value[$parent_repeater_type] = [];
        }

        if (!$parent_value[$parent_repeater_type].hasOwnProperty($parent_index)) {
          $parent_value[$parent_repeater_type][$parent_index] = [];
        }

        if (!$parent_value[$parent_repeater_type][$parent_index].hasOwnProperty($child_repeater_name)) {
          $parent_value[$parent_repeater_type][$parent_index][$child_repeater_name] = [];
        }

        $parent_value[$parent_repeater_type][$parent_index][$child_repeater_name] = {
          'value': $value,
          'type': 'repeater'
        };

        $parent_repeater.find('.repeater-value').last().val(JSON.stringify($parent_value));
      }
    }
  };

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

    // Load custom fields as Select2 or color picker.
    $(document).ajaxComplete(function() {
      if (typeof $.fn.select2 !== 'undefined') {
        $('.pomatio-framework-select.multiple').select2();
      }

      if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.pomatio-framework-color-picker').wpColorPicker();
      }

      initializeFieldVisibility();
    });
  });

  /**
   * Delete repeater element.
   *
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

  // Fix for Icon Picker fields
  $(document).on('input', '.repeater-wrapper input[data-type="icon_picker"]', function() {
    const $this = $(this);
    $update_repeater($this.closest('.repeater-wrapper'));
  });

  /**
   * Make WordPress Color Picker repeater compatible.
   */
  if (typeof $.fn.wpColorPicker !== 'undefined') {
    $('.pomatio-framework-color-picker').wpColorPicker({
      change: function(event, ui) {
        let $color = ui.color.toString();
        let $this = $(event.target);
        $this.val($color);
        $update_repeater($this.closest('.repeater-wrapper'));
      }
    });

    $(document).ajaxComplete(function() {
      $('.pomatio-framework-color-picker').wpColorPicker({
        change: function(event, ui) {
          let $color = ui.color.toString();
          let $this = $(event.target);
          $this.val($color);
          $update_repeater($this.closest('.repeater-wrapper'));
        }
      });
    });
  }

  /**
   * Make repeater elements sortable.
   */
  if (typeof $.fn.sortable !== 'undefined') {
    $('.repeater-wrapper.sortable').sortable({
      update: function(event, ui) {
        $update_repeater($(this));
      }
    });
  }

  /**
   * Update repeater title live.
   */
  $(document).on('keyup', '.repeater .use-for-title', function() {
    let $title_holder = $(this).closest('.repeater').find('.title span').first();

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
    $('.repeater-wrapper .use-for-title').each(function(i, v) {
      let $input_value = $(this).val();
      let $title_holder = $(this).closest('.repeater').find('.title span').first();

      if ($input_value) {
        $title_holder.html(' - ' + $input_value);
      }
    });
  };
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
      Object.keys($decoded).map(function($key) {
        let $value = $decoded[$key].value;

        $this.closest('.repeater').find('input, select, checkbox, textarea').each(function() {
          let $field_name = this.getAttribute('name');
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

    if (!confirm(pomatio_framework_repeater.restore_msg)) {
      return;
    }

    let $this = $(this);
    let $wrapper = $this.closest('.repeater-wrapper');

    let $spinner = $this.closest('.repeater-wrapper').find('.repeater-spinner');
    $spinner.show();

    $wrapper.find('.repeater.default').remove();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'pomatio_framework_restore_repeater_defaults',
        defaults: $this.attr('data-defaults'),
        fields: $this.attr('data-fields'),
        title: $this.attr('data-title')
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
  };

});
