jQuery(function($) {
  function parseDependenciesAttribute(field) {
    if (!field || !field.getAttribute) {
      return null;
    }

    let json = field.getAttribute('data-dependencies');

    if (!json) {
      return null;
    }

    json = json.replaceAll('\'', '"');

    try {
      return JSON.parse(json);
    }
    catch (error) {
      return null;
    }
  }

  function getDependencyScope($field, $context) {
    const $repeaterWrapper = $field.closest('.repeater');

    if ($repeaterWrapper.length) {
      return $repeaterWrapper;
    }

    if ($context && $context.length) {
      return $context;
    }

    return $(document);
  }

  function findDependentField(fieldName, $scope) {
    const selectors = [
      `[data-base-name="${fieldName}"]:input`,
      `[data-base-name="${fieldName}"]`,
      `[name="${fieldName}"]`,
      `[name$="[${fieldName}]"]`,
      `[name$="[${fieldName}][]"]`,
      `[name$="_${fieldName}"]`,
    ];

    for (const selector of selectors) {
      let $dependentField = $scope.find(selector);

      if (!$dependentField.length) {
        continue;
      }

      if (!$dependentField.is(':input')) {
        const $nestedInputs = $dependentField.find(':input');

        if ($nestedInputs.length) {
          return $nestedInputs;
        }
      }

      return $dependentField;
    }

    return $();
  }

  function getFieldCurrentValue($dependentField) {
    if (!$dependentField || !$dependentField.length) {
      return '';
    }

    const $inputs = $dependentField.is(':input') ? $dependentField : $dependentField.find(':input');

    if (!$inputs.length) {
      return '';
    }

    const $radios = $inputs.filter(':radio');

    if ($radios.length) {
      const $checked = $radios.filter(':checked');

      return $checked.length ? ($checked.val() || '') : '';
    }

    const $checkboxes = $inputs.filter(':checkbox');

    if ($checkboxes.length) {
      const $checked = $checkboxes.filter(':checked');

      if (!$checked.length) {
        return '';
      }

      if ($checkboxes.length === 1) {
        return $checked.val() || 'yes';
      }

      return $checked.map((_, element) => $(element).val()).get();
    }

    const $selects = $inputs.filter('select');

    if ($selects.length) {
      const value = $selects.val();

      return Array.isArray(value) ? value.map(String) : (value || '');
    }

    const value = $inputs.first().val();

    return Array.isArray(value) ? value.map(String) : (value || '');
  }

  function isConditionMet(fieldValue, allowedValues) {
    const normalizedAllowedValues = allowedValues.map(String);

    if (Array.isArray(fieldValue)) {
      return fieldValue.some((value) => normalizedAllowedValues.includes(String(value)));
    }

    return normalizedAllowedValues.includes(String(fieldValue));
  }

  function toggleFieldVisibility($field, shouldShow) {
    const $formGroup = $field.closest('.form-group');
    const $tableRow = $field.closest('tr');
    const isInRepeater = $field.closest('.repeater-wrapper').length > 0;

    if (shouldShow) {
      $formGroup.show();

      if (!isInRepeater && $tableRow.length) {
        $tableRow.show();
      }
    }
    else {
      $formGroup.hide();

      if (!isInRepeater && $tableRow.length) {
        $tableRow.hide();
      }
    }
  }

  function handleFieldVisibility(field, context) {
    const dependencies = parseDependenciesAttribute(field);

    if (!dependencies) {
      return;
    }

    const $field = $(field);
    const $scope = getDependencyScope($field, $(context));
    let groupConditionsMet = false;

    for (const group of dependencies) {
      let allConditionsMet = true;

      for (const condition of group) {
        let fieldName = condition.field.replace('field_', '');
        const $dependentField = findDependentField(fieldName, $scope);

        if (!$dependentField.length) {
          allConditionsMet = false;
          break;
        }

        const fieldValue = getFieldCurrentValue($dependentField);

        if (!isConditionMet(fieldValue, condition.values)) {
          allConditionsMet = false;
          break;
        }
      }

      if (allConditionsMet) {
        groupConditionsMet = true;
        break;
      }
    }

    toggleFieldVisibility($field, groupConditionsMet);
  }

  function initializeFieldVisibility(context) {
    const $context = context ? $(context) : $(document);

    $context.find('[data-dependencies]').each(function() {
      handleFieldVisibility(this, $context);
    });
  }

  function onDependencyChange(event) {
    const $target = $(event.target);
    const $scope = $target.closest('.repeater-wrapper');

    initializeFieldVisibility($scope.length ? $scope : $(document));
  }

  $(document).on('change input', 'input, textarea, select', onDependencyChange);

  initializeFieldVisibility();

  window.pomatioDependencies = {
    handleFieldVisibility,
    initializeFieldVisibility,
  };
});
