jQuery(function($) {

  const getIconPickerInput = function($wrapper) {
    return $wrapper.find('input[data-type="icon_picker"]').first();
  };

  const refreshIconPickerState = function($wrapper) {
    const $input = getIconPickerInput($wrapper);
    const value = $input.length ? String($input.val() || '').trim() : '';
    const hasIcon = value.length > 0 || $wrapper.find('.icon-wrapper img').length > 0;

    $wrapper.toggleClass('has-selected-icon', hasIcon);
  };

  const refreshIconPickers = function($scope) {
    $scope.find('.icon-picker-wrapper').addBack('.icon-picker-wrapper').each(function() {
      refreshIconPickerState($(this));
    });
  };

  const setIconPickerValue = function($wrapper, value) {
    const $input = getIconPickerInput($wrapper);

    if (!$input.length) {
      return;
    }

    $input.val(value);
    $input[0].dispatchEvent(new Event('input', { bubbles: true }));
    $input[0].dispatchEvent(new Event('change', { bubbles: true }));
  };

  refreshIconPickers($(document));

  $(document).ajaxComplete(function() {
    refreshIconPickers($(document));
  });

  $(document).on('input change', '.icon-picker-wrapper input[data-type="icon_picker"]', function() {
    refreshIconPickerState($(this).closest('.icon-picker-wrapper'));
  });

  $(document).on('click keydown', '.icon-picker-wrapper.is-clearable .remove-selected-icon', function(e) {
    if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
      return;
    }

    e.preventDefault();

    let $wrapper = $(this).closest('.icon-picker-wrapper');

    $wrapper.find('.icon-wrapper').empty();
    setIconPickerValue($wrapper, '');
    refreshIconPickerState($wrapper);
  });

  if (typeof $.fn.dialog === 'undefined') {
    return;
  }

  // Store the icon picker button that has been clicked
  let $clicked_button;

  let $icon_picker_modal = $('#pom-framework-icons-modal').dialog({
    title: '',
    dialogClass: 'wp-dialog pom-framework-icons-modal',
    autoOpen: false,
    draggable: false,
    minHeight: '95%',
    width: '95%',
    modal: true,
    resizable: false,
    closeOnEscape: true,
    position: {
      my: 'top',
      at: 'top+25',
      of: window
    },
    open: function() {
      $('.ui-widget-overlay').on('click', function() {
        $('#pom-framework-icons-modal').dialog('close');
      });
    },
    close: function() {
      // Set defaults to all libraries on modal close.
      $('#pom-framework-icons-modal .media-menu-item').removeClass('active').attr('aria-selected', '');
      $('#pom-framework-icons-modal .media-frame-content').empty().append('<span class="centered-text">' + pom_framework_icon_picker.loading + '</span>');

      $('.pom-framework-icons-modal .media-menu [data-slug="all"]').addClass('active').attr('aria-selected', 'true');
      let $title = $('.pom-framework-icons-modal [data-slug="all"]').attr('data-label');
      $('#pom-framework-icons-modal .media-frame-title').empty().append('<h1>' + $title + '</h1>');
    },
    create: function() {
      $('.pom-framework-icons-modal .ui-dialog-titlebar').css('display', 'none');

      // Set defaults to all libraries on modal creation.
      $('.pom-framework-icons-modal .media-menu [data-slug="all"]').addClass('active').attr('aria-selected', 'true');
      let $title = $('.pom-framework-icons-modal [data-slug="all"]').attr('data-label');
      $('#pom-framework-icons-modal .media-frame-title').empty().append('<h1>' + $title + '</h1>');
    }
  });

  $(document).on('click', '.open-icon-picker-modal', function(e) {
    e.preventDefault();

    $clicked_button = $(this);
    $icon_picker_modal.dialog('open');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_framework_get_icon_library_icons',
        library: 'all'
      },
      success: function($response) {
        $icon_picker_modal.closest('#pom-framework-icons-modal').find('.media-frame-content').empty().append($response);
      }
    });
  });

  $(document).on('click', '.close-icon-picker-modal', function(e) {
    e.preventDefault();
    $icon_picker_modal.dialog('close');
  });

  /**
   * Get the icons of selected library.
   */
  $(document).on('click', '#pom-framework-icons-modal .media-menu-item', function() {
    let $this = $(this);

    $('#pom-framework-icons-modal input[type="search"]').val('');

    $this.closest('#pom-framework-icons-modal').find('.media-frame-content').empty().append('<span class="centered-text">' + pom_framework_icon_picker.loading + '</span>');

    $('#pom-framework-icons-modal .media-menu-item').removeClass('active').attr('aria-selected', '');
    $this.addClass('active').attr('aria-selected', 'true');

    let $library = $this.attr('data-slug');
    let $label = $this.attr('data-label');
    $this.closest('#pom-framework-icons-modal').find('.media-frame-title').empty().append('<h1>' + $label + '</h1>');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_framework_get_icon_library_icons',
        library: $library
      },
      success: function($response) {
        $this.closest('#pom-framework-icons-modal').find('.media-frame-content').empty().append($response);
      }
    });
  });

  /**
   * When clicking on the icon.
   */
  $(document).on('click', '#pom-framework-icons-modal li.attachment', function(e) {
    $('#pom-framework-icons-modal li.attachment').removeClass('selected');
    $(this).addClass('selected');
    $('#pom-framework-icons-modal .pom-framework-icon-select-button').removeClass('disabled').prop('disabled', false);
  });

  /**
   * Pagination. Load more icons.
   */
  $(document).on('click', '#pom-framework-icons-modal .load-more-icons button', function() {
    let $this = $(this);

    $this.hide();
    $this.next('.icon-picker-spinner').show();

    let $offset = parseInt($this.attr('data-offset'));
    let $library = $('#pom-framework-icons-modal .media-menu-item.active').attr('data-slug');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_framework_get_icon_library_icons',
        library: $library,
        offset: $offset + 88
      },
      success: function($response) {
        let $content = $this.closest('#pom-framework-icons-modal').find('.media-frame-content');
        $content.find('.load-more-icons').remove();
        $content.append($response);
      }
    });
  });

  /**
   * On Select icon button click.
   */
  $(document).on('click', '#pom-framework-icons-modal .pom-framework-icon-select-button', function(e) {
    e.preventDefault();

    let $icon_url = $('#pom-framework-icons-modal .attachment.selected img').attr('src');
    let $wrapper = $clicked_button.closest('.icon-picker-wrapper');

    setIconPickerValue($wrapper, $icon_url);

    $wrapper.find('.icon-wrapper').empty().append('<img alt="" src="' + $icon_url + '">');
    refreshIconPickerState($wrapper);

    $icon_picker_modal.dialog('close');
  });

  /**
   * The delay function will return a wrapped function that internally handles an individual timer,
   * in each execution the timer is restarted with the time delay provided,
   * if multiple executions occur before this time passes, the timer will just reset and start again.
   * When the timer finally ends, the callback function is executed, passing the original context and arguments.
   *
   * @param fn
   * @param ms
   * @returns {(function(...[*]): void)|*}
   */
  function delay_search(fn, ms) {
    let timer = 0;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(fn.bind(this, ...args), ms || 0);
    };
  }

  $(document).on('keyup', '#pom-framework-icons-modal input[type="search"]', delay_search(function() {
    let $this = $(this);
    let $search = $this.val();
    let $library = $this.closest('#pom-framework-icons-modal').find('.media-menu button.active').attr('data-slug');
    $this.closest('#pom-framework-icons-modal').find('.media-frame-content').empty().append('<span class="centered-text">' + pom_framework_icon_picker.loading + '</span>');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_framework_get_icon_by_name',
        search: $search,
        library: $library
      },
      success: function($response) {
        $this.closest('#pom-framework-icons-modal').find('.media-frame-content').empty().append($response);
      }
    });
  }, 500));

});
