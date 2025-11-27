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
    let selector = `[data-base-name="${fieldName}"]`;
    let $dependentField = $scope.find(selector);

    if (!$dependentField.length) {
      $dependentField = $scope.find(`[name="${fieldName}"]`);
    }

    if (!$dependentField.length) {
      $dependentField = $scope.find(`[name$="[${fieldName}]"]`);
    }

    if (!$dependentField.length) {
      $dependentField = $scope.find(`[name$="[${fieldName}][]"]`);
    }

    return $dependentField;
  }

  function toggleFieldVisibility($field, shouldShow) {
    const $formGroup = $field.closest('.form-group');
    const $tableRow = $field.closest('tr');

    if (shouldShow) {
      $formGroup.show();
      if ($tableRow.length) {
        $tableRow.show();
      }
    }
    else {
      $formGroup.hide();
      if ($tableRow.length) {
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

        let fieldValue = '';

        if ($dependentField.is(':radio')) {
          fieldValue = $dependentField.filter(':checked').val() || '';
        }
        else {
          fieldValue = $dependentField.first().val();
        }

        if (!condition.values.includes(fieldValue)) {
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
