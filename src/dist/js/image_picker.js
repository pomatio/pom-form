jQuery(function($) {
  const renderPreview = function($wrapper, imageUrl) {
    const url = typeof imageUrl === 'string' ? imageUrl.trim() : '';
    const $imageWrapper = $wrapper.find('.image-wrapper');

    $imageWrapper.empty();

    if (url.length) {
      $('<img>', { width: '280px', alt: '', src: url }).appendTo($imageWrapper);
      $wrapper.find('.remove-selected-image').css('display', 'inherit');
    }
    else {
      $wrapper.find('.remove-selected-image').css('display', 'none');
    }
  };

  const getPreviewUrl = function($wrapper, $input) {
    if ($input.attr('type') === 'url') {
      return $input.val();
    }

    const previewUrl = $wrapper.data('previewUrl');
    return typeof previewUrl === 'string' ? previewUrl : '';
  };

  const renderPreviewFromInput = function($input) {
    const $wrapper = $input.closest('.pomatio-framework-image-wrapper');
    renderPreview($wrapper, getPreviewUrl($wrapper, $input));
  };

  $('.pomatio-framework-image-wrapper').each(function() {
    const $wrapper = $(this);
    const $input = $wrapper.find('input[data-type="image_picker"]').first();
    if ($input.length) {
      renderPreview($wrapper, getPreviewUrl($wrapper, $input));
    }
  });

  let $media_modal;
  let $clicked_button;

  $(document).on('click', '.open-image-picker', function(e) {
    e.preventDefault();

    if (!$(this).closest('.pomatio-framework-image-wrapper').find('input[data-type="image_picker"]').length) {
      return;
    }

    $clicked_button = $(this);

    // If the uploader object has already been created, reopen the dialog
    if ($media_modal) {
      $media_modal.open();
      return;
    }

    // Extend the wp.media object
    $media_modal = wp.media.frames.file_frame = wp.media({
      title: pom_form_image_picker.title,
      button: {
        text: pom_form_image_picker.button
      },
      multiple: false
    });

    // When a file is selected, grab the URL and set it as the field's value
    $media_modal.on('select', function() {
      let $attachment = $media_modal.state().get('selection').first().toJSON();

      const $wrapper = $clicked_button.closest('.pomatio-framework-image-wrapper');
      const $input = $wrapper.find('input[data-type="image_picker"]');

      if ($input.attr('type') === 'url') {
        $input.val($attachment.url).trigger('change');
        return;
      }

      $wrapper.data('previewUrl', $attachment.url);
      $wrapper.attr('data-preview-url', $attachment.url);
      $input.val($attachment.id).trigger('change');
    });

    // Open the uploader dialog
    $media_modal.open();
  });

  $(document).on('input change', '.pomatio-framework-image-wrapper input[data-type="image_picker"]', function() {
    renderPreviewFromInput($(this));
  });

  $(document).on('click', '.pomatio-framework-image-wrapper .remove-selected-image', function(e) {
    e.preventDefault();

    const $wrapper = $(this).closest('.pomatio-framework-image-wrapper');
    $wrapper.data('previewUrl', '');
    $wrapper.removeAttr('data-preview-url');
    $wrapper.find('input[data-type="image_picker"]').val('').trigger('change');
  });

});
