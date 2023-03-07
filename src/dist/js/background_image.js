jQuery(function($) {
    // Load stored data
    $('.background-image-wrapper').each(function() {
        let $this = $(this);

        let $value = $this.find('input[type="hidden"]').val();
        if ($value) {
            $value = JSON.parse($value);

            $.each($value, function(key, fvalue) {
                let $field = $(`[name="${key}"]`);
                if ($field.is(":radio")) {
                    if (fvalue === 'left' || fvalue === 'center' || fvalue === 'right' || fvalue === 'bottom') {
                        $this.closest('.background-image-wrapper').find(`[name="${key}"][value="${fvalue}"]`).prop("checked", true);
                    }
                    else {
                        let $number = fvalue.match(/\d+/);
                        let $unit = fvalue.replace($number, '');

                        $this.closest('.background-image-wrapper').find(`[name="${key}"][value="custom"]`).prop("checked", true);
                        $this.closest('.background-image-wrapper').find(`.custom-${key}-wrapper`).show();
                        $this.closest('.background-image-wrapper').find(`[name="custom_${key}_number"]`).val($number[0]);
                        $this.closest('.background-image-wrapper').find(`[name="custom_${key}_unit"] option[value="${$unit}"]`).prop('selected', true);
                    }
                }
                else {
                    $this.closest('.background-image-wrapper').find(`[name="${key}"]`).val(fvalue);
                }
            });
        }
    });

    $(document).on('change', 'input[name="background_image"]', function() {
        let $this = $(this);
        let $value = {};

        $value.background_image = $this.val();
        $set_hidden_value($this, $value);
    });

    $(document).on('click', 'input[name="horizontal_alignment"]', function() {
        let $this = $(this);

        if ($this.val() === 'custom') {
            $this.closest('.horizontal-alignment').find('.custom-horizontal_alignment-wrapper').show();
        }
        else {
            $this.closest('.horizontal-alignment').find('.custom-horizontal_alignment-wrapper').hide();
        }
    });

    $(document).on('click', 'input[name="vertical_alignment"]', function() {
        let $this = $(this);

        if ($this.val() === 'custom') {
            $this.closest('.vertical-alignment').find('.custom-vertical_alignment-wrapper').show();
        }
        else {
            $this.closest('.vertical-alignment').find('.custom-vertical_alignment-wrapper').hide();
        }
    });

    $(document).on('change', 'select[name="background_size"]', function() {
        let $this = $(this);

        if ($this.val() === 'custom') {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').show();
        }
        else {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').hide();
        }
    });

    let $get_hidden_value = function ($element) {
        return $element.closest('.background-image-wrapper').find('input[type="hidden"]').val();
    };

    let $set_hidden_value = function ($element, $object) {
        let $saved_value = $get_hidden_value($element);
        let $data = {};

        if ($saved_value.length) {
            $data = JSON.parse($saved_value);
        }

        $.extend($data, $object);

        let $value = JSON.stringify($data);
        $element.closest('.background-image-wrapper').find('input[type="hidden"]').val($value);
    };

    // Horizontal alignment
    $(document).on('change keyup', 'input[name="horizontal_alignment"], input[name="custom_horizontal_alignment_number"], select[name="custom_horizontal_alignment_unit"]', function() {
        let $this = $(this);
        let $value = {};
        let $alignment = $this.closest('.horizontal-alignment').find('input[name="horizontal_alignment"]:checked').val();
        let $number = $this.closest('.horizontal-alignment').find('input[name="custom_horizontal_alignment_number"]').val();
        let $unit = $this.closest('.horizontal-alignment').find('select[name="custom_horizontal_alignment_unit"]').val();

        if ($alignment === 'custom') {
            $value.horizontal_alignment = `${$number}${$unit}`;
        }
        else {
            $value.horizontal_alignment = $this.val();
        }

        $set_hidden_value($this, $value);
    });

    // Vertical alignment
    $(document).on('change keyup', 'input[name="vertical_alignment"], input[name="custom_vertical_alignment_number"], select[name="custom_vertical_alignment_unit"]', function() {
        let $this = $(this);
        let $value = {};
        let $alignment = $this.closest('.vertical-alignment').find('input[name="vertical_alignment"]:checked').val();
        let $number = $this.closest('.vertical-alignment').find('input[name="custom_vertical_alignment_number"]').val();
        let $unit = $this.closest('.vertical-alignment').find('select[name="custom_vertical_alignment_unit"]').val();

        if ($alignment === 'custom') {
            $value.vertical_alignment = `${$number}${$unit}`;
        }
        else {
            $value.vertical_alignment = $this.val();
        }

        $set_hidden_value($this, $value);
    });

    $(document).on('change', 'select[name="background_position"]', function() {
        let $this = $(this);
        let $value = {};

        $value.background_position = $this.val();
        $set_hidden_value($this, $value);
    });

    $(document).on('change', 'select[name="background_attachment"]', function() {
        let $this = $(this);
        let $value = {};

        $value.background_attachment = $this.val();
        $set_hidden_value($this, $value);
    });

    $(document).on('change', 'select[name="background_size"]', function() {
        let $this = $(this);
        let $value = {};

        $value.background_size = $this.val();
        $set_hidden_value($this, $value);
    });
});
