jQuery(function($) {
  const nameSelector = function(name, element = '') {
    return `${element}[name="${name}"], ${element}[name="${name.replace(/_/g, '-')}"]`;
  };

  const selectors = {
    backgroundImage: nameSelector('background-image', 'input'),
    horizontalAlignment: nameSelector('horizontal_alignment', 'input'),
    verticalAlignment: nameSelector('vertical_alignment', 'input'),
    customHorizontalNumber: nameSelector('custom_horizontal_alignment_number', 'input'),
    customHorizontalUnit: nameSelector('custom_horizontal_alignment_unit', 'select'),
    customVerticalNumber: nameSelector('custom_vertical_alignment_number', 'input'),
    customVerticalUnit: nameSelector('custom_vertical_alignment_unit', 'select'),
    backgroundRepeat: nameSelector('background-repeat', 'select'),
    backgroundAttachment: nameSelector('background-attachment', 'select'),
    backgroundSize: nameSelector('background-size', 'input'),
    customBackgroundWidthNumber: nameSelector('custom_background_size_width_number', 'input'),
    customBackgroundWidthUnit: nameSelector('custom_background_size_width_unit', 'select'),
    customBackgroundHeightNumber: nameSelector('custom_background_size_height_number', 'input'),
    customBackgroundHeightUnit: nameSelector('custom_background_size_height_unit', 'select'),
  };

  const parseJson = function(value) {
    try {
      const parsed = JSON.parse(value);
      return $.isPlainObject(parsed) ? parsed : {};
    } catch (e) {
      return {};
    }
  };

  const getHiddenValue = function($wrapper) {
    const rawValue = $wrapper.find('input[type="hidden"]').val();
    if (!rawValue || rawValue === '[]') {
      return {};
    }

    return parseJson(rawValue);
  };

  const getAlignmentValue = function($wrapper, axis) {
    const isHorizontal = axis === 'horizontal';
    const radioSelector = isHorizontal ? selectors.horizontalAlignment : selectors.verticalAlignment;
    const numberSelector = isHorizontal ? selectors.customHorizontalNumber : selectors.customVerticalNumber;
    const unitSelector = isHorizontal ? selectors.customHorizontalUnit : selectors.customVerticalUnit;

    const selected = $wrapper.find(radioSelector).filter(':checked').val();

    if (selected === 'custom') {
      const number = $wrapper.find(numberSelector).val();
      const unit = $wrapper.find(unitSelector).val();

      if (`${number}`.length) {
        return `${number}${unit}`;
      }
    }

    return selected || '';
  };

  const getBackgroundSizeValue = function($wrapper) {
    const selected = $wrapper.find(selectors.backgroundSize).filter(':checked').val();

    if (selected === 'custom') {
      const widthNumber = $wrapper.find(selectors.customBackgroundWidthNumber).val();
      const widthUnit = $wrapper.find(selectors.customBackgroundWidthUnit).val();
      const heightNumber = $wrapper.find(selectors.customBackgroundHeightNumber).val();
      const heightUnit = $wrapper.find(selectors.customBackgroundHeightUnit).val();

      if (`${widthNumber}`.length && `${heightNumber}`.length) {
        return `${widthNumber}${widthUnit} ${heightNumber}${heightUnit}`;
      }
    }

    return selected || '';
  };

  const setHiddenValue = function($element, object = {}) {
    const $wrapper = $element.closest('.background-image-wrapper');
    const savedValue = getHiddenValue($wrapper);
    let data = $.isPlainObject(savedValue) ? savedValue : {};

    data = $.extend(data, object);

    const horizontal = getAlignmentValue($wrapper, 'horizontal') || data.horizontal_alignment || 'center';
    const vertical = getAlignmentValue($wrapper, 'vertical') || data.vertical_alignment || 'center';
    const backgroundSize = object['background-size'] !== undefined ? object['background-size'] : (getBackgroundSizeValue($wrapper) || data['background-size']);

    data.horizontal_alignment = horizontal;
    data.vertical_alignment = vertical;

    if (backgroundSize) {
      data['background-size'] = backgroundSize;
    }

    data['background-position'] = `${horizontal} ${vertical}`;

    $wrapper.find('input[type="hidden"]').val(JSON.stringify(data));
  };

  const toggleCustomWrappers = function($wrapper) {
    const horizontalSelected = $wrapper.find(selectors.horizontalAlignment).filter(':checked').val();
    const verticalSelected = $wrapper.find(selectors.verticalAlignment).filter(':checked').val();
    const backgroundSizeSelected = $wrapper.find(selectors.backgroundSize).filter(':checked').val();

    $wrapper.find('.custom-horizontal_alignment-wrapper')[horizontalSelected === 'custom' ? 'show' : 'hide']();
    $wrapper.find('.custom-vertical_alignment-wrapper')[verticalSelected === 'custom' ? 'show' : 'hide']();
    $wrapper.find('.custom-background-size-wrapper')[backgroundSizeSelected === 'custom' ? 'show' : 'hide']();
  };

  // Load stored data
  $('.background-image-wrapper').each(function() {
    const $wrapper = $(this);

    const storedValue = $wrapper.find('input[type="hidden"]').val();
    if (storedValue) {
      const parsedValue = parseJson(storedValue);

      $.each(parsedValue, function(key, fvalue) {
        const selector = nameSelector(key);
        const $field = $wrapper.find(selector);

        if (!$field.length) {
          return;
        }

        if ($field.is(':radio') && (key === 'horizontal_alignment' || key === 'vertical_alignment')) {
          if (fvalue === 'left' || fvalue === 'center' || fvalue === 'right' || fvalue === 'bottom' || fvalue === 'top') {
            $field.filter(`[value="${fvalue}"]`).prop('checked', true);
          }
          else {
            const numberMatch = `${fvalue}`.match(/[\d.]+/);
            const number = Array.isArray(numberMatch) ? numberMatch[0] : '';
            const unit = `${fvalue}`.replace(number, '');

            $field.filter('[value="custom"]').prop('checked', true);
            $wrapper.find(`.custom-${key}-wrapper`).show();
            $wrapper.find(nameSelector(`custom_${key}_number`)).val(number);
            $wrapper.find(nameSelector(`custom_${key}_unit`)).val(unit);
          }
        }
        else if ($field.is(':radio') && key === 'background-size') {
          if (fvalue !== 'auto' && fvalue !== 'cover' && fvalue !== 'contain') {
            $field.filter('[value="custom"]').prop('checked', true);
            $wrapper.find('.custom-background-size-wrapper').show();

            const values = `${fvalue}`.split(' ');
            if (values.length === 2) {
              const widthNumber = values[0].match(/[\d.]+/);
              const widthUnit = `${values[0]}`.replace(widthNumber, '');

              $wrapper.find(selectors.customBackgroundWidthNumber).val(Array.isArray(widthNumber) ? widthNumber[0] : '');
              $wrapper.find(selectors.customBackgroundWidthUnit).val(widthUnit);

              const heightNumber = values[1].match(/[\d.]+/);
              const heightUnit = `${values[1]}`.replace(heightNumber, '');
              $wrapper.find(selectors.customBackgroundHeightNumber).val(Array.isArray(heightNumber) ? heightNumber[0] : '');
              $wrapper.find(selectors.customBackgroundHeightUnit).val(heightUnit);

              $wrapper.find('.custom-background-size-wrapper').show();
            }

          }
          else {
            $field.filter(`[value="${fvalue}"]`).prop('checked', true);
          }
        }
        else {
          $field.val(fvalue);
        }
      });
    }

    toggleCustomWrappers($wrapper);
    setHiddenValue($wrapper.find('input[type="hidden"]'));
  });

  $(document).on('change', selectors.backgroundImage, function() {
    const $this = $(this);
    const value = {};

    value['background-image'] = $this.val();
    setHiddenValue($this, value);
  });

  $(document).on('click', selectors.horizontalAlignment, function() {
    const $wrapper = $(this).closest('.background-image-wrapper');

    toggleCustomWrappers($wrapper);
  });

  $(document).on('click', selectors.verticalAlignment, function() {
    const $wrapper = $(this).closest('.background-image-wrapper');

    toggleCustomWrappers($wrapper);
  });

  $(document).on('click', selectors.backgroundSize, function() {
    const $wrapper = $(this).closest('.background-image-wrapper');

    toggleCustomWrappers($wrapper);
  });

  // Horizontal alignment
  $(document).on('change keyup', `${selectors.horizontalAlignment}, ${selectors.customHorizontalNumber}, ${selectors.customHorizontalUnit}`, function() {
    const $this = $(this);
    const $wrapper = $this.closest('.background-image-wrapper');
    const value = {};

    value.horizontal_alignment = getAlignmentValue($wrapper, 'horizontal');

    toggleCustomWrappers($wrapper);
    setHiddenValue($this, value);
  });

  // Vertical alignment
  $(document).on('change keyup', `${selectors.verticalAlignment}, ${selectors.customVerticalNumber}, ${selectors.customVerticalUnit}`, function() {
    const $this = $(this);
    const $wrapper = $this.closest('.background-image-wrapper');
    const value = {};

    value.vertical_alignment = getAlignmentValue($wrapper, 'vertical');

    toggleCustomWrappers($wrapper);
    setHiddenValue($this, value);
  });

  $(document).on('click', '.restore-radio-icon', function() {
    const $wrapper = $(this).closest('.background-image-wrapper');

    toggleCustomWrappers($wrapper);
    setHiddenValue($(this));
  });

  $(document).on('change', selectors.backgroundRepeat, function() {
    const $this = $(this);
    const value = {};

    value['background-repeat'] = $this.val();
    setHiddenValue($this, value);
  });

  $(document).on('change', selectors.backgroundAttachment, function() {
    const $this = $(this);
    const value = {};

    value['background-attachment'] = $this.val();
    setHiddenValue($this, value);
  });

  $(document).on('change keyup', `${selectors.backgroundSize}, ${selectors.customBackgroundWidthNumber}, ${selectors.customBackgroundWidthUnit}, ${selectors.customBackgroundHeightNumber}, ${selectors.customBackgroundHeightUnit}`, function() {
    const $this = $(this);
    const $wrapper = $this.closest('.background-image-wrapper');
    const value = {};

    value['background-size'] = getBackgroundSizeValue($wrapper) || $this.val();

    toggleCustomWrappers($wrapper);
    setHiddenValue($this, value);
  });
});
