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

  const renderPreviewFromInput = function($input) {
    renderPreview($input.closest('.pomatio-framework-image-wrapper'), $input.val());
  };

  $('.pomatio-framework-image-wrapper').each(function() {
    renderPreview($(this), $(this).find('input[type="url"]').val());
  });

  let $media_modal;
  let $clicked_button;

  $(document).on('click', '.open-image-picker', function(e) {
    e.preventDefault();

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

      $clicked_button.closest('.pomatio-framework-image-wrapper').find('input[type="url"]').val($attachment.url).trigger('change');
    });

    // Open the uploader dialog
    $media_modal.open();
  });

  $(document).on('input change', '.pomatio-framework-image-wrapper input[type="url"]', function() {
    renderPreviewFromInput($(this));
  });

  $(document).on('click', '.pomatio-framework-image-wrapper .remove-selected-image', function(e) {
    e.preventDefault();

    $(this).closest('.pomatio-framework-image-wrapper').find('input[type="url"]').val('').trigger('change');
  });

});
