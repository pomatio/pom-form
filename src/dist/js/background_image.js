jQuery(function($) {
    // Load stored data
    $('.background-image-wrapper').each(function() {
        let $this = $(this);

        let $value = $this.find('input[type="hidden"]').val();
        if ($value) {
            $value = JSON.parse($value);

            $.each($value, function(key, fvalue) {
                let $field = $(`[name="${key}"]`);
                if ($field.is(":radio") && (key === 'horizontal_alignment' || key === 'vertical_alignment')) {
                    if (fvalue === 'left' || fvalue === 'center' || fvalue === 'right' || fvalue === 'bottom' || fvalue === 'top') {
                        $this.find(`[name="${key}"][value="${fvalue}"]`).prop("checked", true);
                    }
                    else {
                        let $number = fvalue.match(/\d+/);
                        let $unit = fvalue.replace($number, '');

                        $this.find(`[name="${key}"][value="custom"]`).prop("checked", true);
                        $this.find(`.custom-${key}-wrapper`).show();
                        $this.find(`[name="custom_${key}_number"]`).val($number[0]);
                        $this.find(`[name="custom_${key}_unit"] option[value="${$unit}"]`).prop('selected', true);
                    }
                }
                else if ($field.is(":radio") && key === 'background_size') {
                    if (fvalue !== 'auto' && fvalue !== 'cover' && fvalue !== 'contain') {
                        $this.find(`[name="${key}"][value="custom"]`).prop("checked", true);
                        $this.find(`.custom-background-size-wrapper`).show();

                        let $values = fvalue.split(' ');

                        let $width_number = $values[0].match(/\d+/);
                        let $width_unit = $values[0].replace($width_number, '');
                        $this.find(`[name="custom_background_size_width_number"]`).val($width_number);
                        $this.find(`[name="custom_background_size_width_unit"] option[value="${$width_unit}"]`).prop('selected', true);

                        let $height_number = $values[1].match(/\d+/);
                        let $height_unit = $values[1].replace($width_number, '');
                        $this.find(`[name="custom_background_size_height_number"]`).val($height_number);
                        $this.find(`[name="custom_background_size_height_unit"] option[value="${$height_unit}"]`).prop('selected', true);

                        $this.find('.custom-background-size-wrapper').show();
                    }
                    else {
                        $this.find(`[name="${key}"][value="${fvalue}"]`).prop("checked", true);
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

    $(document).on('click', 'input[name="background_size"]', function() {
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

        if ($alignment === 'custom') {
            let $number = $this.closest('.horizontal-alignment').find('input[name="custom_horizontal_alignment_number"]').val();
            let $unit = $this.closest('.horizontal-alignment').find('select[name="custom_horizontal_alignment_unit"]').val();

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

        if ($alignment === 'custom') {
            let $number = $this.closest('.vertical-alignment').find('input[name="custom_vertical_alignment_number"]').val();
            let $unit = $this.closest('.vertical-alignment').find('select[name="custom_vertical_alignment_unit"]').val();

            $value.vertical_alignment = `${$number}${$unit}`;
        }
        else {
            $value.vertical_alignment = $this.val();
        }

        $set_hidden_value($this, $value);
    });

    $(document).on('click', '.restore-radio-icon', function() {
        let $this = $(this);
        let $horizontal_alignment = $this.closest('.horizontal-alignment').find('input[name="horizontal_alignment"]:checked').val();
        let $vertical_alignment = $this.closest('.vertical-alignment').find('input[name="vertical_alignment"]:checked').val();
        let $background_size = $this.closest('.background-size-wrapper').find('input[name="background_size"]:checked').val();

        if ($horizontal_alignment === 'custom') {
            $this.closest('.horizontal-alignment').find('.custom-horizontal_alignment-wrapper').show();
        }
        else {
            $this.closest('.horizontal-alignment').find('.custom-horizontal_alignment-wrapper').hide();
        }

        if ($vertical_alignment === 'custom') {
            $this.closest('.vertical-alignment').find('.custom-vertical_alignment-wrapper').show();
        }
        else {
            $this.closest('.vertical-alignment').find('.custom-vertical_alignment-wrapper').hide();
        }

        if ($background_size === 'custom') {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').show();
        }
        else {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').hide();
        }
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

    $(document).on('change keyup', 'input[name="background_size"], input[name="custom_background_size_width_number"], select[name="custom_background_size_width_unit"], input[name="custom_background_size_height_number"], select[name="custom_background_size_height_unit"]', function() {
        let $this = $(this);
        let $value = {};
        let $size = $this.closest('.background-size-wrapper').find('select[name="background_size"]').val();

        if ($size === 'custom') {
            let $width_number = $this.closest('.background-size-wrapper').find('input[name="custom_background_size_width_number"]').val();
            let $width_unit = $this.closest('.background-size-wrapper').find('select[name="custom_background_size_width_unit"]').val();
            let $height_number = $this.closest('.background-size-wrapper').find('input[name="custom_background_size_height_number"]').val();
            let $height_unit = $this.closest('.background-size-wrapper').find('select[name="custom_background_size_height_unit"]').val();

            $value.background_size = `${$width_number}${$width_unit} ${$height_number}${$height_unit}`;
        }
        else {
            $value.background_size = $this.val();
        }

        $set_hidden_value($this, $value);
    });
});
