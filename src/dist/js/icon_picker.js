jQuery(function($) {

  if (typeof $.fn.dialog === 'undefined') {
    return;
  }

  // Display remove button only if icon is selected.
  $('.icon-picker-wrapper .icon-wrapper').each(function() {
    let $this = $(this);

    if ($this.find('img').length) {
      $(this).closest('.icon-picker-wrapper').find('.remove-selected-icon').css('display', 'inherit');
    }
  });

  // Store the icon picker button that has been clicked
  let $clicked_button;

  let $icon_picker_modal = $('#pomatio-framework-icons-modal').dialog({
    title: '',
    dialogClass: 'wp-dialog pomatio-framework-icons-modal',
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
        $('#pomatio-framework-icons-modal').dialog('close');
      });
    },
    close: function() {
      // Set defaults to all libraries on modal close.
      $('#pomatio-framework-icons-modal .media-menu-item').removeClass('active').attr('aria-selected', '');
      $('#pomatio-framework-icons-modal .media-frame-content').empty().append('<span class="centered-text">' + pom_form_icon_picker.loading + '</span>');

      $('.pomatio-framework-icons-modal .media-menu [data-slug="all"]').addClass('active').attr('aria-selected', 'true');
      let $title = $('.pomatio-framework-icons-modal [data-slug="all"]').attr('data-label');
      $('#pomatio-framework-icons-modal .media-frame-title').empty().append('<h1>' + $title + '</h1>');
    },
    create: function() {
      $('.pomatio-framework-icons-modal .ui-dialog-titlebar').css('display', 'none');

      // Set defaults to all libraries on modal creation.
      $('.pomatio-framework-icons-modal .media-menu [data-slug="all"]').addClass('active').attr('aria-selected', 'true');
      let $title = $('.pomatio-framework-icons-modal [data-slug="all"]').attr('data-label');
      $('#pomatio-framework-icons-modal .media-frame-title').empty().append('<h1>' + $title + '</h1>');
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
        action: 'pom_form_get_icon_library_icons',
        library: 'all'
      },
      success: function($response) {
        $icon_picker_modal.closest('#pomatio-framework-icons-modal').find('.media-frame-content').empty().append($response);
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
  $(document).on('click', '#pomatio-framework-icons-modal .media-menu-item', function() {
    let $this = $(this);

    $('#pomatio-framework-icons-modal input[type="search"]').val('');

    $this.closest('#pomatio-framework-icons-modal').find('.media-frame-content').empty().append('<span class="centered-text">' + pom_form_icon_picker.loading + '</span>');

    $('#pomatio-framework-icons-modal .media-menu-item').removeClass('active').attr('aria-selected', '');
    $this.addClass('active').attr('aria-selected', 'true');

    let $library = $this.attr('data-slug');
    let $label = $this.attr('data-label');
    $this.closest('#pomatio-framework-icons-modal').find('.media-frame-title').empty().append('<h1>' + $label + '</h1>');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_form_get_icon_library_icons',
        library: $library
      },
      success: function($response) {
        $this.closest('#pomatio-framework-icons-modal').find('.media-frame-content').empty().append($response);
      }
    });
  });

  /**
   * When clicking on the icon.
   */
  $(document).on('click', '#pomatio-framework-icons-modal li.attachment', function(e) {
    $('#pomatio-framework-icons-modal li.attachment').removeClass('selected');
    $(this).addClass('selected');
    $('#pomatio-framework-icons-modal .pomatio-framework-icon-select-button').removeClass('disabled').prop('disabled', false);
  });

  /**
   * Pagination. Load more icons.
   */
  $(document).on('click', '#pomatio-framework-icons-modal .load-more-icons button', function() {
    let $this = $(this);

    $this.hide();
    $this.next('.icon-picker-spinner').show();

    let $offset = parseInt($this.attr('data-offset'));
    let $library = $('#pomatio-framework-icons-modal .media-menu-item.active').attr('data-slug');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_form_get_icon_library_icons',
        library: $library,
        offset: $offset + 88
      },
      success: function($response) {
        let $content = $this.closest('#pomatio-framework-icons-modal').find('.media-frame-content');
        $content.find('.load-more-icons').remove();
        $content.append($response);
      }
    });
  });

  /**
   * On Select icon button click.
   */
  $(document).on('click', '#pomatio-framework-icons-modal .pomatio-framework-icon-select-button', function(e) {
    e.preventDefault();

    let $icon_url = $('#pomatio-framework-icons-modal .attachment.selected img').attr('src');
    let $input = $clicked_button.closest('.icon-picker-wrapper').find('input[type="hidden"]');

    $input.val($icon_url);
    $input[0].dispatchEvent(new Event('input', { bubbles: true }));

    $clicked_button.closest('.icon-picker-wrapper').find('.icon-wrapper').empty().append('<img alt="" src="' + $icon_url + '">');
    $clicked_button.closest('.icon-picker-wrapper').find('.remove-selected-icon').css('display', 'inherit');

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

  $(document).on('keyup', '#pomatio-framework-icons-modal input[type="search"]', delay_search(function() {
    let $this = $(this);
    let $search = $this.val();
    let $library = $this.closest('#pomatio-framework-icons-modal').find('.media-menu button.active').attr('data-slug');
    $this.closest('#pomatio-framework-icons-modal').find('.media-frame-content').empty().append('<span class="centered-text">' + pom_form_icon_picker.loading + '</span>');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'pom_form_get_icon_by_name',
        search: $search,
        library: $library
      },
      success: function($response) {
        $this.closest('#pomatio-framework-icons-modal').find('.media-frame-content').empty().append($response);
      }
    });
  }, 500));

  $(document).on('click', '.icon-picker-wrapper .remove-selected-icon', function(e) {
    e.preventDefault();

    let $this = $(this);
    let $wrapper = $this.closest('.icon-picker-wrapper');

    $wrapper.find('.icon-wrapper').empty();
    $wrapper.find('input[type="hidden"]').val('');
    $this.css('display', 'none');
  });

});
