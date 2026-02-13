jQuery(function($) {
  let $media_modal;
  let $clicked_button;
  const mediaFrames = {};

  const applySelectedFont = function(frame) {
    const $selection = frame.state().get('selection');
    if (!$selection || typeof $selection.first !== 'function') {
      return;
    }

    const $selected_item = $selection.first();
    if (!$selected_item) {
      return;
    }

    const $attachment = $selected_item.toJSON();
    if (!$attachment || !$attachment.url || !$clicked_button || !$clicked_button.length) {
      return;
    }

    const $input = $clicked_button.closest('.font-variant').find('input[type="url"]').first();
    if (!$input.length) {
      return;
    }

    $input.val($attachment.url);
    frame.close();
    $input.trigger('change');
  };
  
  $(document).on('click', '.open-font-picker', function(e) {
    e.preventDefault();
    
    $clicked_button = $(this);

    let buttonMimeTypes = $clicked_button.data('mime-types');
    if (typeof buttonMimeTypes === 'string') {
      try {
        buttonMimeTypes = JSON.parse(buttonMimeTypes);
      }
      catch (error) {
        buttonMimeTypes = null;
      }
    }

    const globalMimeTypes = (typeof pom_form_font_picker !== 'undefined' && Array.isArray(pom_form_font_picker.mime_types) && pom_form_font_picker.mime_types.length)
      ? pom_form_font_picker.mime_types
      : ['font/woff2', 'font/woff', 'font/ttf'];
    const mimeTypes = Array.isArray(buttonMimeTypes) && buttonMimeTypes.length
      ? buttonMimeTypes
      : globalMimeTypes;
    const mimeTypesKey = JSON.stringify(mimeTypes);

    if (!mediaFrames[mimeTypesKey]) {
      mediaFrames[mimeTypesKey] = wp.media({
        title: pom_form_font_picker.title,
        button: {
          text: pom_form_font_picker.button
        },
        multiple: false,
        library: {
          type: mimeTypes,
          pom_form_font_picker: true
        }
      });

      mediaFrames[mimeTypesKey].on('select', function() {
        applySelectedFont(mediaFrames[mimeTypesKey]);
      });
      mediaFrames[mimeTypesKey].on('insert', function() {
        applySelectedFont(mediaFrames[mimeTypesKey]);
      });
    }

    $media_modal = mediaFrames[mimeTypesKey];
    $media_modal.open();
  });
  
});
